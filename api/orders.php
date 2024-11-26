<?php
require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');

// Obtener los encabezados HTTP correctamente
$headers = apache_request_headers(); // Alternativa: getallheaders()

if (!isset($headers['Authorization'])) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

$authHeader = $headers['Authorization'];
$jwt = str_replace("Bearer ", "", $authHeader);

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

// Procesar solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id_cliente = isset($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : 0;

    try {
        // Consulta de pedidos
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
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error al obtener pedidos: " . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
