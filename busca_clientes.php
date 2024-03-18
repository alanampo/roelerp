<?php

include "./class_lib/sesionSecurity.php";
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
require('class_lib/class_conecta_mysql.php');
require('class_lib/funciones.php');

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con,"SET NAMES 'utf8'");

$cadena="SELECT c.id_cliente as id_cliente, c.nombre as nombre, c.domicilio as domicilio, c.telefono, c.mail as mail, c.razon_social, c.rut as rut, co.ciudad as ciudad, co.nombre as comuna, co.id as id_comuna FROM clientes c LEFT JOIN comunas co ON c.comuna = co.id ORDER BY nombre ASC;";

$val = mysqli_query($con, $cadena);

if (mysqli_num_rows($val)>0){
 echo "<div class='box box-primary'>";
 echo "<div class='box-header with-border'>";
 echo "</div>";
 echo "<div class='box-body'>";
 echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
 echo "<thead>";
 echo "<tr>";
 $th_eliminar = ($_SESSION["id_usuario"] == 1 ? "<th></th>" :"");
 echo "<th>ID</th><th>Nombre</th><th>Domicilio</th><th>Teléfono</th><th>E-Mail</th><th>R.U.T</th><th>Ciudad</th><th>Comuna</th>$th_eliminar";
 echo "</tr>";
 echo "</thead>";
 echo "<tbody>";
  
 while($ww=mysqli_fetch_array($val)){
     $id_cliente=$ww['id_cliente'];
     $nombre=$ww['nombre'];
     $domicilio=$ww['domicilio'];

     $telefono = $ww['telefono'];
     $mail = $ww['mail'];
    
   echo "<tr class='text-center' x-razon=\"$ww[razon_social]\" style='cursor:pointer;'>";
   echo "<td onClick='modificarCliente(this.parentNode, $id_cliente)' style='color:#1F618D; font-weight:bold; font-size:16px;'>$id_cliente</td>";
   echo "<td onClick='modificarCliente(this.parentNode, $id_cliente)' class='td-nombre'>$nombre</td>";
   echo "<td onClick='modificarCliente(this.parentNode, $id_cliente)' class='td-domicilio'>$domicilio</td>";
   echo "<td onClick='modificarCliente(this.parentNode, $id_cliente)' class='td-telefono'>$telefono</td>";
   echo "<td onClick='modificarCliente(this.parentNode, $id_cliente)' class='td-email'>$mail</td>";
   echo "<td onClick='modificarCliente(this.parentNode, $id_cliente)' class='td-rut'>$ww[rut]</td>";
   echo "<td>$ww[ciudad]</td>";
   echo "<td class='td-comuna' x-id='$ww[id_comuna]'>$ww[comuna]</td>";
   if ($_SESSION["id_usuario"] == 1){
    echo "<td style='text-align: center;'>
    <button class='btn btn-sm btn-danger fa fa-trash' onclick='eliminarCliente($id_cliente, \"$nombre\")'></button>
    </td>";
   }
   echo "</tr>";
   
 }
 echo "</tbody>";
 echo "</table>";
 echo "</div>";
 echo "</div>";


}else{
  echo "<div class='callout callout-danger'><b>No se encontraron clientes en la base de datos...</b></div>";
}
?>