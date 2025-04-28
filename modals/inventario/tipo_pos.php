<div id="modal-tipo" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Tipo Producto/Servicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label class="control-label">Nombre Tipo de Producto/Servicio:</label>
                            <input type="search" autocomplete="off" id="input-nombre-tipo" maxLength="50"
                                style="text-transform: uppercase" class="form-control" placeholder="Ej: PLANTA" />
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="row">
                        <div class="col">
                            <label class="control-label">Código/Siglas:</label>
                            <input type="search" autocomplete="off" id="input-siglas" maxLength="3"
                                style="text-transform: uppercase" class="form-control" placeholder="Ej: PL" />
                        </div>
                    </div>
                </div>
                <div class="row mt-2 mb-2">
                    <div class="col">
                        <label class="control-label">Atributos:
                            <button onclick="modalAtributos()" class="btn btn-secondary btn-sm ml-2">
                                <i class="fa fa-plus-square"></i></button></label>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="table-responsive">
                            <table id="table-tipos-atributo" class="table table-bordered table-light w-100">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Tipo Dato</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarTipo()">GUARDAR</button>
            </div>
        </div>
    </div>
</div>