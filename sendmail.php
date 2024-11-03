<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'config.php';

function enviarEmailNotificacao($email, $assunto, $mensagem)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'mail.faives.com.br';
        $mail->SMTPAuth = true;
        $mail->Username = 'sistema@faives.com.br';
        $mail->Password = 'ComiteRevolucionario';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('sistema@faives.com.br', 'Sistema de Notificação');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $assunto;
        $mail->Body = $mensagem;
        $mail->AltBody = strip_tags($mensagem);

        $mail->send();
        echo "Email enviado para $email\n";
        return true;
    } catch (Exception $e) {
        echo "Erro ao enviar email: {$mail->ErrorInfo}\n";
        return false;
    }
}

$conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Consulta os fornecedores que precisam de validação
$query = "SELECT * FROM fornecedores WHERE status IS NULL";
$stmt = $conn->prepare($query);
$stmt->execute();
$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($fornecedores as $fornecedor) {
    $id = $fornecedor['id'];
    $dataCriacao = new DateTime($fornecedor['data_criacao']);
    $agora = new DateTime();
    $intervalo = $agora->getTimestamp() - $dataCriacao->getTimestamp();

    // Conta os produtos aguardando liberação
    $cnpj = $fornecedor['cnpj'];
    $produtosQuery = "SELECT COUNT(*) AS total_produtos FROM produtos WHERE cnpj = :cnpj AND status = 'não aprovado'";
    $produtosStmt = $conn->prepare($produtosQuery);
    $produtosStmt->bindParam(':cnpj', $cnpj, PDO::PARAM_STR);
    $produtosStmt->execute();
    $produtosCount = $produtosStmt->fetchColumn();

    if ($intervalo <= 48 * 3600 && empty($fornecedor['email_validador'])) {
        $para = 'marcus.lima@hotmail.com.br';
        $campoUpdate = 'email_validador';
        $mensagem = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        padding: 20px;
                    }
                    .container {
                        background-color: #ffffff;
                        border-radius: 5px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        padding: 20px;
                    }
                    h2 {
                        color: #333;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    table, th, td {
                        border: 1px solid #dddddd;
                    }
                    th, td {
                        padding: 10px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    .footer {
                        margin-top: 20px;
                        font-size: 12px;
                        color: #777;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Notificação de Validação de Fornecedor</h2>
                    <p>Prezado Validador,</p>
                    <p>O fornecedor ID <strong>$id</strong> está pendente de validação. Favor verificar.</p>
                    <table>
                        <tr>
                            <th>Razão Social</th>
                            <td>{$fornecedor['razao_social']}</td>
                        </tr>
                        <tr>
                            <th>CNPJ</th>
                            <td>$cnpj</td>
                        </tr>
                        <tr>
                            <th>Total de Produtos Aguardando Liberação</th>
                            <td>$produtosCount</td>
                        </tr>
                    </table>
                    <p>Por favor, tome as medidas necessárias.</p>
                    <div class='footer'>
                        <p>Atenciosamente,<br>Sistema de Notificação</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    } elseif ($intervalo > 48 * 3600 && empty($fornecedor['email_admin'])) {
        $para = 'marcus.lima@hotmail.com.br';
        $campoUpdate = 'email_admin';
        $mensagem = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        padding: 20px;
                    }
                    .container {
                        background-color: #ffffff;
                        border-radius: 5px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        padding: 20px;
                    }
                    h2 {
                        color: #333;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                    }
                    table, th, td {
                        border: 1px solid #dddddd;
                    }
                    th, td {
                        padding: 10px;
                        text-align: left;
                    }
                    th {
                        background-color: #f2f2f2;
                    }
                    .footer {
                        margin-top: 20px;
                        font-size: 12px;
                        color: #777;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Notificação de Fornecedor com Status Pendente</h2>
                    <p>Prezado Admin,</p>
                    <p>O fornecedor ID <strong>$id</strong> está com status pendente há mais de 48 horas. Favor verificar.</p>
                    <table>
                        <tr>
                            <th>Razão Social</th>
                            <td>{$fornecedor['razao_social']}</td>
                        </tr>
                        <tr>
                            <th>CNPJ</th>
                            <td>$cnpj</td>
                        </tr>
                        <tr>
                            <th>Total de Produtos Aguardando Liberação</th>
                            <td>$produtosCount</td>
                        </tr>
                    </table>
                    <p>Por favor, tome as medidas necessárias.</p>
                    <div class='footer'>
                        <p>Atenciosamente,<br>Sistema de Notificação</p>
                    </div>
                </div>
            </body>
            </html>
        ";
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
