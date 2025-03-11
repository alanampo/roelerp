<?php

include "./class_lib/sesionSecurity.php";

require 'class_lib/class_conecta_mysql.php';
require 'class_lib/funciones.php';

try {
    $con = mysqli_connect($host, $user, $password, $dbname);
    // Check connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    mysqli_query($con, "SET NAMES 'utf8'");
    $consulta = $_POST["consulta"];

    if ($consulta == "busca_stock") {
        mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
        $query = "SELECT
          s.id_stock,
          s.cantidad,
          UPPER(s.codigo) as codigo,
          s.porcentaje,
          DATE_FORMAT(s.fecha, '%d/%m/%y') as fecha_stock,
          DATE_FORMAT(s.fecha, '%y%m%d') as fecha_stock_raw,
          c.nombre as nombre_cliente,
          c.id_cliente,
          v.nombre as nombre_variedad,
          e.nombre as nombre_especie,
          t.codigo as tipo_semilla,
          m.nombre as nombre_marca,
          p.nombre as nombre_proveedor,
          sr.id_retiro,
          ROUND(IFNULL(s.precio,0)/s.cantidad) as costo,
          ROUND((IFNULL(s.precio,0)/s.cantidad)*1.19) as costo_iva,
          s.precio
          FROM stock_semillas s
          INNER JOIN clientes c ON c.id_cliente = s.id_cliente
          LEFT JOIN variedades_producto v ON v.id = s.id_variedad
          LEFT JOIN especies_provistas e ON e.id = s.id_especie
          INNER JOIN tipos_producto t ON t.id = v.id_tipo OR t.id = e.id_tipo
          INNER JOIN semillas_marcas m ON m.id = s.id_marca
          INNER JOIN semillas_proveedores p ON p.id = s.id_proveedor
          LEFT JOIN stock_semillas_retiros sr ON sr.id_stock = s.id_stock
          ;
          ";

        $val = mysqli_query($con, $query);

        if (mysqli_num_rows($val) > 0) {

            echo "<div class='box box-primary'>";
            echo "<div class='box-header with-border'>";
            echo "<h3 class='box-title text-primary font-weight-bold'>Stock Actual</h3>";
            echo "</div>";
            echo "<div class='box-body'>";
            echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>Cód/Id</th>
                <th>Variedad/Especie</th>
                <th>Cantidad Actual</th>
                <th>Marca</th>
                <th>Proveedor</th>
                <th>Cliente</th>
                <th>Precio</th>
                <th>Costo</th>
                <th>Costo +IVA</th>
                ";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            while ($ww = mysqli_fetch_array($val)) {
                $aux = mysqli_query($con, "SELECT IFNULL(SUM(cantidad),0) as canti FROM stock_semillas_retiros WHERE id_stock = $ww[id_stock];");
                $canti = $ww["cantidad"];
                if (mysqli_num_rows($aux) > 0) {
                    $re = mysqli_fetch_assoc($aux);
                    $canti = $canti - $re["canti"];
                }
                $cantidad = $canti <= 10 ? "<span class='text-danger font-weight-bold'>$canti</span>" : "<span class='font-weight-bold'>$canti</span>";

                if ($ww["nombre_variedad"] != NULL) {
                    $producto = "$ww[nombre_variedad] ($ww[tipo_semilla]) [$ww[fecha_stock]] $ww[porcentaje]%";
                } else {
                    $producto = "$ww[nombre_especie]  ($ww[tipo_semilla])[$ww[fecha_stock]] $ww[porcentaje]";
                }
                $precio = $ww["precio"] ? "$" . number_format($ww["precio"], 0, ',', '.') : "";
                $costo = $ww["costo"] ? "$" . number_format($ww["costo"], 0, ',', '.') : "";
                $costo_iva = $ww["costo_iva"] ? "$" . number_format($ww["costo_iva"], 0, ',', '.') : "";
                echo "
            <tr class='text-center' style='cursor:pointer' x-id='$ww[id_stock]' x-cantidad='$ww[cantidad]'>
                <td>$ww[codigo]</td>
                <td>$producto</td>
                <td>$cantidad</td>
                <td>$ww[nombre_marca]</td>
                <td>$ww[nombre_proveedor]</td>
                <td>$ww[nombre_cliente] ($ww[id_cliente])</td>
                <td>$precio</td>
                <td>$costo</td>
                <td>$costo_iva</td>
            </tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='callout callout-danger'><b>No se encontraron semillas en stock...</b></div>";
        }
    } else if ($consulta == "busca_ingresos") {
        mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
        $query = "SELECT
          s.id_stock,
          s.cantidad,
          UPPER(s.codigo) as codigo,
          s.porcentaje,
          DATE_FORMAT(s.fecha, '%d/%m/%Y') as fecha_stock_full,
          DATE_FORMAT(s.fecha, '%d/%m/%y') as fecha_stock,
          DATE_FORMAT(s.fecha, '%y%m%d') as fecha_stock_raw,
          DATE_FORMAT(s.fecha_ingreso, '%d/%m/%y<br>%H:%i') as fecha_ingreso,
          DATE_FORMAT(s.fecha_ingreso, '%y%m%d%H%i') as fecha_ingreso_raw,
          c.nombre as nombre_cliente,
          c.id_cliente,
          v.nombre as nombre_variedad,
          e.nombre as nombre_especie,
          t.codigo as tipo_semilla,
          m.nombre as nombre_marca,
          m.id  as id_marca,
          p.nombre as nombre_proveedor,
          p.id as id_proveedor,
          sr.id_retiro,
          ROUND(IFNULL(s.precio,0)/s.cantidad) as costo,
          ROUND((IFNULL(s.precio,0)/s.cantidad)*1.19) as costo_iva,
          s.precio
          FROM stock_semillas s
          INNER JOIN clientes c ON c.id_cliente = s.id_cliente
          LEFT JOIN variedades_producto v ON v.id = s.id_variedad
          LEFT JOIN especies_provistas e ON e.id = s.id_especie
          INNER JOIN tipos_producto t ON t.id = v.id_tipo OR t.id = e.id_tipo
          INNER JOIN semillas_marcas m ON m.id = s.id_marca
          INNER JOIN semillas_proveedores p ON p.id = s.id_proveedor
          LEFT JOIN stock_semillas_retiros sr ON sr.id_stock = s.id_stock
          ;
          ";

        $val = mysqli_query($con, $query);

        if (mysqli_num_rows($val) > 0) {
            echo "<div class='box box-primary'>";
            echo "<div class='box-header with-border'>";
            echo "<h3 class='box-title'>Historial de <span class='font-weight-bold text-success'>Ingresos</span></h3>";
            echo "</div>";
            echo "<div class='box-body'>";
            echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>F. Ingreso</th>
                <th>Cód/Id</th>
                <th>Variedad/Especie</th>
                <th>Cantidad</th>
                <th>Marca</th>
                <th>Proveedor</th>
                <th>Cliente</th>
                <th>Precio</th>
                <th>Costo</th>
                <th>Costo +IVA</th>
                <th></th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            while ($ww = mysqli_fetch_array($val)) {
                $boton_eliminar = !$ww["id_retiro"] ? "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminar($ww[id_stock])'></button>" : "";

                if ($ww["nombre_variedad"] != NULL) {
                    $producto = "$ww[nombre_variedad] ($ww[tipo_semilla]) [$ww[fecha_stock]] $ww[porcentaje]%";
                } else {
                    $producto = "$ww[nombre_especie]  ($ww[tipo_semilla])[$ww[fecha_stock]] $ww[porcentaje]%";
                }
                $onclick = !$ww["id_retiro"] ? "onClick='modalAgregarSemillas(this)'" : "";
                $precio = $ww["precio"] ? "$" . number_format($ww["precio"], 0, ',', '.') : "";
                $costo = $ww["costo"] ? "$" . number_format($ww["costo"], 0, ',', '.') : "";
                $costo_iva = $ww["costo_iva"] ? "$" . number_format($ww["costo_iva"], 0, ',', '.') : "";
                echo "
            <tr class='text-center' style='cursor:pointer' x-id='$ww[id_stock]' x-cantidad='$ww[cantidad]' x-id-marca='$ww[id_marca]' x-id-proveedor='$ww[id_proveedor]' x-codigo='$ww[codigo]' x-porcentaje='$ww[porcentaje]' x-id-cliente='$ww[id_cliente]' x-fecha='$ww[fecha_stock_full]' x-precio='$ww[precio]'>
                <td $onclick><span class='d-none'>$ww[fecha_ingreso_raw]</span>$ww[fecha_ingreso]</td>
                <td $onclick>$ww[codigo]</td>
                <td $onclick>$producto</td>
                <td $onclick>$ww[cantidad]</td>
                <td $onclick>$ww[nombre_marca]</td>
                <td $onclick>$ww[nombre_proveedor]</td>
                <td $onclick>$ww[nombre_cliente] ($ww[id_cliente])</td>
                <td>$precio</td>
                <td>$costo</td>
                <td>$costo_iva</td>
                <td class='text-center'>
                    $boton_eliminar
                </td>
            </tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='callout callout-danger'><b>No se encontraron ingresos de semillas...</b></div>";
        }
    } else if ($consulta == "busca_egresos") {
        mysqli_query($con, "SET SESSION SQL_BIG_SELECTS=1");
        $query = "SELECT
        sr.id_stock,
        sr.cantidad,
        UPPER(s.codigo) as codigo,
        s.porcentaje,
        DATE_FORMAT(sr.fecha, '%d/%m/%y') as fecha_retiro,
        DATE_FORMAT(sr.fecha, '%y%m%d') as fecha_retiro_raw,
        DATE_FORMAT(s.fecha, '%d/%m/%y') as fecha_stock,
        DATE_FORMAT(s.fecha, '%y%m%d') as fecha_stock_raw,
        c.nombre as nombre_cliente,
        c.id_cliente,
        v.nombre as nombre_variedad,
        v.id_interno as id_variedad,
        e.id as id_especie,
        e.nombre as nombre_especie,
        t.codigo as tipo_semilla,
        m.nombre as nombre_marca,
        p.nombre as nombre_proveedor,
        sr.id_retiro,
        u.iniciales,
        pe.id_interno as id_pedido_interno,
        DATE_FORMAT(pe.fecha, '%m/%d') AS mes_dia,
        ap.cant_plantas
        FROM stock_semillas_retiros sr 
        INNER JOIN stock_semillas s ON sr.id_stock = s.id_stock
        INNER JOIN clientes c ON c.id_cliente = s.id_cliente
        INNER JOIN semillas_marcas m ON m.id = s.id_marca
        INNER JOIN semillas_proveedores p ON p.id = s.id_proveedor
        INNER JOIN articulospedidos ap ON ap.id = sr.id_artpedido
        INNER JOIN pedidos pe ON pe.id_pedido = ap.id_pedido
        INNER JOIN usuarios u ON u.id = pe.id_usuario
        LEFT JOIN variedades_producto v ON v.id = ap.id_variedad
        LEFT JOIN especies_provistas e ON e.id = ap.id_especie
        INNER JOIN tipos_producto t ON t.id = v.id_tipo OR t.id = e.id_tipo
          ;
          ";

        $val = mysqli_query($con, $query);

        if (mysqli_num_rows($val) > 0) {

            echo "<div class='box box-primary'>";
            echo "<div class='box-header with-border'>";
            echo "<h3 class='box-title'>Historial de <span class='font-weight-bold text-danger'>Egresos</span></h3>";
            echo "</div>";
            echo "<div class='box-body'>";
            echo "<table id='tabla' class='table table-bordered table-responsive w-100 d-block d-md-table'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th>F. Egreso</th>
                <th>Cód Sem.</th>        
                <th>Variedad/Especie</th>
                <th>Cantidad</th>
                <th>Marca</th>
                <th>Proveedor</th>
                <th>Cliente</th>
                <th>Cód Ped.</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            while ($ww = mysqli_fetch_array($val)) {
                $boton_eliminar = "<button class='btn btn-danger fa fa-trash btn-sm' onClick='eliminarEgreso($ww[id_retiro])'></button>";

                if ($ww["nombre_variedad"] != NULL) {
                    $producto = "$ww[nombre_variedad] ($ww[tipo_semilla]) [$ww[fecha_stock]] $ww[porcentaje]%";
                } else {
                    $producto = "$ww[nombre_especie]  ($ww[tipo_semilla])[$ww[fecha_stock]] $ww[porcentaje]%";
                }
                $id_especie = $ww["id_especie"] ? "-" . str_pad($ww["id_especie"], 2, '0', STR_PAD_LEFT) : "";
                $id_producto = "$ww[iniciales]$ww[id_pedido_interno]/M$ww[mes_dia]/$ww[codigo]" . str_pad($ww["id_interno"], 2, '0', STR_PAD_LEFT) . $id_especie . "/$ww[cant_plantas]/" . str_pad($ww["id_cliente"], 2, '0', STR_PAD_LEFT);

                echo "
            <tr class='text-center' style='cursor:pointer' x-id-retiro='$ww[id_retiro]' x-id-stock='$ww[id_stock]' x-cantidad='$ww[cantidad]'>
                <td><span class='d-none'>$ww[fecha_retiro_raw]</span>$ww[fecha_retiro]</td>
                <td>$ww[codigo]</td>
                <td>$producto</td>
                <td>$ww[cantidad]</td>
                <td>$ww[nombre_marca]</td>
                <td>$ww[nombre_proveedor]</td>
                <td>$ww[nombre_cliente] ($ww[id_cliente])</td>
                <td>$id_producto</td>
            </tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='callout callout-danger'><b>No se encontraron egresos de semillas...</b></div>";
        }
    } else if ($consulta == "guardar_semillas") {
        $tipo = $_POST["tipo"];
        $id_cliente = $_POST["id_cliente"];
        $id_variedad_especie = $_POST["id_variedad_especie"];
        if ($tipo == "S") {
            $id_variedad = $id_variedad_especie;
            $id_especie = "NULL";
        } else {
            $id_variedad = "NULL";
            $id_especie = $id_variedad_especie;
        }
        $id_marca = $_POST["id_marca"];
        $id_proveedor = $_POST["id_proveedor"];
        $cantidad = mysqli_real_escape_string($con, $_POST["cantidad"]);
        $porcentaje = mysqli_real_escape_string($con, $_POST["porcentaje"]);
        $fecha = mysqli_real_escape_string($con, $_POST["fecha"]);
        $codigo = mysqli_real_escape_string($con, $_POST["codigo"]);
        $precio = mysqli_real_escape_string($con, $_POST["precio"]);
        $total = mysqli_real_escape_string($con, $_POST["total"]);

        $edit_mode = strlen($_POST["id_stock_semillas"]) > 0 ? TRUE : FALSE;
        try {
            if ($edit_mode == FALSE) {
                $query = "INSERT INTO stock_semillas (
                id_cliente,
                id_marca,
                id_proveedor,
                id_variedad,
                id_especie,
                cantidad,
                fecha, 
                fecha_ingreso,
                porcentaje,
                codigo,
                precio,
                total
                )
                  VALUES
                (
                $id_cliente,
                $id_marca,
                $id_proveedor,
                $id_variedad,
                $id_especie,
                '$cantidad',
                '$fecha',
                NOW(),
                '$porcentaje',
                '$codigo',
                $precio,
                $total
                )";
            } else {
                $query = "
            UPDATE stock_semillas
            SET
                id_cliente = $id_cliente,
                id_marca = $id_marca,
                id_proveedor = $id_proveedor,
                cantidad = '$cantidad',
                fecha = '$fecha', 
                porcentaje = '$porcentaje',
                codigo = '$codigo',
                precio = $precio,
                total = $total
            WHERE id_stock = $_POST[id_stock_semillas]
            ";
            }

            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    } else if ($consulta == "eliminar_ingreso") {
        $id_stock = $_POST["id_stock"];
        try {
            if (mysqli_query($con, "DELETE FROM stock_semillas WHERE id_stock = $id_stock")) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } catch (\Throwable $th) {
            //throw $th;
            echo "error: $th";
        }
    } else if ($consulta == "cargar_marcas_tabla") {
        $val = mysqli_query($con, "SELECT * FROM semillas_marcas ORDER BY nombre ASC");
        if (mysqli_num_rows($val) > 0) {
            while ($ww = mysqli_fetch_array($val)) {
                echo "
        <tr class='text-center'>
          <td>$ww[nombre]</td>
          <td>
            <div class='d-flex flex-row justify-content-center'>
              <button onclick='eliminarMarca($ww[id])' class='btn btn-danger fa fa-trash'></button>
            </div>
          </td>
        </tr>";
            }
        } else {
            echo "
    <tr>
      <td colspan='2'>
        <div class='callout callout-danger'><b>No se encontraron marcas de Semillas...</b></div>
      </td>
    </tr>";
        }
    } else if ($consulta == "cargar_marcas_select") {
        $query = "SELECT id, nombre FROM semillas_marcas ORDER BY nombre ASC";
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                echo "<option value='$re[id]'>$re[nombre] ($re[id])</option>";
            }
        }
    } else if ($consulta == "guardar_marca") {
        try {
            $nombre = mysqli_real_escape_string($con, $_POST["nombre"]);
            $val = mysqli_query($con, "SELECT * FROM semillas_marcas WHERE nombre = UPPER('$nombre')");
            if (mysqli_num_rows($val) > 0) {
                echo "yaexiste";
            } else {

                $query = "INSERT INTO semillas_marcas (nombre) VALUES (UPPER('$nombre'))";
                if (mysqli_query($con, $query)) {
                    echo "success";
                } else {
                    print_r(mysqli_error($con));
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    } else if ($consulta == "eliminar_marca") {
        try {
            $id_marca = $_POST["id_marca"];
            $query = "DELETE FROM semillas_marcas WHERE id = $id_marca";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    } else if ($consulta == "cargar_proveedores_tabla") {
        $val = mysqli_query($con, "SELECT * FROM semillas_proveedores ORDER BY nombre ASC");
        if (mysqli_num_rows($val) > 0) {
            while ($ww = mysqli_fetch_array($val)) {
                echo "
      <tr class='text-center'>
        <td>$ww[nombre]</td>
        <td>
          <div class='d-flex flex-row justify-content-center'>
            <button onclick='eliminarProveedor($ww[id])' class='btn btn-danger fa fa-trash'></button>
          </div>
        </td>
      </tr>";
            }
        } else {
            echo "
  <tr>
    <td colspan='2'>
      <div class='callout callout-danger'><b>No se encontraron proveedores de Semillas...</b></div>
    </td>
  </tr>";
        }
    } else if ($consulta == "cargar_proveedores_select") {
        $query = "SELECT id, nombre FROM semillas_proveedores ORDER BY nombre ASC";
        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                echo "<option value='$re[id]'>$re[nombre] ($re[id])</option>";
            }
        }
    } else if ($consulta == "guardar_proveedor") {
        try {
            $nombre = mysqli_real_escape_string($con, $_POST["nombre"]);
            $val = mysqli_query($con, "SELECT * FROM semillas_proveedores WHERE nombre = UPPER('$nombre')");
            if (mysqli_num_rows($val) > 0) {
                echo "yaexiste";
            } else {

                $query = "INSERT INTO semillas_proveedores (nombre) VALUES (UPPER('$nombre'))";
                if (mysqli_query($con, $query)) {
                    echo "success";
                } else {
                    print_r(mysqli_error($con));
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    } else if ($consulta == "eliminar_proveedor") {
        try {
            $id_proveedor = $_POST["id_proveedor"];
            $query = "DELETE FROM semillas_proveedores WHERE id = $id_proveedor";
            if (mysqli_query($con, $query)) {
                echo "success";
            } else {
                print_r(mysqli_error($con));
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    } else if ($consulta == "busca_variedades_especies_select") {
        try {
            $id_tipo = $_POST["id_tipo"];
            $cadena = "	SELECT * FROM (
            SELECT 
                            v.id, 
                            v.id_interno, 
                            v.nombre, 
                            t.codigo 
                            FROM variedades_producto v 
                            INNER JOIN tipos_producto t ON t.id = v.id_tipo 
                            WHERE v.eliminada IS NULL AND t.codigo = 'S' 
                            UNION 
                            SELECT
                            e.id,
                            e.id as id_interno,
                            e.nombre, 
                            t.codigo
                            FROM especies_provistas e
                            INNER JOIN tipos_producto t ON t.id = e.id_tipo
                            WHERE e.eliminada IS NULL AND t.codigo = 'HS'
                            ) t1
                            ORDER BY t1.codigo DESC, t1.id_interno ASC
                    ";
            $val = mysqli_query($con, $cadena);
            if (mysqli_num_rows($val) > 0) {
                while ($re = mysqli_fetch_array($val)) {
                    $id_interno = str_pad($re["id_interno"], 2, '0', STR_PAD_LEFT);
                    echo "<option x-codigo='$re[codigo]' value='$re[id]'>$re[nombre] ($re[codigo]$id_interno)</option>";
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    } else if ($consulta == "cargar_semillas_select") {
        $tipo = $_POST["tipo"];
        $id = $_POST["id"];
        $id_cliente = $_POST["id_cliente"];
        $plantinera = $_POST["plantinera"];

        $wherecliente = $plantinera == "1" || $plantinera == 1 ?
            "AND (s.id_cliente = $id_cliente OR s.id_cliente = 1)" :
            "AND s.id_cliente = $id_cliente";


        if ($tipo == "S") {
            $where = " s.id_variedad = $id";
        } else {
            $where = " s.id_especie = $id";
        }

        $query = "SELECT
          s.id_stock,
          s.id_cliente,
          s.cantidad,
          UPPER(s.codigo) as codigo,
          s.porcentaje,
          DATE_FORMAT(s.fecha, '%d/%m/%y') as fecha_stock,
          DATE_FORMAT(s.fecha, '%y%m%d') as fecha_stock_raw,
          m.nombre as nombre_marca,
          p.nombre as nombre_proveedor,
          (SELECT IFNULL(SUM(sr.cantidad),0) FROM stock_semillas_retiros sr WHERE sr.id_stock = s.id_stock) as cantidad_retirada
          FROM stock_semillas s
          LEFT JOIN variedades_producto v ON v.id = s.id_variedad
          LEFT JOIN especies_provistas e ON e.id = s.id_especie
          INNER JOIN tipos_producto t ON t.id = v.id_tipo OR t.id = e.id_tipo
          INNER JOIN semillas_marcas m ON m.id = s.id_marca
          INNER JOIN semillas_proveedores p ON p.id = s.id_proveedor
          WHERE $where $wherecliente
          GROUP BY s.id_stock
          ORDER BY s.fecha DESC
          ;
          ";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $cantidad = $re["cantidad"] - $re["cantidad_retirada"];
                $roel = $re["id_cliente"] == "1" || $re["id_cliente"] == 1 ?
                    "ROEL-" : "";
                if ($cantidad > 0)
                    echo "<option value='$re[id_stock]' x-codigo=\"$roel-$re[codigo]\" x-id-stock='$re[id_stock]' x-cantidad='$cantidad'>$roel$re[codigo] [$re[nombre_marca] - $re[nombre_proveedor]] $re[porcentaje]% - Cant: $cantidad</option>";
            }
        } else {
            echo "<option value='0'>No hay semillas en Stock</option>";
        }
    } else if ($consulta == "modificar_semillas_pedido") {
        $id_stock_semillas = $_POST["id_stock_semillas"];
        $cantidad = $_POST["cantidad"];
        $id_artpedido = $_POST["id_artpedido"];

        mysqli_autocommit($con, FALSE);

        $query = "INSERT INTO semillas_pedidos (
        id_stock_semillas,
        cantidad,
        id_artpedido
    )   VALUES (
        $id_stock_semillas,
        $cantidad,
        $id_artpedido
    )
    ";

        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        $query = "UPDATE articulospedidos SET cant_semillas = cant_semillas + $cantidad WHERE id = $id_artpedido;";

        if (!mysqli_query($con, $query)) {
            $errors[] = mysqli_error($con);
        }

        if (count($errors) === 0) {
            if (mysqli_commit($con)) {
                echo "success";
            } else {
                mysqli_rollback($con);
            }
        } else {
            mysqli_rollback($con);
            print_r($errors);
        }
        mysqli_close($con);
    } else if ($consulta == "cargar_semillas_select_modificar") {
        $tipo = $_POST["tipo"];
        $id = $_POST["id"];
        $id_cliente = $_POST["id_cliente"];
        $id_artpedido = $_POST["id_artpedido"];
        $plantinera = $_POST["plantinera"];

        $wherecliente = $plantinera == "1" || $plantinera == 1 ?
            "AND (s.id_cliente = $id_cliente OR s.id_cliente = 1)" :
            "AND s.id_cliente = $id_cliente";

        if ($tipo == "S") {
            $where = " s.id_variedad = $id";
        } else {
            $where = " s.id_especie = $id";
        }

        $query = "SELECT
          s.id_stock,
          s.id_cliente,
          s.cantidad,
          UPPER(s.codigo) as codigo,
          s.porcentaje,
          DATE_FORMAT(s.fecha, '%d/%m/%y') as fecha_stock,
          DATE_FORMAT(s.fecha, '%y%m%d') as fecha_stock_raw,
          m.nombre as nombre_marca,
          p.nombre as nombre_proveedor,
          (SELECT IFNULL(SUM(sr.cantidad),0) FROM stock_semillas_retiros sr WHERE sr.id_stock = s.id_stock) as cantidad_retirada
          FROM stock_semillas s
          LEFT JOIN variedades_producto v ON v.id = s.id_variedad
          LEFT JOIN especies_provistas e ON e.id = s.id_especie
          INNER JOIN tipos_producto t ON t.id = v.id_tipo OR t.id = e.id_tipo
          INNER JOIN semillas_marcas m ON m.id = s.id_marca
          INNER JOIN semillas_proveedores p ON p.id = s.id_proveedor
          WHERE $where $wherecliente
          AND s.id_stock NOT IN (SELECT sp.id_stock_semillas FROM semillas_pedidos sp WHERE sp.id_artpedido = $id_artpedido)
          GROUP BY s.id_stock
          ORDER BY s.fecha DESC
          ;
          ";

        $val = mysqli_query($con, $query);
        if (mysqli_num_rows($val) > 0) {
            while ($re = mysqli_fetch_array($val)) {
                $cantidad = $re["cantidad"] - $re["cantidad_retirada"];
                $roel = $re["id_cliente"] == "1" || $re["id_cliente"] == 1 ?
                    "ROEL-" : "";
                if ($cantidad > 0)
                    echo "<option value='$re[id_stock]' x-codigo=\"$roel-$re[codigo]\" x-id-stock='$re[id_stock]' x-cantidad='$cantidad'>$roel$re[codigo] [$re[nombre_marca] - $re[nombre_proveedor]] $re[porcentaje]% - Cant: $cantidad</option>";
            }
        } else {
            echo "<option value='0'>No hay semillas en Stock</option>";
        }
    }

} catch (\Throwable $th) {
    throw $th;
}

