<?php
// Archivo para crear preferencias de MercadoPago
// Este archivo debe ejecutarse en el servidor backend

// ✅ CONFIGURACIÓN GLOBAL AUTO-DETECT
require_once '../config/config-global.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo aceptar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

// ✅ OBTENER CREDENCIALES DE CONFIGURACIÓN GLOBAL
$mpConfig = GlobalConfig::getMercadoPagoConfig();
$MP_ACCESS_TOKEN = $mpConfig['access_token'];

if (!$MP_ACCESS_TOKEN) {
    throw new Exception('Access token de MercadoPago no disponible');
}

try {
    // Obtener datos del request
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Log para debug
    error_log('🔍 DEBUG - Input recibido: ' . json_encode($input));
    
    if (!$input) {
        throw new Exception('Datos inválidos');
    }
    
    // Validar datos requeridos
    if (!isset($input['items']) || !isset($input['payer']) || empty($input['items'])) {
        throw new Exception('Faltan datos requeridos: items y payer');
    }
    
    // Obtener método de pago específico si se proporciona
    $payment_method = $input['payment_method'] ?? 'all';
    
    // 🌍 OBTENER URLs DINÁMICAS SEGÚN ENTORNO
    $urls = GlobalConfig::getMercadoPagoUrls();
    $success_url = $input['back_urls']['success'] ?? $urls['success'];
    $failure_url = $input['back_urls']['failure'] ?? $urls['failure'];
    $pending_url = $input['back_urls']['pending'] ?? $urls['pending'];
    
    // Log de URLs
    GlobalConfig::log('URLs configuradas', [
        'success' => $success_url,
        'failure' => $failure_url,
        'pending' => $pending_url,
        'environment' => IS_PRODUCTION ? 'PRODUCTION' : 'DEVELOPMENT'
    ]);
    
    // Configurar métodos de pago según la selección
    $payment_methods = [];
    
    switch ($payment_method) {
        case 'credit_card':
            $payment_methods = [
                'excluded_payment_types' => [
                    ['id' => 'atm'],
                    ['id' => 'ticket'],
                    ['id' => 'bank_transfer'],
                    ['id' => 'debit_card']
                ],
                'installments' => 12
            ];
            break;
            
        case 'debit_card':
            $payment_methods = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'atm'],
                    ['id' => 'ticket'],
                    ['id' => 'bank_transfer']
                ],
                'included_payment_methods' => [
                    ['id' => 'visa'],
                    ['id' => 'master'],
                    ['id' => 'amex']
                ],
                'installments' => 1
            ];
            break;
            
        case 'pse':
            $payment_methods = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'atm'],
                    ['id' => 'ticket']
                ],
                'included_payment_methods' => [
                    ['id' => 'pse']
                ]
            ];
            break;
            
        case 'efecty':
            $payment_methods = [
                'excluded_payment_types' => [
                    ['id' => 'credit_card'],
                    ['id' => 'debit_card'],
                    ['id' => 'atm'],
                    ['id' => 'bank_transfer']
                ],
                'included_payment_methods' => [
                    ['id' => 'efecty']
                ]
            ];
            break;
            
        default: // 'all' - mostrar todos los métodos
            $payment_methods = [
                'installments' => 12,
                'default_installments' => 1
            ];
            break;
    }
    
    // Preparar datos para MercadoPago - ULTRA SIMPLIFICADO
    $preference_data = [
        'items' => $input['items'],
        'payer' => [
            'email' => 'test@example.com'
        ],
        'payment_methods' => $payment_methods,
        'back_urls' => [
            'success' => $success_url,
            'failure' => $failure_url,
            'pending' => $pending_url
        ],
        'external_reference' => $input['external_reference'] ?? 'MUSA-' . time(),
        'statement_descriptor' => 'MUSA FASHION'
    ];
    
    // Log de datos a enviar
    error_log('📦 DEBUG - Datos a enviar a MercadoPago: ' . json_encode($preference_data));
    
    // Realizar petición a MercadoPago
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
        throw new Exception('Error de conexión: ' . $curl_error);
    }
    
    if ($http_code !== 201) {
        $error_data = json_decode($response, true);
        throw new Exception('Error de MercadoPago: ' . json_encode($error_data));
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['init_point'])) {
        throw new Exception('Respuesta inválida de MercadoPago');
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'preference_id' => $result['id'],
        'init_point' => $result['init_point'],
        'sandbox_init_point' => $result['sandbox_init_point'] ?? null,
        'collector_id' => $result['collector_id'] ?? null
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
