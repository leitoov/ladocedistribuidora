<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../config.php';
require '../verify_token.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

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
            "exp" => time() + (24 * 60 * 60),  // Extiende la expiración a 24 horas
            "user_id" => $user['id'],
            "rol" => $user['rol']
        ];

        $jwt = generateJWT($payload, $jwt_secret);

        // Extender la duración de la sesión a 24 horas
        ini_set('session.gc_maxlifetime', 24 * 60 * 60);
        session_set_cookie_params(24 * 60 * 60);
        session_start();

        $_SESSION['token'] = $jwt;

        echo json_encode(["token" => $jwt]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Credenciales inválidas"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Método no permitido"]);
}
