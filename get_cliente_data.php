<?php
header('Content-Type: application/json; charset=utf-8');
require 'class_lib/sesionSecurity.php';
require 'class_lib/class_conecta_mysql.php';

$response = [
    'status' => 'error',
    'message' => 'Ocurrió un error inesperado.'
];

if (isset($_POST['id'])) {
    $con = mysqli_connect($host, $user, $password, $dbname);
    if (!$con) {
        $response['message'] = "Error de conexión: " . mysqli_connect_error();
        echo json_encode($response);
        exit;
    }
    mysqli_query($con, "SET NAMES 'utf8'");

    $id_cliente = $_POST['id'];

    $query = "SELECT
                nombre,
                razon_social,
                domicilio,
                domicilio2,
                telefono,
                mail,
                rut,
                comuna,
                UPPER(provincia) as provincia,
                UPPER(region) as region,
                id_vendedor
              FROM clientes
              WHERE id_cliente = ?";

    if ($stmt = mysqli_prepare($con, $query)) {
        mysqli_stmt_bind_param($stmt, "i", $id_cliente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            $response['status'] = 'success';
            $response['data'] = $row;
            unset($response['message']);
        } else {
            $response['message'] = 'Cliente no encontrado.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = "Error al preparar la consulta: " . mysqli_error($con);
    }
    mysqli_close($con);
} else {
    $response['message'] = 'No se proporcionó un ID de cliente.';
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
