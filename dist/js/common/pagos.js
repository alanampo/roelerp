let currentComprobante = null;

$(document).ready(function () {
    $("#input-monto").on("propertychange input", function (e) {
      this.value = this.value.replace(/\D/g, "");
    });

    $("#input-comprobante").on('change', function () {
      readURL(this);
    });
  });

function agregarPago(rowid, folio, monto, cotEspecial) {
    currentComprobante = null;
    
    $("#btn-guardar-pago").prop("disabled", false);
    $("#modal-pago").attr("x-id", rowid);
    $("#modal-pago").attr("x-monto", monto);
    if (!cotEspecial){
      $("#modal-pago .num-factura").html("Agregar Pago Factura N° " + folio);
      $("#modal-pago").attr("x-especial", false);
    }
    else{
      $("#modal-pago .num-factura").html("Agregar Pago Cot. N° " + rowid);
      $("#modal-pago").attr("x-especial", true);
    }
    
    $("#modal-pago input").val("");
    loadPagos(rowid);
    $("#modal-pago").modal("show");
    $("#input-monto").focus();
  }
  
  function guardarPago() {
    const monto = $("#input-monto").val().trim();
    const comentario = $("#input-comentario-pago").val().trim();
    const facturaID = $("#modal-pago").attr("x-id");

    const cotEspecial = $("#modal-pago").attr("x-especial");
  
    if (!monto.length || isNaN(monto) || parseInt(monto) <= 0) {
      swal("Ingresa el monto del Pago", "", "error");
      return;
    }

    if (document.getElementById("input-comprobante").files.length > 0 || currentComprobante) {
      const file = document.getElementById("input-comprobante").files[0];
      const fileSize = file.size / 1024; // in MiB
      if (fileSize > 2048) {
        swal("El archivo no puede pesar más de 2 MB", "", "error");
        return;
      }
    }
  
    $("#btn-guardar-pago").prop("disabled", true);
    $.ajax({
      type: "POST",
      url: "data_ver_facturacion.php",
      data: {
        consulta: "guardar_pago",
        monto: parseInt(monto),
        comentario: comentario.length ? comentario : null,
        facturaID: facturaID,
        comprobante: currentComprobante,
        cotEspecial: cotEspecial
      },
      success: function (x) {
        if (x.includes("success")) {
          loadPagos(facturaID);
          swal("Agregaste el Pago correctamente!", "", "success");
          if (location.href.includes("ver_facturacion")){
            loadHistorial();
          }
          else if (location.href.includes("ver_situacion")){
            busca_clientes();
            detalleDeuda($("#modal-detalle-deuda").attr("x-id-cliente"))
          }
          
          $("#modal-pago input").val("");
          $("#btn-guardar-pago").prop("disabled", false);
        } else {
          swal("Ocurrió un error al guardar el Pago", x, "error");
          $("#btn-guardar-pago").prop("disabled", false);
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
  
  function loadPagos(facturaID) {
    $("#tabla-pagos > tbody").html("");
    const cotEspecial = $("#modal-pago").attr("x-especial");
    $.ajax({
      type: "POST",
      url: "data_ver_facturacion.php",
      data: {
        consulta: "get_pagos",
        facturaID: facturaID,
        cotEspecial: cotEspecial
      },
      success: function (x) {
        
        $("#tabla-pagos > tbody").html(x);
        calcularMontos();
      },
      error: function (jqXHR, estado, error) {},
    });
  }
  
  function eliminarPago(rowid, facturaID) {
    const cotEspecial = $("#modal-pago").attr("x-especial");
    if (cotEspecial == true || cotEspecial == "true"){
      facturaID = rowid;
    }
    swal("Estás seguro/a de ELIMINAR el Pago?", "", {
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
            url: "data_ver_facturacion.php",
            data: { consulta: "eliminar_pago", rowid: rowid },
            success: function (data) {
              if (data.trim() == "success") {
                swal("Eliminaste el Pago correctamente!", "", "success");
                loadPagos(facturaID);
              } else {
                swal("Ocurrió un error al eliminar el Pago", data, "error");
              }
            },
          });
  
          break;
  
        default:
          break;
      }
    });
  }
  
  function calcularMontos() {
    const totalFactura = parseInt($("#modal-pago").attr("x-monto"));
  
    let sumaPagos = 0;
    $("#tabla-pagos tr").each(function (i, tr) {
      if ($(tr).attr("x-monto") && $(tr).attr("x-monto").length) {
        sumaPagos += parseInt($(tr).attr("x-monto"));
      }
    });
  
    let debe = totalFactura - sumaPagos;
  
    $("#tabla-montos > tbody").html(`
      <tr class='text-center'>
        <td>$${formatearMonto(totalFactura)}</td>
        <td>$${formatearMonto(sumaPagos)}</td>
        <td class='text-${debe <= 0 ? "success" : "danger"}'>$${formatearMonto(
      debe < 0 ? 0 : debe
    )}</td>
      </tr>
    `);
  }

  function readURL(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
  
      reader.onload = function (e) {
        currentComprobante = e.target.result;
        console.log(e.target.result)
      }
      reader.readAsDataURL(input.files[0]);
    }
    else{
      currentComprobante = null;
    }
  }
  
function verComprobante(id, obj){
  $(obj).prop("disabled", true)
  $.ajax({
    type: "POST",
    url: "data_ver_facturacion.php",
    data: { consulta: "get_data_comprobante", id: id },
    success: function (data) {
      console.log(data)
      if (data.includes("data:")) {
        $(obj).prop("disabled", false)
        debugBase64(data)

      } else {
        swal("Ocurrió un error al Obtener el Comprobante", data, "error");
        $(obj).prop("disabled", false)
      }
    },
  });
}

function debugBase64(base64URL){
  var win = window.open();
  win.document.write('<iframe src="' + base64URL  + '" frameborder="0" style="border:0; top:0px; left:0px; bottom:0px; right:0px; width:100%; height:100%;" allowfullscreen></iframe>');
}