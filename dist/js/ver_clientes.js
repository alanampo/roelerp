let tablaClientes = null;

$(document).ready(function(){
  // Sincronización automática en segundo plano (si pasaron +24h)
  sincronizarVendedoresAuto();

  busca_clientes()

  // Event listener para el checkbox de filtro
  $("#filtro-sin-vendedor").on("change", function() {
    aplicarFiltroSinVendedor();
  });
})

function busca_clientes() {
  const filtroSinVendedor = $("#filtro-sin-vendedor").is(":checked");

  $.ajax({
    beforeSend: function () {
      // Destruir tabla existente si existe
      if (tablaClientes) {
        tablaClientes.destroy();
        tablaClientes = null;
      }
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
        autoWidth: true,
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
