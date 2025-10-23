<?php
/**
 * API para procesar pagos directamente con MercadoPago Checkout API
 * Sin redirección - Experiencia fluida para el usuario
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
    
    error_log('� Procesando pago directo con token de ' . strtoupper($environment) . ': ' . substr($access_token, 0, 20) . '...');
    
    // Leer datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos de entrada inválidos');
    }
    
    // Validar campos requeridos
    $requiredFields = ['token', 'transaction_amount', 'payment_method_id', 'payer'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }
    
    // Validar monto
    if ($data['transaction_amount'] <= 0) {
        throw new Exception('Monto inválido');
    }
    
    // Preparar datos del pago
    $token = $data['token'];
    
    // 🔴 VALIDACIÓN DE TOKENS SEGÚN ENTORNO
    if ($environment === 'production') {
        // En producción, rechazar tokens simulados
        if (strpos($token, 'TEST-TOKEN-') === 0 || strpos($token, 'TEST-FALLBACK-') === 0) {
            error_log('❌ Token simulado detectado en producción: ' . $token);
            throw new Exception('Token inválido para entorno de producción');
        }
    } else {
        // En test, permitir tokens simulados y reales
        error_log('🧪 Modo TEST: Aceptando token: ' . substr($token, 0, 20) . '...');
    }
    
    // 🔴 PROCESAMIENTO REAL CON MERCADOPAGO
    $paymentData = [
        'token' => $token,
        'installments' => $data['installments'] ?? 1,
        'payment_method_id' => $data['payment_method_id'],
        'transaction_amount' => floatval($data['transaction_amount']),
        'description' => $data['description'] ?? 'MUSA Fashion - Compra',
        'external_reference' => 'MUSA-DIRECT-' . time(),
        'payer' => [
            'email' => $data['payer']['email'] ?? 'cliente@musa.com',
            'identification' => [
                'type' => $data['payer']['identification']['type'] ?? 'CC',
                'number' => $data['payer']['identification']['number'] ?? '12345678'
            ]
        ],
        'statement_descriptor' => 'MUSA FASHION',
        'capture' => true
    ];
    
    error_log('💳 Datos del pago: ' . json_encode($paymentData));
    
    // Preparar cURL para MercadoPago API
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($paymentData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . uniqid('musa_payment_', true)
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
        $errorMessage = 'Error procesando el pago';
        
        if ($errorData && isset($errorData['message'])) {
            $errorMessage = $errorData['message'];
        } elseif ($errorData && isset($errorData['cause'])) {
            $errorMessage = $errorData['cause'][0]['description'] ?? $errorMessage;
        }
        
        error_log('❌ Error MercadoPago - Código: ' . $httpCode . ' - Respuesta: ' . $response);
        
        // 🧪 MODO TEST: Permitir simulación si es token de prueba
        if ($environment === 'test' && (strpos($token, 'TEST-FALLBACK-') === 0 || $httpCode === 401 || $httpCode === 400)) {
            error_log('🧪 Generando respuesta simulada para token de prueba...');
            
            // Determinar estado basado en el nombre del titular de la tarjeta
            $cardholderName = $data['payer']['name'] ?? '';
            $status = 'approved'; // Por defecto aprobado
            
            error_log('🧪 Evaluando nombre del titular: ' . $cardholderName);
            
            if (strpos(strtoupper($cardholderName), 'PEND') !== false) {
                $status = 'pending';
                error_log('💤 Pago PENDIENTE detectado por nombre: ' . $cardholderName);
            } elseif (strpos(strtoupper($cardholderName), 'OTHE') !== false || strpos(strtoupper($cardholderName), 'REJE') !== false) {
                $status = 'rejected';
                error_log('❌ Pago RECHAZADO detectado por nombre: ' . $cardholderName);
            } else {
                error_log('✅ Pago APROBADO por defecto para nombre: ' . $cardholderName);
            }
            
            $simulatedPayment = [
                'success' => $status !== 'rejected', // Solo exitoso si no es rechazado
                'id' => 'TEST-PAYMENT-' . time() . '-' . rand(1000, 9999),
                'status' => $status,
                'status_detail' => $status === 'approved' ? 'accredited' : ($status === 'pending' ? 'pending_review' : 'cc_rejected_other_reason'),
                'payment_method_id' => $data['payment_method_id'],
                'payment_type_id' => 'credit_card',
                'transaction_amount' => $data['transaction_amount'],
                'currency_id' => 'COP',
                'date_created' => date('c'),
                'simulated' => true,
                'testing_mode' => true
            ];
            
            error_log('✅ Pago simulado generado: ' . $simulatedPayment['id'] . ' - Estado: ' . $status);
            
            if ($status === 'rejected') {
                // Para pagos rechazados, devolver código 400 con los detalles
                http_response_code(400);
                echo json_encode([
                    'error' => true,
                    'message' => 'Pago rechazado',
                    'status' => 'rejected',
                    'status_detail' => 'cc_rejected_other_reason',
                    'payment_data' => $simulatedPayment
                ]);
            } else {
                // Para pagos aprobados o pendientes, devolver 200 OK
                http_response_code(200);
                echo json_encode($simulatedPayment);
            }
            exit();
        }
        
        throw new Exception($errorMessage . ' (Código: ' . $httpCode . ')');
    }
    
    // Decodificar respuesta
    $paymentResult = json_decode($response, true);
    
    if (!$paymentResult) {
        throw new Exception('Respuesta inválida del servidor de pagos');
    }
    
    // Log exitoso
    error_log('✅ Pago procesado exitosamente: ' . $paymentResult['id'] . ' - Estado: ' . $paymentResult['status']);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'id' => $paymentResult['id'],
        'status' => $paymentResult['status'],
        'status_detail' => $paymentResult['status_detail'],
        'transaction_amount' => $paymentResult['transaction_amount'],
        'payment_method_id' => $paymentResult['payment_method_id'],
        'external_reference' => $paymentResult['external_reference'],
        'date_created' => $paymentResult['date_created'],
        'approval_url' => $paymentResult['point_of_interaction']['transaction_data']['ticket_url'] ?? null
    ]);
    
} catch (Exception $e) {
    // Log del error
    error_log('❌ Error procesando pago: ' . $e->getMessage());
    
    // Respuesta de error
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'code' => 'PAYMENT_PROCESSING_ERROR',
        'timestamp' => date('c')
    ]);
}
?>