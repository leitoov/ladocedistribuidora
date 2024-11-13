<?php
require '../config.php';
require '../verify_token.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username;
    $password = $data->password;

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :username LIMIT 1");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['contrasena'])) {
        $jwt = generateJWT($user['id'], $jwt_secret);
        echo json_encode(["token" => $jwt]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Credenciales inválidas"]);
    }
}
