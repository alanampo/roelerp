let productosCotizados = [];
let productoIndex = 0;
let hasChanged = false;
let tieneEspecies = false;
let currentCotizacion;

const phpFile = "data_ver_cotizaciones.php";

let qrVideo = null;
let qrCanvas = null;
let qrCanvasContext = null;
let qrScanningActive = false;
$(document).ready(function () {
  pone_clientes();
  pone_comunas();

  // Initialize QR scanner when modal opens
  $("#modal-qr").on("shown.bs.modal", function () {
    initializeQRScanner();
  });

  $("#modal-qr").on("hidden.bs.modal", function () {
    stopQRScanner();
  });

  // getSucursalesSelect();

  // getStarkenData();

  $("#select-tipo-envio").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      $("#select-sucursal").html("").selectpicker();
      $(".col-direccion-envio-2").addClass("d-none");
      if (this.value == 0) {
        getTransportistasSelect();
        $(".col-select-transp,.col-select-sucursal").removeClass("d-none");
        $(".col-direccion-envio").addClass("d-none");
      } else if (this.value == 1) {
        getTransportistasSelect();
        $(".col-select-sucursal").addClass("d-none");
        $(".col-select-transp").removeClass("d-none");
        $(".col-direccion-envio").removeClass("d-none");
      }
      else if (this.value == 2) {
        getTransportistasSelect();
        $(".col-select-sucursal,.col-direccion-envio").addClass("d-none");
        $(".col-select-transp").removeClass("d-none");
        $(".col-direccion-envio-2").removeClass("d-none");
      }
      else {
        $(".col-select-transp,.col-select-sucursal").addClass("d-none");
        $(".col-direccion-envio").addClass("d-none");
      }
      $("#select-transportista").val("default").selectpicker("refresh");
    }
  );
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

  $("#input-precio")
    .on("keypress", function (evt) {
      var $txtBox = $(this);
      var charCode = evt.which ? evt.which : evt.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
        return false;
      else {
        var len = $txtBox.val().length;
        var index = $txtBox.val().indexOf(".");
        if (index > 0 && charCode == 46) {
          return false;
        }
        if (index > 0) {
          var charAfterdot = len + 1 - index;
          if (charAfterdot > 3) {
            return false;
          }
        }
      }
      return $txtBox; //for chaining
    })
    .on("paste", function (e) {
      return false;
    });
  $("#modal-vistaprevia").on("hidden.bs.modal", function () {
    $("#btn_guardarpedido").prop("disabled", false);
  });

  document.getElementById("defaultOpen").click();
});

function getStarkenData() {
  $.ajax({
    beforeSend: function () { },
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: { consulta: "get_starken_sucursales" },
    success: function (x) {
      console.log(x)
    },
    error: function (jqXHR, estado, error) { },
  });
}

function updateUniqid() {
  $.ajax({
    beforeSend: function () { },
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: { consulta: "genera_uniqid" },
    success: function (x) {
      swal(x, "", "info");
    },
    error: function (jqXHR, estado, error) { },
  });
}

