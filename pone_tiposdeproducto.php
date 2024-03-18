<?php

include "./class_lib/sesionSecurity.php";
error_reporting(0);
require('class_lib/class_conecta_mysql.php');

$tipo = $_POST['tipo'];
$id_tipo = $_POST['id_tipo'];
$id_subtipo = $_POST['id_subtipo'];

$con = mysqli_connect($host, $user, $password,$dbname);
// Check connection
if (!$con) {
  die("Connection failed: " . mysqli_connect_error());
}


mysqli_query($con,"SET NAMES 'utf8'");

if ($tipo == "pone_tiposdeproducto"){

	$cadena = "SELECT id_articulo, nombre FROM tipos_producto ORDER BY nombre ASC";
	$val = mysqli_query($con, $cadena);
	 if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<option value=$re[id_articulo]>$re[nombre]</option>";
	    }
	}
}
else if ($tipo == "carga_subtipos")
{

	$cadena = "SELECT id_articulo, nombre FROM subtipos_producto WHERE id_tipo = '$id_tipo' ORDER BY nombre ASC";
	$val = mysqli_query($con, $cadena);
	 if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<option value=$re[id_articulo]>$re[nombre]</option>";
	    }
	}
}

else if ($tipo == "carga_variedades") {
	$cadena = "SELECT id_articulo, nombre FROM variedades_producto WHERE id_subtipo = $id_subtipo ORDER BY nombre ASC;";
	$val = mysqli_query($con, $cadena);
	if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<option value=$re[id_articulo]>$re[nombre]</option>";
	    }
	}
}

else if ($tipo == "carga_bandejas") {
	$cadena = "SELECT precio_288, precio_200, precio_162, precio_128, precio_72, precio_50, precio_25, precio_49 FROM tipos_producto WHERE id_articulo = $id_tipo;";
	$val = mysqli_query($con, $cadena);
	$array = ["288","200","162","128","72","50","25","49"];
	if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	for ($i = 0;$i<count($array);$i++){
	    		if($re[$i] != NULL){
	    			echo "<option>".$array[$i]."</option>";		
	    		}
	    	}
	    	
	    }
	}
}
else if ($tipo == "carga_precios") {
	$cadena = "SELECT * FROM tipos_producto WHERE id_articulo = $id_tipo;";
	$val = mysqli_query($con, $cadena);
	if (mysqli_num_rows($val)>0){
	    $precios = "";
	    while($re=mysqli_fetch_array($val)){
	    	$precios = array (
			    "288"=>array(
			        "sinsemilla" => $re["precio_288"],
			        "consemilla" => $re["precio_288_s"]
			    ),
			    "200"=>array(
			        "sinsemilla" => $re["precio_200"],
			        "consemilla" => $re["precio_200_s"]
			    ),
			    "162"=>array(
			        "sinsemilla" => $re["precio_162"],
			        "consemilla" => $re["precio_162_s"]
			    ),
			    "128"=>array(
			        "sinsemilla" => $re["precio_128"],
			        "consemilla" => $re["precio_128_s"]
			    ),
			    "72"=>array(
			        "sinsemilla" => $re["precio_72"],
			        "consemilla" => $re["precio_72_s"]
			    ),
			    "50"=>array(
			        "sinsemilla" => $re["precio_50"],
			        "consemilla" => $re["precio_50_s"]
			    ),
			    "25"=>array(
			        "sinsemilla" => $re["precio_25"],
			        "consemilla" => $re["precio_25_s"]
			    ),
			    "49"=>array(
			        "sinsemilla" => $re["precio_49"],
			        "consemilla" => $re["precio_49_s"]
			    )
			);
	    }
	    echo json_encode($precios);
	}
}


else if ($tipo == "carga_stock"){
	$id_tipo = $_POST["id_tipo"];
	$cadena = "(SELECT t.nombre as nombre_tipo, s.nombre as nombre_subtipo, v.nombre as nombre_variedad, SUM(om.cantidad) as cantidad, p.bandeja, v.id_articulo AS id_variedad 
		FROM variedades_producto v 
		INNER JOIN subtipos_producto s ON v.id_subtipo = s.id_articulo 
		INNER JOIN tipos_producto t ON t.id_articulo = s.id_tipo
		INNER JOIN articulospedidos p ON p.id_articulo = v.id_articulo 
		INNER JOIN ordenes_siembra o ON o.id_artpedido = p.id_artpedido
		INNER JOIN ordenes_mesadas om ON om.id_orden = o.id_orden
		WHERE t.id_articulo = $id_tipo AND om.cantidad > 0 AND om.tipo = 1 
		GROUP BY v.id_articulo, p.bandeja
		ORDER BY nombre_subtipo ASC, nombre_variedad ASC)
UNION ALL
		(SELECT t.nombre as nombre_tipo, s.nombre as nombre_subtipo, v.nombre as nombre_variedad, SUM(om.cantidad) as cantidad, om.bandeja, om.id_variedad
		FROM variedades_producto v 
		INNER JOIN subtipos_producto s ON v.id_subtipo = s.id_articulo 
		INNER JOIN tipos_producto t ON t.id_articulo = s.id_tipo
		INNER JOIN ordenes_mesadas om ON om.id_variedad = v.id_articulo
		WHERE om.id_orden IS NULL AND t.id_articulo = $id_tipo AND om.cantidad > 0 AND om.tipo = 1 
		GROUP BY v.id_articulo, om.bandeja
		ORDER BY nombre_subtipo ASC, nombre_variedad ASC);";


	$val = mysqli_query($con, $cadena);
	if (mysqli_num_rows($val)>0){
	    while($re=mysqli_fetch_array($val)){
	    	echo "<tr onClick='toggleSelection(this)' style='cursor:pointer;text-align:center;'>";
    		echo "<td>$re[nombre_subtipo]</td>";
    		echo "<td id='variedad_$re[id_variedad]'>$re[nombre_variedad]</td>";
   			echo "<td>$re[bandeja]</td>";
   			echo "<td>$re[cantidad]</td>";
   			echo "</tr>";
	    }
	}



}

?>
