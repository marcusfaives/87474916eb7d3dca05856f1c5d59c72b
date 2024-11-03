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
            embalagem: [
                "id",
                "descricao_produto",
                "marca",
                "ncm",
                "codigo_barras_unidade",
                "embalagemmaster",
                "embalagem",
                "unidademaster",
                "qtunitcx",
                "unidade",
                "qtunit",
                "pesoliq",
                "pesobruto",
                "gtincodauxiliar",
                "gtincodauxiliartrib",
                "gtincodauxiliar2",
                "validacao",
            ],
            classificacao: [
                "id",
                "descricao_produto",
                "marca",
                "ncm",
                "codigo_barras_unidade",
                "codepto",
                "codsec",
                "codcategoria",
                "codsubcategoria",
                "codmarca",
                "validacao",
            ],
            logistica: [
                "id",
                "descricao_produto",
                "marca",
                "ncm",
                "codigo_barras_unidade",
                "usa_wms",
                "temrepos",
                "qttotpal",
                "lastropal",
                "alturapal",
                "modulo",
                "rua",
                "numero",
                "apto",
                "modulocx",
                "ruacx",
                "numerocx",
                "aptocx",
                "tipoalturapalete",
                "validacao",
            ],
            outros: [
                "id",
                "descricao_produto",
                "marca",
                "ncm",
                "codigo_barras_unidade",
                "enviarinftecnicanfe",
                "sugvenda",
                "coddistrib",
                "usaecommerceunilever",
                "conferenocheckout",
                "tipomerc",
                "aceitavendafracao",
                "enviarforcavendas",
                "naturezaproduto",
                "cestabasicalegis",
                "validacao",
            ],
        };

        function carregarTabela(abaSelecionada) {
            console.log('Tentando.. ' + abaSelecionada);
            const tableHeader = $("#tableHeader");
            tableHeader.empty();
            tableHeader.append(
                '<th><div class="form-check"><input type="checkbox" class="form-check-input" id="selectAll"><label class="form-check-label" for="selectAll"></label></div></th>'
            );

            if (!(abaSelecionada in camposAba)) {
                console.error(`Aba "${abaSelecionada}" não encontrada.`);
                return;
            }

            const camposSelecionados = camposAba[abaSelecionada];
            const columns = [{
                data: null,
                render: function(data) {
                    return (
                        '<div class="form-check"><input type="checkbox" class="form-check-input select-item" value="' +
                        data.id +
                        '"></div>'
                    );
                },
            }];

            camposSelecionados.forEach((campo) => {
                const th = $("<th></th>").text(
                    campo.charAt(0).toUpperCase() + campo.slice(1).replace(/_/g, " ")
                );
                tableHeader.append(th);
                columns.push({
                    data: campo
                });
            });

            const cnpj = $("#cnpj").val();
            const endpoint = "produtos";
            const fields = camposSelecionados.join(",");

            // Montar a URL completa
            const urlCompleta = `./api.php?endpoint=${endpoint}&cnpj=${cnpj}&fields=${fields}`;
            console.log("URL Completa: ", urlCompleta);

            const productTable = $("#productTable").DataTable({
                destroy: true,
                ajax: {
                    url: "./api.php",
                    type: "GET",
                    data: {
                        endpoint: endpoint,
                        cnpj: cnpj,
                        fields: fields,
                    },
                    dataSrc: function(json) {
                        console.log(json);
                        return json;
                    },
                },
                columns: columns,
            });

            function updateSelectedCount() {
                const selectedCount = $(".select-item:checked").length;
                $("#selectedCount").text(selectedCount);
            }

            $("#selectAll").on("change", function() {
                const isChecked = $(this).is(":checked");
                $(".select-item").prop("checked", isChecked);
                updateSelectedCount();
            });

            $(document).on("change", ".select-item", function() {
                updateSelectedCount();
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
                e.preventDefault(); // Impede o envio padrão do formulário

                const formData = $(".tab-pane.active .form-control").serializeArray().reduce(function(obj, item) {
                    obj[item.name] = item.value.trim();
                    return obj;
                }, {});

                let hasEmptyFields = false;

                // Verifica se há campos vazios na aba ativa
                $(".tab-pane.active .form-control").each(function() {
                    if ($(this).val().trim() === "") {
                        hasEmptyFields = true;
                        $(this).addClass("is-invalid"); // Adiciona uma classe para destacar o campo vazio
                    } else {
                        $(this).removeClass("is-invalid"); // Remove a classe se o campo não estiver vazio
                    }
                });

                if (hasEmptyFields) {
                    return; // Se houver campos vazios, não prossegue com o envio
                }

                const selectedIds = $(".select-item:checked").map(function() {
                    return $(this).val();
                }).get();

                const payload = {
                    ids: selectedIds,
                    ...formData,
                };

                $.ajax({
                    url: "./api.php?endpoint=atualizar_produto",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify(payload),
                    success: function(response) {
                        successToast.show();
                        $("#productTable").DataTable().ajax.reload();
                    },
                    error: function() {
                        errorToast.show();
                    },
                });
            });

            $(".aba").on("click", function() {
                const dataTable = $("#productTable").DataTable();
                dataTable.clear().draw();
                dataTable.destroy();
                carregarTabela($(this).data("aba"));
            });

            $('input').on('blur', function() {
                if ($(this).val() !== '') {
                    $(this).removeClass('r');
                }
            });

            carregarTabela("embalagem");

            $.ajax({
                url: "./api.php?endpoint=fornecedores",
                method: "GET",
                success: function(data) {
                    const cnpjSelect = $("#cnpj");
                    cnpjSelect.empty();
                    $.each(data, function(index, fornecedor) {
                        cnpjSelect.append(
                            `<option value="${fornecedor.cnpj}">${fornecedor.razao_social}</option>`
                        );
                    });
                },
            });

            $("#cnpj").on("change", function() {
                const cnpjValue = $(this).val();
                const productTable = $("#productTable").DataTable();
                if (cnpjValue) {
                    productTable.ajax.url(`./api.php?endpoint=produtos&cnpj=${cnpjValue}`).load();
                } else {
                    productTable.ajax.url("./api.php?endpoint=produtos").load();
                }
            });
        });
    </script>



