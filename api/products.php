<?php
require '../config.php';  // Archivo de conexión a la base de datos
require '../verify_token.php';  // Archivo con la función de verificación del token

header('Content-Type: application/json');

// Obtener el token JWT desde la URL o el cuerpo de la solicitud
$jwt = null;
if (isset($_GET['token'])) {
    $jwt = $_GET['token'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $jwt = isset($data->token) ? $data->token : null;
}

if (!$jwt) {
    http_response_code(401);
    echo json_encode(["message" => "Token no proporcionado"]);
    exit();
}

// Verificar el token JWT
$user_id = verifyJWT($jwt, $jwt_secret);
if (!$user_id) {
    http_response_code(401);
    echo json_encode(["message" => "Token inválido o expirado"]);
    exit();
}

// Si el token es válido, obtener y devolver la lista de productos desde la base de datos
try {
    $stmt = $pdo->prepare("SELECT id, nombre, descripcion, precio, stock FROM productos");
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "message" => "Productos obtenidos correctamente",
        "productos" => $productos
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Error al obtener los productos", "error" => $e->getMessage()]);
}
