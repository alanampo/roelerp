<?php
session_name("roel-erp");session_start();
session_destroy();
$parametros_cookies = session_get_cookie_params();
setcookie(session_name(),0,1,$parametros_cookies["path"]);
setcookie('roel-erp-usuario', '', time() - 3600, '/');
setcookie('roel-erp-token', '', time() - 3600, '/');

header("Location: index.php");

?>