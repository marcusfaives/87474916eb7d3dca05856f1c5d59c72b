<?php
session_start();

require_once 'config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

$db->exec("CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    senha VARCHAR(255),
    cnpj VARCHAR(20),
    token_reset_senha VARCHAR(255),
    data_reset_token DATETIME
)");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['registrar'])) {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = password_hash($_POST['senha'], PASSWORD_BCRYPT);
        $cnpj = $_POST['cnpj'];

        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, cnpj) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nome, $email, $senha, $cnpj])) {
            echo "<div class='alert alert-success'>Usuário cadastrado com sucesso!</div>";
        } else {
            echo "<div class='alert alert-danger'>Erro ao cadastrar usuário!</div>";
        }
    } elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $senha = $_POST['senha'];

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            $_SESSION['usuario'] = $usuario['nome'];
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['tipo'] = $usuario['tipo'];
            $_SESSION['cnpj'] = $usuario['cnpj'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Credenciais inválidas!</div>";
        }
    } elseif (isset($_POST['redefinir_senha'])) {
        $email = $_POST['email'];

        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario) {
            $token = bin2hex(random_bytes(50));
            $db->prepare("UPDATE usuarios SET token_reset_senha = ?, data_reset_token = NOW() WHERE email = ?")
                ->execute([$token, $email]);

            $resetLink = "http://seusite.com/redefinir_senha.php?token=$token";
            echo "<div class='alert alert-info'>Um link de redefinição de senha foi enviado para o seu email.</div>";
        } else {
            echo "<div class='alert alert-danger'>Email não encontrado!</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login e Cadastro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-size: 0.9rem;
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="container mx-auto mt-8">
        <div class="bg-white p-4 rounded-md shadow-md mb-6">
            <img src="logo.jpg" alt="Logo">
        </div>

        <h1 class="text-2xl font-bold text-center mb-6">Cadastro de Fornecedores e Produtos</h1>

        <div class="flex flex-col lg:flex-row gap-6">
            <div class="w-full lg:w-1/2">
                <div class="bg-white p-6 rounded-md shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Registrar-se</h2>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="nome" class="block text-sm font-medium text-gray-700">Nome:</label>
                            <input type="text" id="nome" name="nome" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label for="email-register" class="block text-sm font-medium text-gray-700">Email:</label>
                            <input type="email" id="email-register" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label for="senha-register" class="block text-sm font-medium text-gray-700">Senha:</label>
                            <input type="password" id="senha-register" name="senha" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label for="cnpj-register" class="block text-sm font-medium text-gray-700">CNPJ:</label>
                            <input type="text" id="cnpj-register" name="cnpj" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <button type="submit" name="registrar" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Registrar</button>
                    </form>
                </div>
            </div>

            <div class="w-full lg:w-1/2">
                <div class="bg-white p-6 rounded-md shadow-md">
                    <h2 class="text-xl font-semibold mb-4">Login</h2>
                    <form method="POST">
                        <div class="mb-4">
                            <label for="email-login" class="block text-sm font-medium text-gray-700">Email:</label>
                            <input type="email" id="email-login" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div class="mb-4">
                            <label for="senha-login" class="block text-sm font-medium text-gray-700">Senha:</label>
                            <input type="password" id="senha-login" name="senha" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <button type="submit" name="login" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="fixed bottom-0 left-0 right-0 bg-gray-800 text-white p-4 z-50">
        <div class="container mx-auto flex flex-col lg:flex-row justify-between items-center">
            <p class="text-sm mb-4 lg:mb-0">Este site utiliza cookies para garantir a melhor experiência em nosso site. Ao continuar navegando, você concorda com o uso de cookies.</p>
            <button id="accept-cookies" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Aceitar e Fechar</button>
        </div>
    </div>

    <script>
        document.getElementById('accept-cookies').addEventListener('click', function() {
            document.querySelector('.fixed').style.display = 'none';
        });
    </script>


</body>

</html>