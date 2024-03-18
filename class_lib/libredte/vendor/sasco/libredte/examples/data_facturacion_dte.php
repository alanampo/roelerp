<?php

if (strpos($_SERVER['HTTP_HOST'], 'roelplant') !== false) {
    include $_SERVER['DOCUMENT_ROOT'] . "/class_lib/sesionSecurity.php";
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/class_conecta_mysql.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/funciones.php';
} else {
    include $_SERVER['DOCUMENT_ROOT'] . "/erpold/class_lib/sesionSecurity.php";
    require $_SERVER['DOCUMENT_ROOT'] . '/erpold/class_lib/class_conecta_mysql.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/erpold/class_lib/funciones.php';
}

set_time_limit(0);

header('Content-type: text/plain; charset=ISO-8859-1');
include 'inc.php';

$errores = array();

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8mb4'");

\sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::CERTIFICACION);
\sasco\LibreDTE\Sii::setServidor('maullin');

$consulta = $_POST["consulta"];
$GLOBALS['empresa'] = null;
$dir_logo = null;
if ($consulta == "generar_factura" || $consulta == "enviar_mail" || $consulta == "generar_factura_directa" || $consulta == "anular_factura" || $consulta == "anular_factura_antigua" || $consulta == "anular_nc" || $consulta == "get_estado_dte" || $consulta == "imprimir_dte" || $consulta == "generar_guia_despacho" || $consulta == "generar_guia_despacho_desde_cotizaciones" || $consulta == "check_estado_real" || $consulta == "reenviar_factura" || $consulta == "reenviar_nota_credito" || $consulta == "reenviar_nota_debito" || $consulta == "reenviar_guia_despacho" || $consulta == "get_ambiente") {
    $query = "SELECT
            d.rut,
            d.razon_social as razon,
            d.direccion,
            d.telefono,
            d.email,
            d.giro,
            d.numRes,
            DATE_FORMAT(d.fechaRes, '%Y-%m-%d') as fechaRes,
            d.act_eco as actEco,
            com.nombre as comuna,
            d.certificado,
            d.logo,
            d.pass,
            d.modo,
            d.footer1,
            d.footer2
            FROM datos_empresa d
            INNER JOIN comunas com
            ON com.id = d.comuna
             LIMIT 1;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) === 0) {
        exit("Debes completar los Datos de la Empresa en el módulo Integración");
    }

    $GLOBALS["empresa"] = mysqli_fetch_assoc($val);

    if (!$GLOBALS["empresa"]["certificado"] || strlen($GLOBALS["empresa"]["certificado"]) < 10) {
        exit("Debes cargar el Certificado de Autenticación del SII. Dentro del módulo Integración");
    }
    if (!$GLOBALS["empresa"]["logo"] || strlen($GLOBALS["empresa"]["logo"]) < 10) {
        exit("Debes cargar el Logo de la Empresa, el cual se usará para imprimir las Facturas. Dentro del módulo Integración");
    }

    $config2 = [
        'firma' => [
            'data' => base64_decode(str_replace("data:application/x-pkcs12;base64,", "", $GLOBALS["empresa"]["certificado"])), // contenido del archivo certificado.p12
            'pass' => $GLOBALS["empresa"]["pass"],
        ],
    ];

    if ($GLOBALS["empresa"]["modo"] == "PROD") {
        \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::PRODUCCION);
        \sasco\LibreDTE\Sii::setServidor('palena');
    } else {
        \sasco\LibreDTE\Sii::setAmbiente(\sasco\LibreDTE\Sii::CERTIFICACION);
        \sasco\LibreDTE\Sii::setServidor('maullin');
    }
    $_SESSION["footer1"] = $GLOBALS["empresa"]["footer1"];
    $_SESSION["footer2"] = $GLOBALS["empresa"]["footer2"];

    $GLOBALS["Firma"] = new \sasco\LibreDTE\FirmaElectronica($config2['firma']);

    $GLOBALS["caratula"] = [
        'RutEnvia' => $GLOBALS["Firma"]->getID(),
        'RutReceptor' => '60803000-K',
        'FchResol' => $GLOBALS["empresa"]["fechaRes"],
        'NroResol' => $GLOBALS["empresa"]["numRes"],
    ];

    $GLOBALS["Emisor"] = [
        'RUTEmisor' => $GLOBALS["empresa"]["rut"],
        'RznSoc' => $GLOBALS["empresa"]["razon"],
        'GiroEmis' => $GLOBALS["empresa"]["giro"],
        'Acteco' => $GLOBALS["empresa"]["actEco"],
        'DirOrigen' => $GLOBALS["empresa"]["direccion"],
        'CmnaOrigen' => $GLOBALS["empresa"]["comuna"],
        'Telefono' => $GLOBALS["empresa"]["telefono"],
        'CorreoEmisor' => $GLOBALS["empresa"]["email"],
    ];
}

