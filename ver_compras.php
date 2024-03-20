<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Compras</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js"></script>
    <script src="dist/js/ver_compras.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/common/pagos_facturas_compra.js?v=<?php echo $version ?>"></script>
    <script src="js/charts.min.js"></script>
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
                        Compras
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Compras</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="row">
                        <div class="col">
                            <div class="tab">
                                <button class="tablinks" onclick="abrirTab(event, 'mensual');">POR
                                    MES</button>
                                <button id="defaultOpen" class="tablinks"
                                    onclick="abrirTab(event, 'historico');">HISTÓRICO</button>
                                <button class="tablinks" onclick="abrirTab(event, 'proveedores');">POR PROVEEDOR <i
                                        class="fa fa-line-chart text-muted"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="tabco tab-mensual d-none">
                        <div class='row mt-2 mb-5'>
                            <div class='col'>
                                <div class="row">
                                    <div class="col-md-2">
                                        <select id="select-anio" class="selectpicker" onchange="" title="Año"
                                            data-style="btn-info" data-dropup-auto="false" data-width="100%">
                                        </select>
                                    </div>
                                    <div class="col-md-2 col-mes">
                                        <select id="select-mes" class="selectpicker" onchange="" title="Mes"
                                            data-style="btn-info" data-dropup-auto="false" data-width="100%">
                                            <option value="1">Enero</option>
                                            <option value="2">Febrero</option>
                                            <option value="3">Marzo</option>
                                            <option value="4">Abril</option>
                                            <option value="5">Mayo</option>
                                            <option value="6">Junio</option>
                                            <option value="7">Julio</option>
                                            <option value="8">Agosto</option>
                                            <option value="9">Septiembre</option>
                                            <option value="10">Octubre</option>
                                            <option value="11">Noviembre</option>
                                            <option value="12">Diciembre</option>

                                        </select>
                                    </div>
                                </div>
                                <div class='row mt-3 mb-5'>
                                    <div class='col'>
                                        <div id='tabla_entradas'></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tabco tab-historico d-none">
                        <div class='row mt-3 mb-5'>
                            <div class='col'>
                                <div id='tabla_historico'></div>
                            </div>
                        </div>
                    </div>
                    <div class="tabco tab-proveedores d-none">
                        <div class='box box-primary mt-2'>
                            <div class='box-header with-border'>
                                <h4>Facturas de Compra por Proveedor</h4>
                            </div>
                            <div class='box-body chart-container mb-5' style="min-height:75vh;"></div>
                        </div>
                    </div>
                    <!-- Your Page Content Here -->

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


    <div id="modal-detalle-compra" class="modal" data-keyboard="false" data-backdrop="static">
        <!--MODALITO AGREGAR ARTICULO -->
        <div class="modal-content-adminpedido">
            <div class="modal-header">
                <h5 class="modal-title">Detalle Compra</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="row mt-2">
                <div class="col table-wrapper">
                </div>
            </div>

        </div>
    </div>
    <?php include("modals/pagos.php");?>
</body>

</html>