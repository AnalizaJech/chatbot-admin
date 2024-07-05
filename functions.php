<?php
function procesarPregunta($pdo, $pregunta) {
    $preguntas = [
        "ventas diarias" => "obtenerVentasDiarias",
        "ventas de hoy" => "obtenerVentasDiarias",
        "cumpleaños próximos" => "obtenerProximosCumpleanos",
        "próximos cumpleaños" => "obtenerProximosCumpleanos",
        "pagos próximos" => "obtenerProximosPagos",
        "próximos pagos" => "obtenerProximosPagos",
        "ventas mensuales" => "obtenerVentasMensuales",
        "ventas del mes" => "obtenerVentasMensuales",
        "ventas por producto" => "obtenerVentasPorProducto",
        "inventario actual" => "obtenerInventario",
        "mejores vendedores" => "obtenerMejoresVendedores",
        "alertas de stock" => "alertasDeStock",
        "stock bajo" => "alertasDeStock",
        "gastos operativos" => "obtenerGastosOperativos",
        "clientes frecuentes" => "obtenerClientesFrecuentes",
        "rendimiento de las áreas comerciales" => "rendimientoAreasComerciales",
        "áreas comerciales" => "rendimientoAreasComerciales",
        "historial de compras de un cliente" => "historialComprasCliente",
        "compras de un cliente" => "historialComprasCliente",
        "tendencias de venta" => "tendenciasDeVenta",
        "qué preguntas puedo hacer" => "listaDePreguntas"
    ];

    foreach ($preguntas as $clave => $funcion) {
        if (preg_match("/$clave/i", $pregunta)) {
            return $funcion($pdo);
        }
    }
    return "No estoy seguro de cómo responder a eso.";
}

function obtenerVentasDiarias($pdo) {
    $stmt = $pdo->prepare("SELECT SUM(total_venta) AS total_del_dia FROM ventas WHERE DATE(fecha_venta) = CURDATE()");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result['total_del_dia'] === null) {
        return "No hay ventas registradas para hoy.";
    } else {
        return "El total de ventas para el día " . date("Y-m-d") . " es: $" . number_format($result['total_del_dia'], 2);
    }
}

function obtenerVentasMensuales($pdo) {
    $stmt = $pdo->prepare("SELECT SUM(total_venta) AS total_mes FROM ventas WHERE MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE())");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return "El total de ventas para el mes es: $" . number_format($result['total_mes'], 2);
}

