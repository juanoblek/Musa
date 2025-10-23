<?php
/**
 * API para procesar pagos - MODO PRUEBA SIMPLIFICADO
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
    // Leer datos del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos de entrada inválidos');
    }
    
    error_log('🧪 MODO PRUEBA - Datos recibidos: ' . json_encode($data));
    
    // Validar campos requeridos
    $requiredFields = ['token', 'transaction_amount', 'payment_method_id'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }
    
    // Validar monto
    if ($data['transaction_amount'] <= 0) {
        throw new Exception('Monto inválido');
    }
    
    $token = $data['token'];
    $amount = floatval($data['transaction_amount']);
    $payment_method = $data['payment_method_id'];
    
    // 🧪 SIMULACIÓN DE PAGO PARA PRUEBAS
    if (strpos($token, 'TEST-') === 0 || strpos($token, 'TEST-FALLBACK-') === 0) {
        error_log('🧪 Procesando pago de prueba con token: ' . $token);
        
        // Simular diferentes resultados basados en el monto
        $payment_id = 'TEST_' . time() . '_' . rand(1000, 9999);
        
        // Simular respuesta exitosa para montos menores a 50,000,000 (50 millones)
        if ($amount < 50000000) {
            $response = [
                'success' => true,
                'payment_id' => $payment_id,
                'status' => 'approved',
                'status_detail' => 'accredited',
                'transaction_amount' => $amount,
                'payment_method_id' => $payment_method,
                'external_reference' => 'MUSA-TEST-' . time(),
                'date_created' => date('c'),
                'message' => 'Pago de prueba procesado exitosamente',
                'test_mode' => true
            ];
        } else {
            // Simular rechazo para montos altos
            $response = [
                'success' => false,
                'payment_id' => $payment_id,
                'status' => 'rejected',
                'status_detail' => 'cc_rejected_insufficient_amount',
                'transaction_amount' => $amount,
                'payment_method_id' => $payment_method,
                'message' => 'Pago rechazado - Fondos insuficientes (simulación)',
                'test_mode' => true
            ];
        }
        
        error_log('✅ Respuesta de prueba: ' . json_encode($response));
        echo json_encode($response);
        exit();
    }
    
    // Si no es token de prueba, rechazar
    throw new Exception('Token no válido para modo de prueba');
    
} catch (Exception $e) {
    error_log('❌ Error en proceso de pago: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'test_mode' => true
    ]);
}
?>