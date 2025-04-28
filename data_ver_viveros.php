<?php

include "./class_lib/sesionSecurity.php";
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';
header('Content-type: text/html; charset=utf-8');
$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

$consulta = $_POST["consulta"];

if ($consulta == "get_table_viveros") {
    mysqli_query($con, "SET NAMES 'utf8'");

    $cadena = "SELECT 
                v.id as id_vivero, 
                v.nombre as nombre, 
                v.domicilio as domicilio, 
                v.comuna,
                v.telefono, 
                v.email, 
                v.rut as rut
                FROM viveros v";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Nombre</th><th>Dirección</th><th>Teléfono</th><th>Email</th><th>R.U.T</th><th>Comuna</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $onclick = "onClick='modalVivero({
                id: \"$ww[id_vivero]\",
                nombre: \"$ww[nombre]\",
                domicilio: \"$ww[domicilio]\",
                telefono: \"$ww[telefono]\",
                rut: \"$ww[rut]\",
                comuna: \"$ww[comuna]\",
                email: \"$ww[email]\"
            })'";
            echo "<tr class='text-center' style='cursor:pointer;'>
                    <td $onclick style='color:#1F618D; font-weight:bold; font-size:16px;'>$ww[id_vivero]</td>
                    <td $onclick>$ww[nombre]</td>
                    <td $onclick>$ww[domicilio]</td>
                    <td $onclick>$ww[telefono]</td>
                    <td $onclick>$ww[email]</td>
                    <td $onclick>$ww[rut]</td>
                    <td $onclick>$ww[comuna]</td>
                    <td style='text-align: center;'>
                        <button class='btn btn-sm btn-danger fa fa-trash' onclick='eliminarVivero($ww[id_vivero], \"$ww[nombre]\")'></button>
                    </td>            
            </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron viveros...</b></div>";
    }
}  else if ($consulta == "eliminar_vivero") {
    $id_vivero = $_POST["id_vivero"];
    if (mysqli_query($con, "DELETE FROM viveros WHERE id = $id_vivero;")) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
}
else if ($consulta == "guardar_vivero"){
    $id = $_POST["id"];
    
    $nombre = mysqli_real_escape_string($con, test_input(ucwords($_POST["nombre"])));
    $domicilio = isset($_POST["domicilio"]) && strlen($_POST["domicilio"]) ? "'".mysqli_real_escape_string($con, test_input(ucwords($_POST["domicilio"])))."'" : "NULL";
    $comuna = isset($_POST["comuna"]) && strlen($_POST["comuna"]) ? "'".mysqli_real_escape_string($con, test_input(ucwords($_POST["comuna"])))."'" : "NULL";
    $telefono = isset($_POST["telefono"]) && strlen($_POST["telefono"]) ? "'".mysqli_real_escape_string($con, test_input($_POST["telefono"]))."'" : "NULL";
    $rut = isset($_POST["rut"]) && strlen($_POST["rut"]) ? "'".mysqli_real_escape_string($con, test_input($_POST["rut"]))."'" : "NULL";
    $email = isset($_POST["email"]) && strlen($_POST["email"]) ? "'".mysqli_real_escape_string($con, test_input(strtolower($_POST["email"])))."'" : "NULL";

    if (isset($id) && strlen($id)){ // EDITAR
        $query = "UPDATE viveros SET 
                    nombre = '$nombre',
                    domicilio = $domicilio,
                    telefono = $telefono,
                    rut = $rut,
                    comuna = $comuna,
                    email = $email
                WHERE id = $id
            ";
    }
    else{
        $query = "INSERT INTO viveros (
            nombre,
            domicilio,
            telefono,
            rut,
            comuna,
            email
        ) VALUES (
            '$nombre',
            $domicilio,
            $telefono,
            $rut,
            $comuna,
            $email
        )";
    }
    if (mysqli_query($con, $query)){
        echo "success";
    }
    else{
        echo mysqli_error($con)." - ".$query;
    }
}
else if ($consulta == "get_viveros_select") {
    $query = "SELECT
            vi.id,
            vi.nombre
        FROM viveros vi
        ORDER BY vi.nombre";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            echo "<option x-nombre='$ww[nombre]' value='$ww[id]'>$ww[nombre] ($ww[id])</option>";
        }
    }
} else if ($consulta == "get_datos_vivero") {
    $id = $_POST["id"];
    
    $query = "SELECT rut, nombre, domicilio, comuna FROM viveros WHERE id = $id
            ";

    $val = mysqli_query($con, $query);

   
    if (mysqli_num_rows($val) > 0) {
        echo json_encode(mysqli_fetch_assoc($val));
    }
}
