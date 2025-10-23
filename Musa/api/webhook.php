<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log de webhook
function logWebhook($message) {
    $logFile = __DIR__ . '/webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

try {
    // Obtener el cuerpo de la petición
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    logWebhook("Webhook recibido: " . $input);
    
    // Verificar si es una notificación de MercadoPago
    if (isset($data['type']) && isset($data['data']['id'])) {
        
        $notificationType = $data['type'];
        $paymentId = $data['data']['id'];
        
        logWebhook("Tipo: $notificationType, Payment ID: $paymentId");
        
        // Aquí puedes procesar diferentes tipos de notificaciones
        switch ($notificationType) {
            case 'payment':
                // Notificación de pago
                logWebhook("Procesando notificación de pago: $paymentId");
                
                // Aquí deberías:
                // 1. Consultar el estado del pago en MercadoPago
                // 2. Actualizar el estado del pedido en tu base de datos
                // 3. Enviar emails de confirmación si es necesario
                
                break;
                
            case 'merchant_order':
                // Notificación de orden
                logWebhook("Procesando notificación de orden: $paymentId");
                break;
                
            default:
                logWebhook("Tipo de notificación no manejada: $notificationType");
        }
        
        // Responder con 200 OK para confirmar recepción
        http_response_code(200);
        echo json_encode([
            'status' => 'ok',
            'message' => 'Webhook procesado correctamente'
        ]);
        
    } else {
        // Formato inválido
        logWebhook("Formato de webhook inválido");
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Formato de webhook inválido'
        ]);
    }
    
} catch (Exception $e) {
    logWebhook("Error en webhook: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor'
    ]);
}
?>
