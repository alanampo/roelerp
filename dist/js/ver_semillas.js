let currentTab;
let editMode = false;
$(document).ready(function () {
  if (document.location.href.includes("ver_semillas")){
    document.getElementById("defaultOpen").click();
  }

  $.datepicker.setDefaults($.datepicker.regional["es"]);
  $("#input-fecha")
    .datepicker({
      format: "dd-M-yyyy",
      autoclose: true,
      disableTouchKeyboard: true,
      Readonly: true,
      dateFormat: "dd/mm/yy",
    })
    .attr("readonly", "readonly");
  
    $("#input-precio")
    .on("propertychange input", function (e) {
      this.value = this.value.replace(/\D/g, "");
    });
});

function abrirTab(evt, tabName) {
  let i, tabcontent, tablinks;
  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  currentTab = tabName;
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }
  //document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
  busca_entradas(tabName);
}

function busca_entradas(tabName) {
  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Buscando, espere...");
    },
    url: "data_ver_semillas.php",
    type: "POST",
    data: {
      consulta: "busca_"+tabName
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 100,
        order: [[tabName != "stock" ? 0 : 1, tabName != "stock" ? "desc" : "asc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ resultados por página",
          zeroRecords: "No hay resultados",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay resultados",
          infoFiltered: "(filtrado de _MAX_ resultados en total)",
          lengthMenu: "Mostrar _MENU_ resultados",
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

function modalAgregarSemillas(objEditable) {
  $(
    "#select-proveedor,#select-marca,#select-variedad,#select-cliente"
  )
    .val("default")
    .selectpicker("refresh");
  $("#input-cantidad,#input-porcentaje,#input-codigo,#input-precio,#input-total,#input-total-imp").val("");
  $("#input-fecha").val("DD/MM/YYYY")
  cargaVariedadesEspecies()
  $("#modal-agregar-semillas .box-title").html(objEditable ? "Modificar Ingreso Semillas" : "Agregar Semillas")
  $("#select-variedad").attr("disabled", objEditable ? true : false);
  $("#modal-agregar-semillas").modal("show");

  if (objEditable){ //EDITANDO
    editMode = true;
    const tr = $(objEditable).parent();
    const id_cliente = $(tr).attr("x-id-cliente");
    const id_marca = $(tr).attr("x-id-marca");
    const id_proveedor = $(tr).attr("x-id-proveedor");
    const cantidad = $(tr).attr("x-cantidad");
    const precio = $(tr).attr("x-precio");
    const codigo = $(tr).attr("x-codigo");
    const porcentaje = $(tr).attr("x-porcentaje");
    const fecha = $(tr).attr("x-fecha");
    const id_stock_semillas = $(tr).attr("x-id");
    $("#input-cantidad").val(cantidad)
    $("#input-codigo").val(codigo)
    $("#input-porcentaje").val(porcentaje)
    $("#input-fecha").val(fecha)
    $("#input-precio").val(precio)
    setPrecio();
    $("#modal-agregar-semillas").attr("x-id-stock", id_stock_semillas)
    
    loadMarcasSelect(id_marca)
    loadProveedoresSelect(id_proveedor)
    loadClientes(id_cliente)
  }
  else{
    editMode = false;
    loadMarcasSelect()
    loadProveedoresSelect()
    loadClientes()
    $("#modal-agregar-semillas").removeAttr("x-id-stock")
  }
}

function guardarSemillas() {
  const id_variedad_especie = $("#select-variedad").find("option:selected").val();
  const tipo = id_variedad_especie && id_variedad_especie.length ? $("#select-variedad").find("option:selected").attr("x-codigo") : null;
  const marca = $("#select-marca").find("option:selected").val();
  const proveedor = $("#select-proveedor").find("option:selected").val();
  const cantidad = $("#input-cantidad").val().trim();
  const fecha = $("#input-fecha").val().trim();
  const porcentaje = $("#input-porcentaje").val().trim();
  const codigo = $("#input-codigo").val().trim();
  const precio = $("#input-precio").val().trim();
  const total = $("#input-total").val().trim();

  const id_cliente = $("#select-cliente").find("option:selected").val();
  if (
    !editMode && (!id_variedad_especie || !id_variedad_especie.length)
  ) {
    swal(
      "Selecciona una Variedad/Especie",
      "",
      "error"
    );
  }
  else if (
    !marca || !marca.length
  ) {
    swal(
      "Selecciona una Marca de Semillas!",
      "",
      "error"
    );
  }
  else if (
    !proveedor || !proveedor.length
  ) {
    swal(
      "Selecciona un Proveedor de Semillas!",
      "",
      "error"
    );
  }
  else if (
    fecha.includes("DD/")
  ) {
    swal(
      "Selecciona una Fecha",
      "",
      "error"
    );
  }
  else if (!cantidad.length || isNaN(cantidad)) {
    swal("Debes ingresar la cantidad que contiene el paquete de semillas!", "", "error");
  } else if (parseInt(cantidad) <= 0) {
    swal("La cantidad debe ser superior a cero!", "", "error");
  }
  else if (!precio.length || isNaN(precio)) {
    swal("Debes ingresar el precio del Sobre!", "", "error");
  } else if (parseInt(precio) <= 0) {
    swal("El precio debe ser superior a cero!", "", "error");
  } 
  else if (!total.length){
    swal("Revisa la cantidad y precio que ingresaste", "", "error")
  }
  else if (!porcentaje.length || isNaN(porcentaje)) {
    swal("Debes ingresar un porcentaje de germinación!", "", "error");
  } else if (parseInt(porcentaje) <= 0) {
    swal("La cantidad debe ser superior a cero!", "", "error");
  } 
  else if (!codigo.length) {
    swal("Ingresa un código para identificar la semilla!", "", "error");
  }
  else if (!id_cliente || !id_cliente.length){
    swal("Selecciona un Cliente", "", "error")
  }
  else {
    let fecha_raw = fecha.split("/")[2]+"-"+fecha.split("/")[1]+"-"+fecha.split("/")[0];
    $.ajax({
      beforeSend: function () {
        $("#modal-agregar-semillas").modal("hide");
      },
      url: "data_ver_semillas.php",
      type: "POST",
      data: {
        consulta: "guardar_semillas",
        id_marca: marca,
        id_proveedor: proveedor,
        cantidad: cantidad,
        precio: precio,
        porcentaje: porcentaje,
        total:total,
        fecha: fecha_raw,
        codigo: codigo,
        id_cliente: id_cliente,
        tipo: !editMode ? tipo : null, 
        id_variedad_especie: !editMode ? id_variedad_especie : null,
        id_stock_semillas: editMode ? $("#modal-agregar-semillas").attr("x-id-stock") : ""
      },
      success: function (x) {
        if (x.trim() == "success"){
          swal(`${editMode?  "Modificaste" : "Agregaste"} las semillas correctamente!`, "", "success");
          if (document.location.href.includes("ver_semillas")){
            busca_entradas(currentTab);
          }
          else{
            loadSemillasSelect()
          }
        }
        else{
          swal("Ocurrió un error al guardar las semillas", x, "error");
        }
      },
      error: function (jqXHR, estado, error) {},
    });
  }
}

function eliminar(id_stock) {
  swal(
    "Estás seguro/a de ELIMINAR este ingreso de Semillas?",
    "",
    {
      icon: "warning",
      buttons: {
        cancel: "NO",
        catch: {
          text: "SI, ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          type: "POST",
          url: "data_ver_semillas.php",
          data: { consulta: "eliminar_ingreso", id_stock: id_stock },
          success: function (data) {
            if (data.trim() == "success") {
              swal("Eliminaste el Ingreso de Semillas correctamente!", "", "success");
              busca_entradas(currentTab);
            } else {
              swal(
                "Ocurrió un error al eliminar las Semillas",
                data,
                "error"
              );
            }
          },
        });

        break;

      default:
        break;
    }
  });
}
//SEMILLAS
function modalAgregarMarca(){
  $("#input-nombre-marca").val("");
  loadMarcas();
  $("#modal-marca").modal("show")
}

function loadMarcas(){
  $.ajax({
    type: "POST",
    url: "data_ver_semillas.php",
    data: { consulta: "cargar_marcas_tabla" },
    success: function (data) {
      if (data.length){
        $(".tabla-marcas > tbody").html(data);
      }
    },
  });
}

async function loadMarcasSelect(select_id){
  $.ajax({
    beforeSend: function () {
      $("#select-marca").html("Cargando marcas...");
    },
    type: "POST",
    url: "data_ver_semillas.php",
    data: { consulta: "cargar_marcas_select" },
    success: function (x) {
      $("#select-marca").html(x).selectpicker("refresh");
      if (select_id){
        $("#select-marca").val(select_id).selectpicker("refresh");
      }
    },
  });
}

function guardarMarca(){
  const nombre = $("#input-nombre-marca").val().trim();

  if (nombre.length < 3){
    swal("Ingresá un nombre de al menos 3 letras", "", "error");
    return;
  }
  $("#btn-guardar-marca").attr("disabled", true);
  $.ajax({
    type: "POST",
    url: "data_ver_semillas.php",
    data: { consulta: "guardar_marca", nombre: nombre},
    success: function (data) {
      console.log(data)
      if (data.trim() == "success"){
        swal("Guardaste la Marca correctamente!", "", "success");
        $("#input-nombre-marca").val("")
        loadMarcas();
      }
      else if (data.trim() == "yaexiste"){
        swal("Ya existe una marca con ese nombre!", "", "error")
        $("#input-nombre-marca").focus()
      }
      else{
        swal("Ocurrió un error", data, "error")
      }
      $("#btn-guardar-marca").attr("disabled", false);
    },
  });
}

function eliminarMarca(id){
  swal(
    "Estás seguro/a de eliminar esta Marca?",
    "Sólo se podrá eliminar si no fue utilizada en ningún pedido",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          beforeSend: function () {},
          url: "data_ver_semillas.php",
          type: "POST",
          data: { consulta: "eliminar_marca", id_marca: id },
          success: function (x) {
            if (!x.includes("success")) {
              swal("Ocurrió un error!", x, "error");
            } else {
              swal("Eliminaste la Marca correctamente!", "", "success");
              loadMarcas()
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

function modalAgregarProveedor(){
  $("#input-nombre-proveedor").val("");
  loadProveedores();
  $("#modal-proveedor").modal("show")
}

function loadProveedores(){
  $.ajax({
    type: "POST",
    url: "data_ver_semillas.php",
    data: { consulta: "cargar_proveedores_tabla" },
    success: function (data) {
      if (data.length){
        $(".tabla-proveedores > tbody").html(data);
      }
    },
  });
}

async function loadProveedoresSelect(select_id){
  $.ajax({
    beforeSend: function () {
      $("#select-proveedor").html("Cargando proveedores...");
    },
    type: "POST",
    url: "data_ver_semillas.php",
    data: { consulta: "cargar_proveedores_select" },
    success: function (data) {
      $("#select-proveedor").html(data).val("default").selectpicker("refresh");
      if (select_id){
        $("#select-proveedor").val(select_id).selectpicker("refresh");
      }
    },
  });
}

function guardarProveedor(){
  const nombre = $("#input-nombre-proveedor").val().trim();

  if (nombre.length < 3){
    swal("Ingresá un nombre de al menos 3 letras", "", "error");
    return;
  }
  $("#btn-guardar-proveedor").attr("disabled", true);
  $.ajax({
    type: "POST",
    url: "data_ver_semillas.php",
    data: { consulta: "guardar_proveedor", nombre: nombre},
    success: function (data) {
      if (data.trim() == "success"){
        swal("Guardaste el Proveedor correctamente!", "", "success");
        $("#input-nombre-proveedor").val("")
        loadProveedores();
      }
      else if (data.trim() == "yaexiste"){
        swal("Ya existe un proveedor con ese nombre!", "", "error")
        $("#input-nombre-proveedor").focus()
      }
      else{
        swal("Ocurrió un error", data, "error")
      }
      $("#btn-guardar-proveedor").attr("disabled", false);
    },
  });
}

function eliminarProveedor(id){
  swal(
    "Estás seguro/a de eliminar este Proveedor?",
    "Sólo se podrá eliminar si no fue utilizado en ningún pedido",
    {
      icon: "warning",
      buttons: {
        cancel: "Cancelar",
        catch: {
          text: "ELIMINAR",
          value: "catch",
        },
      },
    }
  ).then((value) => {
    switch (value) {
      case "catch":
        $.ajax({
          beforeSend: function () {},
          url: "data_ver_semillas.php",
          type: "POST",
          data: { consulta: "eliminar_proveedor", id_proveedor: id },
          success: function (x) {
            if (!x.includes("success")) {
              swal("Ocurrió un error!", x, "error");
            } else {
              swal("Eliminaste el Proveedor correctamente!", "", "success");
              loadProveedores()
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

function cargaVariedadesEspecies() {
  $.ajax({
    beforeSend: function () {
      $("#select-variedad").html("Cargando variedades...");
    },
    url: "data_ver_semillas.php",
    type: "POST",
    data: { consulta: "busca_variedades_especies_select" },
    success: function (x) {
      $("#select-variedad").val("default").selectpicker("refresh");
      $("#select-variedad").html(x).selectpicker("refresh");
      $("#select-variedad").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
        }
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}

async function loadClientes(select_id) {
  $.ajax({
    beforeSend: function () {
      $("#select-cliente").html("Cargando clientes...");
    },
    url: "data_ver_clientes.php",
    type: "POST",
    data: {
      consulta: "get_clientes_select"
    },
    success: function (x) {
      $("#select-cliente").val("default").selectpicker("refresh");
      $("#select-cliente").html(x).selectpicker("refresh");
      if (select_id){
        $("#select-cliente").val(select_id).selectpicker("refresh");
      }
      $("#select-cliente").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
        }
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}

function loadSemillasSelect(id, tipo){
  const id_cliente = $(".label-main-cliente").first().attr("x-id")
  $.ajax({
    beforeSend: function () {
      $("#select_semillas").html("Cargando semillas...");
    },
    type: "POST",
    url: "data_ver_semillas.php",
    data: { 
      consulta: "cargar_semillas_select",
      id: id,
      tipo: tipo,
      id_cliente: id_cliente,
      plantinera: $("#check_plantinera").is(":checked") ? 1 :0
    },
    success: function (data) {
      $("#select_semillas").html(data).val("default").selectpicker("refresh");
    },
  });
}

function setPrecio(){
  const precio = $("#input-precio").val().trim();
  const cantidad = $("#input-cantidad").val().trim();

  if (precio && precio.length && cantidad && cantidad.length){
    const total = parseInt(precio) / parseInt(cantidad);
    $("#input-total").val(Math.round(total));
    $("#input-total-imp").val(Math.round(total * 1.19));
  }
  else{
    $("#input-total,#input-total-imp").val("");
  }
}