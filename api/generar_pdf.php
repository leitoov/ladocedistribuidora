<?php
require('fpdf.php');
require '../config.php';

function generarPDFPedido($pedidoId)
{
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=c2620852_ladoce", 'root', ''); // Cambia a tus credenciales de base de datos
        
        // Obtener datos del pedido
        $stmtPedido = $pdo->prepare("SELECT p.id_cliente, p.nombre_cliente, p.total, p.estado, p.tipo_pedido, p.fecha, c.nombre AS cliente_nombre
                                    FROM pedidos p
                                    LEFT JOIN clientes c ON p.id_cliente = c.id
                                    WHERE p.id = :pedido_id");
        $stmtPedido->execute(['pedido_id' => $pedidoId]);
        $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            throw new Exception("Pedido no encontrado.");
        }

        // Obtener productos del pedido
        $stmtProductos = $pdo->prepare("SELECT dp.id_producto, dp.cantidad, dp.precio, pr.nombre AS producto_nombre
                                        FROM detalle_pedido dp
                                        JOIN productos pr ON dp.id_producto = pr.id
                                        WHERE dp.id_pedido = :pedido_id");
        $stmtProductos->execute(['pedido_id' => $pedidoId]);
        $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

        if (empty($productos)) {
            throw new Exception("No se encontraron productos en el pedido.");
        }

    } catch (PDOException $e) {
        throw new Exception("Error al conectar con la base de datos: " . $e->getMessage());
    }

    // Generar el PDF con FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Distribuidora La Doce');
    $pdf->Ln(10);
    
    // Mostrar información del cliente
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Cliente: ' . $pedido['nombre_cliente'], 0, 1);
    $pdf->Cell(0, 10, 'ID Pedido: ' . $pedidoId, 0, 1);
    $pdf->Cell(0, 10, 'Fecha: ' . $pedido['fecha'], 0, 1);
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
        $pdf->Cell(30, 10, $producto['id_producto'], 1);
        $pdf->Cell(50, 10, $producto['producto_nombre'], 1);
        $pdf->Cell(30, 10, $producto['cantidad'], 1);
        $pdf->Cell(30, 10, $producto['precio'], 1);
        $pdf->Cell(30, 10, $producto['cantidad'] * $producto['precio'], 1);
        $pdf->Ln();
    }
    
    // Total del pedido
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Total: $' . number_format($pedido['total'], 2), 0, 1, 'R');

    // Guardar y emitir el PDF
    $pdf->Output('I', 'Pedido_' . $pedidoId . '.pdf');
}
