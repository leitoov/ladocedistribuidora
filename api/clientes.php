<?php
require '../config.php';

header('Content-Type: application/json');

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Buscar clientes por término
    $termino = $_GET['termino'] ?? '';
    
    if (strlen($termino) < 3) {
        echo json_encode(["message" => "Debe ingresar al menos 3 caracteres para buscar."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT id, nombre, direccion, telefono, email
            FROM clientes
            WHERE nombre LIKE :termino
               OR direccion LIKE :termino
               OR telefono LIKE :termino
               OR email LIKE :termino
        ");
        $stmt->execute(['termino' => "%$termino%"]);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($clientes) {
            echo json_encode($clientes);
        } else {
            echo json_encode(["message" => "No se encontraron clientes con el término proporcionado."]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error al buscar clientes", "error" => $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar un nuevo cliente
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'] ?? null;

    if (!$nombre) {
        http_response_code(400);
        echo json_encode(["message" => "El nombre del cliente es obligatorio."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre) VALUES (:nombre)");
        $stmt->execute(['nombre' => $nombre]);

        echo json_encode(["message" => "Cliente agregado exitosamente", "id" => $pdo->lastInsertId()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error al agregar el cliente", "error" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
?>