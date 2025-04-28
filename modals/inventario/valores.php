<div class="modal" id="modal-editar-valores" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Valores</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="control-label">Nombre/Valor:</label>
                        <input type="search" autocomplete="off" placeholder="Nombre" class="form-control"
                            id="input-nombre-valor" style="text-transform: uppercase" maxlength="30" />
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="control-label">Precio Extra:</label>
                        <input type="search" autocomplete="off" placeholder="Precio Extra" class="form-control"
                            id="input-precio-valor" maxlength="9" />
                    </div>
                </div>
                <div class="row mt-2 mb-2">
                    <div class="col text-center">
                        <h6>Precios extra por Vivero:</h6>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="control-label">Vivero:</label>
                        <select id="select-vivero-valores" class="selectpicker" title="Vivero"
                  data-style="btn-info" data-width="100%" data-live-search="true"></select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="control-label">Precio Extra:</label>
                        <div class="d-flex flex-row">
                            <input type="search" autocomplete="off" placeholder="Precio Extra" class="form-control"
                            id="input-vivero-precio-valor" maxlength="9" />
                            <button class="btn btn-primary btn-sm ml-1" onclick="guardarPrecioVivero(this)"><i class="fa fa-save"></i></button>
                        </div>
                        
                    </div>
                </div>

                <div style="max-height: 250px;overflow-y: scroll;">
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table id="table-precios-extra" class="table table-bordered table-light w-100">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">Vivero</th>
                                            <th scope="col">Precio</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-2 mb-2">
                    <div class="col text-center">
                        <h6>Precios extra por Producto/Servicio y Vivero:</h6>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12 form-group">
                        <label class="control-label">Producto/Servicio:</label>
                        <select id="select-pys-valores" class="selectpicker" title="P/S"
                        data-style="btn-info" data-width="100%" data-live-search="true"></select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label class="control-label">Vivero:</label>
                        <select id="select-vivero-valores2" class="selectpicker" title="Vivero"
                        data-style="btn-info" data-width="100%" data-live-search="true"></select>
                    </div>
                    <div class="col-md-6 form-group">
                        <label class="control-label">Precio Extra:</label>
                        <div class="d-flex flex-row">
                            <input type="search" autocomplete="off" placeholder="Precio Extra" class="form-control"
                            id="input-vivero-precio-valor2" maxlength="9" />
                            <button class="btn btn-primary btn-sm ml-1" onclick="guardarPrecioViveroPyS(this)"><i class="fa fa-save"></i></button>
                        </div>
                        
                    </div>
                </div>

                <div style="max-height: 250px;overflow-y: scroll;">
                    <div class="row">
                        <div class="col">
                            <div class="table-responsive">
                                <table id="table-precios-extra-pys" class="table table-bordered table-light w-100">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">P/S</th>
                                            <th scope="col">Vivero</th>
                                            <th scope="col">Precio</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button onclick="editarValor()" type="button" class="btn btn-primary">GUARDAR</button>
            </div>
        </div>
    </div>
</div>