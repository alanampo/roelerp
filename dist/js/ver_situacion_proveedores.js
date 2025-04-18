let currentCliente = null;
let currentTab = null;
let phpFileCompras = "class_lib/libredte/vendor/sasco/libredte/examples/data_ver_compras.php";
const phpFile = "data_ver_situacion_proveedores.php";

const meses = [
  "ENERO",
  "FEBRERO",
  "MARZO",
  "ABRIL",
  "MAYO",
  "JUNIO",
  "JULIO",
  "AGOSTO",
  "SEPTIEMBRE",
  "OCTUBRE",
  "NOVIEMBRE",
  "DICIEMBRE",
];

$(document).ready(function () {
  document.getElementById("defaultOpen").click();
  
  busca_clientes();

  $("#select-anio").html("");
  const anio = new Date().getFullYear()+10;
  for (let i = 2024; i <= anio; i++) {
    $("#select-anio").append(`<option value="${i}">${i}</option>`);
  }
  $(".selectpicker").selectpicker("refresh");
  $("#select-anio").val(new Date().getFullYear());
  $(".selectpicker").selectpicker("refresh");
});

function abrirTab(evt, tabName) {
  var i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  $(".tabco").addClass("d-none").removeClass("d-block");

  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  evt.currentTarget.className += " active";
  if (tabName == "clientes") {
    $(".tab-clientes").addClass("d-block");
    busca_clientes();
  } else if (tabName == "porcobrar") {
    $("#select-anio").addClass("d-block").removeClass("d-none");
    $(".tab-graficos").addClass("d-block");
    $(".col-anio").css({display:"block"});
    graficoPorCobrar()
  }
  else if (tabName == "clientescondeuda") {
    $(".col-anio").css({display:"none"});
    $(".tab-graficos").addClass("d-block");
    graficoClientesConDeuda()
  }
  currentTab = tabName;
}

function loadData(){
  if (currentTab == "clientes") {
    $(".tab-clientes").addClass("d-block");
    busca_clientes();
  } else if (currentTab == "porcobrar") {
    $(".tab-graficos").addClass("d-block");
    graficoPorCobrar()
  }
  else if (currentTab == "clientescondeuda") {
    $(".tab-graficos").addClass("d-block");
    graficoClientesConDeuda()
  }
}

function busca_clientes() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando, espere...");
    },
    url: phpFile,
    type: "POST",
    data: { consulta: "get_situacion_proveedores" },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla_hist").DataTable({
        order: [[1, "asc"]],
        pageLength: 50,
        language: {
          lengthMenu: "Mostrando _MENU_ registros por página",
          zeroRecords: "No hay registros",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay registros",
          infoFiltered: "(filtrado de _MAX_ registros en total)",
          lengthMenu: "Mostrar _MENU_ registros",
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
        columnDefs: [
          {
              targets: 0, // Primera columna (índice 0)
              width: "250px", // Ancho máximo de 250px
          },
      ],
      });
      $(function () {
        $('[data-toggle="tooltip"]').tooltip()
      })
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
    url: phpFile,
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

  $(".print-cotizacion").append(`<h5 class='text-center mt-5 font-weight-bold'>Ficha Cliente - Últimas Facturas</h5>`);

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
      const {
        fecha,
        comentario,
        monto,
        sumapagos,
        id_factura,
        estado
      } = factura;
      
      $("#tabla_producto > tbody").append(
        `
          <tr class='text-center' ${estado == "ANU" ? "style='text-decoration: line-through;'" : ""}>
            <td>${id_factura}</td>
            <td>${fecha}</td>
            <td>${!estado ? "DESCONOCIDO" : estado == "ACEPTADO" ? "EMITIDA" : estado == "ANU" ? "ANULADA" : "DESCONOCIDO"}</td>
            <td>$${formatearMonto(monto)}</td>
            <td class='text-${(monto - sumapagos) < 0 ? "success" : "danger"}'>$${(monto - sumapagos) < 0 ? 0 : formatearMonto(monto - sumapagos)}</td>
          </tr>                    
        `
      );
    });
  }  
  else{
    $("#tabla_producto > tbody").append(
        `
          <tr class='text-center'>
            <td colspan='5'><h6>El cliente no posee facturas emitidas.</h6></td>
          </tr>                    
        `
      );
  }
}

function printFicha(){
    $("#miVentana").html($(".print-cotizacion").html());
    printRemito(1)
}

function printRemito(tipo) {
    if (tipo == 1) {
      document.getElementById("ocultar").style.display = "none";
      document.getElementById("miVentana").style.display = "block";
      $("#modal-vistaprevia").modal("hide")
      setTimeout("window.print();printRemito(2);document.title = 'Situación Cliente'", 500);
    } else {
      document.getElementById("ocultar").style.display = "block";
      document.getElementById("miVentana").style.display = "none";
      document.title = "Situación Cliente";
    }
}

