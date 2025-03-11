let productosEnPedido = [];
let productoIndex = 0;

let bandejasEnStock = null;
let semillasSelected = [];
$(document).ready(function () {
    bandejasEnStock = null;
    productosEnPedido = [];

    $("input").on("focus", function () {
        if ($(this).hasClass("datepicker") == false) {
            let hola = this;
            let textareaTop = $(hola).offset().top;
            setTimeout(function () {
                $(".modal-content").scrollTop(textareaTop - 80);
            }, 1000);
        }
    });

    $.datepicker.setDefaults($.datepicker.regional["es"]);
    $("#fecha-ingreso-picker")
        .datepicker({
            format: "dd-M-yyyy",
            autoclose: true,
            disableTouchKeyboard: true,
            Readonly: true,
            minDate: 0,
            dateFormat: "dd/mm/yy",
            onSelect: function (dateText, inst) {
                setFechaEntrega();
            },
        })
        .attr("readonly", "readonly");

    $("#input-codigo,#dias_produccion").on("propertychange input", function (e) {
        this.value = this.value.replace(/\D/g, "");
    });


    $("#input-precio")
        .on("keypress", function (evt) {
            let $txtBox = $(this);
            let charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
                return false;
            else {
                let len = $txtBox.val().length;
                let index = $txtBox.val().indexOf(".");
                if (index > 0 && charCode == 46) {
                    return false;
                }
                if (index > 0) {
                    let charAfterdot = len + 1 - index;
                    if (charAfterdot > 3) {
                        return false;
                    }
                }
            }
            return $txtBox; //for chaining
        })
        .on("paste", function (e) {
            return false;
        });

    $("#input-codigo").focus(function () {
        let val = this.value,
            $this = $(this);
        $this.val("");

        setTimeout(function () {
            $this.val(val);
        }, 1);
    });

    loadDataCotizacion();
});


function loadDataCotizacion() {
    const params = new URLSearchParams(window.location.search);
    $(".label-main-cliente").html(``)
    $(".label-main-cliente").removeAttr("x-id")
    if (params.has('id')) {
        const id = params.get('id');
        $("#observaciones_txt").val(`Cotización Nº ${id}`)
        $.post(
            "data_ver_cotizaciones.php",
            { id: id, consulta: "cargar_cotizacion" },
            function (data) {
                if (data) {
                    try {
                        cotizacion = JSON.parse(data)
                        $(".label-main-cliente").html(`Cliente: ${cotizacion.cliente} (${cotizacion.id_cliente})`)
                        $(".label-main-cliente").attr("x-id", cotizacion.id_cliente)
                        if (cotizacion.productos && cotizacion.productos.length > 0) {
                            cotizacion.productos.forEach((producto, index) => {
                                

                                const nombre_producto = `${producto.variedad} (${producto.codigo}) ${producto.especie ? producto.especie + " (Provisto x Cliente)" : ""}`;

                                if ($(".pedido-vacio-msg").length) {
                                    $("#table-pedido tbody").html("");
                                }

                                let celda = `<tr class='tr-edit tr-index-${index}' x-id-tipo='${producto.id_tipo}' x-id-variedad='${producto.id_variedad
                                    }' x-id-especie='${producto.id_especie ? producto.id_especie : ""}'
                                    x-cant-plantas='${producto.cantidad}'
                                    x-cant-bandejas='0'
                                    x-cant-bandejas-nuevas='0'
                                    x-cant-bandejas-usadas='0'
                                    x-tipo-bandeja=''
                                    x-cant-semillas=''
                                    >
                                
                                    <td scope="row">${nombre_producto}</td>
                                    <td>${producto.cantidad}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-center">
                                        <button class='btn btn-secondary btn-sm fa fa-trash' onclick='eliminar_art(this, ${producto.index})'></button>
                                        <button class='btn btn-primary btn-sm fa fa-edit' onclick='modalAgregarProducto(event, {id_variedad: ${producto.id_variedad_real}, id_especie: ${producto.id_especie ? producto.id_especie : null}, id_tipo: ${producto.id_tipo}, cantidad: ${producto.cantidad}, index: ${index}})'></button>
                                    </td>
                                    </tr>`;
                                $("#table-pedido tbody").append(celda);
                            });
                        }
                    } catch (error) {

                    }
                }
            }
        );
    }


}

function loadSemillasPlantinera() {
    const codigo = $("#select_tipo").find("option:selected").attr("x-codigo");

    if (codigo == "S") {
        const id = $("#select_variedad").find("option:selected").val();
        loadSemillasPedido(id, codigo);
    } else if (codigo == "HS") {
        const id = $("#select_especie").find("option:selected").val();
        loadSemillasPedido(id, codigo);
    }
}

