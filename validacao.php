<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciamento de Produtos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" rel="stylesheet">


    <script>
        const camposAba = {
            embalagem: ["id", "descricao_produto", "marca", "ncm", "codigo_barras_unidade", "pesoliq", "pesobruto", "gtincodauxiliar", "gtincodauxiliartrib", "gtincodauxiliar2", "temrepos", "aceitavendafracao", "validacao"],
            classificacao: ["id", "descricao_produto", "marca", "ncm", "codigo_barras_unidade", "codepto", "codsec", "codcategoria", "codmarca", "validacao"],
            logistica: ["id", "descricao_produto", "marca", "ncm", "codigo_barras_unidade", "usa_wms", "temrepos", "qttotpal", "lastropal", "alturapal", "modulo", "rua", "numero", "apto", "modulocx", "ruacx", "numerocx", "aptocx", "tipoalturapalete", "validacao"],
            outros: ["id", "descricao_produto", "marca", "ncm", "codigo_barras_unidade", "enviarinftecnicanfe", "sugvenda", "coddistrib", "usaecommerceunilever", "conferenocheckout", "tipomerc", "aceitavendafracao", "enviarforcavendas", "naturezaproduto", "cestabasicalegis", "validacao"],
        };

        function carregarTabela(abaSelecionada) {
            const tableHeader = $("#tableHeader");
            tableHeader.empty().append('<th><div class="form-check"><input type="checkbox" class="form-check-input" id="selectAll"><label class="form-check-label" for="selectAll"></label></div></th>');

            if (!(abaSelecionada in camposAba)) return;

            const camposSelecionados = camposAba[abaSelecionada];
            const columns = [{
                data: null,
                render: data => `<div class="form-check"><input type="checkbox" class="form-check-input select-item" value="${data.id}"></div>`
            }];

            camposSelecionados.forEach(campo => {
                tableHeader.append($("<th></th>").text(campo.charAt(0).toUpperCase() + campo.slice(1).replace(/_/g, " ")));
                columns.push({
                    data: campo
                });
            });

            const cnpj = $("#cnpj").val();
            const fields = camposSelecionados.join(",");

            $("#productTable").DataTable({
                destroy: true,
                ajax: {
                    url: "./api.php",
                    type: "GET",
                    data: {
                        endpoint: "produtos",
                        cnpj,
                        fields
                    },
                    dataSrc: json => json,
                },
                columns: columns,
            });

            $("#selectAll").on("change", function() {
                $(".select-item").prop("checked", $(this).is(":checked"));
                $("#selectedCount").text($(".select-item:checked").length);
            });

            $(document).on("change", ".select-item", function() {
                $("#selectedCount").text($(".select-item:checked").length);
            });
        }

        $(document).ready(function() {
            const successToast = new bootstrap.Toast(document.getElementById("successToast"));
            const errorToast = new bootstrap.Toast(document.getElementById("errorToast"));

            $("#toggleForm").on("click", function() {
                $(".formFiltro").slideToggle();
            });

            $("#clearForm").on("click", function() {
                $(".formFiltro")[0].reset();
                $("#cnpj").val("");
                carregarTabela("embalagem");
            });

            $("#saveProducts").on("click", function(e) {
                e.preventDefault();
                const formData = $(".tab-pane.active .form-control").serializeArray().reduce((obj, item) => {
                    obj[item.name] = item.value.trim();
                    return obj;
                }, {});

                let hasEmptyFields = false;
                $(".tab-pane.active .form-control").each(function() {
                    if ($(this).val().trim() === "") {
                        hasEmptyFields = true;
                        $(this).addClass("is-invalid");
                    } else {
                        $(this).removeClass("is-invalid");
                    }
                });

                if (hasEmptyFields) return;

                const selectedIds = $(".select-item:checked").map(function() {
                    return $(this).val();
                }).get();
                const payload = {
                    ids: selectedIds,
                    ...formData
                };

                $.ajax({
                    url: "./api.php?endpoint=atualizar_produto",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(payload),
                    success: function() {
                        successToast.show();
                        $("#productTable").DataTable().ajax.reload();
                    },
                    error: function() {
                        errorToast.show();
                    },
                });
            });

            $(".aba").on("click", function() {

                if (!$("#cnpj").val()) {
                    // Atualiza o texto do toast
                    $("#errorToast .toast-body").text('É obrigatório escolher um fornecedor!');

                    const errorToast = new bootstrap.Toast(document.getElementById("errorToast"));
                    errorToast.show(); // Exibe o toast de erro
                    return; // Não continua se o CNPJ não estiver selecionado
                }

                const aba = $(this).data("aba");
                $("#productTable").DataTable().clear().destroy();
                carregarTabela(aba);
                $('.table-responsive').show();
            });

            $("#cnpj").on("change", function() {

                if (!$("#cnpj").val()) {
                    // Atualiza o texto do toast
                    $("#errorToast .toast-body").text('É obrigatório escolher um fornecedor!');

                    const errorToast = new bootstrap.Toast(document.getElementById("errorToast"));
                    errorToast.show(); // Exibe o toast de erro
                    return; // Não continua se o CNPJ não estiver selecionado
                }


                const cnpjValue = $(this).val();
                const aba = $(".aba.active").data("aba") || "embalagem";
                const productTable = $("#productTable").DataTable();

                if (cnpjValue) {
                    productTable.ajax.url(`./api.php?endpoint=produtos&cnpj=${cnpjValue}&aba=${aba}`).load();
                } else {
                    //productTable.ajax.url(`./api.php?endpoint=produtos&aba=${aba}`).load();
                }
                carregarTabela(aba);
                $('.table-responsive').show();
            });


            $('.table-responsive').hide();

            $.ajax({
                url: "./api.php?endpoint=fornecedores",
                method: "GET",
                success: function(data) {
                    const cnpjSelect = $("#cnpj");
                    cnpjSelect.empty().append(`<option selected></option>`);
                    data.forEach(fornecedor => {
                        cnpjSelect.append(`<option value="${fornecedor.cnpj}">${fornecedor.razao_social}</option>`);
                    });
                }
            });

            carregarTabela("embalagem");
        });
    </script>




