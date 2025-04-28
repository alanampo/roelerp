<div id="modal-producto" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <div class="row">
                        <div class="col form-group">
                            <label class="control-label">Tipo:</label>
                            <select id="select_tipo2" class="selectpicker w-100" title="Tipo de Producto/Servicio"
                                data-style="btn-info" data-live-search="true" data-width="100%"></select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col form-group">
                            <label class="control-label">Nombre del Producto:</label>
                            <input type="search" autocomplete="off" id="input-nombre-producto" maxLength="50"
                                style="text-transform: uppercase" class="form-control"
                                placeholder="Ingresa el Nombre" />
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col form-group">
                            <label class="control-label">Código/ID:
                                <span class="label-codigo text-primary font-weight-bold"></span></label>
                            <input type="search" autocomplete="off" id="input-codigo" maxLength="6"
                                style="text-transform: uppercase" class="form-control" placeholder="SOLO NÚMEROS" />
                        </div>
                    </div>
                
                
                <div class="row mt-2 mb-2">
                    <div class="col">
                        <label class="control-label">Atributos:</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="table-responsive">
                        <table id="table-atributos"
                            class="table table-bordered table-light w-100">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th style="max-width: 60% !important" scope="col">Valor</th>
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
                <button type="button" class="btn btn-primary" onClick="guardarProducto();">GUARDAR</button>
            </div>
        </div>
    </div>
</div>