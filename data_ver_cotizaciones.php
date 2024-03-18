<?php

include "./class_lib/sesionSecurity.php";

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

if ($consulta == "cargar_datos_cliente") {
    $id_cliente = $_POST["id_cliente"];
    $query = "SELECT
        nombre,
        rut,
        domicilio,
        comuna,
        razon_social,
        giro
        FROM clientes WHERE id_cliente = $id_cliente";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        $array = array(
            "nombre" => $ww["nombre"],
            "rut" => $ww["rut"],
            "domicilio" => $ww["domicilio"],
            "comuna" => $ww["comuna"],
            "razon" => $ww["razon_social"],
            "giro" => $ww["giro"],
        );
        echo json_encode($array);
    }
} else if ($consulta == "guardar_cotizacion") {
    $id_cliente = $_POST['id_cliente'];
    $observaciones = mysqli_real_escape_string($con, $_POST['observaciones']);
    $str = json_decode($_POST['jsonarray'], true);
    $id_usuario = $_SESSION["id_usuario"];
    $condicion_pago = $_POST["condicion_pago"];
    $id_cotizacion = $_POST["id_cotizacion"];

    try {
        $errors = array();
        //NUEVA COTIZACION
        if ($id_cotizacion == null || !isset($id_cotizacion) || strlen($id_cotizacion) == 0 || $id_cotizacion < 1) {
            $valor = mysqli_query($con, "SELECT IFNULL(MAX(id)+1, 1) as maximo FROM cotizaciones");
            if (mysqli_num_rows($valor) > 0) {
                $ww = mysqli_fetch_assoc($valor);

                $id_pedido = $ww["maximo"];
                if ((int) $id_pedido > 0) {
                    $uniqid = sha1(uniqid("cot", true));
                    mysqli_autocommit($con, false);
                    if (strlen($observaciones) > 0) {
                        $query = "INSERT INTO cotizaciones (
                        uniqid,
                        id,
                        id_cliente,
                        id_usuario,
                        observaciones,
                        fecha,
                        condicion_pago,
                        monto) VALUES (
                            '$uniqid',
                            $id_pedido,
                            $id_cliente,
                            $id_usuario,
                            UPPER('$observaciones'),
                            NOW(),
                            $condicion_pago,
                            '$_POST[total]'
                        )"; //1 contado, 2 TARJETA
                    } else {
                        $query = "INSERT INTO cotizaciones (
                        uniqid,
                        id,
                        id_cliente,
                        id_usuario,
                        fecha,
                        condicion_pago,
                        monto) VALUES (
                            '$uniqid',
                            $id_pedido,
                            $id_cliente,
                            $id_usuario,
                            NOW(),
                            $condicion_pago,
                            '$_POST[total]'
                        )";
                    }
                    if (!mysqli_query($con, $query)) {
                        $errors[] = mysqli_error($con) . "-" . $query;
                    }

                    for ($i = 0; $i < count($str); $i++) {
                        $id_variedad = $str[$i]["id_variedad"];
                        $cantidad = $str[$i]["cantidad"];
                        $id_especie = $str[$i]["id_especie"];
                        $precio = $str[$i]["precio"];

                        $descuento = $str[$i]["descuento"];

                        $tipo_descuento = "NULL";
                        $valor_descuento = "NULL";
                        if ($descuento != null && isset($descuento) && isset($descuento["tipo"]) && $descuento["tipo"] != null) {
                            if ($descuento["tipo"] == "porcentual") {
                                $tipo_descuento = 1;
                                $valor_descuento = $descuento["valor"];
                            } else if ($descuento["tipo"] == "fijo") {
                                $tipo_descuento = 2;
                                $valor_descuento = $descuento["valor"];
                            }
                        }

                        $id_especie = strlen($id_especie) > 0 ? $id_especie : "NULL";
                        $query = "INSERT INTO cotizaciones_productos (
                        id_variedad,
                        cantidad,
                        id_cotizacion,
                        id_especie,
                        precio_unitario,
                        tipo_descuento,
                        valor_descuento
                        )
                        VALUES (
                            $id_variedad,
                            $cantidad,
                            $id_pedido,
                            $id_especie,
                            '$precio',
                            $tipo_descuento,
                            $valor_descuento
                        );";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                    }
                    if (count($errors) === 0) {
                        if (mysqli_commit($con)) {
                            echo "pedidonum:" . $id_pedido;
                        } else {
                            mysqli_rollback($con);
                        }
                    } else {
                        mysqli_rollback($con);
                        print_r($errors);
                    }
                    mysqli_close($con);
                } else {
                    echo "Error al guardar el pedido. Intentalo de nuevo";
                }
            }
        } else { //ESTOY EDITANDO LA COTIZACION
            $observaciones = $observaciones != null && strlen($observaciones) > 0 ? "UPPER('$observaciones')" : "NULL";
            mysqli_autocommit($con, false);

            $query = "DELETE FROM cotizaciones_productos WHERE id_cotizacion = $id_cotizacion;";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con) . "-" . $query;
            }

            $query = "UPDATE cotizaciones SET
                        id_cliente = $id_cliente,
                        id_usuario = $id_usuario,
                        observaciones = $observaciones,
                        fecha = NOW(),
                        condicion_pago = $condicion_pago,
                        monto = '$_POST[total]'
                        WHERE id = $id_cotizacion
                        ;";

            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con) . "-" . $query;
            }

            for ($i = 0; $i < count($str); $i++) {
                $id_variedad = $str[$i]["id_variedad"];
                $cantidad = $str[$i]["cantidad"];
                $id_especie = $str[$i]["id_especie"];
                $precio = $str[$i]["precio"];

                $descuento = $str[$i]["descuento"];

                $tipo_descuento = "NULL";
                $valor_descuento = "NULL";
                if ($descuento != null && isset($descuento) && isset($descuento["tipo"]) && $descuento["tipo"] != null) {
                    if ($descuento["tipo"] == "porcentual") {
                        $tipo_descuento = 1;
                        $valor_descuento = $descuento["valor"];
                    } else if ($descuento["tipo"] == "fijo") {
                        $tipo_descuento = 2;
                        $valor_descuento = $descuento["valor"];
                    }
                }

                $id_especie = strlen($id_especie) > 0 ? $id_especie : "NULL";
                $query = "INSERT INTO cotizaciones_productos (
                        id_variedad,
                        cantidad,
                        id_cotizacion,
                        id_especie,
                        precio_unitario,
                        tipo_descuento,
                        valor_descuento
                        )
                        VALUES (
                            $id_variedad,
                            $cantidad,
                            $id_cotizacion,
                            $id_especie,
                            '$precio',
                            $tipo_descuento,
                            $valor_descuento
                        );";
                if (!mysqli_query($con, $query)) {
                    $errors[] = mysqli_error($con) . "-" . $query;
                }
            }
            if (count($errors) === 0) {
                if (mysqli_commit($con)) {
                    echo "pedidonum:" . $id_cotizacion;
                } else {
                    mysqli_rollback($con);
                }
            } else {
                mysqli_rollback($con);
                print_r($errors);
            }
            mysqli_close($con);
        }

    } catch (\Throwable $th) {
        echo "error";
    }
} else if ($consulta == "cargar_historial") {
    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
            co.id,
            cl.nombre as cliente,
            cl.id_cliente,
            DATE_FORMAT(co.fecha, '%d/%m/%Y %H:%i') as fecha,
            DATE_FORMAT(co.fecha, '%Y%m%d%H%i') as fecha_raw,
            co.observaciones as comentario,
            co.estado,
            ROUND(co.monto) as monto
            FROM cotizaciones co
            INNER JOIN clientes cl ON cl.id_cliente = co.id_cliente;
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Historial de Cotizaciones</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>N°</th><th>Cliente</th><th>Fecha</th><th style='max-width:200px'>Comentario</th><th>Monto</th><th>Estado</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $estado = boxEstadoCotizacion($ww["estado"], true);
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";
            $boton_eliminar = $_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminarCotizacion($ww[id])'></button>" : "";
            echo "
    <tr class='text-center' style='cursor:pointer' x-id='$ww[id]'>
      <td>$ww[id]</td>
      <td>$ww[cliente] ($ww[id_cliente])</td>
      <td><span class='d-none'>$ww[fecha_raw]</span>$ww[fecha]</td>
      <td>$ww[comentario]</td>
      <td>$monto</td>
      <td onclick='modalCambiarEstado($ww[id])'>$estado</td>
      <td class='text-center'>
            <div class='d-flex flex-row justify-content-center align-items-center'>
                <button onclick='printDataCotizacion($ww[id], this)' class='btn btn-primary fa fa-print btn-sm mr-4'></button>
                $boton_eliminar
            </div>
      </td>
    </tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron cotizaciones...</b></div>";
    }
} else if ($consulta == "eliminar_cotizacion") {
    $rowid = $_POST["rowid"];
    $errors = array();
    mysqli_autocommit($con, false);
    try {
        $query = "DELETE FROM cotizaciones_productos WHERE id_cotizacion = $rowid";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }

        $query = "DELETE FROM cotizaciones WHERE id = $rowid";
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
        //throw $th;
        echo "error: $th";
    }
} else if ($consulta == "cargar_cotizacion") {
    $id = $_POST["id"];

    $directa = $_POST["directa"] != null ? true : false;

    $query = "SELECT
        cl.nombre as cliente,
        cl.rut,
        cl.id_cliente,
        cl.domicilio,
        cl.comuna as id_comuna,
        cl.mail,
        com.nombre as comuna,
        com.ciudad as ciudad,
        DATE_FORMAT(co.fecha, '%d/%m/%Y %H:%i') as fecha,
        co.observaciones as comentario,
        co.condicion_pago,
        co.uniqid,
        cl.giro,
        cl.razon_social,
        ROUND(co.monto) as monto
        FROM clientes cl
        INNER JOIN cotizaciones" . ($directa == true ? "_directas" : "") . " co ON co.id_cliente = cl.id_cliente
        LEFT JOIN comunas com ON cl.comuna = com.id
         WHERE co.id = $id";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);

        $query2 = "SELECT
            v.nombre as nombre_variedad,
            cp.id_variedad as id_variedad_real,
            e.nombre as nombre_especie,
            cp.id_especie as id_especie,
            cp.cantidad,
            v.id_interno as id_variedad,
            t.codigo as codigo_tipo,
            t.nombre as nombre_tipo,
            t.id as id_tipo,
            ROUND(cp.precio_unitario) as precio,
            cp.tipo_descuento,
            cp.valor_descuento
            FROM
            cotizaciones" . ($directa == true ? "_directas" : "") . "_productos cp
            INNER JOIN
            variedades_producto v  ON v.id = cp.id_variedad
            INNER JOIN tipos_producto t ON t.id = v.id_tipo
            LEFT JOIN especies_provistas e ON e.id = cp.id_especie
            WHERE cp.id_cotizacion" . ($directa == true ? "_directa" : "") . " = $id
            ";
        $val2 = mysqli_query($con, $query2);
        if (mysqli_num_rows($val2) > 0) {
            $productos = array();
            while ($ww2 = mysqli_fetch_array($val2)) {
                $subtotal = (int) $ww2["precio"] * (int) $ww2["cantidad"];

                if ($ww2["tipo_descuento"] == 1) { //PORCENTUAL
                    $total = $subtotal - (($subtotal * $ww2["valor_descuento"]) / 100);
                } else if ($ww2["tipo_descuento"] == 2) { //FIJA
                    $total = $subtotal - $ww2["valor_descuento"];
                } else {
                    $total = $subtotal;
                }
                array_push($productos, array(
                    "tipo" => $ww2["nombre_tipo"],
                    "id_tipo" => $ww2["id_tipo"],
                    "variedad" => $ww2["nombre_variedad"],
                    "id_variedad" => $ww2["id_variedad"],
                    "id_variedad_real" => $ww2["id_variedad_real"],
                    "cantidad" => $ww2["cantidad"],
                    "especie" => $ww2["nombre_especie"],
                    "id_especie" => $ww2["id_especie"],
                    "codigo" => $ww2["codigo_tipo"] . str_pad($ww2["id_variedad"], 2, '0', STR_PAD_LEFT),
                    "precio" => $ww2["precio"],
                    "total" => $total,
                    "subtotal" => $subtotal,
                    "descuento" => $ww2["tipo_descuento"] != null && $ww2["tipo_descuento"] > 0 ?
                    array(
                        "tipo" => $ww2["tipo_descuento"] == 1 ? "porcentual" : "fijo",
                        "valor" => $ww2["valor_descuento"],
                    ) : null,
                ));
            }
            $array = array(
                "cliente" => $ww["cliente"],
                "email" => $ww["mail"],
                "uniqid" => $ww["uniqid"],
                "id_cliente" => $ww["id_cliente"],
                "rut" => $ww["rut"],
                "domicilio" => $ww["domicilio"],
                "comuna" => $ww["comuna"],
                "ciudad" => $ww["ciudad"],
                "fecha" => $ww["fecha"],
                "giro" => $ww["giro"],
                "razon" => $ww["razon_social"],
                "comentario" => $ww["comentario"],
                "condicion_pago" => $ww["condicion_pago"],
                "monto" => $ww["monto"],
                "productos" => $productos,
            );
            echo json_encode($array);
        }
    }
} else if ($consulta == "cambiar_estado") {
    $estado = $_POST["estado"];
    $id = $_POST["id"];

    $query = "UPDATE cotizaciones SET estado = $estado WHERE id = $id;";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "guardar_cambios_cliente") {
    $id_cliente = $_POST["id_cliente"];
    $rut = mysqli_real_escape_string($con, $_POST["rut"]);
    $domicilio = mysqli_real_escape_string($con, $_POST["domicilio"]);

    $comuna = $_POST["comuna"];

    $razon_social = mysqli_real_escape_string($con, $_POST["razonSocial"]);
    $giro = mysqli_real_escape_string($con, $_POST["giro"]);

    if (!strlen($domicilio)) {
        $domicilio = "NULL";
    }

    if (!strlen($razon_social)) {
        $razon_social = "NULL";
    }

    if (!strlen($giro)) {
        $giro = "NULL";
    }

    $query = "UPDATE clientes SET rut = '$rut', domicilio = '$domicilio', comuna = $comuna, giro = '$giro', razon_social = '$razon_social' WHERE id_cliente = $id_cliente;";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "pone_comunas") {
    $query = "SELECT * FROM comunas ORDER BY nombre";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            $nombre = mysqli_real_escape_string($con, $re["nombre"]);
            $ciudad = mysqli_real_escape_string($con, $re["ciudad"]);
            echo "<option value='$re[id]' x-nombre='$nombre' x-ciudad='$ciudad'>$re[nombre] ($re[ciudad])</option>";
        }
    }
} else if ($consulta == "get_transportistas_select") {
    $query = "SELECT
                    t.nombre,
                    t.id
                     FROM
                     transportistas t
                     ORDER BY t.nombre ASC";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        while ($re = mysqli_fetch_array($val)) {
            $nombre = mysqli_real_escape_string($con, $re["nombre"]);

            echo "<option value='$re[id]' x-nombre='$nombre'>$re[nombre] ($re[id])</option>";
        }
    }
} else if ($consulta == "get_sucursales_select") {
    $id_transportista = $_POST["id_transportista"];

    if ((int) $id_transportista != 1) {
        $query = "SELECT s.id,
        s.nombre as nombre_sucursal,
        s.direccion
         FROM
         transportistas_sucursales s
         WHERE s.id_transportista = $id_transportista
         ORDER BY s.nombre ASC";
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $nombre = mysqli_real_escape_string($con, $re["nombre_sucursal"]);
                $dire = $re["direccion"];
                if (strlen($dire) > 14) {
                    $dire = substr($dire, 0, 14) . "...";
                }
                $sucu = $re["nombre_sucursal"];
                if (strlen($sucu) > 12) {
                    $sucu = substr($sucu, 0, 12) . "...";
                }
                echo "<option x-direccion='$re[direccion]' value='$re[id]' x-nombre='$nombre'>$sucu [$dire] ($re[id])</option>";
            }
        }
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "https://gateway.starken.cl/externo/integracion/agency/agency");
        $token = "7b14bb8a-9df5-4cea-bb71-c6bc285b2ad7";
        $headers = array(
            "Content-Type: application/json; charset=utf-8",
            "Authorization: Bearer " . $token,
        );

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($ch);
        try {
            $respArray = json_decode($resp);
            foreach ($respArray as $sucursal) {
                # code...
                $direccion = $sucursal->address;
                $id = $sucursal->id;
                $nombre = $sucursal->name;
                                
                if (strlen($direccion) > 40) {
                    $direccion = substr($direccion, 0, 40) . "...";
                }
                if (strlen($nombre) > 40) {
                    $nombre = substr($nombre, 0, 40) . "...";
                }
                echo "<option x-direccion='$direccion' value='$id' x-nombre='$nombre'>$nombre [$direccion] ($id)</option>";
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

} else if ($consulta == "get_cotizacion_qr") {
    $uniqid = $_POST["uniqid"];
    $query = "SELECT id FROM cotizaciones WHERE uniqid = '$uniqid'";
    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0) {
        $v = mysqli_fetch_assoc($val);
        $id = $v["id"];
        echo "id:" . $id;
    }
} else if ($consulta == "get_starken_sucursales") {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, "https://gateway.starken.cl/externo/integracion/agency/agency");
    $token = "7b14bb8a-9df5-4cea-bb71-c6bc285b2ad7";
    $headers = array(
        "Content-Type: application/json; charset=utf-8",
        "Authorization: Bearer " . $token,
    );

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $resp = curl_exec($ch);

    //$respArray = json_decode($resp);
    echo $resp;
} else if ($consulta == "enviar_cotizacion_mail") {
    $file = $_POST["file"];
    $id = $_POST["id"];
    $to = $_POST["email"];

    $link = $_POST["link"];

    $content = chunk_split($file);
    $uid = md5(uniqid(time()));
    $file_name = "cotizacion_$id.pdf";

    $subject = "Cotización";
    $message = "Te enviamos una copia de la cotización que realizaste. Puedes realizar el pago ingresando al siguiente link: $link";

    $from_name = "Roelplant";
    $from_mail = "ventas@roelplant.cl";
    $replyto = "ventas@roelplant.cl";

    $header = "From: " . $from_name . " <" . $from_mail . ">\r\n";
    $header .= "Reply-To: " . $replyto . "\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n\r\n";

    $nmessage = "--" . $uid . "\r\n";
    $nmessage .= "Content-type:text/plain; charset=iso-8859-1\r\n";
    $nmessage .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $nmessage .= $message . "\r\n\r\n";
    $nmessage .= "--" . $uid . "\r\n";
    $nmessage .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"\r\n";
    $nmessage .= "Content-Transfer-Encoding: base64\r\n";
    $nmessage .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\r\n\r\n";
    $nmessage .= $content . "\r\n\r\n";
    $nmessage .= "--" . $uid . "--";

    if (mail($to, $subject, $nmessage, $header)) {
        echo "success";
    } else {
        echo "Hubo un error al enviar el correo.";
    }
}
// else if ($consulta == "genera_uniqid"){
//     $query = "SELECT id FROM cotizaciones";
//     $val = mysqli_query($con, $query);
//     if (mysqli_num_rows($val)){
//         mysqli_autocommit($con, FALSE);
//         $errors = [];
//         while ($v = mysqli_fetch_array($val)){
//             $id = $v["id"];
//             $uniqid = sha1(uniqid("cot-", true));
//             $q = "UPDATE cotizaciones SET uniqid = '$uniqid' WHERE id = $id";
//             if (!mysqli_query($con, $q)) {
//                 $errors[] = mysqli_error($con) . "-" . $q;
//             }
//         }
//         if (count($errors) === 0) {
//             if (mysqli_commit($con)) {
//                 echo "success";
//             } else {
//                 mysqli_rollback($con);
//             }
//         } else {
//             mysqli_rollback($con);
//             print_r($errors);
//         }
//         mysqli_close($con);
//     }
// }
