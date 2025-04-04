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

if ($consulta == "busca_variedades") {
    $id_variedadfiltro = $_POST['filtro'];
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $cadena = "SELECT v.id as id_variedad, t.id as id_tipo, t.nombre as nombre_tipo,
          v.nombre as nombre_variedad, t.codigo, v.precio, v.id_interno, v.dias_produccion 
          FROM variedades_producto v INNER JOIN tipos_producto t ON t.id = v.id_tipo";

    if ($id_variedadfiltro != null) {
        $cadena .= " WHERE v.eliminada IS NULL AND id_tipo = " . $id_variedadfiltro;
    }
    else{
        $cadena.=" WHERE v.eliminada IS NULL";
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
        echo "<th>Tipo</th><th>Variedad</th><th>Precio</th><th>Precio c/ IVA</th><th>Días en Producción</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_variedad = $ww['id_variedad'];
            $tipo = $ww['nombre_tipo'];
            $variedad = $ww['nombre_variedad'];
            $precio = $ww['precio'];
            $precio_iva = number_format(round((float) $ww['precio'] * 1.19, 0, PHP_ROUND_HALF_UP), 2);
            $btneliminar = $_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash' onClick='eliminar($id_variedad)'></button>" : "";
            echo "
    <tr class='text-center' style='cursor:pointer' x-codigo-tipo='$ww[codigo]' x-id-interno='$ww[id_interno]' x-dias-produccion='$ww[dias_produccion]' x-id='$id_variedad' x-id-tipo='$id_tipo' x-precio='$precio' x-precio-iva='$precio_iva' x-nombre='$variedad'>
      <td class='clickable'>$tipo $ww[id_interno]</td>
      <td class='clickable'>$variedad</td>
      <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ $precio</td>
      <td class='clickable' style='font-size: 1.1em; font-weight:bold;'>$ $precio_iva</td>
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
} else if ($consulta == "agregar_variedad") {
    $nombre = mysqli_real_escape_string($con, $_POST['nombre']);
    $codigo = mysqli_real_escape_string($con, $_POST['codigo']);
    $dias_produccion = $_POST["dias_produccion"] == NULL ? "NULL" : $_POST["dias_produccion"];
    $precio = $_POST['precio'];
    $id_tipo = $_POST["id_tipo"];
    try {
        $val = mysqli_query($con, "SELECT * FROM variedades_producto WHERE nombre = UPPER('$nombre') AND id_tipo = $id_tipo;");
        if (mysqli_num_rows($val) > 0) {
            echo "YA EXISTE UNA VARIEDAD CON ESE NOMBRE!";
        } else {
            $val = mysqli_query($con, "SELECT * FROM variedades_producto WHERE id_tipo = $id_tipo AND id_interno = $codigo;");
            if (mysqli_num_rows($val) > 0) {
                echo "EL CÓDIGO INGRESADO YA ESTÁ EN USO. ELIGE OTRO.";
            } else {
                $query = "INSERT INTO variedades_producto (nombre, precio, id_tipo, id_interno, dias_produccion) VALUES (UPPER('$nombre'), '$precio', '$id_tipo', '$codigo', '$dias_produccion');";
                if (mysqli_query($con, $query)){
                    echo "success";
                }
                else{
                    print_r(mysqli_error($con));
                }
            }

        }
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "editar_variedad") {
    $id_variedad = $_POST['id_variedad'];
    $nombre = $_POST["nombre"];
    $precio = $_POST["precio"];
    $dias_produccion = $_POST["dias_produccion"] == NULL ? "NULL" : $_POST["dias_produccion"];
   
    try {
        $query = "UPDATE variedades_producto SET nombre = UPPER('$nombre'), precio = '$precio', dias_produccion = '$dias_produccion' WHERE id = $id_variedad";
        if (mysqli_query($con, $query)){
            echo "success";
        }
        else{
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "eliminar_variedad") {
    try {
        $id_variedad = $_POST["id_variedad"];
        if (mysqli_query($con, "UPDATE variedades_producto SET eliminada = 1 WHERE id = $id_variedad;")){
            echo "success";
        }
        else{
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "busca_variedades_select") {
    try {
        $id_tipo = $_POST["id_tipo"];
        $cadena = "SELECT v.id, v.id_interno, v.nombre, ROUND(v.precio) as precio, ROUND(v.precio_detalle) as precio_detalle, t.codigo 
                   FROM variedades_producto v 
                   INNER JOIN tipos_producto t ON t.id = v.id_tipo 
                   WHERE v.eliminada IS NULL AND v.id_tipo = $id_tipo 
                   ORDER BY v.id_interno ASC";
        $val = mysqli_query($con, $cadena);
        
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                // Consulta para obtener los atributos de la variedad
                $id_variedad = $re['id'];
                $cadena_atributos = "SELECT av.valor 
                                    FROM atributos_valores av
                                    INNER JOIN atributos_valores_variedades avv ON avv.id_atributo_valor = av.id
                                    INNER JOIN atributos at ON at.id = av.id_atributo
                                    WHERE avv.id_variedad = $id_variedad AND at.visible_factura = 1
                                    ORDER BY av.id";
                
                $val_atributos = mysqli_query($con, $cadena_atributos);
                $atributos = array();
                
                while ($atr = mysqli_fetch_array($val_atributos)) {
                    $atributos[] = $atr['valor'];
                }
                
                // Construir el nombre con los atributos
                $nombre_completo = $re['nombre'];
                if (!empty($atributos)) {
                    $nombre_completo .= ' ' . implode(' ', $atributos);
                }
                
                $id_interno = str_pad($re["id_interno"], 2, '0', STR_PAD_LEFT);
                $precio = $re["precio"] != null ? "- $$re[precio]" : "";
                $nombre_escaped = mysqli_real_escape_string($con, $nombre_completo);
                
                echo "<option x-precio='$re[precio]' x-precio-detalle='$re[precio_detalle]' x-codigo='$re[codigo]' x-nombre='$nombre_escaped' x-codigofull='$re[codigo]$id_interno' value='$re[id]'>$nombre_completo ($re[codigo]$id_interno) $precio</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "busca_especies_select") {
    try {
        $id_tipo = $_POST["id_tipo"];
        $cadena = "SELECT e.id, e.nombre FROM especies_provistas e INNER JOIN tipos_producto v ON v.id = e.id_tipo WHERE e.eliminada IS NULL AND e.id_tipo = $id_tipo order by e.id ASC;";
        $val = mysqli_query($con, $cadena);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                echo "<option x-nombre='$re[nombre]' value='$re[id]'>$re[nombre] ($re[id])</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "agregar_especie") {
    $nombre = $_POST['nombre'];
    $id_tipo = $_POST["id_tipo"];
    $dias_produccion = $_POST["dias_produccion"] == NULL ? "NULL" : $_POST["dias_produccion"];
    try {
        $val = mysqli_query($con, "SELECT * FROM especies_provistas WHERE nombre = UPPER('$nombre') AND id_tipo = $id_tipo;");
        if (mysqli_num_rows($val) > 0) {
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
