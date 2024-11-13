<?php
// config.php
$host = 'localhost';
$db_name = 'c2620852_ladoce';
$username = 'c2620852_ladoce';
$password = 'ravoSEku18';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

// Clave secreta para JWT
$jwt_secret = 'clave_secreta_segura';
