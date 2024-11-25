<?php
session_start();
require '../config.php'; // Archivo de configuración para conectar a la base de datos y obtener $pdo
require '../verify_token.php'; // Funciones para generar y verificar JWT

header('Content-Type: application/json');

// Asegurar que el método de la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $username = $data['username'] ?? null;
    $password = $data['password'] ?? null;

    // Validar que se recibieron usuario y contraseña
    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['message' => 'Faltan campos de usuario o contraseña.']);
        exit();
    }

    try {
        // Buscar al usuario en la base de datos
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE usuario = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['contrasena'])) {
            // Generar token JWT
            $payload = [
                'iss' => 'localhost', // Cambia esto al dominio de tu aplicación
                'iat' => time(),
                'exp' => time() + (60 * 60), // Token válido por 1 hora
                'user_id' => $user['id'],
                'rol' => $user['rol'] // Incluye el rol del usuario en el token
            ];

            $jwt = generateJWT($payload, $jwt_secret);

            // Guardar el token en la sesión
            $_SESSION['token'] = $jwt;

            // Respuesta con el token
            echo json_encode(['token' => $jwt, 'rol' => $user['rol']]);
            exit();
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Credenciales inválidas.']);
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['message' => 'Error al procesar la solicitud.']);
        exit();
    }
} else {
    http_response_code(405);
    echo json_encode(['message' => 'Método no permitido.']);
    exit();
}
