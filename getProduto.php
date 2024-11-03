<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para buscar o produto
        $stmt = $db->prepare("
            SELECT p.*, 
            (SELECT preco_unidade 
             FROM precos 
             WHERE produto_id = p.id 
             ORDER BY data_registro DESC LIMIT 1) as preco_unidade,
            (SELECT quantidade_master 
             FROM precos 
             WHERE produto_id = p.id 
             ORDER BY data_registro DESC LIMIT 1) as quantidade_master,
            (SELECT unidade_medida_master 
             FROM precos 
             WHERE produto_id = p.id 
             ORDER BY data_registro DESC LIMIT 1) as unidade_medida_master,
            (SELECT preco_master 
             FROM precos 
             WHERE produto_id = p.id 
             ORDER BY data_registro DESC LIMIT 1) as preco_master
            FROM produtos p
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            echo json_encode($produto);
        } else {
            echo json_encode(['error' => 'Produto não encontrado']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro na conexão com o banco de dados']);
    }
} else {
    echo json_encode(['error' => 'ID do produto não fornecido']);
}
