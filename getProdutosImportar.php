<?php
require_once 'config.php';

try {
    // Conexão com o banco de dados Oracle
    $oracleDb = new PDO("oci:dbname=//$host/$dbname", $user, $pass);
    $oracleDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Conexão com o banco de dados MySQL
    $mysqlDb = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $mysqlDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta para buscar todos os produtos do MySQL
    $stmt = $mysqlDb->prepare("
        SELECT 
            p.`id`,
            p.`descricao_produto`,
            p.`marca`,
            p.`codigo_fabrica`,
            p.`ncm`,
            p.`codigo_barras_unidade`,
            p.`codigo_barras_master`,
            p.`cnpj`,
            p.`id_usuario`,
            p.`data_criacao`,
            p.`usuario_aprovacao`,
            p.`data_aprovacao`,
            p.`status`, 
            c.`codepto`,
            c.`codsec`,
            c.`codcategoria`,
            c.`unidademaster`,
            c.`qtunitcx`,
            c.`unidade`,
            c.`qtunit`,
            c.`naturezaproduto`,
            c.`sugvenda`,
            CONCAT(p.`ncm`, '.') AS `codncmex`,
            c.`usawms`,
            c.`pesoliq`,
            c.`pesobruto`,
            c.`gtincodauxiliar`,
            c.`gtincodauxiliar2`,
            c.`codauxiliar2`,
            c.`pcomext1`,
            c.`cestabasilegis`,
            c.`enviainftecnicanfe`,
            c.`usaunilever`,
            c.`enviarforcavendas`,
            c.`dtcadastro`,
            c.`codfunccadastro`,
            c.`gtincodauxiliartrib`,
            c.`importado203`,
            c.`codimportacao`,
            c.`aceitavendafracao`,
            c.`conferenocheckout`,
            c.`tipomerc`,
            c.`coddistrib`,
            c.`temrepos`,
            c.`codmarca`,
            c.`codcomprador`,
            c.`sequencia`,
            c.`lastropal`,
            c.`qttotpal`,
            c.`alturapal`,
            c.`chavenfe`,
            c.`selecionado`,
            c.`modulo`,
            c.`rua`,
            c.`numero`,
            c.`apto`,
            c.`modulocx`,
            c.`ruacx`,
            c.`numerocx`,
            c.`aptocx`,
            c.`tipoalturapalette`,
            c.`codprodprinc`,
            c.`codprodmaster`,
            c.`codsubcategoria`,
            p.`id_usuario` AS `usuario_id`
        FROM produtos p, (
            SELECT *
            FROM configuracoes
            WHERE id = (
                SELECT MAX(id)
                FROM configuracoes
            )
        ) c WHERE p.status <> 'Importado';
    ");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($produtos) {
        foreach ($produtos as $produto) {
            // Inserir dados na tabela FAIVES_MIGRACAO do Oracle
            $insertStmt = $oracleDb->prepare("
                INSERT INTO FAIVES_MIGRACAO (
                    id,
                    descricao_produto,
                    marca,
                    codigo_fabrica,
                    ncm,
                    codigo_barras_unidade,
                    codigo_barras_master,
                    cnpj,
                    id_usuario,
                    data_criacao,
                    usuario_aprovacao,
                    data_aprovacao,
                    status,
                    codepto,
                    codsec,
                    codcategoria,
                    unidademaster,
                    qtunitcx,
                    unidade,
                    qtunit,
                    naturezaproduto,
                    sugvenda,
                    codncmex,
                    usawms,
                    pesoliq,
                    pesobruto,
                    gtincodauxiliar,
                    gtincodauxiliar2,
                    codauxiliar2,
                    pcomext1,
                    cestabasilegis,
                    enviainftecnicanfe,
                    usaunilever,
                    enviarforcavendas,
                    dtcadastro,
                    codfunccadastro,
                    gtincodauxiliartrib,
                    importado203,
                    codimportacao,
                    aceitavendafracao,
                    conferenocheckout,
                    tipomerc,
                    coddistrib,
                    temrepos,
                    codmarca,
                    codcomprador,
                    sequencia,
                    lastropal,
                    qttotpal,
                    alturapal,
                    chavenfe,
                    selecionado,
                    modulo,
                    rua,
                    numero,
                    apto,
                    modulocx,
                    ruacx,
                    numerocx,
                    aptocx,
                    tipoalturapalette,
                    codprodprinc,
                    codprodmaster,
                    codsubcategoria,
                    usuario_id
                ) VALUES (
                    :id,
                    :descricao_produto,
                    :marca,
                    :codigo_fabrica,
                    :ncm,
                    :codigo_barras_unidade,
                    :codigo_barras_master,
                    :cnpj,
                    :id_usuario,
                    TO_TIMESTAMP(:data_criacao, 'YYYY-MM-DD HH24:MI:SS'),
                    :usuario_aprovacao,
                    TO_TIMESTAMP(:data_aprovacao, 'YYYY-MM-DD HH24:MI:SS'),
                    :status,
                    :codepto,
                    :codsec,
                    :codcategoria,
                    :unidademaster,
                    :qtunitcx,
                    :unidade,
                    :qtunit,
                    :naturezaproduto,
                    :sugvenda,
                    :codncmex,
                    :usawms,
                    :pesoliq,
                    :pesobruto,
                    :gtincodauxiliar,
                    :gtincodauxiliar2,
                    :codauxiliar2,
                    :pcomext1,
                    :cestabasilegis,
                    :enviainftecnicanfe,
                    :usaunilever,
                    :enviarforcavendas,
                    TO_DATE(:dtcadastro, 'YYYY-MM-DD'),
                    :codfunccadastro,
                    :gtincodauxiliartrib,
                    :importado203,
                    :codimportacao,
                    :aceitavendafracao,
                    :conferenocheckout,
                    :tipomerc,
                    :coddistrib,
                    :temrepos,
                    :codmarca,
                    :codcomprador,
                    :sequencia,
                    :lastropal,
                    :qttotpal,
                    :alturapal,
                    :chavenfe,
                    :selecionado,
                    :modulo,
                    :rua,
                    :numero,
                    :apto,
                    :modulocx,
                    :ruacx,
                    :numerocx,
                    :aptocx,
                    :tipoalturapalette,
                    :codprodprinc,
                    :codprodmaster,
                    :codsubcategoria,
                    :usuario_id
                )
            ");

            // Bind dos parâmetros
            $insertStmt->bindParam(':id', $produto['id']);
            $insertStmt->bindParam(':descricao_produto', $produto['descricao_produto']);
            $insertStmt->bindParam(':marca', $produto['marca']);
            $insertStmt->bindParam(':codigo_fabrica', $produto['codigo_fabrica']);
            $insertStmt->bindParam(':ncm', $produto['ncm']);
            $insertStmt->bindParam(':codigo_barras_unidade', $produto['codigo_barras_unidade']);
            $insertStmt->bindParam(':codigo_barras_master', $produto['codigo_barras_master']);
            $insertStmt->bindParam(':cnpj', $produto['cnpj']);
            $insertStmt->bindParam(':id_usuario', $produto['id_usuario']);
            $insertStmt->bindParam(':data_criacao', $produto['data_criacao']);
            $insertStmt->bindParam(':usuario_aprovacao', $produto['usuario_aprovacao']);
            $insertStmt->bindParam(':data_aprovacao', $produto['data_aprovacao']);
            $insertStmt->bindParam(':status', $produto['status']);
            $insertStmt->bindParam(':codepto', $produto['codepto']);
            $insertStmt->bindParam(':codsec', $produto['codsec']);
            $insertStmt->bindParam(':codcategoria', $produto['codcategoria']);
            $insertStmt->bindParam(':unidademaster', $produto['unidademaster']);
            $insertStmt->bindParam(':qtunitcx', $produto['qtunitcx']);
            $insertStmt->bindParam(':unidade', $produto['unidade']);
            $insertStmt->bindParam(':qtunit', $produto['qtunit']);
            $insertStmt->bindParam(':naturezaproduto', $produto['naturezaproduto']);
            $insertStmt->bindParam(':sugvenda', $produto['sugvenda']);
            $insertStmt->bindParam(':codncmex', $produto['codncmex']);
            $insertStmt->bindParam(':usawms', $produto['usawms']);
            $insertStmt->bindParam(':pesoliq', $produto['pesoliq']);
            $insertStmt->bindParam(':pesobruto', $produto['pesobruto']);
            $insertStmt->bindParam(':gtincodauxiliar', $produto['gtincodauxiliar']);
            $insertStmt->bindParam(':gtincodauxiliar2', $produto['gtincodauxiliar2']);
            $insertStmt->bindParam(':codauxiliar2', $produto['codauxiliar2']);
            $insertStmt->bindParam(':pcomext1', $produto['pcomext1']);
            $insertStmt->bindParam(':cestabasilegis', $produto['cestabasilegis']);
            $insertStmt->bindParam(':enviainftecnicanfe', $produto['enviainftecnicanfe']);
            $insertStmt->bindParam(':usaunilever', $produto['usaunilever']);
            $insertStmt->bindParam(':enviarforcavendas', $produto['enviarforcavendas']);
            $insertStmt->bindParam(':dtcadastro', $produto['dtcadastro']);
            $insertStmt->bindParam(':codfunccadastro', $produto['codfunccadastro']);
            $insertStmt->bindParam(':gtincodauxiliartrib', $produto['gtincodauxiliartrib']);
            $insertStmt->bindParam(':importado203', $produto['importado203']);
            $insertStmt->bindParam(':codimportacao', $produto['codimportacao']);
            $insertStmt->bindParam(':aceitavendafracao', $produto['aceitavendafracao']);
            $insertStmt->bindParam(':conferenocheckout', $produto['conferenocheckout']);
            $insertStmt->bindParam(':tipomerc', $produto['tipomerc']);
            $insertStmt->bindParam(':coddistrib', $produto['coddistrib']);
            $insertStmt->bindParam(':temrepos', $produto['temrepos']);
            $insertStmt->bindParam(':codmarca', $produto['codmarca']);
            $insertStmt->bindParam(':codcomprador', $produto['codcomprador']);
            $insertStmt->bindParam(':sequencia', $produto['sequencia']);
            $insertStmt->bindParam(':lastropal', $produto['lastropal']);
            $insertStmt->bindParam(':qttotpal', $produto['qttotpal']);
            $insertStmt->bindParam(':alturapal', $produto['alturapal']);
            $insertStmt->bindParam(':chavenfe', $produto['chavenfe']);
            $insertStmt->bindParam(':selecionado', $produto['selecionado']);
            $insertStmt->bindParam(':modulo', $produto['modulo']);
            $insertStmt->bindParam(':rua', $produto['rua']);
            $insertStmt->bindParam(':numero', $produto['numero']);
            $insertStmt->bindParam(':apto', $produto['apto']);
            $insertStmt->bindParam(':modulocx', $produto['modulocx']);
            $insertStmt->bindParam(':ruacx', $produto['ruacx']);
            $insertStmt->bindParam(':numerocx', $produto['numerocx']);
            $insertStmt->bindParam(':aptocx', $produto['aptocx']);
            $insertStmt->bindParam(':tipoalturapalette', $produto['tipoalturapalette']);
            $insertStmt->bindParam(':codprodprinc', $produto['codprodprinc']);
            $insertStmt->bindParam(':codprodmaster', $produto['codprodmaster']);
            $insertStmt->bindParam(':codsubcategoria', $produto['codsubcategoria']);
            $insertStmt->bindParam(':usuario_id', $produto['usuario_id']);

            // Executar a inserção
            $insertStmt->execute();
        }
    }

    echo "Dados migrados com sucesso!";
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
