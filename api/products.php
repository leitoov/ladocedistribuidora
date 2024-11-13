<?php
require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');

// Obtener el token del encabezado Authorization
$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

// Extraer el token JWT del encabezado Authorization
$authHeader = $headers['Authorization'];
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
