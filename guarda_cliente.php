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

$tipo = $_POST['tipo'];
$global_id_cliente = $_POST['id_cliente'];
$nombre = $_POST['nombre'];
$domicilio = $_POST['domicilio'];

if (strlen(trim($domicilio)) == 0) {
    $domicilio = null;
}
$telefono = $_POST['telefono'];
if (strlen(trim($telefono)) == 0) {
    $telefono = null;
}
$mail = $_POST['mail'];
if (strlen(trim($mail)) == 0) {
    $mail = null;
}

$rut = $_POST['rut'];
if (strlen(trim($rut)) == 0) {
    $rut = null;
}

$razon_social = $_POST['razonSocial'];
if (strlen(trim($razon_social)) == 0) {
    $razon_social = null;
}

$comuna = $_POST['comuna'];

if ($tipo == "agregar") {
    $query = "SELECT * FROM clientes WHERE rut = '$rut' LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0){
        die("Ya existe un Cliente con ese RUT");
    }
    else{
        $query = "INSERT INTO clientes (nombre, domicilio, telefono, mail, rut, comuna, razon_social) VALUES (UPPER('$nombre'), UPPER('$domicilio'), '$telefono', LOWER('$mail'), UPPER('$rut'), $comuna, UPPER('$razon_social'));";
    }
} else if ($tipo == "editar") {
    $query = "SELECT * FROM clientes WHERE rut = '$rut' AND id_cliente <> $global_id_cliente LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0){
        die("Ya existe un Cliente con ese RUT");
    }
    else{
        $query = "UPDATE clientes SET nombre = UPPER('$nombre'), domicilio = UPPER('$domicilio'), telefono = '$telefono', mail = LOWER('$mail'), rut = UPPER('$rut'), comuna = $comuna, razon_social = UPPER('$razon_social') WHERE id_cliente = '$global_id_cliente';";
    }    
}

if (mysqli_query($con, $query)) {
    echo "success";
} else {
    print_r(mysqli_error($con));
}
