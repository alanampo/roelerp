let currentTab;
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
  $(".selectpicker").selectpicker();
  $("#select-anio").html("");
  const anio = new Date().getFullYear();
  for (let i = 2022; i <= anio; i++) {
    $("#select-anio").append(`<option value="${i}">${i}</option>`);
  }
  $(".selectpicker").selectpicker("refresh");
  $("#select-anio").val(anio);
  
  
  //$("#select-mes").val("0");
  $(".selectpicker").selectpicker("refresh");
  if (document.location.href.includes("ver_estadisticas")) {
    document.getElementById("defaultOpen").click();
  }
});

function abrirTab(evt, tabName) {
  let i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  currentTab = tabName;
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  //document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  if (tabName == "facturadodiario"){
    $("#select-mes").val((new Date().getMonth() + 1).toString()).selectpicker("refresh");
  }
  busca_entradas(tabName);
}

function loadData(val) {
  if (currentTab == "lineal") {
    loadGraficoLineal();
  }
  else if (currentTab == "facturadomensual") {
    loadGraficoFacturadoMensual();
  }
  else if (currentTab == "facturadodiario") {
    loadGraficoFacturadoDiario();
  }
}

function busca_entradas(tabName) {
  $(".calendar-container,.col-variedad").addClass("d-none");

  if (tabName == "lineal") {
    $(".chart-container,.col-mes").addClass("d-none");
    loadGraficoLineal();
  } else if (tabName == "facturadomensual") {
    $(".chart-container,.col-mes").addClass("d-none");
    loadGraficoFacturadoMensual();
  }
  else if (tabName == "facturadodiario") {
    $(".chart-container").addClass("d-none");
    $(".col-mes").removeClass("d-none");
    loadGraficoFacturadoDiario();
  }
}

//GRAFICO LINEAL
function loadGraficoLineal() {
  const anio = $("#select-anio option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: "data_ver_estadisticas.php",
    type: "POST",
    data: {
      consulta: "stats_coti_fact",
      anio: anio,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          chartCotizacionesYFacturas(data);
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

function chartCotizacionesYFacturas(json) {
  $(".chart-container").css({ height: `` });
  $(".chart-container").html(`<canvas id="myChart"></canvas>`);
  var ctx = document.getElementById("myChart").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 2; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const randomBetween = (min, max) =>
      min + Math.floor(Math.random() * (max - min + 1));
    
    // const r = randomBetween(0, 255);
    // const g = randomBetween(0, 255);
    // const b = randomBetween(0, 255);
    const color = line === 0 ? "255, 50, 0" : "50, 255, 0";
    dataset.backgroundColor = `rgba(${color},1)`;
    dataset.borderColor = `rgba(${color},1)`;
    dataset.strokeColor = `rgba(${color},1)`;
    dataset.data = []; //contains the 'Y; axis data
    
    for (x = 0; x < 12; x++) {
      if (line === 0 && json["cotizaciones"]){
        y.push(json["cotizaciones"][x]); //push some data aka generate 4 distinct separate lines
      }
      else if (line === 1 && json["facturas"]){
        y.push(json["facturas"][x]); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(meses[x]); //adds x axis labels
    } //for x
    if (json["cotizaciones"]){
      lineChartData.datasets[line].label = line === 0 ? "Cotizaciones" : "Facturas";
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
//FIN GRAFICO LINEAL


function loadGraficoFacturadoMensual() {
  const anio = $("#select-anio option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: "data_ver_estadisticas.php",
    type: "POST",
    data: {
      consulta: "stats_facturado_mensual",
      anio: anio,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          chartFacturadoMensual(data);
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

function chartFacturadoMensual(json) {
  $(".chart-container").css({ height: `` });
  $(".chart-container").html(`<canvas id="myChart"></canvas>`);
  var ctx = document.getElementById("myChart").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 2; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const color = line === 0 ? "0, 50, 255" : "50, 255, 0";
    dataset.backgroundColor = `rgba(${color},1)`;
    dataset.borderColor = `rgba(${color},1)`;
    dataset.strokeColor = `rgba(${color},1)`;
    dataset.data = []; //contains the 'Y; axis data
    
    for (x = 0; x < 12; x++) {
      if (line === 0 && json["bruto"]){
        y.push(Math.round(json["bruto"][x])); //push some data aka generate 4 distinct separate lines
      }
      else if (line === 1 && json["neto"]){
        y.push(Math.round(json["neto"][x])); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(meses[x]); //adds x axis labels
    } //for x
    if (json["bruto"]){
      lineChartData.datasets[line].label = line === 0 ? "Bruto (C/ IVA)" : "Neto (S/ IVA)";
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
//FIN GRAFICO LINEAL


function loadGraficoFacturadoDiario() {
  const anio = $("#select-anio option:selected").val();
  const mes = $("#select-mes option:selected").val();
  $.ajax({
    beforeSend: function () {
      $(".chart-container").html("<h4 class='ml-1'>Buscando, espere...</h4>");
    },
    url: "data_ver_estadisticas.php",
    type: "POST",
    data: {
      consulta: "stats_facturado_diario",
      anio: anio,
      mes: mes
    },
    success: function (x) {
      console.log(x);
      
      if (x.length) {
        try {
          const data = JSON.parse(x);
          //console.log(data)
          chartFacturadoDiario({
            data: data,
            mes: mes
          });
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

function chartFacturadoDiario(json) {
  $(".chart-container").css({ height: `` });
  $(".chart-container").html(`<canvas id="myChart"></canvas>`);
  var ctx = document.getElementById("myChart").getContext("2d");

  lineChartData = {}; //declare an object
  lineChartData.labels = []; //add 'labels' element to object (X axis)
  lineChartData.datasets = []; //add 'datasets' array element to object
  for (line = 0; line < 2; line++) {
    y = [];
    lineChartData.datasets.push({}); //create a new line dataset
    dataset = lineChartData.datasets[line];
    const color = line === 0 ? "0, 50, 255" : "50, 255, 0";
    dataset.backgroundColor = `rgba(${color},1)`;
    dataset.borderColor = `rgba(${color},1)`;
    dataset.strokeColor = `rgba(${color},1)`;
    dataset.data = []; //contains the 'Y; axis data
    
    for (x = 1; x <= json.data.bruto.length; x++) {
      if (line === 0 && json.data.bruto){
        y.push(Math.round(json.data.bruto[x-1])); //push some data aka generate 4 distinct separate lines
      }
      else if (line === 1 && json.data.neto){
        y.push(Math.round(json.data.neto[x-1])); //push some data aka generate 4 distinct separate lines
      }
      if (line === 0) lineChartData.labels.push(`${x}/${json.mes}`); //adds x axis labels
    } //for x
    if (json.data.bruto){
      lineChartData.datasets[line].label = line === 0 ? "Bruto (C/ IVA)" : "Neto (S/ IVA)";
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
//FIN GRAFICO LINEAL
