<?php

include "./class_lib/sesionSecurity.php";

require 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$mysqli = new mysqli($host, $user, $password, $dbname);

// Verificar conexión
if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Obtener el ID de la factura desde la URL
$rowid = isset($_GET['rowid']) ? intval($_GET['rowid']) : 0;

$folio = isset($_GET['folio']) ? intval($_GET['folio']) : 0;

if ($rowid > 0) {
    // Preparar la consulta para obtener el archivo
    $stmt = $mysqli->prepare("SELECT data FROM facturas WHERE rowid = ?");
    $stmt->bind_param("i", $rowid);
    $stmt->execute();
    $stmt->bind_result($data);
    $stmt->fetch();
    $stmt->close();
    $mysqli->close();

    if ($data) {
        // Decodificar el XML en base64
        $xmlContent = base64_decode($data);

        if ($xmlContent) {
            // Configurar encabezados para la descarga
            header('Content-Type: application/xml');
            header('Content-Disposition: attachment; filename="factura_' . $folio . '.xml"');
            header('Content-Length: ' . strlen($xmlContent));

            // Enviar el contenido del archivo al navegador
            echo $xmlContent;
            exit;
        } else {
            die("Error al decodificar el archivo.");
        }
    } else {
        die("Factura no encontrada.");
    }
} else {
    die("ID no válido.");
}