function pone_clientes() {
  $.ajax({
    beforeSend: function () {
      $("#select_cliente").html("Cargando lista de clientes...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "get_clientes_select",
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
  const id_cliente = $("#btn_guardarpedido").attr("x-id-cliente") ?? $("#select_cliente").find("option:selected").val();
  const condicion = $("#select-condicion").find("option:selected").val();

  if (!id_cliente.trim().length) {
    swal("Selecciona un cliente!", "", "error");
  } else if ($(".pedido-vacio-msg").length) {
    swal(
      "La cotización está vacía!",
      "Agrega algún producto para continuar.",
      "error"
    );
  } else if (!condicion || !condicion.length) {
    swal("Selecciona la Condición de Pago", "", "error");
  } else if (hasChanged) {
    swal("Primero debes guardar los cambios del Cliente!", "", "error");
  } else {
    $("#btn_guardarpedido").prop("disabled", true);

    if (productosCotizados.length) {
      const observaciones = $("#input-comentario").val().trim();
      $("#btn-save").removeClass("d-none");
      $(".vistaprevia-group").addClass("d-none");
      $("#btn-save").prop("disabled", false);
      printCotizacion(
        {
          id_cotizacion: "X",
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
        },
        false
      );
    } else {
      swal("Debes agregar algún producto a la Cotización!", "", "error");
    }
  }
}

function cerrarModal(id) {
  $("#" + id).modal("hide");
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
  const total = $("#input-total").val().trim().split(".").join("");

  const tipoDescuento = $("#select_descuento option:selected").val();
  const valorDescuento = $("#input-descuento").val().trim().split(".").join("");

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
      id_tipo: parseInt($("#select_tipo").find("option:selected").val()),
      variedad: nombre_variedad,
      id_variedad: parseInt(
        $("#select_variedad").find("option:selected").val()
      ),
      cantidad: parseInt(cantidad),
      especie: especie && especie.length ? especie : null,
      id_especie: id_especie && id_especie.length ? id_especie : null,
      codigo: codigo,
      precio: parseFloat(precio),
      total: parseFloat(total),
      subtotal: parseFloat(subtotal),
      descuento:
        tipoDescuento &&
          tipoDescuento.length &&
          valorDescuento &&
          valorDescuento.length
          ? {
            tipo: tipoDescuento,
            valor: parseFloat(valorDescuento.split(".").join("")),
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
                <th scope="row" colspan="8" class="text-center"><span class="text-muted">Agrega Productos a la Cotización</span></th>
              </tr>
            `);
        }

      default:
        break;
    }
  });
}

function showPedidoExitosoDialog() {
  var modal = document.getElementById("ModalAdminPedido");
  modal.style.display = "block";
}

function ClearPedido() {
  productosCotizados = [];

  $("#table-pedido > tbody").html(`
              <tr class="pedido-vacio-msg">
                <th scope="row" colspan="8" class="text-center"><span class="text-muted">Agrega Productos a la Cotización</span></th>
              </tr>
  `);
  $("#select_cliente,#select-condicion,#select-comuna")
    .val("")
    .trigger("change");
  $("input").val("");
  $("#btn_guardarpedido").prop("disabled", false);
}

function abrirTab(evt, tabName) {
  var i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  $(".tabco").addClass("d-none").removeClass("d-block");

  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  evt.currentTarget.className += " active";
  if (tabName == "historial") {
    $(".tab-historial").addClass("d-block");
    setEditing(false);
    loadHistorial();
  } else if (tabName == "nueva") {
    $(".tab-nueva").addClass("d-block");
  }
  else if (tabName == "ordenes") {
    $(".tab-ordenes").addClass("d-block");
    loadOrdenes();
  }
  //busca_entradas(tabName);
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
    razon,
    giro,
    telcliente
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
                        <h5 style="color:#F7BE81 !important;">Cotización</h5>
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

  let monto = 0;
  let montodescuento = 0;

  if (productos) {
    console.log(productos);
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
          <td>$${precio.toLocaleString("es-ES", {
          minimumFractionDigits: 0,
        })}</td>
          <td>${descuento && descuento.tipo == "porcentual"
          ? "-" + descuento.valor + "%"
          : descuento && descuento.tipo == "fijo"
            ? "-$" +
            descuento.valor.toLocaleString("es-ES", {
              minimumFractionDigits: 0,
            })
            : ""
        }</td>
          <td>$${subtotal.toLocaleString("es-ES", {
          minimumFractionDigits: 0,
        })}</td>
          
        </tr>                    
      `
      );
      monto += parseFloat(total);
      if (descuento) {
        const totaltemp = cantidad * Math.round(precio);
        if (descuento.tipo == "fijo") {
          montodescuento += parseFloat(descuento.valor);
        } else if (descuento.tipo == "porcentual") {
          const descontado = (totaltemp * parseFloat(descuento.valor)) / 100;
          montodescuento += descontado;
        }
      }
    });
  }

  const iva = Math.round((19 * monto) / 100).toLocaleString("es-ES", {
    minimumFractionDigits: 0,
  });
  const totaltemp = Math.round(monto * 1.19);
  const totalpalabras = NumeroALetras(totaltemp);
  const total = totaltemp.toLocaleString("es-ES", { minimumFractionDigits: 0 });
  const afecto = Math.round(monto).toLocaleString("es-ES", {
    minimumFractionDigits: 0,
  });
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
    }<br><br>
    <br><br>
    <p style='text-wrap: balance;'><b>NOTA:</b> Cotización válida por 24 horas. Producto vivo: puede variar en tamaño/color. Abrir e hidratar el día de llegada. Envío por cuenta y riesgo del cliente; el riesgo se transfiere al entregar al transportista. Reclamos solo dentro de 24 h con fotos del embalaje y contenido. Precios no incluyen flete ni seguro; seguro opcional a solicitud. Al aceptar/pagar, declaras conocer y aceptar los Términos y Condiciones: https://roelplant.cl/terminos.</p>
    </td>
                            <td>Descuento</td>
                            <td>${montodescuento && montodescuento > 0
      ? "-$" +
      montodescuento.toLocaleString("es-ES", {
        minimumFractionDigits: 0,
      })
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
  if (isPrinting) {
    printRemito(1, id_cotizacion);
  } else {
    $("#modal-vistaprevia").attr("x-total", total.trim().split(".").join(""));
    $("#modal-vistaprevia").attr("x-id", id_cotizacion);
    $("#modal-vistaprevia").modal("show");
  }
}

function printRemito(tipo, id_cotizacion) {
  if (tipo == 0) {
    createPDF(id_cotizacion ? id_cotizacion : $("#id-cotizacion").text());
  } else if (tipo == 1) {
    document.getElementById("ocultar").style.display = "none";
    document.getElementById("miVentana").style.display = "block";
    $("#modal-vistaprevia").modal("hide");
    setTimeout(
      "window.print();printRemito(2);document.title = 'Cotizaciones'",
      500
    );
  } else {
    document.getElementById("ocultar").style.display = "block";
    document.getElementById("miVentana").style.display = "none";
    document.title = "Cotizaciones";
  }
}

function cargarDatosCliente(id_cliente) {
  if (!id_cliente) {
    $("#label-vendedor-cliente").text("-");
    return;
  }
  $("#input-rut,#input-domicilio,#input-razon,#input-giro").val("");
  $("#select_comuna").val("default").selectpicker("refresh");
  $("#label-vendedor-cliente").text("-");

  $.ajax({
    beforeSend: function () { },
    url: phpFile,
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

  // Cargar vendedor del cliente
  $.ajax({
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "obtener_vendedor_cliente",
      id_cliente: id_cliente,
    },
    success: function (response) {
      try {
        const data = JSON.parse(response);
        $("#label-vendedor-cliente").text(data.vendedor || "Sin asignar");
      } catch (error) {
        $("#label-vendedor-cliente").text("Sin asignar");
      }
    },
    error: function () {
      $("#label-vendedor-cliente").text("Sin asignar");
    },
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

function loadHistorial() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Buscando, espere...");
    },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "cargar_historial",
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
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
      $("#tabla_entradas").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}


function loadOrdenes() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_ordenes").html("Buscando, espere...");
    },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "cargar_historial_ordenes_envio",
    },
    success: function (x) {
      $("#tabla_ordenes").html(x);
      $("#tabla_ordenes_envio").DataTable({
        pageLength: 50,
        order: [[0, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ órdenes por página",
          zeroRecords: "No hay órdenes",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay órdenes",
          infoFiltered: "(filtrado de _MAX_ órdenes en total)",
          lengthMenu: "Mostrar _MENU_ órdenes",
          loadingRecords: "Cargando...",
          processing: "Procesando...",
          search: "Buscar:",
          zeroRecords: "No se encontraron órdenes",
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
      $("#tabla_ordenes").html(
        "Ocurrió un error al cargar los datos: " + estado + " " + error
      );
    },
  });
}
function eliminarCotizacion(rowid) {
  swal("Estás seguro/a de ELIMINAR la cotización?", "", {
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
          url: phpFile,
          data: { consulta: "eliminar_cotizacion", rowid: rowid },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste la Cotización correctamente!", "", "success");
              loadHistorial();
            } else {
              swal("Ocurrió un error al eliminar la Cotización", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}


function eliminarOrdenEnvio(rowid) {
  swal("Estás seguro/a de ELIMINAR la Órden de Envío?", "", {
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
          url: phpFile,
          data: { consulta: "eliminar_orden_envio", rowid: rowid },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste la Órden correctamente!", "", "success");
              loadOrdenes();
            } else {
              swal("Ocurrió un error al eliminar la Órden", data, "error");
            }
          },
        });

        break;

      default:
        break;
    }
  });
}

