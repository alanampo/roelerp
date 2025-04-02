<?php

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

header('Content-type: text/html; charset=utf-8');
include 'inc.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

$config = [
    'firma' => [
        'file' => 'new.p12',
        'pass' => '2270',
    ],
];
$Firma = new \sasco\LibreDTE\FirmaElectronica($config['firma']);

$consulta = $_POST["consulta"];
error_reporting(E_ERROR | E_PARSE); // Ignorar advertencias de desuso
if ($consulta == "get_compras") {
    $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($config['firma']);
    if (!$token) {
        foreach (\sasco\LibreDTE\Log::readAll() as $error)
            echo $error, "\n";
        exit;
    }

    $rutEmpresa = '77436423';
    $dv = '4';
    
    try {
        // URL del servicio
        $url = "https://www4.sii.cl/consdcvinternetui/services/data/facadeService/getDetalleCompra";

        // Datos para el cuerpo de la solicitud en formato JSON
        $data = [
            "metaData" => [
                "conversationId" => $token,
                "transactionId" => "0",
                "namespace" => "cl.sii.sdi.lob.diii.consdcv.data.api.interfaces.FacadeService/getDetalleCompra"
            ],
            "data" => [
                "rutEmisor" => $rutEmpresa,
                "dvEmisor" => $dv,
                "ptributario" => $_POST["anio"] . str_pad($_POST["mes"], 2, '0', STR_PAD_LEFT),
                "estadoContab" => "REGISTRO",
                "codTipoDoc" => 33,
                "operacion" => "COMPRA"
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
        die(json_encode($response));
        // Verificar errores
        if (curl_errno($ch)) {
            echo "Error al realizar la solicitud: " . curl_error($ch);
        } else {
            // Imprimir la respuesta
            $data = json_decode($response, true);
            if ($data["data"] != null) {
                echo "<div class='box box-primary'>";
                echo "<div class='box-header with-border'>";
                echo "<h3 class='box-title'>Facturas Electrónicas de Compra</h3>";
                echo "</div>";
                echo "<div class='box-body'>";
                echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Fecha</th><th>Folio</th><th>RUT Prov.</th><th>Razon Social</th><th>Monto Total</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                foreach ($data["data"] as $row) {
                    $onclick = "onClick=\"modalDetalle({
                        rutDoc: $row[detRutDoc],
                        dvDoc: $row[detDvDoc],
                        dcvNroDoc: $row[detNroDoc],
                        codTipoDoc: 33,
                        dhdrCodigo: $row[dhdrCodigo],
                        detCodigo: $row[detCodigo],
                        detRznSoc: '$row[detRznSoc]',
                        detFchDoc: '$row[detFchDoc]',
                        rutReceptor: '$rutEmpresa-$dv',
                        detMntIVA: $row[detMntIVA],
                        detMntTotal: $row[detMntTotal],
                        detFecRecepcion: '$row[detFecRecepcion]',
                        descTipoTransaccion: '$row[descTipoTransaccion]'
                    })\"";

                    $f = explode("/", $row["detFchDoc"]);
                    echo "<tr style='cursor:pointer;'>";
                    echo "<td $onclick><span style='display:none;'>$f[2]$f[1]$f[0]</span>$row[detFchDoc]</td>";
                    echo "<td $onclick>$row[detNroDoc]</td>"; //FOLIO
                    echo "<td $onclick>$row[detRutDoc]-$row[detDvDoc]</td>";
                    echo "<td $onclick>$row[detRznSoc]</td>";
                    echo "<td $onclick>" . ($row["detMntTotal"] != NULL ? "$" . number_format($row["detMntTotal"], 0, ',', '.') : "$0") . "</td>";
                    //echo "<td><button onclick='generarFichaCliente($id_cliente)' class='btn btn-success btn-sm fa fa-arrow-circle-right'></button></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div class='callout callout-danger'><b>No se encontraron registros...</b></div>";
            }
        }

        // Cerrar cURL
        curl_close($ch);
    } catch (\Throwable $th) {
        print($th->getMessage());
    }
} else if ($consulta == "exportar_compras") {
    $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($config['firma']);
    if (!$token) {
        foreach (\sasco\LibreDTE\Log::readAll() as $error)
            echo $error, "\n";
        exit;
    }

    $rutEmpresa = '77436423';
    $dv = '4';

    try {
        // URL del servicio
        $url = "https://www4.sii.cl/consdcvinternetui/services/data/facadeService/getDetalleCompra";

        // Datos para el cuerpo de la solicitud en formato JSON
        $data = [
            "metaData" => [
                "conversationId" => $token,
                "transactionId" => "0",
                "namespace" => "cl.sii.sdi.lob.diii.consdcv.data.api.interfaces.FacadeService/getDetalleCompra"
            ],
            "data" => [
                "rutEmisor" => $rutEmpresa,
                "dvEmisor" => $dv,
                "ptributario" => $_POST["anio"] . str_pad($_POST["mes"], 2, '0', STR_PAD_LEFT),
                "estadoContab" => "REGISTRO",
                "codTipoDoc" => 33,
                "operacion" => "COMPRA"
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

        // Verificar errores
        if (curl_errno($ch)) {
            echo "Error al realizar la solicitud: " . curl_error($ch);
        } else {
            // Imprimir la respuesta
            $data = json_decode($response, true);
            if ($data["data"] != null) {
                echo "<div class='box box-primary'>";
                echo "<div class='box-header with-border'>";
                echo "<h3 class='box-title'>Facturas Electrónicas de Compra</h3>";
                echo "</div>";
                echo "<div class='box-body'>";
                echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
                echo "<thead>";
                echo "<tr>";
                echo "<th>Fecha</th><th>Folio</th><th>RUT Prov.</th><th>Razon Social</th><th>Monto Total</th>";
                echo "</tr>";
                echo "</thead>";
                echo "<tbody>";
                foreach ($data["data"] as $row) {
                    $onclick = "onClick=\"modalDetalle({
                        rutDoc: $row[detRutDoc],
                        dvDoc: $row[detDvDoc],
                        dcvNroDoc: $row[detNroDoc],
                        codTipoDoc: 33,
                        dhdrCodigo: $row[dhdrCodigo],
                        detCodigo: $row[detCodigo],
                        detRznSoc: '$row[detRznSoc]',
                        detFchDoc: '$row[detFchDoc]',
                        rutReceptor: '$rutEmpresa-$dv',
                        detMntIVA: $row[detMntIVA],
                        detMntTotal: $row[detMntTotal],
                        detFecRecepcion: '$row[detFecRecepcion]',
                        descTipoTransaccion: '$row[descTipoTransaccion]'
                    })\"";

                    $f = explode("/", $row["detFchDoc"]);
                    echo "<tr style='cursor:pointer;'>";
                    echo "<td $onclick><span style='display:none;'>$f[2]$f[1]$f[0]</span>$row[detFchDoc]</td>";
                    echo "<td $onclick>$row[detNroDoc]</td>"; //FOLIO
                    echo "<td $onclick>$row[detRutDoc]-$row[detDvDoc]</td>";
                    echo "<td $onclick>$row[detRznSoc]</td>";
                    echo "<td $onclick>" . ($row["detMntTotal"] != NULL ? "$" . number_format($row["detMntTotal"], 0, ',', '.') : "$0") . "</td>";
                    //echo "<td><button onclick='generarFichaCliente($id_cliente)' class='btn btn-success btn-sm fa fa-arrow-circle-right'></button></td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div class='callout callout-danger'><b>No se encontraron registros...</b></div>";
            }
        }
        // Cerrar cURL
        curl_close($ch);
    } catch (\Throwable $th) {
        print($th->getMessage());
    }
} else if ($consulta == "get_historico_compras") {
    $query = "SELECT valor FROM config WHERE id = 'LAST_UPDATE_FACTURAS_COMPRA'";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        $row = mysqli_fetch_array($val);
        $lastUpdate = $row["valor"];
    }
    $success = false;
    if ((isset($_POST["isUpdating"]) && $_POST["isUpdating"] == 1) || !isset($lastUpdate)) {
        $success = updateFacturas($config, $con);
        if ($success !== TRUE) {
            echo "<h4 class='text-danger'>Ocurrió un error al actualizar las facturas: $success</h4>";
            exit;
        }
    } else if (isset($lastUpdate)) {
        // Obtener la fecha y hora actual
        $fechaActual = new DateTime();

        // Crear un objeto DateTime desde la cadena con el formato especificado
        $fechaGuardada = DateTime::createFromFormat("Y-m-d H:i:s", $lastUpdate);

        if ($fechaGuardada !== false) {
            // Calcular la diferencia entre las fechas
            $diferencia = $fechaGuardada->diff($fechaActual);

            // Obtener la diferencia en horas
            $horasDiferencia = $diferencia->h + $diferencia->days * 24;

            if ($horasDiferencia >= 24) {
                $success = updateFacturas($config, $con);
                if ($success !== TRUE) {
                    echo "<h4 class='text-danger'>Ocurrió un error al actualizar las facturas: $success</h4>";
                    exit;
                }
            } 
        }
    }

    $query = "SELECT p.razonSocial AS 'razonSocial', p.rut AS 'rut', f.fecha AS 'fecha', DATE_FORMAT(f.fecha, '%d/%m/%y') as fecha_formatted, f.folio AS 'folio', f.montoTotal AS 'montoTotal', f.iva AS 'iva', f.montoNeto AS 'montoNeto', f.id, (SELECT IFNULL(SUM(pa.monto),0) FROM facturas_compra_pagos pa WHERE pa.id_factura_compra = f.id) AS 'pagos' FROM facturas_compra f INNER JOIN proveedores p ON f.id_proveedor = p.id;
    ";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Facturas de Compra <small>Última actualización: " . (!isset($lastUpdate) ? "Nunca" : date("d/m/Y H:i", strtotime($lastUpdate))) . "</small><button onclick='getComprasHistorico(true)' class='btn btn-sm btn-primary ml-2'>Actualizar</button></h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla_hist' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Fecha</th><th>Folio</th><th>Razón Social</th><th>Monto Total</th><th>Balance</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $btn_add_pago = "<button style='padding-left:11px;padding-right:11px' onclick='agregarPago($ww[id], $ww[folio], $ww[montoTotal])' class='btn btn-primary fa fa-usd btn-sm'> </button>";

            $balance = (float) $ww["montoTotal"] - (float) $ww["pagos"];
            $deuda = $balance <= 0 ? 0 : number_format($balance, 0, ',', '.');
            echo "<tr style='cursor:pointer;'>";
            echo "<td><span style='display:none;'>$ww[fecha]</span>$ww[fecha_formatted]</td>";
            echo "<td>$ww[folio]</td>";
            echo "<td>$ww[razonSocial]</td>";
            echo "<td>$" . number_format($ww["montoTotal"], 0, ',', '.') . "</td>";
            echo "<td class='text-" . ($balance <= 0 ? "success" : "danger") . "'>$$deuda</td>";
            echo "<td>$btn_add_pago</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron registros...</b></div>";
    }
} else if ($consulta == "guardar_pago") {
    $monto = mysqli_real_escape_string($con, $_POST["monto"]);
    $comentario = $_POST["comentario"] != null && strlen($_POST["comentario"]) > 0 ? mysqli_real_escape_string($con, $_POST["comentario"]) : null;
    $rowid_factura = $_POST["facturaID"];
    $query = "INSERT INTO facturas_compra_pagos (
            id_factura_compra,
            monto,
            fecha,
            comentario
        ) VALUES (
            $rowid_factura,
            '$monto',
            NOW(),
            UPPER('$comentario')
        )";
    if (mysqli_query($con, $query)) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "get_pagos") {
    $rowid_factura = $_POST["facturaID"];

    $query = "SELECT DATE_FORMAT(fecha, '%d/%m/%y %H:%i') as fecha, 
    monto, comentario, id, id_factura_compra FROM facturas_compra_pagos WHERE id_factura_compra = $rowid_factura ORDER BY fecha DESC";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            $monto = $ww["monto"] != null ? "$" . number_format($ww["monto"], 0, ',', '.') : "";

            $rowid = $ww["id"];
            echo "
                <tr class='text-center' x-monto='$ww[monto]'>
                    <td>$ww[fecha]</td>
                    <td>$ww[comentario]</td>
                    <td>$monto</td>
                    <td>
                        <button onclick='eliminarPago($ww[id])' class='btn btn-sm btn-danger fa fa-trash'></button>
                    </td>
                </tr>
            ";
        }
    } else {
        echo "
            <tr class='text-center'>
                <td colspan='4'>
                    Aún no hay pagos para este documento
                </td>
            </tr>
        ";
    }
} else if ($consulta == "eliminar_pago") {
    $rowid = $_POST["rowid"];
    if (mysqli_query($con, "DELETE FROM facturas_compra_pagos WHERE id = $rowid;")) {
        echo "success";
    } else {
        print_r(mysqli_error($con));
    }
} else if ($consulta == "get_grafico_proveedores") {
    $query = "SELECT p.id as id_proveedor, p.razonSocial AS 'razonSocial', p.rut AS 'rut', f.fecha AS 'fecha', DATE_FORMAT(f.fecha, '%d/%m/%y') as fecha_formatted, f.folio AS 'folio', f.montoTotal AS 'montoTotal', f.iva AS 'iva', f.montoNeto AS 'montoNeto', f.id, (SELECT IFNULL(SUM(pa.monto),0) FROM facturas_compra_pagos pa WHERE pa.id_factura_compra = f.id) AS 'pagos' FROM facturas_compra f INNER JOIN proveedores p ON f.id_proveedor = p.id;
    ";

    $val = mysqli_query($con, $query);
    $items = [];
    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            $items[] = [
                "id" => $ww["id"],
                "id_proveedor" => $ww["id_proveedor"],
                "razonSocial" => $ww["razonSocial"],
                "fecha" => $ww["fecha"],
                "montoTotal" => $ww["montoTotal"],
                "pagos" => $ww["pagos"],
            ];
        }
    }
    echo json_encode($items);
}

