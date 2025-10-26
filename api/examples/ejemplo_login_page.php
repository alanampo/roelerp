<?php
/**
 * Ejemplo de página de login usando la API
 * Este archivo muestra cómo integrar la API en un formulario de login existente
 */

session_start();
require_once __DIR__ . '/php_integration.php';

// Configurar URL de la API
$api = new RoelERPApi('http://localhost/roel/roelerp/api');

// Verificar si ya está autenticado
if ($api->isAuthenticated()) {
    // Validar que el token siga siendo válido
    if ($api->validateToken()) {
        header('Location: inicio.php');
        exit;
    }
}

// Procesar login
$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoLogin = isset($_POST['tipo_login']) ? $_POST['tipo_login'] : 'usuario';

    if ($tipoLogin === 'usuario') {
        // Login de trabajador
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = $api->loginUsuario($username, $password);

        if ($result['status'] === 'success') {
            header('Location: inicio.php');
            exit;
        } else {
            $error = $result['message'];
        }

    } elseif ($tipoLogin === 'cliente') {
        // Login de cliente
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $result = $api->loginCliente($email, $password);

        if ($result['status'] === 'success') {
            header('Location: portal_cliente.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Roel ERP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 400px;
        }
        .login-container h2 {
            margin-top: 0;
            text-align: center;
            color: #333;
        }
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        .tab {
            flex: 1;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            background: #f9f9f9;
            border: none;
            font-size: 16px;
            transition: all 0.3s;
        }
        .tab.active {
            background: white;
            border-bottom: 3px solid #4CAF50;
            margin-bottom: -2px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #45a049;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-error {
            background: #f44336;
            color: white;
        }
        .alert-success {
            background: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Roel ERP</h2>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('usuario')">Trabajadores</button>
            <button class="tab" onclick="switchTab('cliente')">Clientes</button>
        </div>

        <!-- Login de Usuario Trabajador -->
        <div id="tab-usuario" class="tab-content active">
            <form method="POST">
                <input type="hidden" name="tipo_login" value="usuario">

                <div class="form-group">
                    <label for="username">Usuario:</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn">Ingresar como Trabajador</button>
            </form>
        </div>

        <!-- Login de Cliente -->
        <div id="tab-cliente" class="tab-content">
            <form method="POST">
                <input type="hidden" name="tipo_login" value="cliente">

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password_cliente">Contraseña:</label>
                    <input type="password" id="password_cliente" name="password" required>
                </div>

                <button type="submit" class="btn">Ingresar como Cliente</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(type) {
            // Remover active de todos los tabs
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Activar tab seleccionado
            event.target.classList.add('active');
            document.getElementById('tab-' + type).classList.add('active');
        }
    </script>
</body>
</html>
