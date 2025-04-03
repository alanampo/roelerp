<?php



require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
ini_set('xdebug.overload_var_dump', 0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use libredte\lib\Core\Application;
use Derafu\Lib\Core\Support\Store\DataContainer;
use libredte\lib\Core\Package\Billing\Component\Integration\Support\SiiRequest;
use libredte\lib\Core\Package\Billing\Component\Integration\Enum\SiiAmbiente;
use Derafu\Lib\Core\Package\Prime\Component\Xml\Entity\Xml as XmlDocument;
use libredte\lib\Core\Package\Billing\Component\Document\Support\DocumentBag;

if (strpos($_SERVER['HTTP_HOST'], 'roelplant') !== false) {
    include $_SERVER['DOCUMENT_ROOT'] . "/class_lib/sesionSecurity.php";
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/class_conecta_mysql.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/funciones.php';
} else {
    include $_SERVER['DOCUMENT_ROOT'] . "/class_lib/sesionSecurity.php";
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/class_conecta_mysql.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/funciones.php';

}
set_time_limit(0);

header('Content-type: text/plain; charset=ISO-8859-1');
include 'inc.php';
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

$errores = array();

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8mb4'");

sasco\LibreDTE\Sii::setAmbiente(sasco\LibreDTE\Sii::CERTIFICACION);
sasco\LibreDTE\Sii::setServidor('maullin');

$consulta = $_POST["consulta"];
$GLOBALS['empresa'] = null;
$dir_logo = null;
if ($consulta == "generar_factura" || $consulta == "enviar_mail" || $consulta == "generar_factura_directa" || $consulta == "generar_boleta_directa" || $consulta == "anular_factura" || $consulta == "anular_factura_antigua" || $consulta == "anular_nc" || $consulta == "get_estado_dte" || $consulta == "imprimir_dte" || $consulta == "generar_guia_despacho" || $consulta == "generar_guia_despacho_desde_cotizaciones" || $consulta == "check_estado_real" || $consulta == "reenviar_factura" || $consulta == "reenviar_nota_credito" || $consulta == "reenviar_nota_debito" || $consulta == "reenviar_guia_despacho" || $consulta == "get_ambiente" || $consulta == "generar_boleta") {
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
    $GLOBALS["FirmaRaw"] = $config2["firma"];
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

        $DTEGenerado = generarFactura($json, $dataFolio, $folio, $id_guia, $folio_guia, $id_cotizacion, $con);

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

                //MODIFICAR ACA

                try {
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, $json["email"]);
                    generarPDFMailInterno(base64_decode($datita), $dir_logo, $folio, $esBoleta = false);
                } catch (\Throwable $th) {
                    //throw $th;
                }


            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_autocommit($con, true);
            $datita = $DTEGenerado["data"];
            $query = "UPDATE facturas SET data = '$datita' WHERE rowid = $id_fac";
            if (!mysqli_query($con, $query)) {
                array_push($arrErrores, mysqli_error($con));
            }
            array_push($arrErrores, "ERROR_ENVIO_SII" . " " . json_encode($DTEGenerado["errores"]));
        }
    } else {
        array_push($arrErrores, "ERROR_INSERT_FACTURA");
        array_push($errores, mysqli_error($con));
    }

    if (count($arrErrores) > 0) {
        echo (json_encode($arrErrores));
    }
    mysqli_close($con);
} else if ($consulta == "generar_boleta") {
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

    $query = "INSERT INTO boletas (
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

    if (mysqli_query($con, $query)) { // SE INSERTO LA BOLETA
        $id_fac = mysqli_insert_id($con);

        $DTEGenerado = generarBoleta($json, $dataFolio, $folio, $id_guia, $folio_guia, $id_cotizacion, $con);

        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            $track_id = (string) $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];
            //$estado = $DTEGenerado["estado"];
            mysqli_autocommit($con, false);
            $query = "UPDATE boletas SET track_id = '$track_id', estado = 'EPR', data = '$datita'" . ($id_guia != null && $id_guia != "null" ? ", id_guia_despacho = $id_guia" : "") . " WHERE rowid = $id_fac";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }

            if ($id_guia != null && $id_guia != "null") {
                $query = "UPDATE guias_despacho SET id_boleta = '$id_fac' WHERE rowid = $id_guia";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_IDFAC_GUIA");
                }
            }

            if (mysqli_commit($con)) {
                checkEstadoAndUpdate($id_fac, 4, $con, $track_id);
                $dir_logo = getDataLogo();
                try {
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, $json["email"], true);
                    //MODIFICAR ACA
                } catch (\Throwable $th) {
                    //throw $th;
                }

            } else {
                mysqli_rollback($con);
            }
        } else {
            array_push($arrErrores, "ERROR_ENVIO_SII" . " " . json_encode($DTEGenerado["errores"]));
        }
    } else {
        array_push($arrErrores, "ERROR_INSERT_BOLETA");
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
    $esBoleta = isset($_POST["esBoleta"]) && $_POST["esBoleta"] == 1 ? TRUE : FALSE;
    if (isset($dataFolio["data"])) {
        if (!$esBoleta !== TRUE) {
            $DTEGenerado = generarFactura($json, $dataFolio["data"], $_POST["folio"], $id_guia, $dataFolio["folio_guia"]);
        } else {
            $DTEGenerado = generarBoleta($json, $dataFolio["data"], $_POST["folio"], $id_guia, $dataFolio["folio_guia"]);
        }

        $errores = [];
        $arrErrores = [];
        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            $track_id = $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];
            mysqli_autocommit($con, false);
            $query = "UPDATE " . ($esBoleta ? "boletas" : "facturas") . " SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_fac";
            if (!mysqli_query($con, $query)) {
                array_push($errores, mysqli_error($con));
                array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
            }

            if (isset($id_guia) && strlen($id_guia) > 0) {
                $query = "UPDATE guias_despacho SET id_" . ($esBoleta ? "boleta" : "factura") . " = '$id_fac' WHERE rowid = $id_guia";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_IDFAC_GUIA");
                }
            }

            if (mysqli_commit($con)) {
                checkEstadoAndUpdate($id_fac, 4, $con, $track_id);
                $dir_logo = getDataLogo();
                try {
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, $json["email"], $esBoleta);
                    generarPDFMailInterno(base64_decode($datita), $dir_logo, $_POST["folio"], $esBoleta = false);
                    //MODIFICAR ACA
                } catch (\Throwable $th) {
                    //throw $th;
                }

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
    $esBoleta = $_POST["esBoleta"] == 1 ? TRUE : FALSE;
    $dir_logo = getDataLogo();

    $dataFactura = getDataFactura($con, $rowid_factura, $esFactDirecta, $esBoleta);
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
    $DTEGenerado = generarNotaCredito($dataFolio, $folio, $folio_factura, $dataFactura, $esBoleta);

    if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
        mysqli_autocommit($con, false);
        $track_id = $DTEGenerado["trackID"];
        $datita = $DTEGenerado["data"];

        $query = "UPDATE " . ($esBoleta ? "boletas" : "facturas") . " SET estado = 'ANU' WHERE rowid = $rowid_factura;";
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
                try {
                    checkEstadoAndUpdate($rowid, 1, $con, $track_id);
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, null);
                } catch (\Throwable $th) {
                    //throw $th;
                }
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

    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito", "boletas"];

    $tablas[10] = "boletas";

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

    $esBoleta = isset($_POST["esBoleta"]) && $_POST["esBoleta"] == 1 ? TRUE : FALSE;
    $dir_logo = getDataLogo();
    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito"];
    $tablas[10] = "boletas";
    $query = "SELECT d.data FROM $tablas[$tipoDTE] d
     WHERE d.rowid = $rowid;";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $re = mysqli_fetch_assoc($val);
        $data = $re["data"];
        $data = base64_decode($re["data"]);
        if (generarPDFMail($data, $dir_logo, $folio, $email, $_POST["link"], $esBoleta)) {
            echo "success";
        }
    }
} else if ($consulta == "get_estado_dte") {
    $trackID = $_POST["track_id"];
    $rowid = $_POST["rowid"];
    $folio = $_POST["folio"];
    $tipoDTE = $_POST["tipoDoc"];
    echo json_encode(getEstadoDte($trackID, $rowid, $folio, $tipoDTE, $con));
    exit;
    $certPath = base64_decode(str_replace("data:application/x-pkcs12;base64,", "", $GLOBALS["empresa"]["certificado"])); // contenido del archivo certificado.p12
    $certPass = $GLOBALS["empresa"]["pass"];
    // Paso 1: Obtener semilla
    $semilla = obtenerSemilla();
    // echo "Semilla obtenida: $semilla\n";
    $xmlFirmado = firmarSemilla($semilla, $certPath, $certPass);


    //echo "XML firmado correctamente.\n";
    //$xmlFirmado = file_get_contents("firmado.xml");
    // Paso 3: Obtener token
    $token = obtenerToken($xmlFirmado);

    getEstadoBoleta($token);
    die;
} else if ($consulta == "anular_factura") { //GENERAR NOTA DE CREDITO
    $rowid_factura = $_POST["rowid"];
    $folio = $_POST["folio"];
    $folio_factura = $_POST["folioRef"];
    $caf = $_POST["caf"];
    $id_cliente = $_POST["id_cliente"];
    $esBoleta = isset($_POST["esBoleta"]) && $_POST["esBoleta"] == 1 ? TRUE : FALSE;
    $esFactDirecta = (boolean) json_decode(strtolower($_POST["esFactDirecta"]));
    $comentario = mysqli_real_escape_string($con, $_POST["comentario"]);
    $arrErrores = array();
    $dir_logo = getDataLogo();

    $tabla = $esBoleta ? "boleta" : "factura";

    $query = "INSERT INTO notas_credito (
        folio,
        fecha,
        id_$tabla,
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
        $dataFactura = getDataFactura($con, $rowid_factura, $esFactDirecta, $esBoleta);
        if ($dataFactura == null) {
            die("[ERROR_GET_DATA_FACTURA]");
        }

        $tmpFolios = getDataFolios($con, $caf, null);
        if ($tmpFolios == null) {
            die("[ERROR_GET_CAF]");
        }

        $dataFolio = $tmpFolios["data"];
        $condicion_pago = getCondicionPago($dataFactura["condicion_pago"]);

        $DTEGenerado = generarNotaCredito($dataFolio, $folio, $folio_factura, $dataFactura, $esBoleta);

        if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
            mysqli_autocommit($con, false);
            $track_id = $DTEGenerado["trackID"];
            $datita = $DTEGenerado["data"];

            $query = "UPDATE " . $tabla . "s SET estado = 'ANU' WHERE rowid = $rowid_factura;";
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
    $observaciones = $_POST["observaciones"];
    $arrErrores = array();
    $dir_logo = getDataLogo();

    $tmpFolios = getDataFolios($con, $caf, null);
    if ($tmpFolios == null) {
        die("[ERROR_GET_CAF]");
    }
    $dataFolio = $tmpFolios["data"];

    mysqli_autocommit($con, false);
    $uniqid = sha1(uniqid("cotd", true));
    $query = "INSERT INTO cotizaciones_directas (
            uniqid,
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                '$uniqid',
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
                'comentario' => $observaciones && strlen($observaciones) ? $observaciones : null
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
                    if (mysqli_num_rows($val)) {
                        $d = mysqli_fetch_assoc($val);

                        if (isset($d["mail"]) && filter_var(isset($d["mail"]), FILTER_VALIDATE_EMAIL)) {
                            $email = $d["mail"];
                        }

                    }
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, $email);
                    generarPDFMailInterno(base64_decode($datita), $dir_logo, $folio, $esBoleta = false);
                } else {
                    mysqli_rollback($con);
                }
            } else {
                mysqli_autocommit($con, true);
                $datita = $DTEGenerado["data"];
                $query = "UPDATE facturas SET data = '$datita' WHERE rowid = $id_fac";
                if (!mysqli_query($con, $query)) {
                    array_push($arrErrores, mysqli_error($con));
                }
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
} else if ($consulta == "generar_boleta_directa") {
    $caf = $_POST["caf"];
    $folio = $_POST["folioBOL"];
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
    $uniqid = sha1(uniqid("cotd", true));
    $query = "INSERT INTO cotizaciones_directas (
        uniqid,
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                '$uniqid',
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

    $query = "INSERT INTO boletas (
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
        array_push($arrErrores, "ERROR_INSERT_BOLETA");
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
                'comentario' => $observaciones && strlen($observaciones) > 0 ? $observaciones : null
            );
            $DTEGenerado = generarBoleta($mijson, $dataFolio, $folio, null, null);

            if (!isset($DTEGenerado["errores"]) && isset($DTEGenerado["trackID"])) {
                $track_id = $DTEGenerado["trackID"];
                $datita = $DTEGenerado["data"];
                mysqli_autocommit($con, false);
                $query = "UPDATE boletas SET track_id = '$track_id', data = '$datita', estado = 'EPR' WHERE rowid = $id_fac";
                if (!mysqli_query($con, $query)) {
                    array_push($errores, mysqli_error($con));
                    array_push($arrErrores, "SII_SUCCESS_BUT_ERROR_UPDATE_TRACKID");
                }

                if (mysqli_commit($con)) {
                    checkEstadoAndUpdate($id_fac, 0, $con, $track_id);
                    $dir_logo = getDataLogo();

                    $val = mysqli_query($con, "SELECT mail FROM clientes WHERE id_cliente = $id_cliente");
                    $email = NULL;
                    if (mysqli_num_rows($val)) {
                        $d = mysqli_fetch_assoc($val);

                        if (isset($d["mail"]) && filter_var(isset($d["mail"]), FILTER_VALIDATE_EMAIL)) {
                            $email = $d["mail"];
                        }

                    }
                    generarPDF(base64_decode($datita), $dir_logo, $track_id, $email, true);
                } else {
                    mysqli_rollback($con);
                }
            } else {
                array_push($arrErrores, "ERROR_ENVIO_SII");
            }
        } else {
            array_push($arrErrores, "ERROR_INSERT_BOLETA");
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
    $uniqid = sha1(uniqid("cotd", true));
    $query = "INSERT INTO cotizaciones_directas (
            uniqid,
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                '$uniqid',
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
    $uniqid = sha1(uniqid("cotd", true));
    $query = "INSERT INTO cotizaciones_directas (
            uniqid,
            id_cliente,
            observaciones,
            fecha,
            condicion_pago,
            monto)
            VALUES (
                '$uniqid',
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
    $tablas = ["facturas", "notas_credito", "guias_despacho", "notas_debito", "boletas"];
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

function generarPDF($data, $dir_logo, $track_id, $email, $esBoleta = false)
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

    // procesar cada DTE e ir agregándolo al PDF
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
            // Convertir el archivo XML a base64
            if (preg_match('/<RUTRecep>([^<]+)<\/RUTRecep>/', $data, $matches)) {
                $rutReceptor = $matches[1]; // El valor dentro de <RutReceptor>
            }

            $rutReceptor = $rutReceptor ?? $Caratula['RutReceptor'];
            $data = preg_replace('/(<RutReceptor>)([^<]*)(<\/RutReceptor>)/', '<RutReceptor>' . strtoupper($rutReceptor) . '</RutReceptor>', $data);
            $content_xml = chunk_split(base64_encode($data));
            $id = $DTE->getID();

            $content = file_get_contents($path);
            $content = chunk_split(base64_encode($content));
            $uid = md5(uniqid(time()));
            $file_name = (!$esBoleta ? "factura" : "boleta") . "_$id.pdf";
            $file_name_xml = ($esBoleta ? "boleta" : "factura") . "_$id.xml";
            $subject = (!$esBoleta ? "Factura" : "Boleta") . " $id";

            $message = "Te enviamos una copia de la " . (!$esBoleta ? "Factura" : "Boleta") . " N° $id correspondiente a tu compra.";

            // Usando PHPMailer para enviar el correo
            $mail = new PHPMailer(true);
            try {
                // Configuración del servidor SMTP para Gmail
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';  // Dirección del servidor SMTP de Gmail
                $mail->Port = 587;  // Puerto para STARTTLS
                $mail->SMTPAuth = true;  // Habilitar autenticación SMTP

                $mail->Username = getenv('EMAIL_USERNAME');  // Usuario SMTP (tu cuenta de Gmail)
                $mail->Password = getenv('EMAIL_PASSWORD');  // Contraseña SMTP de la cuenta de Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Uso de STARTTLS

                // Deshabilitar la verificación del certificado SSL (si es necesario)
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                // Configuración del remitente
                $mail->setFrom('ventas@roelplant.cl', 'Roelplant');

                // Destinatario del correo
                $mail->addAddress($email); // Dirección del receptor
                $mail->addReplyTo('ventas@roelplant.cl', 'Roelplant');

                // Asunto y mensaje
                $mail->Subject = $subject;
                $mail->Body = $message;

                // Archivos adjuntos
                $mail->addStringAttachment(base64_decode($content), $file_name, 'base64', 'application/octet-stream');
                $mail->addStringAttachment(base64_decode($content_xml), $file_name_xml, 'base64', 'application/octet-stream');

                // Enviar el correo
                $mail->send();
            } catch (Exception $e) {
                //echo "El correo no pudo ser enviado. Error: {$mail->ErrorInfo}";
            }
        }
    }
}

