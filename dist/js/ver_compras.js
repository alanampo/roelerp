let currentCliente = null;
let currentTab = null;
let phpFileCompras = "class_lib/libredte/vendor/sasco/libredte/examples/data_ver_compras.php";
$(document).ready(function () {
  $("#input-monto").on("propertychange input", function (e) {
    this.value = this.value.replace(/\D/g, "");
  });

  $("#select-anio").html("").selectpicker("refresh");
  const anio = new Date().getFullYear();
  for (let i = 2022; i <= anio; i++) {
    $("#select-anio").append(`<option value="${i}">${i}</option>`);
  }
  $(".selectpicker").selectpicker("refresh");
  $("#select-anio").val(anio);
  const mes = new Date().getMonth() + 1;
  $("#select-mes").val(mes);
  $(".selectpicker").selectpicker("refresh");

  document.getElementById("defaultOpen").click();
  $("#select-anio,#select-mes").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      getComprasMensual();
    }
  );
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
  if (tabName == "mensual") {
    $(".tab-mensual").addClass("d-block");
    getComprasMensual();
  } else if (tabName == "historico") {
    $(".tab-historico").addClass("d-block");
    getComprasHistorico()
  }
  else if (tabName == "proveedores") {
    $(".tab-proveedores").addClass("d-block");
    graficoProveedores()
  }
  currentTab = tabName;
}


