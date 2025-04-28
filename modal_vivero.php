<div id="modal-vivero" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Vivero</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="control-label">Nombre:</label>
                        <input type="search" autocomplete="off" id="input-nombre" style="text-transform: capitalize"
                            maxlength="80" class="form-control" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="control-label">Dirección:</label>
                        <input type="search" autocomplete="off" id="input-domicilio" style="text-transform: capitalize"
                            maxlength="120" class="form-control" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="control-label">Comuna/Localidad:</label>
                        <input type="search" autocomplete="off" id="input-comuna" style="text-transform: capitalize"
                            maxlength="120" class="form-control" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="control-label">Teléfono:</label>
                        <input type="search" autocomplete="off" id="input-telefono" style="text-transform: lowercase"
                            maxlength="60" class="form-control" />
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="control-label">Email:</label>
                        <input type="search" autocomplete="off" id="input-email" style="text-transform: lowercase"
                            maxlength="80" class="form-control" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="control-label">R.U.T:</label>
                        <input type="search" autocomplete="off" id="input-rut" style="text-transform: lowercase"
                            maxlength="20" class="form-control" />
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" onclick="guardarVivero()" class="btn btn-primary">GUARDAR</button>
            </div>
        </div>
    </div>
</div>