<?php
function generateJWT($payload, $secret) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode($payload);

    $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", $secret, true);
    $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    return "$base64Header.$base64Payload.$base64Signature";
}

function verifyJWT($jwt, $secret) {
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) {
        header('Location: logout.php'); // Redirige si el token no tiene la estructura correcta
        exit();
    }

    [$header, $payload, $signature] = $parts;

    $validSignature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $base64ValidSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));

    if ($signature !== $base64ValidSignature) {
        header('Location: logout.php'); // Redirige si la firma no es válida
        exit();
    }

    $payloadData = json_decode(base64_decode($payload));

    if ($payloadData->exp < time()) {
        header('Location: logout.php'); // Redirige si el token ha expirado
        exit();
    }

    return $payloadData;
}
