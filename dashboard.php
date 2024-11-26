<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Verificar si el usuario tiene un token en la sesión
if (!isset($_SESSION['token'])) {
  echo 'NO';
    //header('Location: index.html');
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
    $userRole = ucfirst(strtolower($tokenData->rol)); // 'Vendedor', 'Caja', 'Administrador'

    // Redirigir a la vista correspondiente según el rol
    switch ($userRole) {
        case 'Vendedor':
            header('Location: dashboard_vendedor.php');
            exit();
        case 'Caja':
            header('Location: dashboard_caja.php');
            exit();
        case 'Administrador':
            header('Location: dashboard_admin.php');
            exit();
        default:
            throw new Exception('Rol no reconocido.');
    }
} catch (Exception $e) {
    // Redirigir al login si el token no es válido
    echo 'NO NO';
    session_destroy();
   // header('Location: index.html');
    exit();
}
