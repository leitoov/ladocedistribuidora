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
            // Seleccionar todas las columnas de productos
            $stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre LIKE :termino OR descripcion LIKE :termino LIMIT 10");
            $stmt->execute(['termino' => "%$termino%"]);
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($productos)) {
                echo json_encode(["message" => "No se encontraron productos con el término especificado"]);
                exit();
            }
            echo json_encode($productos);
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
