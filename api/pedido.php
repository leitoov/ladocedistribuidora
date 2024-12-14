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

// Verificar que los datos sean válidos
if (empty($productos) || empty($cliente)) {
    http_response_code(400);
    echo json_encode(["message" => "Datos del pedido incompletos"]);
    exit();
}

try {
    // Verificar stock disponible antes de iniciar la transacción
    foreach ($productos as $producto) {
        $stmtCheckStock = $pdo->prepare(
            $producto['tipo'] === 'unidad' ?
                "SELECT stock_unidad FROM productos WHERE id = :id" :
                "SELECT stock_pack FROM productos WHERE id = :id"
        );
        $stmtCheckStock->execute(['id' => $producto['id']]);
        $stockDisponible = $stmtCheckStock->fetchColumn();

        if ($stockDisponible === false || $stockDisponible < $producto['cantidad']) {
            throw new Exception("Stock insuficiente para el producto ({$producto['tipo']}) con ID: {$producto['id']}");
        }
    }

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

        $idCliente = 9999; // ID genérico para cliente no registrado
        $nombreCliente = $cliente; // Guardar el nombre ingresado del cliente
    }

    // Determinar el estado del pedido según el tipo
    $estadoPedido = ($tipoPedido === 'Reparto') ? 'Confirmado' : 'Pendiente';

    // Insertar el pedido en la tabla `pedidos`
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, nombre_cliente, id_usuario, total, estado, tipo_pedido) 
        VALUES (:cliente, :nombre_cliente, :usuario_id, :total, :estado, :tipo_pedido)");

    $total = array_reduce($productos, function ($acc, $producto) {
        $precio = $producto['tipo'] === 'unidad' ? $producto['precio_unitario'] : $producto['precio_pack'];
        return $acc + ($precio * $producto['cantidad']);
    }, 0);

    $stmt->execute([
        'cliente' => $idCliente,
        'nombre_cliente' => $nombreCliente,
        'usuario_id' => $tokenData->user_id, // ID del vendedor que genera el pedido
        'total' => $total,
        'estado' => $estadoPedido, // Asignar estado según el tipo de pedido
        'tipo_pedido' => $tipoPedido
    ]);

    $pedidoId = $pdo->lastInsertId();

    // Preparar consultas para actualizar el stock según el tipo
    $stmtDetalle = $pdo->prepare("
        INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio, tipo) 
        VALUES (:pedido_id, :producto_id, :cantidad, :precio, :tipo)
    ");
    $stmtUpdateStock = $pdo->prepare("
        UPDATE productos SET 
            stock_unidad = CASE WHEN :tipo = 'unidad' THEN stock_unidad - :cantidad ELSE stock_unidad END,
            stock_pack = CASE WHEN :tipo = 'pack' THEN stock_pack - :cantidad ELSE stock_pack END
        WHERE id = :producto_id AND (
            (:tipo = 'unidad' AND stock_unidad >= :cantidad) OR
            (:tipo = 'pack' AND stock_pack >= :cantidad)
        )
    ");

    foreach ($productos as $producto) {
        $precio = $producto['tipo'] === 'unidad' ? $producto['precio_unitario'] : $producto['precio_pack'];

        $stmtDetalle->execute([
            'pedido_id' => $pedidoId,
            'producto_id' => $producto['id'],
            'cantidad' => $producto['cantidad'],
            'precio' => $precio,
            'tipo' => $producto['tipo']
        ]);

        $stmtUpdateStock->execute([
            'producto_id' => $producto['id'],
            'cantidad' => $producto['cantidad'],
            'tipo' => $producto['tipo']
        ]);

        if ($stmtUpdateStock->rowCount() === 0) {
            throw new Exception("Stock insuficiente para el producto ({$producto['tipo']}) con ID: {$producto['id']}");
        }
    }

    // Insertar en historial_pedidos
    $stmtHistorial = $pdo->prepare("
        INSERT INTO historial_pedidos (id_pedido, estado, fecha, id_usuario) 
        VALUES (:id_pedido, :estado, NOW(), :id_usuario)
    ");
    $stmtHistorial->execute([
        'id_pedido' => $pedidoId,
        'estado' => $estadoPedido,
        'id_usuario' => $tokenData->user_id
    ]);

    $historialId = $pdo->lastInsertId(); // Capturar el ID del historial

    // Confirmar la transacción
    $pdo->commit();

    // Respuesta exitosa
    echo json_encode([
        "message" => "Pedido generado correctamente",
        "pedido_id" => $pedidoId,
        "historial_id" => $historialId, // Incluir ID del historial
        "total" => $total,
        "estado" => $estadoPedido
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Error al generar el pedido", "error" => $e->getMessage()]);
}
