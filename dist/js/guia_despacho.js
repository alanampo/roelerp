let productosCotizados = [];
let productoIndex = 0;
let hasChanged,
  hasChangedTransporte = false;
let tieneEspecies = false;
let currentCotizacion;

$(document).ready(function () {
  pone_datos_transporte();
  pone_clientes();
  pone_comunas();
  productosCotizados = [];

  $("#input-cantidad,#input-descuento").on(
    "propertychange input",
    function (e) {
      this.value = this.value.replace(/\D/g, "");
    }
  );

  $("#input-rut,#input-domicilio,#input-comuna,#input-razon,#input-giro").on(
    "propertychange input",
    function (e) {
      setChanged(true);
    }
  );

  $(".input-transporte").on("propertychange input", function (e) {
    setChangedTransporte(true);
  });

  $(".input-rut")
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
});

function pone_clientes() {
  $.ajax({
    beforeSend: function () {
      $("#select_cliente").html("Cargando lista de clientes...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "get_clientes_select"
    },
    success: function (x) {
      $("#select_cliente").html(x).selectpicker("refresh");
      $("#select_cliente").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          setChanged(false);
          cargarDatosCliente(this.value);
        }
      );
    },
    error: function (jqXHR, estado, error) { },
  });
}

function modalAgregarProducto() {
  window.selectedPrice = null;
  if ($("#table-pedido > tbody > tr").length > 19) {
    swal("No puedes cotizar más de 20 productos!", "", "error");
    return;
  }

  ClearModal();
  pone_tiposdeproducto();
  $("#modal-agregar-producto").modal("show");
}

function ClearModal() {
  $("#select_tipo,#select_variedad,#select_especie,#select_descuento")
    .val("default")
    .selectpicker("refresh");
  var groupFilter = $("#select_variedad");
  groupFilter.selectpicker("val", "");
  groupFilter.find("option").remove();
  groupFilter.selectpicker("refresh");

  var selectEspecie = $("#select_especie");
  selectEspecie.selectpicker("val", "");
  selectEspecie.find("option").remove();
  selectEspecie.selectpicker("refresh");

  $("#input-cantidad,#input-total,#input-descuento").val("");
  $(".form-especie").addClass("d-none");
}

function vistaPrevia() {
  const id_cliente = $("#select_cliente").find("option:selected").val();
  const condicion = $("#select-condicion").find("option:selected").val();
  const rutTransporte = $("#input-rut-transporte").val().trim();
  const rutChofer = $("#input-rut-chofer").val().trim();
  const patente = $("#input-patente").val().trim();
  const nombreChofer = $("#input-nombre-chofer").val().trim();


  if (!id_cliente.trim().length) {
    swal("Selecciona un cliente!", "", "error");
  } else if ($(".pedido-vacio-msg").length) {
    swal(
      "La lista de Productos está vacía!",
      "Agrega algún producto para continuar.",
      "error"
    );
  } else if (!condicion || !condicion.length) {
    swal("Selecciona la Condición de Pago", "", "error");
  } else if (hasChanged) {
    swal("Primero debes guardar los cambios del Cliente!", "", "error");
  }
  else if (hasChangedTransporte) {
    swal("Primero debes guardar los datos del Transportista!", "", "error");
  }
  else if (!checkRut(rutTransporte)) {
    swal("El RUT del encargado de Transporte no es válido", "", "error");
  } else if (!checkRut(rutChofer)) {
    swal("El RUT del Chofer no es válido", "", "error");
  } else if (!patente || !patente.length) {
    swal("Ingresa la patente del vehículo", "", "error");
  } else if (!nombreChofer || !nombreChofer.length) {
    swal("Ingresa el nombre del Chofer", "", "error");
  }
  else {
    //console.log(productosCotizados);

    if (productosCotizados.length) {
      const observaciones = $("#input-comentario").val().trim();
      $("#btn-save").removeClass("d-none");
      $(".vistaprevia-group").addClass("d-none");
      $("#btn-save").prop("disabled", false);
      printCotizacion(
        {
          id_cotizacion: "x",
          productos: productosCotizados,
          comentario: observaciones,
          condicion_pago: condicion,
          domicilio: $("#input-domicilio").val().trim(),
          ciudad: $("#select-comuna option:selected").attr("x-ciudad"),
          comuna: $("#select-comuna option:selected").attr("x-nombre"),
          razon: $("#input-razon").val().trim(),
          giro: $("#input-giro").val().trim(),
          rut: $("#input-rut").val().trim(),
          cliente: $("#select_cliente option:selected").attr("x-nombre"),
          rutTransporte: rutTransporte,
          rutChofer: rutChofer,
          patente: patente,
          nombreChofer: nombreChofer
        },
        false
      );
    } else {
      swal("Debes agregar algún producto a la Guía de Despacho!", "", "error");
    }
  }
}

