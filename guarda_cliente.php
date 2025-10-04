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
$domicilio2 = $_POST['domicilio2'];
$region = $_POST['region'];
$provincia = $_POST['provincia'];

if (strlen(trim($domicilio)) == 0) {
    $domicilio = null;
}
if (strlen(trim($domicilio2)) == 0) {
    $domicilio2 = null;
}
if (strlen(trim($region)) == 0) {
    $region = null;
}
if (strlen(trim($provincia)) == 0) {
    $provincia = null;
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

$id_vendedor = $_POST['id_vendedor'];
if (strlen(trim($id_vendedor)) == 0 || $id_vendedor == 'default') {
    $id_vendedor = null;
}

if ($tipo == "agregar") {
    $query = "SELECT * FROM clientes WHERE rut = '$rut' LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0){
        die("Ya existe un Cliente con ese RUT");
    }
    else{
        if ($id_vendedor !== null) {
            $query = "INSERT INTO clientes (nombre, domicilio, domicilio2, telefono, mail, rut, comuna, razon_social, region, provincia, id_vendedor) VALUES (UPPER('$nombre'), UPPER('$domicilio'), UPPER('$domicilio2'), '$telefono', LOWER('$mail'), UPPER('$rut'), $comuna, UPPER('$razon_social'), UPPER('$region'), UPPER('$provincia'), $id_vendedor);";
        } else {
            $query = "INSERT INTO clientes (nombre, domicilio, domicilio2, telefono, mail, rut, comuna, razon_social, region, provincia) VALUES (UPPER('$nombre'), UPPER('$domicilio'), UPPER('$domicilio2'), '$telefono', LOWER('$mail'), UPPER('$rut'), $comuna, UPPER('$razon_social'), UPPER('$region'), UPPER('$provincia'));";
        }
    }
} else if ($tipo == "editar") {
    $query = "SELECT * FROM clientes WHERE rut = '$rut' AND id_cliente <> $global_id_cliente LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0){
        die("Ya existe un Cliente con ese RUT");
    }
    else{
        // En modo edición NO se actualiza el vendedor (se usa el modal de cambio de vendedor para eso)
        $query = "UPDATE clientes SET nombre = UPPER('$nombre'), domicilio = UPPER('$domicilio'), domicilio2 = UPPER('$domicilio2'), telefono = '$telefono', mail = LOWER('$mail'), rut = UPPER('$rut'), comuna = $comuna, razon_social = UPPER('$razon_social'), region = UPPER('$region'), provincia = UPPER('$provincia') WHERE id_cliente = '$global_id_cliente';";
    }
}

if (mysqli_query($con, $query)) {
    echo "success";
} else {
    print_r(mysqli_error($con));
}
