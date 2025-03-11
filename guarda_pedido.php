<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$id_cliente = $_POST['id_cliente'];
$observaciones = mysqli_real_escape_string($con, $_POST['observaciones']);
$str = json_decode($_POST['jsonarray'], true);
$id_usuario = $_SESSION["id_usuario"];

$first_day = date('Y-m-01', strtotime('this month'));
$last_day = date('Y-m-01', strtotime('first day of +1 month'));

try {
    $errors = array();

    $valor = mysqli_query($con, "SELECT IFNULL(MAX(ID_PEDIDO)+1, 1) as maximo FROM pedidos");
    if (mysqli_num_rows($valor) > 0) {
        $ww = mysqli_fetch_assoc($valor);

        $id_pedido = $ww["maximo"];
        if ((int) $id_pedido > 0) {
            mysqli_autocommit($con, false);
            if (strlen($observaciones) > 0) {
                $query = "INSERT INTO pedidos (ID_PEDIDO, id_cliente, id_usuario, observaciones, fecha, id_interno) VALUES ($id_pedido, $id_cliente, $id_usuario, UPPER('$observaciones'), NOW(),
              (select * from (SELECT IFNULL(MAX(id_interno)+1, 1) FROM pedidos WHERE fecha BETWEEN '$first_day' AND '$last_day') t)
            )";
            
            } else {
                $query = "INSERT INTO pedidos (ID_PEDIDO, id_cliente, id_usuario, observaciones, fecha, id_interno) VALUES ($id_pedido, $id_cliente, $id_usuario, NULL, NOW(),
                (select * from (SELECT IFNULL(MAX(id_interno)+1, 1) FROM pedidos WHERE fecha BETWEEN '$first_day' AND '$last_day') t)
              )"
                ;
            }
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con)."-".$query;
            }
            for ($i = 0; $i < count($str); $i++) {
                $id_variedad = $str[$i]["id_variedad"];
                $cantidad_plantas = $str[$i]["cantidad_plantas"];
                $cantidad_bandejas = $str[$i]["cantidad_bandejas"];
                $cantidad_bandejas_nuevas = $str[$i]["cantidad_bandejas_nuevas"];
                $cantidad_bandejas_usadas = $str[$i]["cantidad_bandejas_usadas"];
                $tipo_bandeja = $str[$i]["tipo_bandeja"];
                $id_especie = $str[$i]["id_especie"];
                $cantidad_semillas = $str[$i]["cantidad_semillas"];
                
                if (!$cantidad_semillas || strlen($cantidad_semillas) == 0 || (int)$cantidad_semillas < 1){
                    $cantidad_semillas = "NULL";
                }
                
                $id_artpedido = NULL;

                if (strpos($str[$i]["fecha_ingreso"], "/") !== false) {
                    $fecha = explode("/", $str[$i]["fecha_ingreso"]);
                    $fecha_ingreso = "'$fecha[2]-$fecha[1]-$fecha[0]'";

                    $fecha = explode("/", $str[$i]["fecha_entrega"]);
                    $fecha_entrega = "'$fecha[2]-$fecha[1]-$fecha[0]'";
                }
                else{
                    $fecha_ingreso = "NULL";
                    $fecha_entrega = "NULL";
                }
                $uniqid = uniqid("prod", true);    
                $id_especie = strlen($id_especie) > 0 ? $id_especie : "NULL";
                $query = "INSERT INTO articulospedidos (uniqid, id_variedad, cant_plantas, cant_bandejas, cant_semillas, cant_bandejas_nuevas, cant_bandejas_usadas, tipo_bandeja, fecha_ingreso, fecha_entrega, id_pedido, id_especie)
                        VALUES ('$uniqid', $id_variedad, $cantidad_plantas, $cantidad_bandejas, $cantidad_semillas, $cantidad_bandejas_nuevas, $cantidad_bandejas_usadas, '$tipo_bandeja', $fecha_ingreso, $fecha_entrega, $id_pedido, $id_especie);";
                
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con)."-".$query;
                }
                else{
                    $idart = mysqli_insert_id($con);
                    for ($j = 0; $j < count($str[$i]["semillas"]);$j++){
                        $id_stock_semillas = $str[$i]["semillas"][$j]["id_stock_semillas"];
                        $cantidad_semillas = $str[$i]["semillas"][$j]["cantidad"];
                        $query = "INSERT INTO semillas_pedidos (
                                id_stock_semillas,
                                cantidad,
                                id_artpedido
                            )   VALUES (
                                $id_stock_semillas,
                                $cantidad_semillas,
                                $idart
                            )
                        ";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con)."-".$query;
                        }
                    }   
                }
            }
            if (count($errors) === 0) {
                if (mysqli_commit($con)) {
                    echo "pedidonum:" . $id_pedido;
                } else {
                    mysqli_rollback($con);
                }
            } else {
                mysqli_rollback($con);
                print_r($errors);
            }
            mysqli_close($con);
        }
        else {
            echo "Error al guardar el pedido. Intentalo de nuevo";
        }
    }
} catch (\Throwable $th) {
    throw $th;
    echo "error";
}