function generarPDFMail($data, $dir_logo, $id, $email, $link, $esBoleta = false)
{
    //ENVIAR AL CLIENTE
    // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
    $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
    $EnvioDte->loadXML($data);
    $Caratula = $EnvioDte->getCaratula();

    $Documentos = $EnvioDte->getDocumentos();

    if (preg_match('/<RUTRecep>([^<]+)<\/RUTRecep>/', $data, $matches)) {
        $rutReceptor = $matches[1]; // El valor dentro de <RutReceptor>
    }

    $rutReceptor = $rutReceptor ?? $Caratula['RutReceptor'];
    $data = preg_replace('/(<RutReceptor>)([^<]*)(<\/RutReceptor>)/', '<RutReceptor>' . strtoupper($rutReceptor) . '</RutReceptor>', $data);
    // directorio temporal para guardar los PDF
    $dir = sys_get_temp_dir() . '/dte_' . $Caratula['RutEmisor'] . '_' . $rutReceptor . '_' . str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']);
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
    $content_xml = chunk_split(base64_encode($data));
    $content = file_get_contents($path);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $file_name = ($esBoleta ? "boleta" : "factura") . "_$id.pdf";
    $file_name_xml = ($esBoleta ? "boleta" : "factura") . "_$id.xml";
    $subject = ($esBoleta ? "Boleta" : "Factura") . " $id";

    $message = "Te enviamos una copia de la " . ($esBoleta ? "Boleta" : "Factura") . " N° $id correspondiente a tu compra.";
    if (isset($link) && strlen($link) > 0) {
        $message .= " Puedes realizar el pago ingresando al siguiente link: $link";
    }

    $from_name = "Roelplant";
    $from_mail = "ventas@roelplant.cl";
    $replyto = "ventas@roelplant.cl";

    // Crear una nueva instancia de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP para Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Dirección del servidor SMTP de Gmail
        $mail->Port = 587;  // Puerto para STARTTLS
        $mail->SMTPAuth = true;  // Habilitar autenticación SMTP
        $mail->Username = getenv('EMAIL_USERNAME');  // Usuario SMTP (tu cuenta de Gmail)
        $mail->Password = getenv('EMAIL_PASSWORD');  // Contraseña SMTP de la cuenta de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Uso de STARTTLS

        // Deshabilitar la verificación del certificado SSL (si es necesario)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Configuración del remitente
        $mail->setFrom($from_mail, $from_name);

        // Destinatario del correo
        $mail->addAddress($email);  // Dirección de correo del destinatario

        // Configuración del asunto y cuerpo del correo
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->AltBody = 'Este es el cuerpo del mensaje en texto plano para clientes de correo no HTML.';

        // Adjuntar el archivo PDF
        $mail->addStringAttachment(base64_decode($content), $file_name, 'base64', 'application/pdf');

        // Adjuntar el archivo XML
        $mail->addStringAttachment(base64_decode($content_xml), $file_name_xml, 'base64', 'application/xml');

        // Enviar el correo
        $mail->send();
        return true;

    } catch (Exception $e) {
        //echo 'Error al enviar el correo. Detalles: ' . $mail->ErrorInfo;
        return false;
    }
}


