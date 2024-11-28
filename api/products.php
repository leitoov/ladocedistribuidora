<?php
require '../config.php';  // Archivo de conexión a la base de datos
require '../verify_token.php';  // Archivo con la función de verificación del token

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

// Conectar con la base de datos usando PDO
global $pdo;

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Buscar productos (puede incluir filtro de búsqueda)
        $termino = isset($_GET['termino']) ? trim($_GET['termino']) : '';
        try {
            if ($termino) {
                $stmt = $pdo->prepare("SELECT * FROM productos WHERE nombre LIKE :termino AND stock > 0");
                $stmt->execute(['termino' => "%$termino%"]);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM productos");
                $stmt->execute();
            }

            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($productos);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al obtener productos", "error" => $e->getMessage()]);
        }
        break;

    case 'POST':
        // Añadir un nuevo producto
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->nombre, $data->descripcion, $data->precio, $data->stock)) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, stock) VALUES (:nombre, :descripcion, :precio, :stock)");
            $stmt->execute([
                'nombre' => $data->nombre,
                'descripcion' => $data->descripcion,
                'precio' => $data->precio,
                'stock' => $data->stock
            ]);

            echo json_encode(["message" => "Producto añadido correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al añadir producto", "error" => $e->getMessage()]);
        }
        break;

    case 'PUT':
        // Modificar un producto existente
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id, $data->nombre, $data->descripcion, $data->precio, $data->stock)) {
            http_response_code(400);
            echo json_encode(["message" => "Datos incompletos"]);
            exit();
        }

        try {
            $stmt = $pdo->prepare("UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock = :stock WHERE id = :id");
            $stmt->execute([
                'nombre' => $data->nombre,
                'descripcion' => $data->descripcion,
                'precio' => $data->precio,
                'stock' => $data->stock,
                'id' => $data->id
            ]);

            echo json_encode(["message" => "Producto modificado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al modificar producto", "error" => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Eliminar un producto
        $data = json_decode(file_get_contents("php://input"));
        if (!isset($data->id)) {
            http_response_code(400);
            echo json_encode(["message" => "ID del producto no proporcionado"]);
            exit();
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM productos WHERE id = :id");
            $stmt->execute(['id' => $data->id]);

            echo json_encode(["message" => "Producto eliminado correctamente"]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al eliminar producto", "error" => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Método no permitido"]);
        break;
}