function getComprasMensual() {
  const anio = $("#select-anio option:selected").val();
  const mes = $("#select-mes option:selected").val();
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Obteniendo datos del SII, espere...");
    },
    url: phpFileCompras,
    type: "POST",
    data: { consulta: "get_compras", anio: anio, mes: mes },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[0, "desc"]],
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
      });
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function getComprasHistorico(isUpdating) {
  $.ajax({
    beforeSend: function () {
      $("#tabla_historico").html("Cargando, espere...");
    },
    url: phpFileCompras,
    type: "POST",
    data: { consulta: "get_historico_compras", isUpdating: isUpdating ? 1 : 0 },
    success: function (x) {
      $("#tabla_historico").html(x);

      $("#tabla_hist").DataTable({
        order: [[0, "desc"]],
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
      });
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_historico").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function graficoProveedores() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_historico").html("Cargando, espere...");
    },
    url: phpFileCompras,
    type: "POST",
    data: { consulta: "get_grafico_proveedores" },
    success: function (x) {
     if (x.length) {
      const data = JSON.parse(x);
      chartProveedores(data);
     }
    },
    error: function (jqXHR, estado, error) {
      $("#tabla_historico").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
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

function getDetalle(data) {
  $.ajax({
    url: phpFileCompras,
    type: "POST",
    data: {
      consulta: "get_detalle_compra",
      ...data
    },
    success: function (x) {
      console.log(x)
    },
    error: function (jqXHR, estado, error) {
      swal(
        "Ocurrió un error",
        error.toString(),
        "error"
      );
    },
  });
}

function modalDetalle(data) {
  $("#modal-detalle-compra .table-wrapper").html("");

  $("#modal-detalle-compra").modal("show")
  const { rutDoc, dvDoc, dcvNroDoc, detFecRecepcion, detMntIVA, detMntTotal, descTipoTransaccion, rutReceptor, detRznSoc, detFchDoc } = data;

  const tabla = `<table class='table table-responsive w-100 d-block d-md-table' role='grid'>
                  <tbody>
                  <tr>
                    <td>RUT Emisor</td>
                    <td>${rutDoc}-${dvDoc}</td>
                  </tr>
                  <tr>
                    <td>Razón Social Emisor</td>
                    <td>${detRznSoc}</td>
                  </tr>
                  <tr>
                    <td>Tipo Documento</td>
                    <td>Factura Electrónica (33)</td>
                  </tr>
                  <tr>
                    <td>Folio Documento</td>
                    <td>${dcvNroDoc}</td>
                  </tr>
                  <tr>
                    <td>Fecha Emisión</td>
                    <td>${detFchDoc}</td>
                  </tr>
                  <tr>
                    <td>Rut Receptor</td>
                    <td>${rutReceptor}</td>
                  </tr>
                  <tr>
                    <td>IVA</td>
                    <td>$${detMntIVA.toFixed(2)}</td>
                  </tr>
                  <tr>
                    <td>Monto Total</td>
                    <td>$${detMntTotal.toFixed(2)}</td>
                  </tr>
                  <tr>
                    <td>Fecha Recepcion en SII</td>
                    <td>${detFecRecepcion}</td>
                  </tr>
                  <tr>
                    <td>Tipo Transacción</td>
                    <td>${descTipoTransaccion}</td>
                  </tr>
                  </tbody>
                </table>`;
  $("#modal-detalle-compra").find(".table-wrapper").html(tabla)
}


var myChart = null;
function chartProveedores(json) {
  $(".chart-container").css({ height: `400px`, overflowX: 'auto' });
  $(".chart-container").html(`
    <div class="filters">
      <select id="selectYear">
        <option value="">Año</option>
        ${generateYearOptions()}
      </select>
      <select id="selectMonth">
        <option value="">Mes</option>
        ${generateMonthOptions()}
      </select>
      <button class="btn btn-sm btn-danger" id="resetFilters"><i class="fa fa-times"></i></button>
    </div>
    <canvas id="myChart"></canvas>
  `);

  var ctx = document.getElementById("myChart").getContext("2d");
  var lineChartData = {
    labels: [],
    datasets: []
  };

  var originalData = json;

  function updateChart(year, month) {
    if (myChart !== null) {
      myChart.destroy();
    }
    var filteredData = originalData.filter(function(item) {
      var date = new Date(item.fecha);
      return (!year || date.getFullYear() === year) && (!month || date.getMonth() === month);
    });

    lineChartData = generateChartData(filteredData);

    myChart = new Chart(ctx, {
      type: "line",
      data: lineChartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        elements: {
          line: {
            tension: 0.3
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value, index, values) {
                return value.toLocaleString("es-CL", {
                  style: "currency",
                  currency: "CLP"
                });
              }
            }
          },
          x: {
            title: {
              display: true,
              text: "Proveedores"
            },
            ticks: {
              maxRotation: 90,
              minRotation: 45,
             
            }
          }
        }
      }
    });
  }

  function generateChartData(data) {
    var labels = [];
    var datasetMontoTotal = {
      label: "Monto Total Facturas",
      borderColor: "rgb(255, 99, 132)",
      backgroundColor: "rgba(255, 99, 132, 0.2)",
      fill: false,
      data: []
    };

    var datasetSumaPagos = {
      label: "Suma de Pagos",
      borderColor: "rgb(54, 162, 235)",
      backgroundColor: "rgba(54, 162, 235, 0.2)",
      fill: false,
      data: []
    };

    var proveedorIndex = {};

    data.forEach(function(factura) {
      var proveedorId = factura.id_proveedor;
      var montoTotal = parseFloat(factura.montoTotal);
      var pagos = parseFloat(factura.pagos);

      if (proveedorIndex[proveedorId] !== undefined) {
        datasetMontoTotal.data[proveedorIndex[proveedorId]] += montoTotal;
        datasetSumaPagos.data[proveedorIndex[proveedorId]] += pagos;
      } else {
        proveedorIndex[proveedorId] = labels.length;
        labels.push(factura.razonSocial);
        datasetMontoTotal.data.push(montoTotal);
        datasetSumaPagos.data.push(pagos);
      }
    });

    return {
      labels: labels,
      datasets: [datasetMontoTotal, datasetSumaPagos]
    };
  }

  function generateYearOptions() {
    var currentYear = new Date().getFullYear();
    var options = '';
    for (var year = currentYear; year >= 2022; year--) {
      options += `<option value="${year}">${year}</option>`;
    }
    return options;
  }



  function generateMonthOptions() {
    var options = '';
    var months = [
      "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
      "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    for (var month = 0; month < months.length; month++) {
      options += `<option value="${month}">${months[month]}</option>`;
    }
    return options;
  }

  $("#selectYear, #selectMonth").on("change", function() {
    var year = parseInt($("#selectYear").val());
    var month = parseInt($("#selectMonth").val());
    updateChart(year, month);
  });

  $("#resetFilters").on("click", function() {
    $("#selectYear, #selectMonth").val("");
    updateChart(null, null);
  });

  updateChart(null, null);
}