function generarPDFMailInterno($data, $dir_logo, $id, $esBoleta = false)
{
    $email = "plantinera@roelplant.cl";
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

    // Convertir el archivo PDF a base64
    $content_pdf = file_get_contents($path);
    $content_pdf = chunk_split(base64_encode($content_pdf));

    // Convertir el archivo XML a base64
    if (preg_match('/<RUTRecep>([^<]+)<\/RUTRecep>/', $data, $matches)) {
        $rutReceptor = $matches[1]; // El valor dentro de <RutReceptor>
    }

    $rutReceptor = $rutReceptor ?? $Caratula['RutReceptor'];
    $data = preg_replace('/(<RutReceptor>)([^<]*)(<\/RutReceptor>)/', '<RutReceptor>' . strtoupper($rutReceptor) . '</RutReceptor>', $data);
    $content_xml = chunk_split(base64_encode($data));

    $uid = md5(uniqid(time()));
    $file_name_pdf = ($esBoleta ? "boleta" : "factura") . "_$id.pdf";
    $file_name_xml = ($esBoleta ? "boleta" : "factura") . "_$id.xml";

    $subject = ($esBoleta ? "Boleta" : "Factura") . " $id";

    $message = "Te enviamos una copia de la " . ($esBoleta ? "Boleta" : "Factura") . " N° $id";

    $from_name = "Roelplant";
    $from_mail = "ventas@roelplant.cl";
    $replyto = "ventas@roelplant.cl";

    try {
        // Crear una nueva instancia de PHPMailer
        $mail = new PHPMailer(true);

        // Configuración del servidor SMTP para Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // Dirección del servidor SMTP de Gmail
        $mail->Port = 587;  // Puerto para STARTTLS
        $mail->SMTPAuth = true;  // Habilitar autenticación SMTP
        $mail->Username = getenv('EMAIL_USERNAME');  // Usuario SMTP (tu cuenta de Gmail)
        $mail->Password = getenv('EMAIL_PASSWORD');  // Contraseña SMTP de la cuenta de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Uso de STARTTLS

        // Configuración del remitente
        $mail->setFrom($from_mail, $from_name);

        // Destinatario del correo
        $mail->addAddress($email);

        // Asunto
        $mail->Subject = $subject;

        // Cuerpo del correo
        $mail->Body = $message;

        // Adjuntar el archivo PDF
        $mail->addStringAttachment(base64_decode($content_pdf), $file_name_pdf, 'base64', 'application/octet-stream');

        // Adjuntar el archivo XML
        $mail->addStringAttachment(base64_decode($content_xml), $file_name_xml, 'base64', 'application/octet-stream');

        // Enviar el correo
        if ($mail->send()) {
            return true;
        }
        return false;

    } catch (Exception $e) {
        //echo 'Error al enviar el correo: ' . $e->getMessage();
        return false;
    }
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
    } catch (\Throwable $th) {
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

function generarFactura($json, $dataFolio, $folio, $id_guia, $folio_guia, $id_cotizacion = null, $con = null)
{
    $datita = NULL;
    try {
        if (isset($con) && isset($id_cotizacion)) {
            $dataCotizacion = getDataCotizacion($con, $id_cotizacion, false);
            if (!isset($dataCotizacion)) {
                die("No se encontró la cotización");
            }
        } else {
            $dataCotizacion = $json;
        }

        $comentario = $dataCotizacion["comentario"] ?? $dataCotizacion["observaciones"] ?? null;


        $arrayproductos = array();
        $i = 0;
        foreach ($dataCotizacion["productos"] as $producto) {
            if ($i == count($dataCotizacion["productos"]) - 1 && $comentario && mb_strlen($comentario) > 0100) {
                array_push($arrayproductos, array(
                    'NmbItem' => $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : ""),
                    'DscItem' => $comentario,
                    'QtyItem' => (int) $producto["cantidad"],
                    'PrcItem' => (int) $producto["precio"]
                ));
            } else {
                array_push($arrayproductos, array(
                    'NmbItem' => $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : ""),
                    'QtyItem' => (int) $producto["cantidad"],
                    'PrcItem' => (int) $producto["precio"]
                ));
            }

            $i++;
        }

        $condicion_pago = getCondicionPago($dataCotizacion["condicion_pago"]);

        $set_pruebas = [
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => $folio,
                        'TermPagoGlosa' => $comentario && mb_strlen($comentario) <= 100 ? $comentario : NULL,
                    ],
                    'Emisor' => $GLOBALS["Emisor"],
                    'Receptor' => [
                        'RUTRecep' => $dataCotizacion["rut"],
                        'RznSocRecep' => $dataCotizacion["cliente"],
                        'GiroRecep' => $dataCotizacion["giro"] ? strtoupper($dataCotizacion["giro"]) : "-",
                        'DirRecep' => $dataCotizacion["domicilio"],
                        'CmnaRecep' => $dataCotizacion["comuna"] ? strtoupper($dataCotizacion["comuna"]) : "-",
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

        $caratula = $GLOBALS["caratula"];
        //$caratula["RutReceptor"] = $dataCotizacion["rut"];
        // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->setFirma($GLOBALS["Firma"]);

        $dataDTE = $EnvioDTE->generar();

        // Crear DTE.
        // $document = $app
        //     ->getBillingPackage()
        //     ->getDocumentComponent()
        //     ->bill($set_pruebas, $caf, $certificate)
        //     ->getDocument()
        // ;
        $xmlDocument = new XmlDocument();
        $xmlDocument->loadXml((string) $dataDTE);
        $app = Application::getInstance();
        $caf = $app
            ->getBillingPackage()
            ->getIdentifierComponent()
            ->getCafLoaderWorker()
            ->load($dataFolio)
            ->getCaf()
        ;

        $siiLazyWorker = $app
            ->getBillingPackage()
            ->getIntegrationComponent()
            ->getSiiLazyWorker();

        $certificateLoader = $app
            ->getPrimePackage()
            ->getCertificateComponent()
            ->getLoaderWorker()
        ;

        $certificate = $certificateLoader->createFromData(
            $GLOBALS["FirmaRaw"]["data"],
            $GLOBALS["FirmaRaw"]["pass"],
        );

        $request = new SiiRequest(
            certificate: $certificate,
            options: [
                'ambiente' => $GLOBALS["empresa"]["modo"] == "PROD" ? SiiAmbiente::PRODUCCION : SiiAmbiente::CERTIFICACION,
            ],

        );

        $datita = base64_encode((string) $dataDTE);

        $track_id = $siiLazyWorker->sendXmlDocument(
            $request,
            $xmlDocument,
            $GLOBALS["empresa"]["rut"]
        );
        return array(
            "trackID" => $track_id,
            "data" => $datita,
        );
    } catch (\Throwable $th) {
        return array(
            "errores" => $th->getMessage() . " - " . $th->getTraceAsString(),
            "data" => $datita
        );
    }
}

