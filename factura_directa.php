<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Factura Directa
    </title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="plugins/moment/moment.min.js"></script>
    <script src="js/numeroAlertas.js"></script>
    <script src="dist/js/factura_directa.js?v=<?php echo $version ?>"></script>
    <link rel="stylesheet" href="./css/loading.css">
    <style>
        input {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div id="ocultar">
        <div class="wrapper">
            <header class="main-header">
                <?php include('class_lib/nav_header.php'); ?>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <?php
                include('class_lib/sidebar.php');
                $dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
                $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
                $fecha = $dias[date('w')] . " " . date('d') . " de " . $meses[date('n') - 1] . " del " . date('Y');
                ?>
                <!-- /.sidebar -->
            </aside>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <section class="content-header">
                    <h1>
                        Factura Directa
                        <small>
                            <?php echo $fecha; ?>
                        </small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Factura Directa</li>
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">

                    <div class='row'>
                        <div class="col-12 col-md-4">
                            <select id="select_cliente" class="selectpicker" title="Selecciona un Cliente"
                                data-style="btn-info" data-dropup-auto="false" data-live-search="true" data-width="100%"
                                data-size="10" data-container="body"></select>
                        </div>
                        <div class="col-12 col-md-3 text-lg-right text-center mt-3 mt-lg-0 mb-3 mb-lg-0">
                            <button class="btn btn-success btn-block" onclick="modalAgregarProducto();"><i
                                    class="fa fa-plus-square"></i>
                                AGREGAR PRODUCTO</button>
                        </div>
                        <div class="col-md-5 text-right">


                            <button onClick="setEditing(false)" class="btn btn-danger p-2 font-weight-bold"> <i
                                    class="fa fa-close"></i> </button>

                            <button id="btn_guardarpedido" class="btn btn-primary font-weight-bold p-2 ml-3"
                                onclick="vistaPrevia();"><i class="fa fa-check"></i> VISTA PREVIA</button>


                        </div>

                    </div> <!-- FIN ROW -->


                    <div class="row mt-2">
                        <div class="col-md-4 form-group">
                            <label class="col-form-label" for="input-rut">R.U.T:</label>
                            <input type="search" autocomplete="off" class="form-control w-100 lg-w-75" name="input-rut"
                                id="input-rut" maxlength="20" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="col-form-label" for="input-razon">Razón Social: <button
                                    class="btn btn-sm btn-secondary pb-0 pt-0"
                                    onclick="setRazonSocial2()">=</button></label>
                            <input type="search" autocomplete="off" class="form-control w-100 lg-w-75"
                                name="input-razon" id="input-razon" maxlength="50" />
                        </div>

                        <div class="col-md-4 form-group">
                            <label class="col-md-12 col-form-label pl-0" for="select-condicion">Condición de
                                Pago:</label>
                            <select id="select-condicion" class="selectpicker" data-dropup-auto="false"
                                title="Selecciona" data-style="btn-info">
                                <option value="0">CONTADO</option>
                                <option value="1">CREDITO 30 DÍAS</option>
                                <option value="2">CREDITO 60 DÍAS</option>
                                <option value="3">CREDITO 90 DÍAS</option>
                            </select>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="col-form-label" for="input-domicilio">Domicilio:</label>
                            <input type="search" autocomplete="off" class="form-control w-100 lg-w-75"
                                name="input-domicilio" id="input-domicilio" maxlength="50" />
                        </div>
                        <div class="col-md-4 form-group" id="container-comuna">
                            <label class="col-form-label" for="select-comuna">Comuna:</label>
                            <select id="select-comuna" class="selectpicker w-100 lg-w-75" title="Comuna"
                                data-style="btn-info" data-dropup-auto="false" data-live-search="true" data-width="100%"
                                data-container="container-comuna" data-size="5"></select>
                        </div>


                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label class="col-form-label" for="input-giro">Giro:</label>
                            <input type="text" class="form-control w-100 lg-w-75" name="input-giro" id="input-giro"
                                maxlength="80" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="col-form-label" for="input-comentario">Comentario:</label>
                            <textarea autocomplete="off" class="form-control w-100 lg-w-75" name="input-comentario"
                                id="input-comentario" maxlength="400"></textarea>
                        </div>


                    </div>

                    <div class="row mt-2">
                        <div class="col-md-2">
                            <button id="btn-guardar-cambios" onclick="guardarCambiosCliente()"
                                class="btn btn-success d-none"><i class="fa fa-save"></i> GUARDAR CAMBIOS
                                CLIENTE</button>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            <table id="table-pedido"
                                class="table table-bordered table-light  table-responsive w-100 d-block d-md-table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th scope="col">Item</th>
                                        <th scope="col">Código</th>
                                        <th scope="col" style="width: 35%;">Producto</th>
                                        <th scope="col">Cantidad</th>
                                        <th scope="col">P. Unitario</th>
                                        <th scope="col">Subtotal</th>
                                        <th scope="col">Descuento</th>
                                        <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="pedido-vacio-msg">
                                        <th scope="row" colspan="8" class="text-center"><span class="text-muted">
                                                Agrega Productos a la Factura</span>
                                        </th>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </section><!-- /.content -->
            </div>

        </div>


        <?php

        include('./class_lib/main_footer.php');

        ?>

        <?php include("modal_agregar_cotizacion.php") ?>


        <div id="modal-vistaprevia" class="modal" data-keyboard="false" data-backdrop="static">
            <div class="modal-vistaprevia">
                <div class="loader-anular">
                    <div class="load-3 mt-4 ml-4">
                        <div class="line"></div>
                        <div class="line"></div>
                        <div class="line"></div>
                    </div>
                </div>
                <div class="modal-vistaprevia-wrapper">
                    <div class='box box-primary mb-0'>
                        <div class="modal-header d-block">
                            <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>

                            <div class="row row-select-folio">

                            </div>
                        </div>
                    </div>
                    <div class='box-body'>
                        <div class="print-cotizacion"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="control-sidebar-bg"></div>

    </div>
    <!--ocultar-->
</body>

</html>