function pone_tiposdeproducto() {
  $.ajax({
    beforeSend: function () {
      $("#select_tipo").html("Cargando productos...");
    },
    url: "data_ver_tipos.php",
    type: "POST",
    data: { consulta: "busca_tipos_select" },
    success: function (x) {
      $(".selectpicker").selectpicker();
      $("#select_tipo").html(x).selectpicker("refresh");
      $("#select_tipo").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          const codigo = $("#select_tipo")
            .find("option:selected")
            .attr("x-codigo")
            .trim();
          if (codigo == "HS" || codigo == "HE") {
            $(".form-especie").removeClass("d-none");
            tieneEspecies = true;
          } else {
            $(".form-especie").addClass("d-none");
            tieneEspecies = false;
          }
          carga_variedades(this.value);
          var selectEspecie = $("#select_especie");
          selectEspecie.selectpicker("val", "");
          selectEspecie.find("option").remove();
          selectEspecie.selectpicker("refresh");
          carga_especies(this.value);
        }
      );
    },
    error: function (jqXHR, estado, error) { },
  });
}

function carga_variedades(id_tipo) {
  $.ajax({
    beforeSend: function () {
      $("#select_variedad").html("Cargando variedades...");
    },
    url: "data_ver_variedades.php",
    type: "POST",
    data: { consulta: "busca_variedades_select", id_tipo: id_tipo },
    success: function (x) {
      $("#select_variedad").val("default").selectpicker("refresh");
      $("#select_variedad").html(x).selectpicker("refresh");
      $("#select_variedad").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          $("#modal-agregar-producto").find("#input-cantidad").val("");
          const codigo = $("#select_tipo")
            .find("option:selected")
            .attr("x-codigo");
          $("#modal-agregar-producto").find(".btn-precio-detalle-container").html("")
          const precio_detalle = $(this).find("option:selected").attr("x-precio-detalle");
          const precio = $(this).find("option:selected").attr("x-precio") ?? "";
          let formatoPrecio = precio.toLocaleString('es-ES', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          });
          if (precio_detalle && precio_detalle.length) {
            let formatoDetalle = precio_detalle.toLocaleString('es-ES', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2,
            });

            $("#modal-agregar-producto").find(".btn-precio-detalle-container").html(`
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.selectedPrice = 'mayorista';calcularSubtotal();">Mayorista: $ ${formatoPrecio}</button>
              <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.selectedPrice = 'detalle';calcularSubtotal()">Detalle: $ ${formatoDetalle}</button>
            `)
          }
          else {
            $("#modal-agregar-producto").find(".btn-precio-detalle-container").html(`
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.selectedPrice = 'mayorista';calcularSubtotal();">Mayorista: $ ${formatoPrecio}</button>
            `)
          }
        }
      );
    },
    error: function (jqXHR, estado, error) { },
  });
}

function carga_especies(id_tipo) {
  $.ajax({
    beforeSend: function () {
      $("#select_especie").html("Cargando especies...");
    },
    url: "data_ver_variedades.php",
    type: "POST",
    data: { consulta: "busca_especies_select", id_tipo: id_tipo },
    success: function (x) {
      $("#select_especie").val("default").selectpicker("refresh");
      $("#select_especie").html(x).selectpicker("refresh");
      $("#select_especie").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          $("#modal-agregar-producto").find("#input-cantidad").val("");
          const codigo = $("#select_tipo")
            .find("option:selected")
            .attr("x-codigo");
        }
      );
    },
    error: function (jqXHR, estado, error) { },
  });
}