if ($consulta == "generar_factura") {
    $caf = $_POST["caf"];
    $folio = $_POST["folio"];
    $id_cotizacion = $_POST["id_cotizacion"];
    $json = json_decode($_POST["data"], true);

    $id_guia = $_POST["id_guia"];

    $arrErrores = [];

    $tmpFolios = getDataFolios($con, $caf, $id_guia);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }

    $dataFolio = $tmpFolios["data"];
    $folio_guia = $tmpFolios["folio_guia"];

    $id_cotizacion_directa = "NULL";

    if (isset($id_guia) && strlen($id_guia) > 0) {
        $id_cotizacion_directa = $id_cotizacion;
        $id_cotizacion = "NULL";
    }

    $query = "INSERT INTO facturas (
            folio,
            fecha,
            id_cotizacion,
            id_cotizacion_directa,
            caf,
            id_usuario,
            estado
        ) VALUES (
            $folio,
            NOW(),
            $id_cotizacion,
            $id_cotizacion_directa,
            $caf,
            $_SESSION[id_usuario],
            'NOENV'
        )";

    if (mysqli_query($con, $query)) { // SE INSERTO LA FACTURA
        $id_fac = mysqli_insert_id($con);

        $DTEGenerado = generarFactura($json, $dataFolio, $folio, $id_guia, $folio_guia);

        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            $track_id = $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];
            mysqli_autocommit($con, false);
            $query = "UPDATE facturas SET track_id = '$track_id', estado = 'EPR', data = '$datita'" . ($id_guia != null && $id_guia != "null" ? ", id_guia_despacho = $id_guia" : "") . " WHERE rowid = $id_fac";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }

            if ($id_guia != null && $id_guia != "null") {
                $query = "UPDATE guias_despacho SET id_factura = '$id_fac' WHERE rowid = $id_guia";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_IDFAC_GUIA");
                }
            }

            if (mysqli_commit($con)) {
                checkEstadoAndUpdate($id_fac, 0, $con, $track_id);
                $dir_logo = getDataLogo();
                generarPDF(base64_decode($datita), $dir_logo, $track_id, $json["email"]);
                //MODIFICAR ACA
            } else {
                mysqli_rollback($con);
            }
        } else {
            array_push($arrErrores, "ERROR_ENVIO_SII"." ".json_encode($DTEGenerado["errores"]));
        }
    } else {
        array_push($arrErrores, "ERROR_INSERT_FACTURA");
        array_push($errores, mysqli_error($con));
    }

    if (count($arrErrores) > 0) {
        echo (json_encode($arrErrores));
    }
    mysqli_close($con);
} else if ($consulta == "reenviar_factura") {
    $json = json_decode($_POST["data"], true);

    $id_fac = $_POST["rowid_factura"];
    $id_guia = $_POST["id_guia"];
    $dataFolio = getDataFolios($con, $_POST["caf"], $id_guia);

    if (isset($dataFolio["data"])) {
        $DTEGenerado = generarFactura($json, $dataFolio["data"], $_POST["folio"], $id_guia, $dataFolio["folio_guia"]);
        $errores = [];
        $arrErrores = [];
        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            $track_id = $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];
            mysqli_autocommit($con, false);
            $query = "UPDATE facturas SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_fac";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }

            if (isset($id_guia) && strlen($id_guia) > 0) {
                $query = "UPDATE guias_despacho SET id_factura = '$id_fac' WHERE rowid = $id_guia";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_IDFAC_GUIA");
                }
            }

            if (mysqli_commit($con)) {
                checkEstadoAndUpdate($id_fac, 0, $con, $track_id);
                $dir_logo = getDataLogo();
                generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
            } else {
                mysqli_rollback($con);
            }
        } else {
            array_push($arrErrores, "ERROR_ENVIO_SII");
        }
    }

} else if ($consulta == "reenviar_nota_credito") { //GENERAR NOTA DE CREDITO
    $rowid = $_POST["rowid"];
    $folio = $_POST["folio"];
    $caf = $_POST["caf"];

    $rowid_factura = $_POST["rowid_factura"];
    $folio_factura = $_POST["folio_factura"];
    $esFactDirecta = (boolean) json_decode(strtolower($_POST["esDirecta"]));

    $dir_logo = getDataLogo();

    $dataFactura = getDataFactura($con, $rowid_factura, $esFactDirecta);
    if ($dataFactura == null) {
        die("[ERROR_GET_DATA_FACTURA]");
    }

    $tmpFolios = getDataFolios($con, $caf, null);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }

    $dataFolio = $tmpFolios["data"];
    $condicion_pago = getCondicionPago($dataFactura["condicion_pago"]);
    $arrErrores = [];
    $DTEGenerado = generarNotaCredito($dataFolio, $folio, $folio_factura, $dataFactura);

    if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
        mysqli_autocommit($con, false);
        $track_id = $DTEGenerado["trackID"];
        $datita = $DTEGenerado["data"];

        $query = "UPDATE facturas SET estado = 'ANU' WHERE rowid = $rowid_factura;";
        if (!mysqli_query($con, $query)) {
            array_push($errores, mysqli_error($con));
            array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_ESTADO_FACTURA");
        }

        $query = "UPDATE notas_credito SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $rowid";
        if (!mysqli_query($con, $query)) {
            array_push($errores, mysqli_error($con));
            array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
        }

        if (count($arrErrores) === 0) {
            if (mysqli_commit($con)) {
                checkEstadoAndUpdate($rowid, 1, $con, $track_id);
                generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
            } else {
                mysqli_rollback($con);
            }
        }
    } else {
        array_push($arrErrores, "ERROR_ENVIO_SII");
    }

    if (count($arrErrores) > 0) {
        echo (json_encode($arrErrores));
    }
    mysqli_close($con);
} else if ($consulta == "imprimir_dte") {
    $rowid = $_POST["rowid"];
    $tipoDTE = (int) $_POST["tipoDTE"];

    $dir_logo = getDataLogo();

    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito"];

    $query = "SELECT data FROM $tablas[$tipoDTE] WHERE rowid = $rowid;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $re = mysqli_fetch_assoc($val);
        $data = base64_decode($re["data"]);
        generarPDF($data, $dir_logo, null, null);
    }
} else if ($consulta == "enviar_mail") {
    $rowid = $_POST["rowid"];
    $tipoDTE = (int) $_POST["tipoDTE"];
    $folio = (int) $_POST["folio"];
    $email = $_POST["email"];

    $dir_logo = getDataLogo();
    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito"];

    $query = "SELECT d.data FROM $tablas[$tipoDTE] d
     WHERE d.rowid = $rowid;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $re = mysqli_fetch_assoc($val);
        $data = $re["data"];
        $data = base64_decode($re["data"]);
        if (generarPDFMail($data, $dir_logo, $folio, $email, $_POST["link"])) {
            echo "success";
        }
    }
} else if ($consulta == "get_estado_dte") {
    $trackID = $_POST["track_id"];
    echo getEstadoDte($trackID);
} else if ($consulta == "anular_factura") { //GENERAR NOTA DE CREDITO
    $rowid_factura = $_POST["rowid"];
    $folio = $_POST["folio"];
    $folio_factura = $_POST["folioRef"];
    $caf = $_POST["caf"];
    $id_cliente = $_POST["id_cliente"];
    $esFactDirecta = (boolean) json_decode(strtolower($_POST["esFactDirecta"]));
    $comentario = mysqli_real_escape_string($con, $_POST["comentario"]);
    $arrErrores = array();
    $dir_logo = getDataLogo();

    $query = "INSERT INTO notas_credito (
        folio,
        fecha,
        id_factura,
        caf,
        comentario,
        id_cliente,
        estado
        ) VALUES (
            $folio,
            NOW(),
            $rowid_factura,
            $caf,
            '$comentario',
            $id_cliente,
            'NOENV'
        )";

    if (mysqli_query($con, $query)) { // SE INSERTO LA NOTA DE CREDITO
        $id_nc = mysqli_insert_id($con);
        $dataFactura = getDataFactura($con, $rowid_factura, $esFactDirecta);
        if ($dataFactura == null) {
            die("[ERROR_GET_DATA_FACTURA]");
        }

        $tmpFolios = getDataFolios($con, $caf, null);
        if ($tmpFolios == null) {
            die("[ERROR_GET_CAF]");
        }

        $dataFolio = $tmpFolios["data"];
        $condicion_pago = getCondicionPago($dataFactura["condicion_pago"]);

        $DTEGenerado = generarNotaCredito($dataFolio, $folio, $folio_factura, $dataFactura);

        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            mysqli_autocommit($con, false);
            $track_id = $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];

            $query = "UPDATE facturas SET estado = 'ANU' WHERE rowid = $rowid_factura;";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_ESTADO_FACTURA");
            }

            $query = "UPDATE notas_credito SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_nc";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }

            if (count($arrErrores) === 0) {
                if (mysqli_commit($con)) {
                    checkEstadoAndUpdate($id_nc, 1, $con, $track_id);
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
                } else {
                    mysqli_rollback($con);
                }
            }
        } else {
            array_push($arrErrores, "ERROR_ENVIO_SII");
        }
    } else {
        array_push($arrErrores, "ERROR_INSERT_NC");
        array_push($errores, mysqli_error($con));
    }

    if (count($arrErrores) > 0) {
        echo (json_encode($arrErrores));
    }
    mysqli_close($con);
} else if ($consulta == "anular_factura_antigua") {
    $id_cliente = $_POST["id_cliente"];
    $comentario = mysqli_real_escape_string($con, $_POST["observaciones"]);
    $condicion_pago = $_POST["condicion_pago"];
    $numFactura = $_POST["numFactura"];

    $jsonarray = json_decode($_POST["jsonarray"], true);
    $folio = $_POST["folioNC"]; //NOTA
    $caf = $_POST["cafNC"]; //NOTA
    $arrErrores = [];
    $dir_logo = getDataLogo();

    $query = "SELECT
        cl.nombre as cliente,
        cl.rut,
        cl.id_cliente,
        cl.domicilio,
        cl.comuna as id_comuna,
        com.nombre as comuna,
        com.ciudad as ciudad,
        cl.giro,
        cl.razon_social
        FROM clientes cl
        LEFT JOIN comunas com ON cl.comuna = com.id
         WHERE cl.id_cliente = $id_cliente";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0 && count($jsonarray) > 0) {
        $query = "INSERT INTO notas_credito (
            folio,
            fecha,
            id_factura,
            caf,
            comentario,
            id_cliente,
            fac_antigua,
            estado
            ) VALUES (
                $folio,
                NOW(),
                NULL,
                $caf,
                '$comentario',
                $id_cliente,
                $numFactura,
                'NOENV'
            )";
        if (mysqli_query($con, $query)) { // SE INSERTO LA NOTA DE CREDITO
            $ww = mysqli_fetch_assoc($val);
            $id_nc = mysqli_insert_id($con);

            $tmpFolios = getDataFolios($con, $caf, null);
            if ($tmpFolios == null) {
                die("[ERROR_GET_CAF]");
            }

            $dataFolio = $tmpFolios["data"];
            $condicion_pago = getCondicionPago($condicion_pago);

            $productos = [];
            foreach ($jsonarray as $producto) {
                array_push($productos, array(
                    'NmbItem' => $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : ""),
                    'QtyItem' => (int) $producto["cantidad"],
                    'PrcItem' => (int) $producto["precio"],
                    'DescuentoPct' => ($producto["descuento"] && $producto["descuento"]["tipo"] != null && $producto["descuento"]["tipo"] == "porcentual") ? (int) $producto["descuento"]["valor"] : false,
                    'DescuentoMonto' => ($producto["descuento"] && $producto["descuento"]["tipo"] != null && $producto["descuento"]["tipo"] == "fijo") ? (int) $producto["descuento"]["valor"] : false,
                ));
            }

            $dataFactura = array(
                "productos" => $productos,
                "rut" => $ww["rut"],
                "domicilio" => $ww["domicilio"],
                "cliente" => $ww["razon_social"],
                "giro" => $ww["giro"],
                "comuna" => $ww["comuna"],
            );
            $DTEGenerado = generarNotaCredito($dataFolio, $folio, $numFactura, $dataFactura);

            if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
                mysqli_autocommit($con, false);
                $track_id = $DTEGenerado["trackID"];
                $datita = $DTEGenerado["data"];

                $query = "UPDATE notas_credito SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_nc";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
                }

                if (count($arrErrores) === 0) {
                    if (mysqli_commit($con)) {
                        checkEstadoAndUpdate($id_nc, 1, $con, $track_id, $track_id);
                        generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
                    } else {
                        mysqli_rollback($con);
                    }
                }
            } else {
                array_push($arrErrores, "ERROR_ENVIO_SII");
                array_push($arrErrores, $DTEGenerado["errores"]);
            }
        } else {
            array_push($arrErrores, "ERROR_INSERT_NC");
            array_push($errores, mysqli_error($con));
        }

        if (count($arrErrores) > 0) {
            echo (json_encode($arrErrores));
        }
        mysqli_close($con);
    } else {
        die("[ERROR_GET_DATA_CLIENTE]");
    }
} else if ($consulta == "anular_nc") {
    $id_cliente = $_POST["id_cliente"];
    $comentario = mysqli_real_escape_string($con, $_POST["observaciones"]);
    $condicion_pago = $_POST["condicion_pago"];
    $numNC = $_POST["numNC"];
    $rowidNC = $_POST["rowidNC"];

    $jsonarray = json_decode($_POST["jsonarray"], true);
    $folio = $_POST["folioND"]; //NOTA
    $caf = $_POST["cafND"]; //NOTA
    $arrErrores = [];
    $dir_logo = getDataLogo();

    $rowid_new = (isset($_POST["rowid_new"]) && strlen((string) $_POST["rowid_new"] > 0) ? $_POST["rowid_new"] : null);

    $query = "SELECT
        cl.nombre as cliente,
        cl.rut,
        cl.id_cliente,
        cl.domicilio,
        cl.comuna as id_comuna,
        com.nombre as comuna,
        com.ciudad as ciudad,
        cl.giro,
        cl.razon_social
        FROM clientes cl
        LEFT JOIN comunas com ON cl.comuna = com.id
         WHERE cl.id_cliente = $id_cliente";

    $val = mysqli_query($con, $query);
    if (mysqli_num_rows($val) > 0 && count($jsonarray) > 0) {
        if ($rowid_new === null) {
            $query = "INSERT INTO notas_debito (
                folio,
                fecha,
                id_nc,
                caf,
                comentario,
                id_cliente,
                estado,
                id_nc_antigua
                ) VALUES (
                    $folio,
                    NOW(),
                    $rowidNC,
                    $caf,
                    '$comentario',
                    $id_cliente,
                    'NOENV',
                    NULL
                )";
        } else {
            $query = "SELECT * FROM notas_debito WHERE rowid = $rowid_new";
        }
        $inserted = false;
        if (mysqli_query($con, $query)) { // SE INSERTO LA NOTA DE DEBITO
            $ww = mysqli_fetch_assoc($val);
            $id_nd = ($rowid_new != null ? $rowid_new : mysqli_insert_id($con));
            $inserted = true;
            $tmpFolios = getDataFolios($con, $caf, null);
            if ($tmpFolios == null) {
                die("[ERROR_GET_CAF]");
            }

            $dataFolio = $tmpFolios["data"];
            $condicion_pago = getCondicionPago($condicion_pago);

            $productos = [];
            foreach ($jsonarray as $producto) {
                array_push($productos, array(
                    'NmbItem' => $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : ""),
                    'QtyItem' => (int) $producto["cantidad"],
                    'PrcItem' => (int) $producto["precio"],
                    'DescuentoPct' => ($producto["descuento"] && $producto["descuento"]["tipo"] != null && $producto["descuento"]["tipo"] == "porcentual") ? (int) $producto["descuento"]["valor"] : false,
                    'DescuentoMonto' => ($producto["descuento"] && $producto["descuento"]["tipo"] != null && $producto["descuento"]["tipo"] == "fijo") ? (int) $producto["descuento"]["valor"] : false,
                ));
            }

            $dataNC = array(
                "productos" => $productos,
                "rut" => $ww["rut"],
                "domicilio" => $ww["domicilio"],
                "cliente" => $ww["razon_social"],
                "giro" => $ww["giro"],
                "comuna" => $ww["comuna"],
            );
            $DTEGenerado = generarNotaDebito($dataFolio, $folio, $numNC, $dataNC);

            if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
                mysqli_autocommit($con, false);
                $track_id = $DTEGenerado["trackID"];
                $datita = $DTEGenerado["data"];

                $query = "UPDATE notas_debito SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_nd";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
                }

                $query = "UPDATE notas_credito SET estado = 'ANU' WHERE rowid = $rowidNC";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_ANULAR_NC");
                }

                $query = "UPDATE facturas SET estado = 'ACEPTADO' WHERE rowid = (SELECT id_factura FROM notas_credito WHERE rowid = $rowidNC)";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_ACEPTADO_FACT $query");
                }

                if (count($arrErrores) === 0) {
                    if (mysqli_commit($con)) {
                        checkEstadoAndUpdate($id_nd, 3, $con, $track_id);
                        generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
                    } else {
                        mysqli_rollback($con);
                    }
                }
            } else {
                array_push($arrErrores, "ERROR_ENVIO_SII");
                array_push($arrErrores, $DTEGenerado["errores"]);
            }
        } else {
            array_push($arrErrores, "ERROR_INSERT_ND");
            array_push($errores, mysqli_error($con));
        }

        if (count($arrErrores) > 0) {
            echo (json_encode(
                array("errores" => $arrErrores)
            ));

            echo "<rowid_new>" . ($inserted === true && isset($id_nd) ? $id_nd : null) . "</rowid_new>";
        }

        mysqli_close($con);
    } else {
        die("[ERROR_GET_DATA_CLIENTE]");
    }
} else if ($consulta == "get_ambiente") {
    if (\sasco\LibreDTE\Sii::getAmbiente() == \sasco\LibreDTE\Sii::PRODUCCION) {
        echo "MODO PRODUCCION";
    } else if (\sasco\LibreDTE\Sii::getAmbiente() == \sasco\LibreDTE\Sii::CERTIFICACION) {
        echo "MODO PRUEBAS";
    } else {
        echo "MODO DESCONOCIDO";
    }
} else if ($consulta == "generar_factura_directa") {
    $caf = $_POST["caf"];
    $folio = $_POST["folioFACT"];
    $id_cliente = $_POST["id_cliente"];
    $json = json_decode($_POST["jsonarray"], true);
    $monto = $_POST["total"];
    $giro = mysqli_real_escape_string($con, $_POST["giro"]);
    $razon = mysqli_real_escape_string($con, $_POST["razon"]);
    $domicilio = mysqli_real_escape_string($con, $_POST["domicilio"]);
    $comuna = mysqli_real_escape_string($con, $_POST["comuna"]);
    $rut = mysqli_real_escape_string($con, $_POST["rut"]);
    $observaciones = mysqli_real_escape_string($con, $_POST["observaciones"]);
    $arrErrores = array();
    $dir_logo = getDataLogo();

    $tmpFolios = getDataFolios($con, $caf, null);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }
    $dataFolio = $tmpFolios["data"];

    mysqli_autocommit($con, false);

    $query = "INSERT INTO cotizaciones_directas (
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                $id_cliente,
                '$observaciones',
                NOW(),
                $_POST[condicion_pago],
                '$monto'
            )";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con));
        array_push($arrErrores, "ERROR_INSERT_COTD");
    }

    $id_cotizacion_directa = mysqli_insert_id($con);

    foreach ($json as $producto) {
        $id_variedad = $producto["id_variedad"];
        $cantidad = $producto["cantidad"];
        $id_especie = $producto["id_especie"];
        $precio = $producto["precio"];
        $descuento = $producto["descuento"];

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
        $query = "INSERT INTO cotizaciones_directas_productos (
                        id_variedad,
                        cantidad,
                        id_cotizacion_directa,
                        id_especie,
                        precio_unitario,
                        tipo_descuento,
                        valor_descuento
                        )
                        VALUES (
                            $id_variedad,
                            $cantidad,
                            $id_cotizacion_directa,
                            $id_especie,
                            '$precio',
                            $tipo_descuento,
                            $valor_descuento
                        );";
        if (!mysqli_query($con, $query)) {
            array_push($errores, mysqli_error($con));
            array_push($arrErrores, "ERROR_INSERT_COTD_PROD");
        }
    }

    $query = "INSERT INTO facturas (
                folio,
                fecha,
                id_cotizacion_directa,
                caf,
                id_usuario,
                estado
            ) VALUES (
                $folio,
                NOW(),
                $id_cotizacion_directa,
                $caf,
                $_SESSION[id_usuario],
                'NOENV'
            )";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con));
        array_push($arrErrores, "ERROR_INSERT_FACTURA");
    }

    if (count($arrErrores) === 0) {
        $id_fac = mysqli_insert_id($con);
        if (mysqli_commit($con)) {
            $mijson = array(
                "productos" => $json,
                "rut" => $rut,
                "cliente" => $razon,
                "giro" => $giro,
                "domicilio" => $domicilio,
                "comuna" => $comuna,
                "condicion_pago" => $_POST["condicion_pago"],
            );
            $DTEGenerado = generarFactura($mijson, $dataFolio, $folio, null, null);

            if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
                $track_id = $DTEGenerado["trackID"];
                $datita = $DTEGenerado["data"];
                mysqli_autocommit($con, false);
                $query = "UPDATE facturas SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_fac";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
                }

                if (mysqli_commit($con)) {
                    checkEstadoAndUpdate($id_fac, 0, $con, $track_id);
                    $dir_logo = getDataLogo();

                    $val = mysqli_query($con, "SELECT mail FROM clientes WHERE id_cliente = $id_cliente");
                    $email = NULL;
                    if (mysqli_num_rows($val)){
                        $d = mysqli_fetch_assoc($val);
                        
                        if (isset($d["mail"]) && filter_var(isset($d["mail"]), FILTER_VALIDATE_EMAIL)) {
                            $email = $d["mail"];
                        }

                    }
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, $email);
                } else {
                    mysqli_rollback($con);
                }
            } else {
                array_push($arrErrores, "ERROR_ENVIO_SII");
            }
        } else {
            array_push($arrErrores, "ERROR_INSERT_FACTURA");
            array_push($errores, mysqli_error($con));
        }
    }

    if (count($arrErrores) > 0) {
        echo (json_encode($arrErrores));
    }
    mysqli_close($con);
} else if ($consulta == "generar_guia_despacho") {
    $id_cliente = $_POST["id_cliente"];
    $condicion_pago = $_POST["condicion_pago"];
    $giro = $_POST["giro"];
    $comuna = $_POST["comuna"];
    $razon = $_POST["razon"];
    $domicilio = $_POST["domicilio"];
    $total = $_POST["total"];
    $rut = $_POST["rut"];
    $rutTransporte = $_POST["rutTransporte"];
    $rutChofer = $_POST["rutChofer"];
    $patente = $_POST["patente"];
    $nombreChofer = $_POST["nombreChofer"];
    $folio = $_POST["folio"]; //FOLIO DE LA GUIA - TIPO 52
    $caf = $_POST["caf"];
    $comentario = mysqli_real_escape_string($con, $_POST["observaciones"]);

    $dir_logo = getDataLogo();
    $jsonarray = json_decode($_POST["jsonarray"], true);

    $productos = array();
    $errores = array();
    $arrErrores = [];

    $tmpFolios = getDataFolios($con, $caf, null);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }
    $dataFolio = $tmpFolios["data"];
    mysqli_autocommit($con, false);

    $query = "INSERT INTO cotizaciones_directas (
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                $id_cliente,
                '$comentario',
                NOW(),
                $condicion_pago,
                '$total'
            )";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con));
        array_push($arrErrores, "ERROR_INSERT_COTD");
    }

    $id_cotizacion_directa = mysqli_insert_id($con);

    foreach ($jsonarray as $producto) {
        $id_variedad = $producto["id_variedad"];
        $cantidad = $producto["cantidad"];
        $id_especie = $producto["id_especie"];
        $precio = $producto["precio"];
        $descuento = $producto["descuento"];

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
        $query = "INSERT INTO cotizaciones_directas_productos (
                        id_variedad,
                        cantidad,
                        id_cotizacion_directa,
                        id_especie,
                        precio_unitario,
                        tipo_descuento,
                        valor_descuento
                        )
                        VALUES (
                            $id_variedad,
                            $cantidad,
                            $id_cotizacion_directa,
                            $id_especie,
                            '$precio',
                            $tipo_descuento,
                            $valor_descuento
                        );";
        if (!mysqli_query($con, $query)) {
            array_push($errores, mysqli_error($con));
            array_push($arrErrores, "ERROR_INSERT_COTD_PROD");
        }
    }

    $query = "INSERT INTO guias_despacho (
            folio,
            fecha,
            id_factura,
            id_cotizacion_directa,
            caf,
            comentario,
            id_cliente,
            estado
            ) VALUES (
                $folio,
                NOW(),
                NULL,
                $id_cotizacion_directa,
                $caf,
                '$comentario',
                $_POST[id_cliente],
                'NOENV'
            )";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con));
        array_push($arrErrores, "ERROR_INSERT_GUIA");
    }

    if (count($errores) === 0) {
        $id_gd = mysqli_insert_id($con);
        if (mysqli_commit($con)) {
            $json = array(
                "productos" => $jsonarray,
                "rut" => $rut,
                "domicilio" => $domicilio,
                "giro" => $giro,
                "comuna" => $comuna,
                "razon" => $razon,
                "rutChofer" => $rutChofer,
                "rutTransporte" => $rutTransporte,
                "patente" => $patente,
                "nombreChofer" => $nombreChofer,
            );

            $DTEGenerado = generarGuiaDespacho($dataFolio, $folio, $json);

            if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
                $track_id = $DTEGenerado["trackID"];
                $datita = $DTEGenerado["data"];
                mysqli_autocommit($con, false);
                $query = "UPDATE guias_despacho SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_gd";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
                }

                if (mysqli_commit($con)) {
                    checkEstadoAndUpdate($id_gd, 2, $con, $track_id);
                    $dir_logo = getDataLogo();
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
                } else {
                    mysqli_rollback($con);
                }
            } else {
                array_push($arrErrores, "ERROR_ENVIO_SII");
            }
        } else {
            mysqli_rollback($con);
            mysqli_close($con);
            die("[ERROR_INSERT_GUIA]");
        }
    } else {
        mysqli_rollback($con);
        mysqli_close($con);
        die("[ERROR_INSERT_GUIA]");
    }

    mysqli_close($con);

} else if ($consulta == "reenviar_guia_despacho") {
    $rowid = $_POST["rowid"];
    $folio = $_POST["folio"]; //FOLIO DE LA GUIA - TIPO 52
    $caf = $_POST["caf"];

    $dir_logo = getDataLogo();

    $errores = array();
    $arrErrores = [];

    $tmpFolios = getDataFolios($con, $caf, null);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }
    $dataFolio = $tmpFolios["data"];
    mysqli_autocommit($con, false);

    $query = "SELECT transporte_rut as rutTransporte, transporte_patente as patente, transporte_rut_chofer as rutChofer, transporte_nombre_chofer as nombreChofer FROM datos_empresa LIMIT 1";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) === 0) {
        die("[ERROR_FALTAN_DATOS_TRANSPORTE]");
    }

    $ww = mysqli_fetch_assoc($val);
    $dataGuia = getDataCotizacion($con, $rowid, true);
    if ($dataGuia != null) {
        $json = array(
            "productos" => $dataGuia["productos"],
            "rut" => $dataGuia["rut"],
            "domicilio" => $dataGuia["domicilio"],
            "giro" => $dataGuia["giro"],
            "comuna" => $dataGuia["comuna"],
            "razon" => $dataGuia["razon"],
            "rutChofer" => $ww["rutChofer"],
            "rutTransporte" => $ww["rutTransporte"],
            "patente" => $ww["patente"],
            "nombreChofer" => $ww["nombreChofer"],
        );

        $DTEGenerado = generarGuiaDespacho($dataFolio, $folio, $json);

        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            $track_id = $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];
            mysqli_autocommit($con, false);
            $query = "UPDATE guias_despacho SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $rowid";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }

            if (mysqli_commit($con)) {
                checkEstadoAndUpdate($rowid, 2, $con, $track_id);
                $dir_logo = getDataLogo();
                generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
            } else {
                mysqli_rollback($con);
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }
        } else {
            array_push($arrErrores, "ERROR_ENVIO_SII");
        }

    } else {
        die("[ERROR_GET_DATA_GUIA]");
    }

    if (count($arrErrores) > 0) {
        echo json_encode($arrErrores);
    }

    mysqli_close($con);
} else if ($consulta == "generar_guia_despacho_desde_cotizaciones") {
    $jsonarray = [];

    $id_cotizacion = $_POST["id_cotizacion"];
    $total = $_POST["total"];
    $rutTransporte = $_POST["rutTransporte"];
    $rutChofer = $_POST["rutChofer"];
    $patente = $_POST["patente"];
    $nombreChofer = $_POST["nombreChofer"];
    $folio = $_POST["folio"]; //FOLIO DE LA GUIA - TIPO 52
    $caf = $_POST["caf"];

    $dir_logo = getDataLogo();

    $productos = array();
    $errores = array();
    $arrErrores = [];

    $tmpFolios = getDataFolios($con, $caf, null);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }
    $dataFolio = $tmpFolios["data"];

    $query = "SELECT
                co.id_cliente,
                co.condicion_pago,
                co.observaciones,

                cl.giro,
                cl.razon_social as razon,
                cl.domicilio,
                cl.rut,
                com.nombre as comuna

                FROM cotizaciones co INNER JOIN
                clientes cl ON cl.id_cliente = co.id_cliente
                LEFT JOIN comunas com ON com.id = cl.comuna
                WHERE co.id = $id_cotizacion
                    ";
    $val = mysqli_query($con, $query);

    if (!mysqli_num_rows($val)) {
        die("No se pudieron recuperar los datos de la Cotización");
    }

    $dataCot = mysqli_fetch_assoc($val);

    $query = "SELECT
                        va.nombre as nombre_variedad,
                        es.nombre as nombre_especie,
                        va.id_interno,
                        t.codigo,


                        cop.id_variedad,
                        cop.id_especie,
                        cop.cantidad,
                        cop.precio_unitario as precio,
                        cop.tipo_descuento,
                        cop.valor_descuento
                        FROM cotizaciones_productos cop
                        LEFT JOIN variedades_producto va ON
                        cop.id_variedad = va.id
                        LEFT JOIN especies_provistas es ON cop.id_especie = es.id
                        LEFT JOIN tipos_producto t ON t.id = va.id_tipo
                        WHERE cop.id_cotizacion = $id_cotizacion
                ";
    $val2 = mysqli_query($con, $query);
    if (!mysqli_num_rows($val)) {
        die("No se pudieron recuperar los datos de Cotización/Productos");
    }

    mysqli_autocommit($con, false);

    $query = "INSERT INTO cotizaciones_directas (
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                $dataCot[id_cliente],
                '$dataCot[observaciones]',
                NOW(),
                $dataCot[condicion_pago],
                '$total'
            )";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con) . $query);
        array_push($arrErrores, "ERROR_INSERT_COTD");
    }

    $id_cotizacion_directa = mysqli_insert_id($con);

    while ($producto = mysqli_fetch_array($val2)) {
        $id_variedad = $producto["id_variedad"];
        $cantidad = $producto["cantidad"];
        $id_especie = $producto["id_especie"];
        $precio = $producto["precio"];
        $tipo_descuento = isset($producto["tipo_descuento"]) && strlen($producto["tipo_descuento"]) ? $producto["tipo_descuento"] : "NULL";
        $valor_descuento = isset($producto["valor_descuento"]) && strlen($producto["valor_descuento"]) ? $producto["valor_descuento"] : "NULL";

        $id_especie = isset($id_especie) && strlen($id_especie) > 0 ? $id_especie : "NULL";

        array_push($jsonarray, array(
            "variedad" => $producto["nombre_variedad"],
            "codigo" => $producto["codigo"] . str_pad($producto["id_interno"], 2, '0', STR_PAD_LEFT),
            "especie" => $producto["nombre_especie"],

            "id_variedad" => $id_variedad,
            "cantidad" => $cantidad,
            "id_especie" => isset($producto["id_especie"]) ? $producto["id_especie"] : null,
            "precio" => $precio,
            "descuento" => isset($producto["tipo_descuento"]) ? array(
                "tipo" => $tipo_descuento == 1 ? "porcentual" : "fijo",
                "valor" => $valor_descuento,
            ) : null,
        ));

        $query = "INSERT INTO cotizaciones_directas_productos (
                        id_variedad,
                        cantidad,
                        id_cotizacion_directa,
                        id_especie,
                        precio_unitario,
                        tipo_descuento,
                        valor_descuento
                        )
                        VALUES (
                            $id_variedad,
                            $cantidad,
                            $id_cotizacion_directa,
                            $id_especie,
                            '$precio',
                            $tipo_descuento,
                            $valor_descuento
                        );";
        if (!mysqli_query($con, $query)) {
            array_push($errores, mysqli_error($con) . $query);
            array_push($arrErrores, "ERROR_INSERT_COTD_PROD");
        }
    }

    $query = "INSERT INTO guias_despacho (
            folio,
            fecha,
            id_factura,
            id_cotizacion_directa,
            caf,
            comentario,
            id_cliente,
            estado
            ) VALUES (
                $folio,
                NOW(),
                NULL,
                $id_cotizacion_directa,
                $caf,
                '$dataCot[observaciones]',
                $dataCot[id_cliente],
                'NOENV'
            )";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con) . $query);
        array_push($arrErrores, "ERROR_INSERT_GUIA1");
    }

    $id_gd = mysqli_insert_id($con);

    $query = "DELETE FROM cotizaciones_productos WHERE id_cotizacion = $id_cotizacion;";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con));
        array_push($arrErrores, "ERROR_DELETE_OLDCOTP");
    }

    $query = "DELETE FROM cotizaciones WHERE id = $id_cotizacion;";

    if (!mysqli_query($con, $query)) {
        array_push($errores, mysqli_error($con) . $query);
        array_push($arrErrores, "ERROR_DELETE_OLDCOT");
    }

    if (count($errores) === 0) {

        if (mysqli_commit($con)) {
            $json = array(
                "productos" => $jsonarray,
                "rut" => $dataCot["rut"],
                "domicilio" => $dataCot["domicilio"],
                "giro" => $dataCot["giro"],
                "comuna" => $dataCot["comuna"],
                "razon" => $dataCot["razon"],
                "rutChofer" => $rutChofer,
                "rutTransporte" => $rutTransporte,
                "patente" => $patente,
                "nombreChofer" => $nombreChofer,
            );

            $DTEGenerado = generarGuiaDespacho($dataFolio, $folio, $json);

            if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
                $track_id = $DTEGenerado["trackID"];
                $datita = $DTEGenerado["data"];
                mysqli_autocommit($con, false);
                $query = "UPDATE guias_despacho SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_gd";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
                }

                if (mysqli_commit($con)) {
                    checkEstadoAndUpdate($id_gd, 2, $con, $track_id);
                    $dir_logo = getDataLogo();
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
                } else {
                    mysqli_rollback($con);
                }
            } else {
                array_push($arrErrores, "ERROR_ENVIO_SII");
            }
        } else {
            mysqli_rollback($con);
            mysqli_close($con);
            die("[ERROR_INSERT_GUIA]2");
        }
    } else {
        mysqli_rollback($con);
        //die("[ERROR_INSERT_GUIA]3");
        print_r($errores);
    }

    mysqli_close($con);

}

