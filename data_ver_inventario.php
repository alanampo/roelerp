<?php
include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

if (!isset($dbname)) {
    die("La base de Datos no fue correctamente configurada.");
}

$dbName = $dbname;
$con = mysqli_connect($host, $user, $password, $dbName);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$consulta = $_POST["consulta"];
mysqli_query($con, "SET NAMES 'utf8'");

if ($consulta == "busca_tipos") {
    $cadena = "SELECT t.id, t.nombre, t.codigo,
     (SELECT GROUP_CONCAT(CONCAT(ap.id, '|', ap.nombre,'|', IFNULL(ap.tipo_dato, 'null'), '|', IFNULL(ap.id_tipo_atributo, 'null'), '|', IFNULL(at.nombre, 'null')))
                    FROM
                    atributos_producto ap
                    LEFT JOIN atributos_tipos at ON ap.id_tipo_atributo = at.id
                    WHERE ap.id_tipo_producto = t.id) as atributos_data,
    (SELECT GROUP_CONCAT(ap.nombre SEPARATOR '<br>')
                    FROM
                    atributos_producto ap
                    WHERE ap.id_tipo_producto = t.id) as atributos

     FROM tipos_producto t
     GROUP BY t.id
     ORDER BY nombre;";
    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Nombre</th><th>Atributos</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id = $ww['id'];
            $codigo = $ww["codigo"];
            $tipo = $ww['nombre'];
            $btneliminar = "<button class='btn btn-danger fa fa-trash' onClick='eliminarTipo($id)'></button>";
            $atributos = isset($ww["atributos_data"]) ? $ww["atributos_data"] : "null";
            $onclick = "onclick='modalTipo({id: $id, nombre: \"$tipo\", codigo: \"$codigo\", atributos: \"$atributos\"})'";
            echo "<tr style='cursor:pointer; '>";
            echo "<td $onclick style='text-align: center; width:80px;color:#1F618D;font-weight:bold;' x-id='$id' x-codigo='$codigo'>
                    <span class='d-none'>$id</span>
                    <span style='font-size:1.1em'>$codigo</span><br>
                    <span style='color:grey;font-size:0.8em;'>$id</span>
                 </td>";

            echo "<td $onclick style='text-align: center; font-size:1em;font-weight:bold;'>$tipo</td>";
            echo "<td $onclick class='text-center'>$ww[atributos]</td>";
            echo "<td class='text-center'>$btneliminar</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron productos en la base de datos...</b></div>";
    }
} else if ($consulta == "agregar_tipo") {
    $nombre = $_POST['nombre'];
    $codigo = $_POST['codigo'];
    $table = ($_POST["tab"] == "tipos" ? "producto" : "servicio");
    try {
        $val = mysqli_query($con, "SELECT * FROM tipos_$table WHERE nombre = UPPER('$nombre');");
        if ($val && mysqli_num_rows($val) > 0) {
            echo "Ya existe un Tipo con ese nombre!";
        } else {
            $val = mysqli_query($con, "SELECT * FROM tipos_$table WHERE codigo = UPPER('$codigo');");
            if ($val && mysqli_num_rows($val) > 0) {
                echo "Ya existe un Tipo con ese código!";
            } else {
                $errors = array();
                mysqli_autocommit($con, false);

                $query = "INSERT INTO tipos_$table (nombre, codigo) VALUES (UPPER('$nombre'), UPPER('$codigo'));";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                $id_tipo = mysqli_insert_id($con);

                $atributos = (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) ? json_decode($_POST["atributos"], true) : null;

                if (isset($atributos) && count($atributos) > 0) {
                    for ($i = 0; $i < count($atributos); $i++) {
                        $nombre = $atributos[$i]["nombre"];
                        $tipo = $atributos[$i]["tipoDato"];

                        if (strpos($tipo, "atr-") !== false) { // ES ATRIBUTO SELECCIONABLE
                            $id_tipo_atributo = str_replace("atr-", "", $tipo);
                            $query = "INSERT INTO atributos_$table (id_tipo_$table, nombre, id_tipo_atributo) VALUES
                                (
                                    $id_tipo,
                                    UPPER('$nombre'),
                                    $id_tipo_atributo
                                );";
                        } else {
                            $query = "INSERT INTO atributos_$table (id_tipo_$table, tipo_dato, nombre) VALUES
                                (
                                    $id_tipo,
                                    UPPER('$tipo'),
                                    UPPER('$nombre')
                                );";
                        }

                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
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
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "editar_tipo") {
    $id_tipo = $_POST['id_tipo'];
    $nombre = $_POST["nombre"];
    $codigo = $_POST["codigo"];
    $table = ($_POST["tab"] == "tipos" ? "producto" : "servicio");
    try {
        $val = mysqli_query($con, "SELECT * FROM tipos_$table WHERE nombre = UPPER('$nombre') AND id <> $id_tipo;");
        if ($val && mysqli_num_rows($val) > 0) {
            echo "error: Ya existe un Tipo con ese nombre!";
        } else {
            $val = mysqli_query($con, "SELECT * FROM tipos_$table WHERE codigo = UPPER('$codigo') AND codigo <> '$codigo';");
            if ($val && mysqli_num_rows($val) > 0) {
                echo "error: Ya existe un Tipo con ese código!";
            } else {
                $errors = array();
                mysqli_autocommit($con, false);

                $query = "UPDATE tipos_$table SET nombre = UPPER('$nombre'), codigo = UPPER('$codigo') WHERE id = $id_tipo";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                $atributos = (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) ? json_decode($_POST["atributos"], true) : null;

                if (isset($atributos) && count($atributos) > 0) {
                    for ($i = 0; $i < count($atributos); $i++) {
                        $nombre = $atributos[$i]["nombre"];
                        $tipo = $atributos[$i]["tipoDato"];
                        if (strpos($tipo, "atr-") !== false) { // ES ATRIBUTO SELECCIONABLE
                            $id_tipo_atributo = str_replace("atr-", "", $tipo);
                            $query = "INSERT INTO atributos_$table (id_tipo_$table, nombre, id_tipo_atributo) VALUES
                                (
                                    $id_tipo,
                                    UPPER('$nombre'),
                                    $id_tipo_atributo
                                );";
                        } else {
                            $query = "INSERT INTO atributos_$table (id_tipo_$table, tipo_dato, nombre) VALUES
                                (
                                    $id_tipo,
                                    UPPER('$tipo'),
                                    UPPER('$nombre')
                                );";
                        }

                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
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
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "busca_tipos_select") {
    try {
        $table = ($_POST["tab"] == "productos" ? "producto" : "servicio");
        $cadena = "select id, nombre, codigo from tipos_$table order by nombre ASC";

        $val = mysqli_query($con, $cadena);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                echo "<option value='$re[id]' x-codigo='$re[codigo]' x-nombre='$re[nombre]'>$re[nombre] ($re[codigo])</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "busca_productos") { // PRODUCTOS Y SERVICIOS
    $id_productofiltro = $_POST['filtro'];
    $thatr = "";
    $tdatr = "";
    $arrayAtr = [];
    $tbl = ($_POST["tab"] == "productos") ? "producto" : "servicio";
    if (isset($id_productofiltro) && !empty($id_productofiltro)) {
        $query = "
        SELECT ap.nombre, ap.id
        FROM
        atributos_$tbl ap
        WHERE ap.id_tipo_$tbl = $id_productofiltro
        ";
        $valatr = mysqli_query($con, $query);

        if (mysqli_num_rows($valatr)) {
            while ($atr = mysqli_fetch_array($valatr)) {
                $thatr .= "<th class='atr-$atr[id]'>$atr[nombre]</th>";
                $tdatr .= "<td></td>";
                array_push($arrayAtr, array(
                    "id" => $atr["id"],
                    "nombre" => $atr["nombre"],
                ));
            }
        }
    }

    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");

    $cadena = "SELECT v.id as id_producto,
            t.id as id_tipo,
            t.nombre as nombre_tipo,
            v.nombre as nombre_producto,
            v.imagen,
            t.codigo,
            v.id_interno,
            (SELECT IFNULL(SUM(i.cantidad),0) FROM pys_ingresos i
        WHERE i.id_$tbl = v.id) as ingresos,
        (SELECT IFNULL(SUM(e.cantidad),0) FROM pys_egresos e
        WHERE e.id_$tbl = v.id) as egresos,
        (SELECT GROUP_CONCAT(
            CONCAT(apv.id_atributo, '|',
                   IFNULL(apv.valor_varchar, 'null'), '|',
                   IFNULL(apv.valor_int, 'null'), '|',
                   IFNULL(apv.valor_decimal, 'null'), '|',
                   IFNULL(apv.valor_date, 'null'), '|',
                   IFNULL(apv.valor_text, 'null')
                   )
            )
                    FROM
                    atributos_$tbl" . "_valores apv
                    WHERE apv.id_$tbl = v.id) as atributos_data,

        (SELECT GROUP_CONCAT(CONCAT(atv.nombre, '|', apys.id))
                    FROM
                    atributos_$tbl" . "_valores_seleccionados apvs
                    INNER JOIN atributos_tipos_valores atv ON apvs.id_atributo_tipo_valor = atv.id
                    INNER JOIN atributos_$tbl" . "_valores apysv ON apysv.id = apvs.id_atributo_$tbl" . "_valor
                    INNER JOIN atributos_$tbl apys ON apys.id = apysv.id_atributo
                    WHERE
                    apvs.id_$tbl = v.id) as atributos_select_data
        


        FROM $_POST[tab] v INNER JOIN tipos_$tbl t ON t.id = v.id_tipo
        ";

    if ($id_productofiltro != null) {
        $cadena .= " WHERE v.eliminado IS NULL AND id_tipo = " . $id_productofiltro;
    } else {
        $cadena .= " WHERE v.eliminado IS NULL";
    }

    $val = mysqli_query($con, $cadena);
    $colatributos = "";
    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th></th><th style='text-transform:capitalize'>$tbl</th><th>Cantidad Inventario</th><th>Precio</th><th>Precio IVA</th>$thatr<th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            if ($id_productofiltro != null) {
                if (isset($ww["atributos_data"])) {
                    $tdatr = "";
                    $tmparr = explode(",", $ww["atributos_data"]);
                    for ($i = 0; $i < count($arrayAtr); $i++) {
                        $valu = "";
                        foreach ($tmparr as $atri) {
                            $t = explode("|", $atri);
                            $id_atributo = $t[0] != "null" ? $t[0] : null;
                            $valor_varchar = $t[1] != "null" ? $t[1] : null;
                            $valor_int = $t[2] != "null" ? $t[2] : null;
                            $valor_decimal = $t[3] != "null" ? $t[3] : null;
                            $valor_date = $t[4] != "null" ? $t[4] : null;
                            $valor_text = $t[5] != "null" ? $t[5] : null;

                            if ($id_atributo == $arrayAtr[$i]["id"]) {
                                if (isset($valor_varchar)) {
                                    $valu = $valor_varchar;
                                }

                                if (isset($valor_int)) {
                                    $valu = $valor_int;
                                }

                                if (isset($valor_decimal)) {
                                    $valu = $valor_decimal;
                                }

                                if (isset($valor_date)) {
                                    $valu = $valor_date;
                                }

                                if (isset($valor_text)) {
                                    $valu = $valor_text;
                                }

                                //AGREGAR ALGO ACA

                            }
                        }
                        $tmparr = explode(",", $ww["atributos_select_data"]);
                        foreach ($tmparr as $atri) {
                            $t = explode("|", $atri);
                            $id_atributo = $t[1] != "null" ? $t[1] : null;
                            $nombre = $t[0] != "null" ? $t[0] : null;
                            if ($id_atributo == $arrayAtr[$i]["id"]) {
                                $valu .= "$nombre<br>";
                            }

                        }
                        $tdatr .= "<td>$valu</td>";
                    }
                }
            }

            $id_producto = $ww['id_producto'];

            $q = "SELECT 
                    vi.id, 
                    vi.nombre, 
                    (SELECT SUM(i.cantidad) 
                        FROM pys_ingresos i WHERE i.id_vivero = vi.id AND i.id_$tbl = $id_producto) as ing, 
                    (SELECT SUM(e.cantidad) 
                        FROM pys_egresos e WHERE e.id_vivero = vi.id AND e.id_$tbl = $id_producto) as egr,
                    (SELECT pvp.precio FROM pys_viveros_precios pvp WHERE pvp.id_$tbl = $id_producto AND pvp.id_vivero = vi.id) as precio,
                    (SELECT pvp.precio_mayorista FROM pys_viveros_precios pvp WHERE pvp.id_$tbl = $id_producto AND pvp.id_vivero = vi.id) as precio_mayorista
                    
                    FROM viveros vi 
                    LEFT JOIN pys_ingresos inb ON inb.id_vivero = vi.id 
                    LEFT JOIN pys_egresos egk ON egk.id_vivero = vi.id 
                    GROUP BY vi.id
                    ;
            
            ";

            $dataux = "";
            $dataux_precios = "";
            $dataux_iva = "";
            $dataux_plantket = "";
            $v = mysqli_query($con, $q);
            if (mysqli_num_rows($v)) {
                $dataux .= "<br>";
                while ($d = mysqli_fetch_array($v)) {
                    if (((int) $d["ing"] - (int) $d["egr"]) > 0) {
                        $dataux .= "<small class='text-muted'>$d[nombre]: <span class='badge badge-primary'>" . ((int) $d["ing"] - (int) $d["egr"] . "</span></small><br>");
                    } else {
                        $dataux .= "<small class='text-muted'>$d[nombre]: <span class='badge badge-danger'>" . ((int) $d["ing"] - (int) $d["egr"] . "</span></small><br>");
                    }
                    if (isset($d["precio"])) {
                        $dataux_precios .= "<small>$d[nombre]: <span class='badge badge-primary'>" . ("$" . number_format($d["precio"], 0, ',', '.')) .
                            (isset($d["precio_mayorista"]) ? (" - $" . number_format($d["precio_mayorista"], 0, ',', '.')) : "") .
                            "</span></small><br>";

                        $dataux_iva .= "<small>$d[nombre]: <span class='badge badge-primary'>" . ("$" . number_format(round((float) $d['precio'] * 1.19, 0, PHP_ROUND_HALF_UP), 0, ',', '.'))
                            .
                            (isset($d["precio_mayorista"]) ? (" - $" . number_format(round((float) $d['precio_mayorista'] * 1.19, 0, PHP_ROUND_HALF_UP), 0, ',', '.')) : "") .
                            "</span></small><br>";
                    }
                }
            }

            $tipo = $ww['nombre_tipo'];
            $producto = $ww['nombre_producto'];

            $cantidad = (int) $ww["ingresos"] - (int) $ww["egresos"];
            $class = $cantidad <= 0 ? "text-danger" : "";
            $onclick = "onclick=\"modalProducto({
                                id: '$id_producto',
                                id_tipo: $ww[id_tipo],
                                nombre: '$producto',
                        })\" ";
            $btneliminar = "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminar($id_producto)'></button>";
            $imagen = "dist/img/noimage.jpg";
            if (isset($ww["imagen"])) {
                $imagen = $ww["imagen"];
            }
            echo "
                <tr class='text-center' style='cursor:pointer' x-codigo-tipo='$ww[codigo]' x-id-interno='$ww[id_interno]' x-id='$id_producto' x-id-tipo='$id_tipo' x-precio='$precio' x-precio-iva='$precio_iva' x-nombre='$producto'>
                    <td $onclick style='text-align: center; width:80px;color:#1F618D;font-weight:bold;' x-id='$id' x-codigo='$codigo'>
                        <span class='d-none'>$id_producto</span>
                        <span style='font-size:1.0em'>$id_producto</span><br>
                        <span style='color:grey;font-size:0.7em;'>$ww[codigo]</span>
                    </td>
                    <td>
                        <img src='$imagen' onclick='$(this).next().click()' style='width:80px;height:80px;border-radius:10px;'/>
                        <input class='input-img d-none' x-id='$id_producto' onchange='readFoto(this)' type='file' accept='image/jpg,image/jpeg,image/png' />
                    </td>
                    <td $onclick>$producto</td>
                    <td $onclick class='$class' style='font-weight:bold;'>$cantidad $dataux</td>
                    <td $onclick style='font-weight:bold;padding-top:27px'>$dataux_precios</td>
                    <td $onclick style='font-weight:bold;padding-top:27px'>$dataux_iva</td>
                    $tdatr
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
        echo "<div class='callout callout-danger'><b>No se encontraron $_POST[tab]...</b></div>";
    }
} else if ($consulta == "agregar_producto") {
    $nombre = mysqli_real_escape_string($con, test_input($_POST['nombre']));
    $codigo = mysqli_real_escape_string($con, test_input($_POST['codigo']));

    $id_tipo = $_POST["id_tipo"];
    $table = ($_POST["tab"] == "productos" ? "productos" : "servicios");

    try {
        $val = mysqli_query($con, "SELECT * FROM $table WHERE nombre = UPPER('$nombre') AND id_tipo = $id_tipo;");
        if (mysqli_num_rows($val) > 0) {
            echo "YA EXISTE UN ÍTEM CON ESE NOMBRE!";
        } else {
            $val = mysqli_query($con, "SELECT * FROM $table WHERE id_tipo = $id_tipo AND id_interno = $codigo;");
            if (mysqli_num_rows($val) > 0) {
                echo "EL CÓDIGO INGRESADO YA ESTÁ EN USO. ELIGE OTRO.";
            } else {
                $errors = array();
                mysqli_autocommit($con, false);

                $query = "INSERT INTO $table (nombre, id_tipo, id_interno) VALUES (UPPER('$nombre'), '$id_tipo', '$codigo');";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }

                $id_pys = mysqli_insert_id($con);

                $table = ($_POST["tab"] == "productos" ? "producto" : "servicio");
                if (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) {
                    $atributos = json_decode($_POST["atributos"], true);

                    foreach ($atributos as $atr) {
                        $tipo_dato = isset($atr["tipo_dato"]) ? strtolower($atr["tipo_dato"]) : null;
                        $valor = isset($atr["valor"]) && strlen($atr["valor"]) ? ("'" . $atr["valor"] . "'") : "NULL";

                        $parametro = "";
                        $parametroValue = "";

                        if (isset($tipo_dato)) {
                            $parametro = ", valor_$tipo_dato";
                        }

                        if (isset($atr["valor"]) && strlen($atr["valor"])) {
                            $parametroValue = ", '" . $valor . "'";
                        }
                        $query = "INSERT INTO atributos_$table" . "_valores (
                            id_$table,
                            id_atributo
                            $parametro
                        ) VALUES (
                            $id_pys,
                            $atr[id]
                            $parametroValue
                        )";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }

                        $id_atributo_valor = mysqli_insert_id($con);

                        if (isset($atr["valorSelect"]) && count($atr["valorSelect"]) > 0) {
                            foreach ($atr["valorSelect"] as $item) {
                                $query = "INSERT INTO atributos_$table" . "_valores_seleccionados (
                                    id_$table,
                                    id_atributo_$table" . "_valor,
                                    id_atributo_tipo_valor
                                ) VALUES (
                                    $id_pys,
                                    $id_atributo_valor,
                                    $item
                                )";
                                if (!mysqli_query($con, $query)) {
                                    $errors[] = mysqli_error($con) . "-" . $query;
                                }
                            }
                        }
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
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "editar_producto") {
    $id_producto = $_POST['id_producto'];
    $nombre = $_POST["nombre"];
    $table = ($_POST["tab"] == "productos" ? "productos" : "servicios");

    try {
        $tbl = ($_POST["tab"] == "productos" ? "producto" : "servicio");

        $errors = array();
        mysqli_autocommit($con, false);

        $query = "UPDATE $table SET nombre = UPPER('$nombre') WHERE id = $id_producto";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $table = ($_POST["tab"] == "productos" ? "producto" : "servicio");
        if (isset($_POST["atributos"]) && strlen($_POST["atributos"]) > 0) {
            $atributos = json_decode($_POST["atributos"], true);

            foreach ($atributos as $atr) {
                $tipo_dato = isset($atr["tipo_dato"]) ? strtolower($atr["tipo_dato"]) : null;
                $valor = isset($atr["valor"]) && strlen($atr["valor"]) ? ("'" . $atr["valor"] . "'") : "NULL";

                if (isset($atr["tipo_dato"])) {
                    if (isset($atr["rowid_valor"])) {
                        $query = "UPDATE
                        atributos_$table" . "_valores SET
                        valor_$atr[tipo_dato] = $valor
                        WHERE id = $atr[rowid_valor]
                        ";
                    } else {
                        $query = "
                        INSERT INTO atributos_$table" . "_valores
                        (
                            valor_$atr[tipo_dato],
                            id_$table,
                            id_atributo
                        ) VALUES (
                            $valor,
                            $id_producto,
                            $atr[id]
                        )

                        ";
                    }

                } else {
                    $id_atributo_valor = null;
                    if (!isset($atr["rowid_valor"])) {
                        $query = "
                        INSERT INTO atributos_$table" . "_valores
                        (
                            id_$table,
                            id_atributo
                        ) VALUES (
                            $id_producto,
                            $atr[id]
                        )
                        ";

                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                        $id_atributo_valor = mysqli_insert_id($con);
                    } else {
                        $id_atributo_valor = $atr["rowid_valor"];
                    }

                    if (isset($atr["valorSelect"]) && count($atr["valorSelect"]) > 0) {
                        $query = "DELETE FROM atributos_$table" . "_valores_seleccionados WHERE id_atributo_$table" . "_valor = $id_atributo_valor AND id_$table = $id_producto";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }

                        foreach ($atr["valorSelect"] as $item) {
                            $query = "INSERT INTO atributos_$table" . "_valores_seleccionados (
                                id_$table,
                                id_atributo_$table" . "_valor,
                                id_atributo_tipo_valor
                            ) VALUES (
                                $id_producto,
                                $id_atributo_valor,
                                $item
                            )";
                            if (!mysqli_query($con, $query)) {
                                $errors[] = mysqli_error($con) . "-" . $query;
                            }

                        }
                    }
                    else{
                        $query = "DELETE FROM atributos_$table" . "_valores_seleccionados WHERE id_atributo_$table" . "_valor = $id_atributo_valor AND id_$table = $id_producto";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                    }
                }

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
    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "eliminar_producto") {
    try {
        $table = ($_POST["tab"] == "productos" ? "productos" : "servicios");
        $id_producto = $_POST["id_producto"];
        if (mysqli_query($con, "DELETE FROM $table WHERE id = $id_producto;")) {
            echo "success";
        } else if (mysqli_query($con, "UPDATE $table SET eliminado = 1 WHERE id = $id_producto;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }

    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "eliminar_in_out") {
    try {
        $id = $_POST["id"];
        $tab = $_POST["tab"];
        if (mysqli_query($con, "DELETE FROM pys_$tab WHERE id = $id;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "eliminar_tipo") {
    try {
        $table = ($_POST["tab"] == "tipos" ? "producto" : "servicio");
        $id = $_POST["id"];
        if (mysqli_query($con, "DELETE FROM tipos_$table WHERE id = $id")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "busca_productos_select") {
    try {
        $table = ($_POST["tab"] == "productos" ? "productos" : "servicios");

        $tipo_pos = ($_POST["tab"] == "productos" ? "tipos_producto" : "tipos_servicio");

        $id_tipo = $_POST["id_tipo"];
        $cadena = "select v.id, v.id_interno, v.nombre, t.codigo from $table v INNER JOIN $tipo_pos t ON t.id = v.id_tipo WHERE v.eliminado IS NULL AND v.id_tipo = $id_tipo order by v.id_interno ASC;";
        $val = mysqli_query($con, $cadena);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $id_interno = str_pad($re["id_interno"], 2, '0', STR_PAD_LEFT);
                $nombre = mysqli_real_escape_string($con, $re["nombre"]);
                echo "<option x-codigo='$re[codigo]' x-nombre='$nombre' x-codigofull='$re[codigo]$id_interno' value='$re[id]'>$re[nombre] ($re[codigo]$id_interno)</option>";
            }
        }
    } catch (\Throwable $th) {
        //throw $th;
    }
} else if ($consulta == "busca_tipos_servicio") {
    $cadena = "SELECT t.id, t.nombre, t.codigo,
    (SELECT GROUP_CONCAT(CONCAT(ap.id, '|', ap.nombre,'|', IFNULL(ap.tipo_dato, 'null'), '|', IFNULL(ap.id_tipo_atributo, 'null'), '|', IFNULL(at.nombre, 'null')))
                    FROM
                    atributos_servicio ap
                    LEFT JOIN atributos_tipos at ON ap.id_tipo_atributo = at.id
                    WHERE ap.id_tipo_servicio = t.id) as atributos_data,
   (SELECT GROUP_CONCAT(ap.nombre SEPARATOR '<br>')
                   FROM
                   atributos_servicio ap
                   WHERE ap.id_tipo_servicio = t.id) as atributos

    FROM tipos_servicio t
    GROUP BY t.id
    ORDER BY nombre;";

    $val = mysqli_query($con, $cadena);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Nombre</th><th>Atributos</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $atributos = isset($ww["atributos_data"]) ? $ww["atributos_data"] : "null";
            $id = $ww['id'];
            $codigo = $ww["codigo"];
            $tipo = $ww['nombre'];
            $btneliminar = "<button class='btn btn-danger fa fa-trash' onClick='eliminarTipo($id)'></button>";

            $onclick = "onclick='modalTipo({id: $id, nombre: \"$tipo\", codigo: \"$codigo\", atributos: \"$atributos\"})'";
            echo "<tr style='cursor:pointer; '>";
            echo "<td $onclick style='text-align: center; width:80px;color:#1F618D;font-weight:bold;' x-id='$id' x-codigo='$codigo'>
                    <span class='d-none'>$id</span>
                    <span style='font-size:1.1em'>$codigo</span><br>
                    <span style='color:grey;font-size:0.8em;'>$id</span>
                 </td>";

            echo "<td $onclick style='text-align: center; font-size:1em;font-weight:bold;'>$tipo</td>";
            echo "<td $onclick class='text-center'>$ww[atributos]</td>";
            echo "<td class='text-center'>$btneliminar</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron servicios en la base de datos...</b></div>";
    }
} else if ($consulta == "busca_ingresos" || $consulta == "busca_egresos") {
    $val = mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $tab = $_POST["tab"];

    $filtro = $_POST["filtro"];

    if ($filtro == "productos") {
        $query = "SELECT
                p.id as id_producto,
                t.id as id_tipo,
                t.nombre as nombre_tipo,
                p.nombre as nombre_producto,
                t.codigo as codigo_tipo,
                p.id_interno as  id_interno_producto,

                pvp.precio,
                pvp.precio_mayorista,

                i.id as id_ingreso,
                i.cantidad,
                i.notas,
                v.nombre as nombre_vivero,
                DATE_FORMAT(i.fecha, '%d/%m/%y %H:%i') as fecha,
                DATE_FORMAT(i.fecha, '%y%m%d') as fecha_raw,
                (SELECT GROUP_CONCAT(CONCAT(at.nombre,': ', atv.nombre) SEPARATOR '<br>')
                    FROM pys_$tab" . "_atributos pia 
                    INNER JOIN atributos_tipos_valores atv ON pia.id_atributo_tipo_valor = atv.id
                    INNER JOIN atributos_tipos at ON at.id = atv.id_tipo
                    WHERE pia.id_pys_" . (rtrim($tab, 's')) . " = i.id) as atributos_data

            FROM
            pys_$tab i
            INNER JOIN productos p ON i.id_producto = p.id
            INNER JOIN tipos_producto t ON t.id = p.id_tipo
            LEFT JOIN viveros v ON v.id = i.id_vivero
            LEFT JOIN pys_viveros_precios pvp ON pvp.id_producto = p.id AND pvp.id_vivero = v.id 
            ORDER BY i.id DESC LIMIT 2000
          ";

    } else if ($filtro == "servicios") {
        $query = "SELECT
                s.id as id_servicio,
                ts.id as id_tipo_servicio,
                ts.nombre as nombre_tipo_servicio,
                s.nombre as nombre_servicio,
                ts.codigo as codigo_tipo_servicio,
                s.id_interno as id_interno_servicio,

                pvp.precio,
                pvp.precio_mayorista,

                i.id as id_ingreso,
                i.cantidad,
                i.notas,
                v.nombre as nombre_vivero,
                DATE_FORMAT(i.fecha, '%d/%m/%y %H:%i') as fecha,
                DATE_FORMAT(i.fecha, '%y%m%d') as fecha_raw,

                (SELECT GROUP_CONCAT(CONCAT(at.nombre,': ', atv.nombre) SEPARATOR '<br>')
                    FROM pys_$tab" . "_atributos pia 
                    INNER JOIN atributos_tipos_valores atv ON pia.id_atributo_tipo_valor = atv.id
                    INNER JOIN atributos_tipos at ON at.id = atv.id_tipo
                    WHERE pia.id_pys_" . (rtrim($tab, 's')) . " = i.id) as atributos_data

            FROM
            pys_$tab i
            INNER JOIN servicios s ON s.id = i.id_servicio
            INNER JOIN tipos_servicio ts ON ts.id = s.id_tipo
            LEFT JOIN viveros v ON v.id = i.id_vivero
            LEFT JOIN pys_viveros_precios pvp ON pvp.id_servicio = s.id AND pvp.id_vivero = v.id 
            ORDER BY i.id DESC LIMIT 2000
          ";
    } else {
        $query = "SELECT
            p.id as id_producto,
            t.id as id_tipo,
            t.nombre as nombre_tipo,
            p.nombre as nombre_producto,
            t.codigo as codigo_tipo,
            p.id_interno as  id_interno_producto,

            s.id as id_servicio,
            ts.id as id_tipo_servicio,
            ts.nombre as nombre_tipo_servicio,
            s.nombre as nombre_servicio,
            ts.codigo as codigo_tipo_servicio,
            s.id_interno as id_interno_servicio,

            pvp.precio,
            pvp.precio_mayorista,

            i.id as id_ingreso,
            i.cantidad,
            i.notas,
            v.nombre as nombre_vivero,
            DATE_FORMAT(i.fecha, '%d/%m/%y %H:%i') as fecha,
            DATE_FORMAT(i.fecha, '%y%m%d') as fecha_raw,

            (SELECT GROUP_CONCAT(CONCAT(at.nombre,': ', atv.nombre) SEPARATOR '<br>')
                    FROM pys_$tab" . "_atributos pia 
                    INNER JOIN atributos_tipos_valores atv ON pia.id_atributo_tipo_valor = atv.id
                    INNER JOIN atributos_tipos at ON at.id = atv.id_tipo
                    WHERE pia.id_pys_" . (rtrim($tab, 's')) . " = i.id) as atributos_data

        FROM
        pys_$tab i
        LEFT JOIN productos p ON i.id_producto = p.id
        LEFT JOIN tipos_producto t ON t.id = p.id_tipo
        LEFT JOIN servicios s ON s.id = i.id_servicio
        LEFT JOIN tipos_servicio ts ON ts.id = s.id_tipo
        LEFT JOIN viveros v ON v.id = i.id_vivero
        LEFT JOIN pys_viveros_precios pvp ON (pvp.id_servicio = s.id AND pvp.id_vivero = v.id) OR (pvp.id_producto = p.id AND pvp.id_vivero = v.id)
        ORDER BY i.id DESC LIMIT 2000
        ";
    }

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title " . ($tab == "ingresos" ? "text-primary" : "text-danger") . "' style='text-transform:capitalize'>$tab</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Fecha</th><th>ID P/S</th><th>Producto/Servicio</th><th>Cantidad</th><th>Vivero</th><th style='max-width:250px !important'>Notas</th><th>Precio</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $id_producto = isset($ww["id_servicio"]) ? $ww["id_servicio"] : $ww['id_producto'];
            $id_tipo = isset($ww["id_servicio"]) ? $ww["id_tipo_servicio"] : $ww['id_tipo_producto'];
            $tipo = isset($ww["id_servicio"]) ? $ww['nombre_tipo_servicio'] : $ww['nombre_tipo'];
            $producto = isset($ww["id_servicio"]) ? $ww['nombre_servicio'] : $ww['nombre_producto'];

            if (isset($ww["atributos_data"])) {
                $producto .= "<br><small>$ww[atributos_data]</small>";
            }
            $precio = number_format($ww["precio"], 0, ',', '.');
            $precio_raw = $ww["precio"];

            $precio_m = isset($ww["precio_mayorista"]) ? ("<br><small class='text-muted'>M: $" . (number_format($ww["precio_mayorista"], 0, ',', '.')) . "</small>") : "";

            $cantidad = isset($ww["cantidad"]) ? $ww["cantidad"] : "null";

            $btneliminar = "<button class='btn btn-danger fa fa-trash' onClick='eliminarInOut($ww[id_ingreso], \"$tab\")'></button>";
            echo "
                <tr class='text-center' style='cursor:pointer' x-id='$ww[id_ingreso]' x-codigo-tipo='$ww[codigo]' x-id-pys='$id_producto' x-id-tipo='$id_tipo' x-precio='$precio' x-precio-iva='$precio_iva' x-nombre='$producto'>
                <td $onclick><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
                <td $onclick>$id_producto<br><small class='text-muted'>$tipo (" . (isset($ww["id_servicio"]) ? "S" : "P") . ")</small></td>
                <td $onclick>$producto</td>
                <td $onclick style='font-weight:bold;'>$ww[cantidad]</td>
                <td $onclick>$ww[nombre_vivero]</td>
                <td $onclick><small>$ww[notas]</small></td>
                <td $onclick style='font-weight:bold;'>$ $precio $precio_m</td>
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
        echo "<div class='callout callout-danger'><b>No se encontraron $tab en el Inventario...</b></div>";
    }
} else if ($consulta == "guardar_in_out") {
    $tab = $_POST["tab"];
    $id_tipo = $_POST["id_tipo"];
    $id_pos = $_POST["id_pos"];
    $cantidad = test_input($_POST["cantidad"]);
    $notas = isset($_POST["notas"]) && strlen($_POST["notas"]) > 0 ? "'" . test_input($_POST["notas"]) . "'" : "NULL";
    $pys = (isset($_POST["atributos"]) && strlen($_POST["atributos"])) ? json_decode($_POST["atributos"], true) : null;

    $esPoS = null;
    if ($_POST["esProductoServicio"] == "producto") {
        $esPoS = "id_producto";
    } else if ($_POST["esProductoServicio"] == "servicio") {
        $esPoS = "id_servicio";
    }
    $errors = array();

    if (isset($pys)) {
        mysqli_autocommit($con, false);
        foreach ($pys as $p) {
            $query = "INSERT INTO pys_$tab (
                cantidad,
                notas,
                $esPoS,
                fecha,
                id_usuario,
                id_vivero
            ) VALUES (
                $p[cantidad],
                $notas,
                $id_pos,
                NOW(),
                $_SESSION[id_usuario],
                $p[id_vivero]
            )
            ";

            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con) . "-" . $query;
            }
            $id_ingreso = mysqli_insert_id($con);

            if (isset($p["valores"]) && count($p["valores"])) {
                foreach ($p["valores"] as $v) {
                    $query = "INSERT INTO pys_$tab" . "_atributos (
                        id_pys_" . (rtrim($tab, "s")) . ",
                        id_atributo_tipo_valor  
                    ) VALUES (
                        $id_ingreso,
                        $v
                    )
                    ";

                    if (!mysqli_query($con, $query)) {
                        $errors[] = mysqli_error($con) . "-" . $query;
                    }
                }
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



} else if ($consulta == "eliminar_atributo") {
    $id = $_POST['id'];
    $tab = $_POST['tab'];
    $table = ($_POST["tab"] == "tipos" ? "producto" : "servicio");

    try {

        $errors = array();
        mysqli_autocommit($con, false);

        $query = "DELETE FROM atributos_$table" . "_valores WHERE id_atributo = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM atributos_$table WHERE id = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
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

    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "get_atributos_pys") {
    $table = ($_POST["tab"] == "productos" ? "producto" : "servicio");
    $id = $_POST["id"];
    $query = "SELECT a.id, a.nombre, a.tipo_dato, a.id_tipo_atributo FROM atributos_$table a
                WHERE a.id_tipo_$table = $id";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $array = array();
        while ($ww = mysqli_fetch_array($val)) {
            $arrayAt = null;
            if (isset($ww["id_tipo_atributo"])) {
                $query = "SELECT id, nombre FROM atributos_tipos_valores WHERE id_tipo = $ww[id_tipo_atributo]";
                $val2 = mysqli_query($con, $query);

                if (mysqli_num_rows($val2) > 0) {
                    $arrayAt = array();
                    while ($at = mysqli_fetch_array($val2)) {
                        array_push($arrayAt, array(
                            "id" => $at["id"],
                            "nombre" => $at["nombre"],
                        ));
                    }
                }
            }

            array_push($array, array(
                "id" => $ww["id"],
                "nombre" => $ww["nombre"],
                "tipo_dato" => $ww["tipo_dato"],
                "valores" => $arrayAt,
            ));
        }
        echo json_encode($array);
    }
} else if ($consulta == "guardar_tipo_atributo") {
    $nombre = mysqli_real_escape_string($con, test_input($_POST["nombre"]));

    $query = "INSERT INTO atributos_tipos (
            nombre
        ) VALUES (
            UPPER('$nombre')
        )
        ";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con) . " " . $query;
    }
} else if ($consulta == "get_table_tipos_atributo") {
    $query = "SELECT id, nombre FROM atributos_tipos ORDER BY id DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            $query = "SELECT id, nombre, precio_extra FROM atributos_tipos_valores WHERE id_tipo = $ww[id] ORDER BY id DESC";

            $val2 = mysqli_query($con, $query);
            $valores = "";
            if (mysqli_num_rows($val2) > 0) {
                $valores .= "<ul class='list-group mt-1 mr-3' style='max-height:130px;overflow-y:scroll'>";
                while ($ww2 = mysqli_fetch_array($val2)) {
                    $valores .= "<li class='list-group-item p-1' style='font-size:0.7rem'><span style='cursor:pointer;' onclick='modalEditarValor($ww2[id], \"$ww2[nombre]\", \"$ww2[precio_extra]\")'>$ww2[nombre] " . (isset($ww2["precio_extra"]) ? " <span class='text-muted'>\$" . number_format($ww2["precio_extra"], 0, ',', '.') . "</span>" : "") . "</span> <button onclick='eliminarValorAtributo($ww2[id], this, \"$ww2[nombre]\")' class='ml-2 d-inline-block btn btn-sm btn-secondary fa fa-trash' style='font-style:0.6rem'></button></li>";
                }
                $valores .= "</ul>";
            }

            echo "<tr class='text-center'>
                <td>$ww[nombre]</td>
                <td>
                    <div class='d-flex flex-row'>
                        <input type='search' placeholder='Valor' autocomplete='off' class='form-control' maxlength='30'/>
                        <input type='search' style='width:50%' placeholder='Precio Extra' autocomplete='off' class='form-control ml-2 input-int' maxlength='9'/>
                        <button onclick='guardarValorAtributo($ww[id], this)' class='btn btn-sm ml-2 mr-3 btn-success fa fa-save'></button>
                    </div>
                    $valores
                </td>
                <td class='text-center'>
                    <button onclick='eliminarTipoAtributo($ww[id], this, \"$ww[nombre]\")' class='btn btn-sm btn-danger fa fa-trash'></button>
                </td>
            </tr>
            ";
        }
    } else {
        echo "<tr class='text-center text-muted font-weight-bold'>
            <td colspan='3'>No hay Ítems registrados.</td>
        </tr>";
    }
} else if ($consulta == "guardar_valor_atributo") {
    $nombre = mysqli_real_escape_string($con, test_input($_POST["nombre"]));
    $precioExtra = isset($_POST["precioExtra"]) && strlen($_POST["precioExtra"]) > 0 ? test_input($_POST["precioExtra"]) : "NULL";

    $id = $_POST["id"];

    $query = "INSERT INTO atributos_tipos_valores (
            nombre,
            precio_extra,
            id_tipo
        ) VALUES (
            '$nombre',
            $precioExtra,
            $id
        )
        ";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($con) . " " . $query;
    }
} else if ($consulta == "eliminar_valor_atributo") {
    $id = $_POST['id'];

    try {

        $errors = array();
        mysqli_autocommit($con, false);

        $query = "DELETE FROM atributos_tipos_valores WHERE id = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_rollback($con);
            echo "Aségurate de que el Valor no esté siendo utilizado por ningún Producto/Servicio";
        }
        mysqli_close($con);

    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "eliminar_tipo_atributo") {
    $id = $_POST['id'];

    try {

        $errors = array();
        mysqli_autocommit($con, false);

        $query = "DELETE FROM atributos_producto_valores WHERE id_atributo IN
        (SELECT id FROM atributos_producto WHERE id_tipo_atributo = $id) ";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM atributos_servicio_valores WHERE id_atributo IN
        (SELECT id FROM atributos_servicio WHERE id_tipo_atributo = $id) ";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM atributos_tipos_valores WHERE id_tipo = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM atributos_producto WHERE id_tipo_atributo = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM atributos_servicio WHERE id_tipo_atributo = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM atributos_tipos WHERE id = $id";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
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

    } catch (\Throwable $th) {
        echo "error: " . $th;
    }
} else if ($consulta == "get_tipos_atributo_select") {
    $query = "SELECT id, nombre FROM atributos_tipos ORDER BY id DESC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            echo "<option x-nombre='$ww[nombre]' value='atr-$ww[id]'>$ww[nombre] ($ww[id])</option>";
        }
    }
} else if ($consulta == "get_table_atributos_pys") {
    $table = ($_POST["tab"] == "productos" ? "producto" : "servicio");
    $id = $_POST["id"];
    $id_tipo = $_POST["id_tipo"];

    $query = "SELECT
                a.id,
                av.id_$table,
                a.nombre,
                a.tipo_dato,
                a.id_tipo_atributo,
                av.id as rowid_valor,
                av.valor_varchar,
                av.valor_int,
                av.valor_decimal,
                av.valor_date,
                av.valor_text,
                av.id_atributo_tipo_valor
                FROM atributos_$table a
                LEFT JOIN atributos_$table" . "_valores av ON av.id_atributo = a.id
                WHERE av.id_$table = $id";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) === 0) {
        $query = "SELECT
                a.id,
                a.nombre,
                a.tipo_dato,
                a.id_tipo_atributo

                FROM atributos_$table a

                WHERE a.id_tipo_$table = $id_tipo";
        $val = mysqli_query($con, $query);
    }

    if (mysqli_num_rows($val) > 0) {
        $array = array();
        while ($ww = mysqli_fetch_array($val)) {
            $arrayAt = null;
            $arraySelected = null;
            if (isset($ww["id_tipo_atributo"])) {
                $query = "SELECT id, nombre FROM atributos_tipos_valores WHERE id_tipo = $ww[id_tipo_atributo]";
                $val2 = mysqli_query($con, $query);

                if (mysqli_num_rows($val2) > 0) {
                    $arrayAt = array();
                    while ($at = mysqli_fetch_array($val2)) {
                        array_push($arrayAt, array(
                            "id" => $at["id"],
                            "nombre" => $at["nombre"],
                        ));
                    }
                }
                $id_pys = $ww["id"];
                if (isset($ww["rowid_valor"])) {
                    $query = "SELECT id_atributo_tipo_valor as id FROM atributos_$table" . "_valores_seleccionados WHERE id_$table = $id_pys AND id_atributo_$table" . "_valor = $ww[rowid_valor]";
                    die($query);
                    $val3 = mysqli_query($con, $query);

                    if (mysqli_num_rows($val3) > 0) {
                        $arraySelected = array();
                        while ($as = mysqli_fetch_array($val3)) {
                            array_push($arraySelected, $as["id"]);
                        }
                    }
                }
            }

            array_push($array, array(
                "id" => $ww["id"],
                "rowid_valor" => $ww["rowid_valor"],
                "nombre" => $ww["nombre"],
                "tipo_dato" => $ww["tipo_dato"],
                "valor_varchar" => $ww["valor_varchar"],
                "valor_int" => $ww["valor_int"],
                "valor_decimal" => $ww["valor_decimal"],
                "valor_date" => $ww["valor_date"],
                "valor_text" => $ww["valor_text"],
                "id_atributo_tipo_valor" => $ww["id_atributo_tipo_valor"],
                "valores" => $arrayAt,
                "valores_selected" => $arraySelected,
                "query" => $query,
            ));
        }
        echo json_encode($array);
    }
} else if ($consulta == "editar_valor") {
    try {
        $id = $_POST["id"];
        $nombre = mysqli_real_escape_string($con, test_input($_POST["nombre"]));
        $precioExtra = isset($_POST["precioExtra"]) && strlen($_POST["precioExtra"]) > 0 ? test_input($_POST["precioExtra"]) : "NULL";

        if (mysqli_query($con, "UPDATE atributos_tipos_valores SET nombre = '$nombre', precio_extra = $precioExtra WHERE id = $id;")) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } catch (\Throwable $th) {
        //throw $th;
        echo "error";
    }
} else if ($consulta == "get_viveros_select") {
    $id_pos = $_POST["id_pos"];
    $esPos = $_POST["esPos"];
    $atributos = json_decode($_POST["atributos"], true);

    $query = "SELECT 
            vi.id, 
            vi.nombre, 
            (SELECT pvp.precio FROM pys_viveros_precios pvp WHERE pvp.id_$esPos = $id_pos AND pvp.id_vivero = vi.id) as precio,
            (SELECT pvp.precio_mayorista FROM pys_viveros_precios pvp WHERE pvp.id_$esPos = $id_pos AND pvp.id_vivero = vi.id) as precio_mayorista                    
        FROM viveros vi 
        GROUP BY vi.id";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            echo "<option x-precio='" . (int) $ww["precio"] . "' x-precio-mayorista='" . (int) $ww["precio_mayorista"] . "' x-nombre='$ww[nombre]' value='$ww[id]'>$ww[nombre] (" . ("$" . number_format($ww["precio"], 0, ',', '.')) .
                (isset($ww["precio_mayorista"]) ? (" - $" . number_format($ww["precio_mayorista"], 0, ',', '.')) : "") . ")</option>";
        }
    }
} else if ($consulta == "get_atributos") { //IN OUT
    $id_pos = $_POST["id_pos"];

    $query = "SELECT v.id, v.nombre, 
            (SELECT p.precio FROM pys_viveros_precios p WHERE p.id_vivero = v.id AND p.id_$_POST[esPos] = $id_pos) as precio,
            (SELECT p.precio_mayorista FROM pys_viveros_precios p WHERE p.id_vivero = v.id AND p.id_$_POST[esPos] = $id_pos) as precio_mayorista
            FROM viveros v
            ORDER BY v.nombre ASC";

    $val = mysqli_query($con, $query);

    $viveros = NULL;
    $viverosArr = NULL;
    if (mysqli_num_rows($val) > 0) {
        $viveros = "";
        $viverosArr = [];
        while ($ww = mysqli_fetch_array($val)) {
            $viveros .= "<option x-nombre='$ww[nombre]' 
            x-precio-mayorista='" . (isset($ww["precio_mayorista"]) ? (int) $ww["precio_mayorista"] : "") . "'
            x-precio='" . (isset($ww["precio"]) ? (int) $ww["precio"] : "") . "' value='$ww[id]'>$ww[nombre] ($ww[id])</option>";
            array_push($viverosArr, array(
                "id" => $ww["id"],
                "nombre" => $ww["nombre"],
                "precio" => isset($ww["precio"]) ? (int) $ww["precio"] : NULL,
                "precioM" => isset($ww["precio_mayorista"]) ? (int) $ww["precio_mayorista"] : NULL,
            ));
        }
    }


    $query = "SELECT at.id, at.nombre as nombre_atributo_tipo,
            atv.id as id_atributo_tipo_valor, atv.precio_extra, atv.nombre as nombre_atributo_tipo_valor FROM
                atributos_$_POST[esPos]_valores_seleccionados apysv
                INNER JOIN
                atributos_tipos_valores atv ON apysv.id_atributo_tipo_valor = atv.id
                INNER JOIN atributos_tipos at ON at.id = atv.id_tipo
                WHERE apysv.id_$_POST[esPos] = $id_pos;
    ";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $array = array();
        while ($re = mysqli_fetch_array($val)) {
            $tmp = array(
                "nombre_atributo_tipo" => $re["nombre_atributo_tipo"],
                "id_atributo_tipo_valor" => $re["id_atributo_tipo_valor"],
                "precio_extra" => $re["precio_extra"],
                "nombre_atributo_tipo_valor" => $re["nombre_atributo_tipo_valor"],
            );

            if (!isset($array[$re["id"]])) {
                $array[$re["id"]] = array($tmp);
            } else {
                array_push($array[$re["id"]], $tmp);
            }
        }
        echo json_encode(array(
            "atributos" => $array,
            "viveros" => $viveros,
            "viverosArr" => $viverosArr
        ));
    } else {
        echo json_encode(array(
            "viveros" => $viveros,
            "viverosArr" => $viverosArr
        ));
    }
} else if ($consulta == "guardar_precio") {
    $newPrecio = mysqli_real_escape_string($con, test_input($_POST["newPrecio"]));
    $newPrecioM = isset($_POST["newPrecioM"]) ? "'" . mysqli_real_escape_string($con, test_input($_POST["newPrecioM"])) . "'" : "NULL";
    $id_pos = $_POST["id_pos"];
    $id_vivero = $_POST["id_vivero"];
    $esPos = $_POST["esPos"];

    $query = "SELECT * FROM pys_viveros_precios WHERE id_vivero = $id_vivero AND id_$esPos = $id_pos";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $query = "UPDATE pys_viveros_precios SET precio = $newPrecio, precio_mayorista = $newPrecioM
             WHERE id_vivero = $id_vivero
            AND id_$esPos = $id_pos
             ";
    } else {
        $query = "INSERT INTO pys_viveros_precios (
            precio,
            precio_mayorista,
            id_vivero,
            id_$esPos
        ) VALUES (
            $newPrecio,
            $newPrecioM,
            $id_vivero,
            $id_pos
        )";
    }

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con) . " - " . $query;
    }
} else if ($consulta == "get_viveros_valores_select") {
    $query = "SELECT id, nombre FROM viveros ORDER BY nombre ASC";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            echo "<option x-nombre='$ww[nombre]' value='$ww[id]'>$ww[nombre] ($ww[id])</option>";
        }
    }
} else if ($consulta == "get_precios_viveros") {
    $id_atributo_tipo_valor = $_POST["id_atributo_tipo_valor"];

    $query = "SELECT atvp.id, atvp.precio, v.nombre, v.id as id_vivero
                FROM atributos_tipos_valores_precios atvp
                INNER JOIN viveros v 
                ON v.id = atvp.id_vivero
                WHERE atvp.id_atributo_tipo_valor = $id_atributo_tipo_valor
                ORDER BY v.nombre ASC
    ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            echo "
                <tr class='text-center'>
                    <td>$ww[nombre] ($ww[id_vivero])</td>
                    <td>$" . (number_format($ww["precio"], 0, ',', '.')) . "</td>
                    <td>
                        <button onclick='eliminarPrecioExtra($ww[id], $id_atributo_tipo_valor)' class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></button>
                    </td>
                </tr>
            ";
        }
    }
} else if ($consulta == "guardar_precio_vivero") {
    $id_atributo_tipo_valor = $_POST["id_atributo_tipo_valor"];
    $id_vivero = $_POST["id_vivero"];
    $precio = mysqli_real_escape_string($con, test_input($_POST["precio"]));

    $query = "SELECT id_vivero, id_atributo_tipo_valor FROM 
            atributos_tipos_valores_precios
            WHERE id_vivero = $id_vivero
            AND id_atributo_tipo_valor = $id_atributo_tipo_valor
    ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) { // YA EXISTE - HAY Q ACTUALIZAR
        $query = "UPDATE atributos_tipos_valores_precios SET
            precio = $precio
            WHERE id_vivero = $id_vivero
            AND id_atributo_tipo_valor = $id_atributo_tipo_valor 
        ";
    } else {
        $query = "INSERT INTO atributos_tipos_valores_precios (
            id_vivero,
            precio,
            id_atributo_tipo_valor
        ) VALUES (
            $id_vivero,
            $precio,
            $id_atributo_tipo_valor
        )";
    }



    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con) . " - " . $query;
    }
} else if ($consulta == "eliminar_precio") {
    $id = $_POST["id"];

    $query = "DELETE FROM atributos_tipos_valores_precios WHERE id = $id";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con) . " - " . $query;
    }
} else if ($consulta == "get_pys_select") {
    $query = "SELECT 
    'P' as tipo,
    p.id, p.nombre, tp.codigo FROM 
    productos p
    INNER JOIN tipos_producto tp 
    ON p.id_tipo = tp.id
    
    UNION ALL
    
    SELECT 
    'S' as tipo,
    s.id, s.nombre, ts.codigo FROM 
    servicios s
    INNER JOIN tipos_servicio ts 
    ON s.id_tipo = ts.id
    
    ORDER BY nombre
    ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            echo "<option x-nombre='$ww[nombre]' x-tipo='$ww[tipo]' value='$ww[id]'>$ww[nombre] ($ww[codigo]) [$ww[tipo]]</option>";
        }
    }
} else if ($consulta == "get_precios_viveros_pys") {
    $id_atributo_tipo_valor = $_POST["id_atributo_tipo_valor"];

    $query = "SELECT atvp.id, atvp.precio, v.nombre, v.id as id_vivero,
                p.nombre as nombre_producto,
                s.nombre as nombre_servicio,
                tp.codigo as codigo_producto,
                ts.codigo as codigo_servicio
                FROM atr_valores_precios_productos_viveros atvp
                INNER JOIN viveros v 
                ON v.id = atvp.id_vivero
                LEFT JOIN productos p
                ON p.id = atvp.id_producto
                LEFT JOIN servicios s
                ON s.id = atvp.id_servicio
                LEFT JOIN tipos_producto tp
                ON tp.id = p.id_tipo
                LEFT JOIN tipos_servicio ts
                ON ts.id = s.id_tipo
                WHERE atvp.id_atributo_tipo_valor = $id_atributo_tipo_valor
                ORDER BY atvp.id DESC
    ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            $pos = "";
            if (isset($ww["nombre_producto"])) {
                $pos = $ww["nombre_producto"] . " ($ww[codigo_producto]) [P]";
            } else if (isset($ww["nombre_servicio"])) {
                $pos = $ww["nombre_servicio"] . " ($ww[codigo_servicio]) [S]";
            }
            echo "
                <tr class='text-center'>
                    <td>$pos</td>
                    <td>$ww[nombre] ($ww[id_vivero])</td>
                    <td>$" . (number_format($ww["precio"], 0, ',', '.')) . "</td>
                    <td>
                        <button onclick='eliminarPrecioExtraPyS($ww[id], $id_atributo_tipo_valor)' class='btn btn-sm btn-danger'><i class='fa fa-trash'></i></button>
                    </td>
                </tr>
            ";
        }
    }
} else if ($consulta == "guardar_precio_vivero_pys") {
    $id_atributo_tipo_valor = $_POST["id_atributo_tipo_valor"];
    $id_vivero = $_POST["id_vivero"];
    $id_pys = $_POST["id_pys"];
    $tipo = $_POST["tipo"];

    $precio = mysqli_real_escape_string($con, test_input($_POST["precio"]));

    $query = "SELECT id, id_vivero, id_atributo_tipo_valor FROM 
            atr_valores_precios_productos_viveros
            WHERE id_vivero = $id_vivero
            AND " . ($tipo == "S" ? "id_servicio" : "id_producto") . " = $id_pys
            AND id_atributo_tipo_valor = $id_atributo_tipo_valor
    ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) { // YA EXISTE - HAY Q ACTUALIZAR
        $r = mysqli_fetch_assoc($val);
        $id = $r["id"];
        $query = "UPDATE atr_valores_precios_productos_viveros SET
            precio = $precio
            WHERE id = $id 
        ";
    } else {
        $query = "INSERT INTO atr_valores_precios_productos_viveros (
            id_vivero,
            " . ($tipo == "S" ? "id_servicio" : "id_producto") . ",
            precio,
            id_atributo_tipo_valor
        ) VALUES (
            $id_vivero,
            $id_pys,
            $precio,
            $id_atributo_tipo_valor
        )";
    }

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con) . " - " . $query;
    }
} else if ($consulta == "eliminar_precio_pys") {
    $id = $_POST["id"];

    $query = "DELETE FROM atr_valores_precios_productos_viveros WHERE id = $id";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        echo mysqli_error($con) . " - " . $query;
    }
} else if ($consulta == "subir_imagen") {
    $tab = $_POST["tab"];
    $id = $_POST["id"];
    $query = "UPDATE $tab SET
            imagen = '$_POST[data]'
            WHERE id = $id
        ";

    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }

}