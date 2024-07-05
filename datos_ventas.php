<?php
$pdo = new PDO('mysql:host=localhost;dbname=plazavea', 'root', '');

$stmt = $pdo->query("SELECT MONTH(fecha_venta) AS mes, SUM(total_venta) AS total FROM ventas GROUP BY MONTH(fecha_venta)");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

$meses = [];
$ventas = [];

foreach ($result as $row) {
    $meses[] = $row['mes'];
    $ventas[] = $row['total'];
}

header('Content-Type: application/json');
echo json_encode(['meses' => $meses, 'ventas' => $ventas]);
?>
