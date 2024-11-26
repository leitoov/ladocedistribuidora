<?php
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
} catch (Exception $e) {
    session_destroy();
    header('Location: index.html');
    exit();
}

// Redirigir según el rol
$userRole = $tokenData->rol;

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
        session_destroy();
        header('Location: index.html');
}
exit();
