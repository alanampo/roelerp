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
else if ($consulta == "pone_clientes"){
    $cadena="SELECT id_cliente, nombre, mail FROM clientes ORDER BY nombre";
    $val = mysqli_query($con, $cadena);
    if (mysqli_num_rows($val)>0){
        while($re=mysqli_fetch_array($val)){
            $nombre = mysqli_real_escape_string($con, $re["nombre"]);
            echo "<option x-email='$re[mail]' value='$re[id_cliente]' x-nombre='$nombre'>$re[nombre] ($re[id_cliente])</option>";
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
else if ($consulta == "pone_usuarios"){
    $cadena="SELECT id, nombre_real FROM usuarios ORDER BY nombre_real";
    $val = mysqli_query($con, $cadena);
    if (mysqli_num_rows($val)>0){
        while($re=mysqli_fetch_array($val)){
            $nombre = mysqli_real_escape_string($con, $re["nombre_real"]);
            echo "<option value='$re[id]'>$nombre ($re[id])</option>";
        }
    }
}
else if ($consulta == "cambiar_vendedor"){
    $id_cliente = $_POST["id_cliente"];
    $id_vendedor_nuevo = $_POST["id_vendedor_nuevo"];
    $id_vendedor_anterior = $_POST["id_vendedor_anterior"];
    $justificacion = mysqli_real_escape_string($con, $_POST["justificacion"]);
    $id_usuario_cambio = $_SESSION['id_usuario'];

    if (empty($id_vendedor_nuevo) || $id_vendedor_nuevo == 'default') {
        $id_vendedor_nuevo = NULL;
    }
    if (empty($id_vendedor_anterior) || $id_vendedor_anterior == 'default' || $id_vendedor_anterior == 'null') {
        $id_vendedor_anterior = NULL;
    }

    // Validar que se haya proporcionado una justificación solo si había vendedor anterior
    if ($id_vendedor_anterior !== NULL && strlen(trim($justificacion)) < 3) {
        die("La justificación debe tener al menos 3 caracteres");
    }

    mysqli_autocommit($con, false);

    try {
        // Actualizar cliente con nuevo vendedor
        if ($id_vendedor_nuevo !== NULL) {
            $query = "UPDATE clientes SET
                id_vendedor = $id_vendedor_nuevo,
                vendedor_anterior = " . ($id_vendedor_anterior !== NULL ? $id_vendedor_anterior : "NULL") . ",
                fecha_cambio_vendedor = NOW()
                WHERE id_cliente = $id_cliente";
        } else {
            $query = "UPDATE clientes SET
                id_vendedor = NULL,
                vendedor_anterior = " . ($id_vendedor_anterior !== NULL ? $id_vendedor_anterior : "NULL") . ",
                fecha_cambio_vendedor = NOW()
                WHERE id_cliente = $id_cliente";
        }

        if (!mysqli_query($con, $query)) {
            throw new Exception(mysqli_error($con));
        }

        // Registrar en historial
        $query_historial = "INSERT INTO historial_cambios_vendedor
            (id_cliente, id_vendedor_anterior, id_vendedor_nuevo, id_usuario_cambio, justificacion, fecha_cambio)
            VALUES (
                $id_cliente,
                " . ($id_vendedor_anterior !== NULL ? $id_vendedor_anterior : "NULL") . ",
                " . ($id_vendedor_nuevo !== NULL ? $id_vendedor_nuevo : "NULL") . ",
                $id_usuario_cambio,
                '$justificacion',
                NOW()
            )";

        if (!mysqli_query($con, $query_historial)) {
            throw new Exception(mysqli_error($con));
        }

        mysqli_commit($con);
        echo "success";

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo "Error: " . $e->getMessage();
    }
}
else if ($consulta == "actualizar_fechas_contacto"){
    // Ejecutar script de actualización de fechas
    include("actualizar_fecha_ultimo_contacto.php");
}
else if ($consulta == "desasignar_vendedores_inactivos"){
    // Ejecutar script de desasignación automática
    include("desasignar_vendedores_inactivos.php");
}
else if ($consulta == "obtener_historial_vendedor"){
    $id_cliente = $_POST["id_cliente"];

    $query = "SELECT
        h.id,
        h.fecha_cambio,
        u_anterior.nombre_real as vendedor_anterior,
        u_nuevo.nombre_real as vendedor_nuevo,
        u_cambio.nombre_real as usuario_cambio,
        h.justificacion
    FROM historial_cambios_vendedor h
    LEFT JOIN usuarios u_anterior ON h.id_vendedor_anterior = u_anterior.id
    LEFT JOIN usuarios u_nuevo ON h.id_vendedor_nuevo = u_nuevo.id
    LEFT JOIN usuarios u_cambio ON h.id_usuario_cambio = u_cambio.id
    WHERE h.id_cliente = $id_cliente
    ORDER BY h.fecha_cambio DESC";

    $val = mysqli_query($con, $query);
    $historial = array();

    if (mysqli_num_rows($val) > 0) {
        while ($row = mysqli_fetch_assoc($val)) {
            $historial[] = array(
                'fecha' => date('d/m/Y H:i', strtotime($row['fecha_cambio'])),
                'vendedor_anterior' => $row['vendedor_anterior'] ? $row['vendedor_anterior'] : 'Sin asignar',
                'vendedor_nuevo' => $row['vendedor_nuevo'] ? $row['vendedor_nuevo'] : 'Sin asignar',
                'usuario_cambio' => $row['usuario_cambio'],
                'justificacion' => $row['justificacion']
            );
        }
    }

    echo json_encode($historial);
}
?>