function modalAgregarProducto(event, product) {
    event.preventDefault();

    ClearModal();
    clearStock();
    

    if (product) {
        window.selectedVariedad = product.id_variedad;
        window.selectedEspecie = product.id_especie;
        pone_tiposdeproducto(product.id_tipo);
        // $("#select_variedad").val(product.id_variedad).selectpicker("refresh");
        // $("#select_especie").val(product.id_especie).selectpicker("refresh");
        $("#cantidad_plantas").val(product.cantidad);
        $("#modal-agregar-producto").attr("x-editing-index", product.index)
    }
    else{
        window.selectedVariedad = null;
        window.selectedEspecie = null;
        $("#modal-agregar-producto").removeAttr("x-editing-index");
        pone_tiposdeproducto();
    }
    $("#modal-agregar-producto").modal("show");
}

function ClearModal() {
    $("#select_tipo,#select_variedad,#select_especie,#select_bandeja")
        .val("default")
        .selectpicker("refresh");
    let groupFilter = $("#select_variedad");
    groupFilter.selectpicker("val", "");
    groupFilter.find("option").remove();
    groupFilter.selectpicker("refresh");

    let selectEspecie = $("#select_especie");
    selectEspecie.selectpicker("val", "");
    selectEspecie.find("option").remove();
    selectEspecie.selectpicker("refresh");

    let selectSemillas = $("#select_semillas");
    selectSemillas.selectpicker("val", "");
    selectSemillas.find("option").remove();
    selectSemillas.selectpicker("refresh");

    $("#check_plantinera").prop("checked", false);
    $(
        "#cantidad_plantas,#dias_produccion,#cantidad_bandejas,#cantidad_bandejas_usadas,#cantidad_semillas"
    ).val("");
    $("#select-dias").val("0");
    $("#fecha-ingreso-picker,#fecha-entrega-picker").val("DD/MM/YYYY");
    $(".form-especie,.form-semillas").addClass("d-none");
}

function GuardarPedido() {
    const id_cliente = $(".label-main-cliente").first().attr("x-id")

    if (!id_cliente.trim().length) {
        swal("Seleccioná un cliente!", "", "error");
    } else if ($(".pedido-vacio-msg").length) {
        swal(
            "El pedido está vacío!",
            "Agregá algún producto para continuar.",
            "error"
        );
    } else {
        if ($(".tr-edit").length){
            swal("Debes asignar los valores de los productos y eliminar aquellos que no corresponden al Pedido", "", "error");
            return;
        }

        $("#btn_guardarpedido").prop("disabled", true);

        if (productosEnPedido.length) {
            let jsonarray = JSON.stringify(productosEnPedido);
            let observaciones = $("#observaciones_txt").val().trim();

            let id_usuario;
            $.get(
                "get_session_variable.php",
                { requested: "id_usuario" },
                function (data) {
                    if (data.trim().length) {
                        id_usuario = data.trim();
                    }
                }
            );

            $.ajax({
                url: "guarda_pedido.php",
                type: "POST",
                data: {
                    id_cliente: id_cliente,
                    jsonarray: jsonarray,
                    observaciones: observaciones,
                    id_usuario: id_usuario && id_usuario.length ? id_usuario : "1",
                },
                success: function (x) {
                    console.log(x);
                    if (x.trim().includes("pedidonum")) {
                        let idPedido = x.trim().includes("pedidonum:")
                            ? x.trim().replace("pedidonum:", "")
                            : null;
                        showPedidoExitosoDialog();
                        $("#ModalAdminPedido").attr("x-id-pedido", idPedido);
                        $("#btn_guardarpedido").prop("disabled", false);
                        ClearPedido(true)
                    } else {
                        $("#btn_guardarpedido").prop("disabled", false);
                        swal("Ocurrió un error!", x, "error");
                    }
                },
                error: function (jqXHR, estado, error) {
                    swal(
                        "Ocurrió un error al guardar el Pedido",
                        error.toString(),
                        "error"
                    );
                    $("#btn_guardarpedido").prop("disabled", false);
                },
            });
        } else {
            swal("Debes agregar algún producto al pedido!", "", "error");
        }
    }
}



function modalAgregarVariedad() {
    if ($("#select_tipo").val()) {
        $("#ModalAgregarVariedad").attr("x-id_tipo", $("#select_tipo").val());
        $("#input-nombre,#input-precio,#input-codigo").val("");
        const codigo = $("#select_tipo").find("option:selected").attr("x-codigo");

        if (codigo == "E" || codigo == "S") {
            $(".form-dias-produccion-variedad").removeClass("d-none");
        } else if (codigo == "HE" || codigo == "HS") {
            $(".form-dias-produccion-variedad").addClass("d-none");
        }
        $("#ModalAgregarVariedad").attr("x-codigo-tipo", codigo);
        $(".label-codigo").html(`[${codigo}]`);
        cargarUltimaVariedad();
        $("#ModalAgregarVariedad").modal("show");
        $("#input-nombre").focus();
    } else {
        swal("Debes elegir un Tipo de Producto!", "", "error");
    }
}

