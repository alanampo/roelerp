<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <title>Agregar Pedido</title>
  <?php include "./class_lib/links.php"; ?>
  <?php include "./class_lib/scripts.php"; ?>
  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="plugins/moment/moment.min.js"></script>
  <script src="dist/js/common/agregar_cliente.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/cargar_pedido.js?v=<?php echo $version ?>"></script>
  <script src="dist/js/ver_semillas.js?v=<?php echo $version ?>"></script>
  
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
        $dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
        $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $fecha=$dias[date('w')]." ".date('d')." de ".$meses[date('n')-1]. " del ".date('Y') ;
        ?>
        <!-- /.sidebar -->
      </aside>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
          <h1>
            Agregar Pedido
            <small>
              <?php echo $fecha; ?>
            </small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Agregar Pedidos</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <!-- Your Page Content Here -->
          <div class='row'>
            <div class="col-md-4">
              <p class="label-main-cliente">Cliente:</p>
              <textarea rows="2" class="form-control" name="textarea" id="observaciones_txt" placeholder="OBSERVACIONES"
                style="width:100%;text-transform: uppercase; resize:none"></textarea>
            </div>
            <div class="col-md-2">
              
            </div>
            <div class="col-md-2 text-right">
              <button class="btn btn-success btn-round" onclick="modalAgregarProducto(event);"><i class="fa fa-plus-square"></i>
                AGREGAR PEDIDO</button>
            </div>
            <div class="col-md-4 text-right">


              <button onClick="window.location.href = '/ver_cotizaciones.php';"
                class="btn btn-danger p-2 font-weight-bold"> <i class="fa fa-close"></i> </button>

              <button id="btn_guardarpedido" class="btn btn-primary font-weight-bold p-2 ml-3"
                onclick="GuardarPedido();"><i class="fa fa-save"></i> GUARDAR</button>


            </div>

          </div> <!-- FIN ROW -->



          <div class="row mt-4">
            <div class="col">
              <table id="table-pedido" class="table table-bordered table-light">
                <thead class="thead-dark">
                  <tr>
                    <th scope="col" style="width: 35%;">Producto</th>
                    <th scope="col">Plantas</th>
                    <th scope="col">Bandejas</th>
                    <th scope="col" style="width: 170px;">Fecha Ingreso</th>
                    <th scope="col" style="width: 170px;">Fecha Entrega</th>
                    
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="pedido-vacio-msg">
                    <th scope="row" colspan="6" class="text-center"><span class="text-muted">El Pedido está vacío</span>
                    </th>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <!--FIN ROW-->
        </section><!-- /.content -->
      </div>

    </div>






    <!-- Main Footer -->

    <?php

      include('./class_lib/main_footer.php');

      ?>
    <div id="ModalAdminPedido" x-id-pedido="" class="modal">
      <!--MODALITO AGREGAR ARTICULO -->
      <div class="modal-content-adminpedido">
        <div class="row mt-3">
          <div class="col text-center">
            <h3>¡El pedido fue guardado correctamente!</h3>
            <h4>¿Qué deseas hacer ahora?</h4>
          </div>
        </div>
        <div class="row">
          <div class="col text-center">
            <div class="py-2 mt-3">
              <button type="button" class="btn btn-primary ml-2 mr-2" id="btn_printcliente"
                onClick="print_Cliente(1);"><i class="fa fa-print"></i> IMPRIMIR DETALLE</button>
              <button type="button" class="btn btn-success" id="btn_seguirpidiendo"
                onClick="location.href = '/ver_cotizaciones.php'"><i class="fa fa-check"></i> REGRESAR A COTIZACIONES</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include("modal_agregar_cliente.php") ?>
    
    <?php include("modal_agregar_producto.php") ?>


    
    <div id="ModalAgregarVariedad" class="modal">
      <div class="modal-tipo">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 id="titulo" class="box-title">Agregar Variedad</h3>
          </div>
          <div class="box-body">

            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label">Nombre de la Variedad:</label>
                  <input type="search" autocomplete="off" id="input-nombre" maxLength="50"
                    style="text-transform: uppercase" class="form-control" placeholder="Ingresa el Nombre" />
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label">Código/ID: <span
                      class='label-codigo text-primary font-weight-bold'></span></label>
                  <input type="search" autocomplete="off" id="input-codigo" maxLength="6"
                    style="text-transform: uppercase" class="form-control" placeholder="SÓLO NÚMEROS" />
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label">Precio:</label>
                  <input type="search" autocomplete="off" id="input-precio" maxLength="9" style="font-weight: bold;"
                    class="form-control" placeholder="0.00" />
                </div>
              </div>
            </div>

            <div class="form-group form-dias-produccion-variedad d-none">
              <div class="row">
                <div class="col">
                  <label class="control-label" for="dias-produccion-variedad">Días de Producción</label>
                  <div class="select-editable">
                    <select class="form-control" onchange="this.nextElementSibling.value=this.value;">
                      <option class="option" value="30">30 (1 mes)</option>
                      <option class="option" value="60">60 (2 meses)</option>
                      <option class="option" value="90">90 (3 meses)</option>
                      <option class="option" value="120">120 (4 meses)</option>
                      <option class="option" value="150">150 (5 meses)</option>
                      <option class="option" value="180">180 (6 meses)</option>
                    </select>
                    <input type="search" autocomplete="off" maxlength="3" class="form-control" id="dias-produccion-variedad" value="30" type="search" autocomplete="off">
                  </div>
                </div>
              </div>
            </div>

            <div align="right">
              <button type="button" class="btn fa fa-close btn-modal-bottom"
                onClick="cerrarModalAgregarVariedad();"></button>
              <button type="button" class="btn fa fa-save btn-modal-bottom ml-2" onClick="guardarVariedad();"></button>
            </div>
          </div>
        </div>
      </div>
    </div>



    <div id="ModalAgregarEspecie" class="modal">
      <div class="modal-tipo">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 id="titulo" class="box-title">Agregar Especie</h3>
          </div>
          <div class="box-body">

            <div class="form-group">
              <div class="row">
                <div class="col">
                  <label class="control-label">Nombre de la Especie:</label>
                  <input type="search" autocomplete="off" id="input-nombre-especie" maxLength="50"
                    style="text-transform: uppercase" class="form-control" placeholder="Ingresa el Nombre" />
                </div>
              </div>
            </div>
            <div class="form-group">
              <div class="row">
            <div class="col-md-6">
                <label class="control-label" for="dias-produccion-especie">Días de Producción</label>
                <div class="select-editable">
                  <select class="form-control" onchange="this.nextElementSibling.value=this.value;setFechaEntrega()">
                    <option class="option" value="30">30 (1 mes)</option>
                    <option class="option" value="60">60 (2 meses)</option>
                    <option class="option" value="90">90 (3 meses)</option>
                    <option class="option" value="120">120 (4 meses)</option>
                    <option class="option" value="150">150 (5 meses)</option>
                    <option class="option" value="180">180 (6 meses)</option>
                  </select>
                  <input maxlength="3" type="search" autocomplete="off"  class="form-control" id="dias-produccion-especie" name="dias-produccion-especie" value="30" type="text" onchange="setFechaEntrega()" onkeyup="this.onchange();" onpaste="this.onchange();" oninput="this.onchange();">
                </div>
              </div>
              </div>
              </div>

            <div align="right">
              <button type="button" class="btn fa fa-close btn-modal-bottom"
                onClick="$('#ModalAgregarEspecie').modal('hide');"></button>
              <button type="button" class="btn fa fa-save btn-modal-bottom ml-2" onClick="guardarEspecie();"></button>
            </div>
          </div>
        </div>
      </div>
    </div>



  </div>



  <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
  </div><!-- ./wrapper -->
  </div>

  
  <script type="text/javascript">
    var id_usuario = "<?php echo $_SESSION['id_usuario'] ?>";
    var permisos = "<?php echo $_SESSION['permisos'] ?>";
    func_check(id_usuario, permisos.split(","));
    
  </script>
</body>

</html>