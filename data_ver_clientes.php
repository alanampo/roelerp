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

if ($consulta == "get_clientes_select"){
    $query="SELECT id_cliente, nombre, mail FROM clientes ORDER BY nombre";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val)>0){
    while($re=mysqli_fetch_array($val)){
        $nombre = mysqli_real_escape_string($con, $re["nombre"]);
        echo "<option x-email='$re[mail]' value='$re[id_cliente]' x-nombre='$nombre'>$re[nombre] ($re[id_cliente])</option>";
    }
 }

}
else if ($consulta == "pone_comunas") {
    $query = "SELECT * FROM comunas ORDER BY nombre";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            $nombre = mysqli_real_escape_string($con, $re["nombre"]);
            $ciudad = mysqli_real_escape_string($con, $re["ciudad"]);
            echo "<option value='$re[id]' x-nombre='$nombre' x-ciudad='$ciudad'>$re[nombre] ($re[ciudad])</option>";
        }
    }
}
else if ($consulta == "eliminar_cliente"){
    $id_cliente = $_POST["id_cliente"];
    if (mysqli_query($con, "DELETE FROM clientes WHERE id_cliente = $id_cliente;")){
        echo "success";
    }
    else{
        print_r(mysqli_error($con));
    }
}
?>