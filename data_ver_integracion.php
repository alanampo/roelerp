<?php

require "./class_lib/sesionSecurity.php";
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';
require 'class_lib/libredte/vendor/sasco/libredte/examples/inc.php';
header('Content-type: text/html; charset=utf-8');
$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");
$consulta = $_POST["consulta"];
if ($consulta == "cargar_historial_caf") {
    mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
    $query = "SELECT
            f.id,
            f.rango_d,
            f.rango_h,
            f.tipo_documento,
            DATE_FORMAT(f.fecha_carga, '%d/%m/%Y %H:%i') as fecha_carga,
            DATE_FORMAT(f.fecha_carga, '%Y%m%d%H%i') as fecha_carga_raw,
            DATE_FORMAT(f.fecha_autorizacion, '%d/%m/%Y') as fecha_autorizacion,
            DATE_FORMAT(f.fecha_autorizacion, '%Y%m%d') as fecha_autorizacion_raw,
            GROUP_CONCAT(DISTINCT(an.num_folio) SEPARATOR ', ') as anulados,
            (SELECT COUNT(*) FROM folios_anulados an2 WHERE an2.rowid_folio = f.id) as canti_anulados
            FROM folios_caf f
            LEFT JOIN folios_anulados an
            ON f.id = an.rowid_folio
            GROUP BY f.id
            ";

    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {

        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Archivos CAF Cargados</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla_caf' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>ID</th><th>Tipo Doc</th><th>Desde</th><th>Hasta</th><th>F. Anulados</th><th>Fecha Solicitud</th><th>Cantidad Folios</th><th>Fecha Carga</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $boton_eliminar = $_SESSION["id_usuario"] == 1 ? "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminarCAF($ww[id])'></button>" : "";
            $cantidad = (int) $ww["rango_h"] - (int) $ww["rango_d"] + 1 - $ww["canti_anulados"];

            $tipo_doc = (int) $ww["tipo_documento"];
            if ($tipo_doc == 33) {
                $tipo_doc = "Factura Electrónica ($tipo_doc)";
            } else if ($tipo_doc == 61) {
                $tipo_doc = "Nota de Crédito ($tipo_doc)";
            }
            else if ($tipo_doc == 52) {
                $tipo_doc = "Guía Despacho Electrónica ($tipo_doc)";
            }
            else if ($tipo_doc == 39) {
                $tipo_doc = "Boleta Electrónica ($tipo_doc)";
            }
            else if ($tipo_doc == 56) {
                $tipo_doc = "Nota de Débito ($tipo_doc)";
            }

            $btn_anular = ($ww["anulados"] == NULL ? "<button onclick='modalAnular($ww[id], \"$ww[anulados]\", $ww[rango_d], $ww[rango_h])' class='btn btn-sm btn-primary fa fa-edit'></button>" : "<span onclick='modalAnular($ww[id], \"$ww[anulados]\", $ww[rango_d], $ww[rango_h])'>$ww[anulados]</span>");
            echo "
            <tr class='text-center' style='cursor:pointer' x-id='$ww[id]'>
                <td>$ww[id]</td>
                <td>$tipo_doc</td>
                <td>$ww[rango_d]</td>
                <td>$ww[rango_h]</td>
                <td>$btn_anular</td>
                <td><span class='d-none'>$ww[fecha_autorizacion_raw]</span>$ww[fecha_autorizacion]</td>
                <td>$cantidad</td>
                <td><span class='d-none'>$ww[fecha_carga_raw]</span>$ww[fecha_carga]</td>
                <td class='text-center'>
                        <div class='d-flex flex-row justify-content-center align-items-center'>
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
        echo "<div class='callout callout-danger'><b>No se encontraron CAFs...</b></div>";
    }
} else if ($consulta == "subir_caf") {
    $caf = json_decode($_POST["data"], true);
    $fecha_autorizacion = $caf["fechaA"];
    $rango_d = $caf["rangoD"];
    $rango_h = $caf["rangoH"];
    $Folios = new \sasco\LibreDTE\Sii\Folios($_POST["codigo"]);

    if ($Folios->check()) {
        $errores = "";
        foreach (\sasco\LibreDTE\Log::readAll() as $error) {
            $errores .= "$error, \n";
        }

        if (strlen($errores) === 0) {
            $tipo_documento = $Folios->getTipo();

            $query = "SELECT * FROM folios_caf WHERE tipo_documento = $tipo_documento
            AND rango_d = $rango_d 
            AND rango_h = $rango_h
             ;";

            //PPRIMERO del 15 al 30
            //SEGUNDO del 20 al 28
            $val = mysqli_query($con, $query);

            if (mysqli_num_rows($val) > 0) {
                die("Ya existe un CAF con el mismo rango y tipo de documento");
            }
            else{
                $query = "INSERT INTO folios_caf (
                    fecha_carga,
                    rango_d,
                    rango_h,
                    fecha_autorizacion,
                    data,
                    tipo_documento
                ) VALUES (
                    NOW(),
                    $rango_d,
                    $rango_h,
                    '$fecha_autorizacion 00:00:00',
                    '$_POST[codigo]',
                    $tipo_documento
                )";
    
                if (mysqli_query($con, $query)) {
                    echo "success";
                } else {
                    print_r(mysqli_error($con));
                }
            }
        }
    }
} else if ($consulta == "eliminar_caf") {
    $rowid = $_POST["rowid"];
    $val = mysqli_query($con, "(SELECT rowid FROM facturas WHERE caf = $rowid LIMIT 1)
    UNION
    (SELECT rowid FROM notas_credito WHERE caf = $rowid LIMIT 1)
    UNION
    (SELECT rowid FROM notas_debito WHERE caf = $rowid LIMIT 1)
    UNION
    (SELECT rowid FROM guias_despacho WHERE caf = $rowid LIMIT 1)
    ");

    if (mysqli_num_rows($val) > 0) {
        echo "usado";
    } else {
        $errors = array();
        mysqli_autocommit($con, false);
        try {
            $query = "DELETE FROM folios_caf WHERE id = $rowid";
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
    }
} else if ($consulta == "guardar_datos") {
    $razon_social = mysqli_real_escape_string($con, $_POST["razon"]);
    $rut = mysqli_real_escape_string($con, $_POST["rut"]);
    $direccion = mysqli_real_escape_string($con, $_POST["direccion"]);
    $telefono = mysqli_real_escape_string($con, $_POST["telefono"]);
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $giro = mysqli_real_escape_string($con, $_POST["giro"]);
    $numRes = mysqli_real_escape_string($con, $_POST["numRes"]);
    $ftmp = explode("/", $_POST["fechaRes"]);
    $fechaRes = $ftmp[2] . "-" . $ftmp[1] . "-" . $ftmp[0];
    $actEco = mysqli_real_escape_string($con, $_POST["actEco"]);
    $comuna = $_POST["comuna"];

    $pass = $_POST["pass"] && strlen($_POST["pass"]) > 0 ? mysqli_real_escape_string($con, $_POST["pass"]) : null;

    $certificado = strlen($_POST["certificado"]) > 0 ? base64_decode($_POST["certificado"]) : null;

    $query = "SELECT * FROM datos_empresa LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $query = "UPDATE datos_empresa SET
            razon_social = UPPER('$razon_social'),
            rut = UPPER('$rut'),
            direccion = '$direccion',
            telefono = '$telefono',
            email = LOWER('$email'),
            giro = '$giro',
            act_eco = '$actEco',
            fechaRes = '$fechaRes',
            numRes = '$numRes',
            comuna = '$comuna'
        ";

        if (strlen(mysqli_fetch_assoc($val)["certificado"]) < 10) {
            $query .= ",
            certificado = '$_POST[certificado]'";
        }

        if ($pass != null) {
            $query .= ",
            pass = '$pass'";
        }

        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } else {
        $query = "INSERT INTO datos_empresa (
            razon_social,
            rut,
            direccion,
            telefono,
            email,
            giro,
            act_eco,
            fechaRes,
            numRes,
            comuna,
            certificado,
            pass
        ) VALUES (
            UPPER('$razon_social'),
            UPPER('$rut'),
            '$direccion',
            '$telefono',
            LOWER('$email'),
            '$giro',
            '$actEco',
            '$fechaRes',
            '$numRes',
            '$comuna',
            '$_POST[certificado]',
            '$pass'
        )";

        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    }
} else if ($consulta == "load_datos_empresa") {
    $query = "SELECT
            rut,
            razon_social as razon,
            direccion,
            telefono,
            email,
            giro,
            numRes,
            DATE_FORMAT(fechaRes, '%d/%m/%Y') as fechaRes,
            act_eco as actEco,
            comuna,
            certificado,
            logo,
            modo,
            footer1,
            footer2
            FROM datos_empresa LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        echo json_encode(array(
            "rut" => $ww["rut"],
            "razon" => $ww["razon"],
            "direccion" => $ww["direccion"],
            "telefono" => $ww["telefono"],
            "email" => $ww["email"],
            "giro" => $ww["giro"],
            "numRes" => $ww["numRes"],
            "fechaRes" => $ww["fechaRes"],
            "actEco" => $ww["actEco"],
            "comuna" => $ww["comuna"],
            "certificado" => $ww["certificado"] != null && strlen($ww["certificado"]) > 0 ? true : false,
            "logo" => $ww["logo"],
            "modo" => $ww["modo"],
            "footer1" => $ww["footer1"],
            "footer2" => $ww["footer2"],
        ));
    }

} else if ($consulta == "eliminar_certificado") {
    $errors = array();
    mysqli_autocommit($con, false);
    try {
        $query = "UPDATE datos_empresa SET certificado = NULL, pass = NULL;";
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
} else if ($consulta == "subir_logo") {
    $query = "SELECT * FROM datos_empresa LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $query = "UPDATE datos_empresa SET
            logo = '$_POST[data]'
        ";

        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    } else {
        echo "sindatos";
    }
} else if ($consulta == "cambiar_modo") {

    if ((int) $_SESSION["id_usuario"] != 1) {
        die("No estás autorizado a realizar esta operación");
    }

    $modo = (int) $_POST["tipoModo"] == 1 ? "PROD" : "PRUEBA";
    $query = "SELECT
            rut,
            razon_social as razon,
            direccion,
            telefono,
            email,
            giro,
            numRes,
            fechaRes,
            act_eco as actEco,
            comuna,
            certificado,
            logo
            FROM datos_empresa LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        if (
            $ww["rut"] != null &&
            $ww["razon"] != null &&
            $ww["direccion"] != null &&
            $ww["telefono"] != null &&
            $ww["email"] != null &&
            $ww["giro"] != null &&
            $ww["numRes"] != null &&
            $ww["fechaRes"] != null &&
            $ww["actEco"] != null &&
            $ww["comuna"] != null &&
            $ww["certificado"] != null &&
            strlen($ww["certificado"]) > 0 &&
            $ww["logo"] != null
        ) {
            if (mysqli_query($con, "UPDATE datos_empresa SET modo = '$modo'")) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } else {
            echo "faltandatos";
        }
    } else {
        echo "faltandatos";
    }
} else if ($consulta == "load_datos_empresa_print") {
    $query = "SELECT
            rut,
            razon_social as razon,
            direccion,
            telefono,
            email,
            giro,
            com.nombre as comuna,
            logo,
            footer1,
            footer2
            FROM datos_empresa INNER JOIN comunas com ON com.id = datos_empresa.comuna LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $ww = mysqli_fetch_assoc($val);
        echo json_encode(array(
            "rut" => $ww["rut"],
            "razon" => $ww["razon"],
            "direccion" => $ww["direccion"],
            "telefono" => $ww["telefono"],
            "email" => $ww["email"],
            "giro" => $ww["giro"],
            "comuna" => $ww["comuna"],
            "logo" => $ww["logo"],
            "footer1" => $ww["footer1"],
            "footer2" => $ww["footer2"],
        ));
    }
} else if ($consulta == "guardar_footer") {
    $query = "SELECT * FROM datos_empresa LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $footer1 = mysqli_real_escape_string($con, $_POST["footer1"]);
        $footer2 = mysqli_real_escape_string($con, $_POST["footer2"]);

        $query = "UPDATE datos_empresa SET footer1 = '$footer1', footer2 = '$footer2'";

        if (mysqli_query($con, $query)) {
            echo "success";
        } else {
            print_r(mysqli_error($con));
        }
    }
    else{
        echo "faltandatos";
    }
}
else if ($consulta == "anular_folios"){
    $rowid = $_POST["rowid"];
    $seleccionados = $_POST["seleccionados"];

    if (strlen(trim((string)$seleccionados)) == 0){
        $query = "DELETE FROM folios_anulados WHERE rowid_folio = $rowid";
        if (mysqli_query($con, $query)){
            echo "success";
        }
        else{
            print_r(mysqli_error($con));
        }
    }
    else{
        $seleccionados = json_decode($seleccionados, true);
        $errors = array();
        mysqli_autocommit($con, FALSE);

        $query = "DELETE FROM folios_anulados WHERE rowid_folio = $rowid";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }
        foreach ($seleccionados as $folio) {
            $query = "INSERT INTO folios_anulados (
                rowid_folio,
                num_folio
            ) VALUES (
                $rowid,
                $folio
            )";
            if (!mysqli_query($con, $query)) {
                $errors[] = mysqli_error($con) . "-" . $query;
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