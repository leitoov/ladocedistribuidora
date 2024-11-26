<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verificar si el token está presente en la sesión
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
} catch (Exception $e) {
    // En caso de error, destruir la sesión y redirigir al login
    session_destroy();
    header('Location: index.html');
    exit();
}

// Extraer información del token
$userRole = $tokenData->rol; // rol: 'vendedor', 'caja', 'admin'

// Redirigir según el rol
switch ($userRole) {
    case 'vendedor':
        header('Location: dashboard_vendedor.php');
        break;
    case 'caja':
        header('Location: dashboard_caja.php');
        break;
    case 'admin':
        header('Location: dashboard_admin.php');
        break;
    default:
        // Si el rol no es válido, destruir la sesión
        session_destroy();
        header('Location: index.html');
        break;
}
exit();