function obtenerVentasPorProducto($pdo) {
    $stmt = $pdo->prepare("SELECT p.nombre_producto, SUM(dv.cantidad * dv.precio_unitario) AS total_producto FROM detalle_ventas dv JOIN productos p ON dv.id_producto = p.id_producto GROUP BY p.nombre_producto");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Producto</th><th>Total Ventas ($)</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre_producto']}</td><td>" . number_format($row['total_producto'], 2) . "</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function obtenerInventario($pdo) {
    $stmt = $pdo->prepare("SELECT p.nombre_producto, i.cantidad FROM productos p JOIN inventario i ON p.id_producto = i.id_producto");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Producto</th><th>Cantidad</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre_producto']}</td><td>{$row['cantidad']}</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function obtenerMejoresVendedores($pdo) {
    $stmt = $pdo->prepare("SELECT t.nombre, t.apellido, SUM(v.total_venta) AS total_ventas FROM trabajadores t JOIN ventas v ON t.id_trabajador = v.id_vendedor GROUP BY t.id_trabajador ORDER BY total_ventas DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Vendedor</th><th>Total Ventas ($)</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre']} {$row['apellido']}</td><td>" . number_format($row['total_ventas'], 2) . "</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function alertasDeStock($pdo) {
    $stmt = $pdo->prepare("SELECT p.nombre_producto, i.cantidad FROM inventario i JOIN productos p ON i.id_producto = p.id_producto WHERE i.cantidad < 10");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Producto</th><th>Cantidad</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre_producto']}</td><td>{$row['cantidad']}</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function obtenerGastosOperativos($pdo) {
    $stmt = $pdo->prepare("SELECT SUM(cantidad) AS total_gastos FROM gastos WHERE MONTH(fecha_gasto) = MONTH(CURDATE()) AND YEAR(fecha_gasto) = YEAR(CURDATE())");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return "El total de gastos operativos para el mes es: $" . number_format($result['total_gastos'], 2);
}

function obtenerClientesFrecuentes($pdo) {
    $stmt = $pdo->prepare("SELECT c.nombre_cliente, COUNT(v.id_venta) AS num_compras FROM clientes c JOIN ventas v ON c.id_cliente = v.id_cliente GROUP BY c.id_cliente ORDER BY num_compras DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Cliente</th><th>Compras</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre_cliente']}</td><td>{$row['num_compras']}</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function rendimientoAreasComerciales($pdo) {
    $stmt = $pdo->prepare("SELECT a.nombre_area, SUM(dv.cantidad * dv.precio_unitario) AS total_ventas FROM areas_comerciales a JOIN productos p ON a.id_area = p.id_area JOIN detalle_ventas dv ON p.id_producto = dv.id_producto GROUP BY a.nombre_area");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Área</th><th>Total Ventas ($)</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre_area']}</td><td>" . number_format($row['total_ventas'], 2) . "</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function historialComprasCliente($pdo) {
    $stmt = $pdo->prepare("SELECT c.nombre_cliente, v.fecha_venta, v.total_venta FROM clientes c JOIN ventas v ON c.id_cliente = v.id_cliente ORDER BY v.fecha_venta DESC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Cliente</th><th>Fecha</th><th>Total ($)</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['nombre_cliente']}</td><td>{$row['fecha_venta']}</td><td>" . number_format($row['total_venta'], 2) . "</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function tendenciasDeVenta($pdo) {
    $stmt = $pdo->prepare("SELECT DATE_FORMAT(fecha_venta, '%Y-%m') AS mes, SUM(total_venta) AS total_mes FROM ventas GROUP BY mes ORDER BY mes");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response = "<table class='table'><thead><tr><th>Mes</th><th>Total Ventas ($)</th></tr></thead><tbody>";
    foreach ($result as $row) {
        $response .= "<tr><td>{$row['mes']}</td><td>" . number_format($row['total_mes'], 2) . "</td></tr>";
    }
    $response .= "</tbody></table>";
    return $response;
}

function obtenerProximosCumpleanos($pdo) {
    $stmt = $pdo->prepare("SELECT nombre, apellido, fecha_nacimiento FROM trabajadores WHERE MONTH(fecha_nacimiento) = MONTH(CURDATE()) AND DAY(fecha_nacimiento) >= DAY(CURDATE()) ORDER BY DAY(fecha_nacimiento) ASC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)) {
        return "No hay cumpleaños próximos registrados.";
    } else {
        $response = "<table class='table'><thead><tr><th>Nombre</th><th>Apellido</th><th>Fecha de Nacimiento</th></tr></thead><tbody>";
        foreach ($result as $persona) {
            $response .= "<tr><td>{$persona['nombre']}</td><td>{$persona['apellido']}</td><td>{$persona['fecha_nacimiento']}</td></tr>";
        }
        $response .= "</tbody></table>";
        return $response;
    }
}

function obtenerProximosPagos($pdo) {
    $stmt = $pdo->prepare("SELECT nombre, apellido, dia_pago FROM trabajadores WHERE MONTH(dia_pago) = MONTH(CURDATE()) AND DAY(dia_pago) >= DAY(CURDATE()) ORDER BY DAY(dia_pago) ASC");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($result)) {
        return "No hay pagos próximos registrados.";
    } else {
        $response = "<table class='table'><thead><tr><th>Nombre</th><th>Apellido</th><th>Día de Pago</th></tr></thead><tbody>";
        foreach ($result as $empleado) {
            $response .= "<tr><td>{$empleado['nombre']}</td><td>{$empleado['apellido']}</td><td>{$empleado['dia_pago']}</td></tr>";
        }
        $response .= "</tbody></table>";
        return $response;
    }
}

function listaDePreguntas() {
    return "Puedes hacer las siguientes preguntas:\n" .
           "1. ¿Cuáles son las ventas diarias?\n" .
           "2. ¿Cuáles son las ventas mensuales?\n" .
           "3. ¿Cuáles son las ventas por producto?\n" .
           "4. ¿Cuál es el inventario actual?\n" .
           "5. ¿Quiénes son los mejores vendedores?\n" .
           "6. ¿Hay alertas de stock bajo?\n" .
           "7. ¿Cuáles son los gastos operativos?\n" .
           "8. ¿Quiénes son los clientes frecuentes?\n" .
           "9. ¿Cuál es el rendimiento de las áreas comerciales?\n" .
           "10. ¿Cuál es el historial de compras de un cliente?\n" .
           "11. ¿Cuáles son las tendencias de venta?\n" .
           "12. ¿Cuáles son los cumpleaños próximos?\n" .
           "13. ¿Cuáles son los pagos próximos?\n";
}
?>
