<?php
// config.php

$dsn = 'mysql:host=localhost;dbname=c2620852_ladoce;charset=utf8mb4';
$username = 'c2620852_ladoce';
$password = 'ravoSEku18';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Error en la conexión a la base de datos: ' . $e->getMessage());
}