<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'config.php';

function enviarEmailNotificacao($email, $assunto, $mensagem) {
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'mail.faives.com.br';
        $mail->SMTPAuth = true;
        $mail->Username = 'sistema@faives.com.br';
        $mail->Password = 'ComiteRevolucionario';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Remetente e destinatário
        $mail->setFrom('sistema@faives.com.br', 'Sistema de Notificação');
        $mail->addAddress($email);

        // Conteúdo do email
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;
        $mail->AltBody = strip_tags($mensagem);

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "Erro ao enviar email: {$mail->ErrorInfo}";
        return false;
    }
}

$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT * FROM fornecedores WHERE status IS NULL AND data_criacao <= NOW() - INTERVAL 48 HOUR";
$stmt = $conn->prepare($query);
$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($fornecedores as $fornecedor) {
    $id = $fornecedor['id'];
    $dataCriacao = new DateTime($fornecedor['data_criacao']);
    $intervalo = $dataCriacao->diff(new DateTime())->h;

    if ($intervalo >= 48 && empty($fornecedor['email_admin'])) {
        $para = 'admin@teste.com';
        $campoUpdate = 'email_admin';
        $mensagem = "O fornecedor ID $id está com status pendente há mais de 48 horas. Favor verificar.";
    } elseif (empty($fornecedor['email_validador'])) {
        //$para = 'validador@teste.com';
        $para = 'marcus.lima@hotmail.com.br';
        $campoUpdate = 'email_validador';
        $mensagem = "O fornecedor ID $id está pendente de validação. Favor verificar.";
    } else {
        continue;
    }

    $assunto = "Notificação de Fornecedor Pendente - ID $id";

    if (enviarEmailNotificacao($para, $assunto, $mensagem)) {
        $update = $conn->prepare("UPDATE fornecedores SET $campoUpdate = 1 WHERE id = :id");
        $update->bindParam(':id', $id, PDO::PARAM_INT);
        $update->execute();
    }
}
