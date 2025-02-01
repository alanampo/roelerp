let productosCotizados = [];
let productoIndex = 0;
let hasChanged = false;
let currentCotizacion;
let currentTab = null;

$(document).on("input", ".numeric", function() {
  this.value = this.value.replace(/\D/g,'');
});

$(document).ready(function () {
  productosCotizados = [];
  document.getElementById("defaultOpen").click();
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
  currentTab = tabName;
  if (tabName == "historial") {
    $(".tab-historial").addClass("d-block");
    loadHistorial();
  } else if (tabName == "notas") {
    $(".tab-notas").addClass("d-block");
    loadNotas();
  } else if (tabName == "notas-debito") {
    $(".tab-notas-debito").addClass("d-block");
    loadNotasDebito();
  } else if (tabName == "guias") {
    $(".tab-guias").addClass("d-block");
    loadGuias();
  } else if (tabName == "cotizaciones") {
    $(".tab-cotizaciones").addClass("d-block");
    loadHistorialCotizaciones();
  }
}

//************************* */

async function printCotizacion(dataCotizacion, isPrinting) {
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
  const rutEmpresa = dataMembrete.rut;
  const giroEmpresa = dataMembrete.giro;
  const comunaEmpresa = dataMembrete.comuna;

  $(".print-cotizacion").html("");
  const {
    id_cotizacion,
    cliente,
    productos,
    comentario,
    domicilio,
    ciudad,
    comuna,
    condicion_pago,
    rut,
    giro,
    razon,
    esGuiaDespacho,
    esFactura,
    folio,
    rowid_factura,
    caf,
    id_guia,
  } = dataCotizacion;

  const condicion =
    condicion_pago == 0
      ? "CONTADO"
      : condicion_pago == 1
      ? "CRÉDITO 30 DÍAS"
      : condicion_pago == 2
      ? "CRÉDITO 60 DÍAS"
      : condicion_pago
      ? "CRÉDITO 90 DÍAS"
      : "";
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

  const vencimientotemp = moment().add(1, "months").calendar().split("/");
  const vencimiento =
    vencimientotemp[1] + "/" + vencimientotemp[0] + "/" + vencimientotemp[2];

  let nombre_real;
  await $.get(
    "get_session_variable.php",
    { requested: "nombre_real" },
    function (data) {
      if (data.trim().length) {
        nombre_real = data.trim();
      }
    }
  );

  let headerinfo = `
                <div class="row mt-4 p-0">
                <div class="col-md-8">
                  <div class="d-flex flex-row">
                    <img style="width: 170px !important; height: 110px !important" src="${logo}"></img>
                    <div class="ml-3">
                      <span class="info-plantinera"><b>${razonEmpresa}</b></span><br>
                      <span class="info-plantinera">${giroEmpresa}</span><br>
                      <span class="info-plantinera">${direccion}, ${comunaEmpresa}</span><br>
                      <span class="info-plantinera">Fono: ${telefono}</span><br>
                      <span class="info-plantinera">Email: ${email}</span>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                    <div style="border: 3px solid #FE9A2E !important;border-radius:10px; text-align: center;padding:5px;color:#F7BE81 !important;">
                        <h5 style="color:#F7BE81 !important;">R.U.T: ${rutEmpresa}</h5>
                        <h5 style="color:#F7BE81 !important;">${
                          esGuiaDespacho || esFactura ? "Factura" : "Cotización"
                        }</h5>
                        <h5 style="color:#F7BE81 !important;">N° <span id="id-cotizacion">${
                          folio ? folio : esGuiaDespacho ? "X" : id_cotizacion
                        }</span></h5>
                    </div>
                </div>
              </div>
              
              <div class="customrow pt-3 pb-2 mt-4" style="padding-left:60px;border-top: 1px solid #c9c9c9bd;border-bottom: 1px solid #c9c9c9bd;">
                <div class="column">
                  <div class="row">
                    <div class="col">
                      <h6 style="color:grey !important">Razón Social</h6>
                      <h6 style="">${
                        razon && razon.length ? razon.toUpperCase() : cliente
                      }</h6>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col">
                      <h6 style="color:grey !important">Dirección</h6>
                      <h6 style="">${domicilio}</h6>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col">
                      <h6 style="color:grey !important">Comuna</h6>
                      <h6 style="">${comuna ? comuna.toUpperCase() : "-"}</h6>
                    </div>
                  </div>
                </div>
                <div class="column">
                <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Ciudad</h6>
                  <h6 style="">${ciudad ? ciudad.toUpperCase() : "-"}</h6>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Condición de Pago</h6>
                  <h6 style="">${condicion}</h6>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Fecha Documento</h6>
                  <h6 style="">${datetime}</h6>
                </div>
              </div>
                </div>
                <div class="column">
                <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Giro</h6>
                  <h6 style="">${giro ? giro.toUpperCase() : "-"}</h6>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Vendedor/a</h6>
                  <h6 style="">${
                    nombre_real && nombre_real.length
                      ? nombre_real
                      : "Roelplant"
                  }</h6>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Fecha Vencimiento</h6>
                  <h6 style="">${vencimiento}</h6>
                </div>
              </div>
                </div>
                <div class="column">
                <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">R.U.T</h6>
                  <h6 style="">${rut && rut.length ? rut : "-"}</h6>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Tipo de Cambio</h6>
                  <h6 style="">PESO</h6>
                </div>
              </div>
              <div class="row">
                <div class="col">
                  <h6 style="color:grey !important">Tasa de Cambio</h6>
                  <h6 style="">1</h6>
                </div>
              </div>
                </div>
            </div>
            `;

  $(".print-cotizacion").append(headerinfo);

  const tabla = `<table style='width: 100%' id='tabla_producto' class='table table-bordered tableproductos mt-5 table-responsive w-100 d-block d-md-table' role='grid'>
                        <thead>
                        <tr role='row'>
                          <th class='text-center' style='width:65px'>Cód</th>
                          <th class='text-center'>Producto</th>
                          <th class='text-center' style='width:100px'>Cant</th>
                          <th class='text-center' style='width:100px'>P. Unitario</th>
                          <th class='text-center' style='width:100px'>Descuento</th>
                          <th class='text-center' style='width:130px'>Subtotal</th>
                          
                          </tr>
                        </thead>
                        <tbody>
                          
                        </tbody>
                      </table>`;
  $(".print-cotizacion").append(tabla);

  let monto = 0;
  let montodescuento = 0;

  if (productos) {
    productos.forEach(function (producto, i) {
      const {
        variedad,
        especie,
        codigo,
        cantidad,
        precio,
        total,
        subtotal,
        descuento,
      } = producto;
      const nombre_producto = `${variedad} ${especie ? especie : ""}`;
      $("#tabla_producto > tbody").append(`
        <tr>
          <td>${codigo}</td>
          <td>${nombre_producto}</td>
          <td>${cantidad}</td>
          <td>$${formatearMonto(precio)}</td>
          <td>${
            descuento && descuento.tipo == "porcentual"
              ? "-" + parseInt(descuento.valor) + "%"
              : descuento && descuento.tipo == "fijo"
              ? "-$" + formatearMonto(parseInt(descuento.valor))
              : ""
          }</td>
          <td>$${formatearMonto(subtotal)}</td>
          
        </tr>                    
      `);
      monto += parseInt(total.toString().replace(".", ""));
      montodescuento += subtotal - total;
    });
  }

  const iva = formatearMonto(Math.round((19 * monto) / 100));
  const totaltemp = Math.round(monto * 1.19);
  const totalpalabras = NumeroALetras(totaltemp);
  const total = formatearMonto(totaltemp);
  const afecto = formatearMonto(Math.round(monto));
  const tablatotal = `<table style='width: 100%' id='tabla_total' class='table table-bordered tableproductos mt-2 table-responsive w-100 d-block d-md-table' role='grid'>
                        <thead>
                        <tr role='row' style="visibility:hidden">
                          <th class='text-center' colspan="5"></th>
                          <th class='text-center' style='width:100px'></th>
                          <th class='text-center' style='width:130px'></th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td colspan="5" rowspan="8">COMENTARIO ${
                              comentario && comentario.length
                                ? "<br><br>" + comentario.toUpperCase()
                                : ""
                            }</td>
                            <td>Descuento</td>
                            <td>${
                              montodescuento && montodescuento > 0
                                ? "-$" + formatearMonto(montodescuento)
                                : "$0"
                            }</td>
                          </tr>
                          <tr>
                            <td>Afecto</td>
                            <td>$${afecto}</td>
                          </tr>
                          <tr>
                            <td>Exento</td>
                            <td>$0</td>
                          </tr>
                          <tr>
                            <td>19% IVA</td>
                            <td>$${iva}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td></td>
                          </tr>
                          <tr>
                            <td>Total</td>
                            <td style="font-weight:bold;font-size: 15px;color:#F7BE81 !important;">$${total}</td>
                          </tr>
                          <tr>
                            <td colspan="2">PESOS ${totalpalabras}</td>
                          </tr>
                        </tbody>
                      </table>`;
  $(".print-cotizacion").append(tablatotal);
  if (!isPrinting) {
    $("#modal-vistaprevia").attr("x-id", id_cotizacion);
    $("#modal-vistaprevia").attr("x-folio", folio);
    $("#modal-vistaprevia").attr("x-caf", caf);
    $("#modal-vistaprevia").attr("x-id-guia", id_guia);
    $("#modal-vistaprevia").modal("show");
  }
}

function calcularSubtotal() {
  const precio = $("#select_variedad option:selected").attr("x-precio");
  const cantidad = $("#input-cantidad").val().trim();

  const tipoDescuento = $("#select_descuento option:selected").val();
  const valorDescuento = $("#input-descuento").val().trim();

  if (!precio || !precio.length || !cantidad.length) {
    $("#input-total").val("");
  } else if (
    (tipoDescuento == "porcentual" && !valorDescuento.length) ||
    (tipoDescuento == "fijo" && !valorDescuento.length)
  ) {
    $("#input-total").val("");
  } else if (tipoDescuento == "porcentual" && parseInt(valorDescuento) > 99) {
    $("#input-total").val("");
  } else if (
    tipoDescuento == "fijo" &&
    parseInt(valorDescuento) > parseInt(precio * cantidad)
  ) {
    $("#input-total").val("");
  } else {
    const montototal = parseInt(precio) * cantidad;
    if (!tipoDescuento || !tipoDescuento.length || tipoDescuento == "ninguno") {
      $("#input-total").val(formatearMonto(montototal));
    } else if (tipoDescuento == "porcentual") {
      $("#input-total").val(
        formatearMonto(
          Math.round(montototal - (montototal * parseInt(valorDescuento)) / 100)
        )
      );
    } else if (tipoDescuento == "fijo") {
      $("#input-total").val(
        formatearMonto(Math.round(montototal - parseInt(valorDescuento)))
      );
    }
  }
}

function loadHistorial(mostrarProductos) {
  $.ajax({
    beforeSend: function () {
      $("#tabla_facturas").html("Buscando, espere...");
    },
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cargar_historial",
      mostrarProductos: mostrarProductos ? 1 : 0,
    },
    success: function (x) {
      $("#tabla_facturas").html(x);
      $("#tabla-historial-facturas").DataTable({
        pageLength: 50,
        order: [[2, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ facturas por página",
          zeroRecords: "No hay facturas",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay facturas",
          infoFiltered: "(filtrado de _MAX_ facturas en total)",
          lengthMenu: "Mostrar _MENU_ facturas",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron facturas",
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

function modalCambiarEstado(id) {
  $("#modal-estado").attr("x-id", id);
  $("#modal-estado").modal("show");
}

function guardarEstado(estado) {
  $("#modal-estado").modal("hide");
  const id = $("#modal-estado").attr("x-id");
  $.ajax({
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cambiar_estado",
      id: id,
      estado: estado,
    },
    success: function (x) {
      if (x.includes("success")) {
        loadHistorial();
      } else {
        swal("Ocurrió un error al cambiar el Estado", x, "error");
      }
    },
    error: function (jqXHR, estado, error) {
      swal("Ocurrió un error al cambiar el Estado", error.toString(), "error");
    },
  });
}

function setChanged(value) {
  hasChanged = value;
  if (value && $("#btn-guardar-cambios").hasClass("d-none")) {
    $("#btn-guardar-cambios").removeClass("d-none");
  } else if (!value && !$("#btn-guardar-cambios").hasClass("d-none")) {
    $("#btn-guardar-cambios").addClass("d-none");
  }
}

function guardarCambiosCliente() {
  const rut = $("#input-rut").val().trim();
  const domicilio = $("#input-domicilio").val().trim();
  const comuna = $("#input-comuna").val().trim();
  const ciudad = $("#input-ciudad").val().trim();
  const id_cliente = $("#select_cliente option:selected").val();
  if (!Fn.validaRut(rut)) {
    swal("El RUT ingresado no es válido", "", "error");
  } else {
    setChanged(false);

    $.ajax({
      url: "data_ver_facturacion.php",
      type: "POST",
      data: {
        consulta: "guardar_cambios_cliente",
        id_cliente: id_cliente,
        rut: rut,
        domicilio: domicilio.length ? domicilio : null,
        comuna: comuna.length ? comuna : null,
        ciudad: ciudad.length ? ciudad : null,
      },
      success: function (x) {
        if (x.includes("success")) {
          swal("Guardaste los cambios correctamente!", "", "success");
        } else {
          swal("Ocurrió un error al guardar los cambios", x, "error");
          setChanged(true);
        }
      },
      error: function (jqXHR, estado, error) {
        swal(
          "Ocurrió un error al guardar los cambios",
          error.toString(),
          "error"
        );
        setChanged(true);
      },
    });
  }
}

function setDescuento(value) {
  if (value == "porcentual") {
    $(".form-descuento").removeClass("d-none");
    $("#input-descuento").attr("placeholder", "Porcentaje");
    $("#input-descuento").focus();
  } else if (value == "fijo") {
    $(".form-descuento").removeClass("d-none");
    $("#input-descuento").attr("placeholder", "Monto");
    $("#input-descuento").focus();
  } else {
    $(".form-descuento").addClass("d-none");
    $("#input-cantidad").focus();
  }
  calcularSubtotal();
}

//^*******************************
function GuardarPedido() {
  const id_cliente = $("#select_cliente").find("option:selected").val();
  const condicion = $("#select-condicion").find("option:selected").val();

  if (!id_cliente.trim().length) {
    swal("Seleccioná un cliente!", "", "error");
  } else if ($(".pedido-vacio-msg").length) {
    swal(
      "La cotización está vacía!",
      "Agregá algún producto para continuar.",
      "error"
    );
  } else if (!condicion || !condicion.length) {
    swal("Selecciona la Condición de Pago", "", "error");
  } else {
    //console.log(productosCotizados);
    $("#btn-save").prop("disabled", true);

    if (productosCotizados.length) {
      const jsonarray = JSON.stringify(productosCotizados);
      const observaciones = $("#input-comentario").val().trim();
      let monto = 0.0;
      let montodescuento = 0.0;
      productosCotizados.forEach(function (producto, i) {
        const { total, subtotal } = producto;
        const totalito = parseInt(total.toString().replace(".", ""));
        monto += totalito;
        montodescuento += subtotal - totalito;
      });
      const total = Math.round(monto * 1.19);
      console.log(currentCotizacion);
      $.ajax({
        url: "data_ver_facturacion.php",
        type: "POST",
        data: {
          consulta: "guardar_cotizacion",
          id_cliente: id_cliente,
          jsonarray: jsonarray,
          observaciones: observaciones,
          condicion_pago: condicion,
          total: total,
          id_cotizacion:
            currentCotizacion && currentCotizacion.id_cotizacion
              ? currentCotizacion.id_cotizacion
              : "", //CHEQUEO SI ESTA EDITANDO O NO
        },
        success: function (x) {
          console.log(x);
          if (x.trim().includes("pedidonum")) {
            var idPedido = x.trim().includes("pedidonum:")
              ? x.trim().replace("pedidonum:", "")
              : null;

            printCotizacion(
              {
                id_cotizacion: idPedido,
                productos: productosCotizados,
                comentario: observaciones,
                condicion_pago: condicion,
                domicilio: $("#input-domicilio").val().trim(),
                ciudad: $("#input-ciudad").val().trim(),
                comuna: $("#input-comuna").val().trim(),
                rut: $("#input-rut").val().trim(),
                cliente: $("#select_cliente option:selected").attr("x-nombre"),
              },
              true
            );
            $("#btn-save").prop("disabled", false);
            setEditing(false);
            currentCotizacion = null;
          } else {
            swal("Ocurrió un error!", x, "error");
          }
        },
        error: function (jqXHR, estado, error) {
          swal(
            "Ocurrió un error al guardar la Cotización",
            error.toString(),
            "error"
          );
          $("#btn-save").prop("disabled", false);
        },
      });
    } else {
      swal("Debes agregar algún producto a la Cotización!", "", "error");
    }
  }
}

function loadHistorialCotizaciones() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_cotizaciones_container").html("Buscando, espere...");
    },
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cargar_historial_cotizaciones",
    },
    success: function (x) {
      $("#tabla_cotizaciones_container").html(x);
      $("#tabla_cotizaciones").DataTable({
        pageLength: 50,
        order: [[0, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ cotizaciones por página",
          zeroRecords: "No hay cotizaciones",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay cotizaciones",
          infoFiltered: "(filtrado de _MAX_ cotizaciones en total)",
          lengthMenu: "Mostrar _MENU_ cotizaciones",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron cotizaciones",
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
      $("#tabla_cotizaciones_container").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function generarFactura(id_cotizacion, id_guia, folio, caf) {
  swal("Generar FACTURA?", "", {
    icon: "info",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, GENERAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        if (!folio) {
          swal("No hay Folios!", "", "error");
          return;
        }

        //$("#modal-vistaprevia").modal("hide")
        $(".loading-wrapper").css({ display: "flex" });
        $.ajax({
          type: "POST",
          url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
          data: {
            consulta: "generar_factura",
            id_cotizacion: id_cotizacion,
            folio: folio,
            caf: caf,
            data: JSON.stringify(currentCotizacion.data),
            id_guia: id_guia,
          },
          success: function (x) {
            console.log(x);
            if (x.includes("path")) {
              const data = JSON.parse(x);
              //checkEstadoAfterSendingDTE(data.trackID, id_cotizacion, "FAC")
              reloadData();
              window.open(
                `verpdf.php?tipo=FACT&folio=${folio}&file=${data.path}`,
                "_blank"
              );

              $(".loading-wrapper").css({ display: "none" });
              $("#modal-vistaprevia").modal("hide");
              swal(
                "Generaste la Factura correctamente!",
                "Chequea su estado clickeando sobre el TRACK ID en Historial de Facturas",
                "success"
              );
            } else if (x.includes("SII_SUCCESS_BUT")) {
              $(".loading-wrapper").css({ display: "none" });
              $("#modal-vistaprevia").modal("hide");
              swal(
                "La Factura SE ENVIÓ AL SII, pero hubo un error al actualizar los datos en LA BD.",
                "POR FAVOR COMUNICATE CON SOPORTE ANTES DE CONTINUAR" + x,
                "error"
              );
            } else if (x.includes("ERROR_ENVIO_SII")) {
              $(".loading-wrapper").css({ display: "none" });
              $("#modal-vistaprevia").modal("hide");
              swal(
                "La Factura se guardó en el Sistema, pero hubo un error al enviarla al SII.",
                "Deberás buscar la Factura en el Historial e intentar reenviarla.",
                "error"
              );
            } else {
              swal("Ocurrió un error al generar la Factura", x, "error");
              $(".loading-wrapper").css({ display: "none" });
            }
          },
          error: function (jqXHR, estado, error) {},
        });

        break;

      default:
        break;
    }
  });
}

function reenviarFactura(rowid_factura) {
  swal("Reenviar FACTURA al SII?", "", {
    icon: "info",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, REENVIAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        const folio = $("#modal-vistaprevia").attr("x-folio");
        const caf = $("#modal-vistaprevia").attr("x-caf");
        const id_guia = $("#modal-vistaprevia").attr("x-id-guia");

        $(".loading-wrapper").css({ display: "flex" });
        $.ajax({
          type: "POST",
          url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
          data: {
            consulta: "reenviar_factura",
            rowid_factura: rowid_factura,
            folio: folio,
            caf: caf,
            id_guia: id_guia && id_guia.length ? id_guia : null,
            data: JSON.stringify(currentCotizacion.data),
          },
          success: function (x) {
            console.log(x);
            if (x.includes("path")) {
              const data = JSON.parse(x);
              //checkEstadoAfterSendingDTE(data.trackID, id_cotizacion, "FAC")
              reloadData();
              window
                .open(
                  `verpdf.php?tipo=FACT&folio=${folio}&file=${data.path}`,
                  "_blank"
                )
                .focus();
              $(".loading-wrapper").css({ display: "none" });
              $("#modal-vistaprevia").modal("hide");
              swal(
                "Enviaste la Factura correctamente!",
                "Chequea su estado clickeando sobre el TRACK ID en Historial de Facturas",
                "success"
              );
            } else if (x.includes("SII_SUCCESS_BUT")) {
              $(".loading-wrapper").css({ display: "none" });
              swal(
                "La Factura SE ENVIÓ AL SII, pero hubo un error al actualizar los datos en LA BD.",
                "POR FAVOR COMUNICATE CON SOPORTE ANTES DE CONTINUAR" + x,
                "error"
              );
            } else if (x.includes("ERROR_ENVIO_SII")) {
              $(".loading-wrapper").css({ display: "none" });
              swal("Error al Enviar la Factura al SII", "", "error");
            } else {
              swal("Ocurrió un error al generar la Factura", x, "error");
              $(".loading-wrapper").css({ display: "none" });
            }
          },
          error: function (jqXHR, estado, error) {},
        });

        break;

      default:
        break;
    }
  });
}

function printDTE(obj, rowid, folio, tipoDTE) {
  //0 FACTURA - 1 NOTA DE CREDITO
  $(obj).prop("disabled", true);
  $.ajax({
    type: "POST",
    url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
    data: {
      consulta: "imprimir_dte",
      rowid: rowid,
      tipoDTE: tipoDTE,
    },
    success: function (x) {
      console.log(x);
      if (x.includes("path")) {
        const data = JSON.parse(x);

        window
          .open(
            `verpdf.php?tipo=${
              tipoDTE == 1
                ? "NC"
                : tipoDTE == 0
                ? "FACT"
                : tipoDTE == 2
                ? "GD"
                : tipoDTE == 3
                ? "ND"
                : "XX"
            }&folio=${folio}&file=${data.path}`,
            "_blank"
          )
          .focus();
      } else {
        swal("Ocurrió un error al obtener el Documento", x, "error");
      }
      $(obj).prop("disabled", false);
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getEstadoDTE(trackID, facturaID, tipoDoc, estadoActual, rowid) {
  if (!trackID || !trackID.toString().length) return;

  $(".num-factura").html(
    `Estado ${
      tipoDoc == 0
        ? "Factura"
        : tipoDoc == 1
        ? "Nota Crédito"
        : tipoDoc == 2
        ? "Guía de Despacho"
        : tipoDoc == 3
        ? "Nota Débito"
        : "DTE"
    } N° ${facturaID} <small class='text-muted'>(${trackID})</small>`
  );
  $(".loader-container").html(`
      <div class="loading-wrapper2">
      <div class="load-3 mt-4 ml-4">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
      </div>
    </div>
  `);

  $("#modal-estado").modal("show");
  $.ajax({
    type: "POST",
    url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
    data: {
      consulta: "get_estado_dte",
      track_id: trackID,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        if (x.toLowerCase().includes("error")) {
          $(".loader-container").html(
            "<h6 class='text-danger'>ERROR: " + x + "</h6>"
          );
        } else {
          try {
            const data = JSON.parse(x);
            if (data.rechazados && data.rechazados == "1") {
              $(".loader-container").html(
                "<h5 class='text-danger'>ESTADO: RECHAZADO POR EL SII"
              );
              if (estadoActual != "RECHAZADO") {
                updateEstadoDTE(rowid, "RECHAZADO", tipoDoc);
              }
            } else if (data.aceptados && data.aceptados == "1") {
              $(".loader-container").html(
                "<h5 class='text-success font-weight-bold'>ESTADO: ACEPTADO POR EL SII"
              );
              if (estadoActual != "ACEPTADO") {
                updateEstadoDTE(rowid, "ACEPTADO", tipoDoc);
              }
            } else {
              $(".loader-container").html(
                "<h5 class='text-warning font-weight-bold'>NO SE PUDO OBTENER EL ESTADO. VERIFICA EN LA WEB DEL SII."
              );
            }
          } catch (error) {
            $(".loader-container").html(
              "<h6 class='text-danger'>ERROR: " + x + "</h6>"
            );
          }
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getAndUpdateEstadoDTE(trackID, tipoDoc, rowid) {
  if (!trackID || !trackID.toString().length) return;

  $.ajax({
    type: "POST",
    url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
    data: {
      consulta: "get_estado_dte",
      track_id: trackID,
    },
    success: function (x) {
      console.log(x);
      if (x.length && !x.toLowerCase().includes("error")) {
        try {
          const data = JSON.parse(x);
          if (data.rechazados && data.rechazados == "1") {
            updateEstadoDTE(rowid, "RECHAZADO", tipoDoc);
          } else if (data.aceptados && data.aceptados == "1") {
            updateEstadoDTE(rowid, "ACEPTADO", tipoDoc);
          }
        } catch (error) {}
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function updateEstadoDTE(rowid, newEstado, tipoDoc) {
  $.ajax({
    type: "POST",
    url: "data_ver_facturacion.php",
    data: {
      consulta: "update_estado_dte",
      rowid: rowid,
      estado: newEstado,
      tipoDoc: tipoDoc,
    },
    success: function (x) {
      if (x.includes("success")) {
        reloadData();
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function reloadData() {
  if (currentTab == "historial") {
    loadHistorial();
  } else if (currentTab == "notas") {
    loadNotas();
  } else if (currentTab == "notas-debito") {
    loadNotasDebito();
  } else if (currentTab == "guias") {
    loadGuias();
  } else if (currentTab == "cotizaciones") {
    loadHistorialCotizaciones();
  }
}

function loadNotas() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_notas").html("Buscando, espere...");
    },
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cargar_notas",
    },
    success: function (x) {
      $("#tabla_notas").html(x);
      $("#tabla-historial-notas").DataTable({
        pageLength: 50,
        order: [[3, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ notas por página",
          zeroRecords: "No hay notas",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay notas",
          infoFiltered: "(filtrado de _MAX_ notas en total)",
          lengthMenu: "Mostrar _MENU_ notas",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron notas",
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
      $("#tabla_notas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function modalAnularFactura(rowid, folio, esFactDirecta, id_cliente) {
  $("#modal-anular-factura").attr("x-id-cliente", id_cliente);
  $("#modal-anular-factura").attr("x-fact-directa", esFactDirecta);
  getFoliosDisponibles(null, rowid, 61, folio, null);
  $("#input-comentario").val("");
  $("#modal-anular-factura").modal("show");
}

function anularFactura(rowid, folioRef, folioNC, cafNC) {
  const caf = cafNC;
  const folio = folioNC;
  const comentario = $("#input-comentario").val().trim();

  if (!folio) {
    swal("No hay Folios para generar el DTE!", "", "error");
    return;
  }

  $(".loading-wrapper").css({ display: "flex" });
  $.ajax({
    type: "POST",
    url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
    data: {
      consulta: "anular_factura",
      rowid: rowid,
      folio: folio,
      caf: caf,
      comentario: comentario,
      folioRef: folioRef,
      id_cliente: $("#modal-anular-factura").attr("x-id-cliente"),
      esFactDirecta:
        $("#modal-anular-factura").attr("x-fact-directa") == "true"
          ? true
          : false,
    },
    success: function (x) {
      console.log(x);
      if (x.includes("path")) {
        const data = JSON.parse(x);
        window
          .open(`verpdf.php?tipo=NC&folio=${folio}&file=${data.path}`, "_blank")
          .focus();
        $(".loading-wrapper").css({ display: "none" });
        $("#modal-anular-factura").modal("hide");
        swal(
          "Anulaste la Factura correctamente!",
          "Chequea su estado clickeando sobre el TRACK ID",
          "success"
        );
        setTimeout(loadNotas(), 2000);
      } else if (x.includes("SII_SUCCESS_BUT")) {
        $(".loading-wrapper").css({ display: "none" });
        $("#modal-anular-factura").modal("hide");
        swal(
          "La Nota de Crédito SE ENVIÓ AL SII, pero hubo un error al actualizar los datos en LA BD.",
          "POR FAVOR COMUNICATE CON SOPORTE ANTES DE CONTINUAR" + x,
          "error"
        );
      } else if (x.includes("ERROR_ENVIO_SII")) {
        $(".loading-wrapper").css({ display: "none" });
        $("#modal-anular-factura").modal("hide");
        swal(
          "La Nota de Crédito se guardó en el Sistema, pero hubo un error al enviarla al SII.",
          "Deberás buscar la Nota en el Historial e intentar reenviarla.",
          "error"
        );
      } else {
        swal("Ocurrió un error al anular la Factura", x, "error");
        $(".loading-wrapper").css({ display: "none" });
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function loadGuias() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_guias").html("Buscando, espere...");
    },
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cargar_guias",
    },
    success: function (x) {
      $("#tabla_guias").html(x);
      $("#tabla-historial-guias").DataTable({
        pageLength: 50,
        order: [[3, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ guias por página",
          zeroRecords: "No hay guias",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay guias",
          infoFiltered: "(filtrado de _MAX_ guias en total)",
          lengthMenu: "Mostrar _MENU_ guias",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron guias",
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
      $("#tabla_guias").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function vistaPreviaGuiaDespacho(id, id_guia) {
  currentCotizacion = null;
  $.ajax({
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: {
      consulta: "cargar_cotizacion",
      id: id,
      directa: true,
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
            productos,
            condicion_pago,
            giro,
            razon,
          } = data;

          currentCotizacion = {
            id_cotizacion: id,
            data: data,
          };

          console.log(data);
          printCotizacion(
            {
              id_cotizacion: id,
              productos: productos,
              comentario: comentario,
              domicilio: domicilio,
              ciudad: ciudad,
              comuna: comuna,
              rut: rut,
              cliente: cliente,
              condicion_pago: condicion_pago,
              giro: giro,
              razon: razon,
              esGuiaDespacho: true,
            },
            false
          );

          getFoliosDisponibles(id, null, 33, null, id_guia);
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
      $("#btn_guardarpedido").prop("disabled", false);
    },
  });
}

function printDataCotizacion(id) {
  currentCotizacion = null;
  $.ajax({
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: {
      consulta: "cargar_cotizacion",
      id: id,
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
            productos,
            condicion_pago,
            giro,
            razon,
          } = data;

          currentCotizacion = {
            id_cotizacion: id,
            data: data,
          };

          console.log(data);
          printCotizacion(
            {
              id_cotizacion: id,
              productos: productos,
              comentario: comentario,
              domicilio: domicilio,
              ciudad: ciudad,
              comuna: comuna,
              rut: rut,
              cliente: cliente,
              condicion_pago: condicion_pago,
              giro: giro,
              razon: razon,
            },
            false
          );

          getFoliosDisponibles(id, null, 33, null, null);
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
      $("#btn_guardarpedido").prop("disabled", false);
    },
  });
}

function vistaPreviaReenviarFactura(
  id,
  directa,
  folio,
  rowid_factura,
  caf,
  id_guia
) {
  currentCotizacion = null;
  $.ajax({
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: {
      consulta: "cargar_cotizacion",
      id: id,
      directa: directa ? directa : null,
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
            productos,
            condicion_pago,
            giro,
            razon,
          } = data;

          currentCotizacion = {
            id_cotizacion: id,
            data: data,
          };

          console.log(data);
          printCotizacion(
            {
              id_cotizacion: id,
              productos: productos,
              comentario: comentario,
              domicilio: domicilio,
              ciudad: ciudad,
              comuna: comuna,
              rut: rut,
              cliente: cliente,
              condicion_pago: condicion_pago,
              giro: giro,
              razon: razon,
              esFactura: true,
              folio: folio,
              rowid_factura: rowid_factura,
              caf: caf,
              id_guia: id_guia,
            },
            false
          );

          $(".row-select-folio").html(`
            <div class="col col-md-6">
              <button onclick="reenviarFactura(${rowid_factura})" class="btn btn-success"><i class="fa fa-arrow-circle-right"></i> REENVIAR FACTURA</button>
            </div>
          
          `);
        } catch (error) {
          console.log(error);
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminarDTE(rowid, tipoDTE) {
  //0 FACT - 1 NC - 2 GD
  const tipos = [
    "Factura",
    "Nota de Crédito",
    "Guía de Despacho",
    "Nota de Débito",
  ];
  swal(`Estás seguro/a de ELIMINAR la ${tipos[tipoDTE]}?`, "", {
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
          data: { consulta: "eliminar_dte", rowid: rowid, tipoDTE: tipoDTE },
          success: function (x) {
            if (x.trim() == "success") {
              swal(
                `Eliminaste la ${tipos[tipoDTE]} correctamente!`,
                "",
                "success"
              );
              reloadData();
            } else {
              swal(
                `Ocurrió un error al eliminar la ${tipos[tipoDTE]}`,
                x,
                "error"
              );
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function reenviarNotaCredito(
  folio,
  rowid,
  caf,
  folio_factura,
  rowid_factura,
  esDirecta
) {
  swal(
    `Reenviar la Nota de Crédito ${folio}?`,
    `Corresponde a la Factura ${folio_factura}`,
    {
      icon: "info",
      buttons: {
        cancel: "NO",
        catch: {
          text: "SI, REENVIAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
          data: {
            consulta: "reenviar_nota_credito",
            rowid: rowid,
            caf: caf,
            folio: folio,
            folio_factura: folio_factura,
            rowid_factura: rowid_factura,
            esDirecta: esDirecta,
          },
          success: function (x) {
            console.log(x);
            if (x.includes("path")) {
              const data = JSON.parse(x);
              reloadData();
              window
                .open(
                  `verpdf.php?tipo=NC&folio=${folio}&file=${data.path}`,
                  "_blank"
                )
                .focus();
              swal(
                "Reenviaste la Nota de Crédito correctamente!",
                "Chequea su estado clickeando sobre el TRACK ID",
                "success"
              );
            } else if (x.includes("SII_SUCCESS_BUT")) {
              swal(
                "La Nota de Crédito SE ENVIÓ AL SII, pero hubo un error al actualizar los datos en LA BD.",
                "POR FAVOR COMUNICATE CON SOPORTE ANTES DE CONTINUAR" + x,
                "error"
              );
            } else if (x.includes("ERROR_ENVIO_SII")) {
              swal(
                "Ocurrió un Error al enviar el Documento al SII, intenta de nuevo.",
                "",
                "error"
              );
            } else {
              swal("Ocurrió un error al reenviar el Documento", x, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function reenviarGuiaDespacho(rowid, folio, caf) {
  swal(`Reenviar la Guía de Despacho ${folio}?`, ``, {
    icon: "info",
    buttons: {
      cancel: "NO",
      catch: {
        text: "SI, REENVIAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
          data: {
            consulta: "reenviar_guia_despacho",
            rowid: rowid,
            caf: caf,
            folio: folio,
          },
          success: function (x) {
            console.log(x);
            if (x.includes("path")) {
              const data = JSON.parse(x);
              reloadData();
              window
                .open(
                  `verpdf.php?tipo=GD&folio=${folio}&file=${data.path}`,
                  "_blank"
                )
                .focus();
              swal(
                "Reenviaste la Guía correctamente!",
                "Chequea su estado clickeando sobre el TRACK ID",
                "success"
              );
            } else if (x.includes("SII_SUCCESS_BUT")) {
              swal(
                "La Guía SE ENVIÓ AL SII, pero hubo un error al actualizar los datos en LA BD.",
                "POR FAVOR COMUNICATE CON SOPORTE ANTES DE CONTINUAR" + x,
                "error"
              );
            } else if (x.includes("ERROR_ENVIO_SII")) {
              swal(
                "Ocurrió un Error al enviar el Documento al SII, intenta de nuevo.",
                "",
                "error"
              );
            } else {
              swal("Ocurrió un error al reenviar el Documento", x, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function loadNotasDebito() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_notas_debito").html("Buscando, espere...");
    },
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cargar_notas_debito",
    },
    success: function (x) {
      $("#tabla_notas_debito").html(x);
      $("#tabla-historial-notas-debito").DataTable({
        pageLength: 50,
        order: [[3, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ notas por página",
          zeroRecords: "No hay notas",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay notas",
          infoFiltered: "(filtrado de _MAX_ notas en total)",
          lengthMenu: "Mostrar _MENU_ notas",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron notas",
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
      $("#tabla_notas_debito").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}

function sendMailFactura(btn, rowid, folio, tipoDTE, monto, email) {
  if (!email || !email.length || !validEmail(email)) {
    swal("El email del cliente no es válido", "", "error");
    return;
  }

  if (!monto || isNaN(monto)) {
    swal("El monto de la Factura no es valido", "", "error");
    return;
  }
  //0 FACT - 1 NC - 2 GD
  swal(`Enviar Factura por Email?`, "", {
    icon: "info",
    buttons: {
      cancel: "NO",
      sinlink: {
        text: "ENVIAR SIN LINK",
        value: "sinlink",
      },
      catch: {
        text: "ENVIAR CON LINK",
        value: "catch",
      },
    },
  }).then((value) => {
    const enviarMail = (resp) => {
      $.ajax({
        beforeSend: function () {},
        url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
        type: "POST",
        data: {
          link: resp,
          consulta: "enviar_mail",
          rowid: rowid,
          folio: folio,
          tipoDTE: tipoDTE,
          email: email,
        },
        success: function (x) {
          console.log(x);
          if (x.includes("success")) {
            swal("Enviaste la Factura por Email correctamente!", "", "success");
          } else {
            swal(`Ocurrió un error al enviar el Email`, x, "error");
          }
          setTimeout(() => {
            if (btn) $(btn).prop("disabled", false);
          }, 5000);
        },
        error: function (jqXHR, estado, error) {},
      });
    };

    switch (value) {
      case "catch":
        $.get(
          "flow/public/flow/linkfactura/" +
            folio +
            "/" +
            monto.toString() +
            "/" +
            email,
          function (resp) {
            console.log(resp);
            if (resp.includes("http")) {
              $(btn).prop("disabled", true);
              enviarMail(resp);
            }
          }
        )
          .done(function () {})
          .fail(function () {
            swal("Error al generar el Link de Pago", "", "error");
          });
        break;

      case "sinlink":
        enviarMail(null);

      default:
        break;
    }
  });
}

function generarSolicitudDespacho() {
  if (!$("#tabla-historial-facturas input:checked").length) return;

  let selectedMap = {};
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

  $("#tabla-historial-facturas input:checked").each(function () {
    const label = $(this).closest("td").find(".label-cliente").text();
    const telefono = $(this).closest("td").find(".label-cliente").attr("x-telefono")
    const comuna = $(this).closest("td").find(".label-cliente").attr("x-comuna")
    const ciudad = $(this).closest("td").find(".label-cliente").attr("x-ciudad")
    const cantidad = $(this).attr("x-cantidad-band");
    const producto = $(this).attr("x-producto");

    const line = `<span class='lbl-cant' x-cant='${cantidad}'>${cantidad} bandejas de <input onkeyup="setTotal(this)"
    onpaste="setTotal(this)" autocomplete='off' style='margin-left: 3px;font-size:8px !important; padding: 2px;text-align:center;width:45px !important; display:inline-block' class='form-control numeric' type='search'></input>
    <span style='display:none' class='mask'></span>
    :
      ${producto}
    </span>
    
    `
    
    if (selectedMap[label] && selectedMap[label]["productos"]) {
      selectedMap[label]["productos"].push(line);
    } else {
      selectedMap[label] = {productos: [line]};
    }

    selectedMap[label]["telefono"] = telefono
    selectedMap[label]["destino"] = comuna.length && ciudad.length ? comuna+", "+ciudad : comuna
  });

  console.log(selectedMap);

  let rows = "";
  for (const [key, value] of Object.entries(selectedMap)) {
    let productos = ""
    value["productos"].forEach(element => {
      productos+=`<span>${element}</span><br>`
    });

    rows+=`
    <tr>
      <td>${productos}</td>
      <td class='text-center'>
        <input onkeyup='updateMasks(this)' style='width:70px !important' class='form-control' type='number' step='1' max='31' min='1'></input>
        <span style='display:none' class='mask'></span>
      </td>
      <td class='text-center'>
        <input onkeyup='updateMasks(this)' style='width:70px !important' class='form-control' type='number' step='1' max='12' min='1'></input>
        <span style='display:none' class='mask'></span>
      </td>
      <td class='text-center'>
        <select onchange='updateMasksSelect(this)' data-width='80px' class="selectpicker" style='max-width:80px !important' data-dropup-auto="false"
        title="Inicio" data-style="btn-info">
          <option value="7">7:00am</option>
          <option value="8">8:00am</option>
          <option value="9">9:00am</option>
          <option value="10">10:00am</option>
          <option value="11">11:00am</option>
          <option value="12">12:00pm</option>
          <option value="13">1:00pm</option>
          <option value="14">2:00pm</option>
          <option value="15">3:00pm</option>
          <option value="16">4:00pm</option>
          <option value="17">5:00pm</option>
          <option value="18">6:00pm</option>
          <option value="19">7:00pm</option>
          <option value="20">8:00pm</option>
        </select>
        <span style='display:none' class='mask'></span>
      </td>
      <td class='text-center'>
        <select onchange='updateMasksSelect(this)' class="selectpicker" style='max-width:70px !important' data-dropup-auto="false"
        title="Fin" data-width='80px' data-style="btn-info">
          <option value="7">7:00am</option>
          <option value="8">8:00am</option>
          <option value="9">9:00am</option>
          <option value="10">10:00am</option>
          <option value="11">11:00am</option>
          <option value="12">12:00pm</option>
          <option value="13">1:00pm</option>
          <option value="14">2:00pm</option>
          <option value="15">3:00pm</option>
          <option value="16">4:00pm</option>
          <option value="17">5:00pm</option>
          <option value="18">6:00pm</option>
          <option value="19">7:00pm</option>
          <option value="20">8:00pm</option>
        </select>
        <span style='display:none' class='mask'></span>
      </td>
      <td class='td-total text-center'></td>
      <td class='text-center'>${value.destino}</td>
      <td class='text-center'>${key}<br>${value.telefono}</td>
    </tr>
    `
  }

  $("#modal-solicitud-despacho").modal("show");

  $(".container-solicitud-despacho").html(`
  <div class='row mt-2 mb-3'>
    <div class='col text-center font-weight-bold'>
    AVISO/SOLICITUD DE DESPACHO DE PRODUCTOS REGLAMENTADOS AL<br>
    AREA LIBRE DE PLAGAS CUARENTENARIAS DE LA PAPA
    </div>
  </div>

  <div style='width:100%;display:flex;justify-content:end;'>
  <div style='width:600px'>
    <span style='font-size:12px;border:1px solid #000;padding-left:20px;padding-right:20px'>${datetime}</span>
  </div>
  </div>
  <div style='width:100%;display:flex;justify-content:end;'>
    <table class="tableizer-table" style="width:600px">
    <tbody>
      <tr>
        <td class='font-weight-bold'>Nombre de la Empresa:</td>
        <td colspan="3" class='text-center'>
          <div style="border: 1px solid black">
            PLANTINERA V.V  RUT 77436423-4
          </div>
        </td>
      </tr>
      <tr>
        <td class='font-weight-bold'> Responsable Técnico</td>
        <td colspan="3" class="text-center">
        <div style="border: 1px solid black">
          SERGIO VILLARROEL MATTAR
        </div>
        </td>
      </tr>
      <tr>
        <td class='font-weight-bold'>Telefono de contacto</td>
        <td class="text-center">
        <div style="border: 1px solid black">
          9 5819 4808
        </div>
        </td>
        <td class="text-center">E MAIL</td>
        <td class="text-center">
          <div style="border: 1px solid black">
            plantinera@roelplant.cl
          </div>
        </td>
      </tr>
    </tbody></table>
  </div>
  `);

  $(".container-solicitud-despacho").append(`
  <table class="tableizer2-table tabla-descripcion w-100" style="margin-top:30px">
  <thead>
    <tr class="tableizer2-firstrow"><th colspan="8" style='text-align:center;color:#000'>Descripción de la Solicitud</th></tr>
  </thead>
  <tbody>
  <tr class="theader font-weight-bold text-center">
  <td rowspan="2">ESPECIE PRODUCTO REGLAMENTADO</td>
  <td colspan="2">FECHA DE DESPACHO</td>
  <td colspan="2">HORA DE DESPACHO</td>
  <td>TOTAL</td>
  <td rowspan="2">DESTINO</td>
  <td rowspan="2">NOMBRE DEL CONSIGNATARIO</td>
</tr>
<tr class="theader font-weight-bold text-center">
  <td>DIA</td>
  <td>MES</td>
  <td>INICIO</td>
  <td>TERMINO</td>
  <td></td>

</tr>
   ${rows}
   <tr><td>TOTAL</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class='td-suma-total text-center'></td><td>&nbsp;</td><td></td></tr>
  </tbody></table>
  `);

  $(".container-solicitud-despacho").append(`
  
    <div class='row mt-3'>
      <div class='form-group col-md-6 form-tipo-despacho'>
        <label>TIPO DE DESPACHO (MARCAR UNA OPCION)</label><br>
        <select class="selectpicker select-tipo-despacho" data-dropup-auto="false"
        title="Selecciona" data-style="btn-info" data-width="100%">
          <option value="0">CON INSPECTOR SAG</option>
          <option value="1">DESPACHO DIRECTO</option>
        </select>
      </div>
    </div>
    <div class='row'>
      <div class='form-group col-md-12'>
        <label>Observaciones:</label><br>
        <input type='search'  autocomplete='off' class='form-control input-observaciones-solicitud'></input>
      </div>
    </div>
  `)

  $(".container-solicitud-despacho").append(`
    <div class='row mt-4'>
      <div class='col'>
        <img src='./dist/img/firmaSolicitudDespacho.jpg' style='width: 350px !important'/>
      </div>
    </div>
  `);

  $(".selectpicker").selectpicker()
}

function setChecked(obj) {
  $(obj).parent().find("input[type=checkbox]").prop("checked", true);
}

function setTotal(obj){
  $(obj).next().html($(obj).val())
  let total = 0;
  $(obj).closest("tr").find(".lbl-cant").each(function(){
    let tipoBandeja = $(this).find("input").val();
    const cantidad = $(this).attr("x-cant")

    if (!isNaN(tipoBandeja)){
      total+=(tipoBandeja*cantidad)
    }
  })

  if (total > 0){
    $(obj).closest("tr").find(".td-total").html(total)
  }
  else{
    $(obj).closest("tr").find(".td-total").html("")
  }

  let sumaTotal = 0;

  $(".td-suma-total").html("")
  $(".td-total").each(function(){
    if ($(this).text().length){
      sumaTotal+=parseInt($(this).text())
    }
  })

  if (sumaTotal>0){
    $(".td-suma-total").html(sumaTotal)
  }
}

function printSolicitudDespacho(tipo) {
  if (tipo == 1) {
    $("#miVentana").html($(".container-solicitud-despacho").html())
    
    $("#miVentana").find(".tabla-descripcion").first().find("input").each(function(){
      $(this).remove()
    })

    $("#miVentana").find(".tabla-descripcion").first().find("select").each(function(){
      $(this).html("")
      const valor = $(this).closest("td").find(".mask").first().text()
      $(this).closest("td").html(valor)
    })

    const tipoDespacho = $(".select-tipo-despacho").find("option:selected").text()

    if (tipoDespacho.includes("INSPECTOR")){
      $(".select-tipo-despacho").parent().html(`
      <label>TIPO DE DESPACHO (MARCAR UNA OPCIÓN):</label><br>
        <span>CON INSPECTOR SAG</span><span style='margin-left: 20px;border: 2px solid black;padding-left: 15px;padding-right:15px; padding-top: 5px; padding-bottom: 5px'>X</span><br><br><br>
        <span>DESPACHO DIRECTO</span>      
      `)
    }
    else{
      $(".select-tipo-despacho").parent().html(`
        <label>TIPO DE DESPACHO (MARCAR UNA OPCIÓN):</label><br>
        <span>CON INSPECTOR SAG</span><br><br><br>
        <span>DESPACHO DIRECTO </span><span style='margin-left: 20px;border: 2px solid black;padding-left: 15px;padding-right:15px; padding-top: 5px; padding-bottom: 5px;'>X</span>      
      `)
    }

    $("#miVentana").find(".mask").css({"display":"inline-block"})

    const observaciones = $("#modal-solicitud-despacho").find(".input-observaciones-solicitud").val()
    $("#miVentana").find(".input-observaciones-solicitud").replaceWith(`<span>${observaciones}</span>`)

    document.getElementById("ocultar").style.display = "none";
    document.getElementById("miVentana").style.display = "block";
    $("#modal-solicitud-despacho").modal("hide");
    setTimeout(
      "window.print();printSolicitudDespacho(2);document.title = 'Facturación';generarSolicitudDespacho()",
      500
    );
  } else {
    document.getElementById("ocultar").style.display = "block";
    document.getElementById("miVentana").style.display = "none";
    document.title = "Solicitud de Despacho";
  }
}

function updateMasksSelect(obj){
  $(obj).closest("td").find(".mask").html($(obj).find("option:selected").text())
}

function updateMasks(obj){
  $(obj).closest("td").find(".mask").html($(obj).val())
}


//GUIA TRANSITO


function generarGuiaTransito(obj, rowid, folio, fecha, cliente, domicilio, comuna, id_cotizacion_directa, telefono) {
  

  let selectedMap = {};
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

  $("#modal-guia-transito").modal("show");

  $(".container-guia-transito").html(`
    <div class='row'>
      <div class='col w-100'>
      <div class='d-flex flex-row' style='justify-content: space-between;width:100%;align-items:end;'>
        <img src='dist/img/roelprint.png' style='width: 120px'/>  
        <span>Fecha de Emisión: ${datetime}</span>
      </div>
        
      </div>
    </div>
  `)


  $(".container-guia-transito").append(`
  <div class='row mt-4'>
    <div class='col text-center font-weight-bold'>
      <h4>GUÍA DE LIBRE TRÁNSITO <small id="num-guia"></small></h4>
    </div>
  </div>
  <div class='row font-weight-bold mb-3'>
    <div class='col text-center'>
      <span style='font-size:14px'>(Resoluciones SAG N° 3276 / 2016)</span>
    </div>
  </div>

    <table class="tableizer-table-transito w-100">
    <thead><tr>
    <td class='w-50'>NOMBRE DEL VIVERO DEPÓSITO O EMPRESA AUTORIZADA:
    <br>
    <span class='font-weight-bold'>Plantinera V.V</span>
    </td>
    <td><br>
    <span class='font-weight-bold'></span></td>
  </tr></thead>
    <tbody>
      
      <tr>
        <td>REGIÓN:
        <br>
    <span class='font-weight-bold'>Valparaíso</span>
        </td>
        <td>COMUNA:<br>
        <span class='font-weight-bold'>Quillota</span>
        </td>
      </tr>
      <tr>
        <td>PROVINCIA:<br>
        <span class='font-weight-bold'>Quillota</span></td>
        <td>LUGAR DE EMISIÓN:<br>
        <span class='font-weight-bold'>El Carmen PC7 Lote 2</span></td>
      </tr>
    </tbody></table>  
  `);

  $(".container-guia-transito").append(`
   <div class='row mt-3 mb-3'>
     <div class='col text-center'>
      <span style='font-size:12px'>El suscrito certifica que la presente Guía de Libre Tránsito con Despacho Directo ha sido emitida conforme a los procedimientos reglamentarios y operativos del Servicio Agrícola y Ganadero y ampara a los siguientes reglamentados:</span>
    </div>
   </div>    
  `);

  const input = `<input type='search'  autocomplete='off' class='form-control input-small'></input>`
  const inputw100 = `<input type='text' class='form-control input-small d-inline-block' style='min-width:200px'></input>`

  $(".container-guia-transito").append(`
  <table class="tableizer-table-transito w-100">
  <tbody>
    <tr>
      <td colspan="3">Nombre del consignatario/a: ${cliente}</td>
    </tr>
    <tr>
      <td>N° Guía de Despacho del SII: ${folio}</td>
      <td colspan="2">con fecha: ${fecha}</td>
    </tr>
    <tr>
      <td>Patente Camión: <input type='search'  autocomplete='off' class='form-control input-small d-inline-block' value='N/A'></input></td>
      <td colspan="2">Patente Carro o Acoplado: <input type='search' value='N/A'  autocomplete='off' class='form-control input-small d-inline-block'></input></td>
    </tr>
    <tr>
      <td>Empresa Transporte: <input type='search'  autocomplete='off' class='form-control input-small d-inline-block' value='Starken'></input></td>
      <td colspan="2">Fecha Despacho: <input type='date'  autocomplete='off' class='form-control input-small d-inline-block'></input></td>
    </tr>
    <tr>
      <td>Condición de los productos reglamentados:</td>
      <td>Nombre especie/tipo</td>
      <td>Cantidad</td>
    </tr>
    <tr>
      <td>Sustratos de crecimiento</td>
      <td>${input}</td>
      <td>${input}</td>
    </tr>
    <tr>
      <td>Material vegetal sin suelo adherido</td>
      <td>${input}</td>
      <td>${input}</td>
    </tr>
    <tr>
      <td>Plantas o material vegetal en sustrato esterilizado</td>
      <td>${input}</td>
      <td>${input}</td>
    </tr>
    <tr>
      <td>Plantas o material sin turba o medio inerte</td>
      <td>
        <input type='search'  autocomplete='off' class='form-control input-small' value='PLANTINES'></input>
      </td>
      <td>
        <input type='search'  autocomplete='off' class='form-control input-small input-plantines'></input>
      </td>
    </tr>
    <tr>
      <td>Otros (especificar)</td>
      <td>${input}</td>
      <td>${input}</td>
    </tr>

  </tbody></table>  
  `)


  $(".container-guia-transito").append(`
   <div class='row mt-4'>
     <div class='col'>
      <span style='font-size:12px'>El material se despacha en el medio de transporte específicado y se envía con destino declarado a:</span>
    </div>
   </div>    
  `);

  $(".container-guia-transito").append(`
  <div class='container-data-cliente'>
   <div class='row mt-2'>
      <div class='col-md-6'>
        Región:
        ${inputw100}
      </div>
      <div class='col-md-6'>
        Provincia:
        ${inputw100}
      </div>
   </div>    
   <div class='row mt-2'>
      <div class='col'>
        Comuna:
        <input type='text' class='form-control input-small d-inline-block' style='min-width:200px' value='${comuna}'></input>
      </div>
   </div>    
   <div class='row mt-2'>
      <div class='col'>
        Dirección:
        <input type='text' class='form-control input-small d-inline-block' style='min-width:200px' value='${domicilio}'></input>
      </div>
   </div>    
   <div class='row mt-2'>
      <div class='col'>
        Lleva ${inputw100} sellos, ubicados en ${inputw100}
      </div>
   </div>    

   <div class='row mt-4'>
      <div class='col'>
        Observaciones:
        <textarea style='resize:none'  class='form-control w-100 textarea-obs'></textarea>
      </div>
   </div>    

   <div class='row mt-4'>
      <div class='col-md-6'>
        <span>Nombre Despachador:</span>
        <input type='text' class='form-control input-small2 w-100' value='Sergio Villarroel Mattar'></input>
      </div>
      <div class='col-md-6'>
       <span>R.U.T Despachador:</span>
        <input type='text' class='form-control input-small2 w-100' value='16.182.953-6'></input>
      </div>
   </div>    

   </div>
  `);

  $(".container-guia-transito").append(`
    <div class='row' style="margin-top:100px">
      <div class='col-md-6'>
        
      </div>
      <div class='col-md-6 text-center'>
        Firma y Timbre
      </div>
    </div>    
  `)

  $.ajax({
    url: "data_ver_facturacion.php",
    type: "POST",
    async: false,
    data: {
      consulta: "get_last_guia_transito",
    },
    success: function (x) {
      if (x.includes("max:")){
        $("#num-guia").html("N° "+(x.replace("max:","")))
      }
    }
  });

  $.ajax({
    url: "data_ver_facturacion.php",
    type: "POST",
    async: false,
    data: {
      consulta: "get_cantidad_total_productos",
      id: id_cotizacion_directa
    },
    success: function (x) {
      if (x.length){
        $(".input-plantines").val(x)
      }
    }
  });

  $.ajax({
    url: "data_ver_facturacion.php",
    type: "POST",
    async: false,
    data: {
      consulta: "get_productos",
      id: id_cotizacion_directa
    },
    success: function (x) {
      if (x.length){
        const data = JSON.parse(x)
        let productos = "PLANTINES: ";
        if (data && data.length){
          data.forEach((e) => {
            productos+=`${e.variedad} ${e.cantidad}, `
          })
          $(".textarea-obs").val(productos + " TELEFONO: "+ telefono)
        }
        
      }
    }
  });



  $(".selectpicker").selectpicker()
}


function printGuiaTransito(tipo) {
  


  if (tipo == 1) {
    $("#miVentana").html($(".container-guia-transito").clone())
    
    $("#miVentana").find("input").each(function(){
      const val = $(this).val().trim();
      // Reemplaza el input actual con el nuevo label
      $(this).replaceWith(`<span>${val}</span>`);
    })

    document.getElementById("ocultar").style.display = "none";

    document.getElementById("miVentana").style.display = "block";
    $("#modal-guia-transito").modal("hide");


    $.ajax({
      url: "data_ver_facturacion.php",
      type: "POST",
      data: {
        consulta: "guardar_guia_transito",
        codigo: btoa($("miVentana").html())
      },
      success: function (x) {
        if (x.includes("success")){
          setTimeout(
            "window.print();printGuiaTransito(2);document.title = 'Facturación';",
            500
          );
        }
      },
      error: function (jqXHR, estado, error) {
        
      },
    });
  

    
  } else {
    document.getElementById("ocultar").style.display = "block";
    document.getElementById("miVentana").style.display = "none";
    document.title = "Guía de Libre Tránsito";
  }
}
