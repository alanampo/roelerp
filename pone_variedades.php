<?php

include "./class_lib/sesionSecurity.php";
error_reporting(0);
require('class_lib/class_conecta_mysql.php');

$id_tipo = $_POST['id_tipo'];

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}


mysqli_query($con,"SET NAMES 'utf8'");
$cadena="select v.id_variedad as id_variedad, t.id_tipo, v.nombre_variedad as nombre from variedades v INNER JOIN tipo_articulo t ON t.id_tipo = v.id_tipo WHERE t.id_tipo = $id_tipo order by v.nombre_variedad";
$val = mysqli_query($con, $cadena);
 if (mysqli_num_rows($val)>0){

    while($re=mysqli_fetch_array($val)){
    	echo "<option value=$re[id_variedad]>$re[nombre]</option>";
    }


   }
?>
