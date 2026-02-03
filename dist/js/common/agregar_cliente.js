let edit_mode = false;
let global_id_cliente = null;
$(document).ready(function () {
  pone_comunas();
  pone_usuarios();
  global_id_cliente = null;
  edit_mode = false;

  $("#rutcliente_txt")
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

function MostrarModalAgregarCliente() {
  edit_mode = false;
  $("#ModalAgregarCliente").find("input").val("");
  $("#ModalAgregarCliente").find("textarea").val("");
  $("#ModalAgregarCliente").find("#titulo").html("Agregar Cliente");
  $("#select-comuna2").val("default").selectpicker("refresh");
  $("#select-vendedor").val("default").selectpicker("refresh");

  // Mostrar columna de agregar, ocultar columna de editar
  $("#grupo-vendedor-agregar").show();
  $("#grupo-vendedor-editar").hide();
  $("#historial-vendedor-inline").hide();

  $("#ModalAgregarCliente").modal("show");
  document.getElementById("nombrecliente_txt").focus();
}

function GuardarCliente() {
  const nombre = $("#nombrecliente_txt").val().trim();
  const domicilio = $("#domiciliocliente_txt").val().trim();
  const domicilio2 = $("#domiciliocliente2_txt").val().trim();
  const telefono = $("#telcliente_txt").val().trim();
  const rut = $("#rutcliente_txt").val().trim();
  const razonSocial = $("#razonsocial_txt").val().trim();
  const mail = $("#mailcliente_txt").val().trim();
  const comuna = $("#select-comuna2 option:selected").val();
  const provincia = $("#provinciacliente_txt").val();
  const region = $("#regioncliente_txt").val();

  // Solo enviar id_vendedor al agregar nuevo cliente, no al editar
  const id_vendedor = !edit_mode ? $("#select-vendedor option:selected").val() : null;

  if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
  } else if (domicilio.length < 3) {
    swal("Debes ingresar un Domicilio!", "", "error");
  } else if (!comuna || !comuna.length) {
    swal("Selecciona la Comuna!", "", "error");
  } else if (telefono.length == 0) {
    swal("Debes ingresar un teléfono!", "", "error");
  } else if (mail.includes(" ") == true) {
    swal("El E-Mail no puede contener espacios", "", "error");
  } else {
    $("#ModalAgregarCliente").modal("hide");
    $.ajax({
      url: "guarda_cliente.php",
      type: "POST",
      data: {
        tipo: !edit_mode ? "agregar" : "editar",
        nombre: nombre,
        domicilio: domicilio,
        domicilio2,
        telefono: telefono,
        rut: rut,
        razonSocial: razonSocial,
        mail: mail,
        comuna: comuna,
        provincia: provincia,
        region: region,
        id_vendedor: id_vendedor,
        id_cliente: edit_mode ? global_id_cliente : null,
      },
      success: function (x) {
        if (x.trim() == "success") {
          // Detectar en qué página estamos y refrescar el listado correspondiente
          const currentPage = window.location.href;

          if (currentPage.includes('ver_clientes.php')) {
            // Estamos en ver_clientes.php - refrescar tabla
            if (typeof busca_clientes === 'function') {
              busca_clientes();
            }
          } else if (currentPage.includes('ver_cotizaciones.php') || currentPage.includes('factura_directa.php') || currentPage.includes('boleta_directa.php')) {
            // Estamos en ver_cotizaciones.php, factura_directa.php o boleta_directa.php - refrescar selectpicker
            if (typeof pone_clientes === 'function') {
              pone_clientes();
            }
          }

          swal("El cliente fue guardado correctamente!", "", "success");
        } else {
          swal("Ocurrió un error al guardar el cliente", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
        $("#ModalAgregarCliente").modal("show");
      },
    });
  }
}

function pone_comunas() {
  $("#select_cliente").prop("disabled", true);
  $.ajax({
    beforeSend: function () {
      $("#select-comuna,#select-comuna2").html("Cargando lista de comunas...");
    },
    url: "data_ver_clientes.php",
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

function pone_usuarios() {
  $.ajax({
    beforeSend: function () {
      $("#select-vendedor, #select-nuevo-vendedor-edit").html("Cargando lista de usuarios...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: { consulta: "pone_usuarios" },
    success: function (x) {
      $("#select-vendedor, #select-nuevo-vendedor-edit").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {
      $("#select-vendedor, #select-nuevo-vendedor-edit").html("Error al cargar usuarios").selectpicker("refresh");
    },
  });
}

function MostrarModalModificarCliente(id_cliente) {
  let indice = $("#" + id_cliente)
    .closest("tr")
    .index();
  let id = id_cliente.replace("cliente_", "");
  let nombre = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(1)")
    .text();
  let razon = $("#" + id_cliente)
    .closest("tr").attr("x-razon");
  let domicilio = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(2)")
    .text();
    let domicilio2 = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(3)")
    .text();
  let telefono = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(4)")
    .text();
  let mail = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(5)")
    .text();
  let rut = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(6)")
    .text();
  let comuna = $("#" + id_cliente)
    .closest("tr").attr("x-id-comuna")
  let id_vendedor = $("#" + id_cliente)
    .closest("tr").attr("x-id-vendedor")
  let vendedor_nombre = $("#tabla")
    .find("tr:eq(" + (parseInt(indice) + 1).toString() + ") td:eq(11)")
    .text();

  const tr = $("#tabla")
  .find("tr:eq(" + (parseInt(indice) + 1).toString() + ")");
  const provincia = $(tr).find(".td-provincia").text();
  const region = $(tr).find(".td-region").text();
  $("#select-comuna2").val("default").selectpicker("refresh");
  $("#ModalAgregarCliente").find("#titulo").html("Modificar Cliente");
  $("#nombrecliente_txt").val(nombre);
  $("#razonsocial_txt").val(razon);
  $("#domiciliocliente_txt").val(domicilio);
  $("#domiciliocliente2_txt").val(domicilio2);
  $("#telcliente_txt").val(telefono);
  $("#mailcliente_txt").val(mail);
  $("#rutcliente_txt").val(rut);
  $("#select-comuna2").val(comuna).selectpicker("refresh")
  $("#provinciacliente_txt").val(provincia);
  $("#regioncliente_txt").val(region);

  // Mostrar columna de editar vendedor, ocultar columna de agregar
  $("#grupo-vendedor-agregar").hide();
  $("#grupo-vendedor-editar").show();
  $("#historial-vendedor-inline").hide();

  // Guardar datos del vendedor actual
  $("#vendedor-actual-nombre").text(vendedor_nombre || "Sin vendedor asignado");
  $("#select-nuevo-vendedor-edit").val("default").selectpicker("refresh");
  $("#justificacion-cambio-edit").val("");

  // Mostrar/ocultar asterisco según si tiene vendedor
  const tiene_vendedor = id_vendedor && id_vendedor != '' && id_vendedor != 'null';
  if (tiene_vendedor) {
    $("#asterisco-requerido-edit").show();
    $("#texto-opcional-edit").hide();
  } else {
    $("#asterisco-requerido-edit").hide();
    $("#texto-opcional-edit").show();
  }

  global_id_cliente = id;
  window.id_vendedor_actual_global = id_vendedor; // Para usar en aplicarCambioVendedor
  edit_mode = true;
  $("#ModalAgregarCliente").modal("show");
  document.getElementById("nombrecliente_txt").focus();
}

function setRazonSocial() {
  const nombre = $("#nombrecliente_txt").val().trim();

  if (nombre && nombre.length) {
    $("#razonsocial_txt").val(nombre);
  }
}

// Aplicar cambio de vendedor desde el modal de editar cliente
function aplicarCambioVendedor() {
  const id_cliente = global_id_cliente;
  const id_vendedor_nuevo = $("#select-nuevo-vendedor-edit").val();
  const id_vendedor_anterior = window.id_vendedor_actual_global;
  const justificacion = $("#justificacion-cambio-edit").val().trim();

  // Validaciones
  if (!id_vendedor_nuevo || id_vendedor_nuevo == 'default') {
    swal("Debes seleccionar un nuevo vendedor", "", "error");
    return;
  }

  if (id_vendedor_nuevo == id_vendedor_anterior) {
    swal("El nuevo vendedor debe ser diferente al actual", "", "error");
    return;
  }

  // Solo validar justificación si había vendedor anterior
  const tiene_vendedor_anterior = id_vendedor_anterior && id_vendedor_anterior != '' && id_vendedor_anterior != 'null';

  if (tiene_vendedor_anterior && justificacion.length < 3) {
    swal("Debes proporcionar una justificación de al menos 3 caracteres", "", "error");
    return;
  }

  $.ajax({
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "cambiar_vendedor",
      id_cliente: id_cliente,
      id_vendedor_nuevo: id_vendedor_nuevo,
      id_vendedor_anterior: id_vendedor_anterior,
      justificacion: justificacion,
    },
    success: function (x) {
      if (x.trim() == "success") {
        swal("El vendedor fue cambiado correctamente!", "", "success");
        $("#ModalAgregarCliente").modal("hide");
        busca_clientes();
      } else {
        swal("Ocurrió un error al cambiar el vendedor", x, "error");
      }
    },
    error: function (jqXHR, estado, error) {
      swal("Ocurrió un error", error.toString(), "error");
    },
  });
}

// Ver historial de cambios de vendedor en el modal
function verHistorialVendedorEnModal() {
  const id_cliente = global_id_cliente;
  const $historial = $("#historial-vendedor-inline");
  const $contenido = $("#contenido-historial-inline");

  // Toggle display
  if ($historial.is(":visible")) {
    $historial.hide();
    return;
  }

  $historial.show();
  $contenido.html('<p class="text-center">Cargando historial...</p>');

  $.ajax({
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "obtener_historial_vendedor",
      id_cliente: id_cliente,
    },
    success: function (response) {
      try {
        const historial = JSON.parse(response);

        if (historial.length === 0) {
          $contenido.html(
            '<div class="alert alert-info">No hay cambios de vendedor registrados para este cliente.</div>'
          );
          return;
        }

        let html = '<div class="table-responsive"><table class="table table-bordered table-striped table-sm">';
        html += '<thead><tr><th>Fecha</th><th>De</th><th>A</th><th>Por</th><th>Justificación</th></tr></thead><tbody>';

        historial.forEach(function (item) {
          html += "<tr>";
          html += "<td style='font-size: 11px;'>" + item.fecha + "</td>";
          html += "<td style='font-size: 11px;'>" + item.vendedor_anterior + "</td>";
          html += "<td style='font-size: 11px;'>" + item.vendedor_nuevo + "</td>";
          html += "<td style='font-size: 11px;'>" + item.usuario_cambio + "</td>";
          html += "<td style='font-size: 11px;'>" + (item.justificacion || "-") + "</td>";
          html += "</tr>";
        });

        html += "</tbody></table></div>";
        $contenido.html(html);
      } catch (e) {
        $contenido.html(
          '<div class="alert alert-danger">Error al cargar el historial: ' + response + "</div>"
        );
      }
    },
    error: function (jqXHR, estado, error) {
      $contenido.html(
        '<div class="alert alert-danger">Error al cargar el historial: ' + error + "</div>"
      );
    },
  });
}
