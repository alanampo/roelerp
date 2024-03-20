<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Situación Proveedores</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="js/charts.min.js"></script>
    <script src="dist/js/check_permisos.js"></script>
    <script src="dist/js/common/pagos_facturas_compra.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_situacion_proveedores.js?v=<?php echo $version ?>"></script>
</head>

<body>
    <div id="miVentana"></div>
    <div id="ocultar">
        <div class="wrapper">
            <!-- Main Header -->
            <header class="main-header">
                <!-- Logo -->
                <?php
        include('class_lib/nav_header.php');
        ?>

            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <?php
        include('class_lib/sidebar.php');
        ?>
                <!-- /.sidebar -->
            </aside>

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        Situación Proveedores
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Situación Proveedores</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">

                    <div class="row">
                        <div class="col">
                            <div class="tab">
                                <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'clientes');">LISTADO
                                    PROVEEDORES</button>
                                <button class="tablinks" onclick="abrirTab(event, 'porcobrar');">A PAGAR <i
                                        class="fa fa-line-chart text-muted"></i></button>
                                <!-- <button class="tablinks" onclick="abrirTab(event, 'clientescondeuda');">DEUDA CON PROVEEDORES <i class="fa fa-line-chart text-muted"></i></button> -->
                            </div>
                        </div>
                    </div>
                    <div class="tabco tab-clientes d-none">
                        <div class='row mt-2 mb-5'>
                            <div class='col'>
                                <div id='tabla_entradas'></div>
                            </div>
                        </div>
                    </div>

                    <div class="tabco tab-graficos d-none">
                        <div class='box box-primary mt-2'>
                            <div class='box-header with-border'>
                                <div class="row">
                                    <div class="col-md-2 col-anio">
                                        <select id="select-anio" class="selectpicker" onchange="loadData()" title="Año"
                                            data-style="btn-info" data-dropup-auto="false" data-width="100%">
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class='box-body chart-container mb-5' style="min-height:75vh;"></div>
                        </div>
                    </div>
                </section><!-- /.content -->
            </div><!-- /.content-wrapper -->

            <!-- Main Footer -->
            <?php
      include('class_lib/main_footer.php');
      ?>

            <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
            <div class="control-sidebar-bg"></div>
        </div><!-- ./wrapper -->
    </div>

    <div id="modal-vistaprevia" class="modal" data-keyboard="false" data-backdrop="static">
        <div class="modal-vistaprevia">
            <div class='box box-primary mb-0'>
                <div class="modal-header d-block">
                    <div class="row">
                        <div class="col-6 col-md-2 vistaprevia-group">
                            <button class="btn btn-primary btn-sm btn-block mt-2 mt-md-0" onclick="printFicha()"><i
                                    class="fa fa-print"></i> IMPRIMIR
                            </button>
                        </div>


                        <div class="col text-right">
                            <button type="button" class="close mt-2 mt-lg-0" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class='box-body'>
                <div class="print-cotizacion"></div>
            </div>
        </div>
    </div>

    <div id="modal-detalle-deuda" class="modal" data-keyboard="false" data-backdrop="static">
        <div class="modal-vistaprevia">
            <div class='box box-primary mb-0'>
                <div class="modal-header">
                <h5 class="modal-title">Detalle Deuda</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        
                </div>
                <div class='box-body'>
                    <div class="detalle-deuda"></div>
                </div>
            </div>
            
        </div>
    </div>

    <?php include("modals/pagos.php");?>

</body>

</html>