<?php
require '../config.php';
require '../verify_token.php';

$headers = apache_request_headers();
if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

$authHeader = $headers['Authorization'];
$jwt = str_replace("Bearer ", "", $authHeader);
$user_id = verifyJWT($jwt, $jwt_secret);

if (!$user_id) {
    http_response_code(401);
    echo json_encode(["message" => "Token inválido o expirado"]);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE estado = 'Disponible'");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($products);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
