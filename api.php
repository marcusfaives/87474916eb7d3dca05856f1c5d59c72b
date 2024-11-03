<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");

require_once 'config.php';

// Conexão única ao banco de dados
$db = conectarDB($host, $dbname, $user, $pass);

// Função para conectar ao banco de dados
function conectarDB($host, $dbname, $user, $pass)
{
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch (PDOException $e) {
        echo json_encode(["error" => "Falha na conexão ao banco de dados: " . $e->getMessage()]);
        exit;
    }
}

// Endpoint para buscar produtos com campos específicos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['endpoint']) && $_GET['endpoint'] === 'produtos') {
    $cnpj = isset($_GET['cnpj']) ? $_GET['cnpj'] : '';
    $fields = isset($_GET['fields']) ? explode(',', $_GET['fields']) : ['*'];
    
    // Garante que haja pelo menos um campo válido selecionado
    $fieldList = !empty($fields) ? implode(',', $fields) : '*';

    try {
        $query = "SELECT $fieldList FROM view_produtos  WHERE (SELECT status FROM fornecedores WHERE cnpj = view_produtos.cnpj) = 'Ativado'";
        if (!empty($cnpj)) {
            $query .= " AND cnpj = :cnpj"; // Alterado de WHERE para AND
        }
        $stmt = $db->prepare($query);
        if (!empty($cnpj)) {
            $stmt->bindParam(':cnpj', $cnpj);
        }
        $stmt->execute();
        $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($produtos);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
    }
}



// Endpoint para buscar fornecedores
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['endpoint']) && $_GET['endpoint'] === 'fornecedores') {
    try {
        $stmt = $db->prepare("SELECT * FROM fornecedores where status='Ativado' ");
        $stmt->execute();
        $fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($fornecedores);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao buscar fornecedores: ' . $e->getMessage()]);
    }
}
// Endpoint para atualizar produtos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['endpoint']) && $_GET['endpoint'] === 'atualizar_produto') {
    $dados = json_decode(file_get_contents('php://input'), true);

    if (empty($dados['ids']) || !is_array($dados['ids'])) {
        echo json_encode(['erro' => 'IDs dos produtos são obrigatórios e devem ser um array']);
        exit;
    }

    // Monta a parte SET da consulta SQL
    $set = [];
    foreach ($dados as $key => $value) {
        if ($key !== 'ids' && $value !== null && $value !== '') { // Ignora campos vazios
            $set[] = "$key = :$key"; // Cria um parâmetro nomeado para cada campo
        }
    }

    if (empty($set)) {
        echo json_encode(['erro' => 'Não há dados para atualizar']);
        exit;
    }

    $setSql = implode(', ', $set);
    $ids = implode(',', array_map('intval', $dados['ids']));
    $query = "UPDATE produtos SET $setSql WHERE id IN ($ids)";
    $stmt = $db->prepare($query);

    // Bind dos parâmetros
    foreach ($dados as $key => $value) {
        if ($key !== 'ids' && $value !== null && $value !== '') { // Ignora campos vazios
            $stmt->bindValue(":$key", $value);
        }
    }

    try {
        if ($stmt->execute()) {
            echo json_encode(['sucesso' => 'Produtos atualizados com sucesso']);
        } else {
            echo json_encode(['erro' => 'Falha ao atualizar produtos']);
        }
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro na execução da query: ' . $e->getMessage()]);
    }
    exit;
}


