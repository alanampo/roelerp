<?php

include "./class_lib/sesionSecurity.php";

require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];
if ($consulta == "stats_coti_fact") {
    $anio = $_POST["anio"];
    $aniofin = (int) $anio + 1;

    $querymeses = "";
    $querymesesfact = "";
    for ($i = 1; $i <= 12; $i++) {
        $mes = $i;
        if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
            $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
            $dt = strtotime("$anio-$mesito-01");
            $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));
            $querymeses .= "(SELECT
                IFNULL(COUNT(coti.id),0) as mes$i
                FROM cotizaciones coti
                WHERE coti.fecha >= '$anio-$mesito-01 00:00:00'
                AND coti.fecha < '$fechafin 00:00:00') query$i,";

            $querymesesfact .= "(SELECT
                IFNULL(COUNT(fact.rowid),0) as mes$i
                FROM facturas fact
                WHERE
                fact.estado = 'ACEPTADO' AND
                fact.fecha >= '$anio-$mesito-01 00:00:00'
                AND fact.fecha < '$fechafin 00:00:00') query$i,";
        }
    }

    $querymeses = rtrim($querymeses, ",");
    $querymesesfact = rtrim($querymesesfact, ",");

    $querycotizaciones = "SELECT * FROM
        $querymeses
        ";

    $queryfacturas = "SELECT * FROM
        $querymesesfact
    ";

    $rcoti = mysqli_query($con, $querycotizaciones);
    $rfact = mysqli_query($con, $queryfacturas);

    if (mysqli_num_rows($rcoti) > 0 && mysqli_num_rows($rfact) > 0) {
        $cot = mysqli_fetch_assoc($rcoti);
        $fac = mysqli_fetch_assoc($rfact);

        $array = array(
            "cotizaciones" => [
                (int) $cot["mes1"],
                (int) $cot["mes2"],
                (int) $cot["mes3"],
                (int) $cot["mes4"],
                (int) $cot["mes5"],
                (int) $cot["mes6"],
                (int) $cot["mes7"],
                (int) $cot["mes8"],
                (int) $cot["mes9"],
                (int) $cot["mes10"],
                (int) $cot["mes11"],
                (int) $cot["mes12"],
            ],
            "facturas" => [
                (int) $fac["mes1"],
                (int) $fac["mes2"],
                (int) $fac["mes3"],
                (int) $fac["mes4"],
                (int) $fac["mes5"],
                (int) $fac["mes6"],
                (int) $fac["mes7"],
                (int) $fac["mes8"],
                (int) $fac["mes9"],
                (int) $fac["mes10"],
                (int) $fac["mes11"],
                (int) $fac["mes12"],
            ],
        );
        echo json_encode($array);
    }
} else if ($consulta == "stats_facturado_mensual") {
    $anio = $_POST["anio"];
    $aniofin = (int) $anio + 1;
   

    $querymeses = "";
    $querymesesfact = "";
    for ($i = 1; $i <= 12; $i++) {
        $mes = $i;
        if (strlen($anio) > 0 && strlen($mes) > 0 && (int) $mes > 0) {
            $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
            $dt = strtotime("$anio-$mesito-01");
            $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));
            $querymeses .= "(SELECT
                IFNULL(SUM(coti.monto),0) as mes$i
                FROM cotizaciones coti
                INNER JOIN facturas f
                ON f.id_cotizacion = coti.id
                WHERE
                f.estado = 'ACEPTADO' AND
                f.fecha >= '$anio-$mesito-01 00:00:00'
                AND f.fecha < '$fechafin 00:00:00') query$i,";

            $querymesesfact .= "(SELECT
                IFNULL(SUM(cotid.monto),0) as mes$i
                FROM cotizaciones_directas cotid
                INNER JOIN facturas f
                ON f.id_cotizacion_directa = cotid.id
                WHERE
                f.estado = 'ACEPTADO' AND
                f.fecha >= '$anio-$mesito-01 00:00:00'
                AND f.fecha < '$fechafin 00:00:00') query$i,";
        }
    }

    $querymeses = rtrim($querymeses, ",");
    $querymesesfact = rtrim($querymesesfact, ",");

    $querycotizaciones = "SELECT * FROM
        $querymeses
        ";

    $queryfacturas = "SELECT * FROM
        $querymesesfact
    ";

    $rcoti = mysqli_query($con, $querycotizaciones);

    $rfact = mysqli_query($con, $queryfacturas);

    if (mysqli_num_rows($rcoti) > 0 && mysqli_num_rows($rfact) > 0) {
        $fact = mysqli_fetch_assoc($rcoti);
        $factd = mysqli_fetch_assoc($rfact);

        $array = array(
            "bruto" => [
                (int) $fact["mes1"] + (int) $factd["mes1"],
                (int) $fact["mes2"] + (int) $factd["mes2"],
                (int) $fact["mes3"] + (int) $factd["mes3"],
                (int) $fact["mes4"] + (int) $factd["mes4"],
                (int) $fact["mes5"] + (int) $factd["mes5"],
                (int) $fact["mes6"] + (int) $factd["mes6"],
                (int) $fact["mes7"] + (int) $factd["mes7"],
                (int) $fact["mes8"] + (int) $factd["mes8"],
                (int) $fact["mes9"] + (int) $factd["mes9"],
                (int) $fact["mes10"] + (int) $factd["mes10"],
                (int) $fact["mes11"] + (int) $factd["mes11"],
                (int) $fact["mes12"] + (int) $factd["mes12"],
            ],
            "neto" => [
                ((int) $fact["mes1"] + (int) $factd["mes1"]) / 1.19,
                ((int) $fact["mes2"] + (int) $factd["mes2"]) / 1.19,
                ((int) $fact["mes3"] + (int) $factd["mes3"]) / 1.19,
                ((int) $fact["mes4"] + (int) $factd["mes4"]) / 1.19,
                ((int) $fact["mes5"] + (int) $factd["mes5"]) / 1.19,
                ((int) $fact["mes6"] + (int) $factd["mes6"]) / 1.19,
                ((int) $fact["mes7"] + (int) $factd["mes7"]) / 1.19,
                ((int) $fact["mes8"] + (int) $factd["mes8"]) / 1.19,
                ((int) $fact["mes9"] + (int) $factd["mes9"]) / 1.19,
                ((int) $fact["mes10"] + (int) $factd["mes10"]) / 1.19,
                ((int) $fact["mes11"] + (int) $factd["mes11"]) / 1.19,
                ((int) $fact["mes12"] + (int) $factd["mes12"]) / 1.19,
            ],
        );
        echo json_encode($array);
    }
} else if ($consulta == "stats_facturado_diario") {
    $anio = $_POST["anio"];
    $mes = $_POST["mes"];

    $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
    $dt = strtotime("$anio-$mesito-01");
    if (date("m") == (int)$mes){
        $hoy = date("d");
        $fechafin = (string) date("Y-m-$hoy", $dt);
    }
    else{
        $fechafin = (string) date("Y-m-t", $dt);
    }
    
    $ultimodia = explode("-", $fechafin)[2];
    $querymeses = "";
    $querymesesfact = "";
    for ($i = 1; $i <= $ultimodia; $i++) {
        $dia = str_pad($i, 2, '0', STR_PAD_LEFT);
        $querymeses .= "(SELECT
                IFNULL(SUM(coti.monto),0) as dia$i
                FROM cotizaciones coti
                INNER JOIN facturas f
                ON f.id_cotizacion = coti.id
                WHERE
                f.estado = 'ACEPTADO' AND
                f.fecha >= '$anio-$mesito-$dia 00:00:00'
                AND f.fecha <= '$anio-$mesito-$dia 23:59:59') query$i,";

        $querymesesfact .= "(SELECT
                IFNULL(SUM(cotid.monto),0) as dia$i
                FROM cotizaciones_directas cotid
                INNER JOIN facturas f
                ON f.id_cotizacion_directa = cotid.id
                WHERE
                f.estado = 'ACEPTADO' AND
                f.fecha >= '$anio-$mesito-$dia 00:00:00'
                AND f.fecha <= '$anio-$mesito-$dia 23:59:59') query$i,";

    }

    $querymeses = rtrim($querymeses, ",");
    $querymesesfact = rtrim($querymesesfact, ",");

    $queryfact = "SELECT * FROM
        $querymeses
        ";

    $queryfactd = "SELECT * FROM
        $querymesesfact
    ";

    $rcoti = mysqli_query($con, $queryfact);
    $rfact = mysqli_query($con, $queryfactd);

    if (mysqli_num_rows($rcoti) > 0 && mysqli_num_rows($rfact) > 0) {
        $fact = mysqli_fetch_assoc($rcoti);
        $factd = mysqli_fetch_assoc($rfact);

        $arrayBruto = array();
        $arrayNeto = array();
        $d = 1;
        foreach ($fact as $dia) {
            $dia = (int)((int)$dia + (int)$factd["dia$d"]);
            array_push($arrayBruto, $dia);
            array_push($arrayNeto, round(($dia / 1.19)*100)/100);
            $d++;
        }

        echo json_encode(array(
            "bruto" => $arrayBruto,
            "neto" => $arrayNeto,
        ));
    }
}