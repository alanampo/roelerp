<?php include "./class_lib/sesionSecurity.php"; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Transportistas</title>
    <?php include "./class_lib/links.php"; ?>
    <?php include "./class_lib/scripts.php"; ?>
    <script src="js/charts.min.js"></script>
    <script src="dist/js/check_permisos.js"></script>
    <script src="dist/js/ver_transportistas.js?v=<?php echo $version; ?>"></script>
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
                        Transportistas <button onclick="modalSucursal()" class="btn btn-success ml-2"><i
                                class="fa fa-plus-square"></i></button>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="inicio.php"> Inicio</a></li>
                        <li class="active">Transportistas</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class='row mt-2 mb-5'>
                        <div class='col'>
                            <div id='tabla_entradas'></div>
                        </div>
                    </div>

                </section><!-- /.content -->
            </div><!-- /.content-wrapper -->


            <div id="modal-sucursal" class="modal" data-keyboard="false" data-backdrop="static" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Sucursal</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                  <label for="select-transportista" class="control-label"
                                    >Transportista:</label
                                  >
                                  <div class="d-flex flex-row">
                                    <select
                                    id="select-transportista"
                                    title="Selecciona"
                                    class="selectpicker"
                                    data-style="btn-info"
                                    data-live-search="true"
                                    data-width="100%"
                                    data-size="10"
                                    data-dropup-auto="false"
                                    ></select>
                                    <button onclick="modalTransportistas()" class="btn btn-info btn-sm ml-1"><i class="fa fa-plus-square"></i></button>
                                  </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="input-nombre-sucursal" class="control-label">Nombre Sucursal:</label>
                                    <input type="search" autocomplete="off" class="form-control"
                                    name="input-nombre-sucursal" id="input-nombre-sucursal" maxlength="50" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="input-direccion" class="control-label">Dirección:</label>
                                    <input type="search" autocomplete="off" class="form-control"
                                    name="input-direccion" id="input-direccion" maxlength="100" />
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="input-telefono" class="control-label">Teléfono:</label>
                                    <input type="search" autocomplete="off" class="form-control"
                                    name="input-telefono" id="input-telefono" maxlength="40" />
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="input-notas" class="control-label">Observaciones:</label>
                                    <input type="search" autocomplete="off" class="form-control"
                                    name="input-notas" id="input-notas" maxlength="255" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="button" onclick="guardarSucursal()" class="btn btn-primary">GUARDAR</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--FIN MODAL SUCURSAL-->

            <div id="modal-transportistas" class="modal" data-keyboard="false" data-backdrop="static" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Transportistas</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <label for="input-nombre-transp" class="control-label">Nombre:</label>
                                    <div class="d-flex flex-row">
                                        <input type="search" autocomplete="off" class="form-control"
                                            name="input-nombre-transp" id="input-nombre-transp" maxlength="20" />
                                        <button onclick="guardarTransportista(this)" class="btn btn-success btn-sm ml-1"><i class="fa fa-save"></i></button>
                                    </div>
                                </div>                                
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                  <table id="table-transp" class="table w-100 d-flex d-md-table table-responsive" role="grid">
                                    <thead class="thead-dark">
                                      <tr role="row">
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center" style="width: 50px"></th>
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
            <!--FIN MODAL TRANSPO-->

            <!-- Main Footer -->
            <?php
      include('class_lib/main_footer.php');
      ?>

            <!-- Add the sidebar's background. This div must be placed
           immediately after the control sidebar -->
            <div class="control-sidebar-bg"></div>
        </div><!-- ./wrapper -->
    </div>


</body>

</html>