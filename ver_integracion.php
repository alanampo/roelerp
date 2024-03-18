<?php include "./class_lib/sesionSecurity.php";?>
<!DOCTYPE html>
<html>

<head>
  <title>Integración</title>
  <?php include "./class_lib/links.php";?>
  <?php include "./class_lib/scripts.php";?>

  <link rel="stylesheet" href="css/bootstrap-datepicker3.min.css" />
  <link rel="stylesheet" href="css/cropper.min.css" />

  <script src="dist/js/check_permisos.js?v=<?php echo $version ?>"></script>
  <script src="plugins/moment/moment.min.js"></script>
  <script src="dist/js/uploadcaf.js?v=<?php echo $version ?>"></script>

  <script src="js/Croppie/cropper.min.js"></script>
  <script src="js/Croppie/jquery-cropper.min.js"></script>
  <script src="dist/js/ver_integracion.js?v=<?php echo $version ?>"></script>

  <script src="js/bootstrap-datepicker.min.js"></script>

  <link rel="stylesheet" href="./css/loading.css" />

  <style>
    input {
      text-transform: uppercase;
    }

    .img-negocio img {
      width: 250px;
      height: 200px;
      border: 1px solid #00000033;
      border-radius: 1rem;
    }

    #logo-upload {
      display: none;
    }
  </style>
</head>

