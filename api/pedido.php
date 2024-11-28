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

// Obtener los datos del pedido
$data = json_decode(file_get_contents("php://input"), true);
$cliente = $data['cliente'] ?? null;
$tipoPedido = $data['tipoPedido'] ?? null; // Aquí cambie `tipo_pedido` por `tipoPedido` para coincidir con el JSON del frontend
$productos = $data['productos'] ?? [];

if (!$cliente || !$tipoPedido || empty($productos)) {
    http_response_code(400);
    echo json_encode(["message" => "Datos del pedido incompletos"]);
    exit();
}

try {
    // Iniciar transacción para asegurar la consistencia del pedido y el stock
    $pdo->beginTransaction();

    // Insertar el pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, tipo_pedido, fecha, total, estado) VALUES (:cliente, :tipo_pedido, NOW(), :total, 'Pendiente')");
    $total = array_reduce($productos, function ($acc, $producto) {
        return $acc + ($producto['precio'] * $producto['cantidad']);
    }, 0);
    $stmt->execute([
        'cliente' => $cliente,
        'tipo_pedido' => $tipoPedido,
        'total' => $total
    ]);

    $pedidoId = $pdo->lastInsertId();

    // Insertar los productos en detalle_pedido
    $stmtDetalle = $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (:pedido_id, :producto_id, :cantidad, :precio)");
    foreach ($productos as $producto) {
        $stmtDetalle->execute([
            'pedido_id' => $pedidoId,
            'producto_id' => $producto['id'],
            'cantidad' => $producto['cantidad'],
            'precio' => $producto['precio']
        ]);
    }

    // Confirmar la transacción
    $pdo->commit();

    echo json_encode(["message" => "Pedido generado correctamente"]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Error al generar el pedido", "error" => $e->getMessage()]);
}
