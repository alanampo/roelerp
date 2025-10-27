# API de Gestión de Clientes - Documentación

## Base URL
```
http://tu-dominio.com/api
```

## Autenticación

Todos los endpoints de clientes requieren autenticación con JWT. Incluye el token en el header:

```
Authorization: Bearer <tu-token-aqui>
```

---

## Endpoints

### 1. Listar Todos los Clientes

Obtiene una lista de todos los clientes.

**Endpoint:** `GET /api/clientes`

**Autenticación:** Requiere usuario trabajador

**Query Parameters:**
- `order_by` (opcional): Campo por el cual ordenar. Valores: `nombre`, `id_cliente`, `rut`, `mail`. Default: `nombre`

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "clientes": [
      {
        "id_cliente": 1,
        "nombre": "EMPRESA ABC",
        "domicilio": "CALLE PRINCIPAL 123",
        "domicilio2": "DEPTO 456",
        "telefono": "123456789",
        "mail": "contacto@empresaabc.com",
        "rut": "12345678-9",
        "comuna": 1,
        "razon_social": "EMPRESA ABC SPA",
        "region": "METROPOLITANA",
        "provincia": "SANTIAGO",
        "id_vendedor": 5,
        "vendedor_anterior": null,
        "fecha_cambio_vendedor": null,
        "vendedor_nombre": "Juan Pérez"
      }
    ]
  }
}
```

---

### 2. Obtener Cliente por ID

Obtiene los datos de un cliente específico por su ID.

**Endpoint:** `GET /api/clientes/{id}`

**Autenticación:** Requiere usuario trabajador

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "cliente": {
      "id_cliente": 1,
      "nombre": "EMPRESA ABC",
      "domicilio": "CALLE PRINCIPAL 123",
      "domicilio2": "DEPTO 456",
      "telefono": "123456789",
      "mail": "contacto@empresaabc.com",
      "rut": "12345678-9",
      "comuna": 1,
      "razon_social": "EMPRESA ABC SPA",
      "region": "METROPOLITANA",
      "provincia": "SANTIAGO",
      "id_vendedor": 5,
      "vendedor_anterior": null,
      "fecha_cambio_vendedor": null,
      "vendedor_nombre": "Juan Pérez"
    }
  }
}
```

---

### 3. Obtener Cliente por ID de Usuario

Obtiene los datos de un cliente asociado a un usuario específico.

**Endpoint:** `GET /api/clientes/usuario/{id_usuario}`

**Autenticación:** Requiere autenticación (trabajador o el mismo cliente)

**Notas:**
- Si el usuario autenticado es un cliente, solo puede ver su propio perfil
- Los trabajadores pueden ver cualquier cliente

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "cliente": {
      "id_cliente": 1,
      "nombre": "EMPRESA ABC",
      ...
    }
  }
}
```

---

### 4. Crear Cliente

Crea un nuevo cliente.

**Endpoint:** `POST /api/clientes`

**Autenticación:** Requiere usuario trabajador

**Body:**
```json
{
  "nombre": "Empresa XYZ",
  "domicilio": "Av. Libertador 456",
  "domicilio2": "Oficina 789",
  "telefono": "987654321",
  "mail": "info@empresaxyz.com",
  "rut": "98765432-1",
  "comuna": 2,
  "razon_social": "Empresa XYZ Ltda",
  "region": "Valparaíso",
  "provincia": "Valparaíso",
  "id_vendedor": 3
}
```

**Campos requeridos:**
- `nombre` (string)

**Campos opcionales:**
- `domicilio` (string)
- `domicilio2` (string)
- `telefono` (string)
- `mail` (string)
- `rut` (string) - Debe ser único
- `comuna` (int)
- `razon_social` (string)
- `region` (string)
- `provincia` (string)
- `id_vendedor` (int) - ID del vendedor asignado

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Cliente creado exitosamente",
  "data": {
    "cliente": {
      "id_cliente": 15,
      "nombre": "EMPRESA XYZ",
      ...
    }
  }
}
```

---

### 5. Actualizar Cliente

Actualiza los datos de un cliente existente.

**Endpoint:** `PUT /api/clientes/{id}`

**Autenticación:** Requiere usuario trabajador

**Body:**
```json
{
  "nombre": "Empresa XYZ Actualizada",
  "telefono": "111222333",
  "mail": "nuevo@empresaxyz.com"
}
```

**Notas:**
- Solo se envían los campos que se desean actualizar
- El `id_vendedor` NO se actualiza mediante este endpoint (usar el endpoint de cambiar vendedor)
- El RUT debe ser único si se actualiza

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Cliente actualizado exitosamente",
  "data": {
    "cliente": {
      "id_cliente": 15,
      "nombre": "EMPRESA XYZ ACTUALIZADA",
      ...
    }
  }
}
```

---

### 6. Actualizar Cliente por ID de Usuario

Actualiza los datos de un cliente asociado a un usuario.

**Endpoint:** `PUT /api/clientes/usuario/{id_usuario}`

**Autenticación:** Requiere autenticación (trabajador o el mismo cliente)

**Notas:**
- Si el usuario autenticado es un cliente, solo puede editar su propio perfil
- Los trabajadores pueden editar cualquier cliente

**Body:**
```json
{
  "telefono": "999888777",
  "mail": "actualizado@empresa.com"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Cliente actualizado exitosamente",
  "data": {
    "cliente": { ... }
  }
}
```

---

### 7. Eliminar Cliente

Elimina un cliente del sistema.

**Endpoint:** `DELETE /api/clientes/{id}`

**Autenticación:** Requiere usuario trabajador

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Cliente eliminado exitosamente"
}
```

