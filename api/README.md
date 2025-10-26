# Roel ERP API - Documentación

API REST para autenticación y gestión de usuarios y clientes en Roel ERP.

## Características

- Autenticación con JWT (JSON Web Tokens)
- Soporte para dos tipos de usuarios:
  - **Trabajadores/Usuarios**: Personal interno con permisos modulares
  - **Clientes**: Usuarios externos con acceso limitado
- Tokens de acceso de corta duración (15 minutos)
- Refresh tokens de larga duración (30 días)
- Passwords hasheados con bcrypt
- Consultas preparadas (prevención de SQL injection)
- CORS habilitado
- Respuestas JSON estandarizadas

## Instalación

### 1. Requisitos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache con mod_rewrite habilitado

### 2. Configuración

1. La API está ubicada en el directorio `/api` de tu proyecto
2. Asegúrate de que Apache tenga permisos de lectura en el directorio
3. Verifica que el archivo `.htaccess` esté funcionando correctamente

### 3. Migración de Base de Datos

Ejecuta el script de migración para agregar los campos necesarios:

```bash
mysql -u usuario -p nombre_bd < api/migrations/001_add_auth_fields.sql
```

### 4. Migración de Passwords (Opcional)

Para migrar los passwords existentes de texto plano a bcrypt:

```bash
php api/migrations/migrate_passwords.php
```

O desde el navegador:
```
http://tu-dominio.com/api/migrations/migrate_passwords.php?secret=TU_CLAVE_SECRETA
```

**IMPORTANTE**: Cambia la clave secreta en el archivo `migrate_passwords.php` antes de ejecutar.

### 5. Configuración de Seguridad

**IMPORTANTE - CAMBIAR EN PRODUCCIÓN:**

Edita el archivo `api/config/jwt.php` y cambia la clave secreta:

```php
'secret_key' => 'TU_CLAVE_SECRETA_SUPER_SEGURA_CAMBIAR_EN_PRODUCCION_...'
```

## Uso de la API

### URL Base

```
http://tu-dominio.com/api
```

### Autenticación

La mayoría de los endpoints requieren autenticación con JWT. Incluye el token en el header `Authorization`:

```
Authorization: Bearer <tu-token-aqui>
```

---

## Endpoints - Usuarios Trabajadores

### 1. Login de Trabajador

Autentica un usuario trabajador y obtiene tokens de acceso.

**Endpoint:** `POST /api/auth/login`

**Body:**
```json
{
  "username": "usuario123",
  "password": "contraseña123"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Login exitoso",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 900,
    "user": {
      "id": 1,
      "username": "usuario123",
      "nombre_real": "Juan Pérez",
      "iniciales": "JP",
      "permisos": ["cotizaciones", "facturacion", "stock"]
    }
  }
}
```

**Errores posibles:**
- `401` - Credenciales inválidas
- `403` - Usuario inhabilitado

---

### 2. Registro de Trabajador

Crea un nuevo usuario trabajador.

**Endpoint:** `POST /api/auth/register`

