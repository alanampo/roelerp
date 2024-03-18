let hasChanged = false;
let currentCAF = null;
let currentCERT = null;

$(document).ready(function () {
  currentCAF = null;
  currentCERT = null;
  document.getElementById("defaultOpen").click();
  
  

  $(".datepicker").datepicker({
    language: "es",
    autoclose: true,
    format: "dd/mm/yyyy",
  });

  $("#input-acteco,#input-num-resolucion").on(
    "propertychange input",
    function (e) {
      this.value = this.value.replace(/\D/g, "");
    }
  );

  $("#input-rut")
    .keypress(function (e) {
      var allowedChars = new RegExp("^[0-9-kK]+$");
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (allowedChars.test(str)) {
        return true;
      }
      e.preventDefault();
      return false;
    })
    .keyup(function () {
      // the addition, which whill check the value after a keyup (triggered by Ctrl+V)
      // We take the same regex as for allowedChars, but we add ^ after the first bracket : it means "all character BUT these"
      var forbiddenChars = new RegExp("[^0-9-kK]", "g");
      if (forbiddenChars.test($(this).val())) {
        $(this).val($(this).val().replace(forbiddenChars, ""));
      }
    });
    
    pone_comunas();
    
  
    $("#img-canal").on('click', function () {
      $("#logo-upload").click();
    });
  
    $("#logo-upload").on('change', function () {
      readURL(this);
    });
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
  if (tabName == "datos") {
    $(".tab-datos").addClass("d-block").removeClass("d-none");
    pone_comunas();
  } else if (tabName == "caf") {
    $(".tab-caf").addClass("d-block").removeClass("d-none");
    loadHistorialCAF();
  }
}

function setChanged(value) {
  hasChanged = value;
  if (value && $("#btn-guardar-cambios").hasClass("d-none")) {
    $("#btn-guardar-cambios").removeClass("d-none");
  } else if (!value && !$("#btn-guardar-cambios").hasClass("d-none")) {
    $("#btn-guardar-cambios").addClass("d-none");
  }
}

function modalCAF() {
  currentCAF = null;
  clearInput();
  $("#modal-caf").modal("show");
}

function subirCAF() {
  if (document.getElementById("input-caf").files.length == 0 || !currentCAF) {
    return;
  }

  const file = document.getElementById("input-caf").files[0];
  const fileSize = file.size / 1024; // in MiB
  if (fileSize > 2) {
    swal("El archivo no puede pesar más de 2 KB", "", "error");
    return;
  }

  if (!file.type.includes("xml") || !file.name.includes(".xml")) {
    swal("El archivo debe ser XML", "", "error");
    return;
  }

  $("#modal-caf").modal("hide");
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_integracion.php",
    type: "POST",
    data: {
      consulta: "subir_caf",
      data: JSON.stringify(currentCAF),
      codigo: currentCAF.data,
      //tipoDocumento: tipoDocumento
    },
    success: function (x) {
      if (x.includes("success")) {
        swal("Cargaste el archivo CAF correctamente!", "", "success");
        loadHistorialCAF();
      } else {
        swal("Ocurrió un error al cargar el CAF", x, "error");
      }
      console.log(x);
    },
    error: function (jqXHR, estado, error) {},
  });
}

