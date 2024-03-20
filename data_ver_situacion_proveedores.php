<?php

include "./class_lib/sesionSecurity.php";
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

$consulta = $_POST["consulta"];

if ($consulta == "busca_clientes") {
    $query = "SELECT
        c.id_cliente as id_cliente,
        c.nombre as nombre,
        c.domicilio as domicilio,
        c.telefono,
        c.mail as mail,
        c.razon_social,
        c.rut as rut,
        co.ciudad as ciudad,
        co.nombre as comuna,
        co.id as id_comuna,
        (
            (SELECT IFNULL(SUM(coti.monto),0) FROM cotizaciones coti INNER JOIN facturas f ON f.id_cotizacion = coti.id WHERE coti.id_cliente = c.id_cliente AND f.estado = 'ACEPTADO' ) +
            (SELECT IFNULL(SUM(cotid.monto),0) FROM cotizaciones_directas cotid INNER JOIN facturas f ON f.id_cotizacion_directa = cotid.id WHERE cotid.id_cliente = c.id_cliente AND f.estado = 'ACEPTADO' )
        ) as montofacturas,
        (
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag INNER JOIN facturas f ON f.rowid = pag.rowid_factura INNER JOIN cotizaciones coti ON f.id_cotizacion = coti.id WHERE coti.id_cliente = c.id_cliente) +
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag INNER JOIN facturas f ON f.rowid = pag.rowid_factura INNER JOIN cotizaciones_directas cotid ON f.id_cotizacion_directa = cotid.id WHERE cotid.id_cliente = c.id_cliente)
        ) as sumapagos
        FROM clientes c
        LEFT JOIN comunas co ON c.comuna = co.id
        ORDER BY nombre ASC;";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Nombre</th><th>Domicilio</th><th>R.U.T</th><th>Comuna</th><th>Deuda</th><th>Detalle<br>Deuda</th><th>Generar Ficha</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_cliente = $ww['id_cliente'];
            $nombre = $ww['nombre'];
            $domicilio = $ww['domicilio'];

            $telefono = $ww['telefono'];
            $mail = $ww['mail'];

            $balance = (int) $ww["montofacturas"] - (int) $ww["sumapagos"];
            $deuda = $balance <= 0 ? 0 : number_format($balance, 0, ',', '.');
            echo "<tr class='text-center' style='cursor:pointer;'>";
            echo "<td style='color:#1F618D; font-weight:bold; font-size:16px;'>$id_cliente</td>";
            echo "<td>$nombre</td>";
            echo "<td>$domicilio</td>";
            echo "<td>$ww[rut]</td>";
            echo "<td>$ww[comuna] " . ($ww["comuna"] ? "($ww[ciudad])" : "") . "</td>";
            echo "<td class='text-" . ($balance <= 0 ? "success" : "danger") . "'>$$deuda</td>";
            echo "<td><button onclick='detalleDeuda($id_cliente, \"$ww[nombre]\")' class='btn btn-primary btn fa fa-file'></button></td>";
            echo "<td><button onclick='generarFichaCliente($id_cliente)' class='btn btn-success btn fa fa-arrow-circle-right'></button></td>";
            echo "</tr>";

        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";

    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron clientes en la base de datos...</b></div>";
    }
} else if ($consulta == "generar_ficha") {
    $id = $_POST["clienteID"];

    $query = "SELECT
        cl.nombre as cliente,
        cl.rut,
        cl.id_cliente,
        cl.domicilio,
        cl.comuna as id_comuna,
        com.nombre as comuna,
        com.ciudad as ciudad,
        NULL as comentario,
        cl.razon_social
        FROM clientes cl
        LEFT JOIN comunas com ON cl.comuna = com.id
         WHERE cl.id_cliente = $id";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);

        $query2 = "SELECT
            f.rowid,
            f.folio,
            co.id,
            cl.id_cliente,
            DATE_FORMAT(f.fecha, '%d/%m/%y %H:%i') as fecha,
            co.observaciones as comentario,
            f.estado,
            (IFNULL(co.monto,0) + IFNULL(cod.monto,0)) as monto,
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag WHERE pag.rowid_factura = f.rowid) as sumapagos
            FROM facturas f
            LEFT JOIN cotizaciones co ON f.id_cotizacion = co.id
            LEFT JOIN cotizaciones_directas cod ON f.id_cotizacion_directa = cod.id
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente OR cl.id_cliente = cod.id_cliente
            WHERE cl.id_cliente = $id
            ORDER BY f.fecha DESC
            LIMIT 30
            ;
            ";

        $val2 = mysqli_query($con, $query2);

        $facturas = array();
        if (mysqli_num_rows($val2) > 0) {
            while ($ww2 = mysqli_fetch_array($val2)) {
                array_push($facturas, array(
                    "id_factura" => (int) $ww2["folio"],
                    "fecha" => $ww2["fecha"],
                    "comentario" => $ww2["comentario"],
                    "monto" => (int) $ww2["monto"],
                    "sumapagos" => (int) $ww2["sumapagos"],
                    "estado" => $ww2["estado"],
                ));
            }
        }
        $array = array(
            "cliente" => $ww["cliente"],
            "id_cliente" => $ww["id_cliente"],
            "rut" => $ww["rut"],
            "domicilio" => $ww["domicilio"],
            "comuna" => $ww["comuna"],
            "ciudad" => $ww["ciudad"],
            "razon" => $ww["razon_social"],
            "comentario" => $ww["comentario"],
            "facturas" => $facturas,
        );
        echo json_encode($array);
    }
} else if ($consulta == "grafico_por_cobrar") {
    $anio = $_POST["anio"];
    $aniofin = (int) $anio + 1;

    $querymeses = "";
    for ($i = 1; $i <= 12; $i++) {
        $mes = $i;

        $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $dt = strtotime("$anio-$mesito-01");
        $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));
        $querymeses .= "
            (SELECT (qfact.mes$i + qfactd.mes$i - qpagos.mes$i) as mes$i  FROM
                (SELECT
                    IFNULL(SUM(coti.monto),0) as mes$i
                    FROM cotizaciones coti
                    INNER JOIN facturas f
                    ON f.id_cotizacion = coti.id
                    WHERE
                    f.estado = 'ACEPTADO' AND
                    f.fecha >= '$anio-$mesito-01 00:00:00'
                    AND f.fecha < '$fechafin 00:00:00') qfact,

                (SELECT
                    IFNULL(SUM(cotid.monto),0) as mes$i
                    FROM cotizaciones_directas cotid
                    INNER JOIN facturas f
                    ON f.id_cotizacion_directa = cotid.id
                    WHERE
                    f.estado = 'ACEPTADO' AND
                    f.fecha >= '$anio-$mesito-01 00:00:00'
                    AND f.fecha < '$fechafin 00:00:00') qfactd,

                (SELECT
                    IFNULL(SUM(p.monto),0) as mes$i
                    FROM facturas_pagos p
                    INNER JOIN facturas f
                    ON f.rowid = p.rowid_factura
                    WHERE
                    f.estado = 'ACEPTADO' AND
                    f.fecha >= '$anio-$mesito-01 00:00:00'
                    AND f.fecha < '$fechafin 00:00:00'
                ) qpagos
            ) as qp$i,";

    }
    $querymeses = rtrim($querymeses, ",");

    $querydeudas = "SELECT * FROM
        $querymeses
        ";

    $rdeudas = mysqli_query($con, $querydeudas);
    if (mysqli_num_rows($rdeudas) > 0) {
        $deudas = mysqli_fetch_assoc($rdeudas);

        $array = [
            (int) $deudas["mes1"],
            (int) $deudas["mes2"],
            (int) $deudas["mes3"],
            (int) $deudas["mes4"],
            (int) $deudas["mes5"],
            (int) $deudas["mes6"],
            (int) $deudas["mes7"],
            (int) $deudas["mes8"],
            (int) $deudas["mes9"],
            (int) $deudas["mes10"],
            (int) $deudas["mes11"],
            (int) $deudas["mes12"],
        ];
        echo json_encode($array);
    }
} else if ($consulta == "grafico_clientes_deudores") {
    $query = "SELECT
        c.id_cliente as id_cliente,
        c.nombre as nombre,
        (
            (SELECT IFNULL(SUM(coti.monto),0) FROM cotizaciones coti INNER JOIN facturas f ON f.id_cotizacion = coti.id WHERE coti.id_cliente = c.id_cliente AND f.estado = 'ACEPTADO' ) +
            (SELECT IFNULL(SUM(cotid.monto),0) FROM cotizaciones_directas cotid INNER JOIN facturas f ON f.id_cotizacion_directa = cotid.id WHERE cotid.id_cliente = c.id_cliente AND f.estado = 'ACEPTADO' )
        ) as montofacturas,
        (
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag INNER JOIN facturas f ON f.rowid = pag.rowid_factura INNER JOIN cotizaciones coti ON f.id_cotizacion = coti.id WHERE coti.id_cliente = c.id_cliente) +
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag INNER JOIN facturas f ON f.rowid = pag.rowid_factura INNER JOIN cotizaciones_directas cotid ON f.id_cotizacion_directa = cotid.id WHERE cotid.id_cliente = c.id_cliente)
        ) as sumapagos
        FROM clientes c
        GROUP BY id_cliente
        HAVING (montofacturas - sumapagos) > 0
        ORDER BY nombre ASC;";

    $val = mysqli_query($con, $query);
    $clientes = array();
    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            array_push($clientes, array(
                "id_cliente" => $ww["id_cliente"],
                "nombre_cliente" => $ww["nombre"],
                "deuda" => (int) $ww["montofacturas"] - (int) $ww["sumapagos"],
            ));
        }
        echo json_encode($clientes);
    }
} else if ($consulta == "cargar_detalle_deuda") {
    $id_cliente = $_POST["id_cliente"];
    $query = "SELECT
            f.rowid,
            f.folio,
            f.caf,
            f.id_guia_despacho,
            co.id,
            cl.nombre as cliente,
            cl.id_cliente,
            f.track_id,
            DATE_FORMAT(f.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(f.fecha, '%Y%m%d%H%i') as fecha_raw,
            co.observaciones as comentario,
            cod.observaciones as comentario2,
            f.id_cotizacion,
            f.id_cotizacion_directa,
            f.estado,
            (IFNULL(co.monto,0) + IFNULL(cod.monto,0)) as monto,
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag WHERE pag.rowid_factura = f.rowid) as sumapagos
            FROM facturas f
            LEFT JOIN cotizaciones co
            ON f.id_cotizacion = co.id
            LEFT JOIN cotizaciones_directas cod
            ON f.id_cotizacion_directa = cod.id
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente OR cl.id_cliente = cod.id_cliente
            WHERE cl.id_cliente = $id_cliente AND f.estado = 'ACEPTADO'
            ORDER BY f.fecha DESC
            ;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<table id='tabla-historial-facturas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Fact. N°</th><th>Fecha</th><th>Cot. N°</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th>Deuda</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            if (((int) $ww["monto"] - (int) $ww["sumapagos"]) > 0) {
                $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
                $deuda = (int) $ww["monto"] - (int) $ww["sumapagos"];
                $classdeuda = ($deuda <= 0 ? "success" : "danger");
                $deuda = $ww["estado"] == "ACEPTADO" ? ("$" . number_format($deuda >= 0 ? $deuda : 0, 0, ',', '.')) : "";

                $btn_add_pago = "<button style='padding-left:11px;padding-right:11px' onclick='agregarPago($ww[rowid], $ww[folio], $ww[monto], false)' class='btn btn-success fa fa-usd btn-sm mr-2'> </button>";

                $esFactDirecta = ($ww["id_cotizacion_directa"] != null ? "true" : "false");

                $id_guia = (isset($ww["id_guia_despacho"]) ? $ww["id_guia_despacho"] : "null");

                $docRef = "";

                if (isset($ww["id_guia_despacho"])) {
                    $docRef = "GUÍA DESP. $ww[id_guia_despacho]";
                } else if (isset($ww["id_cotizacion_directa"])) {
                    $docRef = "FACT.<br>DIRECTA";
                } else if (isset($ww["id_cotizacion"])) {
                    $docRef = $ww["id_cotizacion"];
                }
                echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]'>
                <td>$ww[folio]</td>
                <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td>$docRef</td>
                <td>" . ((strlen($ww["comentario"]) > 0 ? $ww["comentario"] : strlen($ww["comentario2"]) > 0) ? $ww["comentario2"] : "") . "</td>
                <td>$monto</td>
                <td class='text-$classdeuda'>$deuda</td>
                <td class='text-center'>
                        <div class='ml-1 d-flex flex-row justify-content-center align-items-center'>
                            $btn_print
                            $btn_add_pago
                        </div>
                </td>
                </tr>";
            }

        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron deudas para este cliente</b></div>";
    }
}
