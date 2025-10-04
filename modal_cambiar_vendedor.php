<div id="modal-cambiar-vendedor" class="modal">
    <div class="modal-content2">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <h3 class='box-title'>Cambiar Vendedor Asignado</h3>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Cliente:</label>
                    </div>
                    <div>
                        <input type="text" id="cambio-nombre-cliente" class="form-control" disabled>
                        <input type="hidden" id="cambio-id-cliente">
                        <input type="hidden" id="cambio-id-vendedor-actual">
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Vendedor Actual:</label>
                    </div>
                    <div>
                        <input type="text" id="cambio-vendedor-actual" class="form-control" disabled>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Nuevo Vendedor:</label>
                    </div>
                    <div>
                        <select id="select-nuevo-vendedor" class="selectpicker" title="Seleccionar Vendedor"
                            data-style="btn-info" data-live-search="true" data-width="100%"></select>
                    </div>
                </div>
                <div class='form-group' id="grupo-justificacion">
                    <div>
                        <label class="control-label">Justificación del Cambio: <span id="asterisco-requerido" style="color:red">*</span></label>
                    </div>
                    <div>
                        <textarea id="justificacion-cambio" class="form-control" rows="4"
                            placeholder="Explique el motivo del cambio de vendedor (ej: vendedor actual no ha respondido, cliente solicita cambio, etc.)"
                            maxlength="500"></textarea>
                        <small class="text-muted">Máximo 500 caracteres. <span id="texto-opcional" style="display:none;">(Opcional para primer asignación)</span></small>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn fa fa-close" style="font-size: 2em"
                    onClick="$('#modal-cambiar-vendedor').modal('hide');"></button>
                <button type="button" class="btn fa fa-save ml-3" style="font-size: 2em"
                    onClick="guardarCambioVendedor();"></button>
            </div>
        </div>
    </div>
</div> <!-- MODAL CAMBIAR VENDEDOR FIN -->