function checkEstadoAndUpdate($id_fac, $tipoDTE, $con, $track_id)
{
    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito"];
    $estado = getEstadoDte($track_id);
    if ($estado != null) {
        $result = json_decode($estado, true);

        if (isset($result["aceptados"]) && (int) $result["aceptados"] == 1) {
            $query = "UPDATE $tablas[$tipoDTE] SET estado = 'ACEPTADO' WHERE rowid = $id_fac";
            mysqli_autocommit($con, true);
            mysqli_query($con, $query);
        } else if (isset($result["rechazados"]) && (int) $result["rechazados"] == 1) {
            $query = "UPDATE $tablas[$tipoDTE] SET estado = 'RECHAZADO' WHERE rowid = $id_fac";
            mysqli_autocommit($con, true);
            mysqli_query($con, $query);
        }
    }
}

function generarPDF($data, $dir_logo, $track_id, $email)
{
    // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
    $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
    $EnvioDte->loadXML($data);
    $Caratula = $EnvioDte->getCaratula();
    $Documentos = $EnvioDte->getDocumentos();

// directorio temporal para guardar los PDF
    $dir = sys_get_temp_dir() . '/dte_' . $Caratula['RutEmisor'] . '_' . $Caratula['RutReceptor'] . '_' . str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
    if (is_dir($dir)) {
        \sasco\LibreDTE\File::rmdir($dir);
    }

    if (!mkdir($dir)) {
        die('No fue posible crear directorio temporal para DTEs');
    }

// procesar cada DTEs e ir agregándolo al PDF
    foreach ($Documentos as $DTE) {
        if (!$DTE->getDatos()) {
            die('No se pudieron obtener los datos del DTE');
        }

        $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte(false); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)
        $pdf->setFooterText(true, $_SESSION["footer1"], $_SESSION["footer2"]);
        $pdf->setLogo($dir_logo); // debe ser PNG!
        $pdf->setResolucion(['FchResol' => $Caratula['FchResol'], 'NroResol' => $Caratula['NroResol']]);
        $pdf->agregar($DTE->getDatos(), $DTE->getTED());

        $path = $dir . 'dte_' . $Caratula['RutEmisor'] . '_' . $DTE->getID() . '.pdf';
        $pdf->Output($path, 'F');

        echo json_encode(array(
            "path" => $path,
            "trackID" => $track_id,
        ));

        if (isset($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $id = $DTE->getID();

            $content = file_get_contents($path);
            $content = chunk_split(base64_encode($content));
            $uid = md5(uniqid(time()));
            $file_name = "factura_$id.pdf";

            $subject = "Factura $id";

            $message = "Te enviamos una copia de la Factura N° $id correspondiente a tu compra.";
            
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
            $nmessage .= "Content-Type: application/octet-stream; name=\"" . $file_name . "\"\r\n";
            $nmessage .= "Content-Transfer-Encoding: base64\r\n";
            $nmessage .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\r\n\r\n";
            $nmessage .= $content . "\r\n\r\n";
            $nmessage .= "--" . $uid . "--";

            mail($email, $subject, $nmessage, $header);
        }
    }
}

