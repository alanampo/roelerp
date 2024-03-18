let edit_mode = false;

$(document).ready(function () {
  $("#username_txt,#password_txt,#password2_txt").on(
    "keydown",
    function (e) {
      if (e.keyCode == 32) return false;
    }
  );

  busca_usuarios();
});

function busca_usuarios() {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando usuarios, espere...");
    },
    url: "data_ver_usuarios.php",
    type: "POST",
    data: { consulta: "busca_usuarios" },
    success: function (x) {
      $("#tabla_entradas").html(x);

      $("#tabla").DataTable({
        order: [[1, "asc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ usuarios por página",
          zeroRecords: "No hay usuarios",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay usuarios",
          infoFiltered: "(filtrado de _MAX_ usuarios en total)",
          lengthMenu: "Mostrar _MENU_ usuarios",
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

function ModificarUsuario(id_usuario, password, nombre, nombre_real, permisos) {
  $("#ModalAgregarUsuario").attr("x-id-usuario", id_usuario);

  let arraypermisos = null;
  if (permisos.length > 0) {
    if (permisos.includes(",")) {
      arraypermisos = permisos.split(", ");
    } else {
      arraypermisos = [permisos];
    }
  }
  $("#select_permisos").val(arraypermisos).selectpicker("refresh");
  $("#username_txt").val(nombre);
  $("#nombre_txt").val(nombre_real);

  $("#password_txt").val(password);
  $("#password2_txt").val(password);

  $("#ModalAgregarUsuario").find("#titulo").html("Modificar Usuario");

  $("#btn-guardar-usuario").on("click", function () {
    modificarUsuario();
  });

  edit_mode = true;
  let modal = document.getElementById("ModalAgregarUsuario");
  modal.style.display = "block";
  document.getElementById("username_txt").focus();
}

function MostrarModalAgregarUsuario() {
  $("#ModalAgregarUsuario")
    .find("#username_txt,#nombre_txt,#password_txt,#password2_txt")
    .val("");
  $("#select_permisos").val("default").selectpicker("refresh");
  
  $("#select_cliente").val("default").selectpicker("refresh");

  $("#ModalAgregarUsuario").find("#titulo").html("Agregar Usuario");
  edit_mode = false;
  let modal = document.getElementById("ModalAgregarUsuario");
  modal.style.display = "block";
  $("#btn-guardar-usuario").on("click", function () {
    guardarNuevoUsuario();
  });
  document.getElementById("username_txt").focus();
}

function CerrarModal() {
  let modal = document.getElementById("ModalAgregarUsuario");
  modal.style.display = "none";
}

function guardarNuevoUsuario() {
  const nombre = $("#username_txt").val().trim();
  const nombre_real = $("#nombre_txt").val().trim();
  const password1 = $("#password_txt").val().trim();
  const password2 = $("#password2_txt").val().trim();
  const permisos = $("#select_permisos").val();
  
  if (nombre.length < 3) {
    swal(
      "Debes ingresar un nombre de usuario de al menos 3 letras",
      "",
      "error"
    );
  } else if (nombre_real.length < 4) {
    swal("Debes ingresar un nombre de al menos 4 letras", "", "error");
  } else if (password1.length < 1) {
    swal("Debes ingresar una contraseña!", "", "error");
  } else if (password1 != password2) {
    swal("Las contraseñas ingresadas no coinciden", "", "error");
  } else if (password1.length > 20) {
    swal("La contraseña es demasiado larga!", "", "error");
  } else if (permisos.length == 0) {
    swal("Debes seleccionar al menos un permiso", "", "error");
  } else if (/^[A-Za-z0-9]+$/.test(password1) == false) {
    swal("La contraseña solo puede tener letras y/o números", "error");
  } else {
    document.getElementById("ModalAgregarUsuario").style.display = "none";
    $.ajax({
      url: "data_ver_usuarios.php",
      type: "POST",
      data: {
        consulta: "agregar",
        nombre: nombre,
        nombre_real: nombre_real,
        password: password1,
        permisos: JSON.stringify(permisos),
      },
      success: function (x) {
        if (x.includes("yaexiste")) {
          swal("Ya existe un usuario con ese nombre", "", "error");
          document.getElementById("ModalAgregarUsuario").style.display =
            "block";
        } else if (x.trim() == "success") {
          swal("El usuario fue agregado correctamente!", "", "success");
          CerrarModal();
          busca_usuarios();
        } else {
          swal("Ocurrió un error", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
}

function modificarUsuario() {
  const nombre = $("#username_txt").val().trim();
  const nombre_real = $("#nombre_txt").val().trim();
  const password1 = $("#password_txt").val().trim();
  const password2 = $("#password2_txt").val().trim();
  const permisos = $("#select_permisos").val();
  const id_usuario = $("#ModalAgregarUsuario").attr("x-id-usuario");
  
  if (nombre.length < 3) {
    swal(
      "Debes ingresar un nombre de usuario de al menos 3 letras",
      "",
      "error"
    );
  } else if (nombre_real.length < 4) {
    swal("Debes ingresar un nombre de al menos 4 letras", "", "error");
  } else if (password1.length < 1) {
    swal("Debes ingresar una contraseña!", "", "error");
  } else if (password1 != password2) {
    swal("Las contraseñas ingresadas no coinciden", "", "error");
  } else if (password1.length > 20) {
    swal("La contraseña es demasiado larga!", "", "error");
  } else if (permisos.length == 0) {
    swal("Debes seleccionar al menos un permiso", "", "error");
  } else if (/^[A-Za-z0-9]+$/.test(password1) == false) {
    swal("La contraseña solo puede tener letras y/o números", "error");
  } else {
    document.getElementById("ModalAgregarUsuario").style.display = "none";
    $.ajax({
      url: "data_ver_usuarios.php",
      type: "POST",
      data: {
        id_usuario: id_usuario,
        consulta: "editar",
        nombre: nombre,
        nombre_real: nombre_real,
        password: password1,
        permisos: JSON.stringify(permisos),
      },
      success: function (x) {
        if (x.includes("yaexiste")) {
          swal("Ya existe un usuario con ese nombre", "", "error");
          document.getElementById("ModalAgregarUsuario").style.display =
            "block";
        } else if (x.trim() == "success") {
          swal("El usuario fue modificado correctamente!", "", "success");
          CerrarModal();
          busca_usuarios();
        } else {
          swal("Ocurrió un error", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
}

function toggleUsuario(id_usuario, inhabilitado) {
  swal(
    `Estás seguro/a de ${
      inhabilitado ? "Inhabilitar a" : "Activar"
    } este usuario?`,
    "",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "ACEPTAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          beforeSend: function () {},
          url: "data_ver_usuarios.php",
          type: "POST",
          data: {
            consulta: "toggle_usuario",
            id_usuario: id_usuario,
            inhabilitado: inhabilitado,
          },
          success: function (x) {
            if (x != "success") {
              swal("Ocurrió un error!", x, "error");
            } else {
              swal("Modificaste el Usuario correctamente!", "", "success");
              busca_usuarios();
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
