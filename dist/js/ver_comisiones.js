let currentCliente = null;
$(document).ready(function () {
  $("#input-porcentaje").on(
    "propertychange input",
    function (e) {
      this.value = this.value.replace(/\D/g, "");
    }
  );

  $("#select-anio").html("");
  const anio = new Date().getFullYear();
  for (let i = 2022; i <= anio; i++) {
    $("#select-anio").append(`<option value="${i}">${i}</option>`);
  }
  $("#select-anio").val(anio);
  const mes = new Date().getMonth() + 1;
  $("#select-mes").val(mes);
  $(".selectpicker").selectpicker("refresh");

  loadComisiones();
  $("#select-anio,#select-mes").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      loadComisiones();
    }
  );
});

function loadComisiones() {
  const anio = $("#select-anio option:selected").val();
  const mes = $("#select-mes option:selected").val();
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando, espere...");
    },
    url: "data_ver_comisiones.php",
    type: "POST",
    data: { consulta: "get_comisiones", anio: anio, mes: mes },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[1, "asc"]],
        pageLength: 50,
        language: {
          lengthMenu: "Mostrando _MENU_ usuarios por página",
          zeroRecords: "No hay usuarios",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay usuarios",
          infoFiltered: "(filtrado de _MAX_ usuarios en total)",
          lengthMenu: "Mostrar _MENU_ usuarios",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron resultados",
          paginate: {
            first: "Primera",
            last: "Última",
            next: "Siguiente",
            previous: "Anterior",
          },
          aria: {
            sortAscending: ": toca para ordenar en modo ascendente",
            sortDescending: ": toca para ordenar en modo descendente",
          },
        },
      });
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function generarFichaCliente(clienteID) {
  $("#modal-vistaprevia").modal("show");
  $.ajax({
    url: "data_ver_comisiones.php",
    type: "POST",
    data: {
      consulta: "generar_ficha",
      clienteID: clienteID,
    },
    success: function (x) {
      console.log(x);
      if (x && x.length) {
        try {
          const data = JSON.parse(x);
          const {
            comentario,
            domicilio,
            ciudad,
            comuna,
            cliente,
            rut,
            razon,
            facturas,
          } = data;

          currentCliente = data;

          console.log(data);
          printCotizacion({
            comentario: comentario,
            domicilio: domicilio,
            ciudad: ciudad,
            comuna: comuna,
            razon: razon,
            rut: rut,
            cliente: cliente,
            facturas: facturas,
          });
        } catch (error) {
          console.log(error);
        }
      }
    },
    error: function (jqXHR, estado, error) {
      swal(
        "Ocurrió un error al guardar la Cotización",
        error.toString(),
        "error"
      );
    },
  });
}

