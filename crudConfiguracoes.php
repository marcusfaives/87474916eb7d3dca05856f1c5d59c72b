<?php
require_once 'config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

// Recuperar o registro mais recente
function getRecentConfig($db) {
    $query = "SELECT * FROM configuracoes ORDER BY data_criacao DESC LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Pega a configuração mais recente
$recentConfig = getRecentConfig($db);

// Adicionar nova configuração
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar todos os campos enviados pelo formulário
    $fields = [
        'codepto', 'codsec', 'codcategoria', 'unidademaster', 'qtunitcx', 'unidade', 'qtunit', 'naturezaproduto',
        'sugvenda', 'codncmex', 'usawms', 'pesoliq', 'pesobruto', 'gtincodauxiliar', 'gtincodauxiliar2', 
        'codauxiliar2', 'pcomext1', 'cestabasilegis', 'enviainftecnicanfe', 'usaunilever', 'enviarforcavendas', 
        'codfunccadastro', 'gtincodauxiliartrib', 'importado203', 'codimportacao', 'aceitavendafracao', 
        'conferenocheckout', 'tipomerc', 'coddistrib', 'temrepos', 'codmarca', 'codcomprador', 'sequencia', 
        'lastropal', 'qttotpal', 'alturapal', 'chavenfe', 'selecionado', 'modulo', 'rua', 'numero', 'apto', 
        'modulocx', 'ruacx', 'numerocx', 'aptocx', 'tipoalturapalette', 'codprodprinc', 'codprodmaster', 
        'codsubcategoria', 'usuario_id'
    ];

    $placeholders = implode(', ', array_fill(0, count($fields), '?'));
    $insertQuery = "INSERT INTO configuracoes (" . implode(', ', $fields) . ", data_criacao) VALUES ($placeholders, NOW())";

    $stmt = $db->prepare($insertQuery);
    $stmt->execute(array_map(fn($field) => $_POST[$field] ?? null, $fields));

    header("Location: crudConfiguracoes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Formulário de Configurações</title>
    <style>
        .form-group {
            margin-bottom: 1rem;
        }
        .form-control {
            width: 200px; /* Ajusta a largura dos campos */
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Adicionar Configuração</h2>
    <fieldset>
    <form id="configForm" method="POST">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label for="codepto" class="form-label">Código Produto</label>
                    <input type="number" class="form-control" id="codepto" name="codepto" value="<?php echo $recentConfig['codepto'] ?? '9999'; ?>">
                </div>
                <div class="form-group">
                    <label for="codsec" class="form-label">Código Secção</label>
                    <input type="number" class="form-control" id="codsec" name="codsec" value="<?php echo $recentConfig['codsec'] ?? '9999'; ?>">
                </div>
                <div class="form-group">
                    <label for="codcategoria" class="form-label">Código Categoria</label>
                    <input type="number" class="form-control" id="codcategoria" name="codcategoria" value="<?php echo $recentConfig['codcategoria'] ?? '99'; ?>">
                </div>
                <div class="form-group">
                    <label for="unidademaster" class="form-label">Unidade Master</label>
                    <input type="text" class="form-control" id="unidademaster" name="unidademaster" value="<?php echo $recentConfig['unidademaster'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="qtunitcx" class="form-label">Qtde Unidade por Caixa</label>
                    <input type="number" step="0.01" class="form-control" id="qtunitcx" name="qtunitcx" value="<?php echo $recentConfig['qtunitcx'] ?? ''; ?>">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="unidade" class="form-label">Unidade</label>
                    <input type="text" class="form-control" id="unidade" name="unidade" value="<?php echo $recentConfig['unidade'] ?? 'UN'; ?>">
                </div>
                <div class="form-group">
                    <label for="qtunit" class="form-label">Qtde por Unidade</label>
                    <input type="number" step="0.01" class="form-control" id="qtunit" name="qtunit" value="<?php echo $recentConfig['qtunit'] ?? '1.00'; ?>">
                </div>
                <div class="form-group">
                    <label for="naturezaproduto" class="form-label">Natureza do Produto</label>
                    <input type="text" class="form-control" id="naturezaproduto" name="naturezaproduto" value="<?php echo $recentConfig['naturezaproduto'] ?? 'OT'; ?>">
                </div>
                <div class="form-group">
                    <label for="sugvenda" class="form-label">Sugestão de Venda</label>
                    <input type="number" step="0.01" class="form-control" id="sugvenda" name="sugvenda" value="<?php echo $recentConfig['sugvenda'] ?? '0.00'; ?>">
                </div>
                <div class="form-group">
                    <label for="codncmex" class="form-label">Código NCM Ex</label>
                    <input type="text" class="form-control" id="codncmex" name="codncmex" value="<?php echo $recentConfig['codncmex'] ?? ''; ?>">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="usawms" class="form-label">Usa WMS</label>
                    <input type="text" class="form-control" id="usawms" name="usawms" value="<?php echo $recentConfig['usawms'] ?? 'N'; ?>">
                </div>
                <div class="form-group">
                    <label for="pesoliq" class="form-label">Peso Líquido</label>
                    <input type="number" step="0.01" class="form-control" id="pesoliq" name="pesoliq" value="<?php echo $recentConfig['pesoliq'] ?? '1.00'; ?>">
                </div>
                <div class="form-group">
                    <label for="pesobruto" class="form-label">Peso Bruto</label>
                    <input type="number" step="0.01" class="form-control" id="pesobruto" name="pesobruto" value="<?php echo $recentConfig['pesobruto'] ?? '1.00'; ?>">
                </div>
                <div class="form-group">
                    <label for="gtincodauxiliar" class="form-label">Código Aux. GTIN</label>
                    <input type="number" class="form-control" id="gtincodauxiliar" name="gtincodauxiliar" value="<?php echo $recentConfig['gtincodauxiliar'] ?? '13'; ?>">
                </div>
                <div class="form-group">
                    <label for="dtcadastro" class="form-label">Data de Cadastro</label>
                    <input type="date" class="form-control" id="dtcadastro" name="dtcadastro" value="<?php echo $recentConfig['data_criacao'] ?? date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label for="importado203" class="form-label">Importado 203</label>
                    <input type="text" class="form-control" id="importado203" name="importado203" value="<?php echo $recentConfig['importado203'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="codimportacao" class="form-label">Código de Importação</label>
                    <input type="text" class="form-control" id="codimportacao" name="codimportacao" value="<?php echo $recentConfig['codimportacao'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="codmarca" class="form-label">Código da Marca</label>
                    <input type="text" class="form-control" id="codmarca" name="codmarca" value="<?php echo $recentConfig['codmarca'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="codsubcategoria" class="form-label">Código da Subcategoria</label>
                    <input type="text" class="form-control" id="codsubcategoria" name="codsubcategoria" value="<?php echo $recentConfig['codsubcategoria'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label for="usuario_id" class="form-label">Usuário</label>
                    <input type="text" class="form-control" id="usuario_id" name="usuario_id" value="<?php echo $recentConfig['usuario_id'] ?? ''; ?>">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
