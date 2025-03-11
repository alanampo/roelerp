<?php
  session_name("roel-erp");session_start();
  date_default_timezone_set("America/Santiago");
  $version = "53";
  header('Content-type: text/html; charset=utf-8');
  if(!isset($_SESSION["roel-erp-token"]) || !isset($_COOKIE["roel-erp-token"])){
    header("Location: index.php");
  }

  if($_SESSION["roel-erp-token"] != $_COOKIE["roel-erp-token"]){
    header("Location: index.php");
  }
?>