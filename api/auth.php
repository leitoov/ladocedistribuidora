<?php
require '../config.php';
require '../verify_token.php';
require '../vendor/autoload.php';  // Si estás usando Firebase JWT, de lo contrario ignora esta línea

use Firebase\JWT\JWT;

header('Content-Type: application/json');  // Asegura que el contenido sea JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    // Verificar si se reciben los datos esperados
    if (!isset($data->username) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Faltan campos de usuario o contraseña"]);
        exit();
    }
    
    $username = $data->username;
    $password = $data->password;

    // Consulta para encontrar al usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar que el usuario exista y la contraseña coincida
    if ($user && password_verify($password, $user['contrasena'])) {
        // Si la autenticación es exitosa, generar el JWT
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60),  // Expira en 1 hora
            "user_id" => $user['id']
        ];
        
        $jwt = JWT::encode($payload, $jwt_secret, 'HS256');
        echo json_encode(["token" => $jwt]);
    } else {
        // Enviar respuesta de credenciales inválidas
        http_response_code(401);
        echo json_encode(["message" => "Credenciales inválidas"]);
    }
} else {
    // Respuesta para métodos que no sean POST
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