function limpiarYRecortar($texto, $limite = 100)
{
    // Eliminar espacios extra y normalizar a un solo espacio
    $textoLimpio = preg_replace('/\s+/', ' ', trim($texto));

    // Recortar a 100 caracteres si es necesario
    return mb_substr($textoLimpio, 0, $limite);
}

function generarBoleta($json, $dataFolio, $folio, $id_guia, $folio_guia, $id_cotizacion = null, $con = null)
{
    try {
        if (isset($con) && isset($id_cotizacion)) {
            $dataCotizacion = getDataCotizacion($con, $id_cotizacion, false);
            if (!isset($dataCotizacion)) {
                die("No se encontró la cotización");
            }
        } else {
            $dataCotizacion = $json;
        }

        $arrayproductos = array();
        foreach ($dataCotizacion["productos"] as $producto) {
            $prod = $producto["variedad"] . " (" . $producto["codigo"] . ") " . ($producto["especie"] && strlen($producto["especie"]) > 0 ? $producto["especie"] : "");
            array_push($arrayproductos, array(
                'NmbItem' => trim($prod),
                'QtyItem' => (int) $producto["cantidad"],
                'PrcItem' => (int) $producto["precio"],
                'DescuentoPct' => $producto["descuento"] && $producto["descuento"]["tipo"] == "porcentual" ? (int) $producto["descuento"]["valor"] : false,
                'DescuentoMonto' => $producto["descuento"] && $producto["descuento"]["tipo"] == "fijo" ? (int) $producto["descuento"]["valor"] : false,
            ));
        }

        $condicion_pago = getCondicionPago($dataCotizacion["condicion_pago"]);
        $comentario = $dataCotizacion["comentario"] ?? $dataCotizacion["observaciones"] ?? null;

        $emisor = $GLOBALS["Emisor"];

        $set_pruebas = [
            [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 39,
                        'Folio' => $folio
                    ],
                    'Emisor' => $emisor,
                    'Receptor' => [
                        'RUTRecep' => $dataCotizacion["rut"],
                        'RznSocRecep' => $dataCotizacion["cliente"],
                        'GiroRecep' => $dataCotizacion["giro"] ? strtoupper($dataCotizacion["giro"]) : "-",
                        'DirRecep' => $dataCotizacion["domicilio"],
                        'CmnaRecep' => $dataCotizacion["comuna"] ? strtoupper($dataCotizacion["comuna"]) : "-",
                    ],
                ],
                'Detalle' => $arrayproductos,
            ]
        ];

        $Folios = [];
        $Folios[39] = new \sasco\LibreDTE\Sii\Folios($dataFolio);
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
        $caratula = $GLOBALS["caratula"];
        //$caratula["RutReceptor"] = $dataCotizacion["rut"];
        // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
        $EnvioDTE->setCaratula($caratula);
        $EnvioDTE->setFirma($GLOBALS["Firma"]);

        $dataDTE = $EnvioDTE->generar();


        $xmlDocument = new XmlDocument();
        $xmlDocument->loadXml((string) $dataDTE);
        $app = Application::getInstance();
        $caf = $app
            ->getBillingPackage()
            ->getIdentifierComponent()
            ->getCafLoaderWorker()
            ->load($dataFolio)
            ->getCaf()
        ;

        $siiLazyWorker = $app
            ->getBillingPackage()
            ->getIntegrationComponent()
            ->getSiiLazyWorker();

        $certificateLoader = $app
            ->getPrimePackage()
            ->getCertificateComponent()
            ->getLoaderWorker()
        ;

        $certificate = $certificateLoader->createFromData(
            $GLOBALS["FirmaRaw"]["data"],
            $GLOBALS["FirmaRaw"]["pass"],
        );

        $set_pruebas = [

            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 39,
                    'Folio' => $folio
                ],
                'Emisor' => $emisor,
                'Receptor' => [
                    'RUTRecep' => $dataCotizacion["rut"],
                    'RznSocRecep' => $dataCotizacion["cliente"],
                    'GiroRecep' => $dataCotizacion["giro"] ? strtoupper($dataCotizacion["giro"]) : "-",
                    'DirRecep' => $dataCotizacion["domicilio"],
                    'CmnaRecep' => $dataCotizacion["comuna"] ? strtoupper($dataCotizacion["comuna"]) : "-",
                ],
            ],
            'Detalle' => $arrayproductos,

        ];

        $builder = $app
            ->getBillingPackage()
            ->getDocumentComponent()
            ->getBuilderWorker()
        ;

        $data = <<<YAML
        Encabezado:
            IdDoc:
                TipoDTE: 39
                Folio: 5027
            Emisor:
                RUTEmisor: '76192083-9'
                RznSoc: 'SASCO SpA'
                GiroEmis: 'Tecnología, Informática y Telecomunicaciones'
                DirOrigen: 'Santiago'
                CmnaOrigen: 'Santiago'
            Receptor:
                RUTRecep: '60803000-K'
                RznSocRecep: 'Servicio de Impuestos Internos'
                GiroRecep: 'Gobierno'
                DirRecep: 'Santiago'
                CmnaRecep: 'Santiago'
        Detalle:
            NmbItem: 'Servicio Plus de LibreDTE'
            QtyItem: 12
            PrcItem: 40000
        YAML;

        $bag = new DocumentBag(
            inputData: $data,
            options: new DataContainer(['parser' => ['strategy' => 'default.yaml']])
        );

        $document = $builder->build($bag);
        // $document = $app
        //     ->getBillingPackage()
        //     ->getDocumentComponent()
        //     ->bill($set_pruebas, $caf, $certificate)
        //     ->getDocument()
        // ;

        $xmlDocument = new XmlDocument();
        $xmlDocument->loadXml($document->saveXml());
        echo $document->saveXml();
        die;
        $request = new SiiRequest(
            certificate: $certificate,
            options: [
                'ambiente' => $GLOBALS["empresa"]["modo"] == "PROD" ? SiiAmbiente::PRODUCCION : SiiAmbiente::CERTIFICACION,
            ],

        );

        $datita = base64_encode((string) $dataDTE);

        $track_id = $siiLazyWorker->sendXmlDocument(
            $request,
            $xmlDocument,
            $GLOBALS["empresa"]["rut"]
        );


        return array(
            "trackID" => $track_id,
            "data" => $datita,
        );

    } catch (Throwable $th) {
        echo $th->getTraceAsString();
        return array(
            "errores" => "Error al enviar al SII {$th->getMessage()}",
        );
    }

    // $dataDTE = preg_replace('/<MntNeto>.*?<\/MntNeto>\s*/', '', (string) $dataDTE);
    // $dataDTE = preg_replace('/<IVA>.*?<\/IVA>\s*/', '', $dataDTE);
    // $token = NULL;
    // try {
    //     $certPath = base64_decode(str_replace("data:application/x-pkcs12;base64,", "", $GLOBALS["empresa"]["certificado"])); // contenido del archivo certificado.p12
    //     $certPass = $GLOBALS["empresa"]["pass"];
    //     // Paso 1: Obtener semilla
    //     $semilla = obtenerSemilla();
    //     // echo "Semilla obtenida: $semilla\n";

    //     $xmlFirmado = firmarSemilla($semilla, $certPath, $certPass);


    //     //echo "XML firmado correctamente.\n";
    //     //$xmlFirmado = file_get_contents("firmado.xml");
    //     // Paso 3: Obtener token
    //     $token = obtenerToken($xmlFirmado);
    // } catch (Exception $e) {
    //     echo "Error: " . $e->getMessage();
    //     die;
    // }

    // // Definir la URL del endpoint del SII
    // $entorno = $GLOBALS["empresa"]["modo"] == "PROD" ? "rahue" : "pangal";
    // $url = "https://rahue.sii.cl/recursos/v1/boleta.electronica.envio"; // Ajusta la URL según sea producción o pruebas

    // // Token de autorización
    // $rutsplit = explode("-", $GLOBALS["empresa"]["rut"]);
    // $rutReceptorSplit = explode("-", $dataCotizacion["rut"]);

    // // Datos requeridos por la API
    // $rutSender = $rutsplit[0];// RUT del emisor sin puntos ni guion
    // $dvSender = $rutsplit[1];         // Dígito verificador del emisor
    // $rutCompany = $rutReceptorSplit[0]; // RUT de la empresa sin puntos ni guion
    // $dvCompany = $rutReceptorSplit[1];         // Dígito verificador de la empresa

    // //$dataDTE = firmarBoletaElectronica($dataDTE, $certPath, $certPass);
    // //$dataDTE = firmarXml($dataDTE, $certPath, $certPass);

    // // Crear un archivo temporal en memoria con el XML
    // $temp = tmpfile();
    // fwrite($temp, (string) $dataDTE);
    // fseek($temp, 0);

    // // Obtener la ruta del archivo temporal
    // $meta_data = stream_get_meta_data($temp);
    // $temp_path = $meta_data['uri'];

    // // Construir la solicitud con cURL
    // $ch = curl_init();

    // $post_fields = [
    //     "rutSender" => $rutSender,
    //     "dvSender" => $dvSender,
    //     "rutCompany" => $rutCompany,
    //     "dvCompany" => $dvCompany,
    //     "archivo" => new CURLFile($temp_path, "text/xml", "factura.xml")
    // ];

    // $headers = [
    //     "accept: application/json",
    //     "User-Agent: Mozilla/4.0 ( compatible; PROG 1.0; Windows NT)",
    //     "Content-Type: multipart/form-data",
    //     "Cookie: TOKEN=" . $token
    // ];

    // curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POST, true);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // // Ejecutar la petición y obtener la respuesta
    // $response = curl_exec($ch);
    // $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // $error = curl_error($ch);

    // // Cerrar cURL
    // curl_close($ch);

    // // Cerrar el archivo temporal
    // fclose($temp);
    // // Manejo de la respuesta
    // if ($error) {
    //     return array(
    //         "errores" => $error,
    //     );
    // } else {
    //     try {
    //         $datita = base64_encode((string) $dataDTE);
    //         $rta = json_decode($response);

    //         if (isset($rta)) {
    //             if (!isset($rta->trackid) || (!isset($rta->estado) || $rta->estado != "REC")) {
    //                 return array(
    //                     "errores" => "Error al enviar al SII " . json_encode($rta),
    //                 );
    //             }
    //             return array(
    //                 "estado" => $rta->estado,
    //                 "trackID" => $rta->trackid,
    //                 "data" => $datita,
    //             );
    //         }
    //     } catch (Throwable $th) {
    //         return array(
    //             "errores" => "Error al enviar al SII {$th->getMessage()}",
    //         );
    //     }
    // }
}

