<?php
// Test simple de conectividad con MercadoPago
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$MP_ACCESS_TOKEN = 'TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501';

// Test básico de preferencia
$preference_data = [
    'items' => [
        [
            'id' => 'test-001',
            'title' => 'Producto de Prueba',
            'quantity' => 1,
            'currency_id' => 'COP',
            'unit_price' => 10000
        ]
    ],
    'back_urls' => [
        'success' => 'http://localhost/Musa/success.html',
        'failure' => 'http://localhost/Musa/failure.html',
        'pending' => 'http://localhost/Musa/pending.html'
    ],
    'auto_return' => 'approved'
];

try {
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($preference_data),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $MP_ACCESS_TOKEN,
            'Content-Type: application/json'
        ],
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    
    curl_close($curl);
    
    if ($curl_error) {
        throw new Exception('Error cURL: ' . $curl_error);
    }
    
    echo json_encode([
        'status' => 'success',
        'http_code' => $http_code,
        'response' => json_decode($response, true),
        'test_mode' => true,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
