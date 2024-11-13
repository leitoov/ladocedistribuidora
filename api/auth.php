<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config.php';
require '../verify_token.php';

// Asegurar que el tipo de respuesta sea JSON
header('Content-Type: application/json');

// Verificar que el método de solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    // Verificar si se han proporcionado los datos necesarios (username y password)
    if (!isset($data->username) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Faltan campos de usuario o contraseña"]);
        exit();
    }
    
    $username = $data->username;
    $password = $data->password;

    // Consulta en la base de datos para buscar al usuario por nombre de usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['contrasena'])) {
        // Si la autenticación es exitosa, genera el token JWT
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60),  // Expira en 1 hora
            "user_id" => $user['id']
        ];
        
        // Generar el token usando la función manual
        $jwt = generateJWT($payload, $jwt_secret);
        echo json_encode(["token" => $jwt]);
    } else {
        // Enviar respuesta de credenciales inválidas
        http_response_code(401);
        echo json_encode(["message" => "Credenciales inválidas"]);
    }
} else {
    // Responder con un error si el método no es POST
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
