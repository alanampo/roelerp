let tablaClientes = null;

$(document).ready(function(){
  // Sincronización automática en segundo plano (si pasaron +24h)
  sincronizarVendedoresAuto();

  busca_clientes()
  cargar_vendedores_para_cambio()

  // Event listener para el checkbox de filtro
  $("#filtro-sin-vendedor").on("change", function() {
    aplicarFiltroSinVendedor();
  });
})

function cargar_vendedores_para_cambio() {
  $.ajax({
    url: "data_ver_clientes.php",
    type: "POST",
    data: { consulta: "pone_usuarios" },
    success: function (x) {
      $("#select-nuevo-vendedor").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {
      console.error("Error al cargar vendedores:", error);
    },
  });
}

function busca_clientes() {
  const filtroSinVendedor = $("#filtro-sin-vendedor").is(":checked");

  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando clientes, espere...");
    },
    url: "busca_clientes.php",
    type: "POST",
    data: { sin_vendedor: filtroSinVendedor ? "true" : "false" },
    success: function (x) {
      $("#tabla_entradas").html(x);

      tablaClientes = $("#tabla").DataTable({
        order: [[1, "asc"]],
        pageLength: 50,
        scrollX: true,
        autoWidth: false,
        language: {
          lengthMenu: "Mostrando _MENU_ clientes por página",
          zeroRecords: "No hay clientes",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay clientes",
          infoFiltered: "(filtrado de _MAX_ clientes en total)",
          lengthMenu: "Mostrar _MENU_ clientes",
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
            sortAscending: ": tocá para ordenar en modo ascendente",
            sortDescending: ": tocá para ordenar en modo descendente",
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

function eliminarCliente(id_cliente, nombre) {
  swal(
    `Estás seguro/a de eliminar el cliente ${nombre}?`,
    "",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "SÍ, ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          beforeSend: function () {},
          url: "data_ver_clientes.php",
          type: "POST",
          data: { consulta: "eliminar_cliente", id_cliente: id_cliente },
          success: function (x) {
            if (!x.includes("success")) {
              swal("Ocurrió un error!", x, "error");
            } else {
              swal("Eliminaste el cliente correctamente!", "", "success");
              busca_clientes();
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

function mostrarModalCambiarVendedor(id_cliente, nombre_cliente, id_vendedor_actual) {
  // Cargar datos del cliente
  $("#cambio-id-cliente").val(id_cliente);
  $("#cambio-nombre-cliente").val(nombre_cliente);
  $("#cambio-id-vendedor-actual").val(id_vendedor_actual);

  // Obtener nombre del vendedor actual
  const tiene_vendedor = id_vendedor_actual && id_vendedor_actual != '' && id_vendedor_actual != 'null';

  if (tiene_vendedor) {
    const vendedor_actual_text = $("#select-nuevo-vendedor option[value='" + id_vendedor_actual + "']").text();
    $("#cambio-vendedor-actual").val(vendedor_actual_text || "Sin asignar");
    $("#asterisco-requerido").show();
    $("#texto-opcional").hide();
  } else {
    $("#cambio-vendedor-actual").val("Sin asignar");
    $("#asterisco-requerido").hide();
    $("#texto-opcional").show();
  }

  // Reset campos
  $("#select-nuevo-vendedor").val("default").selectpicker("refresh");
  $("#justificacion-cambio").val("");

  $("#modal-cambiar-vendedor").modal("show");
}

function guardarCambioVendedor() {
  const id_cliente = $("#cambio-id-cliente").val();
  const id_vendedor_nuevo = $("#select-nuevo-vendedor").val();
  const id_vendedor_anterior = $("#cambio-id-vendedor-actual").val();
  const justificacion = $("#justificacion-cambio").val().trim();

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

  $("#modal-cambiar-vendedor").modal("hide");

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
        busca_clientes();
      } else {
        swal("Ocurrió un error al cambiar el vendedor", x, "error");
      }
    },
    error: function (jqXHR, estado, error) {
      swal("Ocurrió un error", error.toString(), "error");
      $("#modal-cambiar-vendedor").modal("show");
    },
  });
}

function verHistorialVendedor(id_cliente, nombre_cliente) {
  $("#historial-nombre-cliente").val(nombre_cliente);
  $("#contenido-historial").html('<p class="text-center">Cargando historial...</p>');

  $("#modal-historial-vendedor").modal("show");

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
          $("#contenido-historial").html(
            '<div class="alert alert-info">No hay cambios de vendedor registrados para este cliente.</div>'
          );
          return;
        }

        let html = '<div class="table-responsive"><table class="table table-bordered table-striped">';
        html += '<thead><tr><th>Fecha</th><th>De</th><th>A</th><th>Por</th><th>Justificación</th></tr></thead><tbody>';

        historial.forEach(function (item) {
          html += "<tr>";
          html += "<td>" + item.fecha + "</td>";
          html += "<td>" + item.vendedor_anterior + "</td>";
          html += "<td>" + item.vendedor_nuevo + "</td>";
          html += "<td>" + item.usuario_cambio + "</td>";
          html += "<td>" + (item.justificacion || "-") + "</td>";
          html += "</tr>";
        });

        html += "</tbody></table></div>";
        $("#contenido-historial").html(html);
      } catch (e) {
        $("#contenido-historial").html(
          '<div class="alert alert-danger">Error al cargar el historial: ' + response + "</div>"
        );
      }
    },
    error: function (jqXHR, estado, error) {
      $("#contenido-historial").html(
        '<div class="alert alert-danger">Error al cargar el historial: ' + error + "</div>"
      );
    },
  });
}

