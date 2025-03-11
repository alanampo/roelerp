<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];

if ($consulta == "cargar_ultima_variedad") {
    try {
        $id_tipo = $_POST["id_tipo"];
        $cadenaselect = "SELECT IFNULL(MAX(id_interno)+1, 1) as maximo FROM variedades_producto WHERE id_tipo = $id_tipo;";

        $val = mysqli_query($con, $cadenaselect);

        if (mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            echo "success:$re[maximo]";
        }
    } catch (\Throwable $th) {
        throw $th;
    }

} else if ($consulta == "agregar_producto_al_pedido") {
    $id_pedido = $_POST["id_pedido"];
    $id_variedad = $_POST["id_variedad"];
    $cantidad_plantas = $_POST["cantidad_plantas"];
    $cantidad_bandejas = $_POST["cantidad_bandejas"];
    $cantidad_bandejas_nuevas = $_POST["cantidad_bandejas_nuevas"];
    $cantidad_bandejas_usadas = $_POST["cantidad_bandejas_usadas"];
    $tipo_bandeja = $_POST["tipo_bandeja"];
    $id_especie = $_POST["id_especie"];
    $fecha_ingreso = $_POST["fecha_ingreso"];
    $fecha_entrega = $_POST["fecha_entrega"];

    try {
        $errors = array();
        mysqli_autocommit($con, FALSE);
        $uniqid = uniqid("prod", true);
        if (strpos($fecha_ingreso, "/") !== false) {
            $fecha = explode("/", $fecha_ingreso);
            $fecha_ingreso = "$fecha[2]-$fecha[1]-$fecha[0]";
    
            $fecha = explode("/", $fecha_entrega);
            $fecha_entrega = "$fecha[2]-$fecha[1]-$fecha[0]";
    
            if (strlen($id_especie) > 0) {
                $query = "INSERT INTO articulospedidos (uniqid, id_variedad, cant_plantas, cant_bandejas, cant_bandejas_nuevas, cant_bandejas_usadas, tipo_bandeja, fecha_ingreso, fecha_entrega, id_pedido, id_especie)
                VALUES ('$uniqid', $id_variedad, $cantidad_plantas, $cantidad_bandejas, $cantidad_bandejas_nuevas, $cantidad_bandejas_usadas, '$tipo_bandeja', '$fecha_ingreso', '$fecha_entrega', $id_pedido, $id_especie);";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }
            } else {
                $query = "INSERT INTO articulospedidos (uniqid, id_variedad, cant_plantas, cant_bandejas, cant_bandejas_nuevas, cant_bandejas_usadas, tipo_bandeja, fecha_ingreso, fecha_entrega, id_pedido)
                VALUES ('$uniqid',$id_variedad, $cantidad_plantas, $cantidad_bandejas, $cantidad_bandejas_nuevas, $cantidad_bandejas_usadas, '$tipo_bandeja', '$fecha_ingreso', '$fecha_entrega', $id_pedido);";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }
            }
        } else {
            if (strlen($id_especie) > 0) {
                $query = "INSERT INTO articulospedidos (uniqid, id_variedad, cant_plantas, cant_bandejas, cant_bandejas_nuevas, cant_bandejas_usadas, tipo_bandeja, id_pedido, id_especie)
            VALUES ('$uniqid',$id_variedad, $cantidad_plantas, $cantidad_bandejas, $cantidad_bandejas_nuevas, $cantidad_bandejas_usadas, '$tipo_bandeja', $id_pedido, $id_especie);";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }
            } else {
                $query = "INSERT INTO articulospedidos (uniqid, id_variedad, cant_plantas, cant_bandejas, cant_bandejas_nuevas, cant_bandejas_usadas, tipo_bandeja, id_pedido)
            VALUES ('$uniqid', $id_variedad, $cantidad_plantas, $cantidad_bandejas, $cantidad_bandejas_nuevas, $cantidad_bandejas_usadas, '$tipo_bandeja', $id_pedido);";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
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
    } catch (\Throwable $th) {
        throw $th;
    }
}
