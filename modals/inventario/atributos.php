<div id="modal-atributos" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Atributos</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="control-label">Agregar:</label>
                        <div class="d-flex flex-row">
                            <input type="search" autocomplete="off" placeholder="Nombre" class="form-control"
                                id="input-nombre-atributo" style="text-transform: uppercase" maxlength="20" />
                            <button class="ml-2 btn btn-success btn-sm ml-2 mr-2 btn-guardar"
                                onclick="guardarTipoAtributo()">
                                <i class="fa fa-save"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col">
                        <div class="table-responsive">
                            <table id="table-crud" class="table w-100" role="grid">
                                <thead class="thead-dark">
                                    <tr role="row">
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">Valores</th>
                                        <th style="width:50px"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>