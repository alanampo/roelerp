<div id="modal-historial-vendedor" class="modal">
    <div class="modal-content2" style="max-width: 800px;">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <h3 class='box-title'>Historial de Cambios de Vendedor</h3>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <label class="control-label">Cliente:</label>
                    <input type="text" id="historial-nombre-cliente" class="form-control" disabled>
                </div>
                <div id="contenido-historial">
                    <p class="text-center">Cargando historial...</p>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn fa fa-close" style="font-size: 2em"
                    onClick="$('#modal-historial-vendedor').modal('hide');"></button>
            </div>
        </div>
    </div>
</div>
