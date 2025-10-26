<?php
/**
 * Helper de integración para usar la API desde PHP
 *
 * Uso:
 * require_once 'api/examples/php_integration.php';
 * $api = new RoelERPApi('http://tu-dominio.com/api');
 */

class RoelERPApi {
    private $baseUrl;
    private $accessToken;
    private $refreshToken;

    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/');

        // Cargar tokens de la sesión si existen
        if (isset($_SESSION['roel_api_access_token'])) {
            $this->accessToken = $_SESSION['roel_api_access_token'];
        }
        if (isset($_SESSION['roel_api_refresh_token'])) {
            $this->refreshToken = $_SESSION['roel_api_refresh_token'];
        }
    }

    /**
     * Realiza una petición HTTP a la API
     */
    private function request($method, $endpoint, $data = null, $useAuth = false) {
        $url = $this->baseUrl . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        $headers = ['Content-Type: application/json'];

        if ($useAuth && $this->accessToken) {
            $headers[] = 'Authorization: Bearer ' . $this->accessToken;
        }

        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        // Si el token expiró, intentar refrescar
        if ($httpCode === 401 && $useAuth && $this->refreshToken) {
            if ($this->refresh()) {
                // Reintentar la petición con el nuevo token
                return $this->request($method, $endpoint, $data, $useAuth);
            }
        }

        return $result;
    }

    /**
     * Login de usuario trabajador
     */
    public function loginUsuario($username, $password) {
        $result = $this->request('POST', '/auth/login', [
            'username' => $username,
            'password' => $password
        ]);

        if (isset($result['status']) && $result['status'] === 'success') {
            $this->accessToken = $result['data']['access_token'];
            $this->refreshToken = $result['data']['refresh_token'];

            // Guardar en sesión
            $_SESSION['roel_api_access_token'] = $this->accessToken;
            $_SESSION['roel_api_refresh_token'] = $this->refreshToken;
            $_SESSION['roel_api_user'] = $result['data']['user'];
            $_SESSION['roel_api_user_type'] = 'usuario';
        }

        return $result;
    }

    /**
     * Login de cliente
     */
    public function loginCliente($email, $password) {
        $result = $this->request('POST', '/cliente/login', [
            'email' => $email,
            'password' => $password
        ]);

        if (isset($result['status']) && $result['status'] === 'success') {
            $this->accessToken = $result['data']['access_token'];
            $this->refreshToken = $result['data']['refresh_token'];

            // Guardar en sesión
            $_SESSION['roel_api_access_token'] = $this->accessToken;
            $_SESSION['roel_api_refresh_token'] = $this->refreshToken;
            $_SESSION['roel_api_cliente'] = $result['data']['cliente'];
            $_SESSION['roel_api_user_type'] = 'cliente';
        }

        return $result;
    }

    /**
     * Registro de usuario trabajador
     */
    public function registerUsuario($username, $nombreReal, $password, $permisos = []) {
        return $this->request('POST', '/auth/register', [
            'username' => $username,
            'nombre_real' => $nombreReal,
            'password' => $password,
            'permisos' => $permisos
        ]);
    }

    /**
     * Registro de cliente
     */
    public function registerCliente($data) {
        return $this->request('POST', '/cliente/register', $data);
    }

    /**
     * Obtener información del usuario autenticado
     */
    public function getMe() {
        $userType = isset($_SESSION['roel_api_user_type']) ? $_SESSION['roel_api_user_type'] : 'usuario';
        $endpoint = $userType === 'cliente' ? '/cliente/me' : '/auth/me';
        return $this->request('GET', $endpoint, null, true);
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($currentPassword, $newPassword) {
        $userType = isset($_SESSION['roel_api_user_type']) ? $_SESSION['roel_api_user_type'] : 'usuario';
        $endpoint = $userType === 'cliente' ? '/cliente/change-password' : '/auth/change-password';

        return $this->request('POST', $endpoint, [
            'current_password' => $currentPassword,
            'new_password' => $newPassword
        ], true);
    }

    /**
     * Refrescar token de acceso
     */
    public function refresh() {
        if (!$this->refreshToken) {
            return false;
        }

        $userType = isset($_SESSION['roel_api_user_type']) ? $_SESSION['roel_api_user_type'] : 'usuario';
        $endpoint = $userType === 'cliente' ? '/cliente/refresh' : '/auth/refresh';

        // Temporalmente usar el refresh token como access token
        $oldToken = $this->accessToken;
        $this->accessToken = $this->refreshToken;

        $result = $this->request('POST', $endpoint, null, true);

        if (isset($result['status']) && $result['status'] === 'success') {
            $this->accessToken = $result['data']['access_token'];
            $_SESSION['roel_api_access_token'] = $this->accessToken;
            return true;
        }

        $this->accessToken = $oldToken;
        return false;
    }

    /**
     * Logout
     */
    public function logout() {
        $userType = isset($_SESSION['roel_api_user_type']) ? $_SESSION['roel_api_user_type'] : 'usuario';
        $endpoint = $userType === 'cliente' ? '/cliente/logout' : '/auth/logout';

        $result = $this->request('POST', $endpoint, null, true);

        // Limpiar sesión
        unset($_SESSION['roel_api_access_token']);
        unset($_SESSION['roel_api_refresh_token']);
        unset($_SESSION['roel_api_user']);
        unset($_SESSION['roel_api_cliente']);
        unset($_SESSION['roel_api_user_type']);

        $this->accessToken = null;
        $this->refreshToken = null;

        return $result;
    }

    /**
     * Validar si el token actual es válido
     */
    public function validateToken() {
        $userType = isset($_SESSION['roel_api_user_type']) ? $_SESSION['roel_api_user_type'] : 'usuario';
        $endpoint = $userType === 'cliente' ? '/cliente/validate' : '/auth/validate';

        $result = $this->request('GET', $endpoint, null, true);

        return isset($result['data']['valid']) && $result['data']['valid'];
    }

    /**
     * Verificar si hay una sesión activa
     */
    public function isAuthenticated() {
        return $this->accessToken !== null;
    }

    /**
     * Obtener información del usuario/cliente desde la sesión
     */
    public function getUser() {
        if (isset($_SESSION['roel_api_user'])) {
            return $_SESSION['roel_api_user'];
        }
        if (isset($_SESSION['roel_api_cliente'])) {
            return $_SESSION['roel_api_cliente'];
        }
        return null;
    }

    /**
     * Obtener tipo de usuario (usuario o cliente)
     */
    public function getUserType() {
        return isset($_SESSION['roel_api_user_type']) ? $_SESSION['roel_api_user_type'] : null;
    }
}


