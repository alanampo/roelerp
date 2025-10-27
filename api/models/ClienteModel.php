<?php
/**
 * Modelo de Cliente para CRUD de la tabla clientes
 * NO confundir con Cliente.php que es para autenticación de clientes
 */

require_once __DIR__ . '/../config/database.php';

class ClienteModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Obtiene todos los clientes
     */
    public function getAll($orderBy = 'nombre') {
        $validOrders = ['nombre', 'id_cliente', 'rut', 'mail'];
        $orderBy = in_array($orderBy, $validOrders) ? $orderBy : 'nombre';

        $query = "
            SELECT
                c.id_cliente,
                c.nombre,
                c.domicilio,
                c.domicilio2,
                c.telefono,
                c.mail,
                c.rut,
                c.comuna,
                c.razon_social,
                c.region,
                c.provincia,
                c.id_vendedor,
                c.vendedor_anterior,
                c.fecha_cambio_vendedor,
                u.nombre_real as vendedor_nombre
            FROM clientes c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id
            ORDER BY c.$orderBy
        ";

        $result = mysqli_query($this->conn, $query);
        $clientes = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $clientes[] = $row;
            }
        }

        return $clientes;
    }

    /**
     * Busca un cliente por ID
     */
    public function findById($id_cliente) {
        $query = "
            SELECT
                c.id_cliente,
                c.nombre,
                c.domicilio,
                c.domicilio2,
                c.telefono,
                c.mail,
                c.rut,
                c.comuna,
                c.razon_social,
                c.region,
                c.provincia,
                c.id_vendedor,
                c.vendedor_anterior,
                c.fecha_cambio_vendedor,
                u.nombre_real as vendedor_nombre
            FROM clientes c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id
            WHERE c.id_cliente = ?
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    /**
     * Busca un cliente por ID de usuario
     */
    public function findByUsuarioId($id_usuario) {
        // Primero obtener el id_cliente del usuario
        $query = "SELECT id_cliente FROM usuarios WHERE id = ? AND tipo_usuario = 0";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            $usuario = mysqli_fetch_assoc($result);
            if ($usuario['id_cliente']) {
                return $this->findById($usuario['id_cliente']);
            }
        }

        return null;
    }

    /**
     * Busca un cliente por RUT
     */
    public function findByRut($rut) {
        $query = "
            SELECT
                c.id_cliente,
                c.nombre,
                c.domicilio,
                c.domicilio2,
                c.telefono,
                c.mail,
                c.rut,
                c.comuna,
                c.razon_social,
                c.region,
                c.provincia,
                c.id_vendedor,
                c.vendedor_anterior,
                c.fecha_cambio_vendedor,
                u.nombre_real as vendedor_nombre
            FROM clientes c
            LEFT JOIN usuarios u ON c.id_vendedor = u.id
            WHERE c.rut = ?
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $rut);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }

    /**
     * Crea un nuevo cliente
     */
    public function create($data) {
        // Validar RUT único
        if (!empty($data['rut'])) {
            $existing = $this->findByRut($data['rut']);
            if ($existing) {
                return ['success' => false, 'error' => 'Ya existe un cliente con ese RUT'];
            }
        }

        $nombre = $data['nombre'] ?? '';
        $domicilio = $data['domicilio'] ?? null;
        $domicilio2 = $data['domicilio2'] ?? null;
        $telefono = $data['telefono'] ?? null;
        $mail = $data['mail'] ?? null;
        $rut = $data['rut'] ?? null;
        $comuna = $data['comuna'] ?? null;
        $razon_social = $data['razon_social'] ?? null;
        $region = $data['region'] ?? null;
        $provincia = $data['provincia'] ?? null;
        $id_vendedor = $data['id_vendedor'] ?? null;

        // Convertir strings vacíos a null
        if (empty(trim($domicilio))) $domicilio = null;
        if (empty(trim($domicilio2))) $domicilio2 = null;
        if (empty(trim($telefono))) $telefono = null;
        if (empty(trim($mail))) $mail = null;
        if (empty(trim($rut))) $rut = null;
        if (empty(trim($razon_social))) $razon_social = null;
        if (empty(trim($region))) $region = null;
        if (empty(trim($provincia))) $provincia = null;
        if (empty($id_vendedor) || $id_vendedor == 'default') $id_vendedor = null;

        $query = "
            INSERT INTO clientes (
                nombre, domicilio, domicilio2, telefono, mail, rut,
                comuna, razon_social, region, provincia, id_vendedor
            ) VALUES (
                UPPER(?), UPPER(?), UPPER(?), ?, LOWER(?), UPPER(?),
                ?, UPPER(?), UPPER(?), UPPER(?), ?
            )
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssisssi',
            $nombre, $domicilio, $domicilio2, $telefono, $mail, $rut,
            $comuna, $razon_social, $region, $provincia, $id_vendedor
        );

        if (mysqli_stmt_execute($stmt)) {
            $id_cliente = mysqli_insert_id($this->conn);
            return [
                'success' => true,
                'id_cliente' => $id_cliente,
                'cliente' => $this->findById($id_cliente)
            ];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Actualiza un cliente
     */
    public function update($id_cliente, $data) {
        // Verificar que el cliente existe
        $cliente = $this->findById($id_cliente);
        if (!$cliente) {
            return ['success' => false, 'error' => 'Cliente no encontrado'];
        }

        // Validar RUT único (excepto el mismo cliente)
        if (!empty($data['rut'])) {
            $query = "SELECT id_cliente FROM clientes WHERE rut = ? AND id_cliente != ?";
            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'si', $data['rut'], $id_cliente);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) > 0) {
                return ['success' => false, 'error' => 'Ya existe un cliente con ese RUT'];
            }
        }

        $nombre = $data['nombre'] ?? $cliente['nombre'];
        $domicilio = $data['domicilio'] ?? $cliente['domicilio'];
        $domicilio2 = $data['domicilio2'] ?? $cliente['domicilio2'];
        $telefono = $data['telefono'] ?? $cliente['telefono'];
        $mail = $data['mail'] ?? $cliente['mail'];
        $rut = $data['rut'] ?? $cliente['rut'];
        $comuna = $data['comuna'] ?? $cliente['comuna'];
        $razon_social = $data['razon_social'] ?? $cliente['razon_social'];
        $region = $data['region'] ?? $cliente['region'];
        $provincia = $data['provincia'] ?? $cliente['provincia'];

        // Convertir strings vacíos a null
        if (empty(trim($domicilio))) $domicilio = null;
        if (empty(trim($domicilio2))) $domicilio2 = null;
        if (empty(trim($telefono))) $telefono = null;
        if (empty(trim($mail))) $mail = null;
        if (empty(trim($rut))) $rut = null;
        if (empty(trim($razon_social))) $razon_social = null;
        if (empty(trim($region))) $region = null;
        if (empty(trim($provincia))) $provincia = null;

        // NO se actualiza id_vendedor aquí (se usa endpoint separado)
        $query = "
            UPDATE clientes SET
                nombre = UPPER(?),
                domicilio = UPPER(?),
                domicilio2 = UPPER(?),
                telefono = ?,
                mail = LOWER(?),
                rut = UPPER(?),
                comuna = ?,
                razon_social = UPPER(?),
                region = UPPER(?),
                provincia = UPPER(?)
            WHERE id_cliente = ?
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssisssi',
            $nombre, $domicilio, $domicilio2, $telefono, $mail, $rut,
            $comuna, $razon_social, $region, $provincia, $id_cliente
        );

        if (mysqli_stmt_execute($stmt)) {
            return [
                'success' => true,
                'cliente' => $this->findById($id_cliente)
            ];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Elimina un cliente
     */
    public function delete($id_cliente) {
        $query = "DELETE FROM clientes WHERE id_cliente = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_cliente);

        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Cambia el vendedor de un cliente (con historial)
     */
    public function cambiarVendedor($id_cliente, $id_vendedor_nuevo, $id_usuario_cambio, $justificacion = '') {
        mysqli_autocommit($this->conn, false);

        try {
            // Obtener vendedor anterior
            $cliente = $this->findById($id_cliente);
            if (!$cliente) {
                throw new Exception('Cliente no encontrado');
            }

            $id_vendedor_anterior = $cliente['id_vendedor'];

            // Validar justificación si había vendedor anterior
            if ($id_vendedor_anterior !== null && strlen(trim($justificacion)) < 3) {
                throw new Exception('La justificación debe tener al menos 3 caracteres');
            }

            // Convertir valores vacíos a null
            if (empty($id_vendedor_nuevo) || $id_vendedor_nuevo == 'default') {
                $id_vendedor_nuevo = null;
            }

            // Actualizar cliente con nuevo vendedor
            $query = "
                UPDATE clientes SET
                    id_vendedor = ?,
                    vendedor_anterior = ?,
                    fecha_cambio_vendedor = NOW()
                WHERE id_cliente = ?
            ";

            $stmt = mysqli_prepare($this->conn, $query);
            mysqli_stmt_bind_param($stmt, 'iii', $id_vendedor_nuevo, $id_vendedor_anterior, $id_cliente);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_error($this->conn));
            }

            // Registrar en historial
            $query_historial = "
                INSERT INTO historial_cambios_vendedor
                (id_cliente, id_vendedor_anterior, id_vendedor_nuevo, id_usuario_cambio, justificacion, fecha_cambio)
                VALUES (?, ?, ?, ?, ?, NOW())
            ";

            $stmt = mysqli_prepare($this->conn, $query_historial);
            mysqli_stmt_bind_param($stmt, 'iiiis', $id_cliente, $id_vendedor_anterior, $id_vendedor_nuevo, $id_usuario_cambio, $justificacion);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_error($this->conn));
            }

            mysqli_commit($this->conn);

            return [
                'success' => true,
                'cliente' => $this->findById($id_cliente)
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Obtiene el historial de cambios de vendedor de un cliente
     */
    public function getHistorialVendedor($id_cliente) {
        $query = "
            SELECT
                h.id,
                h.fecha_cambio,
                u_anterior.nombre_real as vendedor_anterior,
                u_nuevo.nombre_real as vendedor_nuevo,
                u_cambio.nombre_real as usuario_cambio,
                h.justificacion
            FROM historial_cambios_vendedor h
            LEFT JOIN usuarios u_anterior ON h.id_vendedor_anterior = u_anterior.id
            LEFT JOIN usuarios u_nuevo ON h.id_vendedor_nuevo = u_nuevo.id
            LEFT JOIN usuarios u_cambio ON h.id_usuario_cambio = u_cambio.id
            WHERE h.id_cliente = ?
            ORDER BY h.fecha_cambio DESC
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $historial = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $historial[] = [
                    'id' => $row['id'],
                    'fecha' => $row['fecha_cambio'],
                    'vendedor_anterior' => $row['vendedor_anterior'] ?? 'Sin asignar',
                    'vendedor_nuevo' => $row['vendedor_nuevo'] ?? 'Sin asignar',
                    'usuario_cambio' => $row['usuario_cambio'],
                    'justificacion' => $row['justificacion']
                ];
            }
        }

        return $historial;
    }

    /**
     * Obtiene todos los vendedores (usuarios tipo 1 activos)
     */
    public function getVendedores() {
        $query = "
            SELECT id, nombre_real, nombre
            FROM usuarios
            WHERE tipo_usuario = 1 AND inhabilitado != 1
            ORDER BY nombre_real
        ";

        $result = mysqli_query($this->conn, $query);
        $vendedores = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $vendedores[] = $row;
            }
        }

        return $vendedores;
    }

    /**
     * Obtiene todas las comunas
     */
    public function getComunas() {
        $query = "SELECT * FROM comunas ORDER BY nombre";
        $result = mysqli_query($this->conn, $query);
        $comunas = [];

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $comunas[] = $row;
            }
        }

        return $comunas;
    }

    /**
     * Crea un cliente CON usuario asociado
     * Útil para crear clientes que puedan hacer login
     */
    public function createWithUsuario($dataCliente, $dataUsuario) {
        mysqli_autocommit($this->conn, false);

        try {
            // 1. Crear el cliente primero
            $resultCliente = $this->create($dataCliente);

            if (!$resultCliente['success']) {
                throw new Exception($resultCliente['error']);
            }

            $id_cliente = $resultCliente['id_cliente'];

            // 2. Validar que el email no exista como usuario
            $email = $dataUsuario['email'] ?? $dataCliente['mail'];
            $password = $dataUsuario['password'] ?? null;

            if (empty($email)) {
                throw new Exception('Email requerido para crear usuario');
            }

            if (empty($password)) {
                throw new Exception('Contraseña requerida para crear usuario');
            }

            // Verificar que el email no exista
            $checkQuery = "SELECT id FROM usuarios WHERE nombre = ?";
            $stmt = mysqli_prepare($this->conn, $checkQuery);
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $checkResult = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($checkResult) > 0) {
                throw new Exception('Ya existe un usuario con ese email');
            }

            // 3. Crear usuario tipo cliente (tipo_usuario = 0)
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $tipo_usuario = 0; // Cliente

            $queryUsuario = "
                INSERT INTO usuarios (nombre, password, id_cliente, tipo_usuario, inhabilitado)
                VALUES (LOWER(?), ?, ?, ?, 0)
            ";

            $stmt = mysqli_prepare($this->conn, $queryUsuario);
            mysqli_stmt_bind_param($stmt, 'ssii', $email, $password_hash, $id_cliente, $tipo_usuario);

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error al crear usuario: " . mysqli_error($this->conn));
            }

            $id_usuario = mysqli_insert_id($this->conn);

            mysqli_commit($this->conn);

            return [
                'success' => true,
                'id_cliente' => $id_cliente,
                'id_usuario' => $id_usuario,
                'cliente' => $this->findById($id_cliente),
                'usuario_creado' => true
            ];

        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return ['success' => false, 'error' => $e->getMessage()];
        } finally {
            mysqli_autocommit($this->conn, true);
        }
    }

    /**
     * Asocia un cliente existente a un usuario existente
     * Útil para vincular un trabajador a su propia empresa cliente
     */
    public function asociarUsuario($id_cliente, $id_usuario) {
        // Verificar que el cliente existe
        $cliente = $this->findById($id_cliente);
        if (!$cliente) {
            return ['success' => false, 'error' => 'Cliente no encontrado'];
        }

        // Verificar que el usuario existe
        $queryUsuario = "SELECT id, nombre, tipo_usuario, id_cliente FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $queryUsuario);
        mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
        mysqli_stmt_execute($stmt);
        $resultUsuario = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultUsuario) == 0) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }

        $usuario = mysqli_fetch_assoc($resultUsuario);

        // Verificar que el usuario no esté ya asociado a otro cliente
        if ($usuario['id_cliente'] !== null && $usuario['id_cliente'] != $id_cliente) {
            return ['success' => false, 'error' => 'El usuario ya está asociado a otro cliente'];
        }

        // Asociar el cliente al usuario
        $query = "UPDATE usuarios SET id_cliente = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $id_cliente, $id_usuario);

        if (mysqli_stmt_execute($stmt)) {
            return [
                'success' => true,
                'cliente' => $this->findById($id_cliente),
                'usuario' => [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'tipo_usuario' => $usuario['tipo_usuario']
                ]
            ];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Desasocia un usuario de un cliente
     */
    public function desasociarUsuario($id_usuario) {
        // Verificar que el usuario existe y tiene un cliente asociado
        $queryUsuario = "SELECT id, id_cliente FROM usuarios WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $queryUsuario);
        mysqli_stmt_bind_param($stmt, 'i', $id_usuario);
        mysqli_stmt_execute($stmt);
        $resultUsuario = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($resultUsuario) == 0) {
            return ['success' => false, 'error' => 'Usuario no encontrado'];
        }

        $usuario = mysqli_fetch_assoc($resultUsuario);

        if ($usuario['id_cliente'] === null) {
            return ['success' => false, 'error' => 'El usuario no tiene ningún cliente asociado'];
        }

        // Desasociar
        $query = "UPDATE usuarios SET id_cliente = NULL WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_usuario);

        if (mysqli_stmt_execute($stmt)) {
            return ['success' => true];
        }

        return ['success' => false, 'error' => mysqli_error($this->conn)];
    }

    /**
     * Obtiene el usuario asociado a un cliente (si existe)
     */
    public function getUsuarioAsociado($id_cliente) {
        $query = "
            SELECT id, nombre, tipo_usuario, inhabilitado
            FROM usuarios
            WHERE id_cliente = ?
        ";

        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id_cliente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }

        return null;
    }
}