function getEstadoBoleta($token)
{
    $monto = 3600;
    $tipoDte = 39;
    $folio = 5014;
    $rutEmisor = "16182953";
    $dvEmisor = "6";
    $rutReceptor = "11362816";
    $dvReceptor = "2";
    $fecha = "16-03-2025";
    $trackid = "16576658792";
    //$host_servidor = "https://api.sii.cl/recursos/v1/boleta.electronica/{$rutEmisor}-{$dvEmisor}-{$tipoDte}-{$folio}/estado?rut_receptor={$rutReceptor}&dv_receptor={$dvReceptor}&monto={$monto}&fechaEmision={$fecha}"; // CERTIFICACION

    $host_servidor = "https://api.sii.cl/recursos/v1/boleta.electronica.envio/{$rutEmisor}-{$dvEmisor}-{$trackid}";

    $client = curl_init($host_servidor);
    curl_setopt($client, CURLOPT_TIMEOUT, -1);
    curl_setopt($client, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($client, CURLOPT_HTTPHEADER, array(
        "Cookie: TOKEN=" . $token,
        "Accept: application/json"
    ));
    //curl_setopt($client, CURLOPT_POST, true);

    $response = curl_exec($client);

    if (curl_errno($client)) {
        echo 'Error:' . curl_error($client);
    }

    curl_close($client);
    echo $response;


}
function getDataFolios($con, $caf, $id_guia = null)
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

function getDataFactura($con, $rowid_factura, $esFactDirecta, $esBoleta = false)
{
    $tabla = $esBoleta ? "boletas" : "facturas";
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
                WHERE co.id = (SELECT f.id_cotizacion_directa FROM $tabla f WHERE f.rowid = $rowid_factura)";
    } else {
        $query .= " INNER JOIN cotizaciones co ON co.id_cliente = cl.id_cliente
                LEFT JOIN comunas com ON cl.comuna = com.id
                WHERE co.id = (SELECT f.id_cotizacion FROM $tabla f WHERE f.rowid = $rowid_factura)";
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
                WHERE cp.id_cotizacion" . ($esFactDirecta ? "_directa" : "") . " = (SELECT " . ($esFactDirecta === true ? "f.id_cotizacion_directa" : "f.id_cotizacion") . " FROM $tabla f WHERE f.rowid = $rowid_factura)
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

function generarNotaCredito($dataFolio, $folio, $folio_factura, $dataFactura, $esBoleta = false)
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
                    'TpoDocRef' => $esBoleta ? 39 : 33,
                    'FolioRef' => $folio_factura,
                    'CodRef' => 1,
                    'RazonRef' => "ANULA " . ($esBoleta ? "BOLETA" : "FACTURA"),
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
    $caratula = $GLOBALS["caratula"];
    //$caratula["RutReceptor"] = $dataFactura["rut"];
    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($caratula);
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
    $caratula = $GLOBALS["caratula"];
    //$caratula["RutReceptor"] = $dataFactura["rut"];
    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($caratula);
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
    $caratula = $GLOBALS["caratula"];
    //$caratula["RutReceptor"] = $json["rut"];
    // enviar dtes y mostrar resultado del envío: track id o bien =false si hubo error
    $EnvioDTE->setCaratula($caratula);
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

function getEstadoDte($track_id, $rowid = NULL, $folio = NULL, $tipoDTE = NULL, $con = NULL)
{

    $fecha = "";
    $tabla = "";
    switch ($tipoDTE) {
        case 0://FACTURA
            $tipoDTE = 33;
            $tabla = "facturas";
            break;
        case 1://NOTA CREDITO
            $tipoDTE = 61;
            $tabla = "notas_credito";
            break;
        case 2://GUIA DESPACHO
            $tipoDTE = 52;
            $tabla = "guias_despacho";
            break;
        case 3://NOTA DEBITO
            $tipoDTE = 56;
            $tabla = "notas_debito";
            break;
        default:
            break;
    }
    $periodo = "";
    if (!isset($track_id) || empty($track_id)) {
        $q = "SELECT * FROM $tabla WHERE rowid = $rowid";
        $row = mysqli_fetch_assoc(mysqli_query($con, $q));
        $fecha = explode(" ", $row["fecha"])[0];
        $data = base64_decode($row["data"]);

        $partes = explode("-", $fecha); // Divide la cadena usando el guion como delimitador

        $periodo = $partes[0] . "-" . $partes[1]; // Combina el año y el mes

        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($GLOBALS['Firma']);
        if (!$token) {
            foreach (\sasco\LibreDTE\Log::readAll() as $error)
                echo $error, "\n";
            exit;
        }

        $rutsplit = explode("-", $GLOBALS["empresa"]["rut"]);

        $rutEmpresa = $rutsplit[0];
        $dv = $rutsplit[1];
        try {
            // URL del servicio
            $url = "https://www4.sii.cl/consemitidosinternetui/services/data/facadeService/getDetalleEmitidos3334";

            // Datos para el cuerpo de la solicitud en formato JSON
            $data = [
                "metaData" => [
                    "conversationId" => $token,
                    "transactionId" => "0",
                    "namespace" => "cl.sii.sdi.lob.diii.consemitidos.data.api.interfaces.FacadeService/getDetalleEmitidos3334"
                ],
                "data" => [
                    "dv" => $dv,
                    "rut" => $rutEmpresa,
                    "folio" => $folio,
                    "operacion" => 1,
                    "refNCD" => 0,
                    "derrCodigo" => $tipoDTE,
                    "tipoDoc" => $tipoDTE,
                    "periodo" => $periodo
                ]
            ];

            // Convertir el array a JSON
            $jsonData = json_encode($data);

            // Configurar opciones de cURL
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8;application/json;text/plain",
                    "Cookie: TOKEN=" . $token,
                    "Content-Type: application/json;charset=utf-8"
                )
            );
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

            // Ejecutar la solicitud cURL y obtener la respuesta
            $response = curl_exec($ch);
            $data = json_decode($response, true);
            $dhdrCodigo = "";
            $foundFolio = FALSE;
            if (isset($data) && isset($data["dataResp"]) && isset($data["dataResp"]["detalles"])) {
                foreach ($data["dataResp"]["detalles"] as $dte) {
                    if ($dte["folio"] == $folio) {
                        $dhdrCodigo = $dte["dhdrCodigo"];
                        $foundFolio = TRUE;
                        break;
                    }
                }
                if ($foundFolio === FALSE) {
                    $query = "UPDATE $tabla SET estado = 'NOREC' WHERE rowid = $rowid;";
                    mysqli_query($con, $query);

                    return [
                        "NOREC" => TRUE
                    ];
                }
            } else {
                throw new Exception("Error al obtener el detalle del DTE");
            }


            if (isset($dhdrCodigo) && !empty((string) $dhdrCodigo)) {
                // URL del servicio
                $url = "https://www4.sii.cl/consemitidosinternetui/services/data/facadeService/getDetalleDTE";

                // Datos para el cuerpo de la solicitud en formato JSON
                $data = [
                    "metaData" => [
                        "conversationId" => $token,
                        "transactionId" => "0",
                        "namespace" => "cl.sii.sdi.lob.diii.consemitidos.data.api.interfaces.FacadeService/getDetalleDTE"
                    ],
                    "data" => [
                        "dv" => $dv,
                        "rut" => $rutEmpresa,
                        "folio" => $folio,
                        "tipoDoc" => $tipoDTE,
                        "dhdrCodigo" => $dhdrCodigo
                    ]
                ];

                // Convertir el array a JSON
                $jsonData = json_encode($data);

                // Configurar opciones de cURL
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    array(
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8;application/json;text/plain",
                        "Cookie: TOKEN=" . $token,
                        "Content-Type: application/json;charset=utf-8"
                    )
                );
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

                // Ejecutar la solicitud cURL y obtener la respuesta
                $response = curl_exec($ch);
                $data = json_decode($response, true);

                if (isset($data) && isset($data["detalleDte"]) && isset($data["detalleDte"]["idEnvio"])) {
                    $track_id = $data["detalleDte"]["idEnvio"];
                    $query = "UPDATE $tabla SET track_id = '$track_id' WHERE rowid = '$rowid'";
                    if (mysqli_query($con, $query)) {
                        return getEstadoDteByTrackId($track_id);
                    } else {
                        throw new Exception("Error al actualizar track_id: " . mysqli_error($con));
                    }
                } else {
                    return [
                        "RECNOTRACKID" => TRUE
                    ];
                }
            } else {
                throw new Exception("Error al obtener el código de envío del DTE");
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return NULL;
        }
    } else {
        return getEstadoDteByTrackId($track_id);
    }


    return;
    if (isset($track_id) && empty($track_id)) {

    } else {
        // $rutReceptor = "";
        // $montoTotal = "";
        // if (preg_match('/<RUTRecep>([^<]+)<\/RUTRecep>/', $data, $matches)) {
        //     $rutReceptor = $matches[1]; // El valor dentro de <RutReceptor>
        // }

        // if (preg_match('/<MntTotal>([^<]+)<\/MntTotal>/', $data, $matches)) {
        //     $montoTotal = $matches[1]; // El valor dentro de <RutReceptor>
        // }

        // $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($GLOBALS['Firma']);
        // if (!$token) {
        //     return null;
        // }

        // $app = Application::getInstance();

        // $siiLazyWorker = $app
        //     ->getBillingPackage()
        //     ->getIntegrationComponent()
        //     ->getSiiLazyWorker();

        // $certificateLoader = $app
        //     ->getPrimePackage()
        //     ->getCertificateComponent()
        //     ->getLoaderWorker()
        // ;

        // $certificate = $certificateLoader->createFromData(
        //     $GLOBALS["FirmaRaw"]["data"],
        //     $GLOBALS["FirmaRaw"]["pass"],
        // );

        // $request = new SiiRequest(
        //     certificate: $certificate,
        //     options: [
        //         'ambiente' => $GLOBALS["empresa"]["modo"] == "PROD" ? SiiAmbiente::PRODUCCION : SiiAmbiente::CERTIFICACION,
        //     ],

        // );

        // $documentStatus = $siiLazyWorker->validateDocument(
        //     $request,
        //     $GLOBALS["empresa"]["rut"],
        //     $tipoDTE,
        //     $folio,
        //     $fecha,
        //     $montoTotal,
        //     $rutReceptor
        // );

        // var_dump($documentStatus->getData());
        // die;
    }
}

