function agregarPago(rowid, folio, monto) {
    $("#modal-pago").modal("show");
    $("#btn-guardar-pago").prop("disabled", false);
    $("#modal-pago").attr("x-id", rowid);
    $("#modal-pago").attr("x-monto", monto);
  
    $("#modal-pago .num-factura").html("Agregar Pago Factura N° " + folio);
  
    $("#modal-pago input").val("");
    loadPagos(rowid);
    $("#modal-pago").modal("show");
    $("#input-monto").focus();
  }
  
  function guardarPago() {
    const monto = $("#input-monto").val().trim();
    const comentario = $("#input-comentario-pago").val().trim();
    const facturaID = $("#modal-pago").attr("x-id");
  
    if (!monto.length || isNaN(monto) || parseInt(monto) <= 0) {
      swal("Ingresa el monto del Pago", "", "error");
      return;
    }
  
    $("#btn-guardar-pago").prop("disabled", true);
    $.ajax({
      type: "POST",
      url: phpFileCompras,
      data: {
        consulta: "guardar_pago",
        monto: parseInt(monto),
        comentario: comentario.length ? comentario : null,
        facturaID: facturaID
      },
      success: function (x) {
        if (x.includes("success")) {
          loadPagos(facturaID);
          swal("Agregaste el Pago correctamente!", "", "success");
          if (location.href.includes("ver_compras")) {
            getComprasHistorico();
          }
          else if (location.href.includes("ver_situacion_proveedores")) {
            detalleDeuda($("#modal-detalle-deuda").attr("x-id-cliente"), $("#modal-detalle-deuda").attr("x-nombre-cliente"));
            busca_clientes();
          }
  
          $("#modal-pago input").val("");
          $("#btn-guardar-pago").prop("disabled", false);
        } else {
          swal("Ocurrió un error al guardar el Pago", x, "error");
          $("#btn-guardar-pago").prop("disabled", false);
        }
      },
      error: function (jqXHR, estado, error) { },
    });
  }
  
  function loadPagos(facturaID) {
    $("#tabla-pagos > tbody").html("");
    const cotEspecial = $("#modal-pago").attr("x-especial");
    $.ajax({
      type: "POST",
      url: phpFileCompras,
      data: {
        consulta: "get_pagos",
        facturaID: facturaID,
        cotEspecial: cotEspecial
      },
      success: function (x) {
  
        $("#tabla-pagos > tbody").html(x);
        calcularMontos();
      },
      error: function (jqXHR, estado, error) { },
    });
  }
  
  function eliminarPago(rowid, facturaID) {
    const cotEspecial = $("#modal-pago").attr("x-especial");
    if (cotEspecial == true || cotEspecial == "true") {
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
            url: phpFileCompras,
            data: { consulta: "eliminar_pago", rowid: rowid },
            success: function (data) {
              if (data.trim() == "success") {
                swal("Eliminaste el Pago correctamente!", "", "success");
                loadPagos(facturaID);
                if (location.href.includes("ver_compras")) {
                  getComprasHistorico();
                }
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
  