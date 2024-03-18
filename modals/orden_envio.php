<div id="modal-orden-envio" class="modal" data-keyboard="false" data-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar Órden de Envío</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="select-tipo-envio" class="control-label">Tipo Envío:</label>
                            <select id="select-tipo-envio" title="Selecciona" class="selectpicker"
                                data-style="btn-info" data-width="100%" data-size="10"
                                data-dropup-auto="false">
                            <option value="0">SUCURSAL</option>    
                            <option value="1">DOMICILIO CLIENTE</option>    
                        </select>
                    </div>
                    <div class="form-group col-md-8 col-select-transp d-none">
                        <label for="select-transportista" class="control-label">Transportista:</label>
                        <select id="select-transportista" title="Selecciona" class="selectpicker"
                                data-style="btn-info" data-live-search="true" data-width="100%" data-size="10"
                                data-dropup-auto="false"></select>
                    </div>
                    <div class="form-group col-md-8 col-direccion-envio d-none">
                        <label for="input-direccion-entrega" class="control-label">Dirección:</label>
                        <input type="search" autocomplete="off" class="form-control" name="input-direccion-entrega" id="input-direccion-entrega"
                            maxlength="120" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12 col-select-sucursal d-none">
                        <label for="select-sucursal" class="control-label">Sucursal:</label>
                        <select id="select-sucursal" title="Selecciona" class="selectpicker"
                                data-style="btn-info" data-container="body" data-live-search="true" data-width="100%" data-size="10"
                                data-dropup-auto="false"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col text-center">
                        <h6>Bultos:</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                      <div class="table-responsive">
                        <table id="table-bultos" class="table table-bordered table-light w-100">
                          <thead class="thead-dark text-center">
                            <tr>
                              <th scope="col">N°</th>
                              <th scope="col">Peso (kg)</th>
                              <th scope="col">Alto (cm)</th>
                              <th scope="col">Ancho (cm)</th>
                              <th scope="col">Largo (cm)</th>
                              <th scope="col" style="max-width: 50px;"></th>
                            </tr>
                          </thead>
                          <tbody></tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="input-notas-entrega" class="control-label">Observaciones:</label>
                        <input type="search" autocomplete="off" class="form-control" name="input-notas-entrega" id="input-notas-entrega"
                            maxlength="255" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" onclick="guardarOrdenEnvio()" class="btn btn-primary">IMPRIMIR</button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL SUCURSAL-->