<?php
/**
 * Rutas de autenticación
 * Define todas las rutas relacionadas con autenticación de usuarios y clientes
 */

require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/ClienteAuthController.php';

class AuthRoutes {
    private $authController;
    private $clienteAuthController;

    public function __construct() {
        $this->authController = new AuthController();
        $this->clienteAuthController = new ClienteAuthController();
    }

    /**
     * Maneja las rutas de autenticación
     */
    public function handleRequest($method, $path) {
        // Rutas para usuarios trabajadores
        if (preg_match('#^/auth/login$#', $path) && $method === 'POST') {
            return $this->authController->login();
        }

        if (preg_match('#^/auth/register$#', $path) && $method === 'POST') {
            return $this->authController->register();
        }

        if (preg_match('#^/auth/change-password$#', $path) && $method === 'POST') {
            return $this->authController->changePassword();
        }

        if (preg_match('#^/auth/me$#', $path) && $method === 'GET') {
            return $this->authController->me();
        }

        if (preg_match('#^/auth/refresh$#', $path) && $method === 'POST') {
            return $this->authController->refresh();
        }

        if (preg_match('#^/auth/logout$#', $path) && $method === 'POST') {
            return $this->authController->logout();
        }

        if (preg_match('#^/auth/validate$#', $path) && $method === 'GET') {
            return $this->authController->validate();
        }

        // Rutas para clientes
        if (preg_match('#^/cliente/login$#', $path) && $method === 'POST') {
            return $this->clienteAuthController->login();
        }

        if (preg_match('#^/cliente/register$#', $path) && $method === 'POST') {
            return $this->clienteAuthController->register();
        }

        if (preg_match('#^/cliente/change-password$#', $path) && $method === 'POST') {
            return $this->clienteAuthController->changePassword();
        }

        if (preg_match('#^/cliente/me$#', $path) && $method === 'GET') {
            return $this->clienteAuthController->me();
        }

        if (preg_match('#^/cliente/refresh$#', $path) && $method === 'POST') {
            return $this->clienteAuthController->refresh();
        }

        if (preg_match('#^/cliente/logout$#', $path) && $method === 'POST') {
            return $this->clienteAuthController->logout();
        }

        if (preg_match('#^/cliente/validate$#', $path) && $method === 'GET') {
            return $this->clienteAuthController->validate();
        }

        return false; // Ruta no encontrada
    }
}
