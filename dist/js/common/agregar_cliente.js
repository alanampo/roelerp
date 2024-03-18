function MostrarModalAgregarCliente() {
    $("#ModalAgregarCliente").removeAttr("x-id-cliente");
    $("#ModalAgregarCliente").find("input").val("");
    $("#ModalAgregarCliente").find("#titulo").html("Agregar Cliente");
    $("#select-comuna2").val("default").selectpicker("refresh");
    $('#rutcliente_txt').keypress(function (e) {
      var allowedChars = new RegExp("^[0-9\-kK]+$");
      var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
      if (allowedChars.test(str)) {
          return true;
      }
      e.preventDefault();
      return false;
    }).keyup(function() {
      // the addition, which whill check the value after a keyup (triggered by Ctrl+V)
      // We take the same regex as for allowedChars, but we add ^ after the first bracket : it means "all character BUT these"
      var forbiddenChars = new RegExp("[^0-9\-kK]", 'g');
      if (forbiddenChars.test($(this).val())) {
          $(this).val($(this).val().replace(forbiddenChars, ''));
      }
  });

    $("#ModalAgregarCliente").modal("show");
    document.getElementById("nombrecliente_txt").focus();
  }
  
  function GuardarCliente() {
    const nombre = $("#nombrecliente_txt").val().trim();
    const domicilio = $("#domiciliocliente_txt").val().trim();
    const telefono = $("#telcliente_txt").val().trim();
    const rut = $("#rutcliente_txt").val().trim();
    const razonSocial = $("#razonsocial_txt").val().trim();
    const mail = $("#mailcliente_txt").val().trim();
    const comuna = $("#select-comuna2 option:selected").val();
    const id_cliente = $("#ModalAgregarCliente").attr("x-id-cliente");


    if (nombre.length < 3) {
      swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
    } else if (domicilio.length < 3) {
      swal("Debes ingresar un Domicilio!", "", "error");
    }
    else if (!comuna || !comuna.length){
      swal("Selecciona la Comuna!", "", "error")
    }
    else if (!telefono || !telefono.length ) {
      swal("Debes ingresar un teléfono!", "", "error");
    } else if (mail.includes(" ") == true) {
      swal("El E-Mail no puede contener espacios", "", "error");
    } else {
      $("#ModalAgregarCliente").modal("hide");
      $.ajax({
        url: "guarda_cliente.php",
        type: "POST",
        data: {
          tipo: id_cliente && id_cliente.length ? "editar" : "agregar",
          nombre: nombre,
          domicilio: domicilio,
          telefono: telefono,
          rut: rut,
          razonSocial: razonSocial,
          mail: mail,
          comuna: comuna,
          id_cliente: id_cliente && id_cliente.length ? id_cliente : null
        },
        success: function (x) {
          if (x.trim() == "success") {
            if (location.href.includes("ver_clientes")){
              busca_clientes()
            }
            else{
              pone_clientes();
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
  

  function setRazonSocial(){
    const nombre = $("#nombrecliente_txt").val().trim();
  
    if (nombre && nombre.length){
      $("#razonsocial_txt").val(nombre);
    }
  }
  
  function modificarCliente(tr, id_cliente){
    MostrarModalAgregarCliente()
    $("#ModalAgregarCliente").find("#titulo").html("Modificar Cliente");

    $("#ModalAgregarCliente").attr("x-id-cliente", id_cliente);

    const nombre = $(tr).find(".td-nombre").text();
    const razon = $(tr).attr("x-razon");
    const domicilio = $(tr).find(".td-domicilio").text();
    const telefono = $(tr).find(".td-telefono").text();
    const email = $(tr).find(".td-email").text();
    const rut = $(tr).find(".td-rut").text();
    const id_comuna = $(tr).find(".td-comuna").attr("x-id");

    $("#nombrecliente_txt").val(nombre);
    $("#domiciliocliente_txt").val(domicilio);
    $("#telcliente_txt").val(telefono);
    $("#rutcliente_txt").val(rut);
    $("#razonsocial_txt").val(razon);
    $("#mailcliente_txt").val(email);
    $("#select-comuna2").val(id_comuna).selectpicker("refresh");

  }