</head>
<!--  -->

<body class="bg-dark text-light">
    <div class="toast-container position-fixed p-3 top-0 end-0">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Produtos atualizados com sucesso!</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Erro ao atualizar os produtos. Tente novamente.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Gerenciamento de Produtos</h2>
                    <div>
                        <button class="btn btn-outline-secondary me-2" id="clearForm" aria-label="Limpar configurações">Limpar Configurações</button>
                        <button class="btn btn-primary" id="toggleForm" aria-label="Mostrar ou Ocultar configurações">Mostrar/Ocultar Configurações</button>
                    </div>
                </div>

                <div class="d-flex">
                    <div class="col-2 pe-3">
                        <h5>Selecione um fornecedor</h5>
                        <select id="cnpj" class="form-control form-control-sm" size="10"></select>
                    </div>

                    <div class="col-10">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link aba active" id="embalagem-tab" data-bs-toggle="tab" href="#embalagem" role="tab" aria-controls="embalagem" aria-selected="true" data-aba="embalagem">Embalagem</a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link aba" id="classificacao-tab" data-bs-toggle="tab" href="#classificacao" role="tab" aria-controls="classificacao" aria-selected="false" data-aba="classificacao">Classificação</a>
                            </li>
                        </ul>
                        <?php include 'form_abas.php'; ?>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <button class="btn btn-primary btn-lg mb-3" id="saveProducts">
                        Salvar Produtos Selecionados
                        <span class="badge bg-light text-primary ms-2" id="selectedCount">0</span>
                    </button>
                    <table id="productTable" class="table table-striped">
                        <thead>
                            <tr id="tableHeader">
                                <th>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll" aria-label="Selecionar todos">
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dados preenchidos via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>




    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>





    <style>
        ::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }

        ::-webkit-scrollbar-thumb {
            background-color: #494846FF;
            border-radius: 10px;
            border: 2px solid #969695FF;
        }

        ::-webkit-scrollbar-track {
            background-color: #2c2c2c;
            border-radius: 10px;
        }

        .form-section {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .form-section h5 {
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .collapse-toggle {
            cursor: pointer;
        }

        .form-floating {
            margin-bottom: 15px;
        }

        .btn-save {
            position: sticky;
            bottom: 20px;
            z-index: 100;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }


        td,
        body {
            font-size: 0.85rem !important;
        }

        th {
            padding: 6px !important;
            text-transform: uppercase;
            font-size: 0.7rem !important;
        }

        td {
            padding: 1px !important;

        }

        body {
            padding-left: 5%;
            padding-right: 5%;
        }

        .dataTable tbody tr td:nth-child(-n+6) {
            background-color: #C7E6C9FF;
            color: #000;
        }
    </style>