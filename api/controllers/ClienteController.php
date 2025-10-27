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
}
