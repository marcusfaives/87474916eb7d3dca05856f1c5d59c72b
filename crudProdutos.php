<?php

session_start();

$tipo = $_SESSION['tipo'];
$cnpj = $_SESSION['cnpj'];
require_once 'config.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id_usuario = $_SESSION['user_id'];

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Adicionar/Editar produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $descricao_produto = $_POST['descricao_produto'];
    $marca = $_POST['marca'];
    $codigo_fabrica = $_POST['codigo_fabrica'];
    $ncm = $_POST['ncm'];
    $codigo_barras_unidade = $_POST['codigo_barras_unidade'];
    $codigo_barras_master = $_POST['codigo_barras_master'];
    $cnpj = $_POST['cnpj'];
   

    // Preço information
    $preco_unidade = $_POST['preco_unidade'] ?? null;
    $quantidade_master = $_POST['quantidade_master'] ?? null;
    $unidade_medida_master = $_POST['unidade_medida_master'] ?? null;
    $preco_master = $_POST['preco_master'] ?? null;

    $db->beginTransaction();
    try {
        if ($id) {
            // Atualizar produto
            $stmt = $db->prepare("UPDATE produtos SET descricao_produto = ?, marca = ?, codigo_fabrica = ?, ncm = ?, codigo_barras_unidade = ?, codigo_barras_master = ?, cnpj = ?, id_usuario = ? WHERE id = ?");
            $stmt->execute([$descricao_produto, $marca, $codigo_fabrica, $ncm, $codigo_barras_unidade, $codigo_barras_master, $cnpj, $id_usuario, $id]);
        } else {
            // Inserir novo produto
            $stmt = $db->prepare("INSERT INTO produtos (descricao_produto, marca, codigo_fabrica, ncm, codigo_barras_unidade, codigo_barras_master, cnpj, id_usuario) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$descricao_produto, $marca, $codigo_fabrica, $ncm, $codigo_barras_unidade, $codigo_barras_master, $cnpj, $id_usuario]);
            $id = $db->lastInsertId();
        }

        // Inserir novo registro de preço
        $stmt = $db->prepare("INSERT INTO precos (produto_id, preco_unidade, quantidade_master, unidade_medida_master, preco_master) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $preco_unidade, $quantidade_master, $unidade_medida_master, $preco_master]);

        $db->commit();
        header('Location: crudProdutos.php');
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        die("Erro ao salvar produto: " . $e->getMessage());
    }
}

// Excluir produto
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $db->beginTransaction();
    try {
        $stmt = $db->prepare("DELETE FROM precos WHERE produto_id = ?");
        $stmt->execute([$id]);
        $stmt = $db->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id]);
        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        die("Erro ao excluir produto: " . $e->getMessage());
    }
    header('Location: crudProdutos.php');
    exit;
}

// Pesquisa
$search = $_GET['search'] ?? '';


$id_usuario = $_SESSION['user_id'];

// Monta a query
$sql = "
    SELECT p.*, 
    pr.preco_unidade as preco_recente,
    pr.quantidade_master as quantidade_master_recente,
    pr.unidade_medida_master as unidade_medida_master_recente,
    pr.preco_master as preco_master_recente
    FROM produtos p
    LEFT JOIN (
        SELECT produto_id, preco_unidade, quantidade_master, unidade_medida_master, preco_master,
               ROW_NUMBER() OVER (PARTITION BY produto_id ORDER BY data_registro DESC) as rn
        FROM precos
    ) pr ON p.id = pr.produto_id AND pr.rn = 1
    LEFT JOIN fornecedores f ON f.cnpj = p.cnpj
    WHERE p.descricao_produto LIKE :search
    " . ($tipo === 'Fornecedor' ? "AND f.usuario_id = :user_id" : "") . "
    ORDER BY p.id
";

// Exibe a consulta com os parâmetros substituídos
$queryExibicao = $sql;
$queryExibicao = str_replace(':search', "'%" . $search . "%'", $queryExibicao);
if ($tipo === 'Fornecedor') {
    $queryExibicao = str_replace(':user_id', "'" . $id_usuario . "'", $queryExibicao);
}
//echo "<pre>$queryExibicao</pre>";

// Executa a consulta
$stmt = $db->prepare($sql);
$stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
if ($tipo === 'Fornecedor') {
    $stmt->bindValue(':user_id', $id_usuario, PDO::PARAM_STR);
}
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Fornecedores</title>
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-dark text-light ">

    <div class="container w-full">
        <h1 class="text-light">Cadastro de Produtos</h1>

        <!-- Formulário de Cadastro -->
        <div id="formProduto" class="d-none">
            <div class="card">
                <div class="card-body">
                    <form id="produtoForm" method="POST">
                        <input type="hidden" name="id" id="produtoId">
                        <div class="mb-3">
                            <label for="descricao_produto" class="form-label">Descrição do Produto</label>
                            <input type="text" class="form-control" id="descricao_produto" name="descricao_produto" required>
                        </div>
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" class="form-control" id="marca" name="marca" required>
                        </div>
                        <div class="mb-3">
                            <label for="codigo_fabrica" class="form-label">Código de Fábrica</label>
                            <input type="text" class="form-control" id="codigo_fabrica" name="codigo_fabrica" required>
                        </div>
                        <div class="mb-3">
                            <label for="ncm" class="form-label">NCM</label>
                            <input type="text" class="form-control" id="ncm" name="ncm" required>
                        </div>
                        <div class="mb-3">
                            <label for="cnpj" class="form-label">CNPJ</label>
                            <input type="text" class="form-control" id="cnpj" name="cnpj" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                        <button type="button" class="btn btn-secondary" id="cancelar">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabela de Produtos -->
        <table id="tabelaProdutos" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Descrição do Produto</th>
                    <th>Marca</th>
                    <th>Código de Fábrica</th>
                    <th>NCM</th>
                    <th>CNPJ</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($produto['descricao_produto']); ?></td>
                        <td><?php echo htmlspecialchars($produto['marca']); ?></td>
                        <td><?php echo htmlspecialchars($produto['codigo_fabrica']); ?></td>
                        <td><?php echo htmlspecialchars($produto['ncm']); ?></td>
                        <td><?php echo htmlspecialchars($produto['cnpj']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-editar" data-id="<?php echo $produto['id']; ?>">Editar</button>
                            <a href="?delete=<?php echo $produto['id']; ?>" class="btn btn-danger">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#tabelaProdutos').DataTable();

            const btnEditar = document.querySelectorAll('.btn-editar');
            btnEditar.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    fetch(`getProduto.php?id=${id}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('produtoId').value = data.id;
                            document.getElementById('descricao_produto').value = data.descricao_produto;
                            document.getElementById('marca').value = data.marca;
                            document.getElementById('codigo_fabrica').value = data.codigo_fabrica;
                            document.getElementById('ncm').value = data.ncm;
                            document.getElementById('cnpj').value = data.cnpj;
                            document.getElementById('formProduto').classList.remove('d-none');
                        });
                });
            });

            document.getElementById('cancelar').addEventListener('click', function() {
                document.getElementById('formProduto').classList.add('d-none');
            });
        });
    </script>

</body>

<style>
    td, th {
        font-size: small;
    } 
</style>

</html>
