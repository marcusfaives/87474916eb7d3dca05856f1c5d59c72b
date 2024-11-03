<?php

session_start();
$tipo = $_SESSION['tipo'];
$cnpj = $_SESSION['cnpj'];
$usuario_id = $_SESSION['user_id'];

require_once 'config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['deletar'])) {
        $stmt = $db->prepare("DELETE FROM fornecedores WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();
    } elseif (isset($_POST['ativar'])) {
        $stmt = $db->prepare("UPDATE fornecedores SET status = 'Ativado' WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();
    } elseif (isset($_POST['inativar'])) {
        $stmt = $db->prepare("UPDATE fornecedores SET status = 'Inativado' WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->execute();
    } elseif (isset($_POST['cadastrar'])) {
        // Exibir os dados recebidos no console
        error_log(print_r($_POST, true)); // Para depuração no servidor

        $stmt = $db->prepare("INSERT INTO fornecedores (razao_social, fantasia, cnpj, ie, cep, endereco, numero, bairro, cidade_uf, telefone, email, representante, representante_telefone, representante_email, status, usuario_id) VALUES (:razao_social, :fantasia, :cnpj, :ie, :cep, :endereco, :numero, :bairro, :cidade_uf, :telefone, :email, :representante, :representante_telefone, :representante_email, :status, :usuario_id)");

        $stmt->bindParam(':razao_social', $_POST['razao_social'], PDO::PARAM_STR);
        $stmt->bindParam(':fantasia', $_POST['fantasia'], PDO::PARAM_STR);
        $stmt->bindParam(':cnpj', $_POST['cnpj'], PDO::PARAM_STR);
        $stmt->bindParam(':ie', $_POST['ie'], PDO::PARAM_STR);
        $stmt->bindParam(':cep', $_POST['cep'], PDO::PARAM_STR);
        $stmt->bindParam(':endereco', $_POST['endereco'], PDO::PARAM_STR);
        $stmt->bindParam(':numero', $_POST['numero'], PDO::PARAM_STR);
        $stmt->bindParam(':bairro', $_POST['bairro'], PDO::PARAM_STR);
        $stmt->bindParam(':cidade_uf', $_POST['cidade_uf'], PDO::PARAM_STR);
        $stmt->bindParam(':telefone', $_POST['telefone'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $_POST['email'], PDO::PARAM_STR);
        $stmt->bindParam(':representante', $_POST['representante'], PDO::PARAM_STR);
        $stmt->bindParam(':representante_telefone', $_POST['representante_telefone'], PDO::PARAM_STR);
        $stmt->bindParam(':representante_email', $_POST['representante_email'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $_POST['status'], PDO::PARAM_STR);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);

        $stmt->execute();

        echo '<script>console.log("Fornecedor cadastrado com sucesso!");</script>'; // Mensagem de sucesso no console
    }
}

if (empty($tipo) && !empty($cnpj)) {
    $stmt = $db->prepare("SELECT * FROM fornecedores WHERE cnpj = :cnpj");
    $stmt->bindParam(':cnpj', $cnpj, PDO::PARAM_STR);
    $stmt->execute();
    $fornecedores = $stmt->fetchAll();
} else {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10;
    $start = ($page > 1) ? ($page * $perPage) - $perPage : 0;

    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $whereClause = $search ? "WHERE status = 'Ativado' AND (razao_social LIKE :search OR fantasia LIKE :search OR cnpj LIKE :search)" : "";

    $stmt = $db->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM fornecedores $whereClause ORDER BY razao_social LIMIT :start, :perPage");
    if ($search) {
        $searchParam = "%$search%";
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    $fornecedores = $stmt->fetchAll();

    $stmt = $db->query("SELECT FOUND_ROWS() as total");
    $totalRows = $stmt->fetch()['total'];
    $totalPages = ceil($totalRows / $perPage);
}
?>