function printDataCotizacion(id, btn, id_cliente) {
  $("#btn-save").attr("x-id", id)
  $("#btn-save").attr("x-id-cliente", id_cliente ?? "")
  if (id_cliente) {
    $("#btn_guardarpedido").attr("x-id-cliente", id_cliente)
  }
  else {
    $("#btn_guardarpedido").removeAttr("x-id-cliente")
  }
  currentCotizacion = null;
  if (btn) $(btn).prop("disabled", true);
  document.title = "cotizacion_" + id;
  $.ajax({
    url: phpFile,
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
            domicilio2,
            ciudad,
            comuna,
            cliente,
            region,
            email,
            rut,
            giro,
            razon,
            productos,
            condicion_pago,
            telcliente
          } = data;

          currentCotizacion = {
            id_cotizacion: id,
            data: data,
          };

          console.log(data);
          $("#btn-save").addClass("d-none");
          $(".vistaprevia-group").removeClass("d-none");
          printCotizacion(
            {
              id_cotizacion: id,
              productos: productos,
              comentario: comentario,
              domicilio: domicilio,
              ciudad: ciudad,
              comuna: comuna,
              giro: giro,
              razon: razon,
              rut: rut,
              cliente: cliente,
              email: email,
              condicion_pago: condicion_pago,
              region
            },
            false
          );
        } catch (error) {
          console.log(error);
        }
      }
      setTimeout(() => {
        if (btn) $(btn).prop("disabled", false);
      }, 4000);
    },
    error: function (jqXHR, estado, error) {
      swal(
        "Ocurrió un error al guardar la Cotización",
        error.toString(),
        "error"
      );
      $("#btn_guardarpedido").prop("disabled", false);
      if (btn) $(btn).prop("disabled", false);
    },
  });
}

function modalCambiarEstado(id) {
  $("#modal-estado").attr("x-id", id);
  $("#modal-estado").modal("show");
}

function crearPedido(event, btn) {
  event.preventDefault();
  event.stopPropagation();
  const id = $("#modal-estado").attr("x-id");
  location.href = `/cargar_pedido.php?id=${id}`
}

