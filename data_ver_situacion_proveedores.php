<?php

include "./class_lib/sesionSecurity.php";
header('Content-type: text/html; charset=utf-8');
error_reporting(0);
require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

$con = mysqli_connect($host, $user, $password, $dbname);
// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($con, "SET NAMES 'utf8'");

$consulta = $_POST["consulta"];
function truncarStringConSuspensivos($cadena, $longitudMaxima) {
    // Verificar si la longitud de la cadena es mayor que la longitud máxima
    if (strlen($cadena) > $longitudMaxima) {
        // Truncar la cadena a la longitud máxima y agregar puntos suspensivos
        $cadenaTruncada = substr($cadena, 0, $longitudMaxima) . '...';
        return $cadenaTruncada;
    } else {
        // Devolver la cadena original si no es necesario truncar
        return $cadena;
    }
}

function agregar_guion_ultimo_caracter($cadena) {
    // Verificar que la cadena tenga al menos 1 carácter
    if (strlen($cadena) < 1) {
        return $cadena;
    }

    // Obtener el último carácter
    $ultimo_caracter = substr($cadena, -1);
    // Obtener la parte de la cadena sin el último carácter
    $cadena_sin_ultimo = substr($cadena, 0, -1);
    
    // Agregar el guion antes del último carácter
    $cadena_con_guion = $cadena_sin_ultimo . "-" . $ultimo_caracter;

    return $cadena_con_guion;
}

