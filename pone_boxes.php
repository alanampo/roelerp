<?php

include "./class_lib/sesionSecurity.php";

error_reporting(0);
include 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

$tipo = $_POST["tipo"];


if ($tipo == "alertas") {
    $date = date('Y-m-d');
    $consulta = "SELECT  (SELECT COUNT(*)
                FROM   articulospedidos WHERE fecha_entrega < '$date' AND estado >= 0 AND estado <= 5 AND eliminado IS NULL
                ) AS atrasados,
                (
                SELECT COUNT(*)
                FROM   articulospedidos WHERE estado = 5 OR estado = 6 AND eliminado IS NULL
                ) AS paraentregar,
                (
                SELECT COUNT(*)
                FROM articulospedidos WHERE estado >= 0 AND estado <= 5 AND problema IS NOT NULL AND eliminado IS NULL
                ) AS problemas";
    $val = mysqli_query($con, $consulta);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        $array = array(
            "atrasados" => $ww["atrasados"],
            "paraentregar" => $ww["paraentregar"],
            "problemas" => $ww["problemas"],
        );
        echo json_encode($array);
    }   
}