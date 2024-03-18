const logoPrintImg = "dist/img/roel.jpg";

var globals = {
  logoPrintImg: logoPrintImg,
  printHeader: `
    <div class="row">
        <div class="col text-center">
            <img src='${logoPrintImg}' class="logo-print"></img>
            <address style='font-size:12px !important;padding-top:3px;padding-bottom:10px;'>
            Paradero 7 de San Pedro<br> 
            Quillota, Valparaíso<br>
            Tel.: +56 944 988 254<br>
            <p>E-mail: ventas@roelplant.cl</p>
            </address>
        </div>
    </div>
    `,
  printHeaderSimple: `
    <div align='center'>
        <img src='${logoPrintImg}' class="logo-print"></img>
        <address style='font-size:10px;'>
            <p>ventas@roelplant.cl</p>
        </address>
    </div><br><br>`,
};

async function loadDatosEmpresaPrint() {
  let valor = null;
  await $.ajax({
    beforeSend: function () {},
    url: "data_ver_integracion.php",
    type: "POST",
    data: { consulta: "load_datos_empresa_print" },
    success: function (x) {
      //console.log(x);
      if (x.length) {
        try {
          const data = JSON.parse(x);
          if (data && data.rut) {
            valor = data;
          }
        } catch (error) {
          valor = { error: error };
        }
      } else {
        valor = {
          error:
            "Para poder generar Facturas/NC/ND se deben completar los datos de la Empresa en el módulo Integración.",
        };
      }
    },
    error: function (jqXHR, estado, error) {
      valor = { error: error };
    },
  });
  return valor;
}

function formatearMonto(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function getFoliosDisponibles(
  id_cotizacion,
  rowid,
  tipoDocumento,
  folioRef,
  id_guia
) {
  $(".row-select-folio").html(``);
  $.ajax({
    beforeSend: function () {},
    url: "data_ver_facturacion.php",
    type: "POST",
    data: {
      consulta: "cargar_folios_disponibles",
      tipoDocumento: tipoDocumento,
    },
    success: function (x) {
      getAmbiente();
      console.log(x);
      if (x.trim().length && !x.includes("nohay")) {
        try {
          const data = JSON.parse(x);
          if (data && data.rowid_caf && data.folio) {
            $(".row-select-folio").html(`
              ${
                tipoDocumento == 33
                  ? `<div class="col-12 mb-2">
              <h5>Generar Factura</h5>
              </div>`
                  : tipoDocumento == 56
                  ? `<div class="col-12 mb-2">
              <h5>Anular NC N° ${folioRef} (Emitir Nota de Débito)</h5>
              </div>`
                  : tipoDocumento == 61
                  ? `<div class="col-12 mb-2">
              <h5>Anular Factura N° ${folioRef} (Emitir Nota de Crédito)</h5>
              </div>`
                  : tipoDocumento == 52
                  ? `<div class="col-12 mb-2">
              <h5>Generar Guía Despacho</h5>
              
              </div>`
                  : ""
              }
              <div class="col-6 col-md-4">
                <h5 class="text-primary font-weight-bold">Folio N°: ${data.folio}</h5>
              </div>
              <div class="col-6 col-md-4 col-ultimo-folio pt-2">
                  
              </div>
              <div class="col-12 col-md-4">
                  ${
                    tipoDocumento == 56
                      ? `<button id="btn-generar" class="btn btn-success btn-block"
                      onclick="generarNotaDebito(null, ${data.folio}, ${data.rowid_caf})"><i class="fa fa-arrow-circle-right"></i> GENERAR NOTA</button>`
                      : tipoDocumento == 33
                      ? `<button id="btn-generar" class="btn btn-success btn-block"
                      onclick="generarFactura(${
                        id_cotizacion ? id_cotizacion : "null"
                      }, ${
                          id_guia ? id_guia : "null"
                        }, ${data.folio}, ${data.rowid_caf})"><i class="fa fa-arrow-circle-right"></i> GENERAR FACTURA</button>`
                      : tipoDocumento == 52
                      ? `<button id="btn-generar" class="btn btn-success btn-block"
                      onclick="generarGuiaDespacho(${data.folio}, ${data.rowid_caf})"><i class="fa fa-arrow-circle-right"></i> GENERAR GUÍA</button>`
                      : `<button id="btn-anular" class="btn btn-danger btn-block"
                      onclick="anularFactura(${rowid}, ${folioRef}, ${data.folio}, ${data.rowid_caf})"><i class="fa fa-ban"></i> ANULAR</button>`
                  }
              </div>
          `);
          }
        } catch (error) {}
      } else {
        $(".row-select-folio").html(`
              <div class="col col-md-6">
                <h5 class="text-danger font-weight-bold">No hay folios disponibles cargados</h5>
              </div>
          `);
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function getAmbiente() {
  $(".col-ultimo-folio").html(``);
  $.ajax({
    beforeSend: function () {},
    url: "class_lib/libredte/vendor/sasco/libredte/examples/data_facturacion_dte.php",
    type: "POST",
    data: {
      consulta: "get_ambiente",
    },
    success: function (x) {
      if (x.trim().length) {
        $(".col-ultimo-folio").html(
          `<small class="text-muted">${x}</small>`
        );
      }
    },
    error: function (jqXHR, estado, error) {},
  });
}

function setInputInt(obj) {
  $(obj).on("propertychange input", function (e) {
    this.value = this.value.replace(/\D/g, "");
  });
}

function setInputDecimal(obj) {
  $(obj)
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
}

function validEmail(email) {
  var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(email);
};