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

if ($consulta == "get_table_sucursales") {
    $query = "SELECT
        s.id, s.nombre, s.direccion, s.telefono, s.notas, t.id as id_transportista, t.nombre as nombre_transportista
        FROM transportistas_sucursales s 
        INNER JOIN transportistas t
        ON t.id = s.id_transportista
        ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h5>Sucursales</h5>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "
            <th>ID</th>
            <th>Transportista</th>
            <th>Sucursal</th>
            <th>Dirección</th>
            <th>Teléfono</th>
            <th>Notas</th>
            <th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $tel = isset($ww["telefono"]) ? "'".$ww["telefono"]."'" : "null";
            $notas = isset($ww["notas"]) ? "'".$ww["notas"]."'" : "null";
            $onclick = "onclick=\"modalSucursal({
                id: $ww[id],
                id_transportista: $ww[id_transportista],
                nombre: '$ww[nombre]',
                direccion: '$ww[direccion]',
                telefono: $tel,
                notas: $notas,
            })\"";
            echo "<tr $onclick class='text-center' style='cursor:pointer;'>
                <td>$ww[id]</td>
                <td>$ww[nombre_transportista]</td>
                <td>$ww[nombre]</td>
                <td>$ww[direccion]</td>
                <td>$ww[telefono]</td>
                <td>$ww[notas]</td>
                <td>
                    <button onclick='eliminarSucursal(event, $ww[id])' class='btn btn-danger btn-sm'><i class='fa fa-trash'></i></button>
                </td>
            </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";

    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron sucursales ...</b></div>";
    }
}
else if ($consulta == "get_transportistas_select"){
    $query="SELECT id, nombre FROM transportistas ORDER BY nombre";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val)>0){
        while($re=mysqli_fetch_array($val)){
            $nombre = mysqli_real_escape_string($con, $re["nombre"]);
            echo "<option value='$re[id]' x-nombre='$nombre'>$re[nombre] ($re[id])</option>";
        }
    }
}
else if ($consulta == "get_table_transportistas") {
    $query = "SELECT id, nombre FROM transportistas ORDER BY id DESC";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while($ww=mysqli_fetch_array($val)){
            echo "<tr class='text-center'>
                <td>$ww[id]</td>
                <td>$ww[nombre]</td>
                <td>
                    <button onclick='eliminarTransportista($ww[id], \"$ww[nombre]\")' class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></button>
                </td>
            </tr>
            ";
        }
    } 
    else{
        echo "<tr class='text-center text-muted font-weight-bold'>
            <td colspan='3'>No hay Ítems registrados.</td>
        </tr>";
    }
}
else if ($consulta == "guardar_transportista"){
    
    $nombre = mysqli_real_escape_string($con, test_input($_POST["nombre"]));

    $query = "INSERT INTO transportistas (nombre) VALUES ('$nombre')";
    if (mysqli_query($con, $query)){
        echo "success";
    }
    else{
        echo mysqli_error($con);
    }
}
else if ($consulta == "eliminar_transportista"){
    $id = $_POST["id"];
    mysqli_autocommit($con, false);

    $query = "DELETE FROM transportistas_sucursales WHERE id_transportista = $id";
    if (!mysqli_query($con, $query)) {
        $error = mysqli_error($con);
    }

    $query = "DELETE FROM transportistas WHERE id = $id";
    if (!mysqli_query($con, $query)) {
        $error = mysqli_error($con);
    }

    if (strlen($error) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($error . " " . $query);
    }

    mysqli_close($con);
}
else if ($consulta == "guardar_sucursal"){
    $id_transportista = $_POST["id_transportista"];

    $id_sucursal = isset($_POST["id_sucursal"]) && strlen($_POST["id_sucursal"]) > 0 ? $_POST["id_sucursal"] : NULL;
    
    $nombre = mysqli_real_escape_string($con, test_input($_POST["nombre"]));
    $direccion = mysqli_real_escape_string($con, test_input($_POST["direccion"]));
    $notas = isset($_POST["notas"]) && strlen($_POST["notas"]) > 0 ? "'" . mysqli_real_escape_string($con, $_POST["notas"]) . "'" : "NULL";
    $telefono = isset($_POST["telefono"]) && strlen($_POST["telefono"]) > 0 ? "'" . mysqli_real_escape_string($con, $_POST["telefono"]) . "'" : "NULL";

    if (!isset($id_sucursal)){// ES NUEVO
        $query = "INSERT INTO transportistas_sucursales (
            id_transportista,
            nombre,
            direccion,
            notas,
            telefono
        ) VALUES (
            $id_transportista,
            '$nombre',
            '$direccion',
            $notas,
            $telefono
        )";
    }
    else{
        $query = "UPDATE transportistas_sucursales SET
            id_transportista = $id_transportista,
            nombre = '$nombre',
            direccion = '$direccion',
            telefono = $telefono,
            notas = $notas
            WHERE id = $id_sucursal
        ";
    }

    

    if (mysqli_query($con, $query)){
        echo "success";
    }
    else{
        echo mysqli_error($con);
    }
}
else if ($consulta == "eliminar_sucursal"){
    $id = $_POST["id"];
    mysqli_autocommit($con, false);

    $query = "DELETE FROM transportistas_sucursales WHERE id = $id";
    if (!mysqli_query($con, $query)) {
        $error = mysqli_error($con);
    }

    if (strlen($error) === 0) {
        if (mysqli_commit($con)) {
            echo "success";
        } else {
            mysqli_rollback($con);
        }
    } else {
        mysqli_rollback($con);
        print_r($error . " " . $query);
    }

    mysqli_close($con);
}
