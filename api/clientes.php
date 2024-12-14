<?php
require '../config.php';

header('Content-Type: application/json');

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        // Buscar cliente por ID
        $id = intval($_GET['id']);
        try {
            $stmt = $pdo->prepare("SELECT id, nombre, direccion, telefono, email FROM clientes WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                echo json_encode($cliente);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Cliente no encontrado"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Error al buscar el cliente", "error" => $e->getMessage()]);
        }
    } elseif (isset($_GET['termino'])) {
        // Buscar clientes por término
        $termino = $_GET['termino'];

        if (strlen($termino) < 3) {
            http_response_code(400);
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
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Debe proporcionar un ID o un término de búsqueda"]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Agregar un nuevo cliente
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = $data['nombre'] ?? null;
    $direccion = $data['direccion'] ?? null;
    $telefono = $data['telefono'] ?? null;

    if (!$nombre || trim($nombre) === "") {
        http_response_code(400);
        echo json_encode(["message" => "El nombre del cliente es obligatorio."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nombre, direccion, telefono) 
            VALUES (:nombre, :direccion, :telefono)
        ");
        $stmt->execute([
            'nombre' => $nombre,
            'direccion' => $direccion,
            'telefono' => $telefono
        ]);

        // Devolver los datos completos del cliente insertado
        $idCliente = $pdo->lastInsertId();
        $stmt = $pdo->prepare("SELECT id, nombre, direccion, telefono FROM clientes WHERE id = :id");
        $stmt->execute(['id' => $idCliente]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(["message" => "Cliente agregado exitosamente", "cliente" => $cliente]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error al agregar el cliente", "error" => $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
