<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['produto_id'])) {
        $id = $_GET['produto_id'];
        $stmt = $db->prepare("SELECT produtos.descricao_produto as produto, precos.* FROM precos
                                        INNER JOIN produtos ON produtos.id = precos.produto_id

                                        WHERE produto_id = ? ORDER BY data_registro DESC
        
        
        ");
        $stmt->execute([$id]);
        $precos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($precos) {
            echo json_encode($precos);
        } else {
            echo json_encode([]);
        }
    } else {
        echo json_encode(['error' => 'ID do produto não fornecido']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
