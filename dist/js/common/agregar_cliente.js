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
  const nombre = $("#ModalAgregarCliente #nombrecliente_txt").val().trim();
  const domicilio = $("#ModalAgregarCliente #domiciliocliente_txt").val().trim();
  const domicilio2 = $("#ModalAgregarCliente #domiciliocliente2_txt").val().trim();
  const telefono = $("#ModalAgregarCliente #telcliente_txt").val().trim();
  const rut = $("#ModalAgregarCliente #rutcliente_txt").val().trim();
  const razonSocial = $("#ModalAgregarCliente #razonsocial_txt").val().trim();
  const mail = $("#ModalAgregarCliente #mailcliente_txt").val().trim();
  const comuna = $("#ModalAgregarCliente #select-comuna2 option:selected").val();
  const provincia = $("#ModalAgregarCliente #provinciacliente_txt").val();
  const region = $("#ModalAgregarCliente #regioncliente_txt").val();

  // Solo enviar id_vendedor al agregar nuevo cliente, no al editar
  const id_vendedor = !edit_mode ? $("#ModalAgregarCliente #select-vendedor option:selected").val() : null;

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
  const id = id_cliente.replace("cliente_", "");
  global_id_cliente = id;
  edit_mode = true;

  $.ajax({
    url: 'get_cliente_data.php',
    type: 'POST',
    dataType: 'json',
    data: { id: id },
    beforeSend: function() {
      // Opcional: Mostrar un loader
      swal({
        title: "Cargando cliente...",
        text: "Por favor, espere.",
        icon: "info",
        buttons: false,
        closeOnClickOutside: false,
        closeOnEsc: false,
      });
    },
    success: function(response) {
      swal.close(); // Ocultar el loader
      if(response.status === 'success') {
        const cliente = response.data;
        
        // Limpiar y preparar modal
        $("#ModalAgregarCliente").find("#titulo").html("Modificar Cliente");
        $("#select-comuna2").val("default").selectpicker("refresh");

        // Llenar los campos con los datos obtenidos
        $("#nombrecliente_txt").val(cliente.nombre);
        $("#razonsocial_txt").val(cliente.razon_social);
        $("#domiciliocliente_txt").val(cliente.domicilio);
        $("#domiciliocliente2_txt").val(cliente.domicilio2);
        $("#telcliente_txt").val(cliente.telefono);
        $("#mailcliente_txt").val(cliente.mail);
        $("#rutcliente_txt").val(cliente.rut);
        $("#select-comuna2").val(cliente.comuna).selectpicker("refresh");
        $("#provinciacliente_txt").val(cliente.provincia);
        $("#regioncliente_txt").val(cliente.region);

        // Lógica para el cambio de vendedor
        $("#grupo-vendedor-agregar").hide();
        $("#grupo-vendedor-editar").show();
        $("#historial-vendedor-inline").hide();

        // No se puede obtener el nombre del vendedor de esta consulta.
        // Se puede hacer otra consulta AJAX o modificar get_cliente_data.php
        // Por ahora, se deja un placeholder.
        $("#vendedor-actual-nombre").text("Cargando..."); 
        $("#select-nuevo-vendedor-edit").val("default").selectpicker("refresh");
        $("#justificacion-cambio-edit").val("");

        // Cargar nombre del vendedor actual dinámicamente
        $.ajax({
            url: 'data_ver_clientes.php',
            type: 'POST',
            data: { consulta: 'obtener_vendedor_cliente', id_cliente: id },
            success: function(res) {
                const data = JSON.parse(res);
                $("#vendedor-actual-nombre").text(data.vendedor || "Sin vendedor asignado");
            }
        });


        const tiene_vendedor = cliente.id_vendedor && cliente.id_vendedor != '' && cliente.id_vendedor != 'null';
        if (tiene_vendedor) {
          $("#asterisco-requerido-edit").show();
          $("#texto-opcional-edit").hide();
        } else {
          $("#asterisco-requerido-edit").hide();
          $("#texto-opcional-edit").show();
        }
        
        window.id_vendedor_actual_global = cliente.id_vendedor; // Para usar en aplicarCambioVendedor

        // Mostrar el modal
        $("#ModalAgregarCliente").modal("show");
        document.getElementById("nombrecliente_txt").focus();

      } else {
        swal("Error", response.message, "error");
      }
    },
    error: function(jqXHR, estado, error) {
      swal.close(); // Ocultar el loader
      swal("Error", "No se pudo cargar la información del cliente: " + error, "error");
    }
  });
}

function setRazonSocial() {
  const nombre = $("#ModalAgregarCliente #nombrecliente_txt").val().trim();

  if (nombre && nombre.length) {
    $("#ModalAgregarCliente #razonsocial_txt").val(nombre);
  }
}

// Aplicar cambio de vendedor desde el modal de editar cliente
function aplicarCambioVendedor() {
  const id_cliente = global_id_cliente;
  const id_vendedor_nuevo = $("#ModalAgregarCliente #select-nuevo-vendedor-edit").val();
  const id_vendedor_anterior = window.id_vendedor_actual_global;
  const justificacion = $("#ModalAgregarCliente #justificacion-cambio-edit").val().trim();

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
