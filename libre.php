<?php

echo phpinfo();
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php'; // Ajusta la ruta si es necesario

use libredte\lib\Core\Application;
$app = Application::getInstance();
die("KAKOTA");