function addToPedido() {
  const producto = $("#select_tipo")
    .find("option:selected")
    .attr("x-nombre")
    .trim();
  const variedad = $("#select_variedad :selected").text();

  const especie = $("#select_especie").val()
    ? $("#select_especie").find("option:selected").attr("x-nombre").trim()
    : null;

  const id_especie = $("#select_especie").val();
  const cantidad = $("#input-cantidad").val().trim();
  const total = $("#input-total").val().trim();

  const tipoDescuento = $("#select_descuento option:selected").val();
  const valorDescuento = $("#input-descuento").val().trim();

  if (
    !cantidad ||
    !cantidad.length ||
    isNaN(cantidad) ||
    parseInt(cantidad) < 1
  ) {
    swal("Ingresa la cantidad", "", "error");
  } else if (!producto.length) {
    swal("Debes elegir un producto!", "", "error");
  } else if (!variedad.length) {
    swal("Debes elegir una variedad de producto!", "", "error");
  } else if (!total || !total.length) {
    swal("El precio o monto total no puede quedar vacío!", "", "error");
  } else if (
    (tipoDescuento == "porcentual" || tipoDescuento == "fijo") &&
    (!valorDescuento || !valorDescuento.length || parseInt(valorDescuento) < 1)
  ) {
    swal("Ingresa el valor del descuento!", "", "error");
  } else {
    $("#modal-agregar-producto").modal("hide");
    const codigo = $("#select_variedad :selected").attr("x-codigofull");
    const nombre_variedad = $("#select_variedad :selected").attr("x-nombre");
    const precio = window.selectedPrice === "detalle" ? $("#select_variedad :selected").attr("x-precio-detalle") : $("#select_variedad :selected").attr("x-precio");
    const subtotal = parseInt(precio) * parseInt(cantidad);

    funcAddToPedido({
      index: productoIndex++,
      tipo: producto,
      id_tipo: $("#select_tipo").find("option:selected").val(),
      variedad: nombre_variedad,
      id_variedad: $("#select_variedad").find("option:selected").val(),
      cantidad: cantidad,
      especie: especie,
      id_especie: id_especie,
      codigo: codigo,
      precio: precio,
      total: total,
      subtotal: subtotal,
      descuento:
        tipoDescuento && tipoDescuento.length
          ? {
            tipo: tipoDescuento,
            valor: valorDescuento,
          }
          : null,
    });
  }
}

function funcAddToPedido(producto) {
  productosCotizados.push(producto);

  const {
    index,
    variedad,
    especie,
    id_tipo,
    codigo,
    id_variedad,
    id_especie,
    cantidad,
    precio,
    subtotal,
    descuento,
  } = producto;
  const nombre_producto = `${variedad} ${especie ? especie : ""}`;

  if ($(".pedido-vacio-msg").length) {
    $("#table-pedido tbody").html("");
  }

  var celda = `<tr x-id-tipo='${id_tipo}' x-id-variedad='${id_variedad}' x-id-especie='${id_especie ? id_especie : ""
    }'
    x-cantidad='${cantidad}'
    >
      <td scope="row">${index + 1}</td>
      <td>${codigo}</td>
      <td>${nombre_producto}</td>
      <td>${cantidad}</td>
      <td>$${precio}</td>
      <td>$${subtotal}</td>
      <td>${descuento && descuento.tipo == "porcentual"
      ? "-" + descuento.valor + "%"
      : descuento && descuento.tipo == "fijo"
        ? "-$" + descuento.valor
        : ""
    }</td>
      <td class="text-center"><button class='removeme btn btn-xs fa fa-trash' style='font-size:1.7em' onclick='eliminar_art(this, ${producto.index
    })'></button></td>
      </tr>`;
  $("#table-pedido tbody").append(celda);
}

function eliminar_art(btn, i) {
  swal("¿ELIMINAR este Producto del Pedido?", "", {
    icon: "warning",
    buttons: {
      cancel: "Cancelar",
      catch: {
        text: "ELIMINAR",
        value: "catch",
      },
    },
  }).then((value) => {
    switch (value) {
      case "catch":
        $(btn).parent().parent().remove();
        productosCotizados = productosCotizados.filter(function (
          e,
          index,
          arr
        ) {
          return e.index != i;
        });

        if ($("#table-pedido > tbody > tr").length < 1) {
          $("#table-pedido > tbody").append(`
              <tr class="pedido-vacio-msg">
                <th scope="row" colspan="8" class="text-center"><span class="text-muted">Agrega Productos a la Guía de Despacho</span></th>
              </tr>
            `);
        }

      default:
        break;
    }
  });
}

