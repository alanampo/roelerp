<?php
///****ARCHIVO DE FUNCIONES*****///
$filePath = $_SERVER['DOCUMENT_ROOT'] . '/.env';
if (!file_exists($filePath)) {
  throw new Exception("Archivo .env no encontrado.");
}

$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
  if (strpos($line, '=') !== false) {
    list($name, $value) = explode('=', $line, 2);
    $name = trim($name);
    $value = str_replace('"', '', trim($value));
    
    if (!array_key_exists($name, $_ENV)) {
      putenv("$name=$value");
      $_ENV[$name] = $value;
      $_SERVER[$name] = $value;
    }
  }
}

session_name("roel-erp");
session_start();
date_default_timezone_set("America/Santiago");
$version = "57";
header('Content-type: text/html; charset=utf-8');
if (!isset($_SESSION["roel-erp-token"]) || !isset($_COOKIE["roel-erp-token"])) {
  header("Location: index.php");
}

if ($_SESSION["roel-erp-token"] != $_COOKIE["roel-erp-token"]) {
  header("Location: index.php");
}
?>