<?php
/**
 * Controlador de autenticación para clientes
 */

require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../utils/JWT.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ClienteAuthController {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new Cliente();
    }

    /**
     * Login de cliente
     * POST /api/cliente/login
     */
    public function login() {
        // Obtener datos del request
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        if (!isset($data['email']) || !isset($data['password'])) {
            Response::validationError(
                ['email' => 'Email requerido', 'password' => 'Contraseña requerida'],
                'Datos incompletos'
            );
        }

        $email = trim($data['email']);
        $password = $data['password'];

        // Validar formato de email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::validationError(['email' => 'Formato de email inválido']);
        }

        // Buscar cliente
        $cliente = $this->clienteModel->findByEmail($email);

        if (!$cliente) {
            Response::error('Credenciales inválidas', 401);
        }

        // Verificar si el cliente tiene password_hash (puede no tenerlo si es legacy)
        if (!isset($cliente['password_hash']) || empty($cliente['password_hash'])) {
            Response::error('Cliente sin contraseña configurada. Contacte al administrador', 403);
        }

        // Verificar si está activo
        if (!$cliente['activo'] || $cliente['activo'] == 0) {
            Response::forbidden('Cliente inactivo. Contacte al administrador');
        }

        // Verificar contraseña
        if (!$this->clienteModel->verifyPassword($password, $cliente['password_hash'])) {
            Response::error('Credenciales inválidas', 401);
        }

        // Generar tokens
        $payload = [
            'user_id' => $cliente['id'],
            'email' => $cliente['email'],
            'user_type' => 'cliente'
        ];

        $accessToken = JWT::encode($payload, 'access');
        $refreshToken = JWT::encode($payload, 'refresh');

        Response::success([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 900, // 15 minutos
            'cliente' => [
                'id' => $cliente['id'],
                'nombre' => $cliente['nombre'],
                'email' => $cliente['email'],
                'telefono' => $cliente['telefono'],
                'rut' => $cliente['rut'],
                'razon_social' => $cliente['razon_social']
            ]
        ], 'Login exitoso');
    }

    /**
     * Registro de nuevo cliente
     * POST /api/cliente/register
     */
    public function register() {
        // Obtener datos del request
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $errors = [];
        if (!isset($data['email']) || empty(trim($data['email']))) {
            $errors['email'] = 'Email requerido';
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Formato de email inválido';
        }

        if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
            $errors['nombre'] = 'Nombre requerido';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $errors['password'] = 'Contraseña requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            Response::validationError($errors, 'Errores de validación');
        }

        $email = trim($data['email']);
        $nombre = trim($data['nombre']);
        $password = $data['password'];
        $telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
        $rut = isset($data['rut']) ? trim($data['rut']) : '';
        $domicilio = isset($data['domicilio']) ? trim($data['domicilio']) : '';
        $comuna = isset($data['comuna']) ? trim($data['comuna']) : '';
        $region = isset($data['region']) ? trim($data['region']) : '';
        $razonSocial = isset($data['razon_social']) ? trim($data['razon_social']) : '';

        // Crear cliente
        $result = $this->clienteModel->create(
            $email,
            $nombre,
            $password,
            $telefono,
            $rut,
            $domicilio,
            $comuna,
            $region,
            $razonSocial
        );

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => [
                'id' => $result['cliente']['id'],
                'nombre' => $result['cliente']['nombre'],
                'email' => $result['cliente']['email'],
                'telefono' => $result['cliente']['telefono'],
                'rut' => $result['cliente']['rut'],
                'razon_social' => $result['cliente']['razon_social']
            ]
        ], 'Cliente registrado exitosamente', 201);
    }

    /**
     * Cambiar contraseña
     * POST /api/cliente/change-password
     */
    public function changePassword() {
        // Validar autenticación
        $payload = AuthMiddleware::requireCliente();

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
        $cliente = $this->clienteModel->findById($payload['user_id'], true);

        if (!$cliente || !isset($cliente['password_hash']) || !$this->clienteModel->verifyPassword($data['current_password'], $cliente['password_hash'])) {
            Response::error('Contraseña actual incorrecta', 400);
        }

        // Actualizar contraseña
        $result = $this->clienteModel->updatePassword($payload['user_id'], $data['new_password']);

        if (!$result['success']) {
            Response::serverError('Error al actualizar contraseña');
        }

        Response::success(null, 'Contraseña actualizada exitosamente');
    }

    /**
     * Obtener información del cliente autenticado
     * GET /api/cliente/me
     */
    public function me() {
        // Validar autenticación
        $payload = AuthMiddleware::requireCliente();

        // Obtener información del cliente
        $cliente = $this->clienteModel->findById($payload['user_id']);

        if (!$cliente) {
            Response::notFound('Cliente no encontrado');
        }

        Response::success([
            'id' => $cliente['id'],
            'nombre' => $cliente['nombre'],
            'email' => $cliente['email'],
            'telefono' => $cliente['telefono'],
            'rut' => $cliente['rut'],
            'domicilio' => $cliente['domicilio'],
            'comuna' => $cliente['comuna'],
            'region' => $cliente['region'],
            'razon_social' => $cliente['razon_social']
        ]);
    }

    /**
     * Refrescar token de acceso
     * POST /api/cliente/refresh
     */
    public function refresh() {
        // Validar refresh token
        $payload = AuthMiddleware::validateRefreshToken();

        // Verificar que sea un cliente
        if (!isset($payload['user_type']) || $payload['user_type'] !== 'cliente') {
            Response::forbidden('Token inválido para cliente');
        }

        // Verificar que el cliente siga activo
        $cliente = $this->clienteModel->findById($payload['user_id']);

        if (!$cliente || !$cliente['activo']) {
            Response::unauthorized('Cliente no válido o inactivo');
        }

        // Generar nuevo access token
        $newPayload = [
            'user_id' => $cliente['id'],
            'email' => $cliente['email'],
            'user_type' => 'cliente'
        ];

        $accessToken = JWT::encode($newPayload, 'access');

        Response::success([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 900
        ], 'Token refrescado exitosamente');
    }

    /**
     * Logout
     * POST /api/cliente/logout
     */
    public function logout() {
        // Validar autenticación
        AuthMiddleware::requireCliente();

        // En una implementación completa, aquí se agregaría el token a una blacklist
        // Por ahora solo retornamos éxito, el cliente debe eliminar el token

        Response::success(null, 'Logout exitoso');
    }

    /**
     * Validar token
     * GET /api/cliente/validate
     */
    public function validate() {
        // Validar autenticación
        $payload = AuthMiddleware::requireCliente();

        Response::success([
            'valid' => true,
            'user_id' => $payload['user_id'],
            'user_type' => $payload['user_type']
        ], 'Token válido');
    }
}
