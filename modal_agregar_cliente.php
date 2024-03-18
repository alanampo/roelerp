<div id="ModalAgregarCliente" class="modal" data-keyboard="false" data-backdrop="static">
    <div class="modal-content2">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <h3 id='titulo' class='box-title'>Agregar Cliente</h3>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Nombre:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#domiciliocliente_txt').focus();return false;">
                            <input type="search" autocomplete="off" id="nombrecliente_txt"
                                style="text-transform:uppercase" maxlength="40" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Domicilio:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#select-comuna2').focus();return false;">
                            <input type="search" maxlength="50" autocomplete="off" id="domiciliocliente_txt"
                                style="text-transform:uppercase" class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Comuna:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#telcliente_txt').focus();return false;">
                            <select id="select-comuna2" class="selectpicker" title="Comuna"
                                    data-style="btn-info" data-dropup-auto="false" data-live-search="true"
                                    data-width="100%" data-size="5" ></select>
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Teléfono:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#rutcliente_txt').focus();return false;">
                            <input type="search" maxlength="30" autocomplete="off" id="telcliente_txt" style="text-transform:uppercase"
                                class="form-control">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">R.U.T:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#razonsocial_txt').focus();return false;">
                            <input type="search" maxlength="25" autocomplete="off" id="rutcliente_txt" style="text-transform:uppercase"
                                class="form-control" onpaste="return false">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">Razón Social: <button class="btn btn-sm btn-secondary" onclick="setRazonSocial()">=</button></label>
                    </div>
                    <div>
                        <form action="#" onsubmit="$('#mailcliente_txt').focus();return false;">
                            <input type="search" maxlength="80" autocomplete="off" id="razonsocial_txt" style="text-transform:uppercase"
                                class="form-control" onpaste="return false">
                        </form>
                    </div>
                </div>
                <div class='form-group'>
                    <div>
                        <label class="control-label">E-Mail:</label>
                    </div>
                    <div>
                        <form action="#" onsubmit="return false;">
                            <input maxlength="50" type="search" autocomplete="off" id="mailcliente_txt"
                                style="text-transform:lowercase !important" class="form-control">
                        </form>
                    </div>
                </div>

            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn fa fa-close" style="font-size: 2em"
                    onClick="$('#ModalAgregarCliente').modal('hide');"></button>
                <button type="button" class="btn fa fa-save ml-3" style="font-size: 2em" id="btn_guardarcliente"
                    onClick="GuardarCliente();"></button>
            </div>
        </div>
    </div>
</div> <!-- TERMINA MODAL CLIENTE-->