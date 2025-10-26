-- =====================================================
-- Migración: Agregar campos necesarios para la API
-- Fecha: 2025-10-26
-- Descripción: Agrega campos para autenticación con JWT
-- =====================================================

-- 1. Agregar columnas a la tabla clientes para permitir autenticación
-- =====================================================

-- Verificar si la columna password_hash ya existe antes de agregarla
ALTER TABLE clientes
ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) DEFAULT NULL COMMENT 'Password hasheado con bcrypt para autenticación';

-- Agregar columna activo para controlar acceso de clientes
ALTER TABLE clientes
ADD COLUMN IF NOT EXISTS activo TINYINT(1) DEFAULT 1 COMMENT '1 = activo, 0 = inactivo';

-- Agregar índice en email para búsquedas más rápidas
ALTER TABLE clientes
ADD INDEX IF NOT EXISTS idx_mail (mail);


-- 2. Notas importantes sobre la tabla usuarios
-- =====================================================
-- La tabla usuarios ya tiene las columnas necesarias:
-- - id
-- - nombre
-- - nombre_real
-- - password (se migrará gradualmente a bcrypt)
-- - tipo_usuario (1 = trabajador)
-- - inhabilitado (0 = activo, 1 = inhabilitado)
-- - iniciales

-- 3. Script de migración de passwords (OPCIONAL - ejecutar después)
-- =====================================================
-- Este script migrará gradualmente los passwords en texto plano a bcrypt
-- Se recomienda ejecutarlo en un script PHP separado

-- NOTA: Los usuarios con passwords en texto plano seguirán funcionando
-- gracias a la lógica de verifyPassword() en el modelo Usuario
-- que detecta automáticamente si es texto plano o hash


-- 4. Verificación de índices en tabla usuarios
-- =====================================================

-- Agregar índice en nombre para búsquedas más rápidas
ALTER TABLE usuarios
ADD INDEX IF NOT EXISTS idx_nombre (nombre);

-- Agregar índice en tipo_usuario
ALTER TABLE usuarios
ADD INDEX IF NOT EXISTS idx_tipo_usuario (tipo_usuario);


-- 5. Crear tabla para refresh tokens (OPCIONAL - para producción)
-- =====================================================
-- Esta tabla se puede usar para implementar blacklist de tokens
-- o para revocar tokens específicos

CREATE TABLE IF NOT EXISTS auth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('usuario', 'cliente') NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    type ENUM('access', 'refresh') NOT NULL,
    expires_at DATETIME NOT NULL,
    revoked TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token_hash (token_hash),
    INDEX idx_user (user_id, user_type),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabla para tracking de tokens JWT';


-- =====================================================
-- Datos de prueba (OPCIONAL)
-- =====================================================

-- Para testing, puedes crear un cliente de prueba con password hasheado
-- Password: 123456
-- Hash bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- INSERT INTO clientes (nombre, mail, password_hash, telefono, activo)
-- VALUES (
--     'Cliente Prueba',
--     'cliente@test.com',
--     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
--     '123456789',
--     1
-- );

-- =====================================================
-- Fin de migración
-- =====================================================
