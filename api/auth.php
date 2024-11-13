<?php
require '../config.php';
require '../verify_token.php';

use Firebase\JWT\JWT;

header('Content-Type: application/json');  // Asegúrate de que el contenido sea JSON

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

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['contrasena'])) {
        $payload = [
            "iss" => "localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60),  // Expira en 1 hora
            "user_id" => $user['id']
        ];
        
        $jwt = JWT::encode($payload, $jwt_secret, 'HS256');
        echo json_encode(["token" => $jwt]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Credenciales inválidas"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