function setFechaEntrega() {
    let cant_dias = $("#dias_produccion").val().trim();

    const fecha_ingreso = $("#fecha-ingreso-picker").val().trim();

    if (cant_dias.length && !fecha_ingreso.includes("DD/MM")) {
        const fechaEntrega = moment(fecha_ingreso, "DD/MM/YYYY").add(
            cant_dias,
            "days"
        );

        $("#fecha-entrega-picker").val(fechaEntrega.format("DD/MM/YYYY"));
    } else {
        $("#fecha-entrega-picker").val("DD/MM/YYYY");
    }
}

function guardarVariedad() {
    const nombre = $("#input-nombre").val().trim().replace(/\s+/g, " ");
    const codigo = $("#input-codigo").val().trim().replace(/\s+/g, "");
    const precio = $("#input-precio").val().trim();
    const codigo_tipo = $("#ModalAgregarVariedad").attr("x-codigo-tipo");
    const dias_produccion = $("#dias-produccion-variedad")
        .val()
        .trim()
        .replace(/\s+/g, "");

    const id_tipo = $("#select_tipo").val();
    if (nombre.length < 3) {
        swal("La Variedad debe tener un nombre de al menos 3 letras", "", "error");
    } else if (!codigo.length) {
        swal("Ingresa un código para la Variedad", "", "error");
    } else if (
        (codigo_tipo == "E" || codigo_tipo == "S") &&
        (!dias_produccion.length ||
            isNaN(dias_produccion) ||
            parseInt(dias_produccion) < 0 ||
            parseInt(dias_produccion) > 365)
    ) {
        swal(
            "Ingresa la cantidad de días que permanecerá el producto en Producción",
            "",
            "error"
        );
    } else {
        cerrarModalAgregarVariedad();
        $.ajax({
            url: "data_ver_variedades.php",
            type: "POST",
            data: {
                consulta: "agregar_variedad",
                nombre: nombre,
                id_tipo: id_tipo,
                codigo: codigo,
                precio: precio,
                dias_produccion:
                    codigo_tipo == "E" || codigo_tipo == "S" ? dias_produccion : null,
            },
            success: function (x) {
                if (x.trim() == "success") {
                    swal("Agregaste la Variedad correctamente!", "", "success");
                    carga_variedades(id_tipo);
                } else {
                    swal("Ocurrió un error", x, "error");
                }
            },
            error: function (jqXHR, estado, error) {
                swal("Ocurrió un error", error.toString(), "error");
                $("#ModalAgregarCliente").modal("show");
            },
        });
    }
}

function cargarUltimaVariedad() {
    const id_tipo = $("#select_tipo").val();
    $.ajax({
        url: "data_cargar_pedido.php",
        type: "POST",
        data: {
            consulta: "cargar_ultima_variedad",
            id_tipo: id_tipo,
        },
        success: function (x) {
            if (x.includes("success") && !$("#input-codigo").val().length) {
                $("#input-codigo").val(x.replace("success:", ""));
            }
        },
    });
}

function cargaDiasProduccion(id_producto, tipo) {
    $.ajax({
        url: "data_ver_variedades.php",
        type: "POST",
        data: {
            consulta: "cargar_dias_produccion",
            id_producto: id_producto,
            tipo: tipo,
        },
        success: function (x) {
            if (x.includes("dias:")) {
                const dias = x.replace("dias:", "");
                $("#dias_produccion").val(dias);
                if (
                    dias == "30" ||
                    dias == "60" ||
                    dias == "90" ||
                    dias == "120" ||
                    dias == "150" ||
                    dias == "180"
                ) {
                    $("#select-dias").val(dias);
                }
            }
        },
    });
}

function cerrarModalAgregarVariedad() {
    $("#ModalAgregarVariedad").modal("hide");
}

function cerrarModal(id) {
    $("#" + id).modal("hide");
}

