<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// FORZAR MODO PRODUCCIÓN PARA PRUEBAS
function isProduction() {
    return true; // Forzar modo producción
}

// Configuración de MercadoPago
function getMercadoPagoConfig() {
    // CONFIGURACIÓN DE PRODUCCIÓN FORZADA
    return [
        'environment' => 'production',
        'public_key' => 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577',
        'access_token' => 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340',
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
        'domain' => 'https://' . $_SERVER['HTTP_HOST']
    ];
    
    echo json_encode($response);
    exit;
}

// Procesar pagos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'create_payment') {
        // Simular respuesta de MercadoPago para producción
        $response = [
            'success' => true,
            'payment_id' => 'PROD_' . uniqid(),
            'status' => 'approved',
            'status_detail' => 'accredited',
            'amount' => $input['amount'] ?? 0,
            'environment' => 'production'
        ];
        
        echo json_encode($response);
        exit;
    }
}

echo json_encode(['error' => 'Método no permitido']);
?>