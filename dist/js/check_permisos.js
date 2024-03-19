$(document).ready( function () {
  func_check();
  $("#contenedor_panel").html(`<a href="#"><i class="fa fa-bars"></i> <span>Panel de Control</span> <i class="fa fa-angle-left pull-right"></i></a> 
                          <ul class="treeview-menu menu-open" style="display: block;"> 
                            
                          </ul>`);
});

async function func_check() {
  let id_usuario;
  
  await $.get(
    "get_session_variable.php",
    { requested: "id_usuario" },
    function (data) {
      if (data.trim().length) {
        id_usuario = data.trim();
      }
    }
  );

  let permisos;
  await $.get(
    "get_session_variable.php",
    { requested: "permisos" },
    function (data) {
      if (data.trim().length) {
        permisos = data.trim();
        
      }
    }
  );

  $("#contenedor_modulos").html("");
  if (id_usuario == "1") {
    pone_cotizaciones();
    pone_facturacion();
    pone_estadisticas();
    pone_situacion();
    pone_situacion_especial();
    pone_clientes_sidebar();
    pone_transportistas();
    pone_usuarios();
    pone_integracion();
    pone_comisiones();
    pone_compras();
  } else {
    if (permisos.length) {

      let array = permisos.split(",");
      for (let i = 0; i < array.length; i++) {
        if (array[i] == "cotizaciones") {
          pone_cotizaciones();
        } else if (array[i] == "facturacion") {
          pone_facturacion();
        }
        else if (array[i] == "integracion") {
          pone_integracion();
        }
        else if (array[i] == "situacion") {
          pone_situacion();
          pone_situacion_especial();
        }
        else if (array[i] == "estadisticaserp") {
          pone_estadisticas();
        }
        else if (array[i] == "compras") {
          pone_compras();
        }
      }
    } else {
      window.location.href = "inicio.php";
    }
  }
}

function pone_cotizaciones() {
  if (isHome()) {
    $(".col-cotizaciones")
      .html(
        `
        <a href="ver_cotizaciones.php">
          <div class="small-box" style="background-color:#0080CD"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Cotizaciones</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-usd"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Cotizaciones <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_cotizaciones.php"><i class="fa fa-arrow-circle-right"></i> Cotizaciones</a></li>'
  );
}

function pone_compras() {
  if (isHome()) {
    $(".col-compras")
      .html(
        `
        <a href="ver_compras.php">
          <div class="small-box" style="background-color:#FF0040"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Compras</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-shopping-cart"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Módulo <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_compras.php"><i class="fa fa-arrow-circle-right"></i> Compras</a></li>'
  );
}
function pone_facturacion() {
  if (isHome()) {
    $(".col-facturacion")
      .html(
        `
        <a href="ver_facturacion.php">
          <div class="small-box" style="background-color:#04B431"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Facturación</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-file-text"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Facturas <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_facturacion.php"><i class="fa fa-arrow-circle-right"></i> Facturación</a></li>'
  );
}

function pone_integracion() {
  $("#contenedor_panel .treeview-menu").append(
    '<li><a href="ver_integracion.php"><i class="fa fa-arrow-circle-right"></i> Integración</a></li>'
  );
}

function pone_usuarios() {
  $("#contenedor_panel .treeview-menu").append(
    '<li><a href="ver_usuarios.php"><i class="fa fa-arrow-circle-right"></i> Usuarios</a></li>'
  );
}

function pone_transportistas() {
  $("#contenedor_panel .treeview-menu").append(
    '<li><a href="ver_transportistas.php"><i class="fa fa-arrow-circle-right"></i> Transportistas</a></li>'
  );
}

function pone_clientes_sidebar() {
  $("#contenedor_panel .treeview-menu").append(
    '<li><a href="ver_clientes.php"><i class="fa fa-arrow-circle-right"></i> Clientes</a></li>'
  );
}

function pone_estadisticas() {
  if (isHome()) {
    $(".col-estadisticas")
      .html(
        `
        <a href="ver_estadisticas.php">
          <div class="small-box" style="background-color:#ffd710"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Estadísticas</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-line-chart"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Estadísticas <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_estadisticas.php"><i class="fa fa-arrow-circle-right"></i> Estadísticas</a></li>'
  );
}

function pone_situacion() {
  if (isHome()) {
    $(".col-situacion")
      .html(
        `
        <a href="ver_situacion.php">
          <div class="small-box" style="background-color:#585858"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Situación Clientes</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-users"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Situación <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_situacion.php"><i class="fa fa-arrow-circle-right"></i> Situación Clientes</a></li>'
  );
}

function pone_situacion_especial() {
  if (isHome()) {
    $(".col-situacion-especial")
      .html(
        `
        <a href="ver_situacion_especial.php">
          <div class="small-box" style="background-color:#BE81F7"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Clientes Especiales</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-users"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Situación <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_situacion.php"><i class="fa fa-arrow-circle-right"></i> Clientes Especiales</a></li>'
  );
}


function pone_comisiones() {
  if (isHome()) {
    $(".col-comisiones")
      .html(
        `
        <a href="ver_comisiones.php">
          <div class="small-box" style="background-color:#FF8000"> 
            <div class="inner"  style="height:7.3em;">    
              <p style='color:white'>Comisiones Empleados</p>
            </div>
            <div class="icon">
              <i style="color:rgba(0, 0, 0, 0.15);" class="fa fa-percent"></i>
            </div>
            <span class="small-box-footer" style="background-color:rgba(0, 0, 0, 0.1);">Ver Módulo <i class="fa fa-arrow-circle-right"></i></span>
          </div>
        </a>
      `
      )
      .removeClass("d-none");
  }

  $("#contenedor_modulos").append(
    '<li><a href="ver_comisiones.php"><i class="fa fa-arrow-circle-right"></i> Comisiones</a></li>'
  );
}


function isHome() {
  return window.location.href.includes("inicio.php");
}

function checkRut(rut) {
  if (!rut || rut.trim().length < 3) return false;
  const rutLimpio = rut.replace(/[^0-9kK-]/g, "");

  if (rutLimpio.length < 3) return false;

  const split = rutLimpio.split("-");
  if (split.length !== 2) return false;

  if (!split[1].length) return false;

  const num = parseInt(split[0], 10);
  const dgv = split[1];

  if (dgv == "k" || dgv == "K" || dgv == 0){
    return true;
  }
  
  const dvCalc = calculateDV(num);
  return dvCalc === dgv;
}

function calculateDV(rut) {
  const cuerpo = `${rut}`;
  // Calcular Dígito Verificador
  let suma = 0;
  let multiplo = 2;

  // Para cada dígito del Cuerpo
  for (let i = 1; i <= cuerpo.length; i++) {
    // Obtener su Producto con el Múltiplo Correspondiente
    const index = multiplo * cuerpo.charAt(cuerpo.length - i);

    // Sumar al Contador General
    suma += index;

    // Consolidar Múltiplo dentro del rango [2,7]
    if (multiplo < 7) {
      multiplo += 1;
    } else {
      multiplo = 2;
    }
  }

  // Calcular Dígito Verificador en base al Módulo 11
  const dvEsperado = 11 - (suma % 11);
  if (dvEsperado === 10) return "k";
  if (dvEsperado === 11) return "0";
  return `${dvEsperado}`;
}