<?php
require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');

// Obtener el cuerpo de la solicitud
$data = json_decode(file_get_contents("php://input"), true);
$jwt = isset($data['token']) ? $data['token'] : null;

if (!$jwt) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

// Verificar el token JWT
try {
    $tokenData = verifyJWT($jwt, $jwt_secret);
    if (!$tokenData) {
        throw new Exception('Token inválido o expirado.');
    }
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(["message" => $e->getMessage()]);
    exit();
}

// Procesar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = isset($data['id_cliente']) ? (int)$data['id_cliente'] : 0;

    try {
        // Consulta de pedidos pendientes de tipo 'Caja'
        $stmt = $pdo->prepare("
            SELECT 
                p.id AS pedido_id,
                p.fecha,
                p.total,
                p.estado,
                p.tipo_pedido,
                c.nombre AS cliente,
                dp.id_producto,
                prod.nombre AS producto_nombre,
                dp.cantidad,
                dp.precio AS precio_producto
            FROM pedidos p
            LEFT JOIN detalle_pedido dp ON p.id = dp.id_pedido
            LEFT JOIN productos prod ON dp.id_producto = prod.id
            LEFT JOIN clientes c ON p.id_cliente = c.id
            WHERE p.id_cliente = :id_cliente AND p.tipo_pedido = 'Caja' AND p.estado = 'Pendiente'
        ");
        $stmt->execute(['id_cliente' => $id_cliente]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($orders);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error al obtener pedidos: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
