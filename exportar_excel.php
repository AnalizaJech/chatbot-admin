<?php
include 'functions.php';
$pdo = new PDO('mysql:host=localhost;dbname=plazavea', 'root', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta = $_POST['pregunta'] ?? '';
    $datos = procesarPregunta($pdo, $pregunta);

    // Limpia el HTML para que sea compatible con Excel
    $datos = str_replace('<table class="table">', '<table border="1">', $datos);
    $datos = str_replace('<thead>', '', $datos);
    $datos = str_replace('</thead>', '', $datos);
    $datos = str_replace('<tbody>', '', $datos);
    $datos = str_replace('</tbody>', '', $datos);
    $datos = str_replace('<tr>', '<tr>', $datos);
    $datos = str_replace('<th>', '<td><b>', $datos);
    $datos = str_replace('</th>', '</b></td>', $datos);
    $datos = str_replace('<td>', '<td>', $datos);

    if (!empty($datos)) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="datos.xls"');
        echo "<table border='1'>";
        echo "<tr><th>Pregunta</th></tr>";
        echo "<tr><td>{$pregunta}</td></tr>";
        echo $datos;
        echo "</table>";
        exit();
    } else {
        echo "No se encontraron datos para exportar.";
    }
}
?>
