<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verificar si el usuario tiene un token en la sesión
if (!isset($_SESSION['token'])) {
    header('Location: index.html');
    exit();
}

// Incluir función para verificar el token
require 'verify_token.php';
$jwt_secret = 'Adeleteamo1988@';

try {
    // Verificar y decodificar el token
    $tokenData = verifyJWT($_SESSION['token'], $jwt_secret);
    if (!$tokenData) {
        throw new Exception('Token inválido o expirado.');
    }

    // Extraer información del rol desde el token
    $userRole = strtolower($tokenData->rol); // Rol en minúsculas: 'vendedor', 'caja', 'admin'

    // Redirigir a la vista correspondiente según el rol
    switch ($userRole) {
        case 'vendedor':
            header('Location: dashboard_vendedor.php');
            exit();
        case 'caja':
            header('Location: dashboard_caja.php');
            exit();
        case 'admin':
            header('Location: dashboard_admin.php');
            exit();
        default:
            throw new Exception('Rol no reconocido.');
    }
} catch (Exception $e) {
    // Redirigir al login si el token no es válido
    session_destroy();
    header('Location: index.html');
    exit();
}
