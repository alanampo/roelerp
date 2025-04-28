function modalVivero(data) {
  $("#modal-vivero").removeAttr("x-id-cliente");
  $("#modal-vivero").find("input").val("");
  $("#modal-vivero .modal-title").html("Agregar Vivero");
  $("#modal-vivero").removeAttr("x-id-cliente");
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

    if (data){
      $("#modal-vivero .modal-title").html("Modificar Vivero");

      const {id, nombre, domicilio, telefono, email, rut, comuna} = data;
      $("#modal-vivero").attr("x-id-vivero", id);
      $("#input-nombre").val(nombre);
      $("#input-domicilio").val(domicilio ? domicilio : "");
      $("#input-telefono").val(telefono ? telefono : "");
      $("#input-comuna").val(comuna ? comuna : "");
      $("#input-rut").val(rut ? rut : "");
      $("#input-email").val(email ? email : "");  
    }
    
  $("#modal-vivero").modal("show");
  document.getElementById("input-nombre").focus();
}

function guardarVivero() {
  const nombre = $("#input-nombre").val().trim();
  const domicilio = $("#input-domicilio").val().trim();
  const telefono = $("#input-telefono").val().trim();
  const rut = $("#input-rut").val().trim();
  const email = $("#input-email").val().trim();
  const comuna = $("#input-comuna").val().trim();
  const id_vivero = $("#modal-vivero").attr("x-id-vivero");

  if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
  } else {
    $("#modal-vivero").modal("hide");
    $.ajax({
      url: "data_ver_viveros.php",
      type: "POST",
      data: {
        consulta: "guardar_vivero",
        id: id_vivero && id_vivero.length ? id_vivero : null,
        nombre: nombre,
        domicilio: domicilio,
        telefono: telefono,
        rut: rut,
        email: email,
        comuna: comuna,
      },
      success: function (x) {
        console.log(x);
        if (x.trim() == "success") {
          if (location.href.includes("ver_viveros")) {
            getTableViveros();
          } else {
            getViverosSelect();
          }
          swal("El Vivero fue guardado correctamente!", "", "success");
        } else {
          swal("Ocurrió un error al guardar el Vivero", x, "error");
          $("#modal-vivero").modal("show");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
        $("#modal-vivero").modal("show");
      },
    });
  }
}