function pone_tiposdeproducto(id_tipo) {
    $.ajax({
        beforeSend: function () {
            $("#select_tipo").html("Cargando productos...");
        },
        url: "data_ver_tipos.php",
        type: "POST",
        data: { consulta: "busca_tipos_select" },
        success: function (x) {
            $(".selectpicker").selectpicker();
            $("#select_tipo").html(x).selectpicker("refresh");
            if (id_tipo) {
                setTimeout(()=>{
                    $("#select_tipo").val(id_tipo).selectpicker("refresh").trigger("changed.bs.select");
                },300)
                
            }
            $("#select_tipo").on(
                "changed.bs.select",
                function (e, clickedIndex, newValue, oldValue) {
                    const codigo = $("#select_tipo")
                        .find("option:selected")
                        .attr("x-codigo")
                        .trim();
                    if (codigo == "HS" || codigo == "HE") {
                        $(".form-especie").removeClass("d-none");
                    } else {
                        $(".form-especie").addClass("d-none");
                    }
                    carga_variedades(this.value);
                    let selectEspecie = $("#select_especie");
                    selectEspecie.selectpicker("val", "");
                    selectEspecie.find("option").remove();
                    selectEspecie.selectpicker("refresh");
                    carga_especies(this.value);

                    let selectSemillas = $("#select_semillas");
                    selectSemillas.selectpicker("val", "");
                    selectSemillas.find("option").remove();
                    selectSemillas.selectpicker("refresh");

                    clearStock();
                    if (codigo == "E" || codigo == "HE") {
                        $(".form-semillas").addClass("d-none");
                        $("#select_bandeja").html(`
                <option value="128">128</option>
                <option value="162">162</option>
                <option value="105">105</option>
              `);
                    } else if (codigo == "S" || codigo == "HS") {
                        $(".form-semillas").removeClass("d-none");

                        $("#select_bandeja").html(`
                <option value="200">200</option>
                <option value="105">105</option>
                <option value="50">50</option>
              `);
                    } else {
                        $("#select_bandeja").html(`
                <option value="200">200</option>
                <option value="162">162</option>
                <option value="128">128</option>
                <option value="105">105</option>
                <option value="50">50</option>
              `);
                    }

                    $("#select_bandeja").on(
                        "changed.bs.select",
                        function (e, clickedIndex, newValue, oldValue) {
                            const tipo = $("#select_bandeja").find("option:selected").val();

                            cargar_stock(tipo);
                        }
                    );
                    $("#select_bandeja").selectpicker("refresh");
                }
            );
        },
        error: function (jqXHR, estado, error) { },
    });
}

function clearStock() {
    bandejasEnStock = null;
    $(".label-nuevas,.label-usadas")
        .addClass("d-none")
        .removeClass("text-danger")
        .removeClass("text-success");
    $(".label-stock-usadas-cant,.label-stock-nuevas-cant").html("");
}

function cargar_stock(tipo) {
    clearStock();
    $.ajax({
        url: "data_ver_stock_bandejas.php",
        type: "POST",
        data: {
            consulta: "cargar_stock_bandejas",
            tipo: tipo,
        },
        success: function (x) {
            if (x.length) {
                try {
                    const data = JSON.parse(x);
                    const { nuevas, usadas } = data;
                    $(".label-stock-nuevas-cant").html(nuevas);
                    $(".label-stock-usadas-cant").html(usadas);
                    bandejasEnStock = {
                        nuevas: nuevas,
                        usadas: usadas,
                    };

                    $(".label-nuevas")
                        .addClass(parseInt(nuevas) > 0 ? "text-success" : "text-danger")
                        .removeClass("d-none");
                    $(".label-usadas")
                        .addClass(parseInt(usadas) > 0 ? "text-success" : "text-danger")
                        .removeClass("d-none");
                } catch (error) {
                    swal("Error al obtener el Stock de Bandejas", "", "error");
                    $(".label-stock-nuevas-cant").html("");
                    $(".label-stock-usadas-cant").html("");
                    bandejasEnStock = null;
                }
            } else {
                swal("Error al obtener el Stock de Bandejas", "", "error");
                $(".label-stock-nuevas-cant").html("");
                $(".label-stock-usadas-cant").html("");
                bandejasEnStock = null;
            }
        },
        error: function (jqXHR, estado, error) {
            bandejasEnStock = null;
        },
    });
}

function toggleSelection(objeto) {
    $(".selected2").removeClass("selected2");
    let tr = $(objeto);
    tr.addClass("selected2");
}

function carga_variedades(id_tipo) {
    $.ajax({
        beforeSend: function () {
            $("#select_variedad").html("Cargando variedades...");
        },
        url: "data_ver_variedades.php",
        type: "POST",
        data: { consulta: "busca_variedades_select", id_tipo: id_tipo },
        success: function (x) {
            $("#select_variedad").val("default").selectpicker("refresh");
            $("#select_variedad").html(x).selectpicker("refresh");

            if (window.selectedVariedad){
                setTimeout(()=>{
                    $("#select_variedad").val(window.selectedVariedad).selectpicker("refresh").trigger("changed.bs.select");
                },300)
            }
            $("#select_variedad").on(
                "changed.bs.select",
                function (e, clickedIndex, newValue, oldValue) {
                    if (!$("#modal-agregar-producto").attr("x-editing-index")){
                        $("#modal-agregar-producto").find("#cantidad_plantas").val("");
                    }
                    
                    const codigo = $("#select_tipo")
                        .find("option:selected")
                        .attr("x-codigo");

                    if (codigo == "E" || codigo == "S") {
                        cargaDiasProduccion(this.value, "variedad");
                    }

                    const nombre = $("#select_variedad").find("option:selected").text();

                    if (nombre.includes("BANDEJA 128")) {
                        $("#select_bandeja").selectpicker("val", "128");
                    } else if (nombre.includes("BANDEJA 162")) {
                        $("#select_bandeja").selectpicker("val", "162");
                    } else if (nombre.includes("BANDEJA 200")) {
                        $("#select_bandeja").selectpicker("val", "200");
                    }

                    if (codigo == "S") {
                        loadSemillasSelect(this.value, "S");
                    }
                }
            );
        },
        error: function (jqXHR, estado, error) { },
    });
}