function loadHistorialCAF() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas_caf").html("Buscando, espere...");
    },
    url: "data_ver_integracion.php",
    type: "POST",
    data: {
      consulta: "cargar_historial_caf",
    },
    success: function (x) {
      $("#tabla_entradas_caf").html(x);
      $("#tabla_caf").DataTable({
        pageLength: 50,
        order: [[0, "desc"]],
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
          zeroRecords: "No se encontraron registros",
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
      $("#tabla_entradas_caf").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function eliminarCAF(rowid) {
  swal("Estás seguro/a de ELIMINAR el CAF?", "", {
    icon: "warning",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, ELIMINAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_integracion.php",
          data: { consulta: "eliminar_caf", rowid: rowid },
          success: function (data) {
            if (data.trim().includes("usado")) {
              swal(
                "No es posible eliminar el CAF",
                "Ya fue utilizado en alguna Factura/Nota de Crédito.",
                "error"
              );
            } else if (data.trim() == "success") {
              swal("Eliminaste el CAF correctamente!", "", "success");
              loadHistorialCAF();
            } else {
              swal("Ocurrió un error al eliminar el CAF", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

async function pone_comunas() {
  let id_usuario;
  await $.get(
    "get_session_variable.php",
    { requested: "id_usuario" },
    function (data) {
      if (data.trim().length) {
        id_usuario = data.trim();
        if (!id_usuario || !id_usuario.length || parseInt(id_usuario) != 1){
          $(".modo-servidor-wrapper").remove()
        }
      }
    }
  );


  $.ajax({
    beforeSend: function () {
      $("#select-comuna").html("Cargando lista de comunas...");
    },
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: { consulta: "pone_comunas" },
    success: function (x) {
      $("#select-comuna").html(x).selectpicker("refresh");
      loadDatosempresa();
    },
    error: function (jqXHR, estado, error) {},
  });
}

function guardarDatosEmpresa() {
  const rut = $("#input-rut").val().trim();
  const direccion = $("#input-direccion").val().trim();
  const razon = $("#input-razon").val().trim();
  const telefono = $("#input-telefono").val().trim();
  const email = $("#input-email").val().trim();
  const giro = $("#input-giro").val().trim();
  const actEco = $("#input-acteco").val().trim();
  const fechaRes = $("#input-fecha-resolucion").val().trim();
  const numRes = $("#input-num-resolucion").val().trim();
  const comuna = $("#select-comuna option:selected").val();
  const pass = $("#input-pass-certificado").val().trim();
  
  const inputFile = document.getElementById("input-certificado");
  const fileSize =
    inputFile && inputFile.files && inputFile.files[0]
      ? inputFile.files[0].size / 1024
      : null;

  if (!razon || razon.length <= 3) {
    swal("Ingresa la Razón Social", "", "error");
  } else if (!checkRut(rut)) {
    swal("El RUT ingresado no es válido", "", "error");
  } else if (!direccion || direccion.length <= 3) {
    swal("Ingresa la Dirección de la Empresa", "", "error");
  } else if (!comuna || !comuna.length) {
    swal("Selecciona la Comuna", "", "error");
  } else if (!telefono || !telefono.length) {
    swal("Ingresa el Teléfono de la Empresa", "", "error");
  } else if (!email || !email.length) {
    swal("Ingresa el E-Mail de la Empresa", "", "error");
  } else if (!validateEmail(email)) {
    swal("El E-Mail de la Empresa es inválido", "", "error");
  } else if (!giro || !giro.length) {
    swal(
      "Ingresa el Giro de la Empresa",
      "Ejemplo: Cultivo de Plantas Vivas y Viveros",
      "error"
    );
  } else if (!actEco || !actEco.length || parseInt(actEco) < 1) {
    swal(
      "Ingresa el Código de la Actividad Económica de la Empresa",
      "Haz click en el botón '?' para ver todos los códigos existentes.",
      "error"
    );
  } else if (!fechaRes || !fechaRes.length) {
    swal("Selecciona la fecha de Resolución", "", "error");
  } else if (!numRes || !numRes.length) {
    swal("Ingresa el Número de Resolución", "", "error");
  } else if (
    document.getElementById("input-certificado").files.length > 0 &&
    fileSize &&
    fileSize > 5
  ) {
    swal("El archivo no puede pesar más de 5 KB", "", "error");
  } else if (
    fileSize &&
    !inputFile.files[0].type.includes("p12") &&
    !inputFile.files[0].name.includes(".p12") &&
    !inputFile.files[0].type.includes("pfx") &&
    !inputFile.files[0].name.includes(".pfx")
  ) {
    swal("El archivo debe ser .p12 o .pfx", "", "error");
  }
  else if (document.getElementById("input-certificado").files.length > 0 && (!pass || !pass.length)){
    swal("Ingresa la contraseña del Certificado", "", "error")
  }
  else {
    $("#btn-guardar-datos").prop("disabled", true);
    $.ajax({
      url: "data_ver_integracion.php",
      type: "POST",
      data: {
        consulta: "guardar_datos",
        rut: rut,
        direccion: direccion,
        razon: razon,
        telefono: telefono,
        email: email,
        giro: giro,
        actEco: actEco,
        fechaRes: fechaRes,
        numRes: numRes,
        comuna: comuna,
        certificado: currentCERT ? currentCERT : null,
        pass: document.getElementById("input-certificado").files.length > 0 ? pass : null
      },
      success: function (x) {
        $("#btn-guardar-datos").prop("disabled", false);
        if (x.includes("success")) {
          swal(
            "Guardaste los Datos de la Empresa correctamente!",
            "",
            "success"
          );
          setTimeout(()=>location.reload(),1000)
          
        } else {
          swal("Ocurrió un error", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {
        $("#btn-guardar-datos").prop("disabled", false);
      },
    });
  }
}

function isValidDate(s) {
  // Assumes s is "mm/dd/yyyy"
  if (!/^\d\d\/\d\d\/\d\d\d\d$/.test(s)) {
    return false;
  }
  const parts = s.split("/").map((p) => parseInt(p, 10));
  parts[0] -= 1;
  const d = new Date(parts[2], parts[1], parts[0]);
  return (
    d.getMonth() === parts[1] &&
    d.getDate() === parts[0] &&
    d.getFullYear() === parts[2]
  );
}

function validateEmail(email) {
  let re = /\S+@\S+\.\S+/;
  return re.test(email);
}

function onInput(input) {
  const reader = new FileReader();
  reader.readAsDataURL(input.files[0]);
  reader.onload = () => {
    currentCERT = reader.result;
  };
  reader.onerror = () => {
    currentCERT = null;
  };
}

function loadDatosempresa() {
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_integracion.php",
    type: "POST",
    data: { consulta: "load_datos_empresa" },
    success: function (x) {
      if (x.length) {
        try {
          const data = JSON.parse(x);
          if (data && data.rut) {
            const {
              rut,
              direccion,
              razon,
              telefono,
              email,
              giro,
              actEco,
              fechaRes,
              numRes,
              comuna,
              certificado,
              logo,
              modo,
              footer1,
              footer2
            } = data;

            if (logo && logo.length){
              $("#img-canal").attr("src", logo)
            }
            else{
              $("#img-canal").attr("src", "dist/img/noimage.jpg")
            }

            $("#input-rut").val(rut);
            $("#input-direccion").val(direccion);
            $("#input-razon").val(razon);
            $("#input-telefono").val(telefono);
            $("#input-email").val(email);
            $("#input-giro").val(giro);
            $("#input-acteco").val(actEco);
            $("#input-fecha-resolucion").val(fechaRes);
            $("#input-num-resolucion").val(numRes);

            $("#input-footer1").val(footer1);
            $("#input-footer2").val(footer2);
            
            $("#select-comuna").val(comuna).selectpicker("refresh");

            if (certificado){
              $("#input-certificado").addClass("d-none")
              $("#btn-eliminar-certificado").removeClass("d-none")
              $(".form-pass").addClass("d-none")
            }
            else{
              $("#input-certificado").removeClass("d-none")
              $("#btn-eliminar-certificado").addClass("d-none")
              $(".form-pass").removeClass("d-none")
            }

            if (modo && modo == "PROD"){
              $("#select-modo").val("1")
            }
            else{
              $("#select-modo").val("0")
            }

            $(".datos-wrapper").removeClass("d-none");
            $(".loading-integracion").addClass("d-none")
          }
        } catch (error) {
          swal(
            "Ocurrió un error al cargar los datos de la Empresa",
            "Actualiza la página",
            "error"
          );
          console.log(error)
        }
      }
      else{
        $(".datos-wrapper").removeClass("d-none");
        $(".loading-integracion").addClass("d-none")
      }
    },
    error: function (jqXHR, estado, error) {
      $(".datos-wrapper").removeClass("d-none");
      $(".loading-integracion").addClass("d-none")
      console.log(error)
    },
  });
}

function eliminarCertificado() {
  swal("Estás seguro/a de ELIMINAR el Certificado?", "", {
    icon: "warning",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, ELIMINAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_integracion.php",
          data: { consulta: "eliminar_certificado"},
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste el Certificado correctamente!", "", "success");
              setTimeout(()=>location.reload(), 1000);
            } else {
              swal("Ocurrió un error al eliminar el Certificado", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function readURL(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();

    reader.onload = function (e) {
      $('#verificar-subida-logo').attr('src', e.target.result);
      $("#verificar-subida-logo").cropper("destroy");
      $("#verificar-subida-logo").cropper({
        dragMode: "move",
        minContainerWidth: 300,
        minContainerHeight: 300,
        minCanvasWidth: 250,
        minCanvasHeight: 250,
        minCropBoxWidth: 250,
        minCropBoxHeight: 200,
        cropBoxResizable: false,
        ready: function () {
          $("#img-logo").cropper("setCropBoxData", { width: 250, height: 200 });
        },
      });

      $("#modalUploadLogo").modal("show");

      $("#btn-subir-logo").on("click", function (e) {
        e.preventDefault();

        if ($("#logo-upload").prop('files')[0]) {
          let canvas = $("#verificar-subida-logo").data("cropper").getCroppedCanvas({
            fillColor: "#ffffff",
            width: 250,
            height: 200,
          }).toDataURL();
          console.log(canvas)
          
          $("#modalUploadLogo").modal("hide");
          $.ajax({
            type: "POST",
            url: "data_ver_integracion.php",
            data: { 
              consulta: "subir_logo",
              data: canvas
            },
            success: function (data) {
              if (data.trim() == "success") {
                swal("Subiste el Logo correctamente!", "", "success");
                setTimeout(()=>location.reload(), 1000);
              } 
              else if (data.includes()=="sindatos"){
                swal("Antes de cargar el Logo debes ingresar los Datos de la Empresa", "", "error");
              }
              else {
                swal("Ocurrió un error subir el Logo", data, "error");
              }
            },
          });
        }
      });
    }
    reader.readAsDataURL(input.files[0]);
  }
}

function guardarModo(){
  const tipoModo = $("#select-modo option:selected").val();
  if (!tipoModo || (tipoModo != 0 && tipoModo != 1)) return;

  swal(`Estás seguro/a de activar el modo ${tipoModo == 0 ? "Pruebas" : tipoModo == 1 ? "Producción" : ""}?`, "", {
    icon: "warning",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, ACTIVAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_integracion.php",
          data: { consulta: "cambiar_modo", tipoModo: tipoModo },
          success: function (data) {
            if (data.trim().includes("faltandatos")) {
              swal(
                "Los Datos de la Empresa están incompletos",
                "Aségurate de haber cargado el certificado de autenticación.",
                "error"
              );
            } else if (data.trim() == "success") {
              swal(`Activaste el Modo ${tipoModo == 0 ? "Pruebas" : tipoModo == 1 ? "Producción" : ""} correctamente!`, "", "success");
            } else {
              swal("Ocurrió un error al cambiar de Modo", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function guardarFooter(){
  const footer1 = $("#input-footer1").val().trim();
  const footer2 = $("#input-footer2").val().trim();
  
  $.ajax({
    type: "POST",
    url: "data_ver_integracion.php",
    data: { consulta: "guardar_footer", footer1: footer1, footer2: footer2 },
    success: function (data) {
      if (data.trim() == "success") {
        swal(`Guardaste el Pié de Página correctamente!`, "", "success");
      } 
      else if (data.trim() == "faltandatos"){
        swal("Primero debes ingresar los Datos de la Empresa!", "", "error")
      }
      else {
        swal("Ocurrió un error al guardar los Datos del Pié de Página", data, "error");
      }
    },
  });
}

function modalAnular(rowid, anulados, rangoD, rangoH){
  $("#select-folios").html("")
  for (let i = rangoD; i <= rangoH; i++){
    $("#select-folios").append(`
      <option value='${i}'>${i}</option>
    `)
  }
  if (anulados && anulados.length){
    const sel = anulados.split(", ");
    $("#select-folios").val(sel);
  }
  
  $("#select-folios").selectpicker("refresh");

  $("#modal-anular").attr("x-rowid", rowid)
  $("#modal-anular").modal("show");
}

function anularFolios(){
  const rowid = $("#modal-anular").attr("x-rowid");
  const seleccionados = $("#select-folios").val();

  $("#modal-anular").modal("hide")
  $.ajax({
    type: "POST",
    url: "data_ver_integracion.php",
    data: { 
      consulta: "anular_folios",
      rowid: rowid,
      seleccionados: seleccionados && seleccionados.length ? JSON.stringify(seleccionados) : ""
    },
    success: function (data) {
      if (data.trim() == "success") {
        swal(`Anulaste los Folios correctamente!`, "", "success");
        loadHistorialCAF();
      } 
      else {
        swal("Ocurrió un error al anular los Folios", data, "error");
        $("#modal-anular").modal("show")
      }
    },
  });
  
}