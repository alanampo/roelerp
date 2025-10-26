<?php
/**
 * Entry point de la API
 * Maneja todas las peticiones entrantes y las enruta a los controladores correspondientes
 */

// Manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción

// Headers
header('Content-Type: application/json; charset=utf-8');

// Cargar middlewares
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/utils/Response.php';

// Aplicar CORS
CorsMiddleware::handle();

// Manejador global de errores
set_exception_handler(function($e) {
    error_log($e->getMessage());
    Response::serverError('Error interno del servidor: ' . $e->getMessage());
});

// Obtener método HTTP y ruta
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Remover query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Remover /api del path si existe
$path = preg_replace('#^/api#', '', $path);

// Si la ruta está vacía, mostrar información de la API
if ($path === '/' || $path === '') {
    Response::success([
        'name' => 'Roel ERP API',
        'version' => '1.0.0',
        'description' => 'API de autenticación y gestión para Roel ERP',
        'endpoints' => [
            'auth' => [
                'POST /api/auth/login' => 'Login de usuario trabajador',
                'POST /api/auth/register' => 'Registro de usuario trabajador',
                'POST /api/auth/change-password' => 'Cambiar contraseña (requiere autenticación)',
                'GET /api/auth/me' => 'Obtener información del usuario (requiere autenticación)',
                'POST /api/auth/refresh' => 'Refrescar token de acceso',
                'POST /api/auth/logout' => 'Cerrar sesión (requiere autenticación)',
                'GET /api/auth/validate' => 'Validar token (requiere autenticación)'
            ],
            'cliente' => [
                'POST /api/cliente/login' => 'Login de cliente',
                'POST /api/cliente/register' => 'Registro de cliente',
                'POST /api/cliente/change-password' => 'Cambiar contraseña (requiere autenticación)',
                'GET /api/cliente/me' => 'Obtener información del cliente (requiere autenticación)',
                'POST /api/cliente/refresh' => 'Refrescar token de acceso',
                'POST /api/cliente/logout' => 'Cerrar sesión (requiere autenticación)',
                'GET /api/cliente/validate' => 'Validar token (requiere autenticación)'
            ]
        ],
        'authentication' => 'Bearer Token (JWT)',
        'docs' => 'Ver README.md para más información'
    ], 'Bienvenido a Roel ERP API');
}

// Cargar rutas
require_once __DIR__ . '/routes/auth.php';

$authRoutes = new AuthRoutes();

// Intentar manejar la petición con las rutas de autenticación
$handled = $authRoutes->handleRequest($method, $path);

// Si no se encontró la ruta
if (!$handled) {
    Response::notFound('Endpoint no encontrado: ' . $method . ' ' . $path);
}