function carga_especies(id_tipo) {
    $.ajax({
        beforeSend: function () {
            $("#select_especie").html("Cargando especies...");
        },
        url: "data_ver_variedades.php",
        type: "POST",
        data: { consulta: "busca_especies_select", id_tipo: id_tipo },
        success: function (x) {
            $("#select_especie").val("default").selectpicker("refresh");
            $("#select_especie").html(x).selectpicker("refresh");
            if (window.selectedEspecie){
                setTimeout(()=>{
                    $("#select_especie").val(window.selectedEspecie).selectpicker("refresh").trigger("changed.bs.select");
                },300)
            }
            $("#select_especie").on(
                "changed.bs.select",
                function (e, clickedIndex, newValue, oldValue) {
                    if (!$("#modal-agregar-producto").attr("x-editing-index")){
                        $("#modal-agregar-producto").find("#cantidad_plantas").val("");
                    }
                    const codigo = $("#select_tipo")
                        .find("option:selected")
                        .attr("x-codigo");

                    if (codigo == "HE" || codigo == "HS") {
                        cargaDiasProduccion(this.value, "especie");
                    }
                    if (codigo == "HS") {
                        loadSemillasSelect(this.value, "HS");
                    }
                }
            );
        },
        error: function (jqXHR, estado, error) { },
    });
}

function addToPedido() {
    const producto = $("#select_tipo")
        .find("option:selected")
        .attr("x-nombre")
        .trim();
    const variedad = $("#select_variedad :selected").text();
    const especie = $("#select_especie").val()
        ? $("#select_especie").find("option:selected").attr("x-nombre").trim()
        : null;

    const id_especie = $("#select_especie").val();
    const cantidad_plantas = $("#cantidad_plantas").val().trim();
    const cantidad_bandejas_nuevas = $("#cantidad_bandejas").val().trim();
    const cantidad_bandejas_usadas = $("#cantidad_bandejas_usadas").val().trim();
    const fecha_ingreso = $("#fecha-ingreso-picker").val();
    const fecha_entrega = $("#fecha-entrega-picker").val();
    const bandeja = $("#select_bandeja").find("option:selected").val();
    const codigo = $("#select_tipo").find("option:selected").attr("x-codigo");
    const id_stock_semillas = $("#select_semillas").find("option:selected").val();

    //
    let semillas = [];
    if (codigo == "HS" || codigo == "S") {
        $(".cantidad_semillas").each(function (i, e) {
            const val = $(e).val().trim();
            const max = $(e).attr("max");

            if (!val.length) {
                swal("Los campos de Cantidad de Semillas no pueden quedar vacíos", "", "error")
                return;
            }

            if (val.length && (parseInt(val) > parseInt(max))) {
                swal("Ingresaste una cantidad superior a la disponible!", "", "error")
                return;
            }

            semillas.push({
                nombre: $(e).parent().find(".nombre-semillas").text(),
                id_stock_semillas: $(e).attr("x-id"),
                cantidad: parseInt(val),
                codigo: $(e).attr("x-codigo")
            });
        });
    }

    //

    if (
        !cantidad_plantas ||
        !cantidad_plantas.length ||
        isNaN(cantidad_plantas) ||
        parseInt(cantidad_plantas) < 1
    ) {
        swal("Ingresa la cantidad de Plantas", "", "error");
    } else if (!producto.length) {
        swal("Debes elegir un producto!", "", "error");
    } else if (!variedad.length) {
        swal("Debes elegir una variedad de producto!", "", "error");
    } else if (
        (codigo == "HS" || codigo == "HE") &&
        !$("#select_especie").val()
    ) {
        swal("Debes elegir una Especie!", "", "error");
    } else if (
        (codigo == "HS" || codigo == "S") &&
        (!id_stock_semillas ||
            !id_stock_semillas.length ||
            id_stock_semillas == "0")
    ) {
        swal("Debes elegir las Semillas que se van a utilizar!", "", "error");
    } else if (cantidad_plantas < 0) {
        swal("La cantidad de plantas no puede ser negativa!", "", "error");
    } else if (!bandeja || !bandeja.length) {
        swal("Elige un tipo de Bandeja!", "", "error");
    } else if (
        (!cantidad_bandejas_nuevas.length && !cantidad_bandejas_usadas.length) ||
        (isNaN(cantidad_bandejas_nuevas) && isNaN(cantidad_bandejas_usadas)) ||
        (parseInt(cantidad_bandejas_nuevas) < 1 &&
            parseInt(cantidad_bandejas_usadas) < 1)
    ) {
        swal("Ingresa la cantidad de Bandejas", "", "error");
    } else if (parseInt(cantidad_plantas) < parseInt(bandeja)) {
        swal(
            "La cantidad de plantas no puede ser menor a la capacidad de UNA Bandeja!",
            "",
            "error"
        );
    } else if (fecha_ingreso.includes("DD/")) {
        swal("Elige una fecha de ingreso!", "", "error");
    } else if (fecha_entrega.includes("DD/")) {
        swal("Debes asignar los días de Producción!", "", "error");
    } else {
        let oldIndex = null
        if ($("#modal-agregar-producto").attr("x-editing-index")){
            oldIndex = parseInt($("#modal-agregar-producto").attr("x-editing-index"))
        }
        $("#modal-agregar-producto").modal("hide");
        funcAddToPedido({
            oldIndex,
            index: productoIndex++,
            tipo: producto,
            id_tipo: $("#select_tipo").find("option:selected").val(),
            variedad: variedad,
            id_variedad: $("#select_variedad").find("option:selected").val(),
            cantidad_plantas: cantidad_plantas,
            fecha_ingreso: fecha_ingreso.includes("DD/") ? "-" : fecha_ingreso,
            fecha_entrega: fecha_entrega.includes("DD/") ? "-" : fecha_entrega,
            codigo: codigo,
            especie: especie,
            id_especie: id_especie,
            tipo_bandeja: bandeja,
            cantidad_bandejas:
                cantidad_bandejas_nuevas.length && cantidad_bandejas_usadas.length
                    ? parseInt(cantidad_bandejas_nuevas) +
                    parseInt(cantidad_bandejas_usadas)
                    : !cantidad_bandejas_nuevas.length
                        ? cantidad_bandejas_usadas
                        : !cantidad_bandejas_usadas.length
                            ? cantidad_bandejas_nuevas
                            : 0,
            cantidad_bandejas_nuevas: cantidad_bandejas_nuevas.length
                ? cantidad_bandejas_nuevas
                : 0,
            cantidad_bandejas_usadas: cantidad_bandejas_usadas.length
                ? cantidad_bandejas_usadas
                : 0,
            semillas: semillas,
            cantidad_semillas: $(".label-total-semillas").text()
        });
    }
}

