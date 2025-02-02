<?php

require "./class_lib/sesionSecurity.php";
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';
require 'class_lib/libredte/vendor/sasco/libredte/examples/inc.php';
header('Content-type: text/html; charset=utf-8');
$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];

if ($consulta == "cargar_historial") { //FACTURAS
    $mostrarProductos = (int)$_POST["mostrarProductos"];

    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
            f.rowid,
            f.folio,
            f.caf,
            f.id_guia_despacho,
            co.id,
            cl.nombre as cliente,
            cl.id_cliente,
            cl.mail,
            cl.telefono,
            cl.domicilio,
            com.nombre as comuna,
            com.ciudad as ciudad,
            f.track_id,
            DATE_FORMAT(f.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(f.fecha, '%Y%m%d%H%i') as fecha_raw,
            co.observaciones as comentario,
            cod.observaciones as comentario2,
            f.id_cotizacion,
            f.id_cotizacion_directa,
            f.estado,
            (IFNULL(co.monto,0) + IFNULL(cod.monto,0)) as monto,
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag WHERE pag.rowid_factura = f.rowid) as sumapagos,
            
            (SELECT GROUP_CONCAT(productos_pedidos) FROM  
                (SELECT CONCAT(v.id, '|', v.nombre, '|', cot.cantidad) as productos_pedidos, cot.id as id_cotprod, cot.id_cotizacion as id_coti
                  FROM variedades_producto v INNER JOIN cotizaciones_productos cot ON cot.id_variedad = v.id
                ) t1 WHERE co.id IS NOT NULL AND t1.id_coti = co.id) as productos_cotizados,

            (SELECT GROUP_CONCAT(productos_pedidos) FROM  
                (SELECT CONCAT(v.id, '|', v.nombre, '|', cot.cantidad) as productos_pedidos, cot.id as id_cotprod, cot.id_cotizacion_directa as id_coti
                  FROM variedades_producto v INNER JOIN cotizaciones_directas_productos cot ON cot.id_variedad = v.id
                ) t1 WHERE cod.id IS NOT NULL AND t1.id_coti = cod.id) as productos_cotizados_directa


            FROM facturas f
            LEFT JOIN cotizaciones co
            ON f.id_cotizacion = co.id
            LEFT JOIN cotizaciones_directas cod
            ON f.id_cotizacion_directa = cod.id
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente OR cl.id_cliente = cod.id_cliente
            LEFT JOIN comunas com ON com.id = cl.comuna
            ;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title text-primary font-weight-bold'>Facturas Emitidas <button class='btn btn-sm btn-secondary ml-2' onclick='loadHistorial(true);' style='font-size: .675rem'> VER PRODUCTOS</button></h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-historial-facturas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Fecha</th><th>Cot. N°</th><th>Track ID</th><th>Estado</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th>Deuda</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoFactura($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
            
            $email = isset($ww["mail"]) ? "\"".trim($ww["mail"])."\"" : "null";
            $deuda = (int) $ww["monto"] - (int) $ww["sumapagos"];
            $classdeuda = ($deuda <= 0 ? "success" : "danger");
            $deuda = $ww["estado"] == "ACEPTADO" ? ("$" . number_format($deuda >= 0 ? $deuda : 0, 0, ',', '.')) : "";

            $boton_eliminar = ($_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminarCotizacion($ww[rowid])'></button>" : "");
            $btn_add_pago = $ww["estado"] == "ACEPTADO" ? "<button style='padding-left:11px;padding-right:11px' onclick='agregarPago($ww[rowid], $ww[folio], $ww[monto])' class='btn btn-success fa fa-usd btn-sm mr-2'> </button>" : "";

            $esFactDirecta = ($ww["id_cotizacion_directa"] != null ? "true" : "false");

            $btn_cancelar_factura = ($ww["estado"] == "ACEPTADO" ? "<button onclick='modalAnularFactura($ww[rowid], $ww[folio], $esFactDirecta, $ww[id_cliente])' class='btn btn-danger fa fa-ban btn-sm mr-2'></button>" : "");
            $btn_print = ($ww["track_id"] ? "<button onclick='printDTE(this, $ww[rowid], $ww[folio], 0)' class='btn btn-primary fa fa-print btn-sm mr-2'></button>" : "");


            $montoint = (int)$ww["monto"];
            $btn_enviar = ($ww["track_id"] ? "<button onclick='sendMailFactura(this, $ww[rowid], $ww[folio], 0, $montoint, $email)' class='btn btn-info fa fa-envelope btn-sm'></button>" : "");

            $id_guia = (isset($ww["id_guia_despacho"]) ? $ww["id_guia_despacho"] : "null");
            $onclick = "";

            if ($ww["estado"] === "NOENV" && !$ww["track_id"]) {
                $onclick = "onclick='vistaPreviaReenviarFactura(" . ($ww['id_cotizacion'] != null ? $ww['id_cotizacion'] : $ww['id_cotizacion_directa']) . ", " . ($ww['id_cotizacion_directa'] ? "true" : "false") . ", $ww[folio], $ww[rowid], $ww[caf], $id_guia)'";
            } else if ($ww["estado"] === "NOENV" || !isset($ww["estado"]) || $ww["estado"] === "EPR" || $ww["estado"] === "RECHAZADO" || $ww["estado"] === "ACEPTADO") {
                $onclick = "onclick='getEstadoDTE($ww[track_id], $ww[folio], 0, \"$ww[estado]\", $ww[rowid])'";
            }

            $btn_eliminar = ($ww["estado"] == "NOENV" && !$ww["track_id"]) ? "<button class='btn btn-danger fa fa-trash btn-sm ml-2' onClick='eliminarDTE($ww[rowid], 0)'></button>" : "";

            $docRef = "";

            if (isset($ww["id_guia_despacho"])) {
                $docRef = "GUÍA DESP. $ww[id_guia_despacho]";
            } else if (isset($ww["id_cotizacion_directa"])) {
                $docRef = "FACT.<br>DIRECTA";
                $productos = $ww["productos_cotizados_directa"];
            } else if (isset($ww["id_cotizacion"])) {
                $docRef = $ww["id_cotizacion"];
                $productos = $ww["productos_cotizados"];
            }

            
            if (isset($productos) && $mostrarProductos == 1){
                $tmp = explode(",", $productos);
                $productos = "";
                foreach ($tmp as $producto) {
                    $tmp2 = explode("|", $producto);
                    if (isset($tmp2[2]) && isset($tmp2[1])){
                        $productos.="<br><small>$tmp2[2] Bandejas de $tmp2[1] 
                        <input type='checkbox' x-cantidad-band='$tmp2[2]' x-producto='$tmp2[1]' class='form-check-input' style='width:10px !important;height:10px !important; margin-top:8px; margin-left:5px'>
                        </small>";
                    }
                }                
            }
            else{
                $productos = "";
            }

            
            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]'>
                <td>$ww[folio]</td>
                <td><span class='label-cliente' x-domicilio='$ww[domicilio]' x-comuna='$ww[comuna]' x-ciudad='$ww[ciudad]' x-telefono='$ww[telefono]' onclick='setChecked(this)'>$ww[cliente] ($ww[id_cliente])</span><br>$productos</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td>$docRef</td>
                <td $onclick><small>$ww[track_id]</small></td>
                <td $onclick>$estado</td>
                <td>" . (($ww["comentario"] && strlen($ww["comentario"]) > 0 ? $ww["comentario"] : $ww["comentario2"] && strlen($ww["comentario2"]) > 0) ? $ww["comentario2"] : "") . "</td>
                <td>$monto</td>
                <td class='text-$classdeuda'>$deuda</td>
                <td class='text-center'>
                        <div class='ml-1 d-flex flex-row justify-content-center align-items-center'>
                            $btn_print
                            $btn_add_pago
                        </div>
                        <div class='mt-2 d-flex flex-row justify-content-center align-items-center'>
                            $btn_cancelar_factura
                            $btn_eliminar
                            $btn_enviar
                        </div>
                </td>
                </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron facturas emitidas...</b></div>";
    }
}
else if ($consulta == "cargar_historial_boletas") { //BOLETAS
    $mostrarProductos = (int)$_POST["mostrarProductos"];

    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
            f.rowid,
            f.folio,
            f.caf,
            f.id_guia_despacho,
            co.id,
            cl.nombre as cliente,
            cl.id_cliente,
            cl.mail,
            cl.telefono,
            cl.domicilio,
            com.nombre as comuna,
            com.ciudad as ciudad,
            f.track_id,
            DATE_FORMAT(f.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(f.fecha, '%Y%m%d%H%i') as fecha_raw,
            co.observaciones as comentario,
            cod.observaciones as comentario2,
            f.id_cotizacion,
            f.id_cotizacion_directa,
            f.estado,
            (IFNULL(co.monto,0) + IFNULL(cod.monto,0)) as monto,
            (SELECT IFNULL(SUM(pag.monto),0) FROM boletas_pagos pag WHERE pag.rowid_boleta = f.rowid) as sumapagos,
            
            (SELECT GROUP_CONCAT(productos_pedidos) FROM  
                (SELECT CONCAT(v.id, '|', v.nombre, '|', cot.cantidad) as productos_pedidos, cot.id as id_cotprod, cot.id_cotizacion as id_coti
                  FROM variedades_producto v INNER JOIN cotizaciones_productos cot ON cot.id_variedad = v.id
                ) t1 WHERE co.id IS NOT NULL AND t1.id_coti = co.id) as productos_cotizados,

            (SELECT GROUP_CONCAT(productos_pedidos) FROM  
                (SELECT CONCAT(v.id, '|', v.nombre, '|', cot.cantidad) as productos_pedidos, cot.id as id_cotprod, cot.id_cotizacion_directa as id_coti
                  FROM variedades_producto v INNER JOIN cotizaciones_directas_productos cot ON cot.id_variedad = v.id
                ) t1 WHERE cod.id IS NOT NULL AND t1.id_coti = cod.id) as productos_cotizados_directa


            FROM boletas f
            LEFT JOIN cotizaciones co
            ON f.id_cotizacion = co.id
            LEFT JOIN cotizaciones_directas cod
            ON f.id_cotizacion_directa = cod.id
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente OR cl.id_cliente = cod.id_cliente
            LEFT JOIN comunas com ON com.id = cl.comuna
            ;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title text-warning font-weight-bold'>Boletas Emitidas <button class='btn btn-sm btn-secondary ml-2' onclick='loadHistorialBoletas(true);' style='font-size: .675rem'> VER PRODUCTOS</button></h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-historial-boletas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Fecha</th><th>Cot. N°</th><th>Track ID</th><th>Estado</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th>Deuda</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoFactura($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
            
            $email = isset($ww["mail"]) ? "\"".trim($ww["mail"])."\"" : "null";
            $deuda = (int) $ww["monto"] - (int) $ww["sumapagos"];
            $classdeuda = ($deuda <= 0 ? "success" : "danger");
            $deuda = $ww["estado"] == "ACEPTADO" ? ("$" . number_format($deuda >= 0 ? $deuda : 0, 0, ',', '.')) : "";

            $boton_eliminar = ($_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminarCotizacion($ww[rowid])'></button>" : "");
            $btn_add_pago = $ww["estado"] == "ACEPTADO" ? "<button style='padding-left:11px;padding-right:11px' onclick='agregarPago($ww[rowid], $ww[folio], $ww[monto])' class='btn btn-success fa fa-usd btn-sm mr-2'> </button>" : "";

            $esFactDirecta = ($ww["id_cotizacion_directa"] != null ? "true" : "false");

            $btn_cancelar_factura = ($ww["estado"] == "ACEPTADO" ? "<button onclick='modalAnularFactura($ww[rowid], $ww[folio], $esFactDirecta, $ww[id_cliente], true)' class='btn btn-danger fa fa-ban btn-sm mr-2'></button>" : "");
            $btn_print = ($ww["track_id"] ? "<button onclick='printDTE(this, $ww[rowid], $ww[folio], 10)' class='btn btn-primary fa fa-print btn-sm mr-2'></button>" : "");

            $montoint = (int)$ww["monto"];
            $btn_enviar = ($ww["track_id"] ? "<button onclick='sendMailBoleta(this, $ww[rowid], $ww[folio], 10, $montoint, $email)' class='btn btn-info fa fa-envelope btn-sm'></button>" : "");

            $id_guia = (isset($ww["id_guia_despacho"]) ? $ww["id_guia_despacho"] : "null");
            $onclick = "";

            if ($ww["estado"] === "NOENV" && !$ww["track_id"]) {
                $onclick = "onclick='vistaPreviaReenviarFactura(" . ($ww['id_cotizacion'] != null ? $ww['id_cotizacion'] : $ww['id_cotizacion_directa']) . ", " . ($ww['id_cotizacion_directa'] ? "true" : "false") . ", $ww[folio], $ww[rowid], $ww[caf], $id_guia, true)'";
            } else if ($ww["estado"] === "NOENV" || !isset($ww["estado"]) || $ww["estado"] === "EPR" || $ww["estado"] === "RECHAZADO" || $ww["estado"] === "ACEPTADO") {
                $onclick = "onclick='getEstadoDTE($ww[track_id], $ww[folio], 4, \"$ww[estado]\", $ww[rowid])'";
            }

            $btn_eliminar = ($ww["estado"] == "NOENV" && !$ww["track_id"]) ? "<button class='btn btn-danger fa fa-trash btn-sm ml-2' onClick='eliminarDTE($ww[rowid], 4)'></button>" : "";

            $docRef = "";

            if (isset($ww["id_guia_despacho"])) {
                $docRef = "GUÍA DESP. $ww[id_guia_despacho]";
            } else if (isset($ww["id_cotizacion_directa"])) {
                $docRef = "BOL.<br>DIRECTA";
                $productos = $ww["productos_cotizados_directa"];
            } else if (isset($ww["id_cotizacion"])) {
                $docRef = $ww["id_cotizacion"];
                $productos = $ww["productos_cotizados"];
            }

            
            if (isset($productos) && $mostrarProductos == 1){
                $tmp = explode(",", $productos);
                $productos = "";
                foreach ($tmp as $producto) {
                    $tmp2 = explode("|", $producto);
                    if (isset($tmp2[2]) && isset($tmp2[1])){
                        $productos.="<br><small>$tmp2[2] Bandejas de $tmp2[1] 
                        <input type='checkbox' x-cantidad-band='$tmp2[2]' x-producto='$tmp2[1]' class='form-check-input' style='width:10px !important;height:10px !important; margin-top:8px; margin-left:5px'>
                        </small>";
                    }
                }                
            }
            else{
                $productos = "";
            }

            
            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]'>
                <td>$ww[folio]</td>
                <td><span class='label-cliente' x-domicilio='$ww[domicilio]' x-comuna='$ww[comuna]' x-ciudad='$ww[ciudad]' x-telefono='$ww[telefono]' onclick='setChecked(this)'>$ww[cliente] ($ww[id_cliente])</span><br>$productos</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td>$docRef</td>
                <td $onclick><small>$ww[track_id]</small></td>
                <td $onclick>$estado</td>
                <td>" . (($ww["comentario"] && strlen($ww["comentario"]) > 0 ? $ww["comentario"] : $ww["comentario2"] && strlen($ww["comentario2"]) > 0) ? $ww["comentario2"] : "") . "</td>
                <td>$monto</td>
                <td class='text-$classdeuda'>$deuda</td>
                <td class='text-center'>
                        <div class='ml-1 d-flex flex-row justify-content-center align-items-center'>
                            $btn_print
                            $btn_add_pago
                        </div>
                        <div class='mt-2 d-flex flex-row justify-content-center align-items-center'>
                            $btn_cancelar_factura
                            $btn_eliminar
                            $btn_enviar
                        </div>
                </td>
                </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron boletas emitidas...</b></div>";
    }
}
else if ($consulta == "cargar_cotizacion") {
    $id = $_POST["id"];

    $query = "SELECT
        cl.nombre as cliente,
        cl.rut,
        cl.id_cliente,
        cl.domicilio,
        cl.comuna,
        cl.ciudad,
        cl.giro,
        cl.razon_social,
        DATE_FORMAT(co.fecha, '%d/%m/%Y %H:%i') as fecha,
        co.observaciones as comentario,
        co.condicion_pago,
        ROUND(co.monto) as monto
        FROM clientes cl
        INNER JOIN cotizaciones co ON co.id_cliente = cl.id_cliente
         WHERE co.id = $id";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);

        $query2 = "SELECT
            v.nombre as nombre_variedad,
            cp.id_variedad as id_variedad_real,
            e.nombre as nombre_especie,
            cp.id_especie as id_especie,
            cp.cantidad,
            v.id_interno as id_variedad,
            t.codigo as codigo_tipo,
            t.nombre as nombre_tipo,
            t.id as id_tipo,
            ROUND(cp.precio_unitario) as precio,
            cp.tipo_descuento,
            cp.valor_descuento
            FROM
            cotizaciones_productos cp
            INNER JOIN
            variedades_producto v  ON v.id = cp.id_variedad
            INNER JOIN tipos_producto t ON t.id = v.id_tipo
            LEFT JOIN especies_provistas e ON e.id = cp.id_especie
            WHERE cp.id_cotizacion = $id
            ";
        $val2 = mysqli_query($con, $query2);
        if (mysqli_num_rows($val2) > 0) {
            $productos = array();
            while ($ww2 = mysqli_fetch_array($val2)) {
                $subtotal = (int) $ww2["precio"] * (int) $ww2["cantidad"];

                if ($ww2["tipo_descuento"] == 1) { //PORCENTUAL
                    $total = $subtotal - (($subtotal * $ww2["valor_descuento"]) / 100);
                } else if ($ww2["tipo_descuento"] == 2) { //PORCENTUAL
                    $total = $subtotal - $ww2["valor_descuento"];
                } else {
                    $total = $subtotal;
                }
                array_push($productos, array(
                    "tipo" => $ww2["nombre_tipo"],
                    "id_tipo" => $ww2["id_tipo"],
                    "variedad" => $ww2["nombre_variedad"],
                    "id_variedad" => $ww2["id_variedad"],
                    "id_variedad_real" => $ww2["id_variedad_real"],
                    "cantidad" => $ww2["cantidad"],
                    "especie" => $ww2["nombre_especie"],
                    "id_especie" => $ww2["id_especie"],
                    "codigo" => $ww2["codigo_tipo"] . str_pad($ww2["id_variedad"], 2, '0', STR_PAD_LEFT),
                    "precio" => $ww2["precio"],
                    "total" => $total,
                    "subtotal" => $subtotal,
                    "descuento" => $ww2["tipo_descuento"] != null && $ww2["tipo_descuento"] > 0 ?
                    array(
                        "tipo" => $ww2["tipo_descuento"] == 1 ? "porcentual" : "fijo",
                        "valor" => $ww2["valor_descuento"],
                    ) : null,
                ));
            }
            $array = array(
                "cliente" => $ww["cliente"],
                "id_cliente" => $ww["id_cliente"],
                "rut" => $ww["rut"],
                "domicilio" => $ww["domicilio"],
                "giro" => $ww["giro"],
                "razon" => $ww["razon_social"],
                "comuna" => $ww["comuna"],
                "ciudad" => $ww["ciudad"],
                "fecha" => $ww["fecha"],
                "comentario" => $ww["comentario"],
                "condicion_pago" => $ww["condicion_pago"],
                "monto" => $ww["monto"],
                "productos" => $productos,
            );
            echo json_encode($array);
        }
    }
} else if ($consulta == "cambiar_estado") {
    $estado = $_POST["estado"];
    $id = $_POST["id"];

    $query = "UPDATE cotizaciones SET estado = $estado WHERE id = $id;";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "cargar_historial_cotizaciones") {
    $isBoleta = $_POST["isBoleta"] == 1 ? TRUE : FALSE;

    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
            co.id,
            cl.nombre as cliente,
            cl.id_cliente,
            DATE_FORMAT(co.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(co.fecha, '%Y%m%d%H%i') as fecha_raw,
            co.observaciones as comentario,
            co.estado,
            f.id_cotizacion,
            ROUND(co.monto) as monto
            FROM cotizaciones co
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente
            LEFT JOIN facturas f ON f.id_cotizacion = co.id
            LEFT JOIN boletas b ON b.id_cotizacion = co.id
            WHERE f.id_cotizacion IS NULL
            AND b.id_cotizacion IS NULL
            AND co.estado >= 0 AND co.estado <= 1
            ;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title text-danger font-weight-bold'>Cotizaciones</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla_cotizaciones".($isBoleta ? "_boletas" : "")."' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Fecha</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th>Estado</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoCotizacion($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";

            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[id]'>
                <td>$ww[id]</td>
                <td>$ww[cliente] ($ww[id_cliente])</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td>$ww[comentario]</td>
                <td>$monto</td>
                <td>$estado</td>
                <td class='text-center'>
                        <div class='d-flex flex-row justify-content-center align-items-center'>
                            <button onclick='printDataCotizacion($ww[id], ".($isBoleta ? "true" : "false").")' class='btn btn-success fa fa-edit mr-4'></button>
                        </div>
                </td>
                </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron cotizaciones pendientes...</b></div>";
    }
} else if ($consulta == "cargar_folios_disponibles") {
    $tipo_doc = $_POST["tipoDocumento"];
    $query = "SELECT
            id,
            rango_d,
            rango_h,
            tipo_documento,
            fecha_carga,
            fecha_autorizacion
            FROM folios_caf
            WHERE tipo_documento = $tipo_doc
            ORDER BY rango_h ASC
            ";
    $disponibles = [];
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            $rango_d = (int) $ww["rango_d"];
            $rango_h = (int) $ww["rango_h"];
            $folios_anulados = array();

            // CHEQUEAR FOLIOS OCUPADOS (FACTURAS EMITIDAS)
            $tablas = ["33" => "facturas", "39" => "boletas", "61" => "notas_credito", "52" => "guias_despacho", "56" => "notas_debito"];
            $queryfacturas = "SELECT folio FROM $tablas[$tipo_doc] WHERE folio >= $rango_d AND folio <= $rango_h";

            $val2 = mysqli_query($con, $queryfacturas);
            if (mysqli_num_rows($val2) > 0) {
                while ($folio = mysqli_fetch_array($val2)) {
                    array_push($folios_anulados, (int) ($folio["folio"]));
                }
            }

            // CHEQUEAR FOLIOS ANULADOS
            $queryanulados = "SELECT an.num_folio FROM folios_anulados an INNER JOIN folios_caf fc ON an.rowid_folio = fc.id WHERE fc.tipo_documento = $tipo_doc AND an.num_folio >= $rango_d AND an.num_folio <= $rango_h";
            $val3 = mysqli_query($con, $queryanulados);
            if (mysqli_num_rows($val3) > 0) {
                while ($folio = mysqli_fetch_array($val3)) {
                    array_push($folios_anulados, (int) ($folio["num_folio"]));
                }
            }
            
            for ($i = $rango_d; $i <= $rango_h; $i++) {
                if (!in_array($i, $folios_anulados)) {
                    //echo "<option x-caf='$ww[id]' value='$i'>$i</option>";
                    array_push($disponibles, array(
                        "folio" => $i,
                        "rowid_caf" => $ww["id"]
                    ));
                }
            }
        }
        $querylast = "SELECT IFNULL(folio, 0) as folio FROM $tablas[$tipo_doc] ORDER BY folio DESC LIMIT 1";
        $val3 = mysqli_query($con, $querylast);
        
        if ($val3 && mysqli_num_rows($val3) > 0) {
            $proximo = (int) (mysqli_fetch_assoc($val3)["folio"]) + 1;
            for ($i = 0; $i < count($disponibles); $i++) {
                if ((int)$disponibles[$i]["folio"] >= $proximo){
                    echo json_encode(
                        array(
                            "folio" => (int)$disponibles[$i]["folio"],
                            "rowid_caf" => $disponibles[$i]["rowid_caf"]
                        )
                    );
                    exit();
                }
            }
        }
        else if ($val3){
            $proximo = 1;
            for ($i = 0; $i < count($disponibles); $i++) {
                if ((int)$disponibles[$i]["folio"] >= $proximo){
                    echo json_encode(
                        array(
                            "folio" => $disponibles[$i]["folio"],
                            "rowid_caf" => $disponibles[$i]["rowid_caf"]
                        )
                    );
                    exit();
                }
            }
        }
            
        
    }
    echo "nohay";

} else if ($consulta == "get_ultimo_folio") {
    $tipo_doc = $_POST["tipoDocumento"];
    if ($tipo_doc == 33) {
        $query = "SELECT folio FROM `facturas` WHERE rowid = (SELECT MAX(rowid) FROM facturas)";
    } else if ($tipo_doc == 61) {
        $query = "SELECT folio FROM `notas_credito` WHERE rowid = (SELECT MAX(rowid) FROM notas_credito)";
    } else if ($tipo_doc == 52) {
        $query = "SELECT folio FROM `guias_despacho` WHERE rowid = (SELECT MAX(rowid) FROM guias_despacho)";
    } else if ($tipo_doc == 56) {
        $query = "SELECT folio FROM `notas_debito` WHERE rowid = (SELECT MAX(rowid) FROM notas_debito)";
    }

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        echo $ww["folio"];
    }
} else if ($consulta == "update_estado_dte") {
    $estado = $_POST["estado"];
    $rowid = $_POST["rowid"];
    $tipoDoc = $_POST["tipoDoc"];
    //0 factura / 1 nota de credito

    $errors = array();
    mysqli_autocommit($con, false);

    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito"];
    $query = "UPDATE $tablas[$tipoDoc] SET estado = '$estado' WHERE rowid = $rowid;";
    if (!mysqli_query($con, $query)) {
        $errors[] = mysqli_error($con) . "-" . $query;
    }

    if ($estado == "ACEPTADO" && $tipoDoc == 1) { //NOTA DE CREDITO
        $query = "UPDATE facturas SET estado = 'ANU' WHERE rowid = (SELECT nota.id_factura FROM notas_credito nota WHERE nota.rowid = $rowid)";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }
    }

    if (count($errors) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($errors);
    }
    mysqli_close($con);

} else if ($consulta == "cargar_notas") { //NOTAS DE CREDITO
    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
    'NC' as tipo_nc,
    nc.rowid,
    nc.folio,
    co.id as id_cotizacion,
    cod.id as id_cotizacion_directa,
    cl.nombre as cliente,
    cl.id_cliente,
    nc.track_id,
    nc.caf,
    DATE_FORMAT(nc.fecha, '%d/%m/%y %H:%i') as fecha,
    DATE_FORMAT(nc.fecha, '%Y%m%d%H%i') as fecha_raw,
    UPPER(nc.comentario) as comentario,
    nc.estado,
    f.folio as folio_factura,
    f.rowid as rowid_factura,
    ROUND(IFNULL(co.monto,0) + IFNULL(cod.monto,0)) as monto,
    co.monto as m1,
    cod.monto as m2
    FROM
    notas_credito nc
    INNER JOIN facturas f ON f.rowid = nc.id_factura
    LEFT JOIN cotizaciones co ON co.id = f.id_cotizacion
    LEFT JOIN cotizaciones_directas cod ON cod.id = f.id_cotizacion_directa
    INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente OR cl.id_cliente = cod.id_cliente
UNION
    SELECT
    'NC_ANTIGUA' as tipo_nc,
    nc.rowid,
    nc.folio,
    NULL as id_cotizacion,
    NULL as id_cotizacion_directa,
    cl.nombre as cliente,
    cl.id_cliente,
    nc.track_id,
    nc.caf,
    DATE_FORMAT(nc.fecha, '%d/%m/%y %H:%i') as fecha,
    DATE_FORMAT(nc.fecha, '%Y%m%d%H%i') as fecha_raw,
    UPPER(nc.comentario) as comentario,
    nc.estado,
    nc.fac_antigua as folio_factura,
    NULL as rowid_factura,
    NULL as monto,
    NULL,
    NULL
    FROM notas_credito nc
    INNER JOIN clientes cl ON cl.id_cliente = nc.id_cliente
    WHERE nc.id_factura IS NULL
            ;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title text-warning font-weight-bold'>Notas de Crédito</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-historial-notas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Doc Referencia</th><th>Fecha</th><th>Track ID</th><th>Estado</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoFactura($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
            $btn_eliminar = ($ww["estado"] == "NOENV" && !$ww["track_id"]) ? "<button class='btn btn-danger fa fa-trash btn-sm ml-2' onClick='eliminarDTE($ww[rowid], 1)'></button>" : "";
            $esDirecta = (isset($ww["id_cotizacion_directa"]) ? "true" : "false");
            $rowid_factura = (isset($ww["rowid_factura"]) ? $ww["rowid_factura"] : "null");

            $onclick = "";

            if ($ww["estado"] === "NOENV" || !isset($ww["estado"]) || $ww["estado"] === "EPR" || $ww["estado"] === "RECHAZADO" || $ww["estado"] === "ACEPTADO") {
                $onclick = "onclick='getEstadoDTE($ww[track_id], $ww[folio], 1, \"$ww[estado]\", $ww[rowid])'";
            } else if ($ww["estado"] == "NOENV" && $ww["rowid_factura"] && !$ww["track_id"]) {
                $onclick = "onclick='reenviarNotaCredito($ww[folio], $ww[rowid], $ww[caf], $ww[folio_factura], $rowid_factura, $esDirecta)'";
            }

            $btn_print = ($ww["track_id"] ? "<button onclick='printDTE(this, $ww[rowid], $ww[folio], 1)' class='btn btn-primary fa fa-print btn-sm'></button>" : "");

            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]'>
                <td>$ww[folio]</td>
                <td>$ww[cliente] ($ww[id_cliente])</td>
                <td class='text-danger'>" . ($ww["tipo_nc"] == "NC" ? "FACTURA " : "FACT. ANTIGUA ") . " $ww[folio_factura]</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td $onclick><small>$ww[track_id]</small></td>
                <td $onclick>$estado</td>
                <td>$ww[comentario]</td>
                <td>$monto</td>
                <td class='text-center'>
                        <div class='d-flex flex-row justify-content-center align-items-center'>
                            $btn_print
                            $btn_eliminar
                        </div>
                </td>
                </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron notas de crédito emitidas...</b></div>";
    }
} else if ($consulta == "cargar_guias") { //GUIAS DE DESPACHO
    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
            gd.rowid,
            gd.folio,
            gd.caf,
            gd.id_cotizacion_directa,
            cl.nombre as cliente,
            cl.id_cliente,
            cl.domicilio,
            cl.telefono,
            com.nombre as comuna,
            gd.track_id,
            DATE_FORMAT(gd.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(gd.fecha, '%Y%m%d%H%i') as fecha_raw,
            gd.comentario,
            gd.estado,
            gd.id_factura,
            f.folio as folio_factura,
            ROUND(cod.monto) as monto
            FROM
            guias_despacho gd
            INNER JOIN cotizaciones_directas cod ON cod.id = gd.id_cotizacion_directa
            INNER JOIN clientes cl ON cl.id_cliente = cod.id_cliente
            LEFT JOIN facturas f ON f.rowid = gd.id_factura
            LEFT JOIN comunas com ON com.id = cl.comuna
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title text-info font-weight-bold'>Guías de Despacho</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-historial-guias' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Fecha</th><th>Track ID</th><th>Estado</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th>Facturada</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoFactura($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";

            $onclick = "";

            if ($ww["estado"] === "NOENV" || !isset($ww["estado"]) || $ww["estado"] === "EPR" || $ww["estado"] === "RECHAZADO" || $ww["estado"] === "ACEPTADO") {
                $onclick = "onclick='getEstadoDTE($ww[track_id], $ww[folio], 2, \"$ww[estado]\", $ww[rowid])'";
            } else if ($ww["estado"] == "NOENV" && !$ww["track_id"]) {
                $onclick = "onclick='reenviarGuiaDespacho($ww[rowid], $ww[folio], $ww[caf])'";
            }

            $btn_facturar_guia = ($ww["estado"] == "ACEPTADO" && $ww["id_factura"] == null ? "<button onclick='vistaPreviaGuiaDespacho($ww[id_cotizacion_directa], $ww[rowid])' class='btn btn-sm btn-success fa fa-edit ml-2'></button>" : "");

            $btn_print = ($ww["track_id"] ? "<button onclick='printDTE(this, $ww[rowid], $ww[folio], 2)' class='btn btn-primary fa fa-print btn-sm'></button>" : "");

            $btn_print.="<button onclick='generarGuiaTransito(this, $ww[rowid], $ww[folio], \"$ww[fecha]\", \"$ww[cliente]\", \"$ww[domicilio]\", \"$ww[comuna]\", $ww[id_cotizacion_directa], \"$ww[telefono]\")' class='btn btn-info ml-2 btn-sm d-inline-block'>SAG</button>";
            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]'>
                <td>$ww[folio]</td>
                <td>$ww[cliente] ($ww[id_cliente])</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td $onclick><small>$ww[track_id]</small></td>
                <td $onclick>$estado</td>
                <td>$ww[comentario]</td>
                <td>$monto</td>
                <td>" . ($ww["id_factura"] != null ? "<span class='text-danger'>FACT N° $ww[folio_factura]</span>" : "") . "</td>
                <td class='text-center'>
                        <div class='d-flex flex-row justify-content-center align-items-center'>
                            $btn_print
                            $btn_facturar_guia
                        </div>
                </td>
                </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron Guías de Despacho emitidas...</b></div>";
    }
} else if ($consulta == "guardar_pago") {
    $monto = mysqli_real_escape_string($con, $_POST["monto"]);
    $comentario = $_POST["comentario"] != null && strlen($_POST["comentario"]) > 0 ? mysqli_real_escape_string($con, $_POST["comentario"]) : null;
    $rowid_factura = $_POST["facturaID"];
    $cotEspecial = json_decode($_POST["cotEspecial"]);

    $comprobante = (isset($_POST["comprobante"]) && strlen($_POST["comprobante"]) > 0 ? "'".$_POST["comprobante"]."'" : "NULL" );
    $query = "INSERT INTO facturas_pagos (
            rowid_".($cotEspecial == TRUE ? "cotizacion" : "factura").",
            monto,
            fecha,
            comentario,
            comprobante
        ) VALUES (
            $rowid_factura,
            '$monto',
            NOW(),
            UPPER('$comentario'),
            $comprobante
        )";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "guardar_datos_transporte") {
    $rutTransporte = mysqli_real_escape_string($con, $_POST["rutTransporte"]);
    $rutChofer = mysqli_real_escape_string($con, $_POST["rutChofer"]);
    $patente = mysqli_real_escape_string($con, $_POST["patente"]);
    $nombreChofer = mysqli_real_escape_string($con, $_POST["nombreChofer"]);

    $query = "UPDATE datos_empresa SET transporte_rut = UPPER('$rutTransporte'), transporte_rut_chofer = UPPER('$rutChofer'), transporte_nombre_chofer = UPPER('$nombreChofer'), transporte_patente = UPPER('$patente');";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "get_pagos") {
    $rowid_factura = $_POST["facturaID"];
    
    $cotEspecial = json_decode($_POST["cotEspecial"]);
    $query = "SELECT DATE_FORMAT(fecha, '%d/%m/%y %H:%i') as fecha, monto, comentario, ISNULL(comprobante) as comprobante, id, rowid_".($cotEspecial == TRUE ? "cotizacion" : "factura")." FROM facturas_pagos WHERE rowid_".($cotEspecial == TRUE ? "cotizacion" : "factura")." = $rowid_factura ORDER BY fecha DESC";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
            $btn_comprobante = ($ww["comprobante"] != 1 ? "<button onclick='verComprobante($ww[id], this)' class='btn btn-sm btn-primary fa fa-download'></button>" : "" );
            
            $rowid = ($cotEspecial == TRUE) ? $ww["rowid_cotizacion"] : $ww["rowid_factura"];
            echo "
                <tr class='text-center' x-monto='$ww[monto]'>
                    <td>$ww[fecha]</td>
                    <td>$ww[comentario]</td>
                    <td>$monto</td>
                    <td>$btn_comprobante</td>
                    <td>
                        <button onclick='eliminarPago($ww[id], $rowid)' class='btn btn-sm btn-danger fa fa-trash'></button>
                    </td>
                </tr>
            ";
        }
    } else {
        echo "
            <tr class='text-center'>
                <td colspan='4'>
                    Aún no hay pagos para este documento
                </td>
            </tr>
        ";
    }
} else if ($consulta == "eliminar_pago") {
    $rowid = $_POST["rowid"];
    if (mysqli_query($con, "DELETE FROM facturas_pagos WHERE id = $rowid;")) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "get_datos_transporte") {
    $query = "SELECT transporte_nombre_chofer, transporte_rut, transporte_rut_chofer, transporte_patente FROM datos_empresa LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        echo json_encode(array(
            "rutTransporte" => $ww["transporte_rut"],
            "rutChofer" => $ww["transporte_rut_chofer"],
            "nombreChofer" => $ww["transporte_nombre_chofer"],
            "patente" => $ww["transporte_patente"],
        ));
    }
} else if ($consulta == "eliminar_dte") {
    $rowid = $_POST["rowid"];
    $tipoDTE = $_POST["tipoDTE"];
    $errors = array();

    if ($tipoDTE == 0) { // ES FACTURA
        $query = "SELECT * FROM guias_despacho WHERE id_factura = $rowid";
        $guias = mysqli_query($con, $query);
        $query = "SELECT * FROM notas_credito WHERE id_factura = $rowid";
        $notas = mysqli_query($con, $query);

        mysqli_autocommit($con, false);
        if (mysqli_num_rows($guias) > 0) {
            $query = "UPDATE guias_despacho SET id_factura = NULL WHERE id_factura = $rowid;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
        if (mysqli_num_rows($notas) > 0) {
            $query = "UPDATE notas_credito SET id_factura = NULL WHERE id_factura = $rowid;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
    } else if ($tipoDTE == 1 || $tipoDTE == 3) { // ES NOTA CREDITO
        mysqli_autocommit($con, false);
    } else if ($tipoDTE == 2) { //GUIA DESPACHO
        mysqli_autocommit($con, false);
        $query = "UPDATE facturas SET id_guia_despacho = NULL WHERE id_guia_despacho = $rowid;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
        $query = "UPDATE boletas SET id_guia_despacho = NULL WHERE id_guia_despacho = $rowid;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
    }
    else if ($tipoDTE == 4) { // ES BOLETA
        $query = "SELECT * FROM guias_despacho WHERE id_boleta = $rowid";
        $guias = mysqli_query($con, $query);
        $query = "SELECT * FROM notas_credito WHERE id_boleta = $rowid";
        $notas = mysqli_query($con, $query);

        mysqli_autocommit($con, false);
        if (mysqli_num_rows($guias) > 0) {
            $query = "UPDATE guias_despacho SET id_factura = NULL, id_boleta = NULL WHERE id_boleta = $rowid;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
        if (mysqli_num_rows($notas) > 0) {
            $query = "UPDATE notas_credito SET id_factura = NULL, id_boleta = NULL WHERE id_boleta = $rowid;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
    }
    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito", "boletas"];

    $query = "DELETE FROM $tablas[$tipoDTE] WHERE rowid = $rowid;";
    if (!mysqli_query($con, $query)) {
        $errors[] = mysqli_error($con);
    }

    if (count($errors) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($errors);
    }
    mysqli_close($con);
} else if ($consulta == "cargar_notas_debito") { //NOTAS DE CREDITO
    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
    'ND' as tipo_nd,
    nd.rowid,
    nd.folio,
    co.id as id_cotizacion,
    cod.id as id_cotizacion_directa,
    cl.nombre as cliente,
    cl.id_cliente,
    nd.track_id,
    nd.caf,
    DATE_FORMAT(nd.fecha, '%d/%m/%y %H:%i') as fecha,
    DATE_FORMAT(nd.fecha, '%Y%m%d%H%i') as fecha_raw,
    UPPER(nd.comentario) as comentario,
    nd.estado,
    f.folio as folio_nc,
    nc.rowid as rowid_nc,
    ROUND(IFNULL(co.monto,0) + IFNULL(cod.monto,0)) as monto,
    co.monto as m1,
    cod.monto as m2
    FROM
    notas_debito nd
    INNER JOIN notas_credito nc ON nc.rowid = nd.id_nc
    INNER JOIN facturas f ON f.rowid = nc.id_factura
    LEFT JOIN cotizaciones co ON co.id = f.id_cotizacion
    LEFT JOIN cotizaciones_directas cod ON cod.id = f.id_cotizacion_directa
    INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente OR cl.id_cliente = cod.id_cliente
UNION
    SELECT
    'ND_ANTIGUA' as tipo_nd,
    nd.rowid,
    nd.folio,
    NULL as id_cotizacion,
    NULL as id_cotizacion_directa,
    cl.nombre as cliente,
    cl.id_cliente,
    nd.track_id,
    nd.caf,
    DATE_FORMAT(nd.fecha, '%d/%m/%y %H:%i') as fecha,
    DATE_FORMAT(nd.fecha, '%Y%m%d%H%i') as fecha_raw,
    UPPER(nd.comentario) as comentario,
    nd.estado,
    nd.id_nc_antigua as folio_nc,
    NULL as rowid_nc,
    NULL as monto,
    NULL,
    NULL
    FROM notas_debito nd
    INNER JOIN clientes cl ON cl.id_cliente = nd.id_cliente
    WHERE nd.id_nc IS NULL
            ;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title text-danger font-weight-bold'>Notas de Débito</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla-historial-notas-debito' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Doc Referencia</th><th>Fecha</th><th>Track ID</th><th>Estado</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoFactura($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
            $btn_eliminar = ($ww["estado"] == "NOENV" && !$ww["track_id"]) ? "<button class='btn btn-danger fa fa-trash btn-sm ml-2' onClick='eliminarDTE($ww[rowid], 3)'></button>" : "";
            $esDirecta = (isset($ww["id_cotizacion_directa"]) ? "true" : "false");
            $rowid_nc = (isset($ww["rowid_nc"]) ? $ww["rowid_nc"] : "null");

            $onclick = "";

            if ($ww["estado"] === "NOENV" || !isset($ww["estado"]) || $ww["estado"] === "EPR" || $ww["estado"] === "RECHAZADO" || $ww["estado"] === "ACEPTADO") {
                $onclick = "onclick='getEstadoDTE($ww[track_id], $ww[folio], 3, \"$ww[estado]\", $ww[rowid])'";
            }

            $btn_print = ($ww["track_id"] ? "<button onclick='printDTE(this, $ww[rowid], $ww[folio], 3)' class='btn btn-primary fa fa-print btn-sm'></button>" : "");

            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]'>
                <td>$ww[folio]</td>
                <td>$ww[cliente] ($ww[id_cliente])</td>
                <td class='text-danger'>NC $ww[folio_nc]</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td $onclick><small>$ww[track_id]</small></td>
                <td $onclick>$estado</td>
                <td>$ww[comentario]</td>
                <td>$monto</td>
                <td class='text-center'>
                        <div class='d-flex flex-row justify-content-center align-items-center'>
                            $btn_print
                            $btn_eliminar
                        </div>
                </td>
                </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron notas de débito emitidas...</b></div>";
    }
} else if ($consulta == "pone_nc") {
    $query = "SELECT folio, rowid FROM notas_credito WHERE estado = 'ACEPTADO' ORDER BY folio DESC";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            echo "<option value='$re[rowid]' x-folio='$re[folio]'>$re[folio]</option>";
        }
    }
}
else if ($consulta == "get_data_comprobante") {
    $rowid = $_POST["id"];

    $query = "SELECT comprobante FROM facturas_pagos WHERE id = $rowid;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
            echo $ww["comprobante"];
    }
}
else if ($consulta == "get_last_guia_transito"){
    $query = "SELECT (IFNULL(MAX(id),0)+1) as maximo FROM guias_transito";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $maximo = mysqli_fetch_assoc($val);
        echo "max:".$maximo["maximo"];
    }
}
else if ($consulta == "guardar_guia_transito"){
    $codigo = $_POST["codigo"];
    $query = "INSERT INTO guias_transito (codigo) VALUES ('$codigo')";
    if (mysqli_query($con, $query)){
        echo "success";
    }

}
else if ($consulta == "get_cantidad_total_productos"){
    $id = $_POST["id"];
    $query = "SELECT (IFNULL(SUM(cantidad),0)) as cantidad FROM cotizaciones_directas_productos WHERE id_cotizacion_directa = $id";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $cantidad = mysqli_fetch_assoc($val);
        echo $cantidad["cantidad"];
    }
}
else if ($consulta == "get_productos"){
    $id = $_POST["id"];
    $query = "SELECT v.nombre as nombre_variedad, c.cantidad
     FROM cotizaciones_directas_productos c 
     INNER JOIN variedades_producto v
     ON v.id = c.id_variedad
     WHERE id_cotizacion_directa = $id";
    $val = mysqli_query($con, $query);
    $productos = [];
    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            array_push($productos, [
                "variedad" => $ww["nombre_variedad"],
                "cantidad" => $ww["cantidad"]
            ]);
        }
        echo json_encode($productos);
    }
}