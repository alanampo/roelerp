# Guía de Inicio Rápido - Roel ERP API

## Paso 1: Ejecutar Migración de Base de Datos

```bash
cd /home/alan/Documents/roel/roelerp
mysql -u TU_USUARIO -p TU_BASE_DE_DATOS < api/migrations/001_add_auth_fields.sql
```

## Paso 2: Cambiar Clave Secreta JWT

Edita `api/config/jwt.php` y cambia la clave secreta:

```php
'secret_key' => 'TU_CLAVE_SECRETA_MUY_SEGURA_AQUI_12345'
```

## Paso 3: Verificar que .htaccess funcione

```bash
# Verificar que mod_rewrite esté habilitado
apache2ctl -M | grep rewrite

# Si no está habilitado:
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Paso 4: Probar la API

### Usando cURL:

```bash
# Ver información de la API
curl http://localhost/roel/roelerp/api/

# Login de usuario
curl -X POST http://localhost/roel/roelerp/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"TU_USUARIO","password":"TU_PASSWORD"}'
```

### Respuesta esperada:

```json
{
  "status": "success",
  "message": "Login exitoso",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 900,
    "user": { ... }
  }
}
```

## Paso 5: Migrar Passwords (Opcional)

Para migrar passwords existentes de texto plano a bcrypt:

```bash
php api/migrations/migrate_passwords.php
```

**IMPORTANTE:** Haz backup de la base de datos antes de ejecutar este script.

## Paso 6: Integración en tu Sistema PHP Existente

### Opción A: Usar el Helper PHP

```php
<?php
session_start();
require_once 'api/examples/php_integration.php';

$api = new RoelERPApi('http://localhost/roel/roelerp/api');

// Login
$result = $api->loginUsuario('usuario', 'password');

if ($result['status'] === 'success') {
    // Usuario autenticado
    $user = $api->getUser();
    echo "Bienvenido " . $user['nombre_real'];
}
?>
```

### Opción B: Usar desde JavaScript

```html
<script src="api/examples/javascript_integration.js"></script>
<script>
const api = new RoelERPApiClient('http://localhost/roel/roelerp/api');

async function login() {
    const result = await api.loginUsuario('usuario', 'password');
    if (result.status === 'success') {
        window.location.href = 'inicio.php';
    }
}
</script>
```

## Endpoints Principales

### Usuarios Trabajadores:

- `POST /api/auth/login` - Login
- `POST /api/auth/register` - Registro
- `GET /api/auth/me` - Info del usuario (requiere token)
- `POST /api/auth/change-password` - Cambiar password (requiere token)
- `POST /api/auth/refresh` - Refrescar token
- `POST /api/auth/logout` - Logout (requiere token)

### Clientes:

- `POST /api/cliente/login` - Login
- `POST /api/cliente/register` - Registro
- `GET /api/cliente/me` - Info del cliente (requiere token)
- `POST /api/cliente/change-password` - Cambiar password (requiere token)
- `POST /api/cliente/refresh` - Refrescar token
- `POST /api/cliente/logout` - Logout (requiere token)

## Estructura de Respuestas

### Éxito:
```json
{
  "status": "success",
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

### Error:
```json
{
  "status": "error",
  "message": "Mensaje de error",
  "details": { ... }
}
```

## Testing Rápido

### 1. Login de trabajador:

```bash
curl -X POST http://localhost/roel/roelerp/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"123456"}'
```

### 2. Guardar el token recibido y usarlo:

```bash
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

curl http://localhost/roel/roelerp/api/auth/me \
  -H "Authorization: Bearer $TOKEN"
```

### 3. Registrar un cliente:

```bash
curl -X POST http://localhost/roel/roelerp/api/cliente/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "cliente@test.com",
    "nombre": "Cliente Prueba",
    "password": "123456",
    "telefono": "123456789"
  }'
```

## Solución de Problemas

### Error 404 en todos los endpoints

- Verifica que `.htaccess` esté en `api/.htaccess`
- Verifica que `mod_rewrite` esté habilitado
- Verifica que `AllowOverride All` esté configurado en Apache

### Error de conexión a base de datos

- Verifica las credenciales en `class_lib/class_conecta_mysql.php`
- Verifica que la migración se haya ejecutado correctamente

### Token inválido o expirado

- Los access tokens expiran en 15 minutos
- Usa el refresh token para obtener uno nuevo
- Verifica que la clave secreta JWT sea la misma

### CORS errors desde frontend

- Verifica `api/middleware/CorsMiddleware.php`
- Agrega tu dominio a la whitelist si es necesario

## Próximos Pasos

1. Lee la documentación completa en `api/README.md`
2. Mira los ejemplos en `api/examples/`
3. Configura la seguridad en producción (HTTPS, claves, CORS)
4. Implementa rate limiting si es necesario
5. Agrega logging de auditoría

## Soporte

Para más información, consulta:
- `api/README.md` - Documentación completa
- `api/examples/` - Ejemplos de integración
