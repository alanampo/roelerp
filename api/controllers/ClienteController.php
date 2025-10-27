<?php
/**
 * Controlador para gestión de clientes (CRUD)
 */

require_once __DIR__ . '/../models/ClienteModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class ClienteController {
    private $clienteModel;

    public function __construct() {
        $this->clienteModel = new ClienteModel();
    }

    /**
     * Obtener todos los clientes
     * GET /api/clientes
     */
    public function index() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $orderBy = isset($_GET['order_by']) ? $_GET['order_by'] : 'nombre';
        $clientes = $this->clienteModel->getAll($orderBy);

        Response::success(['clientes' => $clientes]);
    }

    /**
     * Obtener un cliente por ID
     * GET /api/clientes/{id}
     */
    public function show($id) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $cliente = $this->clienteModel->findById($id);

        if (!$cliente) {
            Response::notFound('Cliente no encontrado');
        }

        Response::success(['cliente' => $cliente]);
    }

    /**
     * Obtener un cliente por ID de usuario
     * GET /api/clientes/usuario/{id_usuario}
     */
    public function showByUsuario($id_usuario) {
        // Validar autenticación (puede ser trabajador o el mismo cliente)
        $payload = AuthMiddleware::requireAuth();

        // Si es cliente, solo puede ver su propio perfil
        if ($payload['user_type'] === 'cliente' && $payload['user_id'] != $id_usuario) {
            Response::forbidden('No tiene permisos para ver este cliente');
        }

        $cliente = $this->clienteModel->findByUsuarioId($id_usuario);

        if (!$cliente) {
            Response::notFound('Cliente no encontrado para este usuario');
        }

        Response::success(['cliente' => $cliente]);
    }

    /**
     * Crear un nuevo cliente
     * POST /api/clientes
     */
    public function store() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        $errors = [];
        if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
            $errors['nombre'] = 'Nombre requerido';
        }

        if (!empty($errors)) {
            Response::validationError($errors, 'Errores de validación');
        }

        $result = $this->clienteModel->create($data);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => $result['cliente']
        ], 'Cliente creado exitosamente', 201);
    }

    /**
     * Actualizar un cliente
     * PUT /api/clientes/{id}
     */
    public function update($id) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $data = json_decode(file_get_contents('php://input'), true);

        $result = $this->clienteModel->update($id, $data);

        if (!$result['success']) {
            if ($result['error'] === 'Cliente no encontrado') {
                Response::notFound($result['error']);
            }
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => $result['cliente']
        ], 'Cliente actualizado exitosamente');
    }

    /**
     * Actualizar un cliente por ID de usuario
     * PUT /api/clientes/usuario/{id_usuario}
     */
    public function updateByUsuario($id_usuario) {
        // Validar autenticación (puede ser trabajador o el mismo cliente)
        $payload = AuthMiddleware::requireAuth();

        // Si es cliente, solo puede editar su propio perfil
        if ($payload['user_type'] === 'cliente' && $payload['user_id'] != $id_usuario) {
            Response::forbidden('No tiene permisos para editar este cliente');
        }

        // Obtener el cliente asociado al usuario
        $cliente = $this->clienteModel->findByUsuarioId($id_usuario);

        if (!$cliente) {
            Response::notFound('Cliente no encontrado para este usuario');
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $result = $this->clienteModel->update($cliente['id_cliente'], $data);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => $result['cliente']
        ], 'Cliente actualizado exitosamente');
    }

    /**
     * Eliminar un cliente
     * DELETE /api/clientes/{id}
     */
    public function destroy($id) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        // Verificar que existe
        $cliente = $this->clienteModel->findById($id);
        if (!$cliente) {
            Response::notFound('Cliente no encontrado');
        }

        $result = $this->clienteModel->delete($id);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success(null, 'Cliente eliminado exitosamente');
    }

    /**
     * Cambiar vendedor de un cliente
     * POST /api/clientes/{id}/cambiar-vendedor
     */
    public function cambiarVendedor($id) {
        // Validar autenticación de trabajador
        $payload = AuthMiddleware::requireUsuario();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        if (!isset($data['id_vendedor_nuevo'])) {
            Response::validationError(['id_vendedor_nuevo' => 'ID de vendedor nuevo requerido']);
        }

        $id_vendedor_nuevo = $data['id_vendedor_nuevo'];
        $justificacion = $data['justificacion'] ?? '';

        $result = $this->clienteModel->cambiarVendedor(
            $id,
            $id_vendedor_nuevo,
            $payload['user_id'],
            $justificacion
        );

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => $result['cliente']
        ], 'Vendedor cambiado exitosamente');
    }

    /**
     * Obtener historial de cambios de vendedor
     * GET /api/clientes/{id}/historial-vendedor
     */
    public function historialVendedor($id) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        // Verificar que el cliente existe
        $cliente = $this->clienteModel->findById($id);
        if (!$cliente) {
            Response::notFound('Cliente no encontrado');
        }

        $historial = $this->clienteModel->getHistorialVendedor($id);

        Response::success(['historial' => $historial]);
    }

    /**
     * Obtener vendedores disponibles
     * GET /api/clientes/vendedores
     */
    public function vendedores() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $vendedores = $this->clienteModel->getVendedores();

        Response::success(['vendedores' => $vendedores]);
    }

    /**
     * Obtener comunas disponibles
     * GET /api/clientes/comunas
     */
    public function comunas() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $comunas = $this->clienteModel->getComunas();

        Response::success(['comunas' => $comunas]);
    }

    /**
     * Obtener regiones de Chile
     * GET /api/clientes/regiones
     */
    public function regiones() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $regiones = $this->clienteModel->getRegiones();

        Response::success(['regiones' => $regiones]);
    }

    /**
     * Obtener provincias disponibles
     * GET /api/clientes/provincias
     */
    public function provincias() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $provincias = $this->clienteModel->getProvincias();

        Response::success(['provincias' => $provincias]);
    }

    /**
     * Crear cliente CON usuario asociado
     * POST /api/clientes/with-usuario
     */
    public function storeWithUsuario() {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos del cliente
        $errors = [];
        if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
            $errors['nombre'] = 'Nombre del cliente requerido';
        }

        // Validar datos del usuario
        if (!isset($data['email']) || empty(trim($data['email']))) {
            $errors['email'] = 'Email requerido para el usuario';
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Formato de email inválido';
        }

        if (!isset($data['password']) || empty($data['password'])) {
            $errors['password'] = 'Contraseña requerida para el usuario';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            Response::validationError($errors, 'Errores de validación');
        }

        // Separar datos de cliente y usuario
        $dataCliente = [
            'nombre' => $data['nombre'],
            'domicilio' => $data['domicilio'] ?? null,
            'domicilio2' => $data['domicilio2'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'mail' => $data['email'], // El email va al cliente también
            'rut' => $data['rut'] ?? null,
            'comuna' => $data['comuna'] ?? null,
            'razon_social' => $data['razon_social'] ?? null,
            'region' => $data['region'] ?? null,
            'provincia' => $data['provincia'] ?? null,
            'id_vendedor' => $data['id_vendedor'] ?? null
        ];

        $dataUsuario = [
            'email' => $data['email'],
            'password' => $data['password']
        ];

        $result = $this->clienteModel->createWithUsuario($dataCliente, $dataUsuario);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => $result['cliente'],
            'id_usuario' => $result['id_usuario'],
            'usuario_creado' => true
        ], 'Cliente creado con usuario asociado exitosamente', 201);
    }

    /**
     * Asociar un cliente existente a un usuario existente
     * POST /api/clientes/{id}/asociar-usuario
     */
    public function asociarUsuario($id_cliente) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $data = json_decode(file_get_contents('php://input'), true);

        // Validar datos requeridos
        if (!isset($data['id_usuario'])) {
            Response::validationError(['id_usuario' => 'ID de usuario requerido']);
        }

        $result = $this->clienteModel->asociarUsuario($id_cliente, $data['id_usuario']);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success([
            'cliente' => $result['cliente'],
            'usuario' => $result['usuario']
        ], 'Usuario asociado al cliente exitosamente');
    }

    /**
     * Desasociar un usuario de su cliente
     * POST /api/clientes/desasociar-usuario/{id_usuario}
     */
    public function desasociarUsuario($id_usuario) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        $result = $this->clienteModel->desasociarUsuario($id_usuario);

        if (!$result['success']) {
            Response::error($result['error'], 400);
        }

        Response::success(null, 'Usuario desasociado del cliente exitosamente');
    }

    /**
     * Obtener el usuario asociado a un cliente
     * GET /api/clientes/{id}/usuario-asociado
     */
    public function usuarioAsociado($id_cliente) {
        // Validar autenticación de trabajador
        AuthMiddleware::requireUsuario();

        // Verificar que el cliente existe
        $cliente = $this->clienteModel->findById($id_cliente);
        if (!$cliente) {
            Response::notFound('Cliente no encontrado');
        }

        $usuario = $this->clienteModel->getUsuarioAsociado($id_cliente);

        if (!$usuario) {
            Response::success([
                'usuario' => null,
                'tiene_usuario' => false
            ], 'El cliente no tiene usuario asociado');
        }

        Response::success([
            'usuario' => $usuario,
            'tiene_usuario' => true
        ]);
    }
}
