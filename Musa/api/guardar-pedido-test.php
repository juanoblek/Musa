<?php
/**
 * API SIMPLIFICADA PARA GUARDAR PEDIDOS
 * Versión de prueba que maneja errores de foreign key
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

try {
    // Solo aceptar POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Datos JSON inválidos');
    }

    // Log de datos recibidos
    error_log("📡 PEDIDO RECIBIDO: " . json_encode($input, JSON_PRETTY_PRINT));

    // Validar que el pago sea exitoso
    if (!isset($input['pago_exitoso']) || !$input['pago_exitoso']) {
        throw new Exception('Solo se guardan pedidos con pago exitoso');
    }

    // Simular guardado exitoso para pruebas
    $pedido_id = 'TEST_' . time() . '_' . rand(1000, 9999);
    
    // Log del pedido "guardado"
    error_log("✅ PEDIDO SIMULADO GUARDADO: " . $pedido_id);
    error_log("- Cliente: " . ($input['envio']['nombre_completo'] ?? 'N/A'));
    error_log("- Email: " . ($input['envio']['email'] ?? 'N/A'));
    error_log("- Total: $" . number_format($input['total'] ?? 0));
    error_log("- Productos: " . count($input['productos'] ?? []));

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pedido guardado exitosamente (modo prueba)',
        'pedido_id' => $pedido_id,
        'total' => $input['total'] ?? 0,
        'cliente' => $input['envio']['nombre_completo'] ?? 'N/A',
        'test_mode' => true
    ]);

} catch (Exception $e) {
    error_log('❌ Error guardando pedido: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'test_mode' => true
    ]);
}
?>