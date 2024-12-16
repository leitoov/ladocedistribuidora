<?php
// Modificación de `products.php` para la búsqueda de productos
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

// Conectar con la base de datos usando PDO
global $pdo;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Buscar productos a partir de un término (al menos 3 letras)
        $termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
        if (strlen($termino) < 3) {
            http_response_code(400);
            echo json_encode(["message" => "El término de búsqueda debe tener al menos 3 caracteres"]);
            exit();
        }

        try {
            // Seleccionar todas las columnas de productos que coincidan con el término
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre LIKE :termino OR descripcion LIKE :termino");
            $stmt->execute(['termino' => "%$termino%"]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $productosValidos = [];
            $exclusiones = [];

            foreach ($productos as $producto) {
                $tienePrecioUnitario = $producto['precio_unitario'] > 0;
                $tieneStockUnitario = $producto['stock_unidad'] > 0;

                $tienePrecioPack = $producto['precio_pack'] > 0;
                $tieneStockPack = $producto['stock_pack'] > 0;

                // Verificar si el producto tiene al menos una opción válida (unitario o pack)
                if (($tienePrecioUnitario && $tieneStockUnitario) || ($tienePrecioPack && $tieneStockPack)) {
                    // El producto es válido, se incluye en la lista
                    $productosValidos[] = [
                        "id" => $producto['id'],
                        "nombre" => $producto['nombre'],
                        "descripcion" => $producto['descripcion'],
                        "precio_unitario" => $tienePrecioUnitario ? $producto['precio_unitario'] : null,
                        "stock_unidad" => $tieneStockUnitario ? $producto['stock_unidad'] : null,
                        "precio_pack" => $tienePrecioPack ? $producto['precio_pack'] : null,
                        "stock_pack" => $tieneStockPack ? $producto['stock_pack'] : null,
                        "estado" => $producto['estado'],
                        "categoria" => $producto['categoria'],
                        "aplica_descuento" => $producto['aplica_descuento'],
                        "liberar" => $producto['liberar']
                    ];
                } else {
                    // El producto no cumple con las reglas, se incluye en las exclusiones
                    $razonExclusion = [];

                    if (!$tienePrecioUnitario && !$tienePrecioPack) {
                        $razonExclusion[] = "No tiene precios válidos";
                    }
                    if (!$tieneStockUnitario && !$tieneStockPack) {
                        $razonExclusion[] = "No tiene stock disponible";
                    }

                    $exclusiones[] = [
                        "id" => $producto['id'],
                        "nombre" => $producto['nombre'],
                        "razones" => $razonExclusion
                    ];
                }
            }

            echo json_encode([
                "productos" => $productosValidos,
                "exclusiones" => $exclusiones
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al buscar productos", "error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
