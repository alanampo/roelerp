<?php include "./class_lib/sesionSecurity.php";?>
<!DOCTYPE html>
<html>
  <head>
    <title>Facturación</title>
    <?php include "./class_lib/links.php";?>
    <?php include "./class_lib/scripts.php";?>
    <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
    <script src="plugins/moment/moment.min.js"></script>
    <script src="js/numeroAlertas.js"></script>
    <script src="js/html2pdf.bundle.min.js"></script>
    <script src="dist/js/common/agregar_cliente.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/common/pagos.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/uploadcaf.js?v=<?php echo $version ?>"></script>
    <script src="dist/js/ver_facturacion.js?v=<?php echo $version ?>"></script>
    <link rel="stylesheet" href="./css/loading.css" />
    <style>
      input {
        text-transform: uppercase;
      }
    </style>
  </head>

  <body>
    <div class="print-cotizacion" id="miVentana" style="padding:60px !important"></div>
    <div id="ocultar">
      <div class="wrapper">
        <header class="main-header">
          <?php include 'class_lib/nav_header.php';?>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
          <?php
include 'class_lib/sidebar.php';
$dias = array("Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sábado");
$meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$fecha = $dias[date('w')] . " " . date('d') . " de " . $meses[date('n') - 1] . " del " . date('Y');
?>
          <!-- /.sidebar -->
        </aside>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
          <section class="content-header">
            <h1>
              Facturación
              <small>
                <?php echo $fecha; ?>
              </small>
              <button class="btn btn-sm btn-primary ml-3" onclick="generarSolicitudDespacho()">SOLICITUD DESPACHO</button>

              
            </h1>
            <ol class="breadcrumb">
              <li><a href="inicio.php"> Inicio</a></li>
              <li class="active">Facturación</li>
            </ol>
          </section>
          <!-- Main content -->
          <section class="content">
            <div class="row">
              <div class="col">
                <div class="tab">
                  <button
                    id="defaultOpen"
                    class="tablinks"
                    onclick="abrirTab(event, 'historial');"
                  >
                    HISTORIAL FACTURAS
                  </button>
                  <button class="tablinks" onclick="abrirTab(event, 'notas');">
                    NOTAS DE CRÉDITO
                  </button>
                  <button
                    class="tablinks"
                    onclick="abrirTab(event, 'notas-debito');"
                  >
                    NOTAS DE DÉBITO
                  </button>
                  <button class="tablinks" onclick="abrirTab(event, 'guias');">
                    GUÍAS DE DESPACHO
                  </button>
                  <button
                    id="tabcotizaciones"
                    class="tablinks"
                    onclick="abrirTab(event, 'cotizaciones');"
                  >
                    NUEVA FACTURA
                  </button>
                </div>
              </div>
            </div>

            <div class="tabco tab-historial d-none">
              <div class="row mt-2 mb-5">
                <div class="col">
                  <div id="tabla_facturas"></div>
                </div>
              </div>
            </div>

            <div class="tabco tab-notas d-none">
              <div class="row">
                <div class="col text-right">
                  <button
                    onclick="location.href = 'anular_factura.php'"
                    class="btn btn-secondary btn-sm"
                  >
                    <i class="fa fa-ban"></i> ANULAR FACTURA ANTIGUA
                  </button>
                </div>
              </div>
              <div class="row mt-2 mb-5">
                <div class="col">
                  <div id="tabla_notas"></div>
                </div>
              </div>
            </div>

            <div class="tabco tab-notas-debito d-none">
              <div class="row">
                <div class="col text-right">
                  <button
                    onclick="location.href = 'nota_debito.php'"
                    class="btn btn-primary btn-sm"
                  >
                    <i class="fa fa-plus-square"></i> NUEVA NOTA DÉBITO
                  </button>
                </div>
              </div>
              <div class="row mt-2 mb-5">
                <div class="col">
                  <div id="tabla_notas_debito"></div>
                </div>
              </div>
            </div>

            <div class="tabco tab-guias d-none">
              <div class="row">
                <div class="col text-right">
                  <button
                    onclick="location.href = 'guia_despacho.php'"
                    class="btn btn-primary btn-sm"
                  >
                    <i class="fa fa-plus-square"></i> NUEVA GUÍA
                  </button>
                </div>
              </div>

              <div class="row mt-2 mb-5">
                <div class="col">
                  <div id="tabla_guias"></div>
                </div>
              </div>
            </div>

            <div class="tabco tab-cotizaciones d-none">
              <div class="row">
                <div class="col text-right">
                  <button
                    onclick="location.href = 'factura_directa.php'"
                    class="btn btn-primary btn-sm"
                  >
                    <i class="fa fa-plus-square"></i> FACTURA DIRECTA
                  </button>
                </div>
              </div>
              <div class="row mt-2 mb-5">
                <div class="col">
                  <div id="tabla_cotizaciones_container"></div>
                </div>
              </div>
            </div>
          </section>
          <!-- /.content -->
        </div>
      </div>

      <?php
