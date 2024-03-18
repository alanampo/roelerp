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

if ($consulta == "busca_usuarios") {
    $cadena = "SELECT u.id, u.nombre, u.nombre_real, GROUP_CONCAT(p.modulo SEPARATOR ', ') as modulos, u.password, u.tipo_usuario, u.inhabilitado FROM  usuarios u LEFT JOIN permisos p ON p.id_usuario = u.id GROUP BY u.id HAVING u.id <> 1 AND u.tipo_usuario = 1 ORDER BY u.nombre;";
    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Id</th><th>Usuario</th><th>Nombre Real</th><th>Permisos</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            echo "<tr style='cursor:pointer;'>";
            echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", \"$ww[nombre_real]\", \"$ww[modulos]\", 1)' style='text-align: center; color:#1F618D; font-weight:bold; font-size:16px;'>$ww[id]</td>";
            echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", \"$ww[nombre_real]\", \"$ww[modulos]\", 1)' style='text-align: center;font-weight:bold;font-size:16px;'>$ww[nombre]</td>";
            echo "<td onClick='ModificarUsuario($ww[id], \"$ww[password]\", \"$ww[nombre]\", \"$ww[nombre_real]\", \"$ww[modulos]\", 1)' style='text-align: center;text-transform:capitalize;font-weight:bold;font-size:16px;'>$ww[nombre_real]</td>";
            echo "<td style='text-align: center;font-weight:bold;font-size:16px;'>$ww[modulos]</td>";
            echo "<td style='text-align: center;font-weight:bold;font-size:16px;'>" .
                ($ww["inhabilitado"] == 1 ?
                "<button onclick='toggleUsuario($ww[id], 0)' class='btn btn-danger btn-sm'><i class='fa fa-times'></i> INHABILITADO</button>" :
                "<button onclick='toggleUsuario($ww[id], 1)' class='btn btn-success btn-sm'><i class='fa fa-check'></i> ACTIVO</button>"
            ) . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron usuarios en la base de datos...</b></div>";
    }

} else if ($consulta == "toggle_usuario") {
    $id_usuario = $_POST["id_usuario"];
    $inhabilitado = $_POST["inhabilitado"];
    try {
        if (mysqli_query($con, "UPDATE usuarios SET inhabilitado = $inhabilitado WHERE id = $id_usuario;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        throw $th;
    }
} else if ($consulta == "agregar") {
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $nombre_real = mysqli_real_escape_string($con, $_POST['nombre_real']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $permisos = json_decode($_POST["permisos"]);

    $query = "SELECT * FROM usuarios WHERE nombre = '$nombre'";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        echo "yaexiste";
    } else {
        $inicial = $nombre[0];
        $query = "SELECT * FROM usuarios WHERE iniciales = '$inicial'";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            $inicial = $nombre[0] . $nombre[1];
        }

        $usuario = mysqli_query($con, "SELECT (IFNULL(MAX(id),0)+1) as id_usuario FROM usuarios;");
        if (mysqli_num_rows($usuario) > 0) {
            $id_usuario = mysqli_fetch_assoc($usuario)["id_usuario"];
            mysqli_autocommit($con, false);
            $errors = array();
            $query = "INSERT INTO usuarios (nombre, nombre_real, password, tipo_usuario, iniciales) VALUES (LOWER('$nombre'), '$nombre_real', '$password', 1, UPPER('$inicial'));";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }

            for ($i = 0; $i < count($permisos); $i++) {
                $modulo = $permisos[$i];
                $query = "INSERT INTO permisos (id_usuario, modulo) VALUES ($id_usuario, '$modulo');";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con);
                }
            }
            if (count($errors) === 0) {
                if (mysqli_commit($con)) {
                    echo "success";
                } else {
                    mysqli_rollback($con);
                }
            } else {
                mysqli_rollback($con);
                print_r($errors);
            }
            mysqli_close($con);
        }

    }
} else if ($consulta == "editar") {
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $nombre_real = mysqli_real_escape_string($con, $_POST['nombre_real']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $permisos = json_decode($_POST["permisos"]);
    $id_usuario = $_POST["id_usuario"];

    $query = "SELECT * FROM usuarios WHERE nombre = '$nombre' AND id <> $id_usuario;";

    $val = mysqli_query($con, $query);
    $errors = array();

    if (mysqli_num_rows($val) > 0) {
        echo "yaexiste";
    } else {
        mysqli_autocommit($con, false);
        $query = "UPDATE usuarios SET nombre = LOWER('$nombre'), nombre_real = '$nombre_real', password = '$password' WHERE id = $id_usuario;";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }
        $query = "DELETE FROM permisos WHERE id_usuario = $id_usuario";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        for ($i = 0; $i < count($permisos); $i++) {
            $modulo = $permisos[$i];
            $query = "INSERT INTO permisos (id_usuario, modulo) VALUES ($id_usuario, '$modulo');";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con);
            }
        }
        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_rollback($con);
            print_r($errors);
        }
        mysqli_close($con);
    }
}
