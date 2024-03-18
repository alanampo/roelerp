<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Cotizaciones
    </title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <link rel="stylesheet" href="./css/loading.css" />
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="plugins/moment/moment.min.js"></script>
    <script src="js/numeroAlertas.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>
    <script src="plugins/html5-qrcode/html5-qrcode.min.js"></script>
    <script src="dist/js/common/agregar_cliente.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_cotizaciones.js?v=<?php echo $version ?>"></script>

    <script src="plugins/QRCode/qrcode.min.js"></script>
    <style>
        input {
            text-transform: uppercase;
        }
    </style>
</head>

<body>
    <div class="print-cotizacion" id="miVentana">
    </div>
    <div id="print-orden-envio" class="print-orden-envio" style="position: relative; top: 7px;">

    </div>
    <div id="ocultar">
        <div class="wrapper">
            <header class="main-header">
                <?php include('class_lib/nav_header.php');?>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <?php
                    include('class_lib/sidebar.php');
                    $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                    $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                    $fecha=$dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
                ?>
                <!-- /.sidebar -->
            </aside>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <section class="content-header">
                    <h1>
                        Cotizaciones
                        <small>
                            <?php echo $fecha; ?>
                        </small>
                        <button onclick="modalQR()" class="btn btn-primary btn-sm ml-2"><i class="fa fa-qrcode"></i>
                            ESCANEAR</button>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Cotizaciones</li>
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col">
                            <div class="tab">
                                <button id="defaultOpen" class="tablinks"
                                    onclick="abrirTab(event, 'historial');">HISTORIAL</button>
                                <button id="tabnueva" class="tablinks" onclick="abrirTab(event, 'nueva');"><span
                                        id="label-tab-cotizacion">NUEVA COTIZACIÓN</span></button>

                            </div>
                        </div>
                    </div>

                    <div class="tabco tab-historial d-none">
                        <div class='row mt-2 mb-5'>
                            <div class='col'>
                                <div id='tabla_entradas'></div>
                            </div>
                        </div>
                    </div>

                    <div class="tabco tab-nueva d-none">
                        <div class='row'>
                            <div class="col-10 col-md-4">
                                <select id="select_cliente" class="selectpicker" title="Selecciona un Cliente"
                                    data-style="btn-info" data-dropup-auto="false" data-live-search="true"
                                    data-width="100%" data-size="10" data-container="body"></select>
                            </div>
                            <div class="col-2 col-md-1">
                                <button class="btn btn-info" onclick="MostrarModalAgregarCliente(null);"><i
                                        class="fa fa-plus-square"></i></button>
                            </div>
                            <div class="col-12 col-md-3 text-lg-right text-center mt-3 mt-lg-0 mb-3 mb-lg-0">
                                <button class="btn btn-success btn-block" onclick="modalAgregarProducto();"><i
                                        class="fa fa-plus-square"></i>
                                    AGREGAR PRODUCTO</button>
                            </div>
                            <div class="col-md-4 text-right">


                                <button onClick="setEditing(false)" class="btn btn-danger p-2 font-weight-bold"> <i
                                        class="fa fa-close"></i> </button>

                                <button id="btn_guardarpedido" class="btn btn-primary font-weight-bold p-2 ml-3"
                                    onclick="vistaPrevia();"><i class="fa fa-check"></i> VISTA PREVIA</button>


                            </div>

                        </div> <!-- FIN ROW -->


                        <div class="row mt-2">
                            <div class="col-md-4 form-group">
                                <label class="col-form-label" for="input-rut">R.U.T:</label>
                                <input type="search" autocomplete="off" class="form-control w-100 lg-w-75"
                                    name="input-rut" id="input-rut" maxlength="20" />
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
                                    data-style="btn-info" data-dropup-auto="false" data-live-search="true"
                                    data-width="100%" data-container="container-comuna" data-size="5"></select>
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
                                <input type="search" autocomplete="off" class="form-control w-100 lg-w-75"
                                    name="input-comentario" id="input-comentario" maxlength="100" />
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
                                                    Agrega Productos a la Cotización</span>
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section><!-- /.content -->
            </div>

        </div>


        <?php include('./class_lib/main_footer.php');?>
        <div id="modal-estado" class="modal" data-keyboard="false" data-backdrop="static">
            <div class="modal-content-adminpedido">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Estado a la Cotización</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="row">
                    <div class="col text-center">
                        <div class="py-2 mt-3">
                            <button type="button" class="btn btn-danger mr-2" onClick="guardarEstado(-1)"><i
                                    class="fa fa-ban"></i> CANCELADA</button>
                            <button type="button" class="btn btn-secondary mr-2" onClick="guardarEstado(0)"><i
                                    class="fa fa-clock-o"></i> PENDIENTE</button>
                            <button type="button" class="btn btn-success" onClick="guardarEstado(1)"><i
                                    class="fa fa-check"></i> APROBADA</button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col text-center">
                        <div class="py-2 mt-3">
                            <button type="button" class="btn btn-primary" onClick="guardarEstado(2)"><i
                                    class="fa fa-users"></i> ESPECIAL</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <?php include("modal_agregar_cotizacion.php") ?>

        <?php include("modal_agregar_cliente.php") ?>


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
                            <div class="row">
                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button id="btn-print" class="btn btn-primary btn-block btn-sm mt-2 mt-md-0"
                                        onclick="printRemito(0)"><i class="fa fa-download"></i> DESCARGAR
                                    </button>
                                </div>
                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button class="btn btn-primary btn-sm btn-block mt-2 mt-md-0"
                                        onclick="printRemito(1)"><i class="fa fa-print"></i> IMPRIMIR
                                    </button>
                                </div>
                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button id="btn-email" class="btn btn-info btn-sm mt-2 mt-md-0 btn-block"
                                        onclick="sendMail(this)"><i class="fa fa-envelope"></i> ENVIAR
                                    </button>
                                </div>
                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button id="btn-send" class="btn btn-info btn-sm mt-2 mt-md-0 btn-block"
                                        onclick="toggleContainerGD()"><i class="fa fa-file"></i> GUÍA DESPACHO
                                    </button>
                                </div>
                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button id="btn-send" class="btn btn-secondary btn-sm mt-2 mt-md-0 btn-block"
                                        onclick="editarCotizacion()"><i class="fa fa-edit"></i> EDITAR
                                    </button>
                                </div>

                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button id="btn-orden-envio" class="btn btn-info btn-sm mt-2 mt-md-0 btn-block"
                                        onclick="modalOrdenEnvio()"><i class="fa fa-truck"></i> ORDEN ENVIO
                                    </button>
                                </div>

                                <div class="col-6 col-md-2 vistaprevia-group d-none">
                                    <button id="btn-link-pago" class="btn btn-info btn-sm mt-2 mt-md-2 btn-block"
                                        onclick="modalLinkPago()"><i class="fa fa-link"></i> LINK PAGO
                                    </button>
                                </div>

                                <div class="col-6 col-md-3">
                                    <button id="btn-save" class="btn btn-success d-none mt-2 mt-md-0 btn-block"
                                        onclick="GuardarPedido()"><i class="fa fa-save"></i> CONFIRMAR</button>
                                </div>

                                <div class="col text-right">
                                    <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal"
                                        aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="row mt-2 row-guia d-none">
                                <div class="col-md-12">
                                    <div class="container">
                                        <div class="row mt-2">
                                            <div class="col-md-3 form-group">
                                                <label class="col-form-label" for="input-rut-transporte">R.U.T.
                                                    Transporte:</label>
                                                <input type="search" autocomplete="off"
                                                    class="form-control w-100 lg-w-75 input-transporte input-rut"
                                                    name="input-rut-transporte" id="input-rut-transporte"
                                                    maxlength="10" />
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label class="col-form-label" for="input-patente">Patente
                                                    Transporte:</label>
                                                <input type="search" autocomplete="off"
                                                    class="form-control w-100 lg-w-75 input-transporte"
                                                    name="input-patente" id="input-patente" maxlength="10" />
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label class="col-form-label" for="input-rut-chofer">R.U.T.
                                                    Chofer:</label>
                                                <input type="search" autocomplete="off"
                                                    class="form-control w-100 lg-w-75 input-transporte input-rut"
                                                    name="input-rut-chofer" id="input-rut-chofer" maxlength="10" />
                                            </div>
                                            <div class="col-md-3 form-group">
                                                <label class="col-form-label" for="input-nombre-chofer">Nombre
                                                    Chofer:</label>
                                                <input type="search" autocomplete="off"
                                                    class="form-control w-100 lg-w-75 input-transporte"
                                                    name="input-nombre-chofer" id="input-nombre-chofer"
                                                    maxlength="40" />
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-6">
                                                <button id="btn-guardar-transporte" onclick="guardarCambiosTransporte()"
                                                    class="btn btn-success d-none pull-right"><i class="fa fa-save"></i>
                                                    GUARDAR CAMBIOS
                                                    TRANSPORTE</button>
                                            </div>
                                        </div>

                                        <div class="row row-select-folio">

                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class='box-body'>
                        <div class="print-cotizacion"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php include("modals/orden_envio.php"); ?>

        <div id="modal-qr" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Escanear Cotización</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="loader-anular">
                            <div class="load-3 mt-4 ml-4">
                                <div class="line"></div>
                                <div class="line"></div>
                                <div class="line"></div>
                            </div>
                        </div>
                        <div class="row wrapper">
                            <div class="col">
                                <div style="min-height: 180px;"
                                    class="d-flex flex-row justify-content-center align-items-center w-100">
                                    <div id="qr-reader" style="width:180px"></div>
                                    <div class="btn-switch">
                                        <button onclick="switchCam()" class="d-block btn btn-primary btn-sm ml-2"><i
                                                class="fas fa-camera"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal-link" class="modal" tabindex="-1" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Link de Pago</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    <div class="loader-anular">
                    <div class="load-3 mt-4 ml-4">
                        <div class="line"></div>
                        <div class="line"></div>
                        <div class="line"></div>
                    </div>
                </div>
                        <div class="row link-container">
                            <div class="form-group col-md-12">
                                <div class="d-flex flex-row">
                                    <input style="text-transform:lowercase !important;" class="form-control" id="input-link-pago" type="search" autocomplete="off">
                                    <button class="btn btn-primary btn-sm ml-2" onclick="copyToClipboard()">COPIAR</button>
                                </div>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="control-sidebar-bg"></div>

    </div>
    <!--ocultar-->





</body>

</html>