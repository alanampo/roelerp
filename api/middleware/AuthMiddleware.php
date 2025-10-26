<?php
/**
 * Middleware de autenticación
 * Valida que las peticiones incluyan un token JWT válido
 */

require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';

class AuthMiddleware {
    /**
     * Valida el token JWT y retorna el payload si es válido
     */
    public static function authenticate($requiredType = null) {
        // Obtener token del header
        $token = JWT::getBearerToken();

        if (!$token) {
            Response::unauthorized('Token no proporcionado. Incluya el token en el header Authorization: Bearer <token>');
        }

        // Decodificar y validar token
        $payload = JWT::decode($token);

        if (!$payload) {
            Response::unauthorized('Token inválido o expirado');
        }

        // Verificar tipo de token si se especifica
        if ($requiredType && isset($payload['type']) && $payload['type'] !== $requiredType) {
            Response::unauthorized('Tipo de token inválido');
        }

        return $payload;
    }

    /**
     * Middleware para proteger rutas que requieren usuario trabajador autenticado
     */
    public static function requireUsuario() {
        $payload = self::authenticate('access');

        // Verificar que sea un usuario trabajador
        if (!isset($payload['user_type']) || $payload['user_type'] !== 'usuario') {
            Response::forbidden('Acceso denegado. Se requiere autenticación de trabajador');
        }

        // Verificar que el usuario no esté inhabilitado
        require_once __DIR__ . '/../models/Usuario.php';
        $usuarioModel = new Usuario();

        if ($usuarioModel->isDisabled($payload['user_id'])) {
            Response::forbidden('Usuario inhabilitado. Contacte al administrador');
        }

        return $payload;
    }

    /**
     * Middleware para proteger rutas que requieren cliente autenticado
     */
    public static function requireCliente() {
        $payload = self::authenticate('access');

        // Verificar que sea un cliente
        if (!isset($payload['user_type']) || $payload['user_type'] !== 'cliente') {
            Response::forbidden('Acceso denegado. Se requiere autenticación de cliente');
        }

        // Verificar que el cliente esté activo
        require_once __DIR__ . '/../models/Cliente.php';
        $clienteModel = new Cliente();

        if (!$clienteModel->isActive($payload['user_id'])) {
            Response::forbidden('Cliente inactivo. Contacte al administrador');
        }

        return $payload;
    }

    /**
     * Middleware para rutas que aceptan tanto usuarios como clientes
     */
    public static function requireAuth() {
        return self::authenticate('access');
    }

    /**
     * Valida refresh token
     */
    public static function validateRefreshToken() {
        $payload = self::authenticate('refresh');
        return $payload;
    }
}