function graficoPorCobrar() {
  const anio = $("#select-anio option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "grafico_por_cobrar",
      anio: anio,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          chartPorCobrar(data);
        } catch (error) {
          console.log(error);
          $(".chart-container").html(
            `<div class='callout callout-danger'><b>No se encontraron datos en las fechas indicadas...</b></div>`
          );
        }
      } else {
        $(".chart-container").html(
          `<div class='callout callout-danger'><b>No se encontraron datos en las fechas indicadas...</b></div>`
        );
        $(".label-estadisticas").html("");
      }
    },
    error: function (jqXHR, estado, error) {
      $(".chart-container").html(
        `<div class='callout callout-danger'><b>Ocurrió un error... ${error}</b></div>`
      );
    },
  });
}

function chartPorCobrar(json) {
  $(".chart-container").css({ height: `` });
  $(".chart-container").html(`<canvas id="myChart"></canvas>`);
  var ctx = document.getElementById("myChart").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 1; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const color = line === 0 ? "255, 50, 0" : "50, 255, 0";
    dataset.backgroundColor = `rgba(${color},1)`;
    dataset.borderColor = `rgba(${color},1)`;
    dataset.strokeColor = `rgba(${color},1)`;
    dataset.data = []; //contains the 'Y; axis data
    
    for (x = 0; x < 12; x++) {
      if (line === 0 && json){
        y.push(Math.round(json[x] < 0 ? 0 : json[x])); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(meses[x]); //adds x axis labels
    } //for x
    if (json){
      lineChartData.datasets[line].label = "Deuda en Pesos Chilenos";
      lineChartData.datasets[line].data = y; //send new line data to dataset
    }
  } //for line

  var myChart = new Chart(ctx, {
    type: "line",
    data: lineChartData,
    options: {
      indexAxis: "x",
      responsive: true,
      maintainAspectRatio: false,
      elements: {
        line: {
            tension : 0.3  // smooth lines
        },
    },
      scales: {
        y: {
          beginAtZero: true,
        },
        xAxes: [
          {
            maxBarThickness: 10,
          },
        ],
      },
    },
  });

  $(".chart-container").removeClass("d-none");
}

function graficoClientesConDeuda() {
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "grafico_clientes_deudores",
      
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          chartDeudores(data);
        } catch (error) {
          console.log(error);
          $(".chart-container").html(
            `<div class='callout callout-danger'><b>No se encontraron datos en las fechas indicadas...</b></div>`
          );
        }
      } else {
        $(".chart-container").html(
          `<div class='callout callout-danger'><b>No se encontraron datos en las fechas indicadas...</b></div>`
        );
        $(".label-estadisticas").html("");
      }
    },
    error: function (jqXHR, estado, error) {
      $(".chart-container").html(
        `<div class='callout callout-danger'><b>Ocurrió un error... ${error}</b></div>`
      );
    },
  });
}


function chartDeudores(json) {
  $(".chart-container").css({ height: `` });
  $(".chart-container").html(`<canvas id="myChart"></canvas>`);
  var ctx = document.getElementById("myChart").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 1; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const color = line === 0 ? "255, 50, 0" : "50, 255, 0";
    dataset.backgroundColor = `rgba(${color},1)`;
    dataset.borderColor = `rgba(${color},1)`;
    dataset.strokeColor = `rgba(${color},1)`;
    dataset.data = []; //contains the 'Y; axis data
    
    for (x = 0; x < json.length; x++) {
      if (line === 0 && json){
        y.push(Math.round(json[x].deuda)); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(json[x].nombre_cliente+" ("+json[x].id_cliente+")"); //adds x axis labels
    } //for x
    if (json){
      lineChartData.datasets[line].label = "Deuda en Pesos Chilenos";
      lineChartData.datasets[line].data = y; //send new line data to dataset
    }
  } //for line

  var myChart = new Chart(ctx, {
    type: "line",
    data: lineChartData,
    options: {
      indexAxis: "x",
      responsive: true,
      maintainAspectRatio: false,
      elements: {
        line: {
            tension : 0.3  // smooth lines
        },
    },
      scales: {
        y: {
          beginAtZero: true,
        },
        xAxes: [
          {
            maxBarThickness: 10,
          },
        ],
      },
    },
  });

  $(".chart-container").removeClass("d-none");
}

function detalleDeuda(id_cliente, nombre_cliente){
  if (nombre_cliente){
    $("#modal-detalle-deuda").find(".modal-title").html("Detalle Deuda "+nombre_cliente)
  }
  $("#modal-detalle-deuda").attr("x-id-cliente", id_cliente)
  $("#modal-detalle-deuda").attr("x-nombre-cliente", nombre_cliente)
  $("#modal-detalle-deuda").modal("show")
  $.ajax({
    beforeSend: function () {
      $(".detalle-deuda").html("Cargando, espere...");
    },
    url: phpFile,
    type: "POST",
    data: { consulta: "cargar_detalle_deuda", id_cliente: id_cliente, razonSocial: nombre_cliente },
    success: function (x) {
      $(".detalle-deuda").html(x);
    },
    error: function (jqXHR, estado, error) {
      $(".detalle-deuda").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