function funcAddToPedido(producto) {
    if (document.location.href.includes("cargar_pedido.php")) {
        productosEnPedido.push(producto);

        nombre_producto = `${producto.variedad} ${producto.especie ? producto.especie + " (Provisto x Cliente)" : ""
            }`;

        if ($(".pedido-vacio-msg").length) {
            $("#table-pedido tbody").html("");
        }

        let celda = `<tr x-id-tipo='${producto.id_tipo}' x-id-variedad='${producto.id_variedad
            }' x-id-especie='${producto.id_especie ? producto.id_especie : ""}'
    x-cant-plantas='${producto.cantidad_plantas}'
    x-cant-bandejas='${producto.cantidad_bandejas}'
    x-cant-bandejas-nuevas='${producto.cantidad_bandejas_nuevas}'
    x-cant-bandejas-usadas='${producto.cantidad_bandejas_usadas}'
    x-tipo-bandeja='${producto.tipo_bandeja}'
    x-cant-semillas='${producto.cantidad_semillas}'
    >
  
      <td scope="row">${nombre_producto}</td>
      <td>${producto.cantidad_plantas} ${producto.cantidad_semillas.length
                ? " (" + producto.cantidad_semillas + " Semillas)"
                : ""
            }</td>
      <td>${producto.cantidad_bandejas} (x${producto.tipo_bandeja} ALV.)</td>
      <td>${producto.fecha_ingreso}</td>
      <td>${producto.fecha_entrega}</td>
      <td class="text-center"><button class='removeme btn btn-xs fa fa-trash' style='font-size:1.7em' onclick='eliminar_art(this, ${producto.index})'></button></td>
      </tr>`;
        $("#table-pedido tbody").append(celda);

        if (producto.oldIndex != undefined && producto.oldIndex != null){
            $(`.tr-index-${producto.oldIndex}`).remove()
        }
    } else if (document.location.href.includes("ver_pedidos.php")) {
        const {
            cantidad_bandejas,
            cantidad_bandejas_nuevas,
            cantidad_bandejas_usadas,
            cantidad_plantas,
            id_variedad,
            fecha_ingreso,
            fecha_entrega,
            id_especie,
            tipo_bandeja,
        } = producto;
        const id_pedido = $("#modal-modificar-pedido").attr("x-id-pedido");
        cerrarModal("modal-agregar-producto");

        $.ajax({
            url: "data_cargar_pedido.php",
            type: "POST",
            data: {
                consulta: "agregar_producto_al_pedido",
                id_variedad: id_variedad,
                cantidad_bandejas: cantidad_bandejas,
                cantidad_bandejas_nuevas: cantidad_bandejas_nuevas,
                cantidad_bandejas_usadas: cantidad_bandejas_usadas,
                cantidad_plantas: cantidad_plantas,
                fecha_entrega: fecha_entrega,
                fecha_ingreso: fecha_ingreso,
                tipo_bandeja: tipo_bandeja,
                id_especie: id_especie,
                id_pedido: id_pedido,
            },
            success: function (x) {
                if (x.includes("success")) {
                    swal("Agregaste el Producto al Pedido correctamente!", "", "success");
                    modalModificarPedido(id_pedido, "");
                } else {
                    swal("Ocurrió un error al agregar el Producto", x, error);
                }
            },
        });
    }
}

