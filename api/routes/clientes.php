<?php
/**
 * Rutas para gestión de clientes (CRUD)
 */

require_once __DIR__ . '/../controllers/ClienteController.php';

class ClientesRoutes {
    private $controller;

    public function __construct() {
        $this->controller = new ClienteController();
    }

    /**
     * Maneja las rutas de clientes
     */
    public function handleRequest($method, $path) {
        // GET /clientes/vendedores - Listar vendedores
        if (preg_match('#^/clientes/vendedores$#', $path) && $method === 'GET') {
            return $this->controller->vendedores();
        }

        // GET /clientes/comunas - Listar comunas
        if (preg_match('#^/clientes/comunas$#', $path) && $method === 'GET') {
            return $this->controller->comunas();
        }

        // GET /clientes/usuario/{id} - Obtener cliente por ID de usuario
        if (preg_match('#^/clientes/usuario/(\d+)$#', $path, $matches) && $method === 'GET') {
            return $this->controller->showByUsuario($matches[1]);
        }

        // PUT /clientes/usuario/{id} - Actualizar cliente por ID de usuario
        if (preg_match('#^/clientes/usuario/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return $this->controller->updateByUsuario($matches[1]);
        }

        // GET /clientes/{id}/historial-vendedor - Obtener historial de vendedor
        if (preg_match('#^/clientes/(\d+)/historial-vendedor$#', $path, $matches) && $method === 'GET') {
            return $this->controller->historialVendedor($matches[1]);
        }

        // POST /clientes/{id}/cambiar-vendedor - Cambiar vendedor
        if (preg_match('#^/clientes/(\d+)/cambiar-vendedor$#', $path, $matches) && $method === 'POST') {
            return $this->controller->cambiarVendedor($matches[1]);
        }

        // GET /clientes/{id} - Obtener cliente por ID
        if (preg_match('#^/clientes/(\d+)$#', $path, $matches) && $method === 'GET') {
            return $this->controller->show($matches[1]);
        }

        // PUT /clientes/{id} - Actualizar cliente
        if (preg_match('#^/clientes/(\d+)$#', $path, $matches) && $method === 'PUT') {
            return $this->controller->update($matches[1]);
        }

        // DELETE /clientes/{id} - Eliminar cliente
        if (preg_match('#^/clientes/(\d+)$#', $path, $matches) && $method === 'DELETE') {
            return $this->controller->destroy($matches[1]);
        }

        // GET /clientes - Listar todos los clientes
        if (preg_match('#^/clientes$#', $path) && $method === 'GET') {
            return $this->controller->index();
        }

        // POST /clientes - Crear cliente
        if (preg_match('#^/clientes$#', $path) && $method === 'POST') {
            return $this->controller->store();
        }

        return false; // Ruta no encontrada
    }
}
