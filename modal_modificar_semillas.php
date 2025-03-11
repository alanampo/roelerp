<div id="modal-modificar-semillas" class="modal">
    <div class="modalpago-content">
        <div class='box box-primary'>
            <div class='box-header with-border'>
               <h3 class='box-title'>Agregar Semillas</h3>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div class='row'>
                        <div class='col'>
                            <label for="select_semillas_modificar" class="control-label">Semillas:</label>
                            <select id="select_semillas_modificar" title="Selecciona Semillas" class="selectpicker" data-style="btn-info"
                    data-live-search="true" data-width="100%"></select>
                        </div>
                    </div>
                </div>
                <div class='form-group'>
                    <div class='row'>
                        <div class='col'>
                            <label for="input_cantidad_modificar" class="control-label">Cantidad:</label>
                            <input class="form-control" type="search" autocomplete="off" id="input_cantidad_modificar" maxlength="10">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="d-flex flex-row justify-content-end">
                            <button type="button" class="btn btn-modal-bottom fa fa-close"
                                onClick="$('#modal-modificar-semillas').modal('hide')"></button>
                            <button type="button" class="btn btn-modal-bottom ml-2 fa fa-save"
                                onClick="asignarSemillas();"></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- MODAL MODIFICAR SEMILLAS FIN -->