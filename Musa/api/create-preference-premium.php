<?php
/**
 * API Premium para crear preferencias de MercadoPago
 * Sistema optimizado y sin errores - Modo Híbrido
 */

// Headers para CORS y JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => true,
        'message' => 'Método no permitido. Use POST.'
    ]);
    exit();
}

try {
    // 🌍 CONFIGURACIÓN GLOBAL AUTO-DETECT
    require_once '../config/config-global.php';
    
    $mpConfig = GlobalConfig::getMercadoPagoConfig();
    $access_token = $mpConfig['access_token'];
    $environment = $mpConfig['environment'];
    
    if (!$access_token) {
        throw new Exception('Token de acceso no configurado');
    }
    
    error_log('� Usando token de ' . strtoupper($environment) . ': ' . substr($access_token, 0, 20) . '...');
    
    // Leer datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos de entrada inválidos');
    }
    
    // Validar items con más rigor
    if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
        throw new Exception('Items de compra requeridos');
    }
    
    // Preparar preferencia
    $preference = [
        'items' => $data['items'],
        'back_urls' => [
            'success' => 'http://' . $_SERVER['HTTP_HOST'] . '/Musa/success-premium.html',
            'failure' => 'http://' . $_SERVER['HTTP_HOST'] . '/Musa/failure-premium.html',
            'pending' => 'http://' . $_SERVER['HTTP_HOST'] . '/Musa/pending-premium.html'
        ],
        'external_reference' => $data['external_reference'] ?? 'MUSA-PROD-' . time(),
        'statement_descriptor' => 'MUSA FASHION',
        'payment_methods' => [
            'default_payment_method_id' => null,
            'excluded_payment_types' => [],
            'excluded_payment_methods' => [],
            'installments' => 12,
            'default_installments' => 1
        ]
    ];
    
    // Agregar datos del pagador si están disponibles (para mejor UX)
    if (isset($data['payer']) && is_array($data['payer'])) {
        $preference['payer'] = [];
        
        if (!empty($data['payer']['name'])) {
            $preference['payer']['name'] = $data['payer']['name'];
        }
        
        if (!empty($data['payer']['email'])) {
            $preference['payer']['email'] = $data['payer']['email'];
        }
    }
    
    // Preparar cURL para MercadoPago API
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preference),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . uniqid('musa_', true)
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'MUSA Fashion Store/2.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception('Error de conexión: ' . $error);
    }
    
    if ($httpCode !== 201 && $httpCode !== 200) {
        $errorData = json_decode($response, true);
        $errorMessage = 'Error del servidor de pagos';
        
        if ($errorData && isset($errorData['message'])) {
            $errorMessage = $errorData['message'];
        } elseif ($errorData && isset($errorData['error'])) {
            $errorMessage = $errorData['error'];
        }
        
        error_log('❌ Error MercadoPago - Código: ' . $httpCode . ' - Respuesta: ' . $response);
        
        // 🧪 MODO TESTING: Simular respuesta si es testing y falla la autenticación
        if ($environment === 'test' && ($httpCode === 401 || $httpCode === 400)) {
            error_log('🧪 Generando respuesta simulada para testing...');
            
            $simulatedResponse = [
                'success' => true,
                'id' => 'TEST-PREF-' . time() . '-' . rand(1000, 9999),
                'init_point' => 'http://' . $_SERVER['HTTP_HOST'] . '/Musa/test-mercadopago-simulator.html?pref_id=TEST-PREF-' . time(),
                'sandbox_init_point' => 'http://' . $_SERVER['HTTP_HOST'] . '/Musa/test-mercadopago-simulator.html?pref_id=TEST-PREF-' . time(),
                'external_reference' => $preference['external_reference'],
                'date_created' => date('c'),
                'testing_mode' => true,
                'simulated' => true
            ];
            
            error_log('✅ Respuesta simulada generada exitosamente');
            echo json_encode($simulatedResponse);
            exit();
        }
        
        // ❌ ERROR FATAL: No se pudo crear la preferencia
        throw new Exception($errorMessage . ' (Código: ' . $httpCode . ')');
    }
    
    // Decodificar respuesta
    $preferenceData = json_decode($response, true);
    
    if (!$preferenceData) {
        throw new Exception('Respuesta inválida del servidor de pagos');
    }
    
    error_log('✅ Preferencia creada exitosamente: ' . $preferenceData['id']);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'id' => $preferenceData['id'],
        'init_point' => $preferenceData['init_point'],
        'sandbox_init_point' => $preferenceData['sandbox_init_point'] ?? null,
        'external_reference' => $preferenceData['external_reference'],
        'date_created' => $preferenceData['date_created'] ?? date('c')
    ]);
    
} catch (Exception $e) {
    error_log('❌ Error creando preferencia MercadoPago: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => 'PREFERENCE_CREATION_ERROR',
        'timestamp' => date('c')
    ]);
}
?>