// ==================================================
// EJEMPLOS DE USO
// ==================================================

/*

// 1. Inicializar la API
session_start();
$api = new RoelERPApi('http://tu-dominio.com/api');

// 2. Login de usuario trabajador
$result = $api->loginUsuario('usuario123', 'contraseña123');

if ($result['status'] === 'success') {
    echo "Login exitoso";
    $user = $result['data']['user'];
    echo "Bienvenido " . $user['nombre_real'];
} else {
    echo "Error: " . $result['message'];
}

// 3. Login de cliente
$result = $api->loginCliente('cliente@ejemplo.com', 'contraseña123');

// 4. Obtener información del usuario autenticado
if ($api->isAuthenticated()) {
    $userInfo = $api->getMe();
    print_r($userInfo);
}

// 5. Cambiar contraseña
$result = $api->changePassword('contraseña_actual', 'nueva_contraseña');

// 6. Validar token
if ($api->validateToken()) {
    echo "Token válido";
} else {
    echo "Token inválido o expirado";
}

// 7. Logout
$api->logout();

// 8. Obtener usuario de la sesión
$user = $api->getUser();
if ($user) {
    echo "Usuario: " . $user['nombre_real'];
}

// 9. Verificar tipo de usuario
$userType = $api->getUserType();
if ($userType === 'usuario') {
    echo "Es un trabajador";
} elseif ($userType === 'cliente') {
    echo "Es un cliente";
}

// 10. Registrar nuevo usuario trabajador
$result = $api->registerUsuario(
    'nuevousuario',
    'María González',
    'contraseña123',
    ['cotizaciones', 'stock']
);

// 11. Registrar nuevo cliente
$result = $api->registerCliente([
    'email' => 'nuevocliente@ejemplo.com',
    'nombre' => 'Empresa XYZ',
    'password' => 'contraseña123',
    'telefono' => '987654321',
    'rut' => '98765432-1'
]);

*/