function ClearPedido() {
  productosCotizados = [];

  $("#table-pedido > tbody").html(`
              <tr class="pedido-vacio-msg">
                <th scope="row" colspan="8" class="text-center"><span class="text-muted">Agrega Productos a la Guía de Despacho</span></th>
              </tr>
  `);
  $("#select_cliente,#select-condicion,#select-comuna")
    .val("")
    .trigger("change");
  $("input").val("");
  pone_datos_transporte();

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
    razon,
    giro,
    rutChofer,
    rutTransporte,
    patente,
    nombreChofer
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
                        <h5 style="color:#F7BE81 !important;">Guía Despacho</h5>
                        <h5 style="color:#F7BE81 !important;">N° <span id="id-cotizacion">${id_cotizacion}</span></h5>
                    </div>
                </div>
              </div>
              
              <div class="customrow pt-3 pb-2 mt-4" style="padding-left:60px;border-top: 1px solid #c9c9c9bd;border-bottom: 1px solid #c9c9c9bd;">
                <div class="column">
                  <div class="row">
                    <div class="col">
                      <h6 style="color:grey !important">Razón Social</h6>
                      <h6 style="">${razon && razon.length ? razon.toUpperCase() : cliente
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
                  <h6 style="">${nombre_real && nombre_real.length
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

  let monto = 0.0;
  let montodescuento = 0.0;

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

      $("#tabla_producto > tbody").append(
        `
        <tr>
          <td>${codigo}</td>
          <td>${nombre_producto}</td>
          <td>${cantidad}</td>
          <td>$${formatearMonto(precio)}</td>
          <td>${descuento && descuento.tipo == "porcentual"
          ? "-" + descuento.valor + "%"
          : descuento && descuento.tipo == "fijo"
            ? "-$" + formatearMonto(parseInt(descuento.valor))
            : ""
        }</td>
          <td>$${formatearMonto(subtotal)}</td>
          
        </tr>                    
      `
      );
      monto += parseInt(total.toString().replace(/\./g, ''));
      if (descuento) {
        const totaltemp = cantidad * precio;
        if (descuento.tipo == "fijo") {
          montodescuento += parseInt(descuento.valor);
        } else if (descuento.tipo == "porcentual") {
          const descontado = (totaltemp * parseInt(descuento.valor)) / 100;
          montodescuento += descontado;
        }
      }
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
                            <td colspan="5" rowspan="8">COMENTARIO ${comentario && comentario.length
      ? "<br><br>" + comentario.toUpperCase()
      : ""
    }</td>
                            <td>Descuento</td>
                            <td>${montodescuento && montodescuento > 0
      ? "-$" +
      formatearMonto(Math.round(montodescuento))
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
  $("#modal-vistaprevia").attr("x-total", totaltemp);
  getFoliosDisponibles(null, null, 52, null, null);
  $("#modal-vistaprevia").modal("show");
}

function cargarDatosCliente(id_cliente) {
  $("#input-rut,#input-domicilio,#input-razon,#input-giro").val("");
  $("#select_comuna").val("default").selectpicker("refresh");

  $.ajax({
    beforeSend: function () { },
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: {
      consulta: "cargar_datos_cliente",
      id_cliente: id_cliente,
    },
    success: function (x) {
      if (x.length) {
        try {
          const data = JSON.parse(x);
          const { rut, domicilio, comuna, giro, razon } = data;
          $("#input-rut").val(rut);
          $("#input-domicilio").val(domicilio);
          $("#select-comuna").val(comuna).selectpicker("refresh");
          $("#input-razon").val(razon);
          $("#input-giro").val(giro);
        } catch (error) { }
      }
    },
    error: function (jqXHR, estado, error) { },
  });
}

function calcularSubtotal() {
  const precioVal = $("#select_variedad option:selected").attr("x-precio");
  const precio_detalleVal = $("#select_variedad option:selected").attr("x-precio-detalle");

  const precio = window.selectedPrice == "detalle" ? precio_detalleVal : precioVal;
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

function setChanged(value) {
  hasChanged = value;
  if (value && $("#btn-guardar-cambios").hasClass("d-none")) {
    $("#btn-guardar-cambios").removeClass("d-none");
  } else if (!value && !$("#btn-guardar-cambios").hasClass("d-none")) {
    $("#btn-guardar-cambios").addClass("d-none");
  }
}

function setChangedTransporte(value) {
  hasChangedTransporte = value;
  if (value && $("#btn-guardar-transporte").hasClass("d-none")) {
    $("#btn-guardar-transporte").removeClass("d-none");
  } else if (!value && !$("#btn-guardar-transporte").hasClass("d-none")) {
    $("#btn-guardar-transporte").addClass("d-none");
  }
}

function guardarCambiosCliente() {
  const rut = $("#input-rut").val().trim();
  const domicilio = $("#input-domicilio").val().trim();
  const razon = $("#input-razon").val().trim();
  const giro = $("#input-giro").val().trim();
  const comuna = $("#select-comuna option:selected").val();
  const id_cliente = $("#select_cliente option:selected").val();

  if (!id_cliente || !id_cliente.length) {
    swal("Selecciona un Cliente!", "", "error");
  } else if (!razon || !razon.length) {
    swal("Ingresa la Razón Social!", "", "error");
  } else if (!domicilio || !domicilio.length) {
    swal("Ingresa el Domicilio!", "", "error");
  } else if (!giro || !giro.length) {
    swal("Ingresa el Giro!", "", "error");
  } else if (!comuna || !comuna.length) {
    swal("Selecciona la Comuna!", "", "error");
  } else if (!checkRut(rut)) {
    swal("El RUT ingresado no es válido", "", "error");
  } else {
    setChanged(false);

    $.ajax({
      url: "data_ver_cotizaciones.php",
      type: "POST",
      data: {
        consulta: "guardar_cambios_cliente",
        id_cliente: id_cliente,
        rut: rut,
        domicilio: domicilio.length ? domicilio : null,
        comuna: comuna,
        razonSocial: razon.length ? razon : null,
        giro: giro.length ? giro : null,
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

function generarGuiaDespacho(folio, caf) {
  const id_cliente = $("#select_cliente").find("option:selected").val();
  const comuna = $("#select-comuna").find("option:selected").text();
  const condicion = $("#select-condicion").find("option:selected").val();
  const rut = $("#input-rut").val().trim();
  const domicilio = $("#input-domicilio").val().trim();
  const razon = $("#input-razon").val().trim();
  const giro = $("#input-giro").val().trim();
  const rutTransporte = $("#input-rut-transporte").val().trim();
  const rutChofer = $("#input-rut-chofer").val().trim();
  const patente = $("#input-patente").val().trim();
  const nombreChofer = $("#input-nombre-chofer").val().trim();

  const total = $("#modal-vistaprevia").attr("x-total");


  if (hasChanged) {
    swal("Primero debes guardar los cambios del Cliente!", "", "error");
  } else if (!id_cliente || !id_cliente.length) {
    swal("Selecciona un Cliente!", "", "error");
  } else if (!razon || !razon.length) {
    swal("Ingresa la Razón Social!", "", "error");
  } else if (!domicilio || !domicilio.length) {
    swal("Ingresa el Domicilio!", "", "error");
  } else if (!giro || !giro.length) {
    swal("Ingresa el Giro!", "", "error");
  } else if (!comuna || !comuna.length) {
    swal("Selecciona la Comuna!", "", "error");
  } else if (!checkRut(rut)) {
    swal("El RUT ingresado no es válido", "", "error");
  } else if ($(".pedido-vacio-msg").length) {
    swal(
      "La lista de Productos está vacía!",
      "Agregá algún producto para continuar.",
      "error"
    );
  } else if (!condicion || !condicion.length) {
    swal("Selecciona la Condición de Pago", "", "error");
  } else if (!folio) {
    swal("No hay Folio para la Guía de Despacho", "", "error");
  } else {
    if (productosCotizados.length) {
      console.log(productosCotizados);
      setLoading(true);
      const jsonarray = JSON.stringify(productosCotizados);
      const observaciones = $("#input-comentario").val().trim();

      $.ajax({
        url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
        type: "POST",
        data: {
          consulta: "generar_guia_despacho",
          id_cliente: id_cliente,
          jsonarray: jsonarray,
          observaciones: observaciones,
          condicion_pago: condicion,
          folio: folio,
          caf: caf,
          giro: giro,
          comuna: $("#select-comuna").find("option:selected").attr("x-nombre"),
          razon: razon,
          domicilio: domicilio,
          total: total,
          rut: rut.replace(/\./g, ''),
          rutTransporte: rutTransporte,
          rutChofer: rutChofer,
          patente: patente,
          nombreChofer: nombreChofer
        },
        success: function (x) {
          console.log(x);
          if (x.includes("path")) {
            try {
              const data = JSON.parse(x);
              //checkEstadoAfterSendingDTE(data.trackID, id_cotizacion, "FAC");
              ClearPedido();
              window.open(
                `verpdf.php?tipo=GD&folio=${folio}&file=${data.path}`,
                "_blank"
              );
              setLoading(false);
              $("#modal-vistaprevia").modal("hide");
              swal(
                "Generaste la Guía de Despacho correctamente!",
                "Chequea su estado clickeando sobre el TRACK ID",
                "success"
              );
              setChanged(false);
            } catch (error) {
              ClearPedido();
              setLoading(false);
              $("#modal-vistaprevia").modal("hide");
              swal(
                "La Guía de Despacho se generó correctamente, pero hubo un error al abrir el PDF. Intenta abrirlo desde el Historial de Guías",
                "",
                "warning"
              );
              setChanged(false);
            }
          } else if (x.includes("ERROR_ENVIO_SII")) {
            setLoading(false)
            $("#modal-vistaprevia").modal("hide");
            ClearPedido();
            swal("La Guía de Despacho se guardó en el Sistema, pero hubo un error al enviarla al SII.", "Deberás buscar la Guía en el Historial e intentar reenviarla.", "error");
          } else {
            swal("Ocurrió un error al generar la Guía de Despacho", x, "error");
            setLoading(false);
          }
        },
        error: function (jqXHR, estado, error) {
          swal(
            "Ocurrió un error al guardar la Guía de Despacho",
            error.toString(),
            "error"
          );
          setLoading(false);
        },
      });
    } else {
      swal("Debes agregar algún producto a la Guía de Despacho!", "", "error");
    }
  }
}

function setLoading(val) {
  if (val) {
    $(".loader-anular").css({ display: "flex" });
    $(".modal-vistaprevia-wrapper").css({ display: "none" });
  } else {
    $(".loader-anular").css({ display: "none" });
    $(".modal-vistaprevia-wrapper").css({ display: "block" });
  }
}

function setRazonSocial2() {
  const nombre = $("#select_cliente option:selected").attr("x-nombre");

  if (nombre && nombre.length) {
    $("#input-razon").val(nombre);
    setChanged(true);
  }
}

function pone_comunas() {
  $("#select_cliente").prop("disabled", true);
  $.ajax({
    beforeSend: function () {
      $("#select-comuna,#select-comuna2").html("Cargando lista de comunas...");
    },
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: { consulta: "pone_comunas" },
    success: function (x) {
      $("#select-comuna,#select-comuna2").html(x).selectpicker("refresh");
      $("#select-comuna").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          const id_cliente = $("#select_cliente option:selected").val();
          if (id_cliente && id_cliente.length) setChanged(true);
        }
      );

      $("#select_cliente").prop("disabled", false).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {
      $("#select_cliente").prop("disabled", false).selectpicker("refresh");
    },
  });
}

function pone_datos_transporte() {
  $.ajax({
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "get_datos_transporte"
    },
    success: function (x) {
      if (x.length && !x.includes("error")) {
        try {
          const data = JSON.parse(x);
          const { rutTransporte, rutChofer, patente, nombreChofer } = data;
          $("#input-rut-transporte").val(rutTransporte);
          $("#input-rut-chofer").val(rutChofer);
          $("#input-patente").val(patente);
          $("#input-nombre-chofer").val(nombreChofer);
        } catch (error) {
          console.log(error)
        }
      } else {
        console.log(x)
      }
    },

  });
}

function guardarCambiosTransporte() {
  const rutTransporte = $("#input-rut-transporte").val().trim();
  const rutChofer = $("#input-rut-chofer").val().trim();
  const patente = $("#input-patente").val().trim();
  const nombreChofer = $("#input-nombre-chofer").val().trim();

  if (!checkRut(rutTransporte)) {
    swal("El RUT del encargado de Transporte no es válido", "", "error");
  } else if (!checkRut(rutChofer)) {
    swal("El RUT del Chofer no es válido", "", "error");
  } else if (!patente || !patente.length) {
    swal("Ingresa la patente del vehículo", "", "error");
  } else if (!nombreChofer || !nombreChofer.length) {
    swal("Ingresa el nombre del Chofer", "", "error");
  } else {
    setChangedTransporte(false);
    $.ajax({
      url: "data_ver_facturacion.php",
      type: "POST",
      data: {
        consulta: "guardar_datos_transporte",
        rutTransporte: rutTransporte,
        rutChofer: rutChofer,
        patente: patente,
        nombreChofer: nombreChofer,
      },
      success: function (x) {
        if (x.includes("success")) {
          swal("Guardaste los datos correctamente!", "", "success");
        } else {
          swal("Ocurrió un error al guardar los datos", x, "error");
          setChangedTransporte(false);
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error al guardar los datos", error, "error");
        setChangedTransporte(false);
      },
    });
  }
}
