<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

session_name("roel-erp");
session_start();

try {
    include './class_lib/class_conecta_mysql.php';
    include './class_lib/funciones.php';

    $con = mysqli_connect($host, $user, $password, $dbname);

    if (!$con) {
        throw new Exception("Error de conexión a la base de datos");
    }

    if (!isset($_POST['user']) || !isset($_POST['pass'])) {
        throw new Exception("Usuario y contraseña son requeridos");
    }

    $usuario = test_input($_POST['user']);
    $password = test_input($_POST['pass']);

    mysqli_query($con, "SET NAMES 'utf8'");

    // Primero buscar el usuario sin validar contraseña
    $query = "
        SELECT
            u.nombre,
            u.nombre_real,
            u.password,
            u.id,
            GROUP_CONCAT(p.modulo SEPARATOR ',') as modulos,
            u.inhabilitado
        FROM
            usuarios u
        LEFT JOIN
            permisos p ON p.id_usuario = u.id
        WHERE
            u.nombre='$usuario'
            AND u.tipo_usuario = 1
        GROUP BY
            u.nombre,
            u.nombre_real,
            u.password,
            u.id,
            u.inhabilitado
    ";

    $val = mysqli_query($con, $query);

    if (!$val) {
        throw new Exception("Error en la consulta: " . mysqli_error($con));
    }

    $passwordValid = false;
    $r = null;

    if (mysqli_num_rows($val) > 0) {
        $r = mysqli_fetch_assoc($val);

        // Verificar contraseña: primero texto plano (legacy), luego bcrypt (nuevo)
        if ($r['password'] === $password) {
            // Contraseña en texto plano (legacy)
            $passwordValid = true;
        } elseif (strlen($r['password']) >= 60 && password_verify($password, $r['password'])) {
            // Contraseña hasheada con bcrypt (nuevo desde la API)
            $passwordValid = true;
        }
    }

    if ($passwordValid && $r) {

        if ($r["inhabilitado"] == 1) {
            setcookie('roel-erp-usuario', '', time() - 3600, '/');
            setcookie('roel-erp-token', '', time() - 3600, '/');
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario Inhabilitado',
                'description' => 'Contacta al Administrador para solucionar el problema.'
            ]);
            exit;
        }

        // Login exitoso
        $_SESSION['nombre_de_usuario'] = $r['nombre'];
        $_SESSION['clave'] = $r['password'];
        $_SESSION['id_usuario'] = $r["id"];
        $_SESSION['nombre_real'] = $r["nombre_real"];
        $_SESSION['permisos'] = $r["modulos"];
        $_SESSION["arraypermisos"] = isset($r["modulos"]) ? explode(",", $r["modulos"]) : "";
        $token = sha1(uniqid("roel", TRUE));
        $_SESSION["roel-erp-token"] = $token;
        setcookie("roel-erp-usuario", $r['nombre'], time()+(60*60*24*30), '/');
        setcookie("roel-erp-token", $token, time()+(60*60*24*30), '/');

        echo json_encode([
            'status' => 'success',
            'redirect' => 'inicio.php'
        ]);

    } else {
        setcookie('roel-erp-usuario', '', time() - 3600, '/');
        setcookie('roel-erp-token', '', time() - 3600, '/');
        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales inválidas',
            'description' => 'Por favor verifique sus datos e intente nuevamente'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en el servidor',
        'description' => $e->getMessage()
    ]);
}