async function printCotizacion(dataCotizacion) {
  const dataMembrete = await loadDatosEmpresaPrint();
  if (!dataMembrete || dataMembrete.error) {
    swal(
      "Ocurrió un error al obtener los datos de la Empresa",
      dataMembrete.error,
      "error"
    );
    return;
  }

  const { direccion, email, telefono, logo } = dataMembrete;
  const razonEmpresa = dataMembrete.razon;
  const giroEmpresa = dataMembrete.giro;
  const comunaEmpresa = dataMembrete.comuna;

  $(".print-cotizacion").html("");
  const {
    cliente,
    domicilio,
    ciudad,
    comuna,
    rut,
    razon,
    comentario,
    facturas,
  } = dataCotizacion;

  const now = new Date();
  const datetime =
    (now.getDate() < 10 ? "0" + now.getDate() : now.getDate()) +
    "/" +
    (now.getMonth() + 1 < 10
      ? "0" + (now.getMonth() + 1)
      : now.getMonth() + 1) +
    "/" +
    now.getFullYear() +
    " ";

  let headerinfo = `
                <div class="row mt-2 p-0">
                  <div class="col-md-4 pt-3">
                    <div class="d-flex flex-row">
                      <img style="width: 170px !important; height: 120px !important" src="${logo}"></img>
                      <div class="ml-3">
                      <span class="info-plantinera"><b>${razonEmpresa}</b></span><br>
                      <span class="info-plantinera">${giroEmpresa}</span><br>
                      <span class="info-plantinera">${direccion}, ${comunaEmpresa}</span><br>
                      <span class="info-plantinera">Fono: ${telefono}</span><br>
                      <span class="info-plantinera">Email: ${email}</span>
                    </div>
                    </div>
                  </div>
                  <div class="col-md-8">
                  <div class="customrow pt-2 pb-2" style="padding-left:15px;border: 1px solid #c9c9c9bd;">
                  <div class="tricolumn">
                    <div class="row">
                      <div class="col">
                        <h6 style="color:grey !important">Cliente</h6>
                        <h6 style="">${
                          cliente && cliente.length
                            ? cliente.toUpperCase()
                            : "-"
                        }</h6>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col">
                        <h6 style="color:grey !important">Dirección</h6>
                        <h6 style="">${domicilio}</h6>
                      </div>
                    </div>
                    
                  </div>
                  
                  <div class="tricolumn pl-3">
                    <div class="row">
                      <div class="col">
                        <h6 style="color:grey !important">Comuna</h6>
                        <h6 style="">${comuna ? comuna.toUpperCase() : "-"}</h6>
                      </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <h6 style="color:grey !important">Ciudad</h6>
                            <h6 style="">${
                              ciudad ? ciudad.toUpperCase() : "-"
                            }</h6>
                        </div>
                    </div>

                
                  </div>
                  
                  <div class="tricolumn" style='width:20% !important'>
                    <div class="row">
                        <div class="col">
                            <h6 style="color:grey !important">R.U.T</h6>
                            <h6 style="">${rut && rut.length ? rut : "-"}</h6>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <h6 style="color:grey !important">Fecha Documento</h6>
                            <h6 style="">${datetime}</h6>
                        </div>
                    </div>
                
                  </div>
                </div>
                  </div>
                </div>
                
                
              `;

  $(".print-cotizacion").append(headerinfo);

  $(".print-cotizacion").append(
    `<h5 class='text-center mt-5 font-weight-bold'>Ficha Cliente - Últimas Facturas</h5>`
  );

  const tabla = `<table style='width: 100%' id='tabla_producto' class='table table-bordered tableproductos mt-3 table-responsive w-100 d-block d-md-table' role='grid'>
                          <thead>
                          <tr role='row'>
                            <th class='text-center' style='width:150px'>Fact. N°</th>
                            <th class='text-center'>Fecha</th>
                            <th class='text-center'>Estado</th>
                            <th class='text-center' style='width:200px'>Monto</th>
                            <th class='text-center' style='width:200px'>Deuda</th>
                            </tr>
                          </thead>
                          <tbody>
                            
                          </tbody>
                        </table>`;
  $(".print-cotizacion").append(tabla);

  if (facturas && facturas.length) {
    facturas.forEach(function (factura, i) {
      const { fecha, comentario, monto, sumapagos, id_factura, estado } =
        factura;

      $("#tabla_producto > tbody").append(
        `
          <tr class='text-center' ${
            estado == "ANU" ? "style='text-decoration: line-through;'" : ""
          }>
            <td>${id_factura}</td>
            <td>${fecha}</td>
            <td>${
              !estado
                ? "DESCONOCIDO"
                : estado == "ACEPTADO"
                ? "EMITIDA"
                : estado == "ANU"
                ? "ANULADA"
                : "DESCONOCIDO"
            }</td>
            <td>$${formatearMonto(monto)}</td>
            <td class='text-${monto - sumapagos < 0 ? "success" : "danger"}'>$${
          monto - sumapagos < 0 ? 0 : formatearMonto(monto - sumapagos)
        }</td>
          </tr>                    
        `
      );
    });
  } else {
    $("#tabla_producto > tbody").append(
      `
          <tr class='text-center'>
            <td colspan='5'><h6>El cliente no posee facturas emitidas.</h6></td>
          </tr>                    
        `
    );
  }
}

function printFicha() {
  $("#miVentana").html($(".print-cotizacion").html());
  printRemito(1);
}

function printRemito(tipo) {
  if (tipo == 1) {
    document.getElementById("ocultar").style.display = "none";
    document.getElementById("miVentana").style.display = "block";
    $("#modal-vistaprevia").modal("hide");
    setTimeout(
      "window.print();printRemito(2);document.title = 'Situación Cliente'",
      500
    );
  } else {
    document.getElementById("ocultar").style.display = "block";
    document.getElementById("miVentana").style.display = "none";
    document.title = "Situación Cliente";
  }
}

function modificarPorcentaje(id_usuario, ultimoPorcentaje) {
  $("#input-porcentaje").val(ultimoPorcentaje && ultimoPorcentaje.length ? ultimoPorcentaje : "");
  $("#modal-modificar-porcentaje").modal("show");
  $("#input-porcentaje").focus();

  $("#modal-modificar-porcentaje").attr("x-id-usuario", id_usuario);
}

function guardarPorcentaje(){
  const porcentaje = $("#input-porcentaje").val().trim();
  if (!porcentaje || !porcentaje.length || parseInt(porcentaje) <= 0 || parseInt(porcentaje) > 99){
    swal("Ingresa el Porcentaje de Comisión", "", "error")
    return;
  }

  const id_usuario = $("#modal-modificar-porcentaje").attr("x-id-usuario");
  const anio = $("#select-anio option:selected").val();
  const mes = $("#select-mes option:selected").val();
  $("#modal-modificar-porcentaje").modal("hide");
  $.ajax({
    url: "data_ver_comisiones.php",
    type: "POST",
    data: {
      consulta: "guardar_porcentaje",
      id_usuario: id_usuario,
      mes: mes,
      anio: anio,
      porcentaje: porcentaje
    },
    success: function (x) {
      if (x.includes("success")){
        swal("Actualizaste la Comisión del Usuario correctamente", "", "success");
        loadComisiones();
      }
      else{
        swal("Ocurrió un error", x, "error");
        $("#modal-modificar-porcentaje").modal("show");
      }
    },
    error: function (jqXHR, estado, error) {
      swal(
        "Ocurrió un error al guardar el Porcentaje",
        error.toString(),
        "error"
      );
      $("#modal-modificar-porcentaje").modal("show");
    },
  });
}