if ($consulta == "get_situacion_proveedores") {
    $query = "SELECT
    GROUP_CONCAT(p.rut SEPARATOR ', ') as rut,
    MAX(p.id) AS proveedor_id,
    p.razonSocial AS razonSocial,
    SUM(f.montoTotal) AS total_facturas,
    IFNULL(SUM(pa.monto), 0) AS total_pagos
FROM
    facturas_compra f
INNER JOIN proveedores p ON
    f.id_proveedor = p.id
LEFT JOIN facturas_compra_pagos pa ON
    pa.id_factura_compra = f.id
GROUP BY
    p.razonSocial
    ";
    $val = mysqli_query($con, $query);

    if (mysqli_num_rows($val) > 0) {
        echo "<div class='box box-primary'>";
        echo "<div class='box-header with-border'>";
        echo "<h3 class='box-title'>Situación Proveedores</h3>";
        echo "</div>";
        echo "<div class='box-body'>";
        echo "<table id='tabla_hist' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>RUT</th><th>Razón Social</th><th>Deuda</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            $balance = (float) $ww["total_facturas"] - (float) $ww["total_pagos"];
            $deuda = $balance <= 0 ? 0 : number_format($balance, 0, ',', '.');

            $numeros = explode(", ", $ww["rut"]);

            // Recorrer cada número y agregar guion antes del último carácter
            foreach ($numeros as &$numero) {
                $numero = substr($numero, 0, -1) . "-" . substr($numero, -1);
            }

            // Unir los números modificados de nuevo en una cadena
            $rut = implode(", ", $numeros);
            echo "<tr style='cursor:pointer;'>";
            echo "<td><span ".(strlen($rut)>30 ? "data-toggle='tooltip' data-placement='top' title='$rut'" : "").">".truncarStringConSuspensivos($rut, 30)."</span></td>";
            echo "<td>$ww[razonSocial]</td>";
            echo "<td class='text-" . ($balance <= 0 ? "success" : "danger") . "'>$$deuda</td>";
            echo "<td><button onclick='detalleDeuda($ww[proveedor_id], \"$ww[razonSocial]\")' class='btn btn-primary btn fa fa-file'></button></td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron registros...</b></div>";
    }
}  else if ($consulta == "grafico_por_cobrar") {
    $anio = $_POST["anio"];
    $aniofin = (int) $anio + 1;

    $querymeses = "";
    for ($i = 1; $i <= 12; $i++) {
        $mes = $i;

        $mesito = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $dt = strtotime("$anio-$mesito-01");
        $fechafin = (string) date("Y-m-d", strtotime("+1 month", $dt));
        $querymeses .= "
            (SELECT (qfact.mes$i - qfact.pagos$i) as mes$i  FROM
                (
                SELECT                   
                f.id, 
                f.montoTotal as mes$i, 
                (SELECT IFNULL(SUM(pa.monto),0) 
                FROM facturas_compra_pagos pa 
                WHERE pa.id_factura_compra = f.id) AS 'pagos$i' 
                FROM facturas_compra f 
                INNER JOIN proveedores p ON f.id_proveedor = p.id
                WHERE
                f.fecha >= '$anio-$mesito-01 00:00:00'
                AND f.fecha < '$fechafin 00:00:00') qfact
            ) as qp$i,";
    }
    $querymeses = rtrim($querymeses, ",");

    $querydeudas = "SELECT * FROM
        $querymeses
        ";



    $rdeudas = mysqli_query($con, $querydeudas);
    if (mysqli_num_rows($rdeudas) > 0) {
        $deudas = mysqli_fetch_assoc($rdeudas);

        $array = [
            (int) $deudas["mes1"],
            (int) $deudas["mes2"],
            (int) $deudas["mes3"],
            (int) $deudas["mes4"],
            (int) $deudas["mes5"],
            (int) $deudas["mes6"],
            (int) $deudas["mes7"],
            (int) $deudas["mes8"],
            (int) $deudas["mes9"],
            (int) $deudas["mes10"],
            (int) $deudas["mes11"],
            (int) $deudas["mes12"],
        ];
        echo json_encode($array);
    }
} else if ($consulta == "grafico_clientes_deudores") {
    $query = "SELECT
        c.id_cliente as id_cliente,
        c.nombre as nombre,
        (
            (SELECT IFNULL(SUM(coti.monto),0) FROM cotizaciones coti INNER JOIN facturas f ON f.id_cotizacion = coti.id WHERE coti.id_cliente = c.id_cliente AND f.estado = 'ACEPTADO' ) +
            (SELECT IFNULL(SUM(cotid.monto),0) FROM cotizaciones_directas cotid INNER JOIN facturas f ON f.id_cotizacion_directa = cotid.id WHERE cotid.id_cliente = c.id_cliente AND f.estado = 'ACEPTADO' )
        ) as montofacturas,
        (
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag INNER JOIN facturas f ON f.rowid = pag.rowid_factura INNER JOIN cotizaciones coti ON f.id_cotizacion = coti.id WHERE coti.id_cliente = c.id_cliente) +
            (SELECT IFNULL(SUM(pag.monto),0) FROM facturas_pagos pag INNER JOIN facturas f ON f.rowid = pag.rowid_factura INNER JOIN cotizaciones_directas cotid ON f.id_cotizacion_directa = cotid.id WHERE cotid.id_cliente = c.id_cliente)
        ) as sumapagos
        FROM clientes c
        GROUP BY id_cliente
        HAVING (montofacturas - sumapagos) > 0
        ORDER BY nombre ASC;";

    $val = mysqli_query($con, $query);
    $clientes = array();
    if (mysqli_num_rows($val) > 0) {
        while ($ww = mysqli_fetch_array($val)) {
            array_push($clientes, array(
                "id_cliente" => $ww["id_cliente"],
                "nombre_cliente" => $ww["nombre"],
                "deuda" => (int) $ww["montofacturas"] - (int) $ww["sumapagos"],
            ));
        }
        echo json_encode($clientes);
    }
} else if ($consulta == "cargar_detalle_deuda") {
    $query = "SELECT p.razonSocial AS 'razonSocial', 
    p.rut AS 'rut', f.fecha AS 'fecha', 
    f.id, 
    DATE_FORMAT(f.fecha, '%d/%m/%y') as fecha_formatted, 
    f.folio AS 'folio', 
    f.montoTotal AS 'montoTotal', f.iva AS 'iva', 
    (SELECT IFNULL(SUM(pa.monto),0) 
    FROM facturas_compra_pagos pa 
    WHERE pa.id_factura_compra = f.id) AS 'pagos' 
    FROM facturas_compra f 
    INNER JOIN proveedores p ON f.id_proveedor = p.id
    WHERE p.razonSocial = '$_POST[razonSocial]'
    ORDER BY folio DESC
    ;
    ";

    $val = mysqli_query($con, $query);
    
    if (mysqli_num_rows($val) > 0) {
        echo "<table id='tabla-historial-facturas' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>Folio</th><th>Fecha</th><th>RUT</th><th>Monto</th><th>Deuda</th><th></th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        while ($ww = mysqli_fetch_array($val)) {
            if (((int) $ww["montoTotal"] - (int) $ww["pagos"]) > 0) {
                $monto = $ww["montoTotal"] != null ? "$" . number_format($ww["montoTotal"], 0, ',', '.') : "";
                $deuda = (float) $ww["montoTotal"] - (float) $ww["pagos"];
                $classdeuda = ($deuda <= 0 ? "success" : "danger");
                $deuda = "$" . number_format($deuda >= 0 ? $deuda : 0, 0, ',', '.');

                $btn_add_pago = "<button style='padding-left:11px;padding-right:11px' onclick='agregarPago($ww[id], $ww[folio], $ww[montoTotal])' class='btn btn-success fa fa-usd btn-sm mr-2'> </button>";

                echo "
                <tr style='cursor:pointer' x-id='$ww[folio]'>
                <td>$ww[folio]</td>
                <td><span class='d-none'>$ww[fecha]</span>$ww[fecha_formatted]</td>
                <td>".agregar_guion_ultimo_caracter($ww["rut"])."</td>
                <td>$monto</td>
                <td class='text-$classdeuda'>$deuda</td>
                <td>
                        <div class='ml-1 d-flex flex-row justify-content-center align-items-center'>
                            $btn_add_pago
                        </div>
                </td>
                </tr>";
            }
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        echo "<div class='callout callout-danger'><b>No se encontraron deudas para este proveedor</b></div>";
    }
}