function getEstadoDteByTrackId($track_id, $token = null)
{
    if (!$token) {
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($GLOBALS['Firma']);
        if (!$token) {
            return null;
        }
    }

    $rutsplit = explode("-", $GLOBALS["empresa"]["rut"]);

    $rut = $rutsplit[0];
    $dv = $rutsplit[1];
    $estado = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$rut, $dv, $track_id, $token]);

    if ($estado !== false) {
        $aceptados = $estado->xpath('/SII:RESPUESTA/SII:RESP_BODY/ACEPTADOS');
        $rechazados = $estado->xpath('/SII:RESPUESTA/SII:RESP_BODY/RECHAZADOS');
        return [
            "aceptados" => isset($aceptados[0]) ? (string) $aceptados[0] : null,
            "rechazados" => isset($rechazados[0]) ? (string) $rechazados[0] : null,
        ];
    }
    return null;
}

function obtenerSemilla()
{
    $entorno = $GLOBALS["empresa"]["modo"] != "PROD" ? "" : "cert";
    $url = "https://api.sii.cl/recursos/v1/boleta.electronica.semilla";
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Accept: application/xml"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        throw new Exception("Error obteniendo la semilla.");
    }
    preg_match('/<SEMILLA>(.*?)<\/SEMILLA>/', $response, $matches);
    if (isset($matches[1])) {
        return (string) $matches[1];
    }

    return NULL;
}