<body>
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
            Integración
            <small>
              <?php echo $fecha; ?>
            </small>
          </h1>
          <ol class="breadcrumb">
            <li><a href="inicio.php"> Inicio</a></li>
            <li class="active">Integración</li>
          </ol>
        </section>
        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col">
              <div class="tab">
                <button id="defaultOpen" class="tablinks" onclick="abrirTab(event, 'datos');">
                  DATOS EMPRESA
                </button>
                <button id="tabcaf" class="tablinks" onclick="abrirTab(event, 'caf');">
                  ADMINISTRAR FOLIOS
                </button>
              </div>
            </div>
          </div>

          <div class="tabco tab-caf d-none">
            <div class="row">
              <div class="col-md-2">
                <button class="btn btn-block btn-success" onclick="modalCAF()">
                  <i class="fa fa-plus-square"></i> NUEVO CAF
                </button>
              </div>
            </div>
            <div class="row mt-2 mb-5">
              <div class="col">
                <div id="tabla_entradas_caf"></div>
              </div>
            </div>
          </div>

          <div class="tabco tab-datos d-none">
            <div class="loading-integracion">
              <div class="load-3 mt-2 ml-4">
                <div class="line"></div>
                <div class="line"></div>
                <div class="line"></div>
              </div>
            </div>
            <div class="datos-wrapper d-none">
              <div class="row mt-2 mb-5">
                <div class="col-md-8">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="input-razon">Razón Social</label>
                      <input type="search" autocomplete="off" maxlength="40" class="form-control" id="input-razon"
                        placeholder="Razón Social" />
                    </div>
                    <div class="form-group col-md-6">
                      <label for="input-rut">R.U.T</label>
                      <input type="search" autocomplete="off" class="form-control" id="input-rut"
                        placeholder="R.U.T Empresa" maxlength="12" />
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="input-direccion">Dirección</label>
                      <input type="search" autocomplete="off" maxlength="50" class="form-control" id="input-direccion"
                        placeholder="Domicilio" style="text-transform: none !important" />
                    </div>
                    <div class="form-group col-md-6">
                      <label for="select-comuna">Comuna</label>

                      <select id="select-comuna" class="selectpicker" title="Comuna" data-style="btn-info"
                        data-dropup-auto="false" data-live-search="true" data-width="100%" data-size="8"></select>
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="input-telefono">Teléfono</label>
                      <input type="search" autocomplete="off" class="form-control" maxlength="30" id="input-telefono"
                        placeholder="Teléfono" />
                    </div>
                    <div class="form-group col-md-6">
                      <label for="input-email">E-Mail</label>
                      <input type="search" autocomplete="off" maxlength="60" class="form-control" id="input-email"
                        placeholder="hola@ejemplo.com" style="text-transform: lowercase !important" />
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="input-giro">Giro</label>
                      <input type="search" autocomplete="off" class="form-control" id="input-giro" placeholder="Giro"
                        maxlength="100" style="text-transform: none !important" />
                    </div>
                    <div class="form-group col-md-6">
                      <label for="input-acteco">Cód. Actividad Económica
                        <button
                          onClick="window.open('https://www.sii.cl/catastro/homologacion_codigos_actividad.pdf', '_blank')"
                          class="ml-2 btn btn-sm btn-secondary fa fa-question pb-0 pt-0"></button></label>
                      <input type="search" autocomplete="off" maxlength="10" class="form-control" id="input-acteco"
                        placeholder="Ejemplo 013000" />
                    </div>
                  </div>

                  <div class="form-row">
                    <!-- Date Picker -->
                    <div class="form-group col-md-6">
                      <label for="input-fecha-resolucion">Fecha Resolución SII</label>
                      <div class="datepicker p-0 date input-group">
                        <input type="text" placeholder="Selecciona la fecha" class="form-control"
                          id="input-fecha-resolucion" readonly />
                        <div class="input-group-append">
                          <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                        </div>
                      </div>
                    </div>
                    <!-- // Date Picker -->
                    <div class="form-group col-md-6">
                      <label for="input-num-resolucion">N° Resolución SII</label>
                      <input type="search" autocomplete="off" class="form-control" id="input-num-resolucion"
                        placeholder="N° Resolución" maxlength="6" />
                    </div>
                  </div>

                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label for="input-certificado">Certificado Autenticación SII (.p12 o .pfx)</label>
                      <input id="input-certificado" type="file" accept=".p12,.pfx" onchange="onInput(this)"
                        class="form-control" />
                      <button onclick="eliminarCertificado()" id="btn-eliminar-certificado"
                        class="btn btn-success btn-block d-none">
                        CERTIFICADO CARGADO ( <i class="fa fa-trash"></i> )
                      </button>
                    </div>
                    <div class="form-group col-md-6 form-pass">
                      <label for="input-pass-certificado">Contraseña Certificado</label>
                      <input id="input-pass-certificado" type="password" autocomplete="new-password" maxlength="20"
                        class="form-control" />
                    </div>
                  </div>

                  <button id="btn-guardar-datos" onClick="guardarDatosEmpresa()" class="btn btn-primary mt-4 mb-5">
                    <i class="fa fa-save"></i> GUARDAR DATOS EMPRESA
                  </button>

                  <div class="form-row mt-5 modo-servidor-wrapper">
                    <div class="form-group col-md-6">
                      <label for="input-certificado">Modo Pruebas/Modo Producción</label>
                      <div class="row">
                        <div class="col-md-8">
                          <select class="form-control w-100" id="select-modo">
                            <option value="0">Pruebas (Maullín)</option>
                            <option value="1">PRODUCCIÓN (PALENA)</option>
                          </select>
                        </div>
                        <div class="col-md-4">
                          <button onclick="guardarModo()" class="btn btn-secondary btn-block"><i class="fa fa-save"></i>
                            GUARDAR</button>
                        </div>
                      </div>

                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="container p-3" style="background: #CEE3F6;border-radius:15px">
                    <div class="row">
                      <div class="col text-center">
                        <div class="img-negocio">
                          <h6 for="img-canal">Logo para Impresiones</h6>
                          <img id="img-canal" src="dist/img/noimage.jpg" />
                          <input id="logo-upload" type="file" accept="image/jpg,image/jpeg,image/png" />
                        </div>
                      </div>
                    </div>
                    <div class="form-row mt-5">
                      <div class="form-group col-md-12">
                        <label for="input-footer1">Pié de Página 1</label>
                        <input type="search" autocomplete="off" class="form-control" id="input-footer1"
                          placeholder="Izquierda" maxlength="40" style="text-transform: none;" />
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-12">
                        <label for="input-footer2">Pié de Página 2</label>
                        <input type="search" autocomplete="off" class="form-control" id="input-footer2"
                          placeholder="Derecha" maxlength="40" style="text-transform: none;" />
                      </div>
                    </div>
                    <button onclick="guardarFooter()" class="btn btn-primary mt-3 pull-right"><i class="fa fa-save"></i>
                      GUARDAR PIÉ DE PÁGINA</button>
                  </div>
                </div>
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

    <div id="modal-caf" class="modal" data-keyboard="false" data-backdrop="static">
      <div class="modal-upload-caf">
        <div class="box box-primary mb-0">
          <div class="modal-header">
            <h5 class="modal-title">Nuevo CAF</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class="box-body">
          <div class="row">
            <div class="col-md-5 text-center">
              <div class="drop-zone">
                <span class="drop-zone__prompt">Arrastra el archivo o haz click para subirlo</span>
                <input type="file" id="input-caf" name="myFile" class="drop-zone__input" accept=".xml" />
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

    <div id="modalUploadLogo" class="modal">
      <div class="modal-upload-logo">
        <div class="box box-primary mb-0">
          <div class="modal-header">
            <h5 class="modal-title">Subir Logo</h5>
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


    <div id="modal-anular" class="modal" data-keyboard="false" data-backdrop="static">
      <div class="modal-upload-caf">
        <div class="box box-primary mb-0">
          <div class="modal-header">
            <h5 class="modal-title">Anular Folios</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
        </div>
        <div class="box-body">
          <div class="row">
            <div class="form-group col-md-6">
              <label for="select-folios">Selecciona los Folios:</label>

              <select id="select-folios" class="selectpicker" title="Folios" data-style="btn-info"
                data-dropup-auto="false" data-live-search="true" data-width="100%" data-size="8" multiple></select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" onclick="anularFolios()">
            GUARDAR
          </button>
        </div>
      </div>
    </div>

    <div class="control-sidebar-bg"></div>
  </div>
  <!--ocultar-->
</body>

</html>