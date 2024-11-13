<?php
require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');
$headers = getallheaders();
echo json_encode($headers);
exit();
// Intentar capturar el encabezado Authorization de diferentes maneras
$headers = getallheaders();  // Cambia apache_request_headers() por getallheaders()

// Verificar varias posibles ubicaciones del encabezado Authorization
$authHeader = null;
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
}

if (!$authHeader) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

// Extraer el token JWT del encabezado Authorization
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
