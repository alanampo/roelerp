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

if ($consulta == "get_comisiones") {
    $anio = $_POST["anio"];
    $mes = $_POST["mes"];

    $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
    $dt = strtotime("$anio-$mesito-01");
    $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));

    $query = "SELECT
        u.nombre,
        u.id,
        u.nombre_real,
        (SELECT IFNULL(COUNT(*),0) FROM facturas f WHERE f.estado = 'ACEPTADO' AND f.id_usuario = u.id AND f.fecha >= '$anio-$mesito-01 00:00:00'
                AND f.fecha < '$fechafin 00:00:00') as cant_facturas,
        (SELECT comi.porcentaje FROM comisiones comi WHERE comi.id_usuario = u.id AND comi.mes = $mes AND comi.anio = $anio ORDER BY comi.rowid DESC LIMIT 1) as comision,
        (SELECT comi.porcentaje FROM comisiones comi WHERE comi.id_usuario = u.id ORDER BY comi.rowid DESC LIMIT 1) as ultima_comision,
        (SELECT ROUND(SUM((coti.monto/1.19))) FROM cotizaciones coti INNER JOIN facturas f ON f.id_cotizacion = coti.id WHERE f.id_usuario = u.id AND f.estado = 'ACEPTADO' AND f.fecha >= '$anio-$mesito-01 00:00:00'
                AND f.fecha < '$fechafin 00:00:00') as totalfacturado,
        (SELECT ROUND(SUM((cotid.monto/1.19))) FROM cotizaciones_directas cotid INNER JOIN facturas f ON f.id_cotizacion_directa = cotid.id WHERE f.id_usuario = u.id AND f.estado = 'ACEPTADO' AND f.fecha >= '$anio-$mesito-01 00:00:00'
                AND f.fecha < '$fechafin 00:00:00') as totalfacturadodirecto
        FROM usuarios u
        WHERE u.tipo_usuario = 1 AND u.id != 1
    ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Cantidad de Facturas por Usuario</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Nombre Usuario</th><th>Nombre Real</th><th>Cantidad Facturas en el Mes</th><th>Total Neto Facturado</th><th>% Comisión</th><th>Monto Comisión Total</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $boton = "<button onclick='modificarPorcentaje($ww[id], \"".($ww["comision"] == NULL ? (int)$ww["ultima_comision"] : (int)$ww["comision"])."\")' class='btn btn-sm btn-primary fa fa-edit d-inline-block'></button>";
            $porcentaje = ($ww["ultima_comision"] == NULL ? "$boton" : "<span class='mr-2'>".($ww["comision"] == NULL ? (int)$ww["ultima_comision"] : (int)$ww["comision"])."%</span> $boton");
            $comision = "";
            if ($ww["comision"] != NULL && $ww["totalfacturado"] != NULL){
                $comision = ($ww["comision"] * ($ww["totalfacturado"] + $ww["totalfacturadodirecto"])) / 100;
            }
            else if ($ww["ultima_comision"] != NULL && $ww["totalfacturado"] != NULL){
                $comision = ($ww["ultima_comision"] * ($ww["totalfacturado"] + $ww["totalfacturadodirecto"])) / 100;
            }
            else{
                $comision = "";
            }
            echo "<tr class='text-center' style='cursor:pointer;'>";
            echo "<td>$ww[nombre]</td>";
            echo "<td>$ww[nombre_real]</td>";
            echo "<td>$ww[cant_facturas]</td>";
            echo "<td>".($ww["totalfacturado"] != NULL ? "$".number_format($ww["totalfacturado"], 0, ',', '.') : "$0")."</td>";
            echo "<td>$porcentaje</td>";
            echo "<td>".(strlen($comision) > 0 ? "$".number_format($comision, 0, ',', '.') : "")."</td>";
            //echo "<td><button onclick='generarFichaCliente($id_cliente)' class='btn btn-success btn-sm fa fa-arrow-circle-right'></button></td>";
            echo "</tr>";

        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";

    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron usuarios en la base de datos...</b></div>";
    }
}
else if ($consulta == "generar_ficha") {
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
            ROUND(co.monto) as monto,
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag WHERE pag.rowid_factura = f.rowid) as sumapagos
            FROM cotizaciones co
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente
            INNER JOIN facturas f ON f.id_cotizacion = co.id
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
                    "id_factura" => (int)$ww2["folio"],
                    "fecha" => $ww2["fecha"],
                    "comentario" => $ww2["comentario"],
                    "monto" => (int)$ww2["monto"],
                    "sumapagos" => (int)$ww2["sumapagos"],
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
            "facturas" => $facturas
        );
        echo json_encode($array);
    }
}
else if ($consulta == "guardar_porcentaje"){
    $porcentaje = mysqli_real_escape_string($con, $_POST["porcentaje"]);

    $query = "INSERT INTO comisiones (
        id_usuario,
        porcentaje,
        mes,
        anio
    ) VALUES (
        $_POST[id_usuario],
        '$porcentaje',
        $_POST[mes],
        $_POST[anio]
    )";

    if (mysqli_query($con, $query)){
        echo "success";
    }
    else{
        print_r(mysqli_error($con));
    }
}