<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Ver Pedidos</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <link rel="stylesheet" href="plugins/select2/select2.min.css">
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css">

  <script src="dist/js/cargar_pedido.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_seguimiento.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_pedidos.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>

  <script src="plugins/html5-qrcode/html5-qrcode.min.js"></script>
  <script src="plugins/QRCode/qrcode.min.js"></script>

  <script src="dist/js/common/etiquetas.js?v=<?php echo $version ?>"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    
  </style>
</head>

<body onload="busca_entradas();pone_tipos1();">
  <div id="print-wrapper">
  </div>
  <div class="ocultar" id="ocultar">
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
            Ver Pedidos
            
            <button style="font-size: 14px !important" type="button" class="btn btn-primary btn-round fa fa-print ml-5"
              id="btn_printcliente" onClick="print_Busqueda(1);"> IMPRIMIR</button>
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Ver Pedidos</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->
          <div class='row row-busqueda'>

            <div class='col'>
              <div class="d-flex align-items-end h-100 pb-2">
                <div class="row">
                  <div class="col">
                    <div class="tab">
                      <button class="tablinks" onclick="abrirTab(event, 'todos');" id="defaultOpen">TODOS <span
                          class="label-cant label-todos"></span></button>
                      <button class="tablinks" onclick="abrirTab(event, 'pendientes');"><span
                          class="label-pend">PENDIENTES <span
                            class="label-cant label-pendientes"></span></span></button>
                      <button class="tablinks" onclick="abrirTab(event, 'produccion');">EN PRODUCCIÓN <span
                          class="label-cant label-produccion"></span></button>
                      <button class="tablinks" onclick="abrirTab(event, 'entregados');">ENTREGADOS <span
                          class="label-cant label-entregados"></span></button>
                      <button class="tablinks" onclick="abrirTab(event, 'cancelados');">CANCELADOS <span
                          class="label-cant label-cancelados"></span></button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
            <div class='col-md-4'>
              <div class='box box-primary'>
                <div class='box-header with-border'>
                  <button class='btn btn-primary pull-right' onclick='expande_busqueda()' id='btn-busca'><i
                      class='fa fa-caret-down'></i> Busqueda Avanzada</button>
                </div>
                <div class='box-body p-0 box-body-buscar'>


                  <div id="contenedor_busqueda" style="display:none">
                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-3'>
                          <label>Fechas:</label>
                        </div>
                        <div class='col'>
                          <div class="input-group">
                            <button class="btn btn-default pull-left" id="daterange-btn">
                              <i class="fa fa-calendar"></i> Seleccionar...
                              <i class="fa fa-caret-down"></i>
                            </button>
                          </div>
                        </div>

                      </div>

                      <span class='fe'></span>
                      <input type='hidden' class='form-control' id='fi' value=''>
                      <input type="hidden" class='form-control' id='ff' value=''>
                    </div><!-- /.form group -->
                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-3'>
                          <label>Producto:</label>
                        </div>
                        <div class='col'>
                          <select id="select_tipo1" class="selectpicker mobile-device" title="Tipo"
                            data-style="btn-info" data-dropup-auto="false" data-size="5" data-width="100%"
                            multiple></select>
                        </div>
                      </div>
                    </div>


                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-3'>
                          <label>Variedad:</label>
                        </div>
                        <div class='col'>
                          <div class="btn-group" style="width:100%">
                            <input id="busca_variedad" style="text-transform:uppercase" type="search"
                              class="form-control">
                            <span id="searchclear" onClick="$('#busca_variedad').val('');"
                              class="glyphicon glyphicon-remove-circle"></span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="form-group">
                      <div class='row'>
                        <div class='col-md-3'>
                          <label>Cliente:</label>
                        </div>
                        <div class='col'>
                          <div class="btn-group" style="width:100%">
                            <input id="busca_cliente" style="text-transform:uppercase;" type="search"
                              class="form-control">
                            <span id="searchclear" onClick="$('#busca_cliente').val('');"
                              class="glyphicon glyphicon-remove-circle"></span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col">
                        <button class='btn btn-primary pull-right' onclick='busca_entradas();' id='btn-busca'><i
                            class='fa fa-search'></i> Buscar...</button>
                      </div>
                    </div>

                  </div> <!-- CONTENEDOR BUSQUEDA -->






                </div>
              </div>
            </div>





          </div> <!-- FIN ROW -->


          <div class="row mb-5">
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



      <?php include("./modal_ver_estado.php"); ?>
      <?php include("./modal_modificar_cliente.php"); ?>

      <div id="modal-modificar-pedido" class="modal">
        <div class="modal-content-verpedido">
          <div class='box box-primary'>
            <div class='box-header with-border'>
              <h4 class="box-title">Modificar Pedido: <span class="title-modificar-pedido"></span> <button
                  class="d-inline-block btn btn-sm btn-success ml-5" onclick="modalAgregarProducto()"><i
                    class="fa fa-plus-square"></i> AGREGAR AL PEDIDO</button>
              </h4>
              <button style="float:right;font-size: 1.6em" class="btn fa fa-close"
                onClick="$('#modal-modificar-pedido').modal('hide')"></button>
            </div>
            <div id="tablita">
              <div class='box-body'>
                <div class="row">
                  <div class="col">
                    <table class="table tabla-modificar-pedido table-responsive w-100 d-block d-md-table">
                      <thead class="thead-dark">
                        <tr class="text-center">
                          <th>Producto</th>
                          <th>Plantas/Bandejas</th>
                          <th>F. Ingreso</th>
                          <th>F. Entrega Aprox</th>
                          <th>Etapa</th>
                          <th>ID Prod.</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>

                      </tbody>
                    </table>


                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div> <!-- MODAL FIN -->

      <div id="modal-produccion" class="modal">
        <div class="modal-content-verpedido">
          <div class='box box-primary'>
            <div class='box-header with-border'>
              <h4 class="box-title">Enviar a Producción
              </h4>
              <button style="float:right;font-size: 1.6em" class="btn fa fa-close"
                onClick="$('#modal-produccion').modal('hide')"></button>
            </div>

            <div class='box-body'>
              <div class="row">
                <div class="col">
                  <table class="table tabla-produccion table-responsive w-100 d-block d-md-table">
                    <thead class="thead-dark">
                      <tr class="text-center">
                        <th>Producto</th>
                        <th>Cliente</th>
                        <th style="width: 300px">Plantas/Bandejas</th>
                        <th>F. Ingreso</th>
                        <th>ID Prod.</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>

                    </tbody>
                  </table>


                </div>
              </div>

            </div>

          </div>
        </div>
      </div> <!-- MODAL FIN -->
    </div>


    <?php include("./modal_agregar_producto.php"); ?>
    <?php include("./modal_modificar_semillas.php"); ?>
    <?php include "modals/etiquetas.php";?>
    <div class="control-sidebar-bg"></div>


    <style>
      .table2 tr.selected td {
        background-color: #333;
        color: #fff;
      }

      .table2 tr.selected2 td {
        background-color: #333;
        color: #fff;
      }
    </style>

    <script src="plugins/moment/moment.min.js"></script>
    <script src="plugins/daterangepicker/daterangepicker.js"></script>

    <script type="text/javascript">
      var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
      var permisos = "<?php echo $_SESSION['permisos'] ?>";
      func_check(id_usuario, permisos.split(","));
      $(document).ready(function () {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent)) {
          $('.selectpicker').selectpicker('mobile');
        }
        else {
          var elements = document.querySelectorAll('.mobile-device');
          for (var i = 0; i < elements.length; i++) {
            elements[i].classList.remove('mobile-device');
          }
          $('.selectpicker').selectpicker({});
        }
      });

    </script>
</body>

</html>