<div id="modal-precio" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Modificar Precio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col">
                        <label for="input-editar-precio" class="control-label">Precio Detalle:</label>
                        <input type="search" autocomplete="off" maxlength="9" id="input-editar-precio"
                            class="form-control text-center" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col">
                        <label for="input-editar-precio" class="control-label">Precio Mayorista:</label>
                        <input type="search" autocomplete="off" maxlength="9" id="input-editar-precio-mayorista"
                            class="form-control text-center" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" onClick="guardarPrecio();" class="btn btn-primary">GUARDAR</button>
            </div>
        </div>
    </div>
</div>