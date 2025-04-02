<?php
session_name("roel-erp");
session_start();

try {
  include './class_lib/class_conecta_mysql.php';
  include './class_lib/funciones.php';

  $con = mysqli_connect($host, $user, $password, $dbname);
  // Check connection
  if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $usuario = test_input($_POST['user']);
  $password = test_input($_POST['pass']);

  mysqli_query($con, "SET NAMES 'utf8'");
  $query = "SELECT
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
AND BINARY u.password='$password'
AND u.tipo_usuario = 1
GROUP BY
u.nombre,
u.nombre_real,
u.password,
u.id,
u.inhabilitado";
  $val = mysqli_query($con, $query);
  if (mysqli_num_rows($val) > 0) {
    $r = mysqli_fetch_assoc($val);
    if ($r["nombre"] != null) {
      if ($r["inhabilitado"] != 1) {
        $_SESSION['nombre_de_usuario'] = $r['nombre'];
        $_SESSION['clave'] = $r['password'];
        $_SESSION['id_usuario'] = $r["id"];
        $_SESSION['nombre_real'] = $r["nombre_real"];
        $_SESSION['permisos'] = $r["modulos"];
        $_SESSION["arraypermisos"] = isset($r["modulos"]) ? explode(",", $r["modulos"]) : "";
        $nombre = $r['nombre'];
        $password = $r['password'];
        $token = sha1(uniqid("roel", TRUE));
        $_SESSION["roel-erp-token"] = $token;
        setcookie("roel-erp-usuario", $r['nombre'], time() + (60 * 60 * 24 * 30), '/');
        setcookie("roel-erp-token", $token, time() + (60 * 60 * 24 * 30), '/');

        echo "
        <script>
          document.location.href = 'inicio.php';
        </script>
        ";
      } else {
        echo "
            <script>
            swal(
              'Usuario Inhabilitado',
              'Contacta al Administrador para solucionar el problema.',
              'error'
            );
            </script>";
        setcookie('roel-erp-usuario', '', time() - 3600, '/');
        setcookie('roel-erp-token', '', time() - 3600, '/');
      }

    } else {
      echo "<script>
        swal(
          'Nombre o contraseña inválidos',
          'Por favor verifique sus datos e intente nuevamente',
          'error'
        );
        </script>";
      setcookie('roel-erp-usuario', '', time() - 3600, '/');
      setcookie('roel-erp-token', '', time() - 3600, '/');
    }

  } else {
    echo "<script>
        swal(
          'Nombre o contraseña inválidos',
          'Por favor verifique sus datos e intente nuevamente',
          'error'
        );
      </script>";
    setcookie('roel-erp-usuario', '', time() - 3600, '/');
    setcookie('roel-erp-token', '', time() - 3600, '/');
  }

} catch (\Throwable $th) {
  echo $th->getMessage();
  throw $th;
}

