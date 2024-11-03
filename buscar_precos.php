<?php
require_once 'config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]));
}

if (isset($_GET['produto_id'])) {
    $produto_id = $_GET['produto_id'];
    $stmt = $db->prepare("SELECT * FROM precos WHERE produto_id = ? ORDER BY data_registro DESC");
    $stmt->execute([$produto_id]);
    $precos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($precos);
} else {
    echo json_encode(['error' => 'ID do produto não fornecido']);
}