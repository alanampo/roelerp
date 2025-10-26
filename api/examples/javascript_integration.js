/**
 * Helper de integración JavaScript para usar la API desde el frontend
 *
 * Uso:
 * <script src="api/examples/javascript_integration.js"></script>
 * const api = new RoelERPApiClient('http://tu-dominio.com/api');
 */

class RoelERPApiClient {
    constructor(baseUrl) {
        this.baseUrl = baseUrl.replace(/\/$/, '');
        this.accessToken = localStorage.getItem('roel_api_access_token');
        this.refreshToken = localStorage.getItem('roel_api_refresh_token');
        this.userType = localStorage.getItem('roel_api_user_type');
    }

    /**
     * Realiza una petición HTTP a la API
     */
    async request(method, endpoint, data = null, useAuth = false, isRetry = false) {
        const url = this.baseUrl + endpoint;

        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (useAuth && this.accessToken) {
            options.headers['Authorization'] = `Bearer ${this.accessToken}`;
        }

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            // Si el token expiró, intentar refrescar
            if (response.status === 401 && useAuth && !isRetry && this.refreshToken) {
                const refreshed = await this.refresh();
                if (refreshed) {
                    // Reintentar la petición con el nuevo token
                    return await this.request(method, endpoint, data, useAuth, true);
                }
            }

            return result;

        } catch (error) {
            console.error('Error en petición API:', error);
            return {
                status: 'error',
                message: 'Error de conexión con el servidor'
            };
        }
    }

    /**
     * Login de usuario trabajador
     */
    async loginUsuario(username, password) {
        const result = await this.request('POST', '/auth/login', {
            username: username,
            password: password
        });

        if (result.status === 'success') {
            this.accessToken = result.data.access_token;
            this.refreshToken = result.data.refresh_token;
            this.userType = 'usuario';

            // Guardar en localStorage
            localStorage.setItem('roel_api_access_token', this.accessToken);
            localStorage.setItem('roel_api_refresh_token', this.refreshToken);
            localStorage.setItem('roel_api_user', JSON.stringify(result.data.user));
            localStorage.setItem('roel_api_user_type', 'usuario');
        }

        return result;
    }

    /**
     * Login de cliente
     */
    async loginCliente(email, password) {
        const result = await this.request('POST', '/cliente/login', {
            email: email,
            password: password
        });

        if (result.status === 'success') {
            this.accessToken = result.data.access_token;
            this.refreshToken = result.data.refresh_token;
            this.userType = 'cliente';

            // Guardar en localStorage
            localStorage.setItem('roel_api_access_token', this.accessToken);
            localStorage.setItem('roel_api_refresh_token', this.refreshToken);
            localStorage.setItem('roel_api_cliente', JSON.stringify(result.data.cliente));
            localStorage.setItem('roel_api_user_type', 'cliente');
        }

        return result;
    }

    /**
     * Registro de usuario trabajador
     */
    async registerUsuario(username, nombreReal, password, permisos = []) {
        return await this.request('POST', '/auth/register', {
            username: username,
            nombre_real: nombreReal,
            password: password,
            permisos: permisos
        });
    }

    /**
     * Registro de cliente
     */
    async registerCliente(data) {
        return await this.request('POST', '/cliente/register', data);
    }

    /**
     * Obtener información del usuario autenticado
     */
    async getMe() {
        const endpoint = this.userType === 'cliente' ? '/cliente/me' : '/auth/me';
        return await this.request('GET', endpoint, null, true);
    }

    /**
     * Cambiar contraseña
     */
    async changePassword(currentPassword, newPassword) {
        const endpoint = this.userType === 'cliente' ? '/cliente/change-password' : '/auth/change-password';
        return await this.request('POST', endpoint, {
            current_password: currentPassword,
            new_password: newPassword
        }, true);
    }

    /**
     * Refrescar token de acceso
     */
    async refresh() {
        if (!this.refreshToken) {
            return false;
        }

        const endpoint = this.userType === 'cliente' ? '/cliente/refresh' : '/auth/refresh';

        // Temporalmente usar el refresh token
        const oldToken = this.accessToken;
        this.accessToken = this.refreshToken;

        const result = await this.request('POST', endpoint, null, true);

        if (result.status === 'success') {
            this.accessToken = result.data.access_token;
            localStorage.setItem('roel_api_access_token', this.accessToken);
            return true;
        }

        this.accessToken = oldToken;
        return false;
    }

    /**
     * Logout
     */
    async logout() {
        const endpoint = this.userType === 'cliente' ? '/cliente/logout' : '/auth/logout';
        const result = await this.request('POST', endpoint, null, true);

        // Limpiar localStorage
        localStorage.removeItem('roel_api_access_token');
        localStorage.removeItem('roel_api_refresh_token');
        localStorage.removeItem('roel_api_user');
        localStorage.removeItem('roel_api_cliente');
        localStorage.removeItem('roel_api_user_type');

        this.accessToken = null;
        this.refreshToken = null;
        this.userType = null;

        return result;
    }

    /**
     * Validar token
     */
    async validateToken() {
        const endpoint = this.userType === 'cliente' ? '/cliente/validate' : '/auth/validate';
        const result = await this.request('GET', endpoint, null, true);
        return result.data && result.data.valid;
    }

    /**
     * Verificar si hay una sesión activa
     */
    isAuthenticated() {
        return this.accessToken !== null;
    }

    /**
     * Obtener información del usuario desde localStorage
     */
    getUser() {
        const userJson = localStorage.getItem('roel_api_user');
        const clienteJson = localStorage.getItem('roel_api_cliente');

        if (userJson) {
            return JSON.parse(userJson);
        }
        if (clienteJson) {
            return JSON.parse(clienteJson);
        }
        return null;
    }

    /**
     * Obtener tipo de usuario
     */
    getUserType() {
        return this.userType;
    }
}