**Body:**
```json
{
  "username": "nuevousuario",
  "nombre_real": "María González",
  "password": "contraseña123",
  "permisos": ["cotizaciones", "stock"]
}
```

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Usuario creado exitosamente",
  "data": {
    "user": {
      "id": 5,
      "username": "nuevousuario",
      "nombre_real": "María González",
      "iniciales": "MG",
      "permisos": ["cotizaciones", "stock"]
    }
  }
}
```

---

### 3. Cambiar Contraseña (Trabajador)

Cambia la contraseña del usuario autenticado.

**Endpoint:** `POST /api/auth/change-password`

**Headers:** `Authorization: Bearer <token>`

**Body:**
```json
{
  "current_password": "contraseña_actual",
  "new_password": "nueva_contraseña"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Contraseña actualizada exitosamente"
}
```

---

### 4. Obtener Información del Usuario

Obtiene información del usuario trabajador autenticado.

**Endpoint:** `GET /api/auth/me`

**Headers:** `Authorization: Bearer <token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "id": 1,
    "username": "usuario123",
    "nombre_real": "Juan Pérez",
    "iniciales": "JP",
    "permisos": ["cotizaciones", "facturacion", "stock"]
  }
}
```

---

### 5. Refrescar Token (Trabajador)

Obtiene un nuevo access token usando el refresh token.

**Endpoint:** `POST /api/auth/refresh`

**Headers:** `Authorization: Bearer <refresh-token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Token refrescado exitosamente",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 900
  }
}
```

---

### 6. Logout (Trabajador)

Cierra sesión del usuario.

**Endpoint:** `POST /api/auth/logout`

**Headers:** `Authorization: Bearer <token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Logout exitoso"
}
```

---

### 7. Validar Token (Trabajador)

Verifica si un token es válido.

**Endpoint:** `GET /api/auth/validate`

**Headers:** `Authorization: Bearer <token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Token válido",
  "data": {
    "valid": true,
    "user_id": 1,
    "user_type": "usuario"
  }
}
```

---

## Endpoints - Clientes

### 1. Login de Cliente

Autentica un cliente y obtiene tokens de acceso.

**Endpoint:** `POST /api/cliente/login`

**Body:**
```json
{
  "email": "cliente@ejemplo.com",
  "password": "contraseña123"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Login exitoso",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 900,
    "cliente": {
      "id": 10,
      "nombre": "Empresa ABC",
      "email": "cliente@ejemplo.com",
      "telefono": "123456789",
      "rut": "12345678-9",
      "razon_social": "ABC SpA"
    }
  }
}
```

**Errores posibles:**
- `401` - Credenciales inválidas
- `403` - Cliente inactivo o sin contraseña configurada

---

### 2. Registro de Cliente

Crea un nuevo cliente con acceso al sistema.

**Endpoint:** `POST /api/cliente/register`

**Body:**
```json
{
  "email": "nuevocliente@ejemplo.com",
  "nombre": "Empresa XYZ",
  "password": "contraseña123",
  "telefono": "987654321",
  "rut": "98765432-1",
  "domicilio": "Calle Principal 123",
  "comuna": "Santiago",
  "region": "Metropolitana",
  "razon_social": "XYZ Ltda"
}
```

**Campos requeridos:**
- `email` (válido)
- `nombre`
- `password` (mínimo 6 caracteres)

**Campos opcionales:**
- `telefono`
- `rut`
- `domicilio`
- `comuna`
- `region`
- `razon_social`

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Cliente registrado exitosamente",
  "data": {
    "cliente": {
      "id": 15,
      "nombre": "Empresa XYZ",
      "email": "nuevocliente@ejemplo.com",
      "telefono": "987654321",
      "rut": "98765432-1",
      "razon_social": "XYZ Ltda"
    }
  }
}
```

---

### 3. Cambiar Contraseña (Cliente)

Cambia la contraseña del cliente autenticado.

**Endpoint:** `POST /api/cliente/change-password`

**Headers:** `Authorization: Bearer <token>`

**Body:**
```json
{
  "current_password": "contraseña_actual",
  "new_password": "nueva_contraseña"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Contraseña actualizada exitosamente"
}
```

---

### 4. Obtener Información del Cliente

Obtiene información del cliente autenticado.

**Endpoint:** `GET /api/cliente/me`

**Headers:** `Authorization: Bearer <token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "id": 10,
    "nombre": "Empresa ABC",
    "email": "cliente@ejemplo.com",
    "telefono": "123456789",
    "rut": "12345678-9",
    "domicilio": "Calle Principal 123",
    "comuna": "Santiago",
    "region": "Metropolitana",
    "razon_social": "ABC SpA"
  }
}
```

---

### 5. Refrescar Token (Cliente)

Obtiene un nuevo access token usando el refresh token.

**Endpoint:** `POST /api/cliente/refresh`

**Headers:** `Authorization: Bearer <refresh-token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Token refrescado exitosamente",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 900
  }
}
```

---

### 6. Logout (Cliente)

Cierra sesión del cliente.