function eliminar_art(btn, i) {
    swal("¿ELIMINAR este Producto del Pedido?", "", {
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
                $(btn).parent().parent().remove();
                productosEnPedido = productosEnPedido.filter(function (e, index, arr) {
                    return e.index != i;
                });

                if ($("#table-pedido > tbody > tr").length < 1) {
                    $("#table-pedido > tbody").append(`
              <tr class="pedido-vacio-msg">
                <th scope="row" colspan="6" class="text-center"><span class="text-muted">El Pedido está vacío</span></th>
              </tr>
            `);
                }

            default:
                break;
        }
    });
}

function showPedidoExitosoDialog() {
    let modal = document.getElementById("ModalAdminPedido");
    modal.style.display = "block";
}

function ClearPedido(clear) {
    if (!clear) {
        document.getElementById("ModalAdminPedido").style.display = "none";
        productosEnPedido = [];
    }

    $("#table-pedido > tbody").html(`
              <tr class="pedido-vacio-msg">
                <th scope="row" colspan="6" class="text-center"><span class="text-muted">El Pedido está vacío</span></th>
              </tr>
  `);
    $("#select_cliente").val("").trigger("change");
    $("#observaciones_txt").val("");
    $("#btn_guardarpedido").prop("disabled", false);
}

function print_Cliente(tipo) {
    if (tipo == 1) {
        func_printCliente1();
        document.getElementById("ocultar").style.display = "none";
        document.getElementById("miVentana").style.display = "block";
    } else {
        document.getElementById("ocultar").style.display = "block";
        document.getElementById("miVentana").style.display = "none";
        $("#miVentana").html("");
    }
}

function func_printCliente1() {
    $("#miVentana").html(globals.printHeader);

    let date = new Date();
    let dateStr =
        ("00" + date.getDate()).slice(-2) +
        "/" +
        ("00" + (date.getMonth() + 1)).slice(-2) +
        "/" +
        date.getFullYear() +
        " " +
        ("00" + date.getHours()).slice(-2) +
        ":" +
        ("00" + date.getMinutes()).slice(-2);

    let cliente = $("#select_cliente").find("option:selected").text();
    let idPedido = $("#ModalAdminPedido").attr("x-id-pedido");
    $("#miVentana").append("<h4>Nº Pedido: " + idPedido + "</h4>");
    $("#miVentana").append("<h4>Cliente: " + cliente + "</h4>");
    $("#miVentana").append("<h4>Fecha: " + dateStr + "</h4>");

    let tabla = `
  <table class="table-cliente table table-responsive w-100 d-block d-md-table">
    <thead>
    <tr>
      <th>Producto</th>
      <th class="text-center">Plantas/Bandejas</th>
      <th class="text-center">Fecha Ingreso</th>
      <th class="text-center">Fecha Entrega</th>
    </tr>
    </thead>

    <tbody>
    </tbody>
   </table>
  `;

    $("#miVentana").append(tabla);

    productosEnPedido.forEach((producto) => {
        const nombre_producto = `${producto.variedad} ${producto.especie ? producto.especie + " (Provisto x Cliente)" : ""
            }`;
        $(".table-cliente > tbody").append(`
        <tr>
        <td>${nombre_producto}</td>
        <td class="text-center">${producto.cantidad_plantas}<br>${producto.cantidad_bandejas}</td>
        <td class="text-center">${producto.fecha_ingreso}</td>
        <td class="text-center">${producto.fecha_entrega}</td>
        </tr>
      `);
    });

    $("#miVentana").find("tr").css({ "font-size": "23px" });
    $("#miVentana").find("#table_total").html("");
    setTimeout("window.print();print_Cliente(2)", 500);
}

function print_Pedido(tipo) {
    if (tipo == 1) {
        func_printPedido2();
        document.getElementById("ocultar").style.display = "none";
        document.getElementById("miVentana").style.display = "block";
    } else {
        document.getElementById("ocultar").style.display = "block";
        document.getElementById("miVentana").style.display = "none";
        $("#miVentana").html("");
    }
}

function AgregarPagoModal() {
    let cliente = $("#select_cliente").find("option:selected").text();
    if (cliente.length == 0) {
        swal("ERROR", "Debes elegir un cliente antes de cargar un pago!", "");
    } else {
        $("#input_pago,#input_concepto").val("");
        $("#ModalPagos").modal("show");
        $("#input_pago").focus();
    }
}

function cerrarModalPagos() {
    $("#ModalPagos").modal("hide");
}

