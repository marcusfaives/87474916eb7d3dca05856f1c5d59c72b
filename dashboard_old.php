<?php
session_start();
$tipo = $_SESSION['tipo'];
?>


<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><img src="logo.jpg" alt="Logo" class="img-fluid" style="max-width: 150px;"></a>
            <h1 class="navbar-text">Bem-vindo(a)! <strong><?= $_SESSION['usuario']; ?></strong></h1>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('crudProdutos.php')">Produtos Importados</a>
                    </li>
                    <?php if ($tipo == 'Admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="loadContent('validacao.php')">Validação de Produtos</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="loadContent('crudFornecedores.php')">Fornecedores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="#" onclick="logoff()">Logoff</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div id="conteudo" class="mb-4"></div>
    </div>

    <footer class="text-center text-muted">
        &copy; 2024 Importação de Produtos. Todos os direitos reservados.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadContent(url) {
            $('#conteudo').load(url);
        }

        let tipo = '<?= $tipo ?>';

        if (tipo === 'Admin') {
            loadContent('importacao.php');
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

            if (!validateForm()) {
                return;
            }

            const formData = new FormData(this);
            progressBar.show();

            axios.post('importar.php', formData, {
                    onUploadProgress: function(progressEvent) {
                        const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                        progressBar.find('.progress-bar').css('width', percentCompleted + '%').text(percentCompleted + '%');
                    }
                })
                .then(function(response) {
                    if (response.data.success) {
                        $('#alertSuccess').show();
                        $('#alertError').hide();
                        $('#fileInput').val('');
                        $('#captcha').val('');
                        generateCaptcha();
                    } else {
                        $('#alertError').show();
                        $('#alertSuccess').hide();
                    }
                    progressBar.hide();
                })
                .catch(function(error) {
                    console.error(error);
                    $('#alertError').show();
                    $('#alertSuccess').hide();
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
</body>

</html>