// Sincronización automática (solo si pasaron +24h)
function sincronizarVendedoresAuto() {
  $.ajax({
    url: "sincronizar_vendedores.php",
    type: "POST",
    data: { auto_sync: "true" },
    success: function (response) {
      try {
        const result = JSON.parse(response);
        if (result.status === 'success') {
          console.log('Sincronización automática completada:', result.timestamp);
        } else if (result.status === 'skip') {
          console.log('Sincronización omitida:', result.mensaje);
        }
      } catch (e) {
        console.error('Error en sincronización automática:', e);
      }
    },
    error: function (jqXHR, estado, error) {
      console.error('Error en sincronización automática:', error);
    },
  });
}

// Sincronización manual (siempre se ejecuta)
function sincronizarVendedoresManual() {
  const $btn = $("#btn-sync-vendedores");
  const textoOriginal = $btn.html();

  // Deshabilitar botón y mostrar estado
  $btn.prop("disabled", true);
  $btn.html('<i class="fa fa-spin fa-spinner"></i> <span style="font-family: Arial">SINCRONIZANDO...</span>');

  $.ajax({
    url: "sincronizar_vendedores.php",
    type: "POST",
    data: { auto_sync: "false" },
    success: function (response) {
      try {
        const result = JSON.parse(response);

        if (result.status === 'success') {
          swal("Sincronización completada", "Fechas actualizadas y vendedores verificados correctamente", "success");
          // Recargar tabla de clientes
          busca_clientes();
        } else if (result.status === 'error') {
          swal("Error en sincronización", result.mensaje, "error");
        } else {
          swal("Información", result.mensaje, "info");
        }
      } catch (e) {
        swal("Error", "Error al procesar respuesta: " + response, "error");
      }

      // Restaurar botón
      $btn.prop("disabled", false);
      $btn.html(textoOriginal);
    },
    error: function (jqXHR, estado, error) {
      swal("Error de conexión", "No se pudo completar la sincronización: " + error, "error");

      // Restaurar botón
      $btn.prop("disabled", false);
      $btn.html(textoOriginal);
    },
  });
}

// Filtrar clientes sin vendedor
function aplicarFiltroSinVendedor() {
  busca_clientes();
}
