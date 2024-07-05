<?php
$dsn = 'mysql:host=localhost;dbname=plazavea;charset=utf8mb4';
$username = 'root';  // Asegúrate de usar tus propios credenciales
$password = '';      // Asegúrate de usar tus propios credenciales

try {
    $pdo = new PDO($dsn, $username, $password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>
