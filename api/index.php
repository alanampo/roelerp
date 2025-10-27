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
            'cliente_auth' => [
                'POST /api/cliente/login' => 'Login de cliente',
                'POST /api/cliente/register' => 'Registro de cliente',
                'POST /api/cliente/change-password' => 'Cambiar contraseña (requiere autenticación)',
                'GET /api/cliente/me' => 'Obtener información del cliente (requiere autenticación)',
                'POST /api/cliente/refresh' => 'Refrescar token de acceso',
                'POST /api/cliente/logout' => 'Cerrar sesión (requiere autenticación)',
                'GET /api/cliente/validate' => 'Validar token (requiere autenticación)'
            ],
            'clientes_crud' => [
                'GET /api/clientes' => 'Listar todos los clientes',
                'GET /api/clientes/{id}' => 'Obtener un cliente',
                'GET /api/clientes/usuario/{id_usuario}' => 'Obtener cliente por ID de usuario',
                'POST /api/clientes' => 'Crear cliente',
                'PUT /api/clientes/{id}' => 'Actualizar cliente',
                'PUT /api/clientes/usuario/{id_usuario}' => 'Actualizar cliente por ID de usuario',
                'DELETE /api/clientes/{id}' => 'Eliminar cliente',
                'POST /api/clientes/{id}/cambiar-vendedor' => 'Cambiar vendedor de cliente',
                'GET /api/clientes/{id}/historial-vendedor' => 'Obtener historial de vendedor',
                'GET /api/clientes/vendedores' => 'Listar vendedores disponibles',
                'GET /api/clientes/comunas' => 'Listar comunas disponibles'
            ]
        ],
        'authentication' => 'Bearer Token (JWT)',
        'docs' => 'Ver README.md para más información'
    ], 'Bienvenido a Roel ERP API');
}

// Cargar rutas
require_once __DIR__ . '/routes/auth.php';
require_once __DIR__ . '/routes/clientes.php';

$authRoutes = new AuthRoutes();
$clientesRoutes = new ClientesRoutes();

// Intentar manejar la petición con las rutas de autenticación
$handled = $authRoutes->handleRequest($method, $path);

// Si no fue manejada, intentar con las rutas de clientes
if (!$handled) {
    $handled = $clientesRoutes->handleRequest($method, $path);
}

// Si no se encontró la ruta
if (!$handled) {
    Response::notFound('Endpoint no encontrado: ' . $method . ' ' . $path);
}
