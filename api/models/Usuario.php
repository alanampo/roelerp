<?php
/**
 * Modelo de Usuario (Trabajadores)
 * Maneja operaciones relacionadas con usuarios tipo trabajador (tipo_usuario = 1)
 */

require_once __DIR__ . '/../config/database.php';

class Usuario {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Busca un usuario por nombre de usuario
     * NOTA: Acepta tanto tipo_usuario = 0 (clientes) como tipo_usuario = 1 (trabajadores)
     */
    public function findByUsername($username) {
        $username = mysqli_real_escape_string($this->conn, $username);

        $query = "
            SELECT
                u.id,
                u.nombre,
                u.nombre_real,
                u.password,
                u.tipo_usuario,
                u.inhabilitado,
                u.iniciales,
                u.id_cliente,
                GROUP_CONCAT(p.modulo SEPARATOR ',') as modulos
            FROM usuarios u
            LEFT JOIN permisos p ON p.id_usuario = u.id
            WHERE u.nombre = ?
            GROUP BY u.id, u.nombre, u.nombre_real, u.password, u.tipo_usuario, u.inhabilitado, u.iniciales, u.id_cliente
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    /**
     * Busca un usuario por ID
     */
    public function findById($id, $includePassword = false) {
        $passwordField = $includePassword ? 'u.password,' : '';
        $passwordGroup = $includePassword ? 'u.password,' : '';

        $query = "
            SELECT
                u.id,
                u.nombre,
                u.nombre_real,
                $passwordField
                u.tipo_usuario,
                u.inhabilitado,
                u.iniciales,
                GROUP_CONCAT(p.modulo SEPARATOR ',') as modulos
            FROM usuarios u
            LEFT JOIN permisos p ON p.id_usuario = u.id
            WHERE u.id = ? AND u.tipo_usuario = 1
            GROUP BY u.id, u.nombre, u.nombre_real, $passwordGroup u.tipo_usuario, u.inhabilitado, u.iniciales
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    /**
     * Verifica la contraseña del usuario
     */
    public function verifyPassword($plainPassword, $hashedPassword) {
        // Si la contraseña almacenada no está hasheada (legacy), hacer comparación directa
        if (strlen($hashedPassword) < 60) {
            return $plainPassword === $hashedPassword;
        }

        // Si está hasheada, usar password_verify
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Crea un nuevo usuario
     */
    public function create($username, $nombreReal, $password, $permisos = []) {
        mysqli_autocommit($this->conn, false);

        try {
            // Verificar si el usuario ya existe
            if ($this->findByUsername($username)) {
                return ['success' => false, 'error' => 'El usuario ya existe'];
            }

            // Hashear password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Generar iniciales
            $inicial = strtoupper(substr($username, 0, 1));

            // Verificar si las iniciales ya existen
            $checkQuery = "SELECT id FROM usuarios WHERE iniciales = ?";
            $stmt = mysqli_prepare($this->conn, $checkQuery);
            mysqli_stmt_bind_param($stmt, 's', $inicial);
            mysqli_stmt_execute($stmt);
            $checkResult = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($checkResult) > 0) {
                $inicial = strtoupper(substr($username, 0, 2));
            }

            // Insertar usuario
            $insertQuery = "
                INSERT INTO usuarios (nombre, nombre_real, password, tipo_usuario, iniciales, inhabilitado)
                VALUES (LOWER(?), ?, ?, 1, ?, 0)
            ";

            $stmt = mysqli_prepare($this->conn, $insertQuery);
            mysqli_stmt_bind_param($stmt, 'ssss', $username, $nombreReal, $hashedPassword, $inicial);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al crear usuario: " . mysqli_error($this->conn));
            }

            $userId = mysqli_insert_id($this->conn);

            // Insertar permisos
            if (!empty($permisos)) {
                $permisosQuery = "INSERT INTO permisos (id_usuario, modulo) VALUES (?, ?)";
                $permisosStmt = mysqli_prepare($this->conn, $permisosQuery);

                foreach ($permisos as $modulo) {
                    mysqli_stmt_bind_param($permisosStmt, 'is', $userId, $modulo);
                    if (!mysqli_stmt_execute($permisosStmt)) {
                        throw new Exception("Error al asignar permisos: " . mysqli_error($this->conn));
                    }
                }
            }

            mysqli_commit($this->conn);

            return [
                'success' => true,
                'user_id' => $userId,
                'user' => $this->findById($userId)
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualiza la contraseña de un usuario
     */
    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $query = "UPDATE usuarios SET password = ? WHERE id = ? AND tipo_usuario = 1";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $userId);

        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Verifica si un usuario está inhabilitado
     */
    public function isDisabled($userId) {
        $query = "SELECT inhabilitado FROM usuarios WHERE id = ? AND tipo_usuario = 1";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['inhabilitado'] == 1;
        }

        return true; // Si no existe, considerarlo inhabilitado
    }
}
