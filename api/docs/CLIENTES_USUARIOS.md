# Asociación de Clientes y Usuarios

## Casos de Uso

La API soporta tres casos de asociación entre clientes y usuarios:

### 1. Cliente sin Usuario
Cliente que solo existe como entidad comercial, sin capacidad de login.

**Ejemplo:**
```bash
curl -X POST http://localhost:8888/api/clientes \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Empresa XYZ",
    "rut": "12345678-9",
    "telefono": "555-1234"
  }'
```

### 2. Cliente con Usuario Tipo Cliente (tipo_usuario = 0)
Cliente que puede hacer login al sistema.

**Ejemplo:**
```bash
curl -X POST http://localhost:8888/api/clientes/with-usuario \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Empresa ABC",
    "rut": "98765432-1",
    "email": "contacto@empresaabc.com",
    "password": "contraseña123",
    "telefono": "555-9999"
  }'
```

### 3. Trabajador Asociado a Cliente
Un trabajador (tipo_usuario = 1) que también tiene su propia empresa cliente. Por ejemplo, el admin que es dueño de una empresa cliente.

**Ejemplo:**
```bash
# Asociar el usuario admin (id=1) al cliente (id=15)
curl -X POST http://localhost:8888/api/clientes/15/asociar-usuario \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id_usuario": 1
  }'
```

---

## Endpoints de Asociación

### 1. Crear Cliente CON Usuario

Crea un cliente y automáticamente crea un usuario tipo cliente (tipo_usuario = 0) asociado.

**Endpoint:** `POST /api/clientes/with-usuario`

**Autenticación:** Requiere usuario trabajador

**Body:**
```json
{
  "nombre": "Empresa Nueva",
  "rut": "11111111-1",
  "email": "contacto@empresanueva.com",
  "password": "contraseña123",
  "telefono": "555-1234",
  "domicilio": "Calle Principal 123",
  "razon_social": "Empresa Nueva SpA",
  "id_vendedor": 5
}
```

**Campos requeridos:**
- `nombre` (string) - Nombre del cliente
- `email` (string) - Email para el usuario
- `password` (string) - Contraseña del usuario (mínimo 6 caracteres)

**Campos opcionales:** (mismos que crear cliente normal)

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Cliente creado con usuario asociado exitosamente",
  "data": {
    "cliente": {
      "id_cliente": 25,
      "nombre": "EMPRESA NUEVA",
      "mail": "contacto@empresanueva.com",
      ...
    },
    "id_usuario": 45,
    "usuario_creado": true
  }
}
```

**Notas:**
- El email se guarda tanto en el cliente como en el usuario
- Se crea un usuario tipo_usuario = 0 (cliente)
- El password se hashea con bcrypt
- El email debe ser único (no puede existir otro usuario con ese email)

---

### 2. Asociar Usuario Existente a Cliente

Vincula un usuario existente (de cualquier tipo) a un cliente existente.

**Endpoint:** `POST /api/clientes/{id}/asociar-usuario`

**Autenticación:** Requiere usuario trabajador

**Body:**
```json
{
  "id_usuario": 1
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Usuario asociado al cliente exitosamente",
  "data": {
    "cliente": {
      "id_cliente": 15,
      "nombre": "EMPRESA ABC",
      ...
    },
    "usuario": {
      "id": 1,
      "nombre": "admin",
      "tipo_usuario": 1
    }
  }
}
```

**Validaciones:**
- El usuario no puede estar asociado a otro cliente
- Si ya está asociado al mismo cliente, no da error (idempotente)
- Puede asociar tanto usuarios tipo 0 (clientes) como tipo 1 (trabajadores)

**Casos de uso:**
- Asociar un trabajador a su propia empresa cliente
- Reasignar un usuario cliente a un cliente diferente (primero desasociar)

---

### 3. Desasociar Usuario de Cliente

Quita la asociación entre un usuario y su cliente.

**Endpoint:** `POST /api/clientes/desasociar-usuario/{id_usuario}`

**Autenticación:** Requiere usuario trabajador

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Usuario desasociado del cliente exitosamente"
}
```

**Notas:**
- El usuario queda sin cliente asociado (id_cliente = NULL)
- El usuario sigue existiendo y puede seguir haciendo login
- Útil para cuando un trabajador deja de estar asociado a una empresa

---

### 4. Obtener Usuario Asociado a Cliente

Verifica si un cliente tiene un usuario asociado y obtiene sus datos.

