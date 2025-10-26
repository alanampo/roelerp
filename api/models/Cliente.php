<?php
/**
 * Modelo de Cliente
 * Maneja operaciones relacionadas con clientes que tendrán acceso al sistema
 * Los clientes se autentican con email en lugar de username
 */

require_once __DIR__ . '/../config/database.php';

class Cliente {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Busca un cliente por email
     */
    public function findByEmail($email) {
        $query = "
            SELECT
                id_cliente as id,
                nombre,
                mail as email,
                telefono,
                rut,
                domicilio,
                comuna,
                region,
                razon_social,
                password_hash,
                activo
            FROM clientes
            WHERE mail = ?
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    /**
     * Busca un cliente por ID
     */
    public function findById($id, $includePassword = false) {
        $passwordField = $includePassword ? 'password_hash,' : '';

        $query = "
            SELECT
                id_cliente as id,
                nombre,
                mail as email,
                $passwordField
                telefono,
                rut,
                domicilio,
                comuna,
                region,
                razon_social,
                activo
            FROM clientes
            WHERE id_cliente = ?
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
     * Verifica la contraseña del cliente
     */
    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Crea un nuevo cliente con capacidad de login
     */
    public function create($email, $nombre, $password, $telefono = '', $rut = '', $domicilio = '', $comuna = '', $region = '', $razonSocial = '') {
        try {
            // Verificar si el cliente ya existe
            if ($this->findByEmail($email)) {
                return ['success' => false, 'error' => 'El cliente ya existe con ese email'];
            }

            // Hashear password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insertar cliente
            $query = "
                INSERT INTO clientes (nombre, mail, password_hash, telefono, rut, domicilio, comuna, region, razon_social, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
            ";

            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param(
                $stmt,
                'sssssssss',
                $nombre,
                $email,
                $hashedPassword,
                $telefono,
                $rut,
                $domicilio,
                $comuna,
                $region,
                $razonSocial
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al crear cliente: " . mysqli_error($this->conn));
            }

            $clienteId = mysqli_insert_id($this->conn);

            return [
                'success' => true,
                'cliente_id' => $clienteId,
                'cliente' => $this->findById($clienteId)
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Actualiza la contraseña de un cliente
     */
    public function updatePassword($clienteId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $query = "UPDATE clientes SET password_hash = ? WHERE id_cliente = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'si', $hashedPassword, $clienteId);

        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Verifica si un cliente está activo
     */
    public function isActive($clienteId) {
        $query = "SELECT activo FROM clientes WHERE id_cliente = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $clienteId);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['activo'] == 1;
        }

        return false; // Si no existe, considerarlo inactivo
    }
}