function guardarEstado(estado) {
  $("#modal-estado").modal("hide");
  const id = $("#modal-estado").attr("x-id");
  $.ajax({
    url: phpFile,
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
      url: phpFile,
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

//^*******************************

function GuardarPedido() {
  const edit_id_cliente = $("#btn-save").attr("x-id-cliente");
  const id_cotizacion = $("#btn-save").attr("x-id");
  const id_cliente = edit_id_cliente && edit_id_cliente.length ? edit_id_cliente : $("#select_cliente").find("option:selected").val();
  const comuna = $("#select-comuna").find("option:selected").val();
  const condicion = $("#select-condicion").find("option:selected").val();

  const rut = $("#input-rut").val().trim();
  const domicilio = $("#input-domicilio").val().trim();
  const razon = $("#input-razon").val().trim();
  const giro = $("#input-giro").val().trim();

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
      "La cotización está vacía!",
      "Agregá algún producto para continuar.",
      "error"
    );
  } else if (!condicion || !condicion.length) {
    swal("Selecciona la Condición de Pago", "", "error");
  } else {
    if (productosCotizados.length) {
      $("#modal-vistaprevia").modal("hide");
      $("#btn-save").prop("disabled", true);
      const jsonarray = JSON.stringify(productosCotizados);
      const observaciones = $("#input-comentario").val().trim();
      let monto = 0.0;
      let montodescuento = 0;
      productosCotizados.forEach(function (producto, i) {
        const { total, subtotal } = producto;
        const totalito = parseInt(total.toString().replace(/\./g, ""));
        monto += totalito;
        montodescuento += subtotal - totalito;
      });
      const total = Math.round(monto * 1.19);
      console.log(currentCotizacion);
      $.ajax({
        url: phpFile,
        type: "POST",
        data: {
          consulta: "guardar_cotizacion",
          id_cliente: id_cliente,
          jsonarray: jsonarray,
          observaciones: observaciones,
          condicion_pago: condicion,
          total: total,
          id_cotizacion: id_cotizacion && id_cotizacion.length ? id_cotizacion : null
        },
        success: function (x) {
          console.log(x);
          if (x.trim().includes("pedidonum")) {
            var idPedido = x.trim().includes("pedidonum:")
              ? x.trim().replace("pedidonum:", "")
              : null;

            document.title = "cotizacion_" + idPedido;
            printCotizacion(
              {
                id_cotizacion: idPedido,
                productos: productosCotizados,
                comentario: observaciones,
                condicion_pago: condicion,
                domicilio: $("#input-domicilio").val().trim(),
                ciudad: $("#select-comuna option:selected").attr("x-ciudad"),
                comuna: $("#select-comuna option:selected").attr("x-nombre"),
                rut: $("#input-rut").val().trim(),
                cliente: $("#select_cliente option:selected").attr("x-nombre"),
                razon: $("#input-razon").val().trim(),
              },
              true
            );
            $("#btn-save").prop("disabled", false);
            setEditing(false);
            setChanged(false);
            currentCotizacion = null;
          } else {
            swal("Ocurrió un error!", x, "error");
            $("#modal-vistaprevia").modal("show");
            $("#btn-save").prop("disabled", false);
          }
        },
        error: function (jqXHR, estado, error) {
          swal(
            "Ocurrió un error al guardar la Cotización",
            error.toString(),
            "error"
          );
          $("#btn-save").prop("disabled", false);
          $("#modal-vistaprevia").modal("show");
        },
      });
    } else {
      swal("Debes agregar algún producto a la Cotización!", "", "error");
    }
  }
}

function createPDF(id_cotizacion) {
  $("#modal-vistaprevia").modal("hide");
  document.getElementById("ocultar").style.display = "none";
  document.getElementById("miVentana").style.display = "block";
  var element = document.getElementById("miVentana");
  html2pdf(element, {
    margin: 0,
    padding: 0,
    filename: `cotizacion_${id_cotizacion ? id_cotizacion : "x"}.pdf`,
    image: { type: "jpeg", quality: 1 },
    html2canvas: { scale: 1, logging: true },
    jsPDF: { unit: "in", format: "A4", orientation: "P" },
    class: createPDF,
  }).then(function () {
    printRemito(2);
  });
}

function sendMail(btn) {
  const email = currentCotizacion.data.email;
  if (!email || !email.length) {
    swal("El Cliente no tiene un Email configurado", email, "error");
    return;
  }

  if (!validEmail(email)) {
    swal("El Email del cliente no es válido", email, "error");
    return;
  }

  swal(`Enviar Cotización por Email?`, "", {
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
    const enviar = (resp) => {
      $("#modal-vistaprevia").modal("hide");
      document.getElementById("ocultar").style.display = "none";
      document.getElementById("miVentana").style.display = "block";
      var element = document.getElementById("miVentana");

      $(btn).prop("disabled", true);

      html2pdf() // move your config in the .set({...}) function below
        .set({
          margin: 0,
          padding: 0,
          image: { type: "jpeg", quality: 1 },
          html2canvas: { scale: 1, logging: true },
          filename: `cotizacion_${currentCotizacion.id_cotizacion}.pdf`,
          jsPDF: { unit: "in", format: "A4", orientation: "P" },
        })
        .from(element)
        .outputPdf() // add this to replace implicite .save() method, which triggers file download
        .then(function (pdfObj) {
          printRemito(2);

          $.ajax({
            beforeSend: function () { },
            url: phpFile,
            type: "POST",
            data: {
              consulta: "enviar_cotizacion_mail",
              file: btoa(pdfObj),
              id: currentCotizacion.id_cotizacion,
              email: email,
              link: resp,
            },
            success: function (x) {
              console.log(x);
              if (x.includes("success")) {
                swal(
                  "Enviaste la Cotización por Email correctamente!",
                  email,
                  "success"
                );
              } else {
                swal(
                  `Ocurrió un error al enviar el Email a ${email}`,
                  x,
                  "error"
                );
              }
              setTimeout(() => {
                if (btn) $(btn).prop("disabled", false);
              }, 5000);
              $("#modal-vistaprevia").modal("show");
            },
            error: function (jqXHR, estado, error) { },
          });
        });
    };
    switch (value) {
      case "catch":
        $.get(
          "flow/public/flow/generar/" + currentCotizacion.id_cotizacion,
          function (resp) {
            console.log(resp);
            if (resp.includes("http")) {
              enviar(resp);
            }
          }
        )
          .done(function () {
            $("#modal-link .loader-anular").removeClass("d-flex");
            $("#modal-link .link-container").removeClass("d-none");
          })
          .fail(function () {
            swal("Error al generar el Link de Pago", "", "error");
            $("#modal-link .loader-anular").removeClass("d-flex");
            $("#modal-link .link-container").removeClass("d-none");
          });
        break;

      case "sinlink":
        enviar(null);

      default:
        break;
    }
  });
}

function editarCotizacion() {
  $("#modal-vistaprevia").modal("hide");
  setEditing(true);
  const { comentario, id_cliente, productos, condicion_pago } =
    currentCotizacion.data;

  $("#select_cliente").val([id_cliente]).selectpicker("refresh");
  $("#input-comentario").val(comentario);
  $("#select-condicion").val([condicion_pago]).selectpicker("refresh");

  setChanged(false);
  cargarDatosCliente(id_cliente);
  let index = 0;
  productos.forEach(function (e, i) {
    const {
      tipo,
      id_tipo,
      id_variedad_real,
      variedad,
      cantidad,
      especie,
      id_especie,
      codigo,
      precio,
      total,
      subtotal,
      descuento,
    } = e;
    funcAddToPedido({
      index: index++,
      tipo: tipo,
      id_tipo: id_tipo,
      variedad: variedad,
      id_variedad: id_variedad_real,
      cantidad: cantidad,
      especie: especie,
      id_especie: id_especie,
      codigo: codigo,
      precio: precio.replace(/\./g, ""),
      total: total.toString().replace(/\./g, ""),
      subtotal: subtotal.toString().replace(/\./g, ""),
      descuento: descuento
        ? {
          tipo: descuento.tipo,
          valor: descuento.valor.toString().replace(/\./g, ""),
        }
        : null,
    });
  });
}

function setEditing(value) {
  ClearPedido();
  if (value) {
    $("#label-tab-cotizacion").html("EDITAR COTIZACIÓN");
    document.getElementById("tabnueva").click();
  } else {
    $("#label-tab-cotizacion").html("NUEVA COTIZACIÓN");
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
    url: phpFile,
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

function toggleContainerGD() {
  if ($(".row-guia").hasClass("d-none")) {
    getFoliosDisponibles(null, null, 52, null, null);
    getDatosTransporte();
    $(".row-guia").removeClass("d-none");
  } else {
    $(".row-guia").addClass("d-none");
    $(".row-select-folio").html("");
  }
}

function generarGuiaDespacho(folio, caf) {
  const rutTransporte = $("#input-rut-transporte")
    .val()
    .trim()
    .split(".")
    .join("");
  const rutChofer = $("#input-rut-chofer").val().trim().split(".").join("");
  const patente = $("#input-patente").val().trim();
  const nombreChofer = $("#input-nombre-chofer").val().trim();

  const total = $("#modal-vistaprevia").attr("x-total");
  const id_cotizacion = $("#modal-vistaprevia").attr("x-id");

  if (hasChanged) {
    swal("Primero debes guardar los cambios del Cliente!", "", "error");
  } else if (!folio) {
    swal("No hay Folio para la Guía de Despacho", "", "error");
  } else if (!checkRut(rutTransporte)) {
    swal("El RUT del encargado de Transporte no es válido", "", "error");
  } else if (!checkRut(rutChofer)) {
    swal("El RUT del Chofer no es válido", "", "error");
  } else if (!patente || !patente.length) {
    swal("Ingresa la patente del vehículo", "", "error");
  } else if (!nombreChofer || !nombreChofer.length) {
    swal("Ingresa el nombre del Chofer", "", "error");
  } else {
    setLoading(true);

    $.ajax({
      url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
      type: "POST",
      data: {
        consulta: "generar_guia_despacho_desde_cotizaciones",
        folio: folio,
        caf: caf,
        total: total,
        rutTransporte: rutTransporte,
        rutChofer: rutChofer,
        patente: patente,
        nombreChofer: nombreChofer,
        id_cotizacion: id_cotizacion,
      },
      success: function (x) {
        console.log(x);
        if (x.includes("path")) {
          try {
            const data = JSON.parse(x);
            window.open(
              `verpdf.php?tipo=GD&folio=${folio}&file=${data.path}`,
              "_blank"
            );
            setLoading(false);
            $("#modal-vistaprevia").modal("hide");
            swal(
              "Generaste la Guía de Despacho correctamente!",
              "Se guardó en la Pestaña GUIAS DE DESPACHO en Facturación",
              "success"
            );
            setChanged(false);
            loadHistorial();
          } catch (error) {
            setLoading(false);
            $("#modal-vistaprevia").modal("hide");
            swal(
              "La Guía de Despacho se generó correctamente, pero hubo un error al abrir el PDF. Intenta abrirlo desde el Historial de Guías",
              "",
              "warning"
            );
            setChanged(false);
            loadHistorial();
          }
        } else if (x.includes("ERROR_ENVIO_SII")) {
          setLoading(false);
          $("#modal-vistaprevia").modal("hide");
          swal(
            "La Guía de Despacho se guardó en el Sistema, pero hubo un error al enviarla al SII.",
            "Deberás buscar la Guía en el Historial e intentar reenviarla.",
            "error"
          );
          loadHistorial();
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

function getDatosTransporte() {
  $.ajax({
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "get_datos_transporte",
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
          console.log(error);
        }
      } else {
        console.log(x);
      }
    },
  });
}

//ORDENES ENVIO

function modalOrdenEnvio() {
  $("#input-direccion-entrega").val(currentCotizacion.data.domicilio);
  $("#input-direccion-entrega2").val(currentCotizacion.data.domicilio2);
  $("#select-tipo-envio").val("0").selectpicker("refresh");
  getTransportistasSelect();

  $("#select-transportista").val("default").selectpicker("refresh");
  $("#select-sucursal").html("").selectpicker("refresh");
  $(".col-select-transp,.col-select-sucursal").removeClass("d-none");
  $(".col-direccion-envio,.col-direccion-envio-2").addClass("d-none");

  $("#modal-orden-envio").modal("show");

  $("#table-bultos > tbody").html(`
    <tr scope="row" class="tr-add-row">
      <td colspan="6">
        <button onclick="addBulto()" class="btn btn-success btn-sm"><i class="fa fa-plus-square"></i></button>
      </td>
    </tr>
  `);

  addBulto();
}

function addBulto() {
  const index = $("#table-bultos > tbody > tr").length;

  if (index >= 25) return;

  let peso = "",
    alto = "",
    ancho = "",
    largo = "";
  if ($(".tr-bulto").last().length > 0) {
    const obj = $(".tr-bulto").last();
    peso = $(obj).find(".i-peso").val().trim();
    alto = $(obj).find(".i-alto").val().trim();
    ancho = $(obj).find(".i-ancho").val().trim();
    largo = $(obj).find(".i-largo").val().trim();
  }

  $("#table-bultos > tbody .tr-add-row").first().before(`
    <tr class='tr-bulto'>
      <td class='td-index'>
       ${index}
      </td>
      <td>
        <input value='${peso}' type='search' autocomplete="off" class="form-control input-decimal i-peso text-center" maxlength="6"/>
      </td>
      <td>
        <input value='${alto}' type='search' autocomplete="off" class="form-control input-decimal i-alto text-center" maxlength="6"/>
      </td>
      <td>
        <input value='${ancho}' type='search' autocomplete="off" class="form-control input-decimal i-ancho text-center" maxlength="6"/>
      </td>
      <td>
        <input value='${largo}' type='search' autocomplete="off" class="form-control input-decimal i-largo text-center" maxlength="6"/>
      </td>
      <td class="text-center">
        <button onclick="$(this).parent().parent().remove();updateIndexBulto()" class="btn btn-secondary fa fa-trash btn-sm"></button>
      </td>
    </tr>
  `);

  setInputDecimal($(".input-decimal"));
}

function updateIndexBulto() {
  $("#table-bultos > tbody > tr").each(function (i) {
    if (!$(this).hasClass("tr-bulto")) return;

    $(this)
      .find(".td-index")
      .html(i + 1);
  });
}

function getSucursalesSelect(id) {
  $.ajax({
    beforeSend: function () {
      $("#select-sucursal").selectpicker({ title: "Cargando..." });
      $("#select-sucursal").html("").selectpicker("refresh");
    },
    url: phpFile,
    type: "POST",
    data: {
      id_transportista: id,
      consulta: "get_sucursales_select",
    },
    success: function (x) {
      console.log(x)
      $("#select-sucursal").selectpicker({ title: "Selecciona" });
      $("#select-sucursal").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) { },
  });
}

function getTransportistasSelect() {
  $.ajax({
    beforeSend: function () {
      $("#select-transportista").html("").selectpicker("refresh");
    },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "get_transportistas_select",
    },
    success: function (x) {
      $("#select-transportista").html(x).selectpicker("refresh");
      $("#select-transportista").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          getSucursalesSelect(this.value)
        }
      );
    },
    error: function (jqXHR, estado, error) { },
  });
}


function guardarOrdenEnvio() {
  if (!$(".tr-bulto").length) {
    swal("Debes agregar un bulto", "", "error");
    return;
  }

  const tipo = $("#select-tipo-envio option:selected").val();
  const id_sucursal = $("#select-sucursal option:selected").val();
  const nombre_sucursal = $("#select-sucursal option:selected").attr(
    "x-nombre"
  );
  const nombre_transp = $("#select-transportista option:selected").attr(
    "x-nombre"
  );
  const id_transportista = $("#select-transportista option:selected").val();
  const direccion_sucursal = $("#select-sucursal option:selected").attr(
    "x-direccion"
  );

  if (!tipo || !tipo.length) {
    swal("Selecciona un Tipo de Entrega", "", "error");
    return;
  }

  if (tipo == 0 && (!id_sucursal || !id_sucursal.length)) {
    swal("Selecciona una Sucursal", "", "error");
    return;
  }

  if ((tipo == 1 || tipo == 2) && (!id_transportista || !id_transportista.length)) {
    swal("Selecciona un Transportista", "", "error");
    return;
  }

  const direccion = $("#input-direccion-entrega")
    .val()
    .trim()
    .replace(/[\s|.'"']/g, " ");
  if (tipo == 1 && (!direccion || !direccion.length)) {
    swal("Ingresa la Dirección de Entrega", "", "error");
    return;
  }

  const direccion2 = $("#input-direccion-entrega2")
    .val()
    .trim()
    .replace(/[\s|.'"']/g, " ");
  if (tipo == 2 && (!direccion2 || !direccion2.length)) {
    swal("Ingresa la Dirección de Entrega", "", "error");
    return;
  }

  const notas = $("#input-notas-entrega")
    .val()
    .trim()
    .replace(/[\s|.'"']/g, " ");

  let bultos = [];
  $("#table-bultos > tbody > tr").each(function (i) {
    if ($(this).hasClass("tr-add-row") || $(this).hasClass("tr-ignore")) return;

    const peso = $(this).find(".i-peso").val().trim();
    const alto = $(this).find(".i-alto").val().trim();
    const ancho = $(this).find(".i-ancho").val().trim();
    const largo = $(this).find(".i-largo").val().trim();

    bultos.push({
      index: i + 1,
      peso: peso && peso.length ? parseFloat(peso) : null,
      alto: alto && alto.length ? parseFloat(alto) : null,
      ancho: ancho && ancho.length ? parseFloat(ancho) : null,
      largo: largo && largo.length ? parseFloat(largo) : null,
    });
  });

  if (!bultos.length) {
    swal("Debes agregar un bulto", "", "error");
    return;
  }

  console.log(bultos);

  $("#modal-orden-envio").modal("hide");

  const dataOrden = {
    tipo,
    id_sucursal,
    direccion,
    direccion2,
    notas,
    bultos,
    nombre_sucursal,
    nombre_transp,
    direccion_sucursal,
  };

  printOrdenEnvio(dataOrden);
  return;


}

async function printOrdenEnvio(dataOrden) {
  let dataCotizacion = currentCotizacion;

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

  $(".print-orden-envio").html("");

  const now = new Date();
  const datetime =
    (now.getDate() < 10 ? "0" + now.getDate() : now.getDate()) +
    "/" +
    (now.getMonth() + 1 < 10
      ? "0" + (now.getMonth() + 1)
      : now.getMonth() + 1) +
    "/" +
    now.getFullYear();

  const {
    tipo,
    nombre_sucursal,
    nombre_transp,
    direccion_sucursal,
    notas,
    bultos,
  } = dataOrden;

  let direccionEntrega = "";
  let titulo = "ORDEN ENVÍO";

  if (tipo == 0) {
    titulo = `${nombre_sucursal} - ${nombre_transp}`;
    direccionEntrega = `Suc. ${nombre_transp} ${nombre_sucursal} - ${direccion_sucursal}`;
  } else if (tipo == 1) {
    titulo = "ORDEN ENVÍO";
    direccionEntrega = dataOrden.direccion;
  } else if (tipo == 2) {
    titulo = "ORDEN ENVÍO";
    direccionEntrega = dataOrden.direccion2;
  }

  bultos.forEach(function (b, i) {
    const { peso, alto, ancho, largo } = b;

    const tablarte = `
      <table class='table table-bordered tablin tabla-bulto' style='width: 100%;' role='grid'>
        <tbody>
          <tr>
            <td colspan="2">
              <div class="d-flex flex-row align-items-center">
                <img style="width: 160px; height: 100px" src="${logo}"></img>
                <div class="ml-4">
                  <h4 style="font-weight:bold;">${titulo}</h4>
                  ${direccion_sucursal ? `<span>${direccion_sucursal}</span>` : ""}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td>
              Fecha emisión: ${datetime}
            </td>
            <td class="text-center">
              <h5 class="font-weight-bold">${dataCotizacion.id_cotizacion
        .toString()
        .padStart(6, "0")}</h5>
            </td>
          </tr>
          <tr>
            <td>
              <div><strong>Remitente:</strong> ${razonEmpresa}</div>
              <div><strong>Dirección:</strong> ${direccion}</div>
              <div><strong>R.U.T:</strong> ${rutEmpresa}</div>
              <div><strong>Teléfono:</strong> ${telefono}</div>
              <div><strong>Email:</strong> ${email}</div>
            </td>
            <td class="text-center">
              <div class="p-2 qr-code-cotizacion" id="qr-code-${i}"></div>
            </td>
          </tr>
        </tbody>
      </table>`;

    let montoCotizacion = Math.floor(dataCotizacion.data.monto).toLocaleString('de-DE');

    const tabladest = `
      <table class='table table-bordered w-100' role='grid'>
        <tbody>
          <tr>
            <td>
              <div><strong>Destinatario:</strong> ${dataCotizacion.data.cliente}</div>
              <div><strong>Transportista:</strong> ${nombre_transp}</div>
              <div><strong>Dirección:</strong> ${direccionEntrega}</div>
              <div><strong>Comuna:</strong> ${dataCotizacion.data.comuna ?? '-'}</div>
              <div><strong>Región:</strong> ${dataCotizacion.data.region ?? '-'}</div>
              <div><strong>R.U.T:</strong> ${dataCotizacion.data.rut}</div>
              <div><strong>Teléfono:</strong> ${dataCotizacion.data.telcliente}</div>
              <div><strong>Email:</strong> ${dataCotizacion.data.email || '-'}</div>
              <div><strong>Cotización Nº:</strong> ${dataCotizacion.id_cotizacion} (\$${montoCotizacion})</div>
            </td>
            <td class="text-center">
              <h6>BULTO N°</h6>
              <h4 class="font-weight-bold">${(i + 1)
        .toString()
        .padStart(3, "0")}/${bultos.length.toString().padStart(3, "0")}</h4>
            </td>
          </tr>
          <tr>
            <td>
              <div><strong>Notas:</strong> ${notas ? notas.toUpperCase() : ""}</div>
            </td>
            <td class="text-center">
              <span>Peso: ${peso ? `${peso} kg` : ""}</span><br>
              <span>Alto: ${alto ? `${alto} cm` : ""}</span><br>
              <span>Ancho: ${ancho ? `${ancho} cm` : ""}</span><br>
              <span>Largo: ${largo ? `${largo} cm` : ""}</span><br>
            </td>
          </tr>
        </tbody>
      </table>`;

    const contenidoBulto = `
      <div class="bulto-print">
        ${tablarte}
        ${tabladest}
      </div>
    `;

    $(".print-orden-envio").append(contenidoBulto);

    // Generar QR más grande
    var qrcode = new QRCode(document.getElementById("qr-code-" + i), {
      text: dataCotizacion.data.uniqid,
      width: 150,  // más grande
      height: 150,
      colorDark: "#000000",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.H,
    });
  });

  // Crear CSS específico para orden de envío con ID único
  const css = `
    @media print {
      @page {
        size: 106mm 164mm;
        margin: 0;
      }
      body {
        margin: 0;
        padding: 0;
      }
      .print-orden-envio {
        width: 106mm;
        height: 164mm;
      }
      .print-orden-envio .tablin {
        width: 100vw !important;
        page-break-inside: avoid;
      }
      .bulto-print {
        width: 100vw;
        height: 164mm;
        page-break-after: always;
        box-sizing: border-box;
        padding: 5mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }
      .bulto-print:last-child {
        page-break-after: auto;
      }
      .bulto-print table {
        width: 100% !important;
        table-layout: fixed;
      }
      .bulto-print td {
        vertical-align: top;
        font-size: 12px;
      }
      .qr-code-cotizacion {
        text-align: center;
        width: 150px !important;
        height: 150px !important;
        padding-bottom: 10px !important;
        margin-bottom: 10px !important;
      }
      .qr-code-cotizacion img {
        width: 150px !important;
        height: 150px !important;
      }
    }`;
  
  // Remover estilos anteriores si existen
  const oldStyleTag = document.getElementById('orden-envio-print-styles');
  if (oldStyleTag) {
    document.head.removeChild(oldStyleTag);
  }
  
  let styleTag = document.createElement("style");
  styleTag.id = 'orden-envio-print-styles';
  styleTag.innerHTML = css;
  document.head.appendChild(styleTag);

  $("#ocultar").css({ display: "none" });
  $(".print-orden-envio").css({ display: "block" });
  $("#modal-vistaprevia").modal("hide");

  setTimeout(() => {
    storeOrdenEnvio($(".print-orden-envio").html());
    window.print();
    // Limpiar estilos después de imprimir
    const printStyleTag = document.getElementById('orden-envio-print-styles');
    if (printStyleTag) {
      document.head.removeChild(printStyleTag);
    }
    document.getElementById("ocultar").style.display = "block";
    $(".print-orden-envio").css({ display: "none" });
    $("#modal-vistaprevia").modal("show");
  }, 500);
}

function decodeBase64UTF8(base64String) {
  const binaryString = atob(base64String);
  const bytes = new Uint8Array(binaryString.length);
  for (let i = 0; i < binaryString.length; i++) {
    bytes[i] = binaryString.charCodeAt(i);
  }
  return new TextDecoder("utf-8").decode(bytes);
}
function printOrdenEnvio2(data) {
  // Limpiar el contenedor primero
  $(".print-orden-envio").html("");

  // Decodificar el HTML ya guardado en la BD
  let htmlContent = decodeBase64UTF8(data);
  $(".print-orden-envio").html(htmlContent);

  // Crear CSS específico para orden de envío con ID único (igual que printOrdenEnvio)
  const css = `
    @media print {
      @page {
        size: 106mm 164mm;
        margin: 0;
      }
      body {
        margin: 0;
        padding: 0;
      }
      .print-orden-envio {
        width: 106mm;
        height: 164mm;
      }
      .print-orden-envio .tablin {
        width: 100vw !important;
        page-break-inside: avoid;
      }
      .bulto-print {
        width: 100vw;
        height: 164mm;
        page-break-after: always;
        box-sizing: border-box;
        padding: 5mm;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
      }
      .bulto-print:last-child {
        page-break-after: auto;
      }
      .bulto-print table {
        width: 100% !important;
        table-layout: fixed;
      }
      .bulto-print td {
        vertical-align: top;
        font-size: 12px;
      }
      .qr-code-cotizacion {
        text-align: center;
        width: 150px !important;
        height: 150px !important;
        padding-bottom: 10px !important;
        margin-bottom: 10px !important;
      }
      .qr-code-cotizacion img {
        width: 150px !important;
        height: 150px !important;
      }
    }`;
  
  // Remover estilos anteriores si existen
  const oldStyleTag = document.getElementById('orden-envio-print-styles');
  if (oldStyleTag) {
    document.head.removeChild(oldStyleTag);
  }
  
  let styleTag = document.createElement("style");
  styleTag.id = 'orden-envio-print-styles';
  styleTag.innerHTML = css;
  document.head.appendChild(styleTag);

  $("#ocultar").css({ display: "none" });
  $(".print-orden-envio").css({ display: "block" });

  setTimeout(() => {
    window.print();
    // Limpiar estilos después de imprimir
    const printStyleTag = document.getElementById('orden-envio-print-styles');
    if (printStyleTag) {
      document.head.removeChild(printStyleTag);
    }
    document.getElementById("ocultar").style.display = "block";
    $(".print-orden-envio").css({ display: "none" });
  }, 500);
}

function printOrdenEnvioA4(data) {
  // Limpiar el contenedor primero
  $(".print-orden-envio").html("");

  // Decodificar el HTML ya guardado en la BD
  let htmlContent = decodeBase64UTF8(data);
  $(".print-orden-envio").html(htmlContent);

  // Crear CSS específico para orden de envío A4 con ID único  
  const css = `
    @media print {
      @page {
        size: A4;
        margin: 0mm;
      }
      body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
      }
      .print-orden-envio {
        width: 100%;
        max-width: none;
        height: auto;
      }
      .print-orden-envio .tablin,
      .print-orden-envio .tabla-bulto {
        width: 100% !important;
        page-break-inside: avoid;
        margin-bottom: 20px;
      }
      .bulto-print {
        width: 100%;
        height: auto;
        page-break-after: always !important;
        box-sizing: border-box;
        padding: 15mm;
        display: block;
      }
      .bulto-print:last-child {
        page-break-after: auto;
      }
      .bulto-print table {
        width: 100% !important;
        table-layout: auto;
        border-collapse: collapse;
        margin-bottom: 15px;
      }
      .bulto-print td {
        vertical-align: top;
        font-size: 14px;
        padding: 8px;
        border: 1px solid #ddd;
      }
      .bulto-print th {
        font-size: 14px;
        padding: 8px;
        border: 1px solid #ddd;
      }
      .qr-code-cotizacion {
        text-align: center;
        width: 200px !important;
        height: 200px !important;
        margin: 0 auto;
      }
      .qr-code-cotizacion img {
        width: 200px !important;
        height: 200px !important;
      }
      h4, h5, h6 {
        margin: 5px 0;
      }
      .d-flex {
        display: flex !important;
      }
      .flex-row {
        flex-direction: row !important;
      }
      .align-items-center {
        align-items: center !important;
      }
      .text-center {
        text-align: center !important;
      }
      .font-weight-bold {
        font-weight: bold !important;
      }
      .ml-4 {
        margin-left: 20px !important;
      }
    }`;
  
  // Remover estilos anteriores si existen
  const oldStyleTag = document.getElementById('orden-envio-print-styles-a4-cotizaciones');
  if (oldStyleTag) {
    document.head.removeChild(oldStyleTag);
  }
  
  let styleTag = document.createElement("style");
  styleTag.id = 'orden-envio-print-styles-a4-cotizaciones';
  styleTag.innerHTML = css;
  document.head.appendChild(styleTag);

  $("#ocultar").css({ display: "none" });
  $(".print-orden-envio").css({ display: "block" });

  setTimeout(() => {
    window.print();
    // Limpiar estilos después de imprimir
    const printStyleTag = document.getElementById('orden-envio-print-styles-a4-cotizaciones');
    if (printStyleTag) {
      document.head.removeChild(printStyleTag);
    }
    document.getElementById("ocultar").style.display = "block";
    $(".print-orden-envio").css({ display: "none" });
  }, 500);
}


function storeOrdenEnvio(html) {
  $.ajax({
    beforeSend: function () { },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "guardar_orden_envio",
      data: html,
      id_cotizacion: currentCotizacion.id_cotizacion,
      id_cliente: currentCotizacion.data.id_cliente,
    },
    success: function (x) {
      if (x.includes("success")) {
      }
    },
    error: function (jqXHR, estado, error) { },
  });
}
function initializeQRScanner() {
  qrVideo = document.createElement("video");
  qrCanvas = document.getElementById("qr-reader");
  qrCanvasContext = qrCanvas.getContext("2d");
  
  const loadingMessage = document.getElementById("loadingMessage");
  const outputMessage = document.getElementById("outputMessage");
  
  if (loadingMessage) {
    loadingMessage.innerText = "🎥 Iniciando cámara...";
    loadingMessage.hidden = false;
  }
  
  if (outputMessage) {
    outputMessage.innerText = "Apunta la cámara hacia el código QR";
  }

  function drawLine(begin, end, color) {
    qrCanvasContext.beginPath();
    qrCanvasContext.moveTo(begin.x, begin.y);
    qrCanvasContext.lineTo(end.x, end.y);
    qrCanvasContext.lineWidth = 4;
    qrCanvasContext.strokeStyle = color;
    qrCanvasContext.stroke();
  }

  // Use facingMode: environment to get the back camera on phones
  navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
    .then(function(stream) {
      qrVideo.srcObject = stream;
      qrVideo.setAttribute("playsinline", true);
      qrVideo.play();
      qrScanningActive = true;
      requestAnimationFrame(tick);
      
      if (loadingMessage) {
        loadingMessage.hidden = true;
      }
      qrCanvas.hidden = false;
    })
    .catch(function(err) {
      console.error("Error accessing camera:", err);
      if (loadingMessage) {
        loadingMessage.innerText = "❌ No se pudo acceder a la cámara";
      }
    });

  function tick() {
    if (!qrScanningActive || !qrVideo) {
      return;
    }
    
    if (qrVideo.readyState === qrVideo.HAVE_ENOUGH_DATA) {
      qrCanvas.height = qrVideo.videoHeight;
      qrCanvas.width = qrVideo.videoWidth;
      qrCanvasContext.drawImage(qrVideo, 0, 0, qrCanvas.width, qrCanvas.height);
      
      var imageData = qrCanvasContext.getImageData(0, 0, qrCanvas.width, qrCanvas.height);
      var code = jsQR(imageData.data, imageData.width, imageData.height, {
        inversionAttempts: "dontInvert",
      });
      
      if (code) {
        drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
        drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
        drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
        drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
        
        console.log(`Scan result: ${code.data}`);
        getCotizacionQR(code.data);
        return; // Stop scanning after successful read
      }
    }
    requestAnimationFrame(tick);
  }
}

function stopQRScanner() {
  qrScanningActive = false;
  if (qrVideo && qrVideo.srcObject) {
    const tracks = qrVideo.srcObject.getTracks();
    tracks.forEach(track => track.stop());
    qrVideo.srcObject = null;
  }
  qrVideo = null;
  qrCanvas = null;
  qrCanvasContext = null;
}

function modalQR() {
  $("#modal-qr").modal("show");
}

function setLoadingQR(val) {
  if (val) {
    $("#modal-qr .loader-anular").css({ display: "flex" });
    $("#modal-qr .wrapper").css({ display: "none" });
  } else {
    $("#modal-qr .loader-anular").css({ display: "none" });
    $("#modal-qr .wrapper").css({ display: "block" });
  }
}

function getCotizacionQR(uniqid) {
  $.ajax({
    beforeSend: function () { },
    url: phpFile,
    type: "POST",
    data: {
      consulta: "get_cotizacion_qr",
      uniqid: uniqid,
    },
    success: function (x) {
      if (x.length && x.includes("id:")) {
        printDataCotizacion(x.replace("id:", ""));
        $("#modal-qr").modal("hide");
      } else {
        swal("Ocurrió un error al obtener la Cotización", x, "error");
      }
      setLoadingQR(false);
    },
    error: function (jqXHR, estado, error) { },
  });
}

function modalLinkPago() {
  $("#input-link-pago").val("");

  const email = currentCotizacion.data.email;
  if (!email || !email.length) {
    swal("El Cliente no tiene un Email configurado", email, "error");
    return;
  }

  if (!validEmail(email)) {
    swal("El Email del cliente no es válido", email, "error");
    return;
  }

  $("#modal-link .loader-anular").addClass("d-flex");
  $("#modal-link .link-container").addClass("d-none");

  $("#modal-link").modal("show");

  $.get(
    "flow/public/flow/generar/" + currentCotizacion.id_cotizacion,
    function (resp) {
      console.log(resp);
      if (resp.includes("http")) {
        $("#input-link-pago").val(resp);
      }
    }
  ).then(function () {
    $("#modal-link .loader-anular").removeClass("d-flex");
    $("#modal-link .link-container").removeClass("d-none");
  });
}

function copyToClipboard() {
  // Get the text field
  var copyText = document.getElementById("input-link-pago");

  // Select the text field
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices

  // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.value);
}
