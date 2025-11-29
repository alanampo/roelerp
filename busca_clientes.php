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

// Filtro de clientes sin vendedor
$filtro_sin_vendedor = isset($_POST['sin_vendedor']) && $_POST['sin_vendedor'] == 'true';

$where_clause = "";
if ($filtro_sin_vendedor) {
    $where_clause = "WHERE c.id_vendedor IS NULL";
}

$cadena="SELECT c.id_cliente as id_cliente, c.provincia, c.region, c.nombre as nombre, c.domicilio as domicilio, c.domicilio2, c.telefono, c.mail as mail, c.razon_social, c.rut as rut, c.id_vendedor, c.fecha_ultimo_contacto, co.ciudad as ciudad, co.nombre as comuna, co.id as id_comuna, u.nombre_real as vendedor_nombre,
DATE_FORMAT(c.fecha_ultimo_contacto, '%d/%m/%Y') as fecha_ultimo_contacto_format,
c.razon_social
FROM clientes c
LEFT JOIN comunas co ON c.comuna = co.id
LEFT JOIN usuarios u ON c.id_vendedor = u.id
$where_clause
ORDER BY nombre ASC;";

$val = mysqli_query($con, $cadena);

if (mysqli_num_rows($val)>0){
 echo "<div class='box box-primary'>";
 echo "<div class='box-header with-border'>";
 echo "</div>";
 echo "<div class='box-body'>";
 echo "<div style='overflow-x: auto;'>";
 echo "<table id='tabla' class='table table-bordered table-striped' style='width:100%;'>";
 echo "<thead>";
 echo "<tr>";
 $th_eliminar = ($_SESSION["id_usuario"] == 1 ? "<th></th>" :"");
 echo "<th>ID</th><th>Nombre</th><th>Razón Social</th><th>Domicilio</th><th>Domicilio 2</th><th>Teléfono</th><th>E-Mail</th><th>R.U.T</th><th>Ciudad</th><th>Comuna</th><th>Provincia</th><th>Región</th><th>Vendedor</th><th>Últ. Contacto</th>$th_eliminar";
 echo "</tr>";
 echo "</thead>";
 echo "<tbody>";
  
 while($ww=mysqli_fetch_array($val)){
     $id_cliente=$ww['id_cliente'];
     $nombre=$ww['nombre'];
     $domicilio=$ww['domicilio'];
     $domicilio2=$ww['domicilio2'];

     $telefono = $ww['telefono'];
     $provincia = $ww['provincia'];
     $region = $ww['region'];
     $mail = $ww['mail'];
     $id_vendedor = $ww['id_vendedor'] ? $ww['id_vendedor'] : '';
     $vendedor_nombre = $ww['vendedor_nombre'] ? $ww['vendedor_nombre'] : '-';
     $fecha_ultimo_contacto = $ww['fecha_ultimo_contacto_format'] ? $ww['fecha_ultimo_contacto_format'] : '-';

     // Verificar si han pasado más de 6 meses sin contacto
     $alerta_inactividad = '';
     if ($ww['fecha_ultimo_contacto'] && $ww['id_vendedor']) {
         $fecha_limite = date('Y-m-d', strtotime('-6 months'));
         if ($ww['fecha_ultimo_contacto'] < $fecha_limite) {
             $alerta_inactividad = " style='background-color: #ffcccc; cursor:pointer;'";
         }
     }

   echo "<tr class='text-center' x-razon=\"$ww[razon_social]\" x-id-comuna='$ww[id_comuna]' x-id-vendedor='$id_vendedor' id='cliente_$id_cliente' style='cursor:pointer;'$alerta_inactividad>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' style='color:#1F618D; font-weight:bold; font-size:16px;'>$id_cliente</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-nombre'>$nombre</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-razon'>$ww[razon_social]</td>";
   
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-domicilio'>$domicilio</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-domicilio2'>$domicilio2</td>";

   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-telefono'>$telefono</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-email'>$mail</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-rut'>$ww[rut]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)'>$ww[ciudad]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-comuna'>$ww[comuna]</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-provincia'>$provincia</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)' class='td-region'>$region</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)'>$vendedor_nombre</td>";
   echo "<td onClick='MostrarModalModificarCliente(this.parentNode.id)'>$fecha_ultimo_contacto</td>";
   if ($_SESSION["id_usuario"] == 1){
    echo "<td style='text-align: center;'>
    <button class='btn btn-sm btn-danger fa fa-trash' onclick='eliminarCliente($id_cliente, \"$nombre\")'></button>
    </td>";
   }
   echo "</tr>";
   
 }
 echo "</tbody>";
 echo "</table>";
 echo "</div>"; // cierra overflow-x
 echo "</div>"; // cierra box-body
 echo "</div>"; // cierra box-primary


}else{
  echo "<div class='callout callout-danger'><b>No se encontraron clientes en la base de datos...</b></div>";
}
?>