function generarPDFMail($data, $dir_logo, $id, $email, $link)
{
    // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
    $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
    $EnvioDte->loadXML($data);
    $Caratula = $EnvioDte->getCaratula();
    $Documentos = $EnvioDte->getDocumentos();

    // directorio temporal para guardar los PDF
    $dir = sys_get_temp_dir() . '/dte_' . $Caratula['RutEmisor'] . '_' . $Caratula['RutReceptor'] . '_' . str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
    if (is_dir($dir)) {
        \sasco\LibreDTE\File::rmdir($dir);
    }

    if (!mkdir($dir)) {
        die('No fue posible crear directorio temporal para DTEs');
    }

    // procesar cada DTEs e ir agregándolo al PDF
    $DTE = $Documentos[0];
    if (!$DTE->getDatos()) {
        die('No se pudieron obtener los datos del DTE');
    }

    $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte(false); // =false hoja carta, =true papel contínuo (false por defecto si no se pasa)
    $pdf->setFooterText(true, $_SESSION["footer1"], $_SESSION["footer2"]);
    $pdf->setLogo($dir_logo); // debe ser PNG!
    $pdf->setResolucion(['FchResol' => $Caratula['FchResol'], 'NroResol' => $Caratula['NroResol']]);
    $pdf->agregar($DTE->getDatos(), $DTE->getTED());

    $path = $dir . 'dte_' . $Caratula['RutEmisor'] . '_' . $DTE->getID() . '.pdf';
    $pdf->Output($path, 'F');

    $content = file_get_contents($path);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $file_name = "factura_$id.pdf";

    $subject = "Factura $id";

    $message = "Te enviamos una copia de la Factura N° $id correspondiente a tu compra.";
    if (isset($link) && strlen($link) > 0) {
        $message .= " Puedes realizar el pago ingresando al siguiente link: $link";
    }

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
    $nmessage .= "Content-Type: application/octet-stream; name=\"" . $file_name . "\"\r\n";
    $nmessage .= "Content-Transfer-Encoding: base64\r\n";
    $nmessage .= "Content-Disposition: attachment; filename=\"" . $file_name . "\"\r\n\r\n";
    $nmessage .= $content . "\r\n\r\n";
    $nmessage .= "--" . $uid . "--";

    if (mail($email, $subject, $nmessage, $header)) {
        return true;
    }
    return false;
}

