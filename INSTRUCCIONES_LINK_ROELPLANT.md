# Instrucciones para Implementar Link Roelplant

## Resumen
Esta funcionalidad permite linkear productos/servicios del inventario con variedades de productos del vivero Roelplant. El link se realiza mediante un selectpicker con búsqueda dinámica que filtra las opciones cuando el usuario escribe al menos 3 letras.

## Pasos de Instalación

### 1. Ejecutar el Script SQL

Primero, debes ejecutar el script SQL para agregar el campo `link_roelplant` a las tablas `productos` y `servicios`:

```bash
mysql -u usuario -p nombre_base_datos < /home/alan/Documents/roel/roelerp/data/add_link_roelplant.sql
```

O desde el cliente MySQL:

```sql
source /home/alan/Documents/roel/roelerp/data/add_link_roelplant.sql;
```

Este script:
- Agrega la columna `link_roelplant` a la tabla `productos`
- Agrega la columna `link_roelplant` a la tabla `servicios`
- Crea las claves foráneas correspondientes hacia `variedades_producto`

### 2. Archivos Modificados

Los siguientes archivos fueron modificados para implementar esta funcionalidad:

#### Backend (PHP):
- **`modals/inventario/pos.php`**: Se agregó el selectpicker para buscar y seleccionar la variedad
- **`data_ver_inventario.php`**:
  - Nuevos endpoints: `buscar_variedades_roelplant` y `get_variedad_roelplant`
  - Modificadas funciones: `agregar_producto`, `editar_producto`, `busca_productos`

#### Frontend (JavaScript):
- **`dist/js/ver_inventario.js`**:
  - Modificada función `modalProducto()` para cargar el link si existe
  - Modificada función `guardarProducto()` para enviar el link_roelplant
  - Nuevas funciones: `initBusquedaRoelplant()`, `buscarVariedadesRoelplant()`, `cargarVariedadRoelplant()`

### 3. Cómo Funciona

1. **Al abrir el modal de producto/servicio**: Se muestra un nuevo campo "Linkeado con Vivero Roelplant"

2. **Búsqueda dinámica**:
   - El usuario escribe al menos 3 letras en el campo de búsqueda
   - Después de 500ms (debounce), se realiza una búsqueda automática
   - Los resultados se muestran como: `TIPO VARIEDAD (CODIGO)`
   - Ejemplo: `TOMATE PERITA INDETERMINADO ELPIDA (TOM01)`

3. **Guardar**:
   - El usuario puede seleccionar una variedad o dejar el campo como "Sin linkear"
   - Al guardar, se actualiza el campo `link_roelplant` en la base de datos

4. **Editar**:
   - Si un producto/servicio ya tiene un link, se carga automáticamente en el selectpicker

### 4. Estructura de la Base de Datos

La tabla `variedades_producto` tiene la siguiente estructura:
- `id`: ID único
- `nombre`: Nombre de la variedad
- `id_tipo`: FK hacia `tipos_producto`
- `id_interno`: Código interno de la variedad
- `precio`: Precio
- `dias_produccion`: Días en producción
- `eliminada`: Flag de eliminación lógica

El link se realiza mediante:
- `productos.link_roelplant` → `variedades_producto.id`
- `servicios.link_roelplant` → `variedades_producto.id`

### 5. Notas Importantes

- La búsqueda es **case-insensitive** (no distingue mayúsculas/minúsculas)
- Se limitan los resultados a **50 opciones** para optimizar el rendimiento
- La búsqueda filtra por:
  - Nombre de la variedad
  - Nombre del tipo de producto
  - Concatenación de ambos
- El link es **opcional**: el usuario puede elegir "Sin linkear"
- Si se elimina una variedad que tiene productos/servicios linkeados, el campo se establece en NULL (ON DELETE SET NULL)

### 6. Posibles Mejoras Futuras

- Agregar una columna en la tabla de productos/servicios que muestre el link
- Permitir desvincular directamente desde la tabla sin abrir el modal
- Agregar más información de la variedad en el tooltip
- Implementar sincronización automática de precios desde la variedad linkeada

## Soporte

Si encuentras algún problema durante la instalación o uso de esta funcionalidad, revisa:
1. Que el script SQL se haya ejecutado correctamente
2. Que los archivos JavaScript estén correctamente cacheados (limpia el cache del navegador)
3. Los logs de error del servidor para identificar problemas de backend