<?php

include 'config.php';

function conectarBancoDeDadosMySQL()
{
    global $host, $user, $pass, $dbname;

    $db = @new mysqli($host, $user, $pass, $dbname);
    error_reporting(E_ALL);

    if ($db->connect_error) {
        die("Erro de conexão: " . $db->connect_error);
    }

    mysqli_query($db, "SET time_zone = '-04:00'");
    return $db;
}

function executarConsultaMultiplaMySQL($sql)
{
    $mysqli = conectarBancoDeDadosMySQL();

    if (!$mysqli) {
        return false;
    }

    $result = $mysqli->query($sql);

    if (!$result) {
        echo "Erro na consulta: " . $mysqli->error;
        $mysqli->close();
        return false;
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    $result->free_result();
    $mysqli->close();
    return $rows;
}

function conectarBancoDeDadosOracle()
{
    $usuario = 'C##MARCUS';
    $senha = 'Manaus123';
    $host = 'localhost';
    $porta = '1521';
    $service_name = 'local';

    $conn = oci_connect($usuario, $senha, "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$host)(PORT=$porta))(CONNECT_DATA=(SERVICE_NAME=$service_name)))");

    if (!$conn) {
        $e = oci_error();
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao conectar ao banco de dados: ' . htmlentities($e['message'], ENT_QUOTES)));
        exit();
    }

    return $conn;
}

function executarConsultaOracle($sql)
{
    $sql = "SELECT * FROM (" . $sql . ") WHERE ROWNUM <= 100";
    $conn = conectarBancoDeDadosOracle();
    $stmt = oci_parse($conn, $sql);

    if (!$stmt) {
        $e = oci_error($conn);
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao preparar a consulta: ' . htmlentities($e['message'], ENT_QUOTES)));
        exit();
    }

    $r = oci_execute($stmt);
    if (!$r) {
        $e = oci_error($stmt);
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao executar a consulta: ' . htmlentities($e['message'], ENT_QUOTES)));
        exit();
    }

    $result = [];
    while ($row = oci_fetch_assoc($stmt)) {
        $result[] = $row;
    }

    oci_free_statement($stmt);
    oci_close($conn);
    return $result;
}

function gerarSqlInsertDataOracle($tableName, $data)
{
    foreach ($data as $key => $value) {
        if ($value === null) {
            $data[$key] = 'NULL';
        } elseif (is_numeric($value)) {
            $data[$key] = $value; // Não colocar aspas em números
        } elseif ($key == 'dtcadastro' || $key == 'data_criacao' || $key == 'data_aprovacao') {
            // Para campos de data
            $data[$key] = "TO_DATE('" . date('Y-m-d H:i:s', strtotime($value)) . "', 'YYYY-MM-DD HH24:MI:SS')";
        } else {
            $data[$key] = "'" . addslashes($value) . "'";
        }
    }

    $columns = implode(", ", array_keys($data));
    $values = implode(", ", array_values($data));
    $sql = "INSERT INTO $tableName ($columns) VALUES ($values)";

    return $sql;
}


function executarComandoOracle($sql)
{
    $conn = conectarBancoDeDadosOracle();

    if (!$conn) {
        return false;
    }

    $stmt = oci_parse($conn, $sql);
    
    if (!$stmt) {
        $e = oci_error($conn);
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao preparar a consulta: ' . htmlentities($e['message'], ENT_QUOTES)));
        exit();
    }

    $r = oci_execute($stmt);
    if (!$r) {
        $e = oci_error($stmt);
        http_response_code(500);
        echo json_encode(array('error' => 'Erro ao executar a consulta: ' . htmlentities($e['message'], ENT_QUOTES)));
        exit();
    }

    oci_free_statement($stmt);
    oci_close($conn);
    return true;
}

function buscarCodFornecOracle($cnpj)
{
    $conn = conectarBancoDeDadosOracle();
    $sql = "SELECT codfornec FROM pcfornec WHERE cgc = :cnpj";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ':cnpj', $cnpj);

    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        echo "Erro ao buscar codfornec: " . htmlentities($e['message'], ENT_QUOTES);
        oci_free_statement($stmt);
        oci_close($conn);
        return null;
    }

    $codfornec = null;
    if ($row = oci_fetch_assoc($stmt)) {
        $codfornec = $row['CODFORNEC'];
    }

    oci_free_statement($stmt);
    oci_close($conn);
    return $codfornec;
}

$produtos = executarConsultaMultiplaMySQL("SELECT * FROM view_produtos_nova WHERE status = 'Completo'");

if (empty($produtos)) {
    echo "Nada para importar";
} else {
    foreach ($produtos as $produto) {
        // Busca o codfornec usando o CNPJ do produto
        $produto['codfornec'] = buscarCodFornecOracle($produto['cnpj']);

        if ($produto['codfornec'] === null) {
            echo "Erro: codfornec não encontrado para o produto com CNPJ " . $produto['cnpj'];
            continue;
        }

        // Gerar e executar o comando SQL de inserção
        $comandoInserir = gerarSqlInsertDataOracle('fwi_migracao', $produto);
        echo $comandoInserir;
        executarComandoOracle($comandoInserir);
    }
}

