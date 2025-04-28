let edit_mode = false;
let global_id_tipo = null;
let currentTab = null;
let atributosSelect,
  tiposAtributoSelect,
  viverosSelect = null;
  viverosObj = null;
$(document).ready(function () {
  document.getElementById("defaultOpen").click();

  setInputInt("#input-precio-valor");
  setInputInt("#input-vivero-precio-valor");
  setInputInt("#input-vivero-precio-valor2");
  
  $("#input-cantidad-in-out,#input-codigo").on(
    "propertychange input",
    function (e) {
      this.value = this.value.replace(/\D/g, "");
    }
  );
});

function abrirTab(evt, tabName) {
  var i, tablinks;
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  $(".col-select-tipo-pos").addClass("d-none");

  evt.currentTarget.className += " active ";
  if (tabName == "productos" || tabName == "servicios") {
    $(".col-select-tipo").removeClass("d-none");
  } else {
    $(".col-select-tipo").addClass("d-none");
  }

  if (tabName == "ingresos" || tabName == "egresos") {
    $(".col-select-tipo-pos").removeClass("d-none");
  }
  currentTab = tabName;
  busca_productos(tabName);
  pone_tipos();
}

function busca_productos(tab) {
  global_id_tipo = null;
  const filtro = $(
    `#select${
      tab == "productos" || tab == "servicios" ? "_tipo" : "-tipo-pos-filtro"
    } option:selected`
  ).val();

  $.ajax({
    beforeSend: function () {
      $("#tabla_entradas").html("Cargando...");
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      filtro: filtro && filtro.length ? filtro : null,
      consulta:
        tab == "tipos"
          ? "busca_tipos"
          : tab == "productos"
          ? "busca_productos"
          : tab == "tipos-servicio"
          ? "busca_tipos_servicio"
          : tab == "servicios"
          ? "busca_productos"
          : tab == "ingresos"
          ? "busca_ingresos"
          : tab == "egresos"
          ? "busca_egresos"
          : "",
      tab: tab,
    },
    success: function (x) {
      $("#tabla_entradas").html(x);
      $("#tabla").DataTable({
        pageLength: 50,
        order: [[0, "desc"]],
        language: {
          lengthMenu: "Mostrando _MENU_ ítems por página",
          zeroRecords: "No hay ítems",
          info: "Página _PAGE_ de _PAGES_",
          infoEmpty: "No hay ítems",
          infoFiltered: "(filtrado de _MAX_ ítems en total)",
          lengthMenu: "Mostrar _MENU_ ítems",
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

function modalTipo(producto) {
  $("#table-tipos-atributo > tbody").html(``);
  getTiposAtributoSelect();
  if (producto) {
    $("#modal-tipo").attr("x-id", producto.id);
    if (producto.atributos && producto.atributos != "null") {
      const atributos = producto.atributos.split(",");
      atributos.forEach(function (e) {
        const tmp = e.split("|");
        const id = tmp[0];
        const nombre = tmp[1];
        const tipoDato = tmp[2] != "null" ? tmp[2] : null;
        const idTipoAtributo = tmp[3] != "null" ? tmp[3] : null;
        const nombreTipoAtributo = tmp[4] != "null" ? tmp[4] : null;

        $("#table-tipos-atributo > tbody").append(`
          <tr class='tr-ignore atr-${id}' x-id="${id}">
            <td>${nombre}</td>
            <td>${
              tipoDato
                ? tipoDato
                : idTipoAtributo
                ? `${nombreTipoAtributo} (${idTipoAtributo})`
                : ""
            }</td>
            <td class='text-center'>
              <button onclick="eliminarAtributo(this, ${id})" class="btn btn-secondary fa fa-trash btn-sm"></button>
            </td>
          </tr>
        `);
      });
    }

    $("#modal-tipo .modal-title").html("Modificar Tipo Producto/Servicio");
    $("#input-nombre-tipo").val(producto.nombre);
    $("#input-siglas").val(producto.codigo);

    document.getElementById("input-nombre-tipo").focus();
    global_id_tipo = producto.id;
    edit_mode = true;
  } else {
    document.getElementById("input-nombre-tipo").focus();
    $("#input-nombre-tipo,#input-siglas").val("");
    $("#modal-tipo .modal-title").html("Agregar Tipo Producto/Servicio");

    edit_mode = false;
    global_id_tipo = null;
  }

  $("#table-tipos-atributo > tbody").append(`
      <tr scope="row" class="tr-add-row">
        <td colspan="3">
          <button onclick="addTipoAtributo()" class="btn btn-success btn-sm"><i class="fa fa-plus-square"></i></button>
        </td>
      </tr>
    `);

  $("#modal-tipo").modal("show");

  setTimeout(() => {
    $("#input-nombre-tipo").focus();
  }, 500);
}

function guardarTipo() {
  let nombre = $("#input-nombre-tipo").val().trim();
  let codigo = $("#input-siglas").val().trim();
  let puede = true;
  if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
    puede = false;
  } else if (!codigo.length) {
    swal("Debes ingresar un código en letras", "Ejemplo: PL", "error");
    puede = false;
  }

  let atributos = [];
  $("#table-tipos-atributo > tbody > tr").each(function (i) {
    if ($(this).hasClass("tr-add-row") || $(this).hasClass("tr-ignore")) return;

    const nombre = $(this).find("input").first().val().trim();
    const tipoDato = $(this)
      .find(".selectpicker")
      .first()
      .find("option:selected")
      .val();

    if (!nombre || !nombre.length) {
      swal("El campo Nombre del Atributo no puede quedar vacío!", "", "error");
      return;
    }

    if (!tipoDato || !tipoDato.length) {
      swal("Debes seleccionar el Tipo de Dato del Atributo!", "", "error");
      return;
    }
    atributos.push({
      nombre: nombre,
      tipoDato: tipoDato,
    });
  });

  if (puede == true) {
    if (!edit_mode) {
      $.ajax({
        url: "data_ver_inventario.php",
        type: "POST",
        data: {
          consulta: "agregar_tipo",
          nombre: nombre,
          codigo: codigo,
          tab: currentTab,
          atributos: atributos.length ? JSON.stringify(atributos) : null,
        },
        success: function (x) {
          console.log(atributos);
          if (x.includes("existe")) {
            swal("Ya existe un Tipo con ese nombre", "", "error");
          } else if (x.trim() == "success") {
            busca_productos(currentTab);
            $("#input-nombre-tipo,#input-siglas").val("");
            $("#input-nombre-tipo").focus();
            $("#modal-tipo").modal("hide");
            swal("El Tipo se agregó correctamente!", "", "success");
          } else {
            swal("Ocurrió un error", x, "error");
          }
        },
        error: function (jqXHR, estado, error) {
          alert(
            "Hubo un error al agregar el Tipo de Producto/Servicio " + error
          );
        },
      });
    } else {
      $.ajax({
        url: "data_ver_inventario.php",
        type: "POST",
        data: {
          consulta: "editar_tipo",
          id_tipo: $("#modal-tipo").attr("x-id"),
          nombre: nombre,
          codigo: codigo,
          tab: currentTab,
          edit_mode: true,
          atributos: atributos.length ? JSON.stringify(atributos) : null,
        },
        success: function (x) {
          if (x.trim() == "success") {
            $("#modal-tipo").modal("hide");
            swal("El Tipo fue modificado correctamente!", "", "success");
            busca_productos(currentTab);
          } else {
            swal(
              "Ocurrió un error al modificar el Producto/Servicio",
              x,
              "error"
            );
          }
        },
        error: function (jqXHR, estado, error) {
          alert("Hubo un error al modificar el Producto/Servicio " + error);
        },
      });
    }
  }
}

//-------

function modalProducto(producto) {
  //PRODUCTO O SERVICIO
  $("#table-atributos > tbody").html("");

  $("#select_tipo2").unbind("changed.bs.select");

  if (producto) {
    // EDITANDO
    $("#modal-producto .modal-title").html("Modificar Producto/Servicio");
    $("#select_tipo2").attr("disabled", "disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#input-nombre-producto").val(producto.nombre);

    $("#input-codigo").val(producto.id_interno).attr("disabled", true);

    $("#modal-producto").attr("x-id-producto", producto.id);
    $("#modal-producto").attr("x-codigo-tipo", producto.codigo_tipo);
    getTableAtributosProducto(producto.id, producto.id_tipo);
    edit_mode = true;
  } else {
    //AGREGANDO
    $("#input-nombre-producto, #input-codigo").val("");

    $("#input-codigo").removeAttr("disabled");
    $("#select_tipo2").removeAttr("disabled");
    $("#select_tipo2").val("default").selectpicker("refresh");
    $("#modal-producto .modal-title").html("Agregar Producto/Servicio");

    $("#modal-producto").removeAttr("x-codigo-tipo");
    $("#select_tipo2").on(
      "changed.bs.select",
      function (e, clickedIndex, newValue, oldValue) {
        getAtributosProducto(this.value);
      }
    );
    edit_mode = false;
  }

  $("#modal-producto").modal("show");
  $("#input-nombre-producto").focus();
}

function guardarProducto() {
  const id_tipo = $("#select_tipo2 option:selected").val();
  const nombre = $("#input-nombre-producto").val().trim().replace(/\s+/g, " ");

  const codigo = $("#input-codigo").val().trim().replace(/\s+/g, "");

  if (!edit_mode && !id_tipo.length) {
    if (edit_mode == false) {
      swal("Debes elegir un Tipo", "", "error");
      return;
    }
  } else if (nombre.length < 3) {
    swal("Debes ingresar un nombre de al menos 3 letras", "", "error");
    return;
  } else if (!edit_mode && !codigo.length) {
    swal("Ingresá un código", "", "error");
    return;
  } 

  let atributos = null;
  let puede = true;
  if ($("#table-atributos > tbody > tr").length) {
    atributos = [];
    $("#table-atributos > tbody > tr").each(function (e) {
      const id = $(this).attr("x-id");
      const rowid_valor = $(this).attr("x-rowid-valor");
      const tipo_dato = $(this).attr("x-tipo-dato");
      const id_atributo_tipo_valor = $(this).attr("x-atributo-tipo-valor");
      const valor = $(this).find(".inp").length
        ? $(this).find(".inp").first().val()
        : null;
      const valorSelect = $(this).find(".selectpicker").length
        ? $(this).find(".selectpicker").first().val()
        : null;
      atributos.push({
        id: id,
        tipo_dato:
          !tipo_dato || !tipo_dato.length || tipo_dato == "null"
            ? null
            : tipo_dato,
        valor: valor && valor.length ? valor.toUpperCase() : null,
        id_atributo_tipo_valor:
          id_atributo_tipo_valor && id_atributo_tipo_valor.length
            ? id_atributo_tipo_valor
            : null,
        rowid_valor:
          rowid_valor && rowid_valor.length && rowid_valor != "null"
            ? rowid_valor
            : null,
        valorSelect: valorSelect && valorSelect.length ? valorSelect : null,
      });
    });
  }
  if (!puede) {
    swal("Los valores no pueden quedar vacíos!", "", "error");
    return;
  }

  $("#modal-producto").modal("hide");
  if (!edit_mode) {
    $.ajax({
      url: "data_ver_inventario.php",
      type: "POST",
      data: {
        consulta: "agregar_producto",
        nombre: nombre,
        id_tipo: id_tipo,
        codigo: codigo,
        tab: currentTab,
        atributos:
          atributos && atributos.length ? JSON.stringify(atributos) : null,
      },
      success: function (x) {
        if (x.trim() == "success") {
          busca_productos(currentTab);
          $("#input-nombre-producto,#input-codigo").val("");
          $("#select_tipo2").val("default").selectpicker("refresh");
          $("#input-nombre-producto").focus();
          swal("El Producto/Servicio se agregó correctamente!", "", "success");
        } else {
          swal(x.replace("error: ", ""), "", "error");
          $("#modal-producto").modal("show");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
      },
    });
  } else {
    codigo_tipo = $("#modal-producto").attr("x-codigo-tipo");

    $.ajax({
      url: "data_ver_inventario.php",
      type: "POST",
      data: {
        consulta: "editar_producto",
        id_producto: $("#modal-producto").attr("x-id-producto"),
        nombre: nombre,
        tab: currentTab,
        atributos:
          atributos && atributos.length ? JSON.stringify(atributos) : null,
      },
      success: function (x) {
        if (x.trim() == "success") {
          swal(
            "El Producto/Servicio fue modificado correctamente!",
            "",
            "success"
          );
          busca_productos(currentTab);
        } else {
          swal(
            "Ocurrió un error al modificar el Producto/Servicio",
            x,
            "error"
          );
        }
      },
      error: function (jqXHR, estado, error) {
        swal(
          "Ocurrió un error al modificar el Producto/Servicio",
          error.toString(),
          "error"
        );
        $("#modal-producto").modal("show");
      },
    });
  }
}

function pone_tipos() {
  $.ajax({
    beforeSend: function () {
      $("#select_tipo").html("Cargando tipos...");
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: { consulta: "busca_tipos_select", tab: currentTab },
    success: function (x) {
      $(".selectpicker").selectpicker();
      $("#select_tipo").html(x).selectpicker("refresh");
      $("#select_tipo2").html(x).selectpicker("refresh");

      // $("#select_tipo2").on(
      //   "change",
      //   function (e, clickedIndex, newValue, oldValue) {
      //     //cargarUltimaVariedad2(this.value);
      //     $("#input-nombre-productos").focus();
      //   }
      // );
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminar(id) {
  swal("Estás seguro/a de eliminar este Producto/Servicio?", "", {
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
          beforeSend: function () {},
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_producto",
            id_producto: id,
            tab: currentTab,
          },
          success: function (x) {
            if (x.trim() == "success") {
              swal(
                "Eliminaste el Producto/Servicio correctamente!",
                "",
                "success"
              );
              busca_productos(currentTab);
            } else {
              swal(
                "Ocurrió un error!",
                "Recuerda que sólo se pueden eliminar Productos/Servicios que no estén involucrados en algún Ingreso/Egreso o Pedido",
                "error"
              );
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

function eliminarTipo(id) {
  swal(
    "Estás seguro/a de eliminar este Tipo de Producto?",
    "Sólo se podrá eliminar si no está contenido en ningún pedido.",
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
          url: "data_ver_inventario.php",
          type: "POST",
          data: { consulta: "eliminar_tipo", id: id, tab: currentTab },
          success: function (x) {
            if (x.trim() == "success") {
              swal(
                "Eliminaste el Tipo de Producto/Servicio correctamente!",
                "",
                "success"
              );
              busca_productos(currentTab);
            } else {
              swal(
                "Ocurrió un error!",
                "Recuerda que sólo se pueden eliminar Tipos de Producto/Servicio que no estén involucrados en algún Ingreso/Egreso o Pedido",
                "error"
              );
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

function modalAgregar() {
  if (currentTab == "productos" || currentTab == "servicios") {
    modalProducto();
  } else if (currentTab == "tipos" || currentTab == "tipos-servicio") {
    modalTipo();
  } else if (currentTab == "ingresos" || currentTab == "egresos") {
    modalInOut();
  }
}

function modalInOut() {
  $("#select-pos,#select-tipo-pos").unbind("changed.bs.select");
  
  $("#table-atr-in-out > tbody").html(``);
  if (currentTab == "ingresos") {
    $("#modal-in-out .box-title").html(`Ingreso Producto/Servicio`);
  } else if (currentTab == "egresos") {
    $("#modal-in-out .box-title").html(`Egreso Producto/Servicio`);
  }
  $("#modal-in-out input").val("");
  $("#modal-in-out select").val("default").selectpicker("refresh");

  $("#select-es-pos").val("producto").selectpicker("refresh");
  pone_tiposdeproducto();

  $("#modal-in-out").modal("show");
}

function guardarInOut() {
  const esProductoServicio = $("#select-es-pos").find("option:selected").val();

  if (!esProductoServicio || !esProductoServicio.length) {
    return;
  }

  const id_tipo = $("#select-tipo-pos option:selected").val();
  const id_pos = $("#select-pos option:selected").val();

  const notas = $("#input-notas").val().trim();

  if (!id_tipo || !id_tipo.length) {
    swal("Debes elegir un Tipo de Producto/Servicio!", "", "error");
  } else if (!id_pos || !id_pos.length) {
    swal("Debes elegir un Producto/Servicio!", "", "error");
  } else {
    let atributos = [];

    let puede = true;
    $("#table-atr-in-out > tbody > tr").each(function (i) {
      if ($(this).hasClass("tr-add-row") || $(this).hasClass("tr-ignore"))
        return;

      const cantidad = $(this).find("input").first().val().trim();

      const id_vivero = $(this)
        .find(".selectpicker")
        .first()
        .find("option:selected")
        .val();

      if (!id_vivero || !id_vivero.length) {
        swal("Debes seleccionar el Vivero!", "", "error");
        puede = false;
        return;
      }

      if (!cantidad || !cantidad.length || parseInt(cantidad) < 1) {
        swal("Debes ingresar la Cantidad!", "", "error");
        puede = false;
        return;
      }

      let valores = [];
      $(this)
        .find(".td-atributos")
        .first()
        .find("select")
        .each(function (ind, e) {
          const val = $(e).find("option:selected").val();
          if (val && val.length) {
            valores.push(val)
            // puede = false;
            // swal("Debes seleccionar todos los atributos!", "", "error");
            // return;
          }

          
        });

      if (!puede) return;

      atributos.push({
        id_vivero: id_vivero,
        cantidad: cantidad,
        valores: valores
      });
    });

    console.log(atributos)

    if (!puede) {
      return;
    }
    
    $("#modal-in-out").modal("hide");

    $.ajax({
      url: "data_ver_inventario.php",
      type: "POST",
      data: {
        consulta: "guardar_in_out",
        id_tipo: id_tipo,
        id_pos: id_pos,
        notas: notas && notas.length ? notas.toUpperCase() : null,
        tab: currentTab,
        esProductoServicio: esProductoServicio,
        atributos: atributos && atributos.length ? JSON.stringify(atributos) : null
      },
      success: function (x) {
        console.log(x);
        if (x.trim() == "success") {
          busca_productos(currentTab);
          swal("El Ingreso/Egreso se guardó correctamente", "", "success");
        } else {
          swal(x.replace("error: ", ""), "", "error");
          $("#modal-in-out").modal("show");
        }
      },
      error: function (jqXHR, estado, error) {
        swal("Ocurrió un error", error.toString(), "error");
      },
    });
  }
}

function setPoS(val) {
  $(".label-pos").html(val + ":");

  pone_tiposdeproducto();
  $("#select-pos").html("").selectpicker("refresh");
}

function pone_tiposdeproducto() {
  const pos = $("#select-es-pos option:selected").val();
  if (!pos || !pos.length) return;
  $.ajax({
    beforeSend: function () {
      $("#select-tipo-pos").html("Cargando productos...");
    },
    url: "data_ver_tipos.php",
    type: "POST",
    data: { consulta: "busca_tipos_select", pos: pos },
    success: function (x) {
      console.log(x);
      $(".selectpicker").selectpicker();
      $("#select-tipo-pos").html(x).selectpicker("refresh");
      $("#select-tipo-pos").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          const tipo_pos = $("#select-es-pos").find("option:selected").val();

          carga_pos(this.value, tipo_pos);
        }
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}

function carga_pos(id_tipo, tipo_pos) {
  $("#select-pos").unbind("changed.bs.select");
  $("#table-atr-in-out > tbody").html(``);
  $.ajax({
    beforeSend: function () {
      $("#select-pos").html("Cargando...");
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "busca_productos_select",
      id_tipo: id_tipo,
      tab: tipo_pos + "s",
    },
    success: function (x) {
      $("#select-pos").val("default").selectpicker("refresh");
      $("#select-pos").html(x).selectpicker("refresh");
      $("#select-pos").on(
        "changed.bs.select",
        function (e, clickedIndex, newValue, oldValue) {
          getAtributosInOut(this.value);
          
        }
      );
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminarInOut(id, tab) {
  const tipo = tab == "ingresos" ? "Ingreso" : "Egreso";
  swal(`Estás seguro/a de eliminar este ${tipo}?`, "", {
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
          beforeSend: function () {},
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_in_out",
            id: id,
            tab: tab,
          },
          success: function (x) {
            if (x.trim() == "success") {
              swal(`Eliminaste el ${tipo} correctamente!`, "", "success");
              busca_productos(currentTab);
            } else {
              swal("Ocurrió un error!", x, "error");
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

function addTipoAtributo() {
  if ($("#table-tipos-atributo > tbody > tr").length > 20) {
    return;
  }

  $(".tr-add-row").first().before(`
    <tr>
      <td>
        <input type='search' autocomplete="off" class="form-control" maxlength="20"/>
      </td>
      <td>
      <select data-container="body" class="selectpicker" data-dropup-auto="false"
      title="Tipo" data-style="btn-info">
        ${tiposAtributoSelect ? tiposAtributoSelect : ""}
        <!--
        <option value="VARCHAR">TEXTO CORTO</option>
        <option value="TEXT">TEXTO LARGO / DESCRIPCION</option>
        <option value="INT">NÚMERO ENTERO</option>
        <option value="DECIMAL">NÚMERO DECIMAL</option>
        <option value="DATE">FECHA</option>
        <option value="PRODUCTO">REF. OTRO PRODUCTO</option>
        <option value="COLOR">COLOR SELECCIONABLE</option>
        -->
      </select>
      </td>
      <td class="text-center">
        <button onclick="$(this).parent().parent().remove()" class="btn btn-secondary fa fa-trash btn-sm"></button>
      </td>
    </tr>
  `);

  $("#table-tipos-atributo .selectpicker").selectpicker("refresh");
  $("#table-tipos-atributo .selectpicker").on(
    "changed.bs.select",
    function (e, clickedIndex, newValue, oldValue) {
      if (this.value.includes("atr-")) {
        $(this)
          .closest("tr")
          .find("input")
          .first()
          .val($(this).find("option:selected").attr("x-nombre"));
      } else {
        $(this).closest("tr").find("input").first().val("");
      }
    }
  );
}

function eliminarAtributo(obj, id) {
  $(obj).prop("disabled", true);
  swal(
    "Estás seguro/a de eliminar este Atributo?",
    "Se eliminarán todos los datos relacionados al Atributo",
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
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_atributo",
            id: id,
            tab: currentTab,
          },
          success: function (x) {
            if (x.trim() == "success") {
              $(".atr-" + id).remove();
              busca_productos(currentTab);
            } else {
              swal("Ocurrió un error al eliminar el Atributo", x, "error");
              $(obj).prop("disabled", false);
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

function getAtributosProducto(id) {
  $("#table-atributos > tbody").html("");

  $.ajax({
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "get_atributos_pys",
      id: id,
      tab: currentTab,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          data.forEach(function (e) {
            const { id, nombre, tipo_dato, valores } = e;

            let input = "";
            if (tipo_dato == "VARCHAR") {
              input = `
               <input type='search' autocomplete='off' maxlength='50' class='form-control inp'/>
              `;
            } else if (tipo_dato == "INT") {
              input = `
               <input type='search' autocomplete='off' maxlength='9' class='form-control inp input-int'/>
              `;
            } else if (tipo_dato == "DECIMAL") {
              input = `
               <input type='search' autocomplete='off' maxlength='9' class='form-control inp input-decimal'/>
              `;
            } else if (tipo_dato == "TEXT") {
              input = `
               <textarea style="resize: none;" rows="2" maxlength='500' class='form-control inp'></textarea>
              `;
            } else if (valores && valores.length) {
              input = `
              <select class="selectpicker" data-dropup-auto="false"
              title="Valor" data-container="body" data-size="5" data-style="btn-info" data-width="100%" multiple>`;
              valores.forEach(function (e) {
                input += `
                  <option value='${e.id}'>${e.nombre}</option>
                `;
              });

              input += `</select>
              `;
            }

            $("#table-atributos > tbody").append(`
              <tr x-id='${id}' x-tipo-dato='${tipo_dato}' class='text-center'>
                <td>${nombre}</td>
                <td style="max-width: 200px !important">
                  ${input}
                </td>
              </tr>
            `);
          });

          setInputDecimal(".input-decimal");
          setInputInt(".input-int");
          $("#table-atributos .selectpicker").selectpicker("refresh");
        } catch (error) {
          console.error(error);
        }
      }
    },
  });

  $(".tr-add-row").first().before(`
      <tr>
        <td>
          <input type='search' autocomplete="off" class="form-control" maxlength="20"/>
        </td>
        <td>
          <select class="selectpicker" data-container="body" data-dropup-auto="false"
          title="Tipo" data-style="btn-info">
            <!--
            <option value="VARCHAR">TEXTO CORTO</option>
            <option value="TEXT">TEXTO LARGO / DESCRIPCION</option>
            <option value="INT">NÚMERO ENTERO</option>
            <option value="DECIMAL">NÚMERO DECIMAL</option>
            
            <option value="DATE">FECHA</option>
            <option value="PRODUCTO">REF. OTRO PRODUCTO</option>
            <option value="COLOR">COLOR SELECCIONABLE</option>
            -->
          </select>
        </td>
        <td class="text-center">
          <button onclick="$(this).parent().parent().remove()" class="btn btn-secondary fa fa-trash btn-sm"></button>
        </td>
      </tr>
    `);

  $("#table-tipos-atributo .selectpicker").selectpicker("refresh");
}

function modalAtributos() {
  $("#modal-atributos input").val("");
  getTableTiposAtributo();
  $("#modal-atributos").modal("show");

  $("#modal-atributos input").focus();
}

function guardarTipoAtributo() {
  const nombre = $("#input-nombre-atributo").val().trim().replace(/\s/g, " ");
  if (!nombre || !nombre.length) {
    swal("Ingresa el Nombre del Tipo de Atributo", "", "error");
    return;
  }

  if ($("#table-crud > tbody > tr").length > 24) {
    swal("Sólo puedes agregar hasta 25 atributos!", "", "error");
    return;
  }

  $("#modal-atributos .btn-guardar").prop("disabled", true);
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "guardar_tipo_atributo",
      nombre: nombre,
    },
    success: function (x) {
      if (x.trim() == "success") {
        $("#modal-atributos input").val("");
        $("#modal-atributos input").focus();
        getTiposAtributoSelect();
        getTableTiposAtributo();
      } else {
        swal("Ocurrió un error al guardar el Tipo de Atributo", x, "error");
      }
      $("#modal-atributos .btn-guardar").prop("disabled", false);
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getTableTiposAtributo() {
  $("#table-crud > tbody").html("");
  $.ajax({
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "get_table_tipos_atributo",
    },
    success: function (x) {
      console.log(x);
      $("#table-crud > tbody").html(x);
      setInputInt(".input-int");
    },
  });
}

function guardarValorAtributo(id, obj) {
  const nombre = $(obj)
    .parent()
    .find("input")
    .first()
    .val()
    .trim()
    .replace(/\s/g, " ");
  const precioExtra = $(obj)
    .parent()
    .find(".input-int")
    .val()
    .trim()
    .replace(/\s/g, "");
  if (!nombre || !nombre.length) {
    swal("Ingresa el Valor del Atributo", "", "error");
    return;
  }
  $(obj).prop("disabled", true);
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "guardar_valor_atributo",
      nombre: nombre.toUpperCase(),
      precioExtra:
        precioExtra && precioExtra.length && parseInt(precioExtra) > 0
          ? precioExtra
          : null,
      id: id,
    },
    success: function (x) {
      if (x.trim() == "success") {
        $(obj).parent().find("input").val("");
        $(obj).parent().find("input").first().focus();
        getTableTiposAtributo();
      } else {
        swal("Ocurrió un error al guardar el Valor del Atributo", x, "error");
      }
      $(obj).prop("disabled", false);
    },
    error: function (jqXHR, estado, error) {},
  });
}

function eliminarValorAtributo(id, obj, nombre) {
  $(obj).prop("disabled", true);
  swal(`Estás seguro/a de eliminar el Valor ${nombre}?`, "", {
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
          beforeSend: function () {},
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_valor_atributo",
            id: id,
          },
          success: function (x) {
            if (x.trim() == "success") {
              getTableTiposAtributo();
            } else {
              swal("Ocurrió un error al eliminar el Valor", x, "error");
              $(obj).prop("disabled", false);
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

function eliminarTipoAtributo(id, obj, nombre) {
  $(obj).prop("disabled", true);
  swal(`Estás seguro/a de eliminar el Atributo ${nombre}?`, "", {
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
          beforeSend: function () {},
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_tipo_atributo",
            id: id,
          },
          success: function (x) {
            if (x.trim() == "success") {
              getTableTiposAtributo();
            } else {
              swal("Ocurrió un error al eliminar el Atributo", x, "error");
              $(obj).prop("disabled", false);
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

function getTiposAtributoSelect() {
  $.ajax({
    beforeSend: function () {
      tiposAtributoSelect = null;
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: { consulta: "get_tipos_atributo_select" },
    success: function (x) {
      console.log(x);
      if (x.includes("option")) {
        tiposAtributoSelect = x.trim();
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getTableAtributosProducto(id, id_tipo) {
  $("#table-atributos > tbody").html("");

  $.ajax({
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "get_table_atributos_pys",
      id: id,
      id_tipo: id_tipo,
      tab: currentTab,
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          console.log(data);
          data.forEach(function (e) {
            const {
              id,
              nombre,
              tipo_dato,
              valores,
              valores_selected,
              id_atributo_tipo_valor,
              valor_varchar,
              valor_int,
              valor_decimal,
              valor_date,
              valor_text,
              rowid_valor,
            } = e;

            let input = "";
            if (tipo_dato == "VARCHAR") {
              input = `
               <input type='search' autocomplete='off' maxlength='50' class='form-control inp' value="${
                 valor_varchar ? valor_varchar : ""
               }"/>
              `;
            } else if (tipo_dato == "INT") {
              input = `
               <input type='search' autocomplete='off' maxlength='9' class='form-control inp input-int' value="${
                 valor_int ? valor_int : ""
               }"/>
              `;
            } else if (tipo_dato == "DECIMAL") {
              input = `
               <input type='search' autocomplete='off' maxlength='9' class='form-control inp input-decimal' value="${
                 valor_decimal ? valor_decimal : ""
               }"/>
              `;
            } else if (tipo_dato == "TEXT") {
              input = `
               <textarea style="resize: none;" rows="2" maxlength='500' class='form-control inp'>${
                 valor_text ? valor_text : ""
               }</textarea>
              `;
            } else if (valores && valores.length) {
              input = `
              <select class="selectpicker" data-dropup-auto="false"
              title="Valor" data-container="body" id="selatr-${id}" data-size="5" data-style="btn-info" data-width="100%" multiple>`;
              valores.forEach(function (e) {
                input += `
                  <option x-atr='true' value='${e.id}'>${e.nombre}</option>
                `;
              });

              input += `</select>
              `;
            }

            $("#table-atributos > tbody").append(`
              <tr x-id='${id}' x-rowid-valor='${rowid_valor}' x-tipo-dato='${tipo_dato}' ${id_atributo_tipo_valor ? `x-id-atributo-tipo-valor='${id_atributo_tipo_valor}'` : ""} class='text-center'>
                <td>${nombre}</td>
                <td style="max-width: 200px !important">
                  ${input}
                </td>
              </tr>
            `);

            if (valores_selected && valores_selected.length) {
              $(`#selatr-${id}`).selectpicker("val", valores_selected);
            }
          });

          setInputDecimal(".input-decimal");
          setInputInt(".input-int");

          $("#table-atributos .selectpicker").selectpicker("refresh");
        } catch (error) {
          console.error(error);
        }
      }
    },
  });
}

function clearFiltro() {
  $("#select_tipo").val("").selectpicker("refresh");
  busca_productos(currentTab);
}

function modalEditarValor(id, nombre, precio) {
  $("#input-nombre-valor").val(nombre);
  $("#input-precio-valor").val(precio && precio.length ? parseInt(precio) : "");
  $("#input-vivero-precio-valor").val("")

  getViverosValoresSelect();
  getPySSelect();
  getTablePreciosViveros(id);

  getTablePreciosViverosPyS(id);
  $("#modal-editar-valores").attr("x-id", id);
  $("#modal-editar-valores").modal("show");
  
}

function editarValor() {
  const nombre = $("#input-nombre-valor").val().trim().replace(/\s+/g, " ");
  const precioExtra = $("#input-precio-valor").val().trim().replace(/\s+/g, "");
  const id = $("#modal-editar-valores").attr("x-id");

  if (!nombre || !nombre.length) {
    swal("Ingresa el Nombre del Valor", "", "error");
    return;
  }

  $.ajax({
    beforeSend: function () {
      $("#modal-editar-valores").modal("hide");
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "editar_valor",
      id: id,
      nombre: nombre.toUpperCase(),
      precioExtra:
        precioExtra && precioExtra.length && parseInt(precioExtra) > 0
          ? precioExtra
          : null,
    },
    success: function (x) {
      console.log(x);
      if (x.includes("success")) {
        getTableTiposAtributo();
      } else {
        swal("Ocurrió un error al editar el Valor", "", "error");
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getViverosValoresSelect() {
  $.ajax({
    beforeSend: function () {
      $("#select-vivero-valores,#select-vivero-valores2").html("").selectpicker("refresh")
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: { consulta: "get_viveros_valores_select" },
    success: function (x) {
      console.log(x);
      $("#select-vivero-valores,#select-vivero-valores2").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}

function addProductoIngreso() {
  if ($("#table-atr-in-out > tbody > tr").length > 20 || !viverosObj) {
    return;
  }

    viverosSelect = "";
    viverosObj.forEach(function(v){
      viverosSelect += `<option x-nombre='${v.nombre}' x-precio='${v.precio ? v.precio : ""}' x-precio-mayorista='${v.precioM ? v.precioM : ""}' value='${v.id}'>${v.nombre} (${v.id})</option>`
    })          
  

  $("#table-atr-in-out > tbody .tr-add-row").first().before(`
    <tr>
      <td>
        <select class="selectpicker select-vivero" data-dropup-auto="false"
        title="Vivero" onchange="setPrecioVivero(this)" data-style="btn-info" data-size="8">
          ${viverosSelect}
        </select>
      </td>
      <td class="td-atributos">
        ${atributosSelect}
      </td>
      <td>
        <input type='search' autocomplete="off" class="form-control input-int text-center" maxlength="6"/>
      </td>
      <td class="text-center td-precio">
        
      </td>
      <td class="text-center">
        <button onclick="$(this).parent().parent().remove()" class="btn btn-secondary fa fa-trash btn-sm"></button>
      </td>
    </tr>
  `);

  $("#table-atr-in-out .selectpicker").selectpicker("refresh");
  setInputInt($(".input-int"));
}

function getAtributosInOut(id_pos) {
  atributosSelect = null;
  viverosObj = null;
  $("#table-atr-in-out > tbody").html(``);
  $.ajax({
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "get_atributos",
      id_pos: id_pos,
      esPos: $("#select-es-pos option:selected").val(),
    },
    success: function (x) {
      console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          console.log(data);

          const hasKeys = data.atributos != null && data.atributos != undefined ? !!Object.keys(data.atributos).length : false;
          atributosSelect = "";
          if (hasKeys) {
            const keys = JSON.stringify(Object.keys(data.atributos));
            const tmp = JSON.parse(keys);
            for (let i = 0; i < tmp.length; i++) {
              //ITERO CADA KEY - CADA TIPO DE ATRIBUTO
              const key = tmp[i];

              atributosSelect += `
                <div class="row mb-2">
                  <div class="col">
                      <select class="selectpicker" data-dropup-auto="false"
                      title="${data.atributos[key][0].nombre_atributo_tipo}" data-width="100%" data-style="btn-info" data-size="8">
              `;

              let options = "";
              data.atributos[key].forEach(function (atr, i) {
                const {
                  nombre_atributo_tipo_valor,
                  id_atributo_tipo_valor,
                  precio_extra,
                } = atr;
                options += `
                  <option x-nombre-atr="${
                    data.atributos[key][0].nombre_atributo_tipo
                  }" x-nombre="${nombre_atributo_tipo_valor}" x-precio="${
                  precio_extra && precio_extra.length
                    ? parseInt(precio_extra)
                    : ""
                }" value="${id_atributo_tipo_valor}">${nombre_atributo_tipo_valor}${
                  precio_extra && precio_extra.length
                    ? " ($" +
                      parseInt(precio_extra).toLocaleString("es-ES", {
                        minimumFractionDigits: 0,
                      }) +
                      ")"
                    : ""
                }</option>
                `;
              });

              atributosSelect += `
                 ${options}
                      </select>
                    
                  </div>
                </div>
              `;
            } // FIN ITERACION KEYS

            $("#table-atr-in-out .selectpicker").selectpicker("refresh");
          }

          viverosObj = data.viverosArr;

          $("#table-atr-in-out > tbody").append(`
          <tr>
            <td style="max-width:140px">
              <select class="selectpicker select-vivero" data-dropup-auto="false"
              title="Vivero" data-size="8" onchange="setPrecioVivero(this)" data-width="100%" data-style="btn-info">
                ${data.viveros}
              </select>
            </td>
            <td class="td-atributos">
              ${atributosSelect}
            </td>
            <td style="max-width:80px">
              <input type='search' autocomplete="off" class="form-control input-int text-center" maxlength="6"/>
            </td>
            <td class="text-center td-precio">
              
            </td>
          <td class="text-center">
            <button onclick="$(this).parent().parent().remove()" class="btn btn-secondary fa fa-trash btn-sm"></button>
          </td>
        </tr>
        `);
          $("#table-atr-in-out > tbody").append(`
          <tr scope="row" class="tr-add-row">
            <td colspan="5">
              <button onclick="addProductoIngreso()" class="btn btn-success btn-sm"><i class="fa fa-plus-square"></i></button>
            </td>
          </tr>
        `);

          $("#table-atr-in-out > tbody").find(".selectpicker").selectpicker();
          setInputInt($(".input-int"));
        } catch (error) {
          console.error(error);
        }
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}




function setPrecioVivero(obj){
  const precio = $(obj).find("option:selected").attr("x-precio");
  const precioM = $(obj).find("option:selected").attr("x-precio-mayorista");
  const td = $(obj).closest("tr").find(".td-precio");
  $(td).html(`<div class="label-precio"></div>`);

  if (precio && precio.length){
    $(td).find(".label-precio").html(`<h6>$
      ${parseInt(precio).toLocaleString("es-ES", {
        minimumFractionDigits: 0,
      })+(precioM && precioM.length ? 
        "<br>$ "+precioM.toLocaleString("es-ES", {
          minimumFractionDigits: 0,
        }) : ""
        )}
    </h6>`)
  }

  const id_pos = $("#select-pos option:selected").val();
  $(td).append(`<button onclick="modalPrecio('${precio}', '${precioM}', ${$(obj).find("option:selected").val()}, ${id_pos}, '${$("#select-es-pos option:selected").val()}')" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></button>`)

}
let editingPrecio = null;

function modalPrecio(precio, precioM, id_vivero, id_pos, esPos){
  editingPrecio = {
    precio: precio,
    precioM: precioM,
    id_vivero: id_vivero,
    id_pos: id_pos,
    esPos: esPos
  }
  $("#input-editar-precio").val(precio);
  $("#input-editar-precio-mayorista").val(precioM && precioM.length ? precioM : "");
  $("#modal-precio").modal("show");

  $("#modal-precio input").first().focus();

  setInputInt("#input-editar-precio,#input-editar-precio-mayorista");
}

function guardarPrecio(){
  if (!editingPrecio) return;

  const newPrecio = $("#input-editar-precio").val().trim()

  const newPrecioM = $("#input-editar-precio-mayorista").val().trim()

  if (!newPrecio || !newPrecio.length || parseInt(newPrecio) <= 0){
    swal("Ingresa el Precio", "", "error");
    return;
  }

  $("#modal-precio").modal("hide");

  $.ajax({
    beforeSend: function () {
      
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: { 
      newPrecio: newPrecio,
      newPrecioM: newPrecioM && newPrecioM.length ? newPrecioM : null,
      id_vivero: editingPrecio.id_vivero,
      id_pos: editingPrecio.id_pos,
      esPos: editingPrecio.esPos,
      consulta: "guardar_precio" 
    },
    success: function (x) {
      console.log(x);
      if (x.includes("success")) {
        swal("Guardaste el Precio correctamente!", "", "success")

        $("#table-atr-in-out > tbody > tr").each(function(i,e){
          const id_v = $(this).find(".select-vivero").find("option:selected").val()
          if (id_v && id_v.length && id_v == editingPrecio.id_vivero){
            $(this).find(".label-precio").html("$ "+newPrecio.toLocaleString("es-ES", {
              minimumFractionDigits: 0,
            })+(newPrecioM && newPrecioM.length ? 
              "<br>$ "+newPrecioM.toLocaleString("es-ES", {
                minimumFractionDigits: 0,
              }) : ""
              ));

          }
        })

        if (viverosObj && viverosObj.length){
          viverosSelect = "";
          viverosObj.forEach(function(v){
            if (v.id == editingPrecio.id_vivero){
              v.precio = newPrecio
              v.precioM = newPrecioM && newPrecio.length ? newPrecio : null
            }
            viverosSelect += `<option x-nombre='${v.nombre}' x-precio='${v.precio ? v.precio : ""}' x-precio-mayorista='${v.precioM ? v.precioM : ""}' value='${v.id}'>${v.nombre} (${v.id})</option>`
          })          
        }

        $(".select-vivero").find(`option[value='${editingPrecio.id_vivero}']`).attr("x-precio", newPrecio)
        $(".select-vivero").find(`option[value='${editingPrecio.id_vivero}']`).attr("x-precio-mayorista", newPrecioM)
        $(".select-vivero").selectpicker("refresh");
          
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getTablePreciosViveros(id_atributo_tipo_valor) {
  $("#table-precios-extra > tbody").html("");

  $.ajax({
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "get_precios_viveros",
      id_atributo_tipo_valor: id_atributo_tipo_valor
    },
    success: function (x) {
      console.log(x);
      $("#table-precios-extra > tbody").html(x);
    },
  });
}

function guardarPrecioVivero(btn){
  const id_vivero = $("#select-vivero-valores option:selected").val()
  const precio = $("#input-vivero-precio-valor").val().trim();
  const id_atributo_tipo_valor = $("#modal-editar-valores").attr("x-id");

  if (!id_atributo_tipo_valor || !id_atributo_tipo_valor.length) return;

  if (!id_vivero || !id_vivero.length){
    swal("Selecciona el Vivero!", "", "error")
  }
  else if (!precio || !precio.length || parseInt(precio) <= 0){
    swal("Ingresa el Precio!", "", "error")
  }
  else{
    $(btn).prop("disabled", true);

    $.ajax({
      url: "data_ver_inventario.php",
      type: "POST",
      data: {
        consulta: "guardar_precio_vivero",
        id_atributo_tipo_valor: id_atributo_tipo_valor,
        id_vivero: id_vivero,
        precio: precio
      },
      success: function (x) {
        console.log(x);
        
        if (x.includes("success")){
          $("#input-vivero-precio-valor").val("");
          $("#select-vivero-valores").val("default").selectpicker("refresh");

          getTablePreciosViveros(id_atributo_tipo_valor);
        }
        
        $(btn).prop("disabled", false)
      },
      error: function(x){
        $(btn).prop("disabled", false)
      }
    });
  }
}

function eliminarPrecioExtra(id, id_atributo_tipo_valor) {
  swal("Estás seguro/a de eliminar este Precio?", "", {
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
          beforeSend: function () {},
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_precio",
            id: id
          },
          success: function (x) {
            if (x.trim() == "success") {
              getTablePreciosViveros(id_atributo_tipo_valor)
            } else {
              swal(
                "Ocurrió un error!",
                x,
                "error"
              );
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
/////
function getPySSelect() {
  $.ajax({
    beforeSend: function () {
      $("#select-pys-valores").html("").selectpicker("refresh")
    },
    url: "data_ver_inventario.php",
    type: "POST",
    data: { consulta: "get_pys_select" },
    success: function (x) {
      console.log(x);
      $("#select-pys-valores").html(x).selectpicker("refresh");
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getTablePreciosViverosPyS(id_atributo_tipo_valor) {
  $("#table-precios-extra-pys > tbody").html("");

  $.ajax({
    url: "data_ver_inventario.php",
    type: "POST",
    data: {
      consulta: "get_precios_viveros_pys",
      id_atributo_tipo_valor: id_atributo_tipo_valor
    },
    success: function (x) {
      console.log(x);
      $("#table-precios-extra-pys > tbody").html(x);
    },
  });
}

function guardarPrecioViveroPyS(btn){
  const id_pys = $("#select-pys-valores option:selected").val()
  const tipo = $("#select-pys-valores option:selected").attr("x-tipo")
  const id_vivero = $("#select-vivero-valores2 option:selected").val()
  const precio = $("#input-vivero-precio-valor2").val().trim();
  const id_atributo_tipo_valor = $("#modal-editar-valores").attr("x-id");

  if (!id_atributo_tipo_valor || !id_atributo_tipo_valor.length) return;

  if (!id_pys || !id_pys.length){
    swal("Selecciona el Producto/Servicio!", "", "error")
  }
  else if (!id_vivero || !id_vivero.length){
    swal("Selecciona el Vivero!", "", "error")
  }
  else if (!precio || !precio.length || parseInt(precio) <= 0){
    swal("Ingresa el Precio!", "", "error")
  }
  else{
    $(btn).prop("disabled", true);

    $.ajax({
      url: "data_ver_inventario.php",
      type: "POST",
      data: {
        consulta: "guardar_precio_vivero_pys",
        id_atributo_tipo_valor: id_atributo_tipo_valor,
        id_vivero: id_vivero,
        id_pys: id_pys,
        tipo: tipo,
        precio: precio
      },
      success: function (x) {
        console.log(x);
        
        if (x.includes("success")){
          $("#input-vivero-precio-valor2").val("");
          $("#select-vivero-valores2").val("default").selectpicker("refresh");

          getTablePreciosViverosPyS(id_atributo_tipo_valor);
        }
        
        $(btn).prop("disabled", false)
      },
      error: function(x){
        $(btn).prop("disabled", false)
      }
    });
  }
}

function eliminarPrecioExtraPyS(id, id_atributo_tipo_valor) {
  swal("Estás seguro/a de eliminar este Precio?", "", {
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
          beforeSend: function () {},
          url: "data_ver_inventario.php",
          type: "POST",
          data: {
            consulta: "eliminar_precio_pys",
            id: id
          },
          success: function (x) {
            if (x.trim() == "success") {
              getTablePreciosViverosPyS(id_atributo_tipo_valor)
            } else {
              swal(
                "Ocurrió un error!",
                x,
                "error"
              );
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

function readFoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();

    reader.onload = function (e) {
      $('#verificar-subida-logo').attr('src', e.target.result);
      $("#verificar-subida-logo").cropper("destroy");
      $("#verificar-subida-logo").cropper({
        dragMode: "move",
        minContainerWidth: 300,
        minContainerHeight: 300,
        minCanvasWidth: 250,
        minCanvasHeight: 250,
        minCropBoxWidth: 250,
        minCropBoxHeight: 250,
        cropBoxResizable: false,
        ready: function () {
          //$(input).prev().cropper("setCropBoxData", { width: 250, height: 200 });
        },
      });

      $("#modalUploadLogo").modal("show");
      $("#btn-subir-logo").unbind("click");

      $("#btn-subir-logo").on("click", function (e) {
        e.preventDefault();

        if ($(input).prop('files')[0]) {
          let canvas = $("#verificar-subida-logo").data("cropper").getCroppedCanvas({
            fillColor: "#ffffff",
            width: 250,
            height: 250,
          }).toDataURL();
          
          $("#modalUploadLogo").modal("hide");
          $.ajax({
            type: "POST",
            url: "data_ver_inventario.php",
            data: { 
              consulta: "subir_imagen",
              data: canvas,
              tab: currentTab,
              id: $(input).attr("x-id")
            },
            success: function (data) {
              if (data.trim() == "success") {
                busca_productos(currentTab)
              } 
              else {
                swal("Ocurrió un error subir la Imagen", data, "error");
              }
            },
          });
        }
      });
    }
    reader.readAsDataURL(input.files[0]);
  }
}
