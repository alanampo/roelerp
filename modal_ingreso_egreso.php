<div id="modal-in-out" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
  <div class="modal-dialog modal-dialog-scrollable" style="max-width:900px">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Ingreso Producto/Servicio</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="form-row">
          <div class="form-group col-md-4">
            <label for="select-es-pos" class="control-label">Producto/Servicio:</label>
            <select id="select-es-pos" title="Selecciona" class="selectpicker" data-style="btn-info" data-width="100%"
              data-dropup-auto="false" onchange="setPoS(this.value)">
              <option value="producto">Producto</option>
              <option value="servicio">Servicio</option>
            </select>
          </div>
          <div class="form-group col-md-8">
            <label for="select-tipo-pos" class="control-label">Tipo:</label>
            <select id="select-tipo-pos" title="Selecciona Tipo" class="selectpicker" data-style="btn-info"
              data-live-search="true" data-width="100%" data-size="10" data-dropup-auto="false"></select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group col-md-12">
            <label for="select-pos" style="text-transform: capitalize;" class="control-label label-pos">Producto:</label>
  
            <select id="select-pos" title="Selecciona" class="selectpicker" data-style="btn-info" data-size="10"
              data-live-search="true" data-dropup-auto="false" data-width="100%"></select>
          </div>
        </div>
  
        <div class="row">
          <div class="col">
            <div class="table-responsive">
              <table id="table-atr-in-out" class="table table-bordered table-light w-100">
                <thead class="thead-dark text-center">
                  <tr>
                    <th scope="col">Vivero</th>
                    <th scope="col">Atributos</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Precio</th>
                    <th scope="col" style="max-width: 50px;"></th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
  
        <div class="form-row">
          <div class="form-group col">
            <label for="input-notas" class="control-label">Notas:</label>
            <input style="color: black !important" type="search" autocomplete="off" maxlength="120" id="input-notas"
              class="form-control" />
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" onClick="guardarInOut();" class="btn btn-primary">GUARDAR</button>
      </div>
    </div>
  </div>
</div>