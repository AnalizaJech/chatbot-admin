<?php
include 'functions.php';
$pdo = new PDO('mysql:host=localhost;dbname=plazavea', 'root', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pregunta = $_POST['pregunta'] ?? '';
    echo procesarPregunta($pdo, $pregunta);
}
?>