function firmarSemilla($semilla, $certPath, $certPass)
{
    // Crear el XML de base
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<getToken><item><Semilla>' . $semilla . '</Semilla></item></getToken>';

    // Cargar el certificado desde el archivo .p12
    $pkcs12 = $certPath;
    if (!openssl_pkcs12_read($pkcs12, $certs, $certPass)) {
        throw new Exception("Error leyendo el certificado.");
    }

    $privateKey = $certs['pkey'];
    $publicCert = $certs['cert'];

    // Cargar el XML en un DOMDocument
    $doc = new DOMDocument();
    $doc->loadXML($xml);

    // Crear la firma usando xmlseclibs
    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N); // Método de canonicalización
    $objDSig->addReference(
        $doc, // Se firma todo el XML
        XMLSecurityDSig::SHA1, // Algoritmo de hash
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'] // Tipo de transformación
    );

    // Crear la clave de seguridad con la clave privada
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
    $objKey->loadKey($privateKey);

    // Firmar el documento
    $objDSig->sign($objKey);

    // Agregar el certificado público
    $objDSig->add509Cert($publicCert);

    // Insertar la firma en el XML
    $objDSig->appendSignature($doc->documentElement);

    // Retornar el XML firmado como string
    $pulito = str_replace("ds:", "", $doc->saveXML());
    $xmlString = preg_replace('/<\/Signature><\/getToken>/', PHP_EOL . '</Signature></getToken>', $pulito);
    return $xmlString;
}

function firmarBoletaElectronica($xmlString, $certPath, $certPass)
{
    // Cargar el XML en un objeto DOMDocument
    $doc = new DOMDocument();
    $doc->loadXML($xmlString);

    // Eliminar las firmas existentes
    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

    // Eliminar firma del DTE
    $dteSignature = $xpath->query('//ds:Signature[ancestor::DTE]')->item(0);
    if ($dteSignature) {
        $dteSignature->parentNode->removeChild($dteSignature);
    }

    // Eliminar firma del EnvioBOLETA
    $envioSignature = $xpath->query('//ds:Signature[ancestor::EnvioBOLETA]')->item(0);
    if ($envioSignature) {
        $envioSignature->parentNode->removeChild($envioSignature);
    }

    // Cargar el certificado desde el archivo .p12
    $pkcs12 = $certPath;
    if (!openssl_pkcs12_read($pkcs12, $certs, $certPass)) {
        throw new Exception("Error leyendo el certificado.");
    }

    $privateKey = $certs['pkey'];
    $publicCert = $certs['cert'];

    // Firmar el DTE
    $dte = $xpath->query('//DTE')->item(0);
    if ($dte) {
        $dteID = $dte->getAttribute('ID');
        $dteSignature = firmarNodo($doc, $dte, $privateKey, $publicCert, $dteID);
        $dte->appendChild($dteSignature);
    }

    // Firmar el EnvioBOLETA
    $envioBOLETA = $xpath->query('//EnvioBOLETA')->item(0);
    if ($envioBOLETA) {
        $envioID = $envioBOLETA->getElementsByTagName('SetDTE')->item(0)->getAttribute('ID');
        $envioSignature = firmarNodo($doc, $envioBOLETA, $privateKey, $publicCert, $envioID);
        $envioBOLETA->appendChild($envioSignature);
    }

    // Devolver el XML firmado como string
    $pulito = str_replace("ds:", "", $doc->saveXML());
    $xmlString = preg_replace('/<\/Signature><\/EnvioBOLETA>/', PHP_EOL . '</Signature></EnvioBOLETA>', $pulito);
    return $xmlString;
}

