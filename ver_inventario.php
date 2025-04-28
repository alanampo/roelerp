<?php 
  include "./class_lib/sesionSecurity.php";
?>
<!DOCTYPE html>
<html>

<head>
  <title>Inventario</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <link rel="stylesheet" href="js/Croppie/cropper.min.css" />

  <script src="dist/js/ver_inventario.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="js/Croppie/cropper.min.js"></script>
  <script src="js/Croppie/jquery-cropper.min.js"></script>
  
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
          <h1>Inventario</h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Inventario</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="tab">
            <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'productos');">
              PRODUCTOS
            </button>
            <button class="tablinks" onclick="abrirTab(event, 'tipos');">
              TIPOS DE PRODUCTO
            </button>
            <button class="tablinks" onclick="abrirTab(event, 'servicios');">
              SERVICIOS
            </button>
            <button class="tablinks" onclick="abrirTab(event, 'tipos-servicio');">
              TIPOS DE SERVICIO
            </button>
            <button class="tablinks" onclick="abrirTab(event, 'ingresos');">
              INGRESOS
            </button>
            <button class="tablinks" onclick="abrirTab(event, 'egresos');">
              EGRESOS
            </button>
          </div>

          <div class="row">
            <div class="col-6 col-select-tipo d-none">
              <div class="d-flex flex-row align-items-center">
                <label for="select_tipo" class="control-label">Filtrar por:</label>
                <select id="select_tipo" class="selectpicker ml-3 w-75" title="Tipo de Producto/Servicio"
                  data-style="btn-info" data-live-search="true" onChange="busca_productos(currentTab);"></select>

                <button class="btn btn-info ml-2" onclick="clearFiltro()"><i class="fa fa-times"></i></button>
              </div>
            </div>
            <div class="col-6 col-select-tipo-pos d-none">
              <div class="d-flex flex-row">
                <label for="select-tipo-pos-filtro" class="control-label">Filtrar por:</label>
                <select id="select-tipo-pos-filtro" class="selectpicker ml-3 w-75" title="Selecciona"
                  data-style="btn-info" onChange="busca_productos(currentTab);">
                  <option value="-1">Todos</option>
                  <option value="productos">Productos</option>
                  <option value="servicios">Servicios</option>
                </select>
              </div>
            </div>
            <div class="col text-right">
              <button class="btn btn-success fa fa-plus-square" style="font-size: 1.6em"
                onclick="modalAgregar();"></button>
            </div>
          </div>



          <!-- Your Page Content Here -->
          <div class="row mt-2 mb-5">
            <div class="col">
              <div id="tabla_entradas"></div>
            </div>
          </div>
        </section>
        <!-- /.content -->
      </div>
      <!-- /.content-wrapper -->

      <div id="modalUploadLogo" class="modal">
        <div class="modal-upload-logo">
          <div class="box box-primary mb-0">
            <div class="modal-header">
              <h5 class="modal-title">Subir Imagen</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col text-center">
                <div class="d-flex justify-content-center w-100">
                  <div style="width: 300px; height: 300px">
                    <img id="verificar-subida-logo" src="#" style="display: block; max-width: 100%" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button id="btn-subir-logo" type="button" class="btn btn-primary">
              SUBIR
            </button>
          </div>
        </div>
      </div>


      <!-- Main Footer -->
      <?php include('class_lib/main_footer.php');?>

      <!-- Modals -->
      <?php include('modals/inventario/tipo_pos.php');?>
      <?php include('modals/inventario/pos.php');?>
      <?php include("modals/inventario/atributos.php");?>
      <?php include("modals/inventario/valores.php");?>
      <?php include('modal_ingreso_egreso.php');?>

      <?php include("modals/inventario/precio.php");?>
      
      <div class="control-sidebar-bg"></div>
    </div>
    <!-- ./wrapper -->
  </div>
  <!-- MODAL FIN -->
</body>

</html>