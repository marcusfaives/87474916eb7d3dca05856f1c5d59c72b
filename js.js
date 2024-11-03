const camposAba = {
  embalagem: [
    "id",
    "descricao_produto",
    "marca",
    "codigo_fabrica",
    "ncm",
    "codigo_barras_unidade",
    "cnpj",
    "embalagemmaster",
    "embalagem",
    "qtunitcx",
    "unidade",
    "qtunit",
    "pesoliq",
    "pesobruto",
  ],
  classificacao: [
    "id",
    "descricao_produto",
    "marca",
    "codigo_fabrica",
    "ncm",
    "codigo_barras_unidade",
    "cnpj",
    "codepto",
    "codsec",
    "codcategoria",
    "codsubcategoria",
    "unidademaster",
  ],
  gestaoControle: [
    "id",
    "descricao_produto",
    "marca",
    "codigo_fabrica",
    "ncm",
    "codigo_barras_unidade",
    "cnpj",
    "temrepos",
    "coddistrib",
  ],
  logistica: [
    "id",
    "descricao_produto",
    "marca",
    "codigo_fabrica",
    "ncm",
    "codigo_barras_unidade",
    "cnpj",
    "cestabasica",
    "enviarinftecnicanfe",
  ],
};

function carregarTabela(abaSelecionada) {
  const tableHeader = $("#tableHeader");
  tableHeader.empty();
  tableHeader.append(
    '<th><div class="form-check"><input type="checkbox" class="form-check-input" id="selectAll"><label class="form-check-label" for="selectAll"></label></div></th>'
  );

  // Verifica se a aba selecionada é válida
  if (!(abaSelecionada in camposAba)) {
    console.error(`Aba "${abaSelecionada}" não encontrada.`);
    return;
  }

  let camposSelecionados = camposAba[abaSelecionada];

  const columns = [
    {
      data: null,
      render: function (data) {
        return (
          '<div class="form-check"><input type="checkbox" class="form-check-input select-item" value="' +
          data.id +
          '"></div>'
        );
      },
    },
  ];

  // Adiciona cabeçalhos e colunas dinamicamente
  camposSelecionados.forEach((campo) => {
    const th = $("<th></th>").text(
      campo.charAt(0).toUpperCase() + campo.slice(1).replace(/_/g, " ")
    );
    tableHeader.append(th);
    columns.push({ data: campo });
  });

  const productTable = $("#productTable").DataTable({
    destroy: true,
    ajax: {
      url: "./api.php",
      type: "GET",
      data: {
        endpoint: "produtos",
        cnpj: $("#cnpj").val(),
        fields: camposSelecionados.join(","),
      },
      dataSrc: function (json) {
        console.log(json); // Verifique a resposta aqui
        return json; // Certifique-se de que está retornando um array
      },
    },
    columns: columns,
  });

  // Atualiza a contagem de produtos selecionados
  function updateSelectedCount() {
    const selectedCount = $(".select-item:checked").length;
    $("#selectedCount").text(selectedCount);
  }

  // Evento para selecionar/deselecionar todos os checkboxes
  $("#selectAll").on("change", function () {
    const isChecked = $(this).is(":checked");
    $(".select-item").prop("checked", isChecked);
    updateSelectedCount();
  });

  // Evento para contar checkboxes selecionados
  $(document).on("change", ".select-item", function () {
    updateSelectedCount();
  });
}

$(document).ready(function () {
  const successToast = new bootstrap.Toast(
    document.getElementById("successToast")
  );
  const errorToast = new bootstrap.Toast(document.getElementById("errorToast"));

  $("#toggleForm").on("click", function () {
    $("#formFiltro").slideToggle();
  });

  $("#clearForm").on("click", function () {
    $("#formFiltro")[0].reset();
    $("#cnpj").val("");
    carregarTabela();
  });

  $("#saveProducts").on("click", function () {
    const selectedIds = $(".select-item:checked")
      .map(function () {
        return $(this).val();
      })
      .get();

    const formData = $("#formFiltro")
      .serializeArray()
      .reduce(function (obj, item) {
        obj[item.name] = item.value.trim();
        return obj;
      }, {});

    const payload = {
      ids: selectedIds,
      ...formData,
    };

    $.ajax({
      url: "./api.php?endpoint=atualizar_produto",
      type: "POST",
      contentType: "application/json",
      data: JSON.stringify(payload),
      success: function (response) {
        successToast.show();
        $("#productTable").DataTable().ajax.reload();
      },
      error: function () {
        errorToast.show();
      },
    });
  });

  $(".aba").on("click", function () {
    const dataTable = $("#productTable").DataTable();
    dataTable.clear().draw();
    dataTable.destroy();
    carregarTabela($(this).data("aba"));
  });

  carregarTabela("embalagem");

  // Carregar fornecedores na página
  $.ajax({
    url: "./api.php?endpoint=fornecedores",
    method: "GET",
    success: function (data) {
      const cnpjSelect = $("#cnpj");
      cnpjSelect.empty(); // Limpa opções anteriores
      $.each(data, function (index, fornecedor) {
        cnpjSelect.append(
          `<option value="${fornecedor.cnpj}">${fornecedor.razao_social}</option>`
        );
      });
    },
  });

  // Atualiza a tabela com o CNPJ selecionado
  $("#cnpj").on("change", function () {
    const cnpjValue = $(this).val();
    if (cnpjValue) {
      table.ajax.url(`./api.php?endpoint=produtos&cnpj=${cnpjValue}`).load();
    } else {
      // Se nenhum CNPJ estiver selecionado, carregar todos os produtos
      table.ajax.url("./api.php?endpoint=produtos").load();
    }
  });

  carregarCNPJs();
});