**Endpoint:** `POST /api/cliente/logout`

**Headers:** `Authorization: Bearer <token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Logout exitoso"
}
```

---

### 7. Validar Token (Cliente)

Verifica si un token de cliente es válido.

**Endpoint:** `GET /api/cliente/validate`

**Headers:** `Authorization: Bearer <token>`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Token válido",
  "data": {
    "valid": true,
    "user_id": 10,
    "user_type": "cliente"
  }
}
```

---

## Manejo de Errores

Todos los errores siguen el mismo formato:

```json
{
  "status": "error",
  "message": "Descripción del error",
  "details": {
    "campo": "mensaje de error específico"
  }
}
```

### Códigos de Estado HTTP

- `200` - Éxito
- `201` - Recurso creado
- `400` - Petición incorrecta
- `401` - No autorizado (credenciales inválidas o token expirado)
- `403` - Prohibido (acceso denegado)
- `404` - Recurso no encontrado
- `422` - Error de validación
- `500` - Error interno del servidor

---

## Integración con PHP

### Ejemplo de Login

```php
<?php
function loginApi($username, $password) {
    $url = 'http://tu-dominio.com/api/auth/login';

    $data = json_encode([
        'username' => $username,
        'password' => $password
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return json_decode($response, true);
}

// Uso
$result = loginApi('usuario123', 'contraseña123');

if ($result['status'] === 'success') {
    $accessToken = $result['data']['access_token'];
    $refreshToken = $result['data']['refresh_token'];

    // Guardar tokens en sesión
    $_SESSION['access_token'] = $accessToken;
    $_SESSION['refresh_token'] = $refreshToken;
    $_SESSION['user'] = $result['data']['user'];
}
?>
```

### Ejemplo de Petición Autenticada

```php
<?php
function getUserInfo($token) {
    $url = 'http://tu-dominio.com/api/auth/me';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Uso
$userInfo = getUserInfo($_SESSION['access_token']);
?>
```

### Ejemplo de Refresh Token

```php
<?php
function refreshToken($refreshToken) {
    $url = 'http://tu-dominio.com/api/auth/refresh';

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $refreshToken
    ]);

    $response = curl_exec($ch);
    $result = json_decode($response, true);

    if ($result['status'] === 'success') {
        $_SESSION['access_token'] = $result['data']['access_token'];
    }

    return $result;
}
?>
```

---

## Seguridad

### Buenas Prácticas

1. **Cambiar la clave secreta JWT**: Edita `api/config/jwt.php` con una clave única
2. **HTTPS en producción**: Siempre usa HTTPS para proteger los tokens
3. **Almacenar tokens de forma segura**: No los almacenes en localStorage si puedes evitarlo
4. **Implementar rate limiting**: Limita intentos de login
5. **Blacklist de tokens**: Implementa una blacklist para tokens revocados (tabla `auth_tokens`)
6. **Logs de seguridad**: Registra intentos fallidos de autenticación
7. **Expiración corta de access tokens**: Los tokens de acceso expiran en 15 minutos

### Configuración CORS

Por defecto, la API acepta peticiones de cualquier origen. En producción, edita `api/middleware/CorsMiddleware.php` para restringir los orígenes permitidos:

```php
// En lugar de permitir cualquier origen:
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");

// Usa una whitelist:
$allowed_origins = [
    'https://tu-dominio.com',
    'https://app.tu-dominio.com'
];

if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
```

---

## Testing

### Con cURL

```bash
# Login
curl -X POST http://tu-dominio.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"usuario123","password":"contraseña123"}'

# Obtener info del usuario
curl -X GET http://tu-dominio.com/api/auth/me \
  -H "Authorization: Bearer <tu-token>"
```

### Con Postman/Insomnia

Importa el archivo `api/docs/api-collection.json` (incluido) en Postman o Insomnia para tener todos los endpoints preconfigurados.

---

## Soporte

Para reportar bugs o solicitar funcionalidades, contacta al equipo de desarrollo.

## Licencia

Uso interno de Roel ERP.
