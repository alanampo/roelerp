<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Usuarios</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_usuarios.js?v=<?php echo $version ?>"></script>
</head>

<body>
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
                        Usuarios <button class="btn btn-success btn-round fa fa-plus-square ml-3"
                        onclick="MostrarModalAgregarUsuario();"></button>

                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Usuarios</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <!-- Your Page Content Here -->
                    <div class='row mt-2 mb-5'>
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

            <div id="ModalAgregarUsuario" class="modal" data-keyboard="false" data-backdrop="static">
                <div class="modal-usuarios">
                    <div class='box box-primary'>
                        <div class='box-header with-border'>
                            <h3 id='titulo' class='box-title'>Agregar Usuario</h3>
                        </div>
                        <div class='box-body'>
                            <div class="form-usuario">
                                <div class='form-group'>
                                    <div>
                                        <label for="username_txt" class="control-label">Nombre de Usuario:</label>
                                    </div>
                                    <div>
                                        <form autocomplete="off" method="post" action="">
                                            <input autocomplete="false" maxlength="20" type="search" id="username_txt"
                                                style="text-transform:lowercase !important;" class="form-control">
                                        </form>
                                    </div>
                                </div>

                                <div class='form-group'>
                                    <label for="nombre_txt" class="control-label">Nombre Real:</label>

                                    <input autocomplete="false" maxlength="40" type="search" id="nombre_txt"
                                        style="text-transform:capitalize !important;" class="form-control">


                                </div>
                            </div>
                            

                            <div class='form-group'>
                                <div>
                                    <label for="password_txt" class="control-label">Contraseña:</label>
                                </div>
                                <div>
                                    <form autocomplete="new-password" method="post" action="">
                                        <input autocomplete="new-password" maxlength="20" type="password"
                                            id="password_txt" class="form-control">
                                    </form>
                                </div>
                            </div>

                            <div class='form-group'>
                                <div>
                                    <label for="password2_txt" class="control-label">Repita Contraseña:</label>
                                </div>
                                <div>
                                    <form autocomplete="new-password" method="post" action="">
                                        <input autocomplete="new-password" maxlength="20" type="password"
                                            id="password2_txt" class="form-control">
                                    </form>
                                </div>
                            </div>

                            <div class='form-group form-permisos'>

                                <label for="select_permisos" class="control-label">Permisos:</label>

                                <select id="select_permisos" class="selectpicker"
                                    title="Selecciona los Permisos" data-style="btn-info" data-dropup-auto="false"
                                    data-size="6" data-width="100%" multiple>
                                    <option value="cotizaciones">Cotizaciones</option>
                                    <option value="facturacion">Facturación</option>
                                    <option value="integracion">Integración</option>
                                    <option value="situacion">Situación Clientes</option>
                                    <option value="estadisticaserp">Estadísticas ERP</option>
                                    <option value="pedidos">Cargar/Ver Pedidos</option>
                                    <option value="planificacionpedidos">Planificación de Pedidos</option>
                                    <option value="historialentrega">Historial de Entregas</option>
                                    <option value="mesadas">Mesadas</option>
                                    <option value="estadisticas">Estadísticas Control</option>
                                    <option value="stock">Stock Bandejas</option>
                                    <option value="semillas">Semillas</option>
                                    <option value="tendencias">Tendencias</option>
                                    <option value="productos">Productos</option>
                                    <option value="panel">Panel de Control</option>
                                    <option value="compras">Facturas de Compras</option>
                                    <option value="situacion-proveedores">Situación Proveedores</option>
                                </select>
                            </div>

                            <div style="margin-top: 100px" align="right">
                                <button type="button" class="btn btn-modal-bottom fa fa-close" id="btn_cancel"
                                    onClick="CerrarModal();"></button>
                                <button type="button" class="btn btn-modal-bottom ml-3 fa fa-save"
                                    id="btn-guardar-usuario"></button>
                            </div>

                        </div>

                    </div>

                </div> <!-- MODAL FIN -->
                <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
                <div class="control-sidebar-bg"></div>
            </div><!-- ./wrapper -->
        </div>


        <script>
            var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
            var permisos = "<?php echo $_SESSION['permisos'] ?>";
            func_check(id_usuario, permisos.split(","));   
        </script>
</body>

</html>