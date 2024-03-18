$(document).ready(function(){
  busca_clientes()
  pone_comunas();
})

function busca_clientes() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando clientes, espere...");
    },
    url: "busca_clientes.php",
    type: "POST",
    data: null,
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[1, "asc"]],
        pageLength: 50,
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

function pone_comunas() {
  $.ajax({
    beforeSend: function () {
      $("#select-comuna2").html("Cargando lista de comunas...");
    },
    url: "data_ver_cotizaciones.php",
    type: "POST",
    data: {consulta: "pone_comunas"},
    success: function (x) {
      $("#select-comuna2").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {
      
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