function firmarNodo($doc, $nodo, $privateKey, $publicCert, $referenceURI)
{
    // Crear la firma usando xmlseclibs
    $objDSig = new XMLSecurityDSig();
    $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N); // Método de canonicalización
    $objDSig->addReference(
        $nodo, // Se firma el nodo específico
        XMLSecurityDSig::SHA1, // Algoritmo de hash
        ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'], // Tipo de transformación
        ['force_uri' => true, 'uri' => '#' . $referenceURI] // Referencia URI
    );

    // Crear la clave de seguridad con la clave privada
    $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
    $objKey->loadKey($privateKey);

    // Firmar el documento
    $objDSig->sign($objKey);

    // Agregar el certificado público
    $objDSig->add509Cert($publicCert);

    // Insertar la firma en el nodo
    $objDSig->appendSignature($nodo);

    // Retornar el nodo de la firma
    return $nodo->lastChild;
}
function obtenerToken($xmlFirmado)
{
    $url = "https://api.sii.cl/recursos/v1/boleta.electronica.token";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlFirmado);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/xml",
        "Accept: application/xml"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        throw new Exception("Error obteniendo el token.");
    }
    preg_match('/<TOKEN>(.*?)<\/TOKEN>/', $response, $matches);
    if (isset($matches[1])) {
        return (string) $matches[1];
    }

    return NULL;
}

function firmarXml($xmlString, $p12Path, $p12Pass)
{
    // Cargar el XML
    $xml = new DOMDocument();
    $xml->loadXML($xmlString);

    // Eliminar todas las firmas existentes
    $signatureNodes = $xml->getElementsByTagName('Signature');
    while ($signatureNodes->length > 0) {
        $signatureNode = $signatureNodes->item(0);
        $signatureNode->parentNode->removeChild($signatureNode);
    }

    // Cargar el archivo .p12
    if (!openssl_pkcs12_read($p12Path, $certs, $p12Pass)) {
        throw new Exception('No se pudo cargar el archivo PKCS#12.');
    }

    // Obtener la clave privada y pública
    $privateKey = $certs['pkey'];
    $publicCert = $certs['cert'];

    // Obtener la clave pública como un objeto OpenSSL
    $publicKey = openssl_pkey_get_public($publicCert);
    if (!$publicKey) {
        throw new Exception('No se pudo obtener la clave pública.');
    }

    // Obtener los detalles de la clave pública (modulus y exponent)
    $keyDetails = openssl_pkey_get_details($publicKey);
    if (!$keyDetails) {
        throw new Exception('No se pudo obtener detalles de la clave pública.');
    }

    $modulus = $keyDetails['rsa']['n'];  // Modulus
    $exponent = $keyDetails['rsa']['e'];  // Exponent

    // Convertir modulus y exponent a base64
    $modulusBase64 = base64_encode($modulus);
    $exponentBase64 = base64_encode($exponent);

    // Crear un objeto para manejar el XML y obtener el contenido a firmar
    $canonicalXml = $xml->C14N();  // Obtener el XML en formato canónico

    // Firmar el contenido dos veces con la misma o diferentes claves
    $signature1 = null;
    if (!openssl_sign($canonicalXml, $signature1, $privateKey, OPENSSL_ALGO_SHA1)) {
        throw new Exception('No se pudo firmar el XML.');
    }

    // Crear la primera firma
    $signatureNode1 = $xml->createElement('Signature');
    $signedInfo1 = $xml->createElement('SignedInfo');
    $canonicalizationMethod1 = $xml->createElement('CanonicalizationMethod', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
    $signedInfo1->appendChild($canonicalizationMethod1);
    $signatureMethod1 = $xml->createElement('SignatureMethod', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    $signedInfo1->appendChild($signatureMethod1);

    $reference1 = $xml->createElement('Reference');
    $reference1->setAttribute('URI', '#LibreDTE_T39F5024');
    $transforms1 = $xml->createElement('Transforms');
    $transform1 = $xml->createElement('Transform');
    $transform1->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
    $transforms1->appendChild($transform1);
    $reference1->appendChild($transforms1);
    $digestMethod1 = $xml->createElement('DigestMethod', 'http://www.w3.org/2000/09/xmldsig#sha1');
    $digestValue1 = $xml->createElement('DigestValue', base64_encode(sha1($canonicalXml, true)));
    $reference1->appendChild($digestMethod1);
    $reference1->appendChild($digestValue1);
    $signedInfo1->appendChild($reference1);

    $signatureValue1 = $xml->createElement('SignatureValue', base64_encode($signature1));

    $keyInfo1 = $xml->createElement('KeyInfo');
    $keyValue1 = $xml->createElement('KeyValue');
    $rsaKeyValue1 = $xml->createElement('RSAKeyValue');
    $modulusElement1 = $xml->createElement('Modulus', $modulusBase64);  // Modulus codificado en base64
    $exponentElement1 = $xml->createElement('Exponent', $exponentBase64);  // Exponent codificado en base64
    $rsaKeyValue1->appendChild($modulusElement1);
    $rsaKeyValue1->appendChild($exponentElement1);
    $keyValue1->appendChild($rsaKeyValue1);
    $keyInfo1->appendChild($keyValue1);

    $signatureNode1->appendChild($signedInfo1);
    $signatureNode1->appendChild($signatureValue1);
    $signatureNode1->appendChild($keyInfo1);

    // Insertar la primera firma en el XML
    $xml->documentElement->appendChild($signatureNode1);

    // Firmar el contenido por segunda vez
    $signature2 = null;
    if (!openssl_sign($canonicalXml, $signature2, $privateKey, OPENSSL_ALGO_SHA1)) {
        throw new Exception('No se pudo firmar el XML.');
    }

    // Crear la segunda firma (lo mismo que antes)
    $signatureNode2 = $xml->createElement('Signature');
    $signedInfo2 = $xml->createElement('SignedInfo');
    $canonicalizationMethod2 = $xml->createElement('CanonicalizationMethod', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
    $signedInfo2->appendChild($canonicalizationMethod2);
    $signatureMethod2 = $xml->createElement('SignatureMethod', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
    $signedInfo2->appendChild($signatureMethod2);

    $reference2 = $xml->createElement('Reference');
    $reference2->setAttribute('URI', '#LibreDTE_T39F5024');
    $transforms2 = $xml->createElement('Transforms');
    $transform2 = $xml->createElement('Transform');
    $transform2->setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
    $transforms2->appendChild($transform2);
    $reference2->appendChild($transforms2);
    $digestMethod2 = $xml->createElement('DigestMethod', 'http://www.w3.org/2000/09/xmldsig#sha1');
    $digestValue2 = $xml->createElement('DigestValue', base64_encode(sha1($canonicalXml, true)));
    $reference2->appendChild($digestMethod2);
    $reference2->appendChild($digestValue2);
    $signedInfo2->appendChild($reference2);

    $signatureValue2 = $xml->createElement('SignatureValue', base64_encode($signature2));

    $keyInfo2 = $xml->createElement('KeyInfo');
    $keyValue2 = $xml->createElement('KeyValue');
    $rsaKeyValue2 = $xml->createElement('RSAKeyValue');
    $modulusElement2 = $xml->createElement('Modulus', $modulusBase64);  // Modulus codificado en base64
    $exponentElement2 = $xml->createElement('Exponent', $exponentBase64);  // Exponent codificado en base64
    $rsaKeyValue2->appendChild($modulusElement2);
    $rsaKeyValue2->appendChild($exponentElement2);
    $keyValue2->appendChild($rsaKeyValue2);
    $keyInfo2->appendChild($keyValue2);

    $signatureNode2->appendChild($signedInfo2);
    $signatureNode2->appendChild($signatureValue2);
    $signatureNode2->appendChild($keyInfo2);

    // Insertar la segunda firma en el XML
    $xml->documentElement->appendChild($signatureNode2);

    return $xml->saveXML();  // Devolver el XML firmado
}
