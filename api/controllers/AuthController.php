<?php
/**
 * Controlador de autenticación para usuarios trabajadores
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Login de usuario trabajador
     * POST /api/auth/login
     */
    public function login() {
        // Obtener datos del request
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        if (!isset($data['username']) || !isset($data['password'])) {
            Response::validationError(
                ['username' => 'Usuario requerido', 'password' => 'Contraseña requerida'],
                'Datos incompletos'
            );
        }

        $username = trim($data['username']);
        $password = $data['password'];

        // Buscar usuario
        $usuario = $this->usuarioModel->findByUsername($username);

        if (!$usuario) {
            Response::error('Credenciales inválidas', 401);
        }

        // Verificar si está inhabilitado
        if ($usuario['inhabilitado'] == 1) {
            Response::forbidden('Usuario inhabilitado. Contacta al administrador para solucionar el problema');
        }

        // Verificar contraseña
        if (!$this->usuarioModel->verifyPassword($password, $usuario['password'])) {
            Response::error('Credenciales inválidas', 401);
        }

        // Generar tokens
        $payload = [
            'user_id' => $usuario['id'],
            'username' => $usuario['nombre'],
            'user_type' => 'usuario',
            'tipo_usuario' => $usuario['tipo_usuario']
        ];

        $accessToken = JWT::encode($payload, 'access');
        $refreshToken = JWT::encode($payload, 'refresh');

        // Preparar permisos
        $permisos = isset($usuario['modulos']) && $usuario['modulos']
            ? explode(',', $usuario['modulos'])
            : [];

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 900, // 15 minutos
            'user' => [
                'id' => $usuario['id'],
                'username' => $usuario['nombre'],
                'nombre_real' => $usuario['nombre_real'],
                'iniciales' => $usuario['iniciales'],
                'permisos' => $permisos
            ]
        ], 'Login exitoso');
    }

    /**
     * Registro de nuevo usuario trabajador
     * POST /api/auth/register
     */
    public function register() {
        // Obtener datos del request
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $errors = [];
        if (!isset($data['username']) || empty(trim($data['username']))) {
            $errors['username'] = 'Usuario requerido';
        }
        if (!isset($data['nombre_real']) || empty(trim($data['nombre_real']))) {
            $errors['nombre_real'] = 'Nombre real requerido';
        }
        if (!isset($data['password']) || empty($data['password'])) {
            $errors['password'] = 'Contraseña requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            Response::validationError($errors, 'Errores de validación');
        }

        $username = trim($data['username']);
        $nombreReal = trim($data['nombre_real']);
        $password = $data['password'];
        $permisos = isset($data['permisos']) && is_array($data['permisos']) ? $data['permisos'] : [];

        // Crear usuario
        $result = $this->usuarioModel->create($username, $nombreReal, $password, $permisos);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'user' => [
                'id' => $result['user']['id'],
                'username' => $result['user']['nombre'],
                'nombre_real' => $result['user']['nombre_real'],
                'iniciales' => $result['user']['iniciales'],
                'permisos' => isset($result['user']['modulos']) && $result['user']['modulos']
                    ? explode(',', $result['user']['modulos'])
                    : []
            ]
        ], 'Usuario creado exitosamente', 201);
    }

    /**
     * Cambiar contraseña
     * POST /api/auth/change-password
     */
    public function changePassword() {
        // Validar autenticación
        $payload = AuthMiddleware::requireUsuario();

        // Obtener datos del request
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $errors = [];
        if (!isset($data['current_password']) || empty($data['current_password'])) {
            $errors['current_password'] = 'Contraseña actual requerida';
        }
        if (!isset($data['new_password']) || empty($data['new_password'])) {
            $errors['new_password'] = 'Nueva contraseña requerida';
        } elseif (strlen($data['new_password']) < 6) {
            $errors['new_password'] = 'La nueva contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            Response::validationError($errors, 'Errores de validación');
        }

        // Verificar contraseña actual (incluir password en la consulta)
        $usuario = $this->usuarioModel->findById($payload['user_id'], true);

        if (!$usuario || !isset($usuario['password']) || !$this->usuarioModel->verifyPassword($data['current_password'], $usuario['password'])) {
            Response::error('Contraseña actual incorrecta', 400);
        }

        // Actualizar contraseña
        $result = $this->usuarioModel->updatePassword($payload['user_id'], $data['new_password']);

        if (!$result['success']) {
            Response::serverError('Error al actualizar contraseña');
        }

        Response::success(null, 'Contraseña actualizada exitosamente');
    }

    /**
     * Obtener información del usuario autenticado
     * GET /api/auth/me
     */
    public function me() {
        // Validar autenticación
        $payload = AuthMiddleware::requireUsuario();

        // Obtener información del usuario
        $usuario = $this->usuarioModel->findById($payload['user_id']);

        if (!$usuario) {
            Response::notFound('Usuario no encontrado');
        }

        // Preparar permisos
        $permisos = isset($usuario['modulos']) && $usuario['modulos']
            ? explode(',', $usuario['modulos'])
            : [];

        Response::success([
            'id' => $usuario['id'],
            'username' => $usuario['nombre'],
            'nombre_real' => $usuario['nombre_real'],
            'iniciales' => $usuario['iniciales'],
            'permisos' => $permisos
        ]);
    }

    /**
     * Refrescar token de acceso
     * POST /api/auth/refresh
     */
    public function refresh() {
        // Validar refresh token
        $payload = AuthMiddleware::validateRefreshToken();

        // Verificar que el usuario siga activo
        $usuario = $this->usuarioModel->findById($payload['user_id']);

        if (!$usuario || $usuario['inhabilitado'] == 1) {
            Response::unauthorized('Usuario no válido o inhabilitado');
        }

        // Generar nuevo access token
        $newPayload = [
            'user_id' => $usuario['id'],
            'username' => $usuario['nombre'],
            'user_type' => 'usuario',
            'tipo_usuario' => $usuario['tipo_usuario']
        ];

        $accessToken = JWT::encode($newPayload, 'access');

        Response::success([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 900
        ], 'Token refrescado exitosamente');
    }

    /**
     * Logout (invalidar tokens sería con blacklist en producción)
     * POST /api/auth/logout
     */
    public function logout() {
        // Validar autenticación
        AuthMiddleware::requireUsuario();

        // En una implementación completa, aquí se agregaría el token a una blacklist
        // Por ahora solo retornamos éxito, el cliente debe eliminar el token

        Response::success(null, 'Logout exitoso');
    }

    /**
     * Validar token
     * GET /api/auth/validate
     */
    public function validate() {
        // Validar autenticación
        $payload = AuthMiddleware::requireUsuario();

        Response::success([
            'valid' => true,
            'user_id' => $payload['user_id'],
            'user_type' => $payload['user_type']
        ], 'Token válido');
    }
}
