<?php

include "./class_lib/sesionSecurity.php";


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

if ($consulta == "busca_historial") {
  mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
  $query = "SELECT 
          rowid,
          tipo_bandeja, 
          cantidad_original,
          cantidad, 
          condicion, 
          fecha, 
          DATE_FORMAT(fecha, '%d/%m/%Y %H:%i') as fecha_stock,
          DATE_FORMAT(fecha, '%Y%m%d%H%i') as fecha_raw
          FROM stock_bandejas;
          ";

  $val = mysqli_query($con, $query);

  if (mysqli_num_rows($val) > 0) {

      echo "<div class='box box-primary'>";
      echo "<div class='box-header with-border'>";
      echo "<h3 class='box-title'>Historial de Cargas</h3>";
      echo "</div>";
      echo "<div class='box-body'>";
      echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Tipo Bandeja</th><th>Cantidad Ingresada</th><th>Condición</th><th>Fecha Carga</th><th></th>";
      echo "</tr>";
      echo "</thead>";
      echo "<tbody>";

      while ($ww = mysqli_fetch_array($val)) {
        $condicion = $ww["condicion"] == 0 ? "USADAS" : "NUEVAS";
        $boton_eliminar = $ww["cantidad_original"] == $ww["cantidad"] ? "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminar($ww[rowid])'></button>" : "";
          echo "
  <tr class='text-center' style='cursor:pointer' x-id='$ww[rowid]' x-tipo-bandeja='$ww[tipo_bandeja]' x-cantidad='$ww[cantidad_original]' x-condicion='$ww[condicion]'>
    <td>$ww[tipo_bandeja]</td>
    <td>$ww[cantidad_original]</td>
    <td>$condicion</td>
    <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha_stock]</td>
    <td class='text-center'>
        $boton_eliminar
    </td>
  </tr>";
      }
      echo "</tbody>";
      echo "</table>";
      echo "</div>";
      echo "</div>";
  } else {
      echo "<div class='callout callout-danger'><b>No se encontraron bandejas en stock...</b></div>";
  }
}
else if ($consulta == "busca_stock_actual") {
  $query = "SELECT 
  s.tipo_bandeja, 
  SUM(s.cantidad) as cantidad,
  s.condicion 
  FROM stock_bandejas s
  GROUP BY s.tipo_bandeja, s.condicion;
          ";

  $val = mysqli_query($con, $query);

  if (mysqli_num_rows($val) > 0) {

      echo "<div class='box box-primary'>";
      echo "<div class='box-header with-border'>";
      echo "<h3 class='box-title'>Stock Actual</h3>";
      echo "</div>";
      echo "<div class='box-body'>";
      echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
      echo "<thead>";
      echo "<tr>";
      echo "<th>Tipo Bandeja</th><th>Cantidad Disponible</th><th>Condición</th>";
      echo "</tr>";
      echo "</thead>";
      echo "<tbody>";

      while ($ww = mysqli_fetch_array($val)) {
        $aux = mysqli_query($con, "SELECT IFNULL(SUM(cantidad),0) as canti FROM stock_bandejas_retiros WHERE condicion = $ww[condicion] AND tipo_bandeja = $ww[tipo_bandeja]");
        $canti = $ww["cantidad"];
        if (mysqli_num_rows($aux) > 0) {
          $re = mysqli_fetch_assoc($aux);
          $canti = $canti - $re["canti"];
        }
        $condicion = $ww["condicion"] == 0 ? "<span class='text-secondary font-weight-bold'>USADAS</span>" : "<span class='text-primary font-weight-bold'>NUEVAS</span>";
        $cantidad = $canti <= 10 ? "<span class='text-danger font-weight-bold'>$canti</span>" : "<span class='font-weight-bold'>$canti</span>";
        echo "
          <tr class='text-center' style='cursor:pointer' x-tipo-bandeja='$ww[tipo_bandeja]' x-cantidad='$canti' x-condicion='$ww[condicion]'>
            <td>$ww[tipo_bandeja]</td>
            <td>$cantidad</td>
            <td>$condicion</td>
          </tr>";
      }
      echo "</tbody>";
      echo "</table>";
      echo "</div>";
      echo "</div>";
  } else {
      echo "<div class='callout callout-danger'><b>No se encontraron bandejas en stock...</b></div>";
  }
}
else if ($consulta == "guardar_bandejas") {
    $tipo_bandeja = $_POST["bandeja"];
    $condicion = $_POST["condicion"];
    $cantidad = mysqli_real_escape_string($con, $_POST["cantidad"]);

    try {
        $query = "INSERT INTO stock_bandejas
            (fecha, tipo_bandeja, condicion, cantidad, cantidad_original)
              VALUES
            (
              NOW(),
              '$tipo_bandeja',
              $condicion,
              '$cantidad',
              '$cantidad'  
            )";
        if (mysqli_query($con, $query)){
          echo "success";
        }
        else{
          print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        throw $th;
    }
}
else if ($consulta == "eliminar_bandejas"){
  $rowid = $_POST["rowid"];
  try {
      if (mysqli_query($con, "DELETE FROM stock_bandejas WHERE rowid = $rowid")){
        echo "success";
      }
      else{
        print_r(mysqli_error($con));
      }
      
  } catch (\Throwable $th) {
      //throw $th;
      echo "error: $th";
  }
}
else if ($consulta == "cargar_stock_bandejas"){
  $tipo_bandeja = $_POST["tipo"];
  $query = 
          "SELECT  (
            SELECT IFNULL(SUM(cantidad),0) as cantidad
            FROM stock_bandejas
            WHERE 
            tipo_bandeja = '$tipo_bandeja' AND condicion = 1
            ) - (
            SELECT IFNULL(SUM(sr.cantidad),0) as cantidad
            FROM stock_bandejas_retiros sr
            WHERE 
            sr.tipo_bandeja = '$tipo_bandeja' AND sr.condicion = 1
            ) AS nuevas,
            (
                SELECT IFNULL(SUM(cantidad),0) as cantidad
              FROM stock_bandejas
              WHERE 
              tipo_bandeja = '$tipo_bandeja' AND condicion = 0
            )-(
                SELECT IFNULL(SUM(sr.cantidad),0) as cantidad
              FROM stock_bandejas_retiros sr
              WHERE 
              sr.tipo_bandeja = '$tipo_bandeja' AND sr.condicion = 0
            ) AS usadas";

  $val = mysqli_query($con, $query);

  if (mysqli_num_rows($val) > 0) {
    $ww = mysqli_fetch_assoc($val);
    echo json_encode(array(
      "nuevas" => $ww["nuevas"],
      "usadas" => $ww["usadas"]
    ));
  }
  
}