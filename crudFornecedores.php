<?php

session_start();
$tipo = $_SESSION['tipo'];
$cnpj = $_SESSION['cnpj'];

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

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD de Fornecedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
</head>

<body class="bg-dark text-light">
    <div class="container mt-5">
        <button class="btn btn-success btn-sm mb-4" id="btnAdicionar">Adicionar Fornecedor</button>
        <div class="card mb-4 p-2">
            <h1 class="mb-4">Cadastro de Fornecedores</h1>

            <?php if (!empty($tipo) || empty($cnpj)): ?>
                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Buscar fornecedores..." name="search" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Razão Social</th>
                            <th>Nome Fantasia</th>
                            <th>CNPJ</th>
                            <th>Telefone</th>
                            <th>Email</th>
                            <th>Representante</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fornecedores as $index => $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['razao_social']) ?></td>
                                <td><?= htmlspecialchars($f['fantasia']) ?></td>
                                <td><?= htmlspecialchars($f['cnpj']) ?></td>
                                <td><?= htmlspecialchars($f['telefone']) ?></td>
                                <td><?= htmlspecialchars($f['email']) ?></td>
                                <td><?= htmlspecialchars($f['representante']) ?></td>
                                <td><?= htmlspecialchars($f['status']) ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" id="editar" name="editar" onclick="editar(<?= htmlspecialchars(json_encode($f)) ?>)">Editar</button>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <button type="submit" name="deletar" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja deletar?')">Deletar</button>
                                    </form>

                                    <?php if ($f['status'] == 'Inativado'): ?>
                                        <?php if ($tipo !== 'Fornecedor'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <button type="submit" name="ativar" class="btn btn-success btn-sm">Ativar</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                            <button type="submit" name="inativar" class="btn btn-secondary btn-sm">Inativar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
<script>
    let tipo = '<?= $tipo ?>';

    if (tipo === 'Validador') {
        $('#btnAdicionar').hide();
        $('button[name="deletar"]').hide();
        $('button[name="inativar"]').hide();
        $('button[name="editar"]').hide();
    }

    if (tipo === 'Fornecedor') {
        $('#btnAdicionar').hide();
        $('button[name="deletar"]').hide();
        $('button[name="ativar"]').hide();
        $('button[name="inativar"]').hide();
        $('button[name="editar"]').hide();
    }
</script>
