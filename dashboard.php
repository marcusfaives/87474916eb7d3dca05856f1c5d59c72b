<?php
session_start();
$tipo = $_SESSION['tipo'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Dados de Fornecedores e Produtos</title>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .progress {
            display: none;
            height: 20px;
            margin-top: 20px;
        }

        h1 {
            font-size: 2em !important;
        }

        #conteudo {
            width: 100%;
            height: 1200px;
            border: none;
        }
    </style>
</head>

<body class="bg-dark">
    <div class="p-2 d-flex flex-wrap justify-content-center justify-content-sm-end w-100 gap-2 mt-4">
        <a href="#" onclick="loadContent('crudProdutos.php')" id="link_produtos" class="btn btn-primary">Produtos Importados</a>
        <?php if ($tipo == 'Admin' || $tipo ==  'Validador'): ?>
            <a href="#" onclick="loadContent('validacao.php')" class="btn btn-warning">Validação de Produtos</a>
        <?php endif; ?>
        <a href="#" onclick="loadContent('crudFornecedores.php')" class="btn btn-secondary">Fornecedores</a>
        <a class="btn btn-danger" onclick="logoff()">Logoff</a>
    </div>
    <div class="d-flex flex-wrap align-items-center justify-content-between p-1 bg-white shadow bg-dark">
        <h1 class="text-left w-100 w-sm-auto"><img src="logo.jpg" alt="Logo" style="width: 100px;" class="w-3 mb-1 m-3">Bem-vindo(a)! <strong><?= $_SESSION['usuario']; ?></strong></h1>
    </div>

    <div class="form">
        <div class="container mt-3">
            <div class="text-center mb-4">
                <p class="text-lg fw-bold text-primary">Importe os seus produtos</p>
            </div>

            <div class="card">
                <div class="card-header bg-gradient text-white text-center">
                    <h2 class="h5">Importar Planilha</h2>
                </div>
                <div class="card-body">
                    <p class="text-center text-muted mb-4">
                        Use o formato Excel (.xlsx) para importar dados de fornecedores e produtos. As abas devem seguir os formatos indicados: <strong>A primeira aba "Fornecedores" e a segunda "Produtos".</strong>
                    </p>

                    <div id="alertSuccess" class="d-none alert alert-success">Dados importados com sucesso!</div>
                    <div id="alertError" class="d-none alert alert-danger">Ocorreu um erro ao processar a planilha. Tente novamente.</div>

                    <form id="importForm" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">Selecione a planilha (.xlsx):</label>
                            <input type="file" id="fileInput" name="file" accept=".xlsx" required class="form-control">
                            <div id="fileError" class="d-none text-danger">Por favor, envie um arquivo Excel válido (.xlsx).</div>
                        </div>

                        <div class="mb-3">
                            <label for="captcha" class="form-label">Resolva o Captcha:</label>
                            <span id="captchaQuestion" class="form-text">Digite a resposta correta abaixo:</span>
                            <input type="text" id="captcha" name="captcha" required class="form-control">
                            <div id="captchaError" class="d-none text-danger">Captcha incorreto, por favor, tente novamente.</div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Enviar Planilha</button>
                    </form>

                    <div class="mt-3 progress">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;">0%</div>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="modelo_planilha.xlsx" class="btn btn-link">Baixar Planilha Modelo</a>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <p class="text-muted">Certifique-se de que o formato da planilha esteja correto. Se necessário, baixe o modelo e preencha os dados adequadamente.</p>
            </div>
        </div>
    </div>

    <iframe id="conteudo" class="conteudo"></iframe>

    <footer class="mt-5 text-center text-muted">
        &copy; 2024 Importação de Produtos. Todos os direitos reservados.
    </footer>

    <script>
        function loadContent(url) {
            $('.form').hide();
            $('#conteudo').attr('src', url);
        }

        let tipo = '<?= $tipo ?>';

        if (tipo != 'Validador') {
            $('.form').show();
        } else {
            $('.form').hide();
            $('#link_produtos').hide();
            loadContent('validacao.php');
        }

        let captchaAnswer;

        function generateCaptcha() {
            const num1 = Math.floor(Math.random() * 10) + 1;
            const num2 = Math.floor(Math.random() * 10) + 1;
            const operations = ['+', '-', '*'];
            const operation = operations[Math.floor(Math.random() * operations.length)];

            let question, answer;

            if (operation === '+') {
                question = `${num1} + ${num2}`;
                answer = num1 + num2;
            } else if (operation === '-') {
                question = `${num1} - ${num2}`;
                answer = num1 - num2;
            } else if (operation === '*') {
                question = `${num1} * ${num2}`;
                answer = num1 * num2;
            }

            $('#captchaQuestion').text(`Quanto é ${question}?`);
            captchaAnswer = answer;
        }

        function validateForm() {
            const fileInput = $('#fileInput');
            const captchaInput = $('#captcha');
            let valid = true;

            if (!fileInput.val()) {
                $('#fileError').show();
                valid = false;
            } else {
                $('#fileError').hide();
            }

            if (captchaInput.val() != captchaAnswer) {
                $('#captchaError').show();
                valid = false;
            } else {
                $('#captchaError').hide();
            }

            return valid;
        }

        $('#importForm').on('submit', function(event) {
            event.preventDefault();

            const progressBar = $('.progress');
            progressBar.show();
            progressBar.find('.progress-bar').css('width', '0%').text('0%');

            const formData = new FormData(this);
            axios.post('importar.php', formData, {
                onUploadProgress: function(progressEvent) {
                    const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    progressBar.find('.progress-bar').css('width', percentCompleted + '%').text(percentCompleted + '%');
                }
            })
            .then(function(response) {
                if (response.data.success) {
                    $('#alertSuccess').removeClass('d-none');                    
                    $('#fileInput').val('');
                    $('#captcha').val('');
                    generateCaptcha();

                    loadContent('crudProdutos.php');

                } else {
                    $('#alertError').removeClass('d-none');
                    $('#alertSuccess').addClass('d-none');
                }
                progressBar.hide();
            })
            .catch(function(error) {
                $('#alertError').removeClass('d-none');
                $('#alertSuccess').addClass('d-none');
                progressBar.hide();
                generateCaptcha();
            });
        });

        function logoff() {
            if (confirm('Deseja realmente sair?')) {
                window.location.href = 'logoff.php';
            }
        }

        $(document).ready(function() {
            generateCaptcha();
        });
    </script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

</body>
</html>
