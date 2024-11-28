<?php
require '../config.php';
require '../verify_token.php';
require 'fpdf.php';

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

        // Usar el ID genérico para clientes no registrados
        $idCliente = 9999; // ID genérico para cliente no registrado
        $nombreCliente = $cliente; // Guardar el nombre ingresado del cliente
    }

    // Determinar el estado del pedido
    $estadoPedido = ($tipoPedido === 'Reparto') ? 'Confirmado' : 'Pendiente';

    // Insertar el pedido
    $stmt = $pdo->prepare("INSERT INTO pedidos (id_cliente, nombre_cliente, total, estado, tipo_pedido) VALUES (:cliente, :nombre_cliente, :total, :estado, :tipo_pedido)");
    $total = array_reduce($productos, function ($acc, $producto) {
        return $acc + ($producto['precio'] * $producto['cantidad']);
    }, 0);
    $stmt->execute([
        'cliente' => $idCliente,
        'nombre_cliente' => $nombreCliente,
        'total' => $total,
        'estado' => $estadoPedido,
        'tipo_pedido' => $tipoPedido
    ]);

    $pedidoId = $pdo->lastInsertId();

    // Insertar los productos en detalle_pedido y actualizar el stock
    $stmtDetalle = $pdo->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio) VALUES (:pedido_id, :producto_id, :cantidad, :precio)");
    $stmtUpdateStock = $pdo->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id = :producto_id AND stock >= :cantidad");

    foreach ($productos as $producto) {
        // Insertar detalle del pedido
        $stmtDetalle->execute([
            'pedido_id' => $pedidoId,
            'producto_id' => $producto['id'],
            'cantidad' => $producto['cantidad'],
            'precio' => $producto['precio']
        ]);

        // Actualizar el stock del producto
        $stmtUpdateStock->execute([
            'producto_id' => $producto['id'],
            'cantidad' => $producto['cantidad']
        ]);

        // Verificar si se actualizó correctamente el stock
        if ($stmtUpdateStock->rowCount() === 0) {
            throw new Exception('Stock insuficiente para el producto con ID: ' . $producto['id']);
        }
    }

    // Confirmar la transacción
    $pdo->commit();

    // Generar e imprimir el PDF si el pedido es de tipo "Reparto"
    if ($tipoPedido === 'Reparto') {
        generarPDFPedido($pedidoId, $nombreCliente, $productos, $total);
        echo json_encode([
            "message" => "Pedido generado correctamente. Tipo: Reparto. Estado: Confirmado. Imprimiendo pedido...",
            "print" => true
        ]);
    } else {
        echo json_encode(["message" => "Pedido generado correctamente"]);
    }
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["message" => "Error al generar el pedido", "error" => $e->getMessage()]);
}

// Función para generar el PDF del pedido
function generarPDFPedido($pedidoId, $nombreCliente, $productos, $total)
{
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Distribuidora La Doce');
    $pdf->Ln(10);

    // Mostrar información del cliente
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Cliente: ' . $nombreCliente, 0, 1);
    $pdf->Cell(0, 10, 'ID Pedido: ' . $pedidoId, 0, 1);
    $pdf->Cell(0, 10, 'Fecha: ' . date('Y-m-d H:i:s'), 0, 1);
    $pdf->Ln(5);

    // Tabla de productos
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(30, 10, 'Codigo', 1);
    $pdf->Cell(50, 10, 'Descripcion', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(30, 10, 'P. Unitario', 1);
    $pdf->Cell(30, 10, 'Total', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 10);
    foreach ($productos as $producto) {
        $pdf->Cell(30, 10, $producto['id'], 1);
        $pdf->Cell(50, 10, $producto['nombre'], 1);
        $pdf->Cell(30, 10, $producto['cantidad'], 1);
        $pdf->Cell(30, 10, number_format($producto['precio'], 2), 1);
        $pdf->Cell(30, 10, number_format($producto['cantidad'] * $producto['precio'], 2), 1);
        $pdf->Ln();
    }

    // Total del pedido
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Total: $' . number_format($total, 2), 0, 1, 'R');

    // Guardar y emitir el PDF
    $pdf->Output('I', 'Pedido_' . $pedidoId . '.pdf');
}