---

### 8. Cambiar Vendedor de Cliente

Cambia el vendedor asignado a un cliente y registra el cambio en el historial.

**Endpoint:** `POST /api/clientes/{id}/cambiar-vendedor`

**Autenticación:** Requiere usuario trabajador

**Body:**
```json
{
  "id_vendedor_nuevo": 7,
  "justificacion": "Cliente solicita cambio de vendedor por mejor atención"
}
```

**Campos requeridos:**
- `id_vendedor_nuevo` (int) - Puede ser `null` para desasignar vendedor

**Campos opcionales:**
- `justificacion` (string) - Requerida solo si había vendedor anterior (mínimo 3 caracteres)

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Vendedor cambiado exitosamente",
  "data": {
    "cliente": {
      "id_cliente": 1,
      "id_vendedor": 7,
      "vendedor_anterior": 5,
      "fecha_cambio_vendedor": "2025-10-27 14:30:00",
      "vendedor_nombre": "María González",
      ...
    }
  }
}
```

---

### 9. Obtener Historial de Vendedor

Obtiene el historial completo de cambios de vendedor de un cliente.

**Endpoint:** `GET /api/clientes/{id}/historial-vendedor`

**Autenticación:** Requiere usuario trabajador

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "historial": [
      {
        "id": 3,
        "fecha": "2025-10-27 14:30:00",
        "vendedor_anterior": "Juan Pérez",
        "vendedor_nuevo": "María González",
        "usuario_cambio": "Sergio Villarroel",
        "justificacion": "Cliente solicita cambio de vendedor por mejor atención"
      },
      {
        "id": 2,
        "fecha": "2025-09-15 10:00:00",
        "vendedor_anterior": "Sin asignar",
        "vendedor_nuevo": "Juan Pérez",
        "usuario_cambio": "Sergio Villarroel",
        "justificacion": "Asignación inicial"
      }
    ]
  }
}
```

---

### 10. Listar Vendedores Disponibles

Obtiene la lista de vendedores (usuarios trabajadores activos) disponibles para asignar.

**Endpoint:** `GET /api/clientes/vendedores`

**Autenticación:** Requiere usuario trabajador

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "vendedores": [
      {
        "id": 5,
        "nombre_real": "Juan Pérez",
        "nombre": "jperez"
      },
      {
        "id": 7,
        "nombre_real": "María González",
        "nombre": "mgonzalez"
      }
    ]
  }
}
```

---

### 11. Listar Comunas Disponibles

Obtiene la lista de comunas disponibles en el sistema.

**Endpoint:** `GET /api/clientes/comunas`

**Autenticación:** Requiere usuario trabajador

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "data": {
    "comunas": [
      {
        "id": 1,
        "nombre": "Santiago",
        "ciudad": "Santiago"
      },
      {
        "id": 2,
        "nombre": "Valparaíso",
        "ciudad": "Valparaíso"
      }
    ]
  }
}
```

---

## Ejemplos de Uso con cURL

### Listar clientes
```bash
TOKEN="tu-access-token"

curl http://localhost:8888/api/clientes \
  -H "Authorization: Bearer $TOKEN"
```

### Crear cliente
```bash
curl -X POST http://localhost:8888/api/clientes \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Nueva Empresa",
    "rut": "11111111-1",
    "mail": "contacto@nuevaempresa.com",
    "telefono": "555-1234",
    "id_vendedor": 5
  }'
```

### Actualizar cliente
```bash
curl -X PUT http://localhost:8888/api/clientes/15 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "telefono": "555-9999",
    "mail": "nuevo@empresa.com"
  }'
```

### Cambiar vendedor
```bash
curl -X POST http://localhost:8888/api/clientes/15/cambiar-vendedor \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "id_vendedor_nuevo": 7,
    "justificacion": "Cliente solicita cambio por mejor atención"
  }'
```

### Eliminar cliente
```bash
curl -X DELETE http://localhost:8888/api/clientes/15 \
  -H "Authorization: Bearer $TOKEN"
```

---

## Códigos de Error

- `400` - Petición incorrecta (datos inválidos)
- `401` - No autorizado (token inválido o expirado)
- `403` - Prohibido (sin permisos para esta acción)
- `404` - Cliente no encontrado
- `422` - Error de validación
- `500` - Error interno del servidor

---

## Notas Importantes

1. **RUT Único**: El campo `rut` debe ser único en la base de datos
2. **Vendedor**: No se actualiza mediante PUT, usar endpoint específico `/cambiar-vendedor`
3. **Mayúsculas**: Los campos de texto se guardan en mayúsculas automáticamente (excepto email)
4. **Email**: Se guarda en minúsculas automáticamente
5. **Permisos**: Solo usuarios trabajadores pueden crear, editar y eliminar clientes
6. **Clientes**: Pueden ver y editar solo su propio perfil (vía `/usuario/{id}`)
