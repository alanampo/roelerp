<?php
  
include "./class_lib/sesionSecurity.php";
// The location of the PDF file
// on the server
$filename = $_GET["file"];
$folio = $_GET["folio"];
$tipo = $_GET["tipo"];
// Header content type
header("Content-type: application/pdf");
header("Content-Disposition: filename=$tipo-$folio.pdf");
header("Content-Length: " . filesize($filename));


// Send the file to the browser.
readfile($filename);
?> 

