<div class="tab-content" id="pills-tabContent">
    <!-- Embalagem -->

    <div class="tab-pane fade show active" id="embalagem" role="tabpanel" aria-labelledby="embalagem-tab">
        <form class="formFiltro" method="POST" action="api.php" class="row g-3">
            <div class="row g-3 mt-3">
                <div class="col-md-1">
                    <label for="embalagemmaster" class="form-label">Embalagem Master</label>
                    <input required type="text" class="form-control" id="embalagemmaster" name="embalagemmaster" value="UN">
                </div>
                <div class="col-md-1">
                    <label for="embalagem" class="form-label">Embalagem</label>
                    <input value="UN" required type="text" class="form-control" id="embalagem" name="embalagem">
                </div>
                <div class="col-md-1">
                    <label for="unidademaster" class="form-label">Unidade Master</label>
                    <input value="UN" required type="text" class="form-control" id="unidademaster" name="unidademaster">
                </div>
                
                <div class="col-md-1">
                    <label for="qtunitcx" class="form-label">Qtde Unid Caixa</label>
                    <input value="1" required type="text" class="form-control" id="qtunitcx" name="qtunitcx">
                </div>
                <div class="col-md-1">
                    <label for="unidade" class="form-label">Unidade</label>
                    <input value="UN" required type="text" class="form-control" id="unidade" name="unidade">
                </div>
                <div class="col-md-1">
                    <label for="pesoliq" class="form-label">Peso Líquido</label>
                    <input value="" required type="number" class="form-control r" id="pesoliq" name="pesoliq">
                </div>
                <div class="col-md-1">
                    <label for="pesobruto" class="form-label">Peso Bruto</label>
                    <input value="" required type="number" class="form-control r" id="pesobruto" name="pesobruto">
                </div>
                <div class="col-md-1">
                    <label for="gtincodauxiliar" class="form-label">GTIN Código Auxiliar</label>
                    <input value="13" required type="text" class="form-control" id="gtincodauxiliar" name="gtincodauxiliar">
                </div>
                <div class="col-md-1">
                    <label for="gtincodauxiliartrib" class="form-label">GTIN Cod Auxiliar Trib</label>
                    <input value="13" required type="text" class="form-control" id="gtincodauxiliartrib" name="gtincodauxiliartrib">
                </div>
                <div class="col-md-1">
                    <label for="gtincodauxiliar2" class="form-label">GTIN Código Auxiliar 2</label>
                    <input value="13" required type="text" class="form-control" id="gtincodauxiliar2" name="gtincodauxiliar2">
                </div>
            </div>
        </form>
    </div>


    <!-- Classificação -->

    <div class="tab-pane fade" id="classificacao" role="tabpanel" aria-labelledby="classificacao-tab">
        <form class="formFiltro" method="POST" action="api.php" class="row g-3">
            <div class="row g-3 mt-3">
                <div class="col-md-1">
                    <label for="codepto" class="form-label">Código Departamento</label>
                    <input value="99" required type="text" class="form-control" id="codepto" name="codepto">
                </div>
                <div class="col-md-1">
                    <label for="codsec" class="form-label">Código Seção</label>
                    <input value="9999" required type="text" class="form-control" id="codsec" name="codsec">
                </div>
                <div class="col-md-1">
                    <label for="codcategoria" class="form-label">Código Categoria</label>
                    <input value="9999" required type="text" class="form-control" id="codcategoria" name="codcategoria">
                </div>
                <div class="col-md-1">
                    <label for="codsubcategoria" class="form-label">Código Subcategoria</label>
                    <input value="" required type="text" class="form-control r" id="codsubcategoria" name="codsubcategoria">
                </div>
                <div class="col-md-1">
                    <label for="codmarca" class="form-label">Código Marca</label>
                    <input value="" required type="text" class="form-control r" id="codmarca" name="codmarca">
                </div>
            </div>
        </form>
    </div>


    <!-- Logística -->

    <div class="tab-pane fade" id="logistica" role="tabpanel" aria-labelledby="logistica-tab">
        <form class="formFiltro" method="POST" action="api.php" class="row g-3">
            <div class="row g-3 mt-3">
                <div class="col-md-1">
                    <label for="usa_wms" class="form-label">Usa WMS</label>
                    <input value="N" required type="text" class="form-control" id="usa_wms" name="usa_wms">
                </div>
                <div class="col-md-1">
                    <label for="temrepos" class="form-label">Tem Repos</label>
                    <input value="N" required type="text" class="form-control" id="temrepos" name="temrepos">
                </div>
                <div class="col-md-1">
                    <label for="qttotpal" class="form-label">Qt. Total Paletes</label>
                    <input value="" required type="number" class="form-control r" id="qttotpal" name="qttotpal">
                </div>
                <div class="col-md-1">
                    <label for="lastropal" class="form-label">Lastro Palete</label>
                    <input value="" required type="number" class="form-control r" id="lastropal" name="lastropal">
                </div>
                <div class="col-md-1">
                    <label for="alturapal" class="form-label">Altura Palete</label>
                    <input value="" required type="number" class="form-control r" id="alturapal" name="alturapal">
                </div>
                <div class="col-md-1">
                    <label for="modulo" class="form-label">Módulo</label>
                    <input value="1" required type="text" class="form-control" id="modulo" name="modulo">
                </div>
                <div class="col-md-1">
                    <label for="rua" class="form-label">Rua</label>
                    <input value="1" required type="text" class="form-control" id="rua" name="rua">
                </div>
                <div class="col-md-1">
                    <label for="numero" class="form-label">Número</label>
                    <input value="1" required type="text" class="form-control" id="numero" name="numero">
                </div>
                <div class="col-md-1">
                    <label for="apto" class="form-label">Apto</label>
                    <input value="1" required type="text" class="form-control" id="apto" name="apto">
                </div>
                <div class="col-md-1">
                    <label for="modulocx" class="form-label">Módulo CX</label>
                    <input value="1" required type="text" class="form-control" id="modulocx" name="modulocx">
                </div>
                <div class="col-md-1">
                    <label for="ruacx" class="form-label">Rua CX</label>
                    <input value="1" required type="text" class="form-control" id="ruacx" name="ruacx">
                </div>
                <div class="col-md-1">
                    <label for="numerocx" class="form-label">Número CX</label>
                    <input value="1" required type="text" class="form-control" id="numerocx" name="numerocx">
                </div>
                <div class="col-md-1">
                    <label for="aptocx" class="form-label">Apto CX</label>
                    <input value="1" required type="text" class="form-control" id="aptocx" name="aptocx">
                </div>
                <div class="col-md-1">
                    <label for="tipoalturapalete" class="form-label">Tipo Altura Palete</label>
                    <input value="1" required type="text" class="form-control" id="tipoalturapalete" name="tipoalturapalete">
                </div>
            </div>
        </form>
    </div>


    <!-- Outros -->

    <div class="tab-pane fade" id="outros" role="tabpanel" aria-labelledby="outros-tab">
        <form class="formFiltro" method="POST" action="api.php" class="row g-3">
            <div class="row g-3 mt-3">
            <div class="col-md-1">
                    <label for="enviarinftecnicanfe" class="form-label">Enviar Inf Tec NFE</label>
                    <input value="N" required type="text" class="form-control" id="enviarinftecnicanfe" name="enviarinftecnicanfe">
                </div>
                <div class="col-md-1">
                    <label for="sugvenda" class="form-label">Sugestão de Venda</label>
                    <input required type="text" class="form-control r" id="sugvenda" name="sugvenda">
                </div>
                <div class="col-md-1">
                    <label for="coddistrib" class="form-label">Código Distribuição</label>
                    <input value="1" required type="text" class="form-control" id="coddistrib" name="coddistrib">
                </div>
                <div class="col-md-1">
                    <label for="usaecommerceunilever" class="form-label">Usa Eco Unilever</label>
                    <input value="N" required type="text" class="form-control" id="usaecommerceunilever" name="usaecommerceunilever">
                </div>
                <div class="col-md-1">
                    <label for="conferenocheckout" class="form-label">Confere No CheckOut</label>
                    <input value="N" required type="text" class="form-control" id="conferenocheckout" name="conferenocheckout">
                </div>
                <div class="col-md-1">
                    <label for="tipomerc" class="form-label">Tipo Mercadoria</label>
                    <input value="L" required type="text" class="form-control" id="tipomerc" name="tipomerc">
                </div>
                <div class="col-md-1">
                    <label for="aceitavendafracao" class="form-label">Aceita Venda Fração</label>
                    <input value="N" required type="text" class="form-control" id="aceitavendafracao" name="aceitavendafracao">
                </div>
                <div class="col-md-1">
                    <label for="enviarforcavendas" class="form-label">Enviar Força de Vendas</label>
                    <input value="N" required type="text" class="form-control" id="enviarforcavendas" name="enviarforcavendas">
                </div>
                <div class="col-md-1">
                    <label for="naturezaproduto" class="form-label">Natureza do Produto</label>
                    <input value="OT" required type="text" class="form-control" id="naturezaproduto" name="naturezaproduto">
                </div>                          
                <div class="col-md-1">
                    <label for="cestabasicalegis" class="form-label">Cesta Basica Legis</label>
                    <input value="N" required type="text" class="form-control" id="cestabasicalegis" name="cestabasicalegis">
                </div> 
            </div>
        </form>
    </div>

</div>
<style>
    .r {
        border: solid 2px red;
        background-color: #cfb4b4 !important;
    }
</style>