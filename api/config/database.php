<?php
/**
 * Configuración de base de datos para la API
 */

require_once __DIR__ . '/../../class_lib/class_conecta_mysql.php';

class Database {
    private $connection;

    public function __construct() {
        global $host, $user, $password, $dbname;

        $this->connection = mysqli_connect($host, $user, $password, $dbname);

        if (!$this->connection) {
            throw new Exception("Error de conexión a la base de datos: " . mysqli_connect_error());
        }

        mysqli_set_charset($this->connection, 'utf8');
    }

    public function getConnection() {
        return $this->connection;
    }

    public function close() {
        if ($this->connection && mysqli_ping($this->connection)) {
            mysqli_close($this->connection);
            $this->connection = null;
        }
    }

    public function __destruct() {
        $this->close();
    }
}
