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
        throw new Exception('Token inválido.');
    }

    [$header, $payload, $signature] = $parts;

    $validSignature = hash_hmac('sha256', "$header.$payload", $secret, true);
    $base64ValidSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));

    if ($signature !== $base64ValidSignature) {
        throw new Exception('Firma inválida.');
    }

    $payloadData = json_decode(base64_decode($payload));

    if ($payloadData->exp < time()) {
        throw new Exception('Token expirado.');
    }

    return $payloadData;
}
