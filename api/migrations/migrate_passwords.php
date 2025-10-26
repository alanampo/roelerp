<?php
/**
 * Script de migración de passwords en texto plano a bcrypt
 * IMPORTANTE: Ejecutar este script UNA SOLA VEZ después de hacer backup de la BD
 *
 * Uso:
 * php migrate_passwords.php
 *
 * O desde el navegador:
 * http://tu-dominio.com/api/migrations/migrate_passwords.php?secret=TU_CLAVE_SECRETA
 */

// Clave de seguridad para ejecutar el script (cambiar en producción)
define('MIGRATION_SECRET', 'cambiar_esta_clave_secreta_en_produccion');

// Verificar clave si se ejecuta desde el navegador
if (php_sapi_name() !== 'cli') {
    if (!isset($_GET['secret']) || $_GET['secret'] !== MIGRATION_SECRET) {
        die('Acceso denegado');
    }
}

require_once __DIR__ . '/../../class_lib/class_conecta_mysql.php';

echo "==============================================\n";
echo "Migración de passwords a bcrypt\n";
echo "==============================================\n\n";

$con = mysqli_connect($host, $user, $password, $dbname);

if (!$con) {
    die("Error de conexión: " . mysqli_connect_error() . "\n");
}

mysqli_set_charset($con, 'utf8');

// Migrar passwords de usuarios
echo "1. Migrando passwords de usuarios trabajadores...\n";

$query = "SELECT id, nombre, password FROM usuarios WHERE tipo_usuario = 1";
$result = mysqli_query($con, $query);

if (!$result) {
    die("Error en query: " . mysqli_error($con) . "\n");
}

$usuarios_migrados = 0;
$usuarios_omitidos = 0;

while ($row = mysqli_fetch_assoc($result)) {
    // Verificar si el password ya está hasheado (bcrypt tiene 60 caracteres)
    if (strlen($row['password']) >= 60) {
        echo "  - Usuario '{$row['nombre']}' ya tiene password hasheado. Omitiendo.\n";
        $usuarios_omitidos++;
        continue;
    }

    // Hashear el password
    $hashedPassword = password_hash($row['password'], PASSWORD_BCRYPT);

    // Actualizar en la base de datos
    $updateQuery = "UPDATE usuarios SET password = ? WHERE id = ?";
    $stmt = mysqli_prepare($con, $updateQuery);
    mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $row['id']);

    if (mysqli_stmt_execute($stmt)) {
        echo "  ✓ Usuario '{$row['nombre']}' migrado exitosamente\n";
        $usuarios_migrados++;
    } else {
        echo "  ✗ Error al migrar usuario '{$row['nombre']}': " . mysqli_error($con) . "\n";
    }
}

echo "\nResumen de usuarios:\n";
echo "  - Migrados: $usuarios_migrados\n";
echo "  - Omitidos: $usuarios_omitidos\n\n";

// Migrar passwords de clientes (si tienen)
echo "2. Verificando passwords de clientes...\n";

// Primero verificar si la columna password_hash existe
$checkColumn = mysqli_query($con, "SHOW COLUMNS FROM clientes LIKE 'password_hash'");

if (mysqli_num_rows($checkColumn) == 0) {
    echo "  ⚠ La columna password_hash no existe en la tabla clientes.\n";
    echo "  ⚠ Ejecutar primero la migración 001_add_auth_fields.sql\n\n";
} else {
    echo "  ✓ Columna password_hash existe. Los clientes nuevos usarán passwords hasheados.\n\n";
}

mysqli_close($con);

echo "==============================================\n";
echo "Migración completada\n";
echo "==============================================\n";

// Si se ejecuta desde el navegador, mostrar mensaje HTML
if (php_sapi_name() !== 'cli') {
    echo "<script>alert('Migración completada. Usuarios migrados: $usuarios_migrados');</script>";
}
