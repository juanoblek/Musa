<?php
/**
 * API TEMPORAL PARA PRUEBAS PSE - SANDBOX
 * Este archivo es SOLO para probar PSE con credenciales de sandbox
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

try {
    // Credenciales de SANDBOX para pruebas PSE - CREDENCIALES REALES
    $accessToken = 'TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501';
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido');
    }
    
    // Preparar datos para MercadoPago
    $preferenceData = [
        'items' => $data['items'] ?? [],
        'payer' => $data['payer'] ?? [],
        'payment_methods' => [
            'excluded_payment_types' => [
                ['id' => 'credit_card'],
                ['id' => 'debit_card'],
                ['id' => 'ticket']
            ],
            'excluded_payment_methods' => [],
            'installments' => 1
        ],
        'back_urls' => [
            'success' => 'http://localhost/Musa/Musa/pago-premium.html?status=approved',
            'failure' => 'http://localhost/Musa/Musa/pago-premium.html?status=rejected',
            'pending' => 'http://localhost/Musa/Musa/pago-premium.html?status=pending'
        ],
        'external_reference' => $data['external_reference'] ?? 'pse-test-' . time(),
        'notification_url' => 'http://localhost/Musa/Musa/api/webhook-mercadopago.php'
    ];
    
    // Agregar información adicional si existe
    if (isset($data['additional_info'])) {
        $preferenceData['additional_info'] = $data['additional_info'];
    }
    
    // Inicializar cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preferenceData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception('Error de conexión: ' . $curlError);
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode !== 201) {
        $error = $responseData['message'] ?? 'Error desconocido';
        $errorCode = $responseData['status'] ?? $httpCode;
        throw new Exception($error . ' (Código: ' . $errorCode . ')');
    }
    
    // Log para debug
    error_log('PSE Test - Preferencia creada: ' . $responseData['id']);
    
    // Respuesta exitosa con simulador local
    echo json_encode([
        'success' => true,
        'preference_id' => 'LOCAL-PSE-' . time(),
        'init_point' => 'http://localhost/Musa/Musa/pse-simulator.html?amount=' . urlencode('$109.999') . '&return_url=' . urlencode('http://localhost/Musa/Musa/pago-premium.html?status=approved'),
        'sandbox_init_point' => 'http://localhost/Musa/Musa/pse-simulator.html?amount=' . urlencode('$109.999') . '&return_url=' . urlencode('http://localhost/Musa/Musa/pago-premium.html?status=approved'),
        'environment' => 'local_simulator'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    error_log('Error PSE Test: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>