// ==================================================
// EJEMPLOS DE USO
// ==================================================

/*

// 1. Inicializar la API
const api = new RoelERPApiClient('http://tu-dominio.com/api');

// 2. Login de usuario trabajador
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const result = await api.loginUsuario(username, password);

    if (result.status === 'success') {
        alert('Login exitoso');
        window.location.href = 'inicio.php';
    } else {
        alert('Error: ' + result.message);
    }
});

// 3. Login de cliente
async function loginCliente() {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const result = await api.loginCliente(email, password);

    if (result.status === 'success') {
        console.log('Cliente:', result.data.cliente);
        window.location.href = 'portal_cliente.php';
    }
}

// 4. Obtener información del usuario autenticado
if (api.isAuthenticated()) {
    const userInfo = await api.getMe();
    console.log('Usuario:', userInfo);
}

// 5. Cambiar contraseña
async function cambiarPassword() {
    const result = await api.changePassword('contraseña_actual', 'nueva_contraseña');

    if (result.status === 'success') {
        alert('Contraseña actualizada');
    }
}

// 6. Validar token
async function verificarSesion() {
    if (await api.validateToken()) {
        console.log('Token válido');
    } else {
        console.log('Token inválido, redirigir a login');
        window.location.href = 'login.php';
    }
}

// 7. Logout
async function cerrarSesion() {
    await api.logout();
    window.location.href = 'login.php';
}

// 8. Obtener usuario de localStorage
const user = api.getUser();
if (user) {
    document.getElementById('userName').textContent = user.nombre_real || user.nombre;
}

// 9. Verificar tipo de usuario
const userType = api.getUserType();
if (userType === 'usuario') {
    console.log('Es un trabajador');
    // Mostrar menú de trabajadores
} else if (userType === 'cliente') {
    console.log('Es un cliente');
    // Mostrar menú de clientes
}

// 10. Registrar nuevo usuario trabajador
async function registrarUsuario() {
    const result = await api.registerUsuario(
        'nuevousuario',
        'María González',
        'contraseña123',
        ['cotizaciones', 'stock']
    );

    if (result.status === 'success') {
        alert('Usuario registrado exitosamente');
    }
}

// 11. Registrar nuevo cliente
async function registrarCliente() {
    const result = await api.registerCliente({
        email: 'nuevocliente@ejemplo.com',
        nombre: 'Empresa XYZ',
        password: 'contraseña123',
        telefono: '987654321',
        rut: '98765432-1'
    });

    if (result.status === 'success') {
        alert('Cliente registrado exitosamente');
    }
}

// 12. Verificar autenticación al cargar página protegida
window.addEventListener('DOMContentLoaded', async () => {
    if (!api.isAuthenticated()) {
        window.location.href = 'login.php';
        return;
    }

    // Validar que el token siga siendo válido
    if (!await api.validateToken()) {
        alert('Sesión expirada');
        window.location.href = 'login.php';
    }
});

// 13. Interceptar fetch para agregar token automáticamente (avanzado)
const originalFetch = window.fetch;
window.fetch = async function(url, options = {}) {
    // Si es una petición a la API y hay token
    if (url.includes('/api/') && api.isAuthenticated()) {
        options.headers = options.headers || {};
        options.headers['Authorization'] = `Bearer ${api.accessToken}`;
    }

    return originalFetch(url, options);
};

*/
