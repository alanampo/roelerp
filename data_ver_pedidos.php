<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';
header('Content-type: text/html; charset=utf-8');

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");

$consulta = $_POST["consulta"];

if ($consulta == "busca_pedidos") {
    $fechai = $_POST['fechai'];
    $fechaf = $_POST['fechaf'];

    $fechai = str_replace("/", "-", $fechai);
    $fechaf = str_replace("/", "-", $fechaf);

    if (strlen($fechai) == 0) {
        $fechai = (string) date('y-m-d', strtotime("first day of -3 month"));
    }
    if (strlen($fechaf) == 0) {
        $fechaf = "NOW()";
    }

    $filtros = json_decode($_POST['filtros'], true);

    $query = "SELECT
        t.nombre as nombre_tipo,
        v.nombre as nombre_variedad,
        c.nombre as nombre_cliente,
        t.id as id_tipo,
        c.id_cliente,
        p.fecha,
        p.id_pedido,
        ap.id as id_artpedido,
        ap.cant_plantas,
        ap.cant_bandejas,
        ap.tipo_bandeja,
        t.codigo,
        v.id_interno,
        ap.estado,
        p.id_interno as id_pedido_interno,
        DATE_FORMAT(p.fecha, '%m/%d') AS mes_dia,
        ap.problema,
        ap.observacionproblema,
        ap.observacion,
        p.id_pedido,
        u.iniciales,
        e.nombre as nombre_especie,
        ap.id_especie,
        ap.eliminado,
        DATE_FORMAT(p.fecha, '%Y%m%d') AS fecha_pedido_raw,
        DATE_FORMAT(p.fecha, '%d/%m/%y') as fecha_pedido,
        DATE_FORMAT(ap.fecha_ingreso, '%Y%m%d') AS fecha_ingreso_solicitada_raw,
        DATE_FORMAT(ap.fecha_ingreso, '%d/%m/%y') as fecha_ingreso_solicitada,
        DATE_FORMAT(ap.fecha_entrega, '%Y%m%d') AS fecha_entrega_solicitada_raw,
        DATE_FORMAT(ap.fecha_entrega, '%d/%m/%y') as fecha_entrega_solicitada
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        INNER JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        WHERE ap.eliminado IS NULL 
        AND
        ";

    if (isset($_POST["tipo_pedido"]) && $_POST["tipo_pedido"] != NULL){
        if ($_POST["tipo_pedido"] == "atrasados"){
            $date = date('Y-m-d');
            $query .=" ap.fecha_entrega < '$date' AND ap.estado >= 0 AND ap.estado <= 5";
        }
        else if ($_POST["tipo_pedido"] == "paraentregar"){
            $date = date('Y-m-d');
            $query .=" ap.estado = 5 OR ap.estado = 6";
        }
        else if ($_POST["tipo_pedido"] == "problemas"){
            $date = date('Y-m-d');
            $query .=" ap.estado >= 0 AND ap.estado <= 5 AND ap.problema IS NOT NULL ";
        }
    }
    else{
        $query.=" p.fecha >= '$fechai' AND ";

        if ($fechaf == "NOW()") {
            $query .= "p.fecha <= NOW() ";
        } else {
            $query .= " p.fecha <= '$fechaf' ";
        }
    
        if ($filtros["tipo"] != null) {
            $query .= " AND id_tipo IN " . $filtros["tipo"] . " ";
        }
    
        if ($filtros["variedad"] != null) {
            $query .= " AND nombre_variedad REGEXP '" . $filtros["variedad"] . "' ";
        }
    
        if ($filtros["cliente"] != null) {
            $query .= " AND nombre_cliente REGEXP '" . $filtros["cliente"] . "' ";
        }
    
        if ($filtros["tipo_busqueda"] == "todos") {
            $query .= " AND ap.estado >= -10";
        } else if ($filtros["tipo_busqueda"] == "pendientes") {
            $query .= " AND ap.estado = -10 ";
        } else if ($filtros["tipo_busqueda"] == "produccion") {
            $query .= " AND ap.estado >= 0 AND ap.estado <= 6 ";
        } else if ($filtros["tipo_busqueda"] == "entregados") {
            $query .= " AND ap.estado = 7 ";
        } else if ($filtros["tipo_busqueda"] == "cancelados") {
            $query .= " AND ap.estado = -1 ";
        }
    }

    

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Pedidos</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table-pedidos table table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Ped</th><th>Fecha</th><th>Producto</th><th>Cliente</th><th>Plantas/Bandejas<br>Pedidas</th><th>F. Ingreso</th><th>F. Entrega Aprox</th><th>Etapa</th><th>ID Prod.</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        $array = array();

        while ($ww = mysqli_fetch_array($val)) {
            $id_cliente = $ww['id_cliente'];
            $id_pedido = $ww['id_pedido'];
            $id_artpedido = $ww['id_artpedido'];
            $fecha = $ww['fecha_pedido_raw'];
            $tipo = "";

            $especie = $ww["nombre_especie"] ? $ww["nombre_especie"] : "";
            $producto = "$ww[nombre_variedad] ($ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . ") <span class='text-primary'>$especie</span>";

            $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
            $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);

            $cliente = $ww['nombre_cliente'] . " ($id_cliente)";

            $fecha_ingreso = $ww['fecha_ingreso_original'];

            $fecha_pedido = $ww["fecha_pedido"];
            $estado = generarBoxEstado($ww["estado"], $ww["codigo"], true);
            $onclick = "onClick='MostrarModalEstado($ww[id_artpedido], \"$id_producto\", \"$ww[nombre_cliente]\")'";

            $onclickPedido = "onClick='modalModificarPedido($ww[id_pedido], \"$ww[nombre_cliente]\")'";

            echo "<tr style='cursor:pointer;' x-codigo='$id_producto' x-etapa='$ww[estado]' x-id-artpedido='$id_artpedido'>";

            if (in_array($ww['id_pedido'], $array)) {

                echo "<td $onclickPedido x-id-pedido='$id_pedido' style='text-align: center;color:#1F618D;font-size:0.7em;'>$id_pedido</td>";

                echo "<td $onclick style='text-align: center'><span style='display:none;'>" . $fecha . "</span><span style='display:none'" . $fecha_pedido . "</span></td>";

                echo "<td $onclick>$producto</td>";

                echo "<td $onclick><span style='display:none'>$cliente</span></td>";

            } else {

                echo "<td $onclickPedido id='pedido_$id_pedido' style='text-align: center;color:#1F618D; font-weight:bold; font-size:1.0em'>$id_pedido</td>";

                echo "<td $onclick style='text-align: center'><span style='display:none;'>" . $fecha . "</span>" . $fecha_pedido . "</td>";

                echo "<td $onclick>$producto</td>";

                echo "<td $onclick>$cliente</td>";

            }

            echo "<td $onclick style='text-align: center;font-weight:bold;font-size:1.0em'>$ww[cant_plantas]<br><small>$ww[cant_bandejas] de $ww[tipo_bandeja]</small></td>";

            echo "<td $onclick style='text-align: center;'><span style='display:none'>$ww[fecha_ingreso_solicitada_raw]</span>$ww[fecha_ingreso_solicitada]</td>";

            echo "<td $onclick style='text-align: center;'><span style='display:none'>$ww[fecha_entrega_solicitada_raw]</span>$ww[fecha_entrega_solicitada]</td>";

            echo "<td $onclick><div style='cursor:pointer'>$estado</div></td>";

            echo "<td $onclick style='text-align: center; font-size:1.0em; font-weight:bold'>
   <span style='font-size:1em;'>$id_producto</span>
   </td>";
            echo "<td onclick='setSelected(this)'><button class='btn btn-secondary btn-sm fa fa-arrow-circle-left'></button></td>";
            echo "</tr>";

            array_push($array, $ww['id_pedido']);

        }

        echo "</tbody>";

        echo "</table>";

        echo "</div>";

        echo "</div>";

    } else {

        echo "<div class='callout callout-danger'><b>No se encontraron pedidos en las fechas indicadas...</b></div>";

    }
} else if ($consulta == "carga_cantidad_pedidos") {
    try {
        $arraypedidos = array();
        $val = mysqli_query($con, "SELECT  
        (
            SELECT COUNT(*)
            FROM   articulospedidos WHERE estado = -10 AND eliminado IS NULL
        ) AS pendientes,
        (
            SELECT COUNT(*)
            FROM   articulospedidos WHERE estado >= 0 AND estado <= 6 AND eliminado IS NULL
        ) AS produccion,
        (
            SELECT COUNT(*)
            FROM   articulospedidos WHERE estado = -1 AND eliminado IS NULL
        ) AS cancelados,
        (
            SELECT COUNT(*)
            FROM   articulospedidos WHERE estado = 7 AND eliminado IS NULL
        ) AS entregados,
        (
            SELECT COUNT(*)
            FROM   articulospedidos WHERE estado = 6 AND eliminado IS NULL
        ) AS parcial,
        (
            SELECT COUNT(*)
            FROM   articulospedidos WHERE estado IN (-10, -1, 0, 1, 2, 3, 4, 5, 6, 7) AND eliminado IS NULL
        ) AS todos");

        if (mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            echo json_encode(array(
                "pendientes" => $re["pendientes"],
                "produccion" => $re["produccion"],
                "cancelados" => $re["cancelados"],
                "entregados" => $re["entregados"],
                "parcial" => $re["parcial"],
                "todos" => $re["todos"],
            ));
        }
    } catch (\Throwable $th) {
        throw $th;
    }
} else if ($consulta == "get_pedido_especifico") {
    $id_pedido = $_POST["id_pedido"];

    $query = "SELECT
        t.nombre as nombre_tipo,
        v.nombre as nombre_variedad,
        t.id as id_tipo,
        p.fecha,
        p.id_pedido,
        ap.id as id_artpedido,
        ap.cant_plantas,
        ap.cant_bandejas,
        ap.tipo_bandeja,
        c.id_cliente,
        t.codigo,
        v.id_interno,
        ap.estado,
        p.id_interno as id_pedido_interno,
        DATE_FORMAT(p.fecha, '%m/%d') AS mes_dia,
        ap.problema,
        ap.observacionproblema,
        ap.observacion,
        p.id_pedido,
        u.iniciales,
        e.nombre as nombre_especie,
        ap.id_especie,
        ap.eliminado,
        DATE_FORMAT(ap.fecha_ingreso, '%d/%m/%y') as fecha_ingreso_solicitada,
        DATE_FORMAT(ap.fecha_entrega, '%d/%m/%y') as fecha_entrega_solicitada

        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        GROUP BY ap.id
        HAVING ap.eliminado IS NULL AND p.id_pedido = $id_pedido;
        ";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $arraypedidos = array();
        while ($ww = mysqli_fetch_array($val)) {
            $id_artpedido = $ww['id_artpedido'];
            $especie = $ww["nombre_especie"] ? $ww["nombre_especie"] : "";
            $producto = "$ww[nombre_variedad] ($ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . ") <span class='text-primary'>$especie</span>";
            $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
            $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);

            $estado = generarBoxEstado($ww["estado"], $ww["codigo"], true);

            array_push($arraypedidos, array(
                "id_artpedido" => $ww["id_artpedido"],
                "producto" => $producto,
                "id_producto" => $id_producto,
                "fecha_ingreso" => $ww["fecha_ingreso_solicitada"],
                "fecha_entrega" => $ww["fecha_entrega_solicitada"],
                "etapa" => $estado,
                "cant_bandejas" => $ww["cant_bandejas"],
                "cant_plantas" => $ww["cant_plantas"],
                "estado" => $ww["estado"],
            )

            );
        }
        if (count($arraypedidos) > 0) {
            echo json_encode($arraypedidos);
        }

    }
} else if ($consulta == "modificar_pedido") {
    $id_artpedido = $_POST["id_artpedido"];
    $cant_bandejas = $_POST["cant_bandejas"];
    $cant_plantas = $_POST["cant_plantas"];

    try {
        if (mysqli_query($con, "UPDATE articulospedidos SET cant_bandejas = $cant_bandejas, cant_plantas = $cant_plantas WHERE id = $id_artpedido;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        throw $th;
    }

} else if ($consulta == "get_pedidos_para_produccion") {

    $productos = str_replace("[", "(", $_POST["productos"]);
    $productos = str_replace("]", ")", $productos);

    $query = "SELECT
        t.nombre as nombre_tipo,
        v.nombre as nombre_variedad,
        t.id as id_tipo,
        v.id as id_variedad,
        p.fecha,
        p.id_pedido,
        ap.id as id_artpedido,
        ap.cant_plantas,
        ap.cant_bandejas,
        ap.cant_bandejas_nuevas,
        ap.cant_bandejas_usadas,
        ap.tipo_bandeja,
        ap.cant_semillas,
        c.id_cliente,
        c.nombre as nombre_cliente,
        t.codigo,
        v.id_interno,
        ap.estado,
        p.id_interno as id_pedido_interno,
        DATE_FORMAT(p.fecha, '%m/%d') AS mes_dia,
        ap.problema,
        ap.observacionproblema,
        ap.observacion,
        p.id_pedido,
        u.iniciales,
        e.nombre as nombre_especie,
        ap.id_especie,
        ap.eliminado,
            (
            SELECT IFNULL(SUM(s.cantidad),0) as cantidad
            FROM stock_bandejas s
            WHERE
            s.tipo_bandeja = ap.tipo_bandeja AND s.condicion = 1
            ) - (
            SELECT IFNULL(SUM(sr.cantidad),0) as cantidad
            FROM stock_bandejas_retiros sr
            WHERE
            sr.tipo_bandeja = ap.tipo_bandeja AND sr.condicion = 1
            ) AS stock_nuevas,
            (
            SELECT IFNULL(SUM(s.cantidad),0) as cantidad
            FROM stock_bandejas s
            WHERE
            s.tipo_bandeja = ap.tipo_bandeja AND s.condicion = 0
            ) - (
            SELECT IFNULL(SUM(sr.cantidad),0) as cantidad
            FROM stock_bandejas_retiros sr
            WHERE
            sr.tipo_bandeja = ap.tipo_bandeja AND sr.condicion = 0
            ) AS stock_usadas,
        DATE_FORMAT(ap.fecha_ingreso, '%d/%m/%y') as fecha_ingreso_solicitada,
        DATE_FORMAT(ap.fecha_entrega, '%d/%m/%y') as fecha_entrega_solicitada
        FROM tipos_producto t
        INNER JOIN variedades_producto v ON v.id_tipo = t.id
        INNER JOIN articulospedidos ap ON ap.id_variedad = v.id
        INNER JOIN pedidos p ON p.ID_PEDIDO = ap.id_pedido
        INNER JOIN clientes c ON c.id_cliente = p.id_cliente
        LEFT JOIN usuarios u ON u.id = p.id_usuario
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        GROUP BY ap.id
        HAVING ap.eliminado IS NULL AND ap.id IN $productos;
        ";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $arraypedidos = array();
        while ($ww = mysqli_fetch_array($val)) {
            $querysemillas = "
            SELECT
            sp.id,
            ss.id_cliente as id_cliente_semillas,
            sp.id_stock_semillas as id_stock_semillas,
            sp.cantidad as cantidad_semillas_stock,
            ss.codigo as codigo_semillas,
            ss.porcentaje as porcentaje_semillas,
            ms.nombre as marca_semillas,
            ps.nombre as proveedor_semillas,
            ss.cantidad as cantidad_stock,
            (SELECT IFNULL(SUM(sr.cantidad),0) FROM stock_semillas_retiros sr WHERE sr.id_stock = ss.id_stock) as cantidad_retirada
            FROM semillas_pedidos sp
            INNER JOIN stock_semillas ss ON ss.id_stock = sp.id_stock_semillas
            INNER JOIN semillas_marcas ms ON ms.id = ss.id_marca
            INNER JOIN semillas_proveedores ps ON ps.id = ss.id_proveedor
            WHERE sp.id_artpedido = $ww[id_artpedido]
            ";
            
            $valsemillas = mysqli_query($con, $querysemillas);
            $datasemillas = NULL;
            if (mysqli_num_rows($valsemillas) > 0) {
                $datasemillas = array();
                while ($data = mysqli_fetch_array($valsemillas)) {
                    array_push($datasemillas, array(
                        "id_semillapedida" => $data["id"],
                        "id_cliente" => $data["id_cliente_semillas"],
                        "id_stock" => $data["id_stock_semillas"],
                        "cantidad" => $data["cantidad_semillas_stock"],
                        "codigo" => $data["codigo_semillas"],
                        "porcentaje" => $data["porcentaje_semillas"],
                        "marca" => $data["marca_semillas"],
                        "proveedor" => $data["proveedor_semillas"],
                        "cantidad_stock" => $data["cantidad_stock"] - $data["cantidad_retirada"]
                    ));
                }
            }

            $id_artpedido = $ww['id_artpedido'];
            $especie = $ww["nombre_especie"] ? $ww["nombre_especie"] : "";
            $producto = "$ww[nombre_variedad] ($ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . ") <span class='text-primary'>$especie</span>";
            $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
            $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);

            array_push($arraypedidos, array(
                "id_cliente" => $ww["id_cliente"],
                "id_artpedido" => $ww["id_artpedido"],
                "producto" => $producto,
                "id_producto" => $id_producto,
                "fecha_ingreso" => $ww["fecha_ingreso_solicitada"],
                "fecha_entrega" => $ww["fecha_entrega_solicitada"],
                "etapa" => $estado,
                "cant_bandejas" => $ww["cant_bandejas"],
                "cant_bandejas_nuevas" => $ww["cant_bandejas_nuevas"],
                "cant_bandejas_usadas" => $ww["cant_bandejas_usadas"],
                "cant_semillas" => $ww["cant_semillas"],
                "cant_plantas" => $ww["cant_plantas"],
                "nombre_cliente" => $ww["nombre_cliente"],
                "tipo_bandeja" => $ww["tipo_bandeja"],
                "stock_nuevas" => $ww["stock_nuevas"],
                "stock_usadas" => $ww["stock_usadas"],
                "codigo" => $ww["codigo"],
                "id_especie" => $ww["id_especie"], //NO SIRVE
                "id_variedad" => $ww["id_variedad"],
                "semillas" => $datasemillas
                
            )
            );
        }
        if (count($arraypedidos) > 0) {
            echo json_encode($arraypedidos);
        }
    }

} else if ($consulta == "enviar_produccion") {
    $id_artpedido = $_POST["id_artpedido"];
    $cant_bandejas_nuevas = (int) $_POST["cantidad_bandejas_nuevas"];
    $cant_bandejas_usadas = (int) $_POST["cantidad_bandejas_usadas"];
    $tipo_bandeja = $_POST["tipo_bandeja"];
    
    $semillas = isset($_POST["semillas"]) ? json_decode($_POST["semillas"], true) : NULL;

    try {
        $query =
            "SELECT  (
            SELECT IFNULL(SUM(cantidad),0) as cantidad
            FROM stock_bandejas
            WHERE
            tipo_bandeja = '$tipo_bandeja' AND condicion = 1
            ) - (
            SELECT IFNULL(SUM(cantidad),0) as cantidad
            FROM stock_bandejas_retiros
            WHERE
            tipo_bandeja = '$tipo_bandeja' AND condicion = 1
            )
             AS nuevas,
            (
                SELECT IFNULL(SUM(cantidad),0) as cantidad
              FROM stock_bandejas
              WHERE
              tipo_bandeja = '$tipo_bandeja' AND condicion = 0
            )-(
                SELECT IFNULL(SUM(cantidad),0) as cantidad
              FROM stock_bandejas_retiros
              WHERE
              tipo_bandeja = '$tipo_bandeja' AND condicion = 0
            )  AS usadas
            ";

        $val = mysqli_query($con, $query);
        $errors = array();
        
        if (mysqli_num_rows($val) > 0) {
            $ww = mysqli_fetch_assoc($val);
            if ($ww["nuevas"] < 0) {
                $ww["nuevas"] = 0;
            }

            if ($ww["usadas"] < 0) {
                $ww["usadas"] = 0;
            }

            if ($ww["nuevas"] < $cant_bandejas_nuevas || $ww["usadas"] < $cant_bandejas_usadas) {
                echo "ERROR: No hay suficientes bandejas en Stock. (Nuevas disponibles: $ww[nuevas] | Usadas: $ww[usadas])";
            } else {
                $puede = true;
                $total_semillas = NULL;
                if (!is_null($semillas)) {
                    $total_semillas = 0;
                    for ($i = 0;$i < count($semillas);$i++){
                        $total_semillas += (int)$semillas[$i]["cantidad"];
                        $query = "SELECT (
                            SELECT cantidad FROM stock_semillas WHERE id_stock = ".$semillas[$i]["id_stock_semillas"]."
                        ) as stock_semillas,
                        (
                            SELECT IFNULL(SUM(sr.cantidad),0) FROM stock_semillas_retiros sr WHERE sr.id_stock = ".$semillas[$i]["id_stock_semillas"]."
                        ) as stock_semillas_retiros";
    
                        $val2 = mysqli_query($con, $query);
                        if (mysqli_num_rows($val2) > 0) {
                            $querysemillas = mysqli_fetch_assoc($val2);
                            if (($querysemillas["stock_semillas"] - $querysemillas["stock_semillas_retiros"]) < (int)$semillas[$i]["cantidad"]) {
                                $puede = false;
                                echo "ERROR: No hay suficientes semillas en Stock";
                                break;
                            }
                        }
                    }
                }

                if ($puede) {
                    
                    mysqli_autocommit($con, false);
                    if ($total_semillas && $total_semillas > 0){
                        $query = "UPDATE articulospedidos SET estado = 0, cant_semillas = $total_semillas WHERE id = $id_artpedido;";
                    }
                    else{
                        $query = "UPDATE articulospedidos SET estado = 0 WHERE id = $id_artpedido;";
                    }
                    if (!mysqli_query($con, $query)) {
                        $errors[] = mysqli_error($con);
                    }
                    if ($cant_bandejas_nuevas > 0) {
                        $query = "INSERT INTO stock_bandejas_retiros
                    (fecha, cantidad, tipo_bandeja, condicion, id_artpedido)
                    VALUES
                    (NOW(), $cant_bandejas_nuevas, '$tipo_bandeja', 1, $id_artpedido)";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con);
                        }
                    }
                    if ($cant_bandejas_usadas > 0) {
                        $query =
                            "INSERT INTO stock_bandejas_retiros
                    (fecha, cantidad, tipo_bandeja, condicion, id_artpedido)
                    VALUES
                    (NOW(), $cant_bandejas_usadas, '$tipo_bandeja', 0, $id_artpedido)";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con);
                        }
                    }

                    if ($total_semillas > 0){
                        for ($i = 0;$i < count($semillas);$i++){
                            $id_stock_semillas = $semillas[$i]["id_stock_semillas"];
                            $cantidad_semillas = $semillas[$i]["cantidad"];
                            $query =
                            "INSERT INTO stock_semillas_retiros
                            (fecha, cantidad, id_stock, id_artpedido)
                            VALUES
                            (
                                NOW(), 
                                $cantidad_semillas, 
                                $id_stock_semillas, 
                                $id_artpedido)";
                            if (!mysqli_query($con, $query)) {
                                $errors[] = mysqli_error($con)."-".$query;
                            }
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
                }

            }
        }
    } catch (\Throwable $th) {
        echo "ERROR: $th";
    }
}
else if ($consulta == "quitar_semillas"){
    $id_artpedido = $_POST["id_artpedido"];
    $id_semillapedida = $_POST["id_semillapedida"];
    mysqli_autocommit($con, FALSE);

    $query = "UPDATE articulospedidos SET cant_semillas = cant_semillas - (SELECT cantidad FROM semillas_pedidos WHERE id = $id_semillapedida) WHERE id = $id_artpedido";
    if (!mysqli_query($con, $query)) {
        $errors[] = mysqli_error($con);
    }

    $query = "DELETE FROM semillas_pedidos WHERE id = $id_semillapedida;";
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
}
else if ($consulta == "migrar_semillas"){
    $query = "SELECT id_stock_semillas, id, cant_semillas FROM articulospedidos WHERE id_stock_semillas IS NOT NULL";
    $val = mysqli_query($con, $query);
    $errors = array();
        
    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        mysqli_autocommit($con, FALSE);
        while ($ww = mysqli_fetch_array($val)) {
            $query = "INSERT INTO semillas_pedidos (
                id_stock_semillas,
                cantidad,
                id_artpedido
            ) VALUES (
                $ww[id_stock_semillas],
                $ww[cant_semillas],
                $ww[id]
            )";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
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
    }
}