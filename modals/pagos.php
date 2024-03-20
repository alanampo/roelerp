<div id="modal-pago" class="modal" data-keyboard="false" data-backdrop="static">
    <div class="modalpago-content">
        <div class="modal-header">
            <h5 class="modal-title num-factura">Agregar Pago</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="form-row mt-2">
            <div class="col-md-4 form-group">
                <label class="col-form-label" for="input-monto">Monto:</label>
                <input type="search" autocomplete="off" class="form-control" name="input-monto" id="input-monto"
                    maxlength="14" />
            </div>
            <div class="col-md-8 form-group">
                <label class="col-form-label" for="input-comentario-pago">Comentario:</label>
                <input type="search" autocomplete="off" class="form-control" name="input-comentario-pago"
                    id="input-comentario-pago" maxlength="50" />
            </div>
        </div>

        <div class="row">
            <div class="col">
                <button id="btn-guardar-pago" onclick="guardarPago()" class="btn btn-success pull-right"><i
                        class="fa fa-save"></i> GUARDAR</button>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <table id="tabla-pagos" class="table w-100 d-flex d-md-table table-responsive" role="grid">
                    <thead class="thead-dark">
                        <tr role="row">
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Comentario</th>
                            <th class="text-center">Monto ($)</th>
                            <th class="text-center" style="width: 50px"></th>
                            <th class="text-center" style="width: 50px"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col">
                <table id="tabla-montos" class="table w-100 d-flex d-md-table table-responsive" role="grid">
                    <thead class="thead-light">
                        <tr role="row">
                            <th class="text-center">Total Factura</th>
                            <th class="text-center">Suma Pagos</th>
                            <th class="text-center">Debe</th>

                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>