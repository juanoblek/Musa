<?php
// Headers para CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// FORZAR MODO SANDBOX PARA PRUEBAS
function getMercadoPagoConfig() {
    return [
        'environment' => 'sandbox',
        'public_key' => 'TEST-8d9f7e8c-8c8e-4b3a-9c7f-8d9f7e8c8c8e',
        'access_token' => 'TEST-123456789-access-token-sandbox',
        'configured' => true
    ];
}

// Manejar solicitud de configuración
if (isset($_GET['get_config']) && $_GET['get_config'] === 'true') {
    $config = getMercadoPagoConfig();
    
    $response = [
        'success' => true,
        'configured' => $config['configured'],
        'environment' => $config['environment'],
        'public_key' => $config['public_key'],
        'domain' => 'http://' . $_SERVER['HTTP_HOST']
    ];
    
    echo json_encode($response);
    exit;
}

echo json_encode(['error' => 'Método no permitido']);
?>