<?php
session_start();
require 'vendor/autoload.php';
require_once 'config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$conn = new mysqli($host, $user, $pass, $dbname);

function limparCNPJ($cnpj)
{
    return preg_replace('/[^0-9]/', '', $cnpj);
}

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => true, 'message' => 'Conexão falhou: ' . $conn->connect_error]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file']['tmp_name'];

    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', '300');

    $id_usuario = $_SESSION['user_id'] ?? 12;
    if (!$id_usuario) {
        http_response_code(400);
        echo json_encode(['error' => true, 'message' => "Usuário não autenticado."]);
        exit;
    }

    try {
        $spreadsheet = IOFactory::load($file);

        $produtosSheet = $spreadsheet->getSheet(0);
        $produtosData = $produtosSheet->toArray(null, true, true, true);

        foreach ($produtosData as $index => $row) {
            if ($index === 1) continue;

            if (empty($row['E'])) continue;

            $codigo_barras_unidade = $conn->real_escape_string($row['E']);
            $cnpj = limparCNPJ($conn->real_escape_string($row['K'])); 

            if (strlen($cnpj) !== 14 || !is_numeric($cnpj)) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "CNPJ inválido. Deve ter 14 caracteres numéricos. ".$cnpj]);
                exit;
            }

            $sqlCheckFornecedor = "SELECT id FROM fornecedores WHERE cnpj = '$cnpj'";
            $resultFornecedor = $conn->query($sqlCheckFornecedor);
            if (!$resultFornecedor) {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => "Erro na consulta fornecedores: " . $conn->error]);
                exit;
            }

            if ($resultFornecedor->num_rows === 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "CNPJ não encontrado na tabela fornecedores."]);
                exit;
            }

            $sqlCheckFornecedor = "SELECT id FROM fornecedores WHERE usuario_id = '$id_usuario'";
            $resultFornecedor = $conn->query($sqlCheckFornecedor);
            if (!$resultFornecedor) {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => "Erro na consulta fornecedores: " . $conn->error]);
                exit;
            }

            if ($resultFornecedor->num_rows === 0) {
                http_response_code(400);
                echo json_encode(['error' => true, 'message' => "CNPJ não encontrado na tabela fornecedores."]);
                exit;
            }

            $sqlCheckProduto = "SELECT id FROM produtos WHERE codigo_barras_unidade = '$codigo_barras_unidade' AND cnpj = '$cnpj'";
            $resultProduto = $conn->query($sqlCheckProduto);
            if (!$resultProduto) {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => "Erro na consulta produtos: " . $conn->error]);
                exit;
            }

            if ($resultProduto->num_rows === 0) {
                $descricao_produto = $conn->real_escape_string($row['A']);
                $marca = $conn->real_escape_string($row['B']);
                $codigo_fabrica = $conn->real_escape_string($row['C']);
                $ncm = $conn->real_escape_string($row['D']);
                $codigo_barras_master = $conn->real_escape_string($row['G']);
                $quantidade_master = $conn->real_escape_string($row['H']);

                $sqlProdutos = "INSERT INTO produtos (descricao_produto, marca, codigo_fabrica, ncm, codigo_barras_unidade, codigo_barras_master, cnpj, id_usuario)
                VALUES ('$descricao_produto', '$marca', '$codigo_fabrica', '$ncm', '$codigo_barras_unidade', '$codigo_barras_master', '$cnpj', $id_usuario)
                ON DUPLICATE KEY UPDATE marca='$marca', ncm='$ncm', codigo_barras_master='$codigo_barras_master'";

                if ($conn->query($sqlProdutos) === FALSE) {
                    http_response_code(500);
                    echo json_encode(['error' => true, 'message' => "Erro ao inserir produtos: " . $conn->error, 'sql' => $sqlProdutos]);
                    exit;
                }
                $produto_id = $conn->insert_id;
            } else {
                $produto = $resultProduto->fetch_assoc();
                $produto_id = $produto['id'];
            }

            $preco_unidade = $conn->real_escape_string($row['F']);
            $unidade_medida_master = $conn->real_escape_string($row['H']);
            $quantidade_master = $conn->real_escape_string($row['I']);
            $preco_master = $conn->real_escape_string($row['J']);
            $data_registro = date('Y-m-d H:i:s');

            $sqlPrecos = "INSERT INTO precos (produto_id, preco_unidade, unidade_medida_master, quantidade_master, preco_master, id_usuario, data_registro)
            VALUES ('$produto_id', '$preco_unidade', '$unidade_medida_master', '$quantidade_master', '$preco_master', $id_usuario, '$data_registro')";

            if ($conn->query($sqlPrecos) === FALSE) {
                http_response_code(500);
                echo json_encode(['error' => true, 'message' => "Erro ao inserir preços: " . $conn->error, 'sql' => $sqlPrecos]);
                exit;
            }
        }

        http_response_code(200);
        echo json_encode(['error' => false, 'message' => "Dados importados com sucesso!"]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => "Erro ao processar a planilha: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => "Nenhum arquivo enviado ou erro no upload."]);
}

$conn->close();
