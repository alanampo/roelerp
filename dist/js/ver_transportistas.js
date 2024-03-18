const phpFile = "data_ver_transportistas.php";
let editingSucursal = null;

$(document).ready(function () {
  getTableSucursales();
});

function getTableSucursales() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando sucursales, espere...");
    },
    url: phpFile,
    type: "POST",
    data: { consulta: "get_table_sucursales" },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[0, "desc"]],
        pageLength: 50,
        language: {
          lengthMenu: "Mostrando _MENU_ sucursales por página",
          zeroRecords: "No hay sucursales",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay sucursales",
          infoFiltered: "(filtrado de _MAX_ sucursales en total)",
          lengthMenu: "Mostrar _MENU_ sucursales",
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

function modalSucursal(edit) {
  $("#modal-sucursal").modal("show");
  $("#modal-sucursal input").val("");
  $("#modal-sucursal select").val("default").selectpicker("refresh");
  

  if (edit){
    const {id, id_transportista, nombre, telefono, notas, direccion} = edit;
    getTransportistasSelect(id_transportista);
    $("#input-nombre-sucursal").val(nombre)
    $("#input-direccion").val(direccion)
    $("#input-telefono").val(telefono)
    $("#input-notas").val(notas)
    editingSucursal = id;
  }
  else{
    editingSucursal = null;
    getTransportistasSelect();
  }
}

function modalTransportistas() {
  getTableTransportistas();
  $("#table-transp > tbody").html("")
  $("#modal-transportistas").modal("show");
  $("#input-nombre-transp").val("");
  $("#input-nombre-transp").focus();
}

function getTransportistasSelect(id_transportista) {
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
      if (id_transportista){
        $("#select-transportista").val(id_transportista).selectpicker("refresh")
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getTableTransportistas() {
  $("#table-transp > tbody").html("");
  $.ajax({
    url: phpFile,
    type: "POST",
    data: {
      consulta: "get_table_transportistas",
    },
    success: function (x) {
      $("#table-transp > tbody").html(x);
    },
  });
}

function guardarTransportista(btn){
  const nombre = $("#input-nombre-transp").val().trim().replace(/\s/g, " ")
  .replace("|", " ")
  .replace(",", ".");

  if (!nombre || !nombre.length){
    swal("Ingresa el Nombre", "", "error")
    return
  }

  $(btn).prop("disabled", true);
  $.ajax({
    url: phpFile,
    type: "POST",
    data: {
      consulta: "guardar_transportista",
      nombre: nombre.toUpperCase()
    },
    success: function (x) {
      if (x.includes("success")){
        getTableTransportistas();
        getTransportistasSelect()
        $("#input-nombre-transp").val("")
        $("#input-nombre-transp").focus();
      }
      else{
        swal("Ocurrió un error", x, "error")
      }
      $(btn).prop("disabled", false);
    },
    error: function(){
      $(btn).prop("disabled", false);
    }
  });
}

function eliminarTransportista(id, nombre) {
  swal(`¿ELIMINAR Transportista ${nombre}?`, "", {
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
        $.ajax({
          url: phpFile,
          type: "POST",
          data: {
            consulta: "eliminar_transportista",
            id: id
          },
          success: function (x) {
            if (x.includes("success")){
              getTableTransportistas();
              getTransportistasSelect()
              $("#input-nombre-transp").val("")
            }
            else{
              swal("Ocurrió un error", x, "error")
            }
          },
          error: function(){
          }
        });

      default:
        break;
    }
  });
}

function guardarSucursal(){
  const id_transportista = $("#select-transportista option:selected").val();
  const nombre = $("#input-nombre-sucursal").val().trim().replace(/[\s|.'"']/g, " ");
  const direccion = $("#input-direccion").val().trim().replace(/[\s|.'"']/g, " ");
  const telefono = $("#input-telefono").val().trim().replace(/[\s|.'"']/g, " ");
  const notas = $("#input-notas").val().trim().replace(/[\s|.'"']/g, " ");

  if (!id_transportista || !id_transportista.length){
    swal("Selecciona el Transportista", "", "error")
    return;
  }

  if (!nombre || !nombre.length){
    swal("Ingresa el Nombre de la Sucursal", "", "error")
    return;
  }

  if (!direccion || !direccion.length){
    swal("Ingresa la Dirección de la Sucursal", "", "error")
    return;
  }

  $("#modal-sucursal").modal("hide");

  $.ajax({
    url: phpFile,
    type: "POST",
    data: {
      consulta: "guardar_sucursal",
      id_transportista: id_transportista,
      nombre: nombre.toUpperCase(),
      direccion: direccion.toUpperCase(),
      telefono: telefono && telefono.length ? telefono.toUpperCase() : null,
      notas: notas && notas.length ? notas.toUpperCase() : null,
      id_sucursal: editingSucursal
    },
    success: function (x) {
      if (x.includes("success")){
        getTableSucursales();
        swal("Guardaste la Sucursal correctamente!", "", "success")
      }
      else{
        swal("Ocurrió un error", x, "error")
        $("#modal-sucursal").modal("show")
      }
    },
    error: function(error){
      swal("Ocurrió un error", error, "error")
      $("#modal-sucursal").modal("show")
    }
  });
}

function eliminarSucursal(e, id) {
  e.preventDefault();
  e.stopPropagation();
  swal(`¿ELIMINAR esta Sucursal?`, "", {
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
        $.ajax({
          url: phpFile,
          type: "POST",
          data: {
            consulta: "eliminar_sucursal",
            id: id
          },
          success: function (x) {
            if (x.includes("success")){
              getTableSucursales();
              swal("Eliminaste la Sucursal correctamente", "", "success")
            }
            else{
              swal("Ocurrió un error", x, "error")
            }
          },
          error: function(){
          }
        });

      default:
        break;
    }
  });
}
