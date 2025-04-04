<?php
error_reporting(0);

if (strpos($_SERVER['HTTP_HOST'], 'roelplant') !== false) {
  $host = getenv("DB_HOST");
  $user = getenv("DB_USER");
  $password = getenv("DB_PASSWORD");
  $dbname = getenv("DB_NAME");
  $dbpresta = getenv("DB_NAME_PRESTASHOP");
}
else{
  $host = getenv("DB_HOST_LOCAL");
  $user = getenv("DB_USER_LOCAL");
  $password = getenv("DB_PASSWORD_LOCAL");
  $dbname = getenv("DB_NAME_LOCAL");
  $dbpresta = getenv("DB_NAME_PRESTASHOP_LOCAL");
}

?>