function agregarPago() {
    const monto = $("#input_pago").val().trim();
    const concepto = $("#input_concepto").val().trim();
    const id_cliente = $("#ModalPagos").attr("x-id-cliente");
    if (parseInt(monto) < 0) {
        swal("El pago no puede ser negativo!", "", "error");
    } else {
        if (!isNaN(monto) && parseInt(monto) > 0) {
            $("#monto_pago").html(parseFloat(monto).toFixed(2).toString());
        }
        $("#ModalPagos").attr("x-monto", monto);
        $("#ModalPagos").attr("x-concepto", concepto);
        cerrarModalPagos();
    }
}

function modalAgregarEspecie() {
    if ($("#select_tipo").val()) {
        $("#ModalAgregarEspecie").attr(
            "x-id-tipo",
            $("#select_tipo").find("option:selected").val()
        );

        const codigo = $("#select_tipo").find("option:selected").attr("x-codigo");

        $("#ModalAgregarEspecie").attr("x-codigo-tipo", codigo);

        $("#input-nombre-especie").val("");
        $("#ModalAgregarEspecie").modal("show");
        $("#input-nombre-especie").focus();
    } else {
        swal("Debes elegir un Tipo de Producto!", "", "error");
    }
}

function guardarEspecie() {
    const nombre = $("#input-nombre-especie").val().trim().replace(/\s+/g, " ");
    const id_tipo = $("#ModalAgregarEspecie").attr("x-id-tipo");
    const codigo_tipo = $("#ModalAgregarEspecie").attr("x-codigo-tipo");
    const dias_produccion = $("#dias-produccion-especie")
        .val()
        .trim()
        .replace(/\s+/g, "");

    if (nombre.length < 3) {
        swal("La Especie debe tener un nombre de al menos 3 letras", "", "error");
    } else if (
        (codigo_tipo == "HE" || codigo_tipo == "HS") &&
        (!dias_produccion.length ||
            isNaN(dias_produccion) ||
            parseInt(dias_produccion) < 0 ||
            parseInt(dias_produccion) > 365)
    ) {
        swal(
            "Ingresa la cantidad de días que permanecerá el producto en Producción",
            "",
            "error"
        );
    } else {
        $("#ModalAgregarEspecie").modal("hide");
        $.ajax({
            url: "data_ver_variedades.php",
            type: "POST",
            data: {
                consulta: "agregar_especie",
                nombre: nombre,
                id_tipo: id_tipo,
                dias_produccion:
                    codigo_tipo == "HE" || codigo_tipo == "HS" ? dias_produccion : null,
            },
            success: function (x) {
                if (x.trim() == "success") {
                    swal("Agregaste la Especie correctamente!", "", "success");
                    carga_especies(id_tipo);
                } else {
                    swal("Ocurrió un error!", x, "error");
                    $("#ModalAgregarEspecie").modal("show");
                }
            },
            error: function (jqXHR, estado, error) {
                swal("Ocurrió un error", error.toString(), "error");
                $("#ModalAgregarEspecie").modal("show");
            },
        });
    }
}

function loadSemillasPedido(id, tipo) {
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
            plantinera: $("#check_plantinera").is(":checked") ? 1 : 0,
        },
        success: function (data) {
            $("#select_semillas").html(data).val("default").selectpicker("refresh");
            $("#select_semillas").on(
                "changed.bs.select",
                function (e, clickedIndex, newValue, oldValue) {
                    $(".cantidad-semillas-container").html("");
                    $(".label-total-semillas").html("0");
                    $(".total-semillas").addClass("d-none")
                    let selected = $(this).find("option:selected");
                    selected.each(function (i, e) {
                        $(".cantidad-semillas-container").append(`
            <div class="col-md-4 mt-2">
            <label for="cantidad_semillas" class="control-label">Cantidad Sobre ${i + 1}:</label>
            <input x-codigo="${$(this).attr("x-codigo")}" style="font-weight: bold; font-size: 1.2em; color: black !important;" type="number" min="0"
              x-id="${$(this).val()}" max="${$(this).attr("x-cantidad")}" maxlength="20" step="1" placeholder="Cantidad Semillas"
              class="form-control text-right cantidad_semillas" onkeyup="checkCantidadSemillas(this)" onpaste="this.onkeyup()">
            <span class='text-primary nombre-semillas' style='font-size:11px'>${$(this).text()}</span>
            </div>
            `)
                    });
                }
            );
        },
    });
}

function checkCantidadSemillas(obj) {
    const max = $(obj).attr("max");
    const value = $(obj).val().trim();

    if (value.length && (parseInt(value) > parseInt(max))) {
        $(obj).val(max)
    }
    let total = 0;
    $(".cantidad_semillas").each(function (i, e) {
        const val = $(e).val().trim();
        if (val.length && parseInt(val) > 0) {
            total += parseInt(val);
        }
        $(".label-total-semillas").html(total);
        $(".total-semillas").removeClass("d-none")
    });
}