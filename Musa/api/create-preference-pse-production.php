<?php
/**
 * API PSE PRODUCCIÓN - PAGOS REALES
 * Sistema de Pagos Seguros en Línea para transacciones reales
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
    // 🚀 PSE EN PRODUCCIÓN REAL - PAGOS REALES HABILITADOS
    
    require_once '../config/config-global.php';
    
    error_log('🚀 PSE PRODUCCIÓN - Procesando pago PSE real');
    
    $mpConfig = GlobalConfig::getMercadoPagoConfig(); // Usar config de producción
    $accessToken = $mpConfig['access_token'];
    $environment = $mpConfig['environment'];
    
    if (!$accessToken) {
        throw new Exception('Token de acceso PSE no configurado');
    }
    
    error_log('💰 PSE PRODUCCIÓN - Usando token de ' . strtoupper($environment) . ': ' . substr($accessToken, 0, 20) . '...');
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON inválido');
    }
    
    error_log('PSE PRODUCCIÓN - Iniciando creación de preferencia: ' . json_encode($data));
    
    // Validar datos requeridos - el frontend envía 'items' no 'amount'
    if (!isset($data['items']) || empty($data['items'])) {
        throw new Exception('Items de pago requeridos');
    }
    
    // Calcular el total desde los items
    $total = 0;
    foreach ($data['items'] as $item) {
        $total += (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 1);
    }
    
    if ($total <= 0) {
        throw new Exception('Monto total inválido: ' . $total);
    }
    
    error_log('PSE PRODUCCIÓN - Total calculado: ' . $total);
    
    // Usar la estructura recibida del frontend (que es más completa)
    $preference = $data;
    
    // Asegurar que tiene configuración PSE
    $preference['payment_methods'] = [
        'excluded_payment_types' => [
            ['id' => 'credit_card'],
            ['id' => 'debit_card'],
            ['id' => 'digital_wallet']
        ],
        'included_payment_methods' => [
            ['id' => 'pse']
        ]
    ];
    
    // Asegurar URLs de retorno correctas para PSE (sin auto_return por compatibilidad)
    $baseUrl = 'http://localhost/Musa/Musa/pago-premium.html';
    $preference['back_urls'] = [
        'success' => $baseUrl . '?status=approved',
        'failure' => $baseUrl . '?status=rejected', 
        'pending' => $baseUrl . '?status=pending'
    ];
    
    // NO usar auto_return para evitar problemas con sandbox
    // $preference['auto_return'] = 'approved';
    $preference['statement_descriptor'] = 'MUSA MODA PSE';
    
    error_log('PSE - URLs configuradas: ' . json_encode($preference['back_urls']));
    
    // Crear preferencia en MercadoPago
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
        CURLOPT_POSTFIELDS => json_encode($preference),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken,
            'X-Idempotency-Key: ' . uniqid('pse_', true)
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    
    if ($error) {
        throw new Exception('Error CURL: ' . $error);
    }
    
    if ($httpCode !== 201) {
        error_log('PSE PRODUCCIÓN - Error HTTP: ' . $httpCode . ' - Respuesta: ' . $response);
        throw new Exception('Error creando preferencia PSE: HTTP ' . $httpCode);
    }
    
    $preferenceData = json_decode($response, true);
    
    if (!$preferenceData || !isset($preferenceData['id'])) {
        throw new Exception('Respuesta inválida de MercadoPago');
    }
    
    error_log('PSE PRODUCCIÓN - Preferencia creada exitosamente: ' . $preferenceData['id']);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'preference_id' => $preferenceData['id'],
        'init_point' => $preferenceData['init_point'],
        'sandbox_init_point' => $preferenceData['sandbox_init_point'] ?? null,
        'external_reference' => $preference['external_reference'],
        'mode' => 'PRODUCTION',
        'message' => 'Preferencia PSE de producción creada exitosamente'
    ]);
    
} catch (Exception $e) {
    error_log('PSE PRODUCCIÓN - Error: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'mode' => 'PRODUCTION'
    ]);
}
?>