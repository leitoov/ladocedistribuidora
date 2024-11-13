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
    $id_cliente = isset($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : 0;

    // Consulta de pedidos con detalles para un cliente específico
    $stmt = $pdo->prepare("
        SELECT 
            p.id AS pedido_id,
            p.fecha,
            p.total,
            p.estado,
            dp.id_producto,
            prod.nombre AS producto_nombre,
            dp.cantidad,
            dp.precio AS precio_producto
        FROM pedidos p
        LEFT JOIN detalle_pedido dp ON p.id = dp.id_pedido
        LEFT JOIN productos prod ON dp.id_producto = prod.id
        WHERE p.id_cliente = :id_cliente
    ");
    $stmt->execute(['id_cliente' => $id_cliente]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
