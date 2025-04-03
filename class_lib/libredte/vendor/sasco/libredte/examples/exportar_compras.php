<?php

if (strpos($_SERVER['HTTP_HOST'], 'roelplant') !== false) {
    include $_SERVER['DOCUMENT_ROOT'] . "/class_lib/sesionSecurity.php";
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/class_conecta_mysql.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/class_lib/funciones.php';
} else {
    include $_SERVER['DOCUMENT_ROOT'] . "/roelerp/class_lib/sesionSecurity.php";
    require $_SERVER['DOCUMENT_ROOT'] . '/roelerp/class_lib/class_conecta_mysql.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/roelerp/class_lib/funciones.php';
}
include 'inc.php';

set_time_limit(360);
$config = [
    'firma' => [
        'file' => 'firma.p12',
        //'data' => '', // contenido del archivo certificado.p12
        'pass' => ""
    ],
];
try {
    $con = mysqli_connect($host, $user, $password, $dbname);
    // Check connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_query($con, "SET NAMES 'utf8'");

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
    $anio = 2022;
    $mes = 9;
  
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
                    mysqli_query($con, $query);

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
                    mysqli_query($con, $query);
                }
            }
        }
        curl_close($ch);
    }

    echo "FINISHED";
} catch (\Throwable $th) {
    echo $th->getMessage();
}