include './class_lib/main_footer.php';
?>

      <?php include "modal_agregar_cotizacion.php"?>

      <div
        id="modal-caf"
        class="modal"
        data-keyboard="false"
        data-backdrop="static"
      >
        <div class="modal-upload-caf">
          <div class="box box-primary mb-0">
            <div class="modal-header">
              <h5 class="modal-title">Nuevo CAF</h5>
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-5 text-center">
                <div class="drop-zone">
                  <span class="drop-zone__prompt"
                    >Arrastra el archivo o haz click para subirlo</span
                  >
                  <input
                    type="file"
                    id="input-caf"
                    name="myFile"
                    class="drop-zone__input"
                    accept=".xml"
                  />
                </div>
              </div>
              <div class="col-md-7">
                <div class="d-flex align-items-center h-100 folio-info">
                  <div class="row">
                    <div class="col">
                      <h5>Tipo: <span class="label-folio-tipo"></span></h5>
                      <h5>
                        Fecha Autorización:
                        <span class="label-folio-fecha"></span>
                      </h5>
                      <h5>
                        Rango Folios: <span class="label-folio-rango"></span>
                      </h5>
                      <h5>
                        Cantidad: <span class="label-folio-cantidad"></span>
                      </h5>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="subirCAF()">
              SUBIR
            </button>
          </div>
        </div>
      </div>

      <div
        id="modal-vistaprevia"
        class="modal"
        data-keyboard="false"
        data-backdrop="static"
      >
        <div class="modal-vistaprevia">
          <div class="box box-primary mb-0">
            <div class="modal-header d-block">
              <button
                type="button"
                class="close pull-right"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
              <?php include "loader.php";?>
              <div class="row row-select-folio"></div>
            </div>
          </div>
          <div class="box-body">
            <div class="print-cotizacion"></div>
          </div>
        </div>
      </div>

      <div
        id="modal-estado"
        class="modal"
        data-keyboard="false"
        data-backdrop="static"
      >
        <div class="modal-content-adminpedido">
          <div class="modal-header">
            <h5 class="modal-title num-factura"></h5>
            <button
              type="button"
              class="close"
              data-dismiss="modal"
              aria-label="Close"
            >
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="row">
            <div class="col text-center">
              <div class="py-2 mt-3 loader-container"></div>
            </div>
          </div>
        </div>
      </div>

      <div
        id="modal-anular-factura"
        class="modal"
        data-keyboard="false"
        data-backdrop="static"
      >
        <div class="modal-content-adminpedido">
          <div class="box box-primary mb-0">
            <div class="modal-header d-block">
              <button
                type="button"
                class="close pull-right"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
              <?php include "loader.php";?>
              <div class="row-select-folio"></div>
            </div>
          </div>
          <div class="box-body">
            <div class="row mt-2">
              <div class="col-md-12 form-group">
                <label class="col-form-label" for="input-comentario"
                  >Comentario:</label
                >
                <input
                  type="search"
                  autocomplete="off"
                  class="form-control w-100 lg-w-75"
                  name="input-comentario"
                  id="input-comentario"
                  maxlength="50"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        id="modal-guia-despacho"
        class="modal"
        data-keyboard="false"
        data-backdrop="static"
      >
        <div class="modal-content-adminpedido">
          <div class="box box-primary mb-0">
            <div class="modal-header d-block">
              <button
                type="button"
                class="close pull-right"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
              <?php include "loader.php";?>
              <div class="row row-select-folio"></div>
            </div>
          </div>
          <div class="box-body">
            <div class="row mt-2">
              <div class="col-md-12 form-group">
                <label class="col-form-label" for="input-comentario-guia"
                  >Comentario:</label
                >
                <input
                  type="search"
                  autocomplete="off"
                  class="form-control w-100 lg-w-75"
                  name="input-comentario-guia"
                  id="input-comentario-guia"
                  maxlength="50"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php include "modal_pagos.php"?>

      <div id="modal-solicitud-despacho" class="modal" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width:1300px !important">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Solicitud Despacho</h5>
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="container-solicitud-despacho"></div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal"
              >
                Cancelar
              </button>
              <button type="button" onclick="printSolicitudDespacho(1)" class="btn btn-primary">
                Imprimir
              </button>
            </div>
          </div>
        </div>
      </div>


      <div id="modal-guia-transito" class="modal" tabindex="-1">
        <div class="modal-dialog modal-xl" style="max-width:1300px !important">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Guía Libre Tránsito</h5>
              <button
                type="button"
                class="close"
                data-dismiss="modal"
                aria-label="Close"
              >
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="container-guia-transito"></div>
            </div>
            <div class="modal-footer">
              <button
                type="button"
                class="btn btn-secondary"
                data-dismiss="modal"
              >
                Cancelar
              </button>
              <button type="button" onclick="printGuiaTransito(1)" class="btn btn-primary">
                Imprimir
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="control-sidebar-bg"></div>
    </div>
    <!--ocultar-->
  </body>
</html>
