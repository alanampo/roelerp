const dataPHP = "data_ver_viveros.php";

$(document).ready(function(){
  getTableViveros()
})

function getTableViveros() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando espere...");
    },
    url: dataPHP,
    type: "POST",
    data: {
      consulta: "get_table_viveros"
    },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[1, "asc"]],
        pageLength: 50,
        language: {
          lengthMenu: "Mostrando _MENU_ viveros por página",
          zeroRecords: "No hay viveros",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay viveros",
          infoFiltered: "(filtrado de _MAX_ viveros en total)",
          lengthMenu: "Mostrar _MENU_ viveros",
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

function eliminarVivero(id_vivero, nombre) {
  swal(
    `Estás seguro/a de eliminar el Vivero ${nombre}?`,
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
          url: dataPHP,
          type: "POST",
          data: { consulta: "eliminar_vivero", id_vivero: id_vivero },
          success: function (x) {
            if (!x.includes("success")) {
              swal("Ocurrió un error!", x, "error");
            } else {
              swal("Eliminaste el Vivero correctamente!", "", "success");
              getTableViveros();
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