</head>
<!--  -->

<body class="bg-dark text-light">

    <div class="toast-container">
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
                        <button class="btn btn-outline-secondary me-2" id="clearForm">Limpar Configurações</button>
                        <button class="btn btn-primary" id="toggleForm">Mostrar/Ocultar Configurações</button>
                    </div>
                </div>

                <div class="mb-3">
                    <h5>Selecione um fornecedor</h5>
                    <select id="cnpj" class="form-control form-control-sm mb-2"></select>

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link aba active" id="embalagem-tab" data-bs-toggle="tab" href="#embalagem" role="tab" aria-controls="embalagem" aria-selected="true" data-aba="embalagem">Embalagem</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link aba" id="classificacao-tab" data-bs-toggle="tab" href="#classificacao" role="tab" aria-controls="classificacao" aria-selected="false" data-aba="classificacao">Classificação</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link aba" id="logistica-tab" data-bs-toggle="tab" href="#logistica" role="tab" aria-controls="logistica" aria-selected="false" data-aba="logistica">Logística</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link aba" id="outros-tab" data-bs-toggle="tab" href="#outros" role="tab" aria-controls="outros" aria-selected="false" data-aba="outros">Outros</a>
                        </li>
                    </ul>




                    <?php include 'form_abas.php' ?>


                </div>

                <div class="table-responsive">
                    <button class="btn btn-primary btn-lg mb-3" id="saveProducts">
                        Salvar Produtos Selecionados
                        <span class="badge bg-light text-primary ms-2" id="selectedCount">0</span>
                    </button>

                    <table id="productTable" class="table table-striped">
                        <thead>
                            <tr id="tableHeader">
                                <th>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                        <label class="form-check-label" for="selectAll"></label>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dados serão preenchidos via DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg mb-3">Confirmar e Sincronizar selecionados</button>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>





    <style>
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