<?php
/**
 * Middleware de CORS (Cross-Origin Resource Sharing)
 * Permite que la API sea accedida desde otros dominios
 */

class CorsMiddleware {
    public static function handle() {
        // Lista de orígenes permitidos
        $allowedOrigins = [
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            'https://erp.roelplant.cl',
            'https://roelplant.cl',
            'http://localhost',
            'http://127.0.0.1'
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Verificar si el origen está permitido o permitir todos en desarrollo
        if (in_array($origin, $allowedOrigins) || strpos($origin, 'localhost') !== false || strpos($origin, '127.0.0.1') !== false) {
            header("Access-Control-Allow-Origin: $origin");
        } else {
            // En producción, permitir el origen si está en la lista
            if ($origin && in_array($origin, $allowedOrigins)) {
                header("Access-Control-Allow-Origin: $origin");
            } else {
                // Fallback: permitir localhost para desarrollo
                header("Access-Control-Allow-Origin: *");
            }
        }

        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin');
        header('Access-Control-Max-Age: 86400'); // cache for 1 day

        // Manejar preflight requests (OPTIONS)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit(0);
        }
    }
}
