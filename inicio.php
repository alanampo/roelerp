<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>
  <head>
    <title>ERP Roelplant</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  </head>
  <body>
    <div class="wrapper">
      <header class="main-header">
        <?php
        include('class_lib/nav_header.php');
        ?>
      </header>
      <!-- Left side column. contains the logo and sidebar -->
      <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <?php
        include('class_lib/sidebar.php');
        include('class_lib/class_conecta_mysql.php');
        $dias = array("Domingo","Lunes","Martes","Miércoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha=$dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
        ?>
        <!-- /.sidebar -->
      </aside>

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <div class="bg-light alert-wrapper"></div>
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            <small><?php echo $fecha; ?></small>
          </h1>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row row-modulos">
            <div class="col-6 col-md-3 col-cotizaciones d-none"></div>

            <div class="col-6 col-md-3 col-facturacion d-none"></div>
  
            <!--<div class="col-6 col-md-3 col-integracion d-none"></div>-->
            
            <div class="col-6 col-md-3 col-estadisticas d-none"></div>

            <div class="col-6 col-md-3 col-situacion d-none"></div>

            <div class="col-6 col-md-3 col-situacion-especial d-none"></div>

            <div class="col-6 col-md-3 col-comisiones d-none"></div>
          </div>
        </section>
      </div>
      <!-- /.content-wrapper -->
      <!-- Main Footer -->
      <?php include('./class_lib/main_footer.php'); ?>
      <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
      <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->
  </body>
</html>