function getDataLogo()
{
    $dir_logotmp = null;
    try {
        $dir_logotmp = sys_get_temp_dir() . '/dte_logo.png';
        $dataimg = str_replace("data:image/png;base64,", "", $GLOBALS["empresa"]["logo"]);
        $dataimg = str_replace("data:image/jpeg;base64,", "", $dataimg);
        $dataimg = str_replace("data:image/jpg;base64,", "", $dataimg);
        file_put_contents($dir_logotmp, base64_decode($dataimg));
        return $dir_logotmp;
    } catch (\Throwable$th) {
        die("Error al cargar el logo para las impresiones");
    }
    return null;
}

function getCondicionPago($val)
{
    $condicion_pago = "";
    if ((int) $val == 0) {
        $condicion_pago = "CONTADO";
    } else if ((int) $val == 1) {
        $condicion_pago = "CRÉDITO 30 DÍAS";
    } else if ((int) $val == 2) {
        $condicion_pago = "CRÉDITO 60 DÍAS";
    } else if ((int) $val == 3) {
        $condicion_pago = "CRÉDITO 90 DÍAS";
    }
    return $condicion_pago;
}

function generarFactura($json, $dataFolio, $folio, $id_guia, $folio_guia)
{
    $arrayproductos = array();
    foreach ($json["productos"] as $producto) {
        array_push($arrayproductos, array(
            'NmbItem' => $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : ""),
            'QtyItem' => (int) $producto["cantidad"],
            'PrcItem' => (int) $producto["precio"],
            'DescuentoPct' => $producto["descuento"] && $producto["descuento"]["tipo"] == "porcentual" ? (int) $producto["descuento"]["valor"] : false,
            'DescuentoMonto' => $producto["descuento"] && $producto["descuento"]["tipo"] == "fijo" ? (int) $producto["descuento"]["valor"] : false,
        ));
    }

    $condicion_pago = getCondicionPago($json["condicion_pago"]);

    $set_pruebas = [
        [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                    'Folio' => $folio,
                ],
                'Emisor' => $GLOBALS["Emisor"],
                'Receptor' => [
                    'RUTRecep' => $json["rut"],
                    'RznSocRecep' => $json["cliente"],
                    'GiroRecep' => $json["giro"] ? strtoupper($json["giro"]) : "-",
                    'DirRecep' => $json["domicilio"],
                    'CmnaRecep' => $json["comuna"] ? strtoupper($json["comuna"]) : "-",
                ],
            ],
            'Detalle' => $arrayproductos,
            'Referencia' => [
                'TpoDocRef' => ($id_guia != null && $id_guia != "null" ? 52 : 33),
                'FolioRef' => ($id_guia != null && $id_guia != "null" ? $folio_guia : $folio),
                'RazonRef' => $condicion_pago,
            ],
        ],
    ];

    $Folios = [];
    $Folios[33] = new \sasco\LibreDTE\Sii\Folios($dataFolio);
    $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();

    // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
    foreach ($set_pruebas as $documento) {
        $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
        if (!$DTE->timbrar($Folios[$DTE->getTipo()])) {
            break;
        }

        if (!$DTE->firmar($GLOBALS["Firma"])) {
            break;
        }

        $EnvioDTE->agregar($DTE);
    }

    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($GLOBALS["caratula"]);
    $EnvioDTE->setFirma($GLOBALS["Firma"]);

    $dataDTE = $EnvioDTE->generar();
    $datita = base64_encode($dataDTE);

    $track_id = $EnvioDTE->enviar();

    $err = array();
    foreach (\sasco\LibreDTE\Log::readAll() as $error) {
        array_push($err, $error);
    }

    if (count($err) > 0) {
        return array(
            "errores" => $err,
        );
    } else if (!$track_id) {
        return array(
            "errores" => "Error al enviar al SII",
        );
    } else {
        return array(
            "trackID" => $track_id,
            "data" => $datita,
        );
    }
}