**Endpoint:** `GET /api/clientes/{id}/usuario-asociado`

**Autenticación:** Requiere usuario trabajador

**Respuesta si tiene usuario (200):**
```json
{
  "status": "success",
  "data": {
    "usuario": {
      "id": 45,
      "nombre": "contacto@empresaabc.com",
      "tipo_usuario": 0,
      "inhabilitado": 0
    },
    "tiene_usuario": true
  }
}
```

**Respuesta si NO tiene usuario (200):**
```json
{
  "status": "success",
  "message": "El cliente no tiene usuario asociado",
  "data": {
    "usuario": null,
    "tiene_usuario": false
  }
}
```

---

## Ejemplos Completos

### Caso 1: Crear Cliente con Login

```bash
TOKEN="tu-token-aqui"

# Crear cliente con usuario
curl -X POST http://localhost:8888/api/clientes/with-usuario \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Mi Empresa",
    "rut": "12345678-9",
    "email": "contacto@miempresa.com",
    "password": "MiPassword123",
    "telefono": "555-1234"
  }'

# Ahora el cliente puede hacer login
curl -X POST http://localhost:8888/api/cliente/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "contacto@miempresa.com",
    "password": "MiPassword123"
  }'
```

### Caso 2: Asociar Admin a su Empresa

```bash
# Primero crear el cliente de la empresa del admin
curl -X POST http://localhost:8888/api/clientes \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Roel Plantas",
    "rut": "76543210-9",
    "telefono": "555-0000"
  }'

# Respuesta: id_cliente = 100

# Asociar el admin (id=1) a ese cliente
curl -X POST http://localhost:8888/api/clientes/100/asociar-usuario \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id_usuario": 1
  }'

# Ahora el admin puede ver/editar ese cliente por su id_usuario
curl http://localhost:8888/api/clientes/usuario/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Caso 3: Verificar si Cliente Tiene Usuario

```bash
# Verificar si el cliente 100 tiene usuario asociado
curl http://localhost:8888/api/clientes/100/usuario-asociado \
  -H "Authorization: Bearer $TOKEN"
```

---

## Flujo Recomendado

### Para Clientes que NECESITAN Login:

```
1. POST /api/clientes/with-usuario
   ↓
2. Cliente creado + Usuario tipo 0 creado
   ↓
3. Cliente puede hacer login con email/password
   ↓
4. GET /api/cliente/me (como cliente autenticado)
```

### Para Trabajadores con Empresa:

```
1. POST /api/clientes (crear empresa)
   ↓
2. POST /api/clientes/{id}/asociar-usuario
   ↓
3. Trabajador vinculado a su empresa
   ↓
4. GET /api/clientes/usuario/{id_usuario}
```

---

## Errores Comunes

### "Ya existe un usuario con ese email"
```json
{
  "status": "error",
  "message": "Ya existe un usuario con ese email"
}
```
**Solución:** Usa un email diferente o asocia el usuario existente.

### "El usuario ya está asociado a otro cliente"
```json
{
  "status": "error",
  "message": "El usuario ya está asociado a otro cliente"
}
```
**Solución:** Primero desasocia el usuario del cliente anterior.

### "El usuario no tiene ningún cliente asociado"
```json
{
  "status": "error",
  "message": "El usuario no tiene ningún cliente asociado"
}
```
**Solución:** El usuario ya no tiene cliente, no hay nada que desasociar.

---

## Tabla de Resumen

| Tipo de Usuario | tipo_usuario | id_cliente | Puede Ver Clientes | Puede Editar Clientes |
|-----------------|--------------|------------|-------------------|----------------------|
| Trabajador sin cliente | 1 | NULL | Todos | Todos |
| Trabajador con cliente | 1 | 100 | Todos + el suyo | Todos + el suyo |
| Cliente con login | 0 | 100 | Solo el suyo | Solo el suyo |
| Cliente sin login | - | - | No aplica | No aplica |

---

## Diagrama de Relaciones

```
usuarios (id=1, tipo_usuario=1, id_cliente=100)  ──┐
usuarios (id=45, tipo_usuario=0, id_cliente=100) ──┼── clientes (id_cliente=100)
usuarios (id=50, tipo_usuario=0, id_cliente=NULL) ─┘
```

En este ejemplo:
- Cliente 100 tiene DOS usuarios asociados:
  - Usuario 1 (trabajador/admin)
  - Usuario 45 (cliente)
- Usuario 50 es un trabajador sin cliente asociado
