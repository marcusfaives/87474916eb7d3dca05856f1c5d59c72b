<?php
$host = 'localhost';
$dbname = 'cadastroautomatico';
$user = 'root';
$pass = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Verificar se a requisição POST tem o token e nova senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['token'])) {
    $token = $_POST['token'];
    $novaSenha = password_hash($_POST['nova_senha'], PASSWORD_BCRYPT);

    // Atualizar a senha e invalidar o token
    $stmt = $db->prepare("UPDATE usuarios SET senha = ?, token_reset_senha = NULL, data_reset_token = NULL WHERE token_reset_senha = ?");
    if ($stmt->execute([$novaSenha, $token])) {
        echo "Senha redefinida com sucesso!";
    } else {
        echo "Erro ao redefinir a senha!";
    }
    
// Verificar se o token é passado via GET
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verificar se o token é válido e buscar o usuário
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE token_reset_senha = ?");
    $stmt->execute([$token]);
    $usuario = $stmt->fetch();

    // Se o token não for válido
    if (!$usuario) {
        echo "Token inválido!";
        exit();
    }
} else {
    echo "Requisição inválida.";
    exit();
}
?>

</body>
</html>