function getDataFolios($con, $caf, $id_guia)
{
    $query = "SELECT data FROM folios_caf WHERE id = $caf;";
    $val = mysqli_query($con, $query);
    $dataFolio = null;
    $folio_guia = null;

    $err = false;
    if (mysqli_num_rows($val) > 0) { //CARGO XML DEL CAF
        $re = mysqli_fetch_assoc($val);
        $dataFolio = $re["data"];

        if ($id_guia != null && $id_guia != "null") { //SI ES GUIA DESPACHO BUSCO EL FOLIO TAMBIEN
            $query = "SELECT folio FROM guias_despacho WHERE rowid = $id_guia;";
            $val = mysqli_query($con, $query);

            if (mysqli_num_rows($val) > 0) {
                $re = mysqli_fetch_assoc($val);
                $folio_guia = $re["folio"];
            } else {
                $err = true;
            }
        }
    } else {
        $err = true;
    }

    if ($err === false) {
        return array(
            "data" => $dataFolio,
            "folio_guia" => $folio_guia,
        );
    }
    return null;
}

function getDataFactura($con, $rowid_factura, $esFactDirecta)
{
    $query = "SELECT
            cl.nombre as cliente,
            cl.rut,
            cl.id_cliente,
            cl.domicilio,
            cl.comuna as id_comuna,
            com.nombre as comuna,
            co.condicion_pago,
            cl.giro,
            cl.razon_social
            FROM clientes cl ";

    if ((bool) $esFactDirecta === true) {
        $query .= " INNER JOIN cotizaciones_directas co ON co.id_cliente = cl.id_cliente
                LEFT JOIN comunas com ON cl.comuna = com.id
                WHERE co.id = (SELECT f.id_cotizacion_directa FROM facturas f WHERE f.rowid = $rowid_factura)";
    } else {
        $query .= " INNER JOIN cotizaciones co ON co.id_cliente = cl.id_cliente
                LEFT JOIN comunas com ON cl.comuna = com.id
                WHERE co.id = (SELECT f.id_cotizacion FROM facturas f WHERE f.rowid = $rowid_factura)";
    }

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
                " . ($esFactDirecta === true ? "cotizaciones_directas_productos" : "cotizaciones_productos") . " cp
                INNER JOIN
                variedades_producto v  ON v.id = cp.id_variedad
                INNER JOIN tipos_producto t ON t.id = v.id_tipo
                LEFT JOIN especies_provistas e ON e.id = cp.id_especie
                WHERE cp.id_cotizacion" . ($esFactDirecta ? "_directa" : "") . " = (SELECT " . ($esFactDirecta === true ? "f.id_cotizacion_directa" : "f.id_cotizacion") . " FROM facturas f WHERE f.rowid = $rowid_factura)
                ";

        $val2 = mysqli_query($con, $query2);
        if (mysqli_num_rows($val2) > 0) {
            $productos = array();
            while ($ww2 = mysqli_fetch_array($val2)) {
                array_push($productos, array(
                    'NmbItem' => $ww2["nombre_variedad"] . " " . $ww2["nombre_especie"] . "(" . $ww2["codigo_tipo"] . str_pad($ww2["id_variedad"], 2, '0', STR_PAD_LEFT) . ") " . ($ww2["nombre_especie"] && strlen($ww2["nombre_especie"]) > 0 ? $ww2["nombre_especie"] : ""),
                    'QtyItem' => (int) $ww2["cantidad"],
                    'PrcItem' => (int) $ww2["precio"],
                    'DescuentoPct' => ($ww2["tipo_descuento"] != null && $ww2["tipo_descuento"] == 1) ? (int) $ww2["valor_descuento"] : false,
                    'DescuentoMonto' => ($ww2["tipo_descuento"] != null && $ww2["tipo_descuento"] == 2) ? (int) $ww2["valor_descuento"] : false,
                ));
            }

            return array(
                "cliente" => $ww["cliente"],
                "rut" => $ww["rut"],
                "id_cliente" => $ww["id_cliente"],
                "domicilio" => $ww["domicilio"],
                "comuna" => $ww["comuna"],
                "condicion_pago" => $ww["condicion_pago"],
                "giro" => $ww["giro"],
                "razon_social" => $ww["razon_social"],
                "productos" => $productos,
            );
        }

        return null;
    }
}

