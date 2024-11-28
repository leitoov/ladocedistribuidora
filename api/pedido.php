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
$tipoPedido = $data['tipoPedido'] ?? null;
$productos = $data['productos'] ?? [];

if (empty($productos) || empty($cliente)) {
    http_response_code(400);
    echo json_encode(["message" => "Datos del pedido incompletos"]);
    exit();
}

try {
    // Iniciar transacción para asegurar la consistencia del pedido y el stock
    $pdo->beginTransaction();

    // Verificar si el cliente existe
    $stmtCliente = $pdo->prepare("SELECT id, nombre FROM clientes WHERE nombre = :nombre LIMIT 1");
    $stmtCliente->execute(['nombre' => $cliente]);
    $clienteData = $stmtCliente->fetch(PDO::FETCH_ASSOC);

    if ($clienteData) {
        $idCliente = $clienteData['id'];
        $nombreCliente = $clienteData['nombre'];
    } else {
        // Verificar si el cliente genérico ya existe, si no, insertarlo
        $stmtGenCliente = $pdo->prepare("SELECT id FROM clientes WHERE id = 9999 LIMIT 1");
        $stmtGenCliente->execute();
        $genClienteData = $stmtGenCliente->fetch(PDO::FETCH_ASSOC);

        if (!$genClienteData) {
            // Insertar cliente genérico si no existe
            $stmtInsertGen = $pdo->prepare("INSERT INTO clientes (id, nombre) VALUES (9999, 'Cliente Genérico')");
            $stmtInsertGen->execute();
        }

        // Después de intentar insertar, verificar que el cliente genérico ahora exista
        $stmtGenCliente->execute(); // Volver a ejecutar para verificar
        $genClienteData = $stmtGenCliente->fetch(PDO::FETCH_ASSOC);
        if (!$genClienteData) {
            throw new Exception('No se pudo insertar el cliente genérico.');
        }

        // Usar el ID genérico para clientes no registrados
        $idCliente = 9999; // ID genérico para cliente no registrado
        $nombreCliente = $cliente; // Guardar el nombre ingresado del cliente
    }

    // Insertar el pedido (asegurándonos de usar el campo correcto)
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, nombre_cliente, pedido, fecha, total, estado) VALUES (:cliente, :nombre_cliente, :pedido, NOW(), :total, 'Pendiente')");
    $total = array_reduce($productos, function ($acc, $producto) {
        return $acc + ($producto['precio'] * $producto['cantidad']);
    }, 0);
    $stmt->execute([
        'cliente' => $idCliente,
        'nombre_cliente' => $nombreCliente,
        'pedido' => $tipoPedido,
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
