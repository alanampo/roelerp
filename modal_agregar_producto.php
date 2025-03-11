<!-- MODAL AGREGAR PRODUCTO -->
<div id="modal-agregar-producto" class="modal">
  <div class="modal-add-to-pedido">
    <div class='box box-primary mb-0'>
      <div class='box-header with-border'>
        <h3 class='box-title'>Agregar Producto al Pedido</h3>
      </div>
    </div>
    <div id="modalAgregarProducto" class='box-body'>
      <div class='form-group'>
        <div class="row">
          <div class="col-md-8">
            <label for="select_tipo" class="control-label">Tipo de Producto:</label>
            <select id="select_tipo" title="Selecciona Tipo" class="selectpicker" data-style="btn-info"
              data-live-search="true" data-width="100%"></select>
          </div>

        </div>
      </div>
      <div class='form-group'>
        <div class="row">
          <div class="col-md-8">
            <label for="select_variedad" class="control-label">Variedad:</label>
            <div>
              <div class="row">
                <div class="col-md-10">
                  <select id="select_variedad" title="Selecciona Variedad" class="selectpicker" data-style="btn-info"
                    data-live-search="true" data-width="100%"></select>
                </div>
                <div class="col-md-2">
                  <button class="btn btn-info btn-block" onclick="modalAgregarVariedad()"><i
                      class="fa fa-plus-square"></i></button>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <label for="select_bandeja" class="control-label">Bandeja:</label>
            <select id="select_bandeja" class="selectpicker" data-dropup-auto="false" title="Tipo Bandeja"
              data-style="btn-info" data-width="100%">
            </select>
          </div>
        </div>
      </div>

      <div class='form-group form-especie d-none'>
        <div class="row">
          <div class="col-md-8">
            <label for="select_especie" class="control-label">Especie provista por el Cliente:</label>
            <div class="">
              <div class="row">
                <div class="col-md-10">
                  <select id="select_especie" title="Selecciona Especie" class="selectpicker" data-style="btn-info"
                    data-live-search="true" data-width="100%"></select>
                </div>
                <div class="col-md-2">
                  <button class="btn btn-info btn-block" onclick="modalAgregarEspecie()"><i
                      class="fa fa-plus-square"></i></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class='form-group form-semillas d-none'>
        <div class="row">
          <div class="col-md-8">
            <label for="select_semillas" class="control-label">Semillas:</label>
            <div class="d-inline-block flex-row ml-5">
              <input onclick="loadSemillasPlantinera()" type="checkbox" class="form-check-input" id="check_plantinera">
              <label for="check_plantinera" class="ml-4">Mostrar Semillas de la Plantinera</label>
            </div>
            <div>
              <div class="row">
                <div class="col-md-10">
                  <select id="select_semillas" title="Selecciona Semillas" class="selectpicker" data-style="btn-info"
                    data-live-search="true" data-width="100%" multiple></select>
                </div>
                <div class="col-md-2">
                  <button class="btn btn-info btn-block" onclick="modalAgregarSemillas()"><i
                      class="fa fa-plus-square"></i></button>
                </div>
              </div>
            </div>
          </div>   
          <div class="col-md-4">
            <h5 class="text-danger text-center mt-4 font-weight-bold total-semillas d-none">Total Semillas: <span class="label-total-semillas">3000</span></h5>
          </div>       
        </div>
        <div class="row cantidad-semillas-container">
          
        </div>
      </div>

      <div class='form-group'>
        <div class="row">
          <div class="col-md-4">
            <label for="cantidad_plantas" class="control-label">Plantas:</label>
            <input style="font-weight: bold; font-size: 1.2em; color: black !important;" type="number" min="0"
              maxlength="9" step="1" id="cantidad_plantas" placeholder="Cantidad Plantas"
              class="form-control text-right">
          </div>
          <div class="col-md-4">
            <label for="cantidad_bandejas" class="control-label">Band. Nuevas:</label>
            <input style="font-weight: bold; font-size: 1.2em; color: black !important;" type="number" min="0"
              maxlength="9" step="1" id="cantidad_bandejas" placeholder="Bandejas NUEVAS"
              class="form-control text-right">
            <div class="w-100 text-center"><span class="label-nuevas d-none">STOCK DISPONIBLE: <span
                  class="label-stock-nuevas-cant"></span></span></div>
          </div>
          <div class="col-md-4">
            <label for="cantidad_bandejas_usadas" class="control-label">Band. Usadas:</label>
            <input style="font-weight: bold; font-size: 1.2em; color: black !important;" type="number" min="0"
              maxlength="9" step="1" id="cantidad_bandejas_usadas" placeholder="Bandejas USADAS"
              class="form-control text-right">
            <div class="w-100 text-center"><span class="label-usadas d-none">STOCK DISPONIBLE: <span
                  class="label-stock-usadas-cant"></span></span></div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <div class="row">
          <div class="col-md-4">
            <label class="control-label" for="fecha-ingreso-picker">Fecha Ingreso Solicitada:</label>
            <br>
            <input type='text' data-date-format='dd/mm/yy' value="DD/MM/YYYY" class="datepicker form-control"
              id="fecha-ingreso-picker" />
          </div>
          <div class="col-md-4">
            <label class="control-label" for="dias_produccion">Días de Producción</label>
            <div class="select-editable">
              <select class="form-control" id="select-dias"
                onchange="this.nextElementSibling.value=this.value;setFechaEntrega(this.value)">
                <option class="option" value="0">NO DEFINIDO</option>
                <option class="option" value="30">30 (1 mes)</option>
                <option class="option" value="60">60 (2 meses)</option>
                <option class="option" value="90">90 (3 meses)</option>
                <option class="option" value="120">120 (4 meses)</option>
                <option class="option" value="150">150 (5 meses)</option>
                <option class="option" value="180">180 (6 meses)</option>
              </select>
              <input input type="text" maxlength="3" class="form-control" id="dias_produccion" name="dias_produccion1"
                value="" type="text" onchange="setFechaEntrega(this.value)" onkeyup="this.onchange();"
                onpaste="this.onchange();" oninput="this.onchange();">
            </div>
          </div>
          <div class="col-md-4">
            <label class="control-label" for="fecha-entrega-picker">Fecha Entrega Aprox.:</label>
            <div class="input-group">
              <input type='text' data-date-format='dd/mm/yy' disabled="disabled" value="DD/MM/YYYY"
                class="datepicker form-control" id="fecha-entrega-picker" placeholder="DD-MM-AAAA" />
            </div>
          </div>
        </div>
      </div>

      <div class="form-group">
        <div class="row">



        </div>
      </div>
      <div class="row">
        <div class="col text-right">
          <button type="button" class="btn fa fa-close" style="font-size: 2em" id="btn_cancel"
            onClick="cerrarModal('modal-agregar-producto');"></button>
          <button type="button" class="btn fa fa-save" style="font-size: 2em;margin-left: 0.5em" id="btn_guardarcliente"
            onClick="addToPedido();"></button>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- MODAL FIN -->

<?php include("modal_semillas.php");?>