function generarNotaCredito($dataFolio, $folio, $folio_factura, $dataFactura)
{
    // datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
    $set_pruebas = [
        [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 61,
                    'Folio' => $folio,
                ],
                'Emisor' => $GLOBALS["Emisor"],
                'Receptor' => [
                    'RUTRecep' => $dataFactura["rut"],
                    'RznSocRecep' => $dataFactura["cliente"],
                    'GiroRecep' => $dataFactura["giro"] ? strtoupper($dataFactura["giro"]) : "-",
                    'DirRecep' => $dataFactura["domicilio"],
                    'CmnaRecep' => $dataFactura["comuna"] ? strtoupper($dataFactura["comuna"]) : "-",
                ],
                'Totales' => [
                    // estos valores serán calculados automáticamente
                    'MntNeto' => 0,
                    'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                    'IVA' => 0,
                    'MntTotal' => 0,
                ],
            ],
            'Detalle' => $dataFactura["productos"],
            'Referencia' => [
                [
                    'TpoDocRef' => 33,
                    'FolioRef' => $folio_factura,
                    'CodRef' => 1,
                    'RazonRef' => "ANULA FACTURA",
                ],
            ],
        ],
    ];

    $Folios = [];
    $Folios[61] = new \sasco\LibreDTE\Sii\Folios($dataFolio);

    $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();

    // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
    foreach ($set_pruebas as $documento) {
        $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
        if (!$DTE->timbrar($Folios[$DTE->getTipo()])) {
            break;
        }

        if (!$DTE->firmar($GLOBALS["Firma"])) {
            break;
        }
        $EnvioDTE->agregar($DTE);
    }

    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($GLOBALS["caratula"]);
    $EnvioDTE->setFirma($GLOBALS["Firma"]);

    $dataDTE = $EnvioDTE->generar();
    $datita = base64_encode($dataDTE);

    $track_id = $EnvioDTE->enviar();

    $err = array();
    foreach (\sasco\LibreDTE\Log::readAll() as $error) {
        array_push($err, $error);
    }

    if (count($err) > 0) {
        return array(
            "errores" => $err,
        );
    } else if (!$track_id) {
        return array(
            "errores" => "Error al enviar al SII",
        );
    } else {
        return array(
            "trackID" => $track_id,
            "data" => $datita,
        );
    }
}

