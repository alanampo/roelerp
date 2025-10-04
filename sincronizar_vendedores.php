<?php
// Script para sincronizar vendedores automáticamente
// Verifica si han pasado más de 24 horas desde la última sincronización

include "./class_lib/sesionSecurity.php";
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';

$con = mysqli_connect($host, $user, $password, $dbname);
if (!$con) {
    die("error|Connection failed: " . mysqli_connect_error());
}

mysqli_query($con, "SET NAMES 'utf8'");

$auto_sync = isset($_POST['auto_sync']) && $_POST['auto_sync'] == 'true';
$resultados = array();

// Verificar si existe la tabla sistema_config
$tabla_existe = mysqli_query($con, "SHOW TABLES LIKE 'sistema_config'");
if (mysqli_num_rows($tabla_existe) == 0) {
    // Crear tabla si no existe
    $create_table = "CREATE TABLE IF NOT EXISTS sistema_config (
        config_key VARCHAR(50) PRIMARY KEY,
        config_value TEXT,
        ultima_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    mysqli_query($con, $create_table);

    // Insertar valor inicial
    mysqli_query($con, "INSERT INTO sistema_config (config_key, config_value, ultima_actualizacion)
                        VALUES ('ultima_sync_vendedores', NOW(), NOW())");
}

// Obtener última sincronización
$query = "SELECT config_value, ultima_actualizacion FROM sistema_config WHERE config_key = 'ultima_sync_vendedores'";
$result = mysqli_query($con, $query);

$debe_sincronizar = false;
$ultima_sync = null;

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $ultima_sync = $row['ultima_actualizacion'];

    // Calcular diferencia en horas
    $ahora = new DateTime();
    $ultima = new DateTime($ultima_sync);
    $diferencia = $ahora->diff($ultima);
    $horas_transcurridas = ($diferencia->days * 24) + $diferencia->h;

    // Si es sincronización automática, solo ejecutar si pasaron más de 24 horas
    if ($auto_sync) {
        $debe_sincronizar = ($horas_transcurridas >= 24);
    } else {
        // Si es manual, siempre ejecutar
        $debe_sincronizar = true;
    }
} else {
    // No existe registro, crear y sincronizar
    mysqli_query($con, "INSERT INTO sistema_config (config_key, config_value, ultima_actualizacion)
                        VALUES ('ultima_sync_vendedores', NOW(), NOW())
                        ON DUPLICATE KEY UPDATE ultima_actualizacion = NOW()");
    $debe_sincronizar = true;
}

if (!$debe_sincronizar) {
    echo json_encode(array(
        'status' => 'skip',
        'mensaje' => 'No es necesario sincronizar. Última sincronización: ' . $ultima_sync,
        'ultima_sync' => $ultima_sync
    ));
    exit;
}

// Ejecutar sincronización
try {
    // 1. Actualizar fechas de último contacto desde cotizaciones/facturas
    $query_actualizar_fechas = "UPDATE clientes c
    LEFT JOIN (
        SELECT id_cliente, MAX(fecha_ultimo) as fecha_ultimo_contacto
        FROM (" . construirQueryFechas($con) . ") AS todas_fechas
        GROUP BY id_cliente
    ) AS ultimas_fechas ON c.id_cliente = ultimas_fechas.id_cliente
    SET c.fecha_ultimo_contacto = ultimas_fechas.fecha_ultimo_contacto
    WHERE ultimas_fechas.fecha_ultimo_contacto IS NOT NULL";

    $resultado_fechas = mysqli_query($con, $query_actualizar_fechas);
    $clientes_actualizados = mysqli_affected_rows($con);

    // 2. Desasignar vendedores inactivos (más de 6 meses sin contacto)
    $query_desasignar = "UPDATE clientes
    SET
        vendedor_anterior = id_vendedor,
        id_vendedor = NULL,
        fecha_cambio_vendedor = NOW()
    WHERE
        id_vendedor IS NOT NULL
        AND fecha_ultimo_contacto IS NOT NULL
        AND fecha_ultimo_contacto < DATE_SUB(NOW(), INTERVAL 6 MONTH)";

    $resultado_desasignacion = mysqli_query($con, $query_desasignar);
    $vendedores_desasignados = mysqli_affected_rows($con);

    // 3. Actualizar timestamp de sincronización
    mysqli_query($con, "UPDATE sistema_config
                        SET config_value = NOW(), ultima_actualizacion = NOW()
                        WHERE config_key = 'ultima_sync_vendedores'");

    echo json_encode(array(
        'status' => 'success',
        'mensaje' => 'Sincronización completada correctamente',
        'clientes_actualizados' => $clientes_actualizados,
        'vendedores_desasignados' => $vendedores_desasignados,
        'timestamp' => date('Y-m-d H:i:s')
    ));

} catch (Exception $e) {
    echo json_encode(array(
        'status' => 'error',
        'mensaje' => 'Error en sincronización: ' . $e->getMessage()
    ));
}

mysqli_close($con);

// Función auxiliar para construir el query de fechas dinámicamente
function construirQueryFechas($con) {
    $tables_check = mysqli_query($con, "SHOW TABLES");
    $existing_tables = array();
    while ($row = mysqli_fetch_array($tables_check)) {
        $existing_tables[] = $row[0];
    }

    $union_parts = array();

    // Cotizaciones
    if (in_array('cotizaciones', $existing_tables)) {
        $union_parts[] = "SELECT id_cliente, MAX(fecha) as fecha_ultimo FROM cotizaciones WHERE id_cliente IS NOT NULL GROUP BY id_cliente";
    }

    // Cotizaciones directas
    if (in_array('cotizaciones_directas', $existing_tables)) {
        $union_parts[] = "SELECT id_cliente, MAX(fecha) as fecha_ultimo FROM cotizaciones_directas WHERE id_cliente IS NOT NULL GROUP BY id_cliente";
    }

    // Facturas vía cotizaciones
    if (in_array('facturas', $existing_tables) && in_array('cotizaciones', $existing_tables)) {
        $cols_cotiz = mysqli_query($con, "SHOW COLUMNS FROM cotizaciones WHERE `Key` = 'PRI'");
        if ($cols_cotiz && mysqli_num_rows($cols_cotiz) > 0) {
            $col = mysqli_fetch_array($cols_cotiz);
            $id_col_cotiz = $col[0]; // Nombre de la columna PRIMARY KEY
            $union_parts[] = "SELECT c.id_cliente, MAX(f.fecha) as fecha_ultimo
                             FROM facturas f
                             INNER JOIN cotizaciones c ON f.id_cotizacion = c.$id_col_cotiz
                             WHERE c.id_cliente IS NOT NULL
                             GROUP BY c.id_cliente";
        }
    }

    // Facturas vía cotizaciones directas
    if (in_array('facturas', $existing_tables) && in_array('cotizaciones_directas', $existing_tables)) {
        $cols_cotiz_dir = mysqli_query($con, "SHOW COLUMNS FROM cotizaciones_directas WHERE `Key` = 'PRI'");
        if ($cols_cotiz_dir && mysqli_num_rows($cols_cotiz_dir) > 0) {
            $col = mysqli_fetch_array($cols_cotiz_dir);
            $id_col_cotiz_dir = $col[0]; // Nombre de la columna PRIMARY KEY
            $union_parts[] = "SELECT cd.id_cliente, MAX(f.fecha) as fecha_ultimo
                             FROM facturas f
                             INNER JOIN cotizaciones_directas cd ON f.id_cotizacion_directa = cd.$id_col_cotiz_dir
                             WHERE cd.id_cliente IS NOT NULL
                             GROUP BY cd.id_cliente";
        }
    }

    if (count($union_parts) == 0) {
        return "SELECT NULL as id_cliente, NULL as fecha_ultimo WHERE 1=0"; // Query vacío válido
    }

    return implode(" UNION ALL ", $union_parts);
}
?>
