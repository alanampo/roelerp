<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>

<html>
  <head>
    <title>Clientes</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js"></script>
    <script src="dist/js/common/agregar_cliente.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_clientes.js?v=<?php echo $version ?>"></script>

  </head>
  <body>
    <div id="miVentana">
    </div>
    
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
            Clientes

          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Clientes</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">

          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label style="cursor: pointer;">
                  <input type="checkbox" id="filtro-sin-vendedor" style="margin-right: 8px;">
                  <span style="font-size: 14px;">Mostrar solo clientes sin vendedor asignado</span>
                </label>
              </div>
            </div>
            <div class="col-md-6 text-lg-right text-center">
              <button class="btn btn-info" onclick="sincronizarVendedoresManual();" id="btn-sync-vendedores"><i class="fa fa-refresh"></i> ACTUALIZAR VENDEDORES</button>
              <button class="btn btn-success" onclick="MostrarModalAgregarCliente();"><i class="fa fa-plus-square"></i> AGREGAR</span></button>
            </div>
          </div>


          <!-- Your Page Content Here -->
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

  <?php include("modal_agregar_cliente.php") ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
  </div>

    <!-- REQUIRED JS SCRIPTS -->


    <script>
      const id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
       const permisos = "<?php echo $_SESSION['permisos'] ?>";
       func_check(id_usuario, permisos.split(","));
    </script>
  </body>
</html>