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
        $stmt = $db->prepare("UPDATE fornecedores SET status = 'Ativado', data_ativacao = NOW(), data_inativacao = NULL, usuario_inativacao = NULL, usuario_ativacao = :usuario_id WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
    } elseif (isset($_POST['inativar'])) {
        $stmt = $db->prepare("UPDATE fornecedores SET status = 'Inativado', data_ativacao = NULL, data_inativacao = NOW(), usuario_ativacao = NULL, usuario_inativacao = :usuario_id WHERE id = :id");
        $stmt->bindParam(':id', $_POST['id'], PDO::PARAM_INT);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();        
    } elseif (isset($_POST['cadastrar'])) {
        $stmt = $db->prepare("INSERT INTO fornecedores 
        (razao_social, fantasia, cnpj, ie, cep, endereco, numero, bairro, cidade_uf, telefone, email, representante, representante_telefone, representante_email, status, usuario_id)
        VALUES (:razao_social, :fantasia, :cnpj, :ie, :cep, :endereco, :numero, :bairro, :cidade_uf, :telefone, :email, :representante, :representante_telefone, :representante_email, :status, :usuario_id)");

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

        $logData = [
            'razao_social' => $_POST['razao_social'],
            'fantasia' => $_POST['fantasia'],
            'cnpj' => $_POST['cnpj'],
            'telefone' => $_POST['telefone'],
            'email' => $_POST['email'],
            'representante' => $_POST['representante'],
            'usuario_id' => $usuario_id,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        $logFile = 'supplier_logs.txt';
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);

        echo '<script>console.log("Fornecedor adicionado com sucesso!");</script>';
    }
}

if ($tipo === 'Fornecedor') {
    $stmt = $db->prepare("SELECT * FROM fornecedores WHERE usuario_id = :usuario_id");
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
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

        <div id="formCadastro" class="card mb-4 p-2" style="display:none;">
            <h1 class="mb-4">Cadastrar Fornecedor</h1>
            <form method="POST" action="crudFornecedores.php">
                <div class="mb-3">
                    <label for="razao_social" class="form-label">Razão Social</label>
                    <input type="text" class="form-control d-none" name="cadastrar">
                    <input type="text" class="form-control" name="razao_social" required>
                </div>
                <div class="mb-3">
                    <label for="fantasia" class="form-label">Nome Fantasia</label>
                    <input type="text" class="form-control" name="fantasia" required>
                </div>
                <div class="mb-3">
                    <label for="cnpj" class="form-label">CNPJ</label>
                    <input type="text" class="form-control" name="cnpj" required>
                </div>
                <div class="mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" class="form-control" name="telefone" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" required value="TESTE@teste.com">
                </div>
                <div class="mb-3">
                    <label for="representante" class="form-label">Representante</label>
                    <input type="text" class="form-control" name="representante" required>
                </div>
                <div class="mb-3">
                    <label for="representante_telefone" class="form-label">Telefone Representante</label>
                    <input type="text" class="form-control" name="representante_telefone" maxlength="15">
                </div>
                <div class="mb-3">
                    <label for="representante_email" class="form-label">Email Representante</label>
                    <input type="email" class="form-control" name="representante_email" maxlength="100">
                </div>
                <button type="submit" class="btn btn-primary">Cadastrar</button>
                <button type="button" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
            </form>
        </div>

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

                                    <?php if ($f['status'] == 'Novo' || $f['status'] == ''): ?>
                                        <?php if ($tipo == 'Validador'): ?>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                                <button type="submit" name="ativar" class="btn btn-success btn-sm">Ativar</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php elseif ($f['status'] == 'Inativado' && $tipo == "Admin"): ?>
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

    <script>
        // document.getElementById('btnAdicionar').addEventListener('click', function() {
        //     document.getElementById('formCadastro').style.display = 'block';
        // });

        // document.getElementById('btnCancelar').addEventListener('click', function() {
        //     document.getElementById('formCadastro').style.display = 'none';
        // });

        function editar(fornecedor) {
            document.getElementsByName('razao_social')[0].value = fornecedor.razao_social;
            document.getElementsByName('fantasia')[0].value = fornecedor.fantasia;
            document.getElementsByName('cnpj')[0].value = fornecedor.cnpj;
            document.getElementsByName('telefone')[0].value = fornecedor.telefone;
            document.getElementsByName('email')[0].value = fornecedor.email;
            document.getElementsByName('representante')[0].value = fornecedor.representante;
            document.getElementById('formCadastro').style.display = 'block';
        }

        let tipo = '<?= $tipo ?>';

        console.log(tipo);

        if (tipo === 'Validador') {
            $('#btnAdicionar').hide();
            $('button[name="deletar"]').hide();
            $('button[name="inativar"]').hide();
            $('button[name="editar"]').hide();
        }

        if (tipo === 'Fornecedor') {
            $('button[name="deletar"]').hide();
            $('button[name="ativar"]').hide();
            $('button[name="inativar"]').hide();
            $('button[name="editar"]').hide();
        }

        $('#btnAdicionar').on('click', function() {
            $('#formCadastro').toggle();
        });

        $('#btnCancelar').on('click', function() {
            $('#formCadastro').hide();
        });
    </script>
</body>

</html>