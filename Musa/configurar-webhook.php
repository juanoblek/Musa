<?php
// Configurar webhook automáticamente en MercadoPago
header('Content-Type: application/json');

try {
    // Datos de configuración
    $access_token = 'APP_USR-6153740999578703-091219-3ce9553b42e997ddafbec8c4ee0c75c2-2567098533';
    $webhook_url = 'http://localhost/Musa/webhook-mercadopago.php';
    
    // Configuración del webhook
    $webhook_data = [
        'url' => $webhook_url,
        'events' => [
            'payment'
        ]
    ];
    
    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/v1/webhooks');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhook_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($http_code === 201) {
        echo json_encode([
            'success' => true,
            'message' => 'Webhook configurado correctamente',
            'webhook_id' => $result['id'],
            'webhook_url' => $webhook_url
        ]);
    } else {
        // Verificar si ya existe un webhook
        if (isset($result['message']) && strpos($result['message'], 'already exists') !== false) {
            echo json_encode([
                'success' => true,
                'message' => 'Webhook ya configurado previamente',
                'webhook_url' => $webhook_url
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error configurando webhook: ' . ($result['message'] ?? 'Error desconocido'),
                'response' => $result
            ]);
        }
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>