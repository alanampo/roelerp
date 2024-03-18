<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Comisiones</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js"></script>
    <script src="dist/js/ver_comisiones.js?v=<?php echo $version ?>"></script>
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
                        Comisiones
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Comisiones</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <!-- Your Page Content Here -->
                    <div class="row">
                        <div class="col-md-2">
                            <select id="select-anio" class="selectpicker" onchange="" title="Año" data-style="btn-info"
                                data-dropup-auto="false" data-width="100%">
                            </select>
                        </div>
                        <div class="col-md-2 col-mes">
                            <select id="select-mes" class="selectpicker" onchange="" title="Mes" data-style="btn-info"
                                data-dropup-auto="false" data-width="100%">
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


    <div id="modal-modificar-porcentaje" class="modal" data-keyboard="false" data-backdrop="static">
        <!--MODALITO AGREGAR ARTICULO -->
        <div class="modal-content-adminpedido">
            <div class="modal-header">
                <h5 class="modal-title">Modificar Porcentaje</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="row mt-5 pl-2 pr-2">
                <div class="col-md-10">
                    <div class="input-group flex-nowrap">
                        <span class="input-group-text" id="addon-wrapping">Porcentaje</span>
                        <input id="input-porcentaje" type="search" autocomplete="off" maxlength="2" class="form-control ml-2 mr-2" placeholder="" aria-label="Porcentaje" aria-describedby="addon-wrapping">
                        <button onclick="guardarPorcentaje()" class="btn btn-success"><i class="fa fa-save"></i> GUARDAR</button>
                      </div>
                </div>
            </div>
            
        </div>


</body>

</html>