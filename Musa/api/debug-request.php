<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Debug del request
$input = json_decode(file_get_contents('php://input'), true);

echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'not set',
    'input_raw' => file_get_contents('php://input'),
    'input_decoded' => $input,
    'has_back_urls' => isset($input['back_urls']),
    'back_urls' => $input['back_urls'] ?? null
]);
?>
