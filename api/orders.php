<?php
require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');
session_start();

// Verificar el token de sesión
if (!isset($_SESSION['token'])) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

$jwt_secret = 'Adeleteamo1988@';
try {
    $tokenData = verifyJWT($_SESSION['token'], $jwt_secret);
    if (!$tokenData) {
        throw new Exception('Token inválido o expirado.');
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => $e->getMessage()]);
    exit();
}

// Obtener solo los pedidos pendientes de tipo "Caja"
try {
    $stmt = $pdo->prepare("
        SELECT p.id AS pedido_id, p.nombre_cliente, p.total, p.estado, p.tipo_pedido, p.fecha, 
               u.nombre AS vendedor
        FROM pedidos p
        LEFT JOIN usuarios u ON p.id_usuario = u.id
        WHERE p.tipo_pedido = 'Caja' AND p.estado = 'Pendiente'
        ORDER BY p.fecha DESC
    ");
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($pedidos) {
        echo json_encode($pedidos);
    } else {
        echo json_encode(["message" => "No hay pedidos pendientes de tipo 'Caja'."]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error al obtener los pedidos pendientes", "error" => $e->getMessage()]);
}
