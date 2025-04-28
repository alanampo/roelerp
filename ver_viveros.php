<?php 
  include "./class_lib/sesionSecurity.php"; 
?>
<!DOCTYPE html>

<html>
  <head>
    <title>Viveros</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js"></script>
    <script src="dist/js/common/agregar_editar_vivero.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_viveros.js?v=<?php echo $version ?>"></script>

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
            Viveros <button class="btn ml-3 btn-success fa fa-plus-square" onclick="modalVivero();"></button>
            
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Viveros</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
        

        
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

  <?php include("modal_vivero.php") ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div><!-- ./wrapper -->
  </div>

    <!-- REQUIRED JS SCRIPTS -->
    
    
    
  </body>
</html>