function updateFacturas($config, $con)
{
    set_time_limit(360);
    $errors = array();
    try {
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($config['firma']);
        if (!$token) {
            foreach (\sasco\LibreDTE\Log::readAll() as $error)
                echo $error, "\n";
            exit;
        }

        $url = "https://www4.sii.cl/consdcvinternetui/services/data/facadeService/getDetalleCompra";
        $rutEmpresa = '77436423';
        $dv = '4';
        // Crear un intervalo de meses
        $fechaActual = new DateTime();

        // Restar 2 meses a la fecha actual
        $fechaActual->sub(new DateInterval('P2M'));

        // Obtener el año y el mes
        $anio = $fechaActual->format('Y');
        $mes = $fechaActual->format('m');

        // Definir la fecha de inicio
        $fecha_inicio = new DateTime("$anio-$mes-01");

        // Definir la fecha de fin
        $fecha_fin = new DateTime("now");

        // Crear un intervalo de meses
        $intervalo = new DateInterval('P1M');

        // Ajustar la fecha de fin para que incluya el último mes
        $fecha_fin->modify('+1 month');

        // Crear un rango de fechas
        $periodo = new DatePeriod($fecha_inicio, $intervalo, $fecha_fin);

        // Iterar sobre cada fecha en el rango
        foreach ($periodo as $fecha) {
            // Obtener el mes y el año de la fecha actual
            $mes_actual = $fecha->format('n');
            $anio_actual = $fecha->format('Y');
            // Datos para el cuerpo de la solicitud en formato JSON
            $data = [
                "metaData" => [
                    "conversationId" => $token,
                    "transactionId" => "0",
                    "namespace" => "cl.sii.sdi.lob.diii.consdcv.data.api.interfaces.FacadeService/getDetalleCompra"
                ],
                "data" => [
                    "rutEmisor" => $rutEmpresa,
                    "dvEmisor" => $dv,
                    "ptributario" => $anio_actual . str_pad($mes_actual, 2, '0', STR_PAD_LEFT),
                    "estadoContab" => "REGISTRO",
                    "codTipoDoc" => 33,
                    "operacion" => "COMPRA"
                ]
            ];
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

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo "Error al realizar la solicitud: " . curl_error($ch);
            } else {
                $data = json_decode($response, true);

                if ($data["data"] != null) {
                    foreach ($data["data"] as $row) {
                        $f = explode("/", $row["detFchDoc"]);
                        $fr = explode(" ", $row["detFecRecepcion"]);

                        $fr2 = explode("/", $fr[0]);

                        $query = "INSERT IGNORE INTO proveedores (razonSocial, rut)
                    VALUES ('$row[detRznSoc]', '$row[detNroDoc]$row[detDvDoc]')";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }

                        $query = "INSERT IGNORE INTO facturas_compra (
                    id_proveedor,
                    fecha,
                    fechaIngresoSII ,
                    folio,
                    montoTotal,
                    iva,
                    montoNeto)
                    VALUES (
                        (SELECT id FROM proveedores WHERE rut = '$row[detNroDoc]$row[detDvDoc]'),
                        '$f[2]-$f[1]-$f[0]',
                        '$fr2[2]-$fr2[1]-$fr2[0] $fr[1]',
                        $row[detNroDoc],
                        $row[detMntTotal],
                        $row[detMntIVA],
                        $row[detMntNeto]
                    )";
                        if (!mysqli_query($con, $query)) {
                            $errors[] = mysqli_error($con) . "-" . $query;
                        }
                    }
                }
            }
            curl_close($ch);
        }
        $valor = date("Y-m-d H:i:s");
        $query = "INSERT INTO config (id, valor) VALUES ('LAST_UPDATE_FACTURAS_COMPRA', '$valor')
        ON DUPLICATE KEY UPDATE valor = '$valor';";
        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con) . "-" . $query;
        }
        if (count($errors) > 0) {
            return json_encode($errors);
        }
        return TRUE;
    } catch (\Throwable $th) {
        return $th->getMessage();
    }
}
