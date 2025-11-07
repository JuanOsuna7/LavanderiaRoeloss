<?php
$host = 'localhost';
$dbname = 'lavanderiaroeloss'; // 🔹 cambia por el nombre real de tu BD
$username = 'root';
$password = 'mysql';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
