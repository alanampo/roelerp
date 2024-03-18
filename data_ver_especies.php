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

if ($consulta == "busca_especies") {
    $id_especiefiltro = $_POST['filtro'];
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $cadena = "SELECT e.id as id_especie, t.id as id_tipo, t.nombre as nombre_tipo,
          e.nombre as nombre_especie, t.codigo, e.dias_produccion, e.eliminada  
          FROM especies_provistas e INNER JOIN tipos_producto t ON t.id = e.id_tipo";

    if ($id_especiefiltro != null) {
        $cadena .= " WHERE e.eliminada IS NULL AND id_tipo = " . $id_especiefiltro;
    }
    else{
        $cadena .= " WHERE e.eliminada IS NULL AND t.codigo IN ('HE', 'HS')";
    }

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Tipo</th><th>Especie</th><th>Días en Producción</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_especie = $ww['id_especie'];
            $tipo = $ww['nombre_tipo'];
            $especie = $ww['nombre_especie'];
            $btneliminar = $_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash' onClick='eliminar($id_especie)'></button>" : "";
            echo "
    <tr class='text-center' style='cursor:pointer' x-codigo-tipo='$ww[codigo]' x-dias-produccion='$ww[dias_produccion]' x-id='$id_especie' x-id-tipo='$ww[id_tipo]' x-nombre='$especie'>
      <td class='clickable'>$tipo ($ww[codigo])</td>
      <td class='clickable'>$especie</td>
      <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ww[dias_produccion]</td>
      
      <td class='text-center'>
          $btneliminar
      </td>
    </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron productos en la base de datos...</b></div>";
    }
}  else if ($consulta == "editar_especie") {
    $id_especie = $_POST['id_especie'];
    $nombre = $_POST["nombre"];
    $dias_produccion = $_POST["dias_produccion"] == NULL ? "NULL" : $_POST["dias_produccion"];
   
    try {
        $query = "UPDATE especies_provistas SET nombre = UPPER('$nombre'), dias_produccion = '$dias_produccion' WHERE id = $id_especie";
        if (mysqli_query($con, $query)){
            echo "success";
        }
        else{
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "eliminar_especie") {
    try {
        $id_especie = $_POST["id_especie"];
        if (mysqli_query($con, "UPDATE especies_provistas SET eliminada = 1 WHERE id = $id_especie;")){
            echo "success";
        }
        else{
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
}
else if ($consulta == "agregar_especie") {
    $nombre = $_POST['nombre'];
    $id_tipo = $_POST["id_tipo"];
    $dias_produccion = $_POST["dias_produccion"] == NULL ? "NULL" : $_POST["dias_produccion"];
    try {
        $val = mysqli_query($con, "SELECT * FROM especies_provistas WHERE nombre = UPPER('$nombre') AND id_tipo = $id_tipo;");
        if ($val && mysqli_num_rows($val) > 0) {
            echo "Ya existe una especie con ese nombre!";
        } else {
            $query = "INSERT INTO especies_provistas (nombre, id_tipo, dias_produccion) VALUES (UPPER('$nombre'), '$id_tipo', '$dias_produccion');";
            if (mysqli_query($con, $query)){
                echo "success";
            }
            else{
                print_r(mysqli_error($con));
            }
        }

    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
}
else if ($consulta == "cargar_dias_produccion") {
    $tipo = $_POST["tipo"];
    $id_producto = $_POST["id_producto"];
    try {
        $val = mysqli_query($con, "SELECT dias_produccion FROM ".($tipo == "variedad" ? "variedades_producto" : "especies_provistas")." WHERE id = $id_producto;");
        if (mysqli_num_rows($val) > 0) {
            $re = mysqli_fetch_assoc($val);
            echo "dias:$re[dias_produccion]";
        } 
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
}
