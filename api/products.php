<?php
require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');

// Intentar capturar el encabezado Authorization de diferentes maneras
$headers = getallheaders();
$authHeader = null;

if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

// Depuración: Ver los encabezados y el valor de Authorization si existe
if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado", "headers" => $headers]);
    exit();
}

// Extraer el token JWT del encabezado
$jwt = str_replace("Bearer ", "", $authHeader);

// Verificar el token usando la función `verifyJWT`
$user_id = verifyJWT($jwt, $jwt_secret);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["message" => "Token inválido o expirado"]);
    exit();
}

// Si el token es válido, continuar con la lógica del endpoint
echo json_encode(["message" => "Token válido", "user_id" => $user_id]);