function generarNotaDebito($dataFolio, $folio, $folio_nc, $dataFactura)
{
    // datos de los DTE (cada elemento del arreglo $set_pruebas es un DTE)
    $set_pruebas = [
        [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 56,
                    'Folio' => $folio,
                ],
                'Emisor' => $GLOBALS["Emisor"],
                'Receptor' => [
                    'RUTRecep' => $dataFactura["rut"],
                    'RznSocRecep' => $dataFactura["cliente"],
                    'GiroRecep' => $dataFactura["giro"] ? strtoupper($dataFactura["giro"]) : "-",
                    'DirRecep' => $dataFactura["domicilio"],
                    'CmnaRecep' => $dataFactura["comuna"] ? strtoupper($dataFactura["comuna"]) : "-",
                ],
                'Totales' => [
                    // estos valores serán calculados automáticamente
                    'MntNeto' => 0,
                    'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                    'IVA' => 0,
                    'MntTotal' => 0,
                ],
            ],
            'Detalle' => $dataFactura["productos"],
            'Referencia' => [
                [
                    'TpoDocRef' => 61,
                    'FolioRef' => $folio_nc,
                    'CodRef' => 1,
                    'RazonRef' => "ANULA NC",
                ],
            ],
        ],
    ];

    $Folios = [];
    $Folios[56] = new \sasco\LibreDTE\Sii\Folios($dataFolio);

    $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();

    // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
    foreach ($set_pruebas as $documento) {
        $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
        if (!$DTE->timbrar($Folios[$DTE->getTipo()])) {
            break;
        }

        if (!$DTE->firmar($GLOBALS["Firma"])) {
            break;
        }
        $EnvioDTE->agregar($DTE);
    }

    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($GLOBALS["caratula"]);
    $EnvioDTE->setFirma($GLOBALS["Firma"]);

    $dataDTE = $EnvioDTE->generar();
    $datita = base64_encode($dataDTE);

    $track_id = $EnvioDTE->enviar();

    $err = array();
    foreach (\sasco\LibreDTE\Log::readAll() as $error) {
        array_push($err, $error);
    }

    if (count($err) > 0) {
        return array(
            "errores" => $err,
        );
    } else if (!$track_id) {
        return array(
            "errores" => "Error al enviar al SII",
        );
    } else {
        return array(
            "trackID" => $track_id,
            "data" => $datita,
        );
    }
}

function generarGuiaDespacho($dataFolio, $folio, $json)
{
    $productos = [];
    foreach ($json["productos"] as $producto) {
        array_push($productos, array(
            'NmbItem' => $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : ""),
            'QtyItem' => (int) $producto["cantidad"],
            'PrcItem' => (int) $producto["precio"],
            'DescuentoPct' => ($producto["descuento"] && $producto["descuento"]["tipo"] != null && $producto["descuento"]["tipo"] == "porcentual") ? (int) $producto["descuento"]["valor"] : false,
            'DescuentoMonto' => ($producto["descuento"] && $producto["descuento"]["tipo"] != null && $producto["descuento"]["tipo"] == "fijo") ? (int) $producto["descuento"]["valor"] : false,
        ));
    }

    $set_pruebas = [
        [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 52,
                    'Folio' => $folio,
                    'TipoDespacho' => 1,
                    'IndTraslado' => 1,
                ],
                'Emisor' => $GLOBALS["Emisor"],
                'Receptor' => [
                    'RUTRecep' => $json["rut"],
                    'RznSocRecep' => $json["razon"],
                    'GiroRecep' => $json["giro"],
                    'DirRecep' => $json["domicilio"],
                    'CmnaRecep' => $json["comuna"],
                ],
                'Totales' => [
                    // estos valores serán calculados automáticamente
                    'MntNeto' => 0,
                    'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                    'IVA' => 0,
                    'MntTotal' => 0,
                ],
                "Transporte" => [
                    "Patente" => strtoupper($json["patente"]),
                    "RUTTrans" => strtoupper($json["rutTransporte"]),
                    "Chofer" => [
                        "RUTChofer" => strtoupper($json["rutChofer"]),
                        "NombreChofer" => strtoupper($json["nombreChofer"]),
                    ],
                    "DirDest" => "Dirección del Cliente",
                    "CmnaDest" => "(" . $json["comuna"] . ")",
                ],
            ],
            'Detalle' => $productos,
        ],
    ];

    // Objetos de Firma, Folios y EnvioDTE
    $Folios = [];
    $Folios[52] = new \sasco\LibreDTE\Sii\Folios($dataFolio);

    $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();

    // generar cada DTE, timbrar, firmar y agregar al sobre de EnvioDTE
    foreach ($set_pruebas as $documento) {
        $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
        if (!$DTE->timbrar($Folios[$DTE->getTipo()])) {
            break;
        }
        if (!$DTE->firmar($GLOBALS["Firma"])) {
            break;
        }
        $EnvioDTE->agregar($DTE);
    }

    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($GLOBALS["caratula"]);
    $EnvioDTE->setFirma($GLOBALS["Firma"]);

    $dataDTE = $EnvioDTE->generar();

    $datita = base64_encode($dataDTE);
    $track_id = $EnvioDTE->enviar();

    $err = array();
    foreach (\sasco\LibreDTE\Log::readAll() as $error) {
        array_push($err, $error);
    }

    if (count($err) > 0) {
        return array(
            "errores" => $err,
        );
    } else if (!$track_id) {
        return array(
            "errores" => "Error al enviar al SII",
        );
    } else {
        return array(
            "trackID" => $track_id,
            "data" => $datita,
        );
    }
}

function getDataCotizacion($con, $id_cotizacion, $directa)
{
    $query = "SELECT
        cl.nombre as cliente,
        cl.rut,
        cl.id_cliente,
        cl.domicilio,
        cl.comuna as id_comuna,
        com.nombre as comuna,
        com.ciudad as ciudad,
        DATE_FORMAT(co.fecha, '%d/%m/%Y %H:%i') as fecha,
        co.observaciones as comentario,
        co.condicion_pago,
        cl.giro,
        cl.razon_social,
        ROUND(co.monto) as monto
        FROM clientes cl
        INNER JOIN cotizaciones" . ($directa == true ? "_directas" : "") . " co ON co.id_cliente = cl.id_cliente
        LEFT JOIN comunas com ON cl.comuna = com.id
         WHERE co.id = $id_cotizacion";

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
            WHERE cp.id_cotizacion" . ($directa == true ? "_directa" : "") . " = $id_cotizacion
            ";
        $val2 = mysqli_query($con, $query2);
        if (mysqli_num_rows($val2) > 0) {
            $productos = array();
            while ($ww2 = mysqli_fetch_array($val2)) {
                $subtotal = (int) $ww2["precio"] * (int) $ww2["cantidad"];

                if ($ww2["tipo_descuento"] == 1) { //PORCENTUAL
                    $total = $subtotal - (($subtotal * $ww2["valor_descuento"]) / 100);
                } else if ($ww2["tipo_descuento"] == 2) { //PORCENTUAL
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
            return $array;
        }
    }
    return null;
}

function getEstadoDte($track_id)
{
    $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($GLOBALS['Firma']);
    if (!$token) {
        return null;
    }
    $rutsplit = explode("-", $GLOBALS["empresa"]["rut"]);

    $rut = $rutsplit[0];
    $dv = $rutsplit[1];
    $estado = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$rut, $dv, $track_id, $token]);

    if ($estado !== false) {
        $aceptados = $estado->xpath('/SII:RESPUESTA/SII:RESP_BODY/ACEPTADOS');
        $rechazados = $estado->xpath('/SII:RESPUESTA/SII:RESP_BODY/RECHAZADOS');
        return json_encode(array(
            "aceptados" => isset($aceptados[0]) ? (string) $aceptados[0] : null,
            "rechazados" => isset($rechazados[0]) ? (string) $rechazados[0] : null,
        ));
    }
    return null;
}
