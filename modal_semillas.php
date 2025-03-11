<div id="modal-agregar-semillas" class="modal">
    <div class="modal-content">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <h3 class='box-title'>Agregar Semillas</h3>
            </div>
        </div>
        <div class='box-body'>
            <div class='form-group'>
                <div class="row">
                    <div class="col-md-6">
                        <label for="select-variedad" class="control-label">Variedad/Especie:</label>
                        <div class="row">
                            <div class="col-md-12">
                                <select id="select-variedad" title="Variedad/Especie" class="selectpicker"
                                    data-style="btn-info" data-width="100%" data-size="10" data-live-search="true">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="select-marca" class="control-label">Marca:</label>
                        <div class="row">
                            <div class="col-md-10">
                                <select id="select-marca" data-size="10" data-live-search="true" title="Marca"
                                    class="selectpicker" data-style="btn-info" data-width="100%">
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-info btn-block" onclick="modalAgregarMarca()"><i
                                        class="fa fa-plus-square"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <label for="select-proveedor" class="control-label">Proveedor:</label>
                        <div class="row">
                            <div class="col-md-10">
                                <select id="select-proveedor" data-size="10" data-live-search="true" title="Proveedor"
                                    class="selectpicker" data-style="btn-info" data-width="100%">
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-info btn-block" onclick="modalAgregarProveedor()"><i
                                        class="fa fa-plus-square"></i></button>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-6">
                        <label class="control-label">Cantidad:</label>
                        <input maxLength="12" onchange='setPrecio()' onkeyup='this.onchange();' onpaste='this.onchange();' oninput='this.onchange();' max="99999999" type="number" min="0" step="1" id="input-cantidad"
                            class="form-control text-right">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <label for="input-precio" class="control-label">Precio Sobre (CLP):</label>
                        <input type='search' autocomplete="off"  maxLength="16"
                            class="form-control" id="input-precio" onchange='setPrecio()' onkeyup='this.onchange();' onpaste='this.onchange();' oninput='this.onchange();'>
                    </div>
                    <div class="col-md-3">
                        <label for="input-total" class="control-label">Valor Total:</label>
                        <input type="text" id="input-total"
                            class="form-control text-center" readonly>
                    </div>
                    <div class="col-md-3">
                        <label for="input-total-imp" class="control-label">Total +IMP:</label>
                        <input type="text" id="input-total-imp"
                            class="form-control text-center" readonly>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <label for="input-fecha" class="control-label">Fecha:</label>
                        <input type='text' data-date-format='dd/mm/yy' value="DD/MM/YYYY"
                            class="datepicker form-control" id="input-fecha">
                    </div>
                    <div class="col-md-6">
                        <label for="input-porcentaje" class="control-label">% de Germinación:</label>
                        <input maxLength="3" max="100" type="number" min="0" step="1" id="input-porcentaje"
                            class="form-control text-right">
                    </div>
                </div>
            </div>
           
            <div class="form-group">
                <div class="row">
                    <div class="col-md-6">
                        <label for="input-codigo" class="control-label">Código / Identificador:</label>
                        <input maxLength="20" max="100" type="search" autocomplete="off" id="input-codigo"
                            class="form-control font-weight-bold">
                    </div>
                    <div class="col-md-6">
                        <label for="select-cliente" class="control-label">Cliente:</label>
                        <select id="select-cliente" data-size="10" data-live-search="true" title="Cliente"
                            class="selectpicker" data-style="btn-info" data-width="100%">
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <div class="d-flex flex-row justify-content-end">
                        <button type="button" class="btn btn-modal-bottom fa fa-close"
                            onClick="$('#modal-agregar-semillas').modal('hide')"></button>
                        <button type="button" class="btn btn-modal-bottom ml-2 fa fa-save"
                            onClick="guardarSemillas();"></button>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- MODAL FIN -->
</div>

<div id="modal-marca" class="modal">
    <div class="modal-verpagos" style="overflow-y: auto;">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <div class="col-md-10">
                    <h3 class='box-title'>Agregar Marca</h3>
                </div>
                <div class="col text-right">
                    <button type="button" class="btn fa fa-close btn-modal-top"
                        onClick="$('#modal-marca').modal('hide');loadMarcas()"></button>
                </div>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div class="row">
                        <div class="col">
                            <label for="input-nombre-marca" class="control-label">Nombre Marca:</label>
                            <div class="row">
                                <div class="col-md-8">
                                    <input id="input-nombre-marca" class="form-control" type="search" autocomplete="off"
                                        maxLength="50">
                                </div>
                                <div class="col-md-4">
                                    <button id="btn-guardar-marca" onclick="guardarMarca()"
                                        class="btn btn-success btn-block"><i class="fa fa-save"></i> GUARDAR</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col">
                            <table class="table tabla-marcas table-responsive w-100 d-block d-md-table">
                                <thead class="thead-dark">
                                    <tr class="text-center">
                                        <th style="width:70%">Otras Marcas</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal-proveedor" class="modal">
    <div class="modal-verpagos" style="overflow-y: auto;">
        <div class='box box-primary'>
            <div class='box-header with-border'>
                <div class="col-md-10">
                    <h3 class='box-title'>Agregar Proveedor</h3>
                </div>
                <div class="col text-right">
                    <button type="button" class="btn fa fa-close btn-modal-top"
                        onClick="$('#modal-proveedor').modal('hide');loadProveedores()"></button>
                </div>
            </div>
            <div class='box-body'>
                <div class='form-group'>
                    <div class="row">
                        <div class="col">
                            <label for="input-nombre-proveedor" class="control-label">Nombre Proveedor:</label>
                            <div class="row">
                                <div class="col-md-8">
                                    <input id="input-nombre-proveedor" class="form-control" type="search"
                                        autocomplete="off" maxLength="50">
                                </div>
                                <div class="col-md-4">
                                    <button id="btn-guardar-proveedor" onclick="guardarProveedor()"
                                        class="btn btn-success btn-block"><i class="fa fa-save"></i> GUARDAR</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col">
                            <table class="table tabla-proveedores table-responsive w-100 d-block d-md-table">
                                <thead class="thead-dark">
                                    <tr class="text-center">
                                        <th style="width:70%">Otros Proveedores</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>