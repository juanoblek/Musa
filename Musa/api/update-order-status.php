<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener datos JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos inválidos');
    }
    
    // Validar campos requeridos
    if (!isset($data['order_id']) || !isset($data['status_type']) || !isset($data['new_status'])) {
        throw new Exception('Campos requeridos: order_id, status_type, new_status');
    }
    
    $order_id = $data['order_id'];
    $status_type = $data['status_type']; // 'payment' o 'shipping'
    $new_status = $data['new_status'];
    
    // Validar que el pedido existe
    $check_stmt = $pdo->prepare("SELECT id, pedido_id FROM pedidos WHERE id = ?");
    $check_stmt->execute([$order_id]);
    $pedido = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('Pedido no encontrado');
    }
    
    // Actualizar según el tipo de estado
    if ($status_type === 'payment') {
        // Actualizar estado de pago en tabla pedidos
        $valid_payment_statuses = ['pendiente', 'aprobado', 'rechazado'];
        if (!in_array($new_status, $valid_payment_statuses)) {
            throw new Exception('Estado de pago inválido');
        }
        
        $stmt = $pdo->prepare("UPDATE pedidos SET estado_pago = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        
        $message = "Estado de pago actualizado a: " . $new_status;
        
    } elseif ($status_type === 'shipping') {
        // Actualizar estado de envío en tabla envios
        $valid_shipping_statuses = ['pendiente', 'en_proceso', 'enviado', 'entregado'];
        if (!in_array($new_status, $valid_shipping_statuses)) {
            throw new Exception('Estado de envío inválido');
        }
        
        $stmt = $pdo->prepare("UPDATE envios SET estado_envio = ? WHERE pedido_id = ?");
        $stmt->execute([$new_status, $pedido['pedido_id']]);
        
        $message = "Estado de envío actualizado a: " . $new_status;
        
    } else {
        throw new Exception('Tipo de estado inválido. Use "payment" o "shipping"');
    }
    
    // Verificar que se actualizó al menos un registro
    if ($stmt->rowCount() === 0) {
        throw new Exception('No se pudo actualizar el estado. Verifique que el pedido existe.');
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => $message,
        'order_id' => $order_id,
        'pedido_id' => $pedido['pedido_id'],
        'status_type' => $status_type,
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    error_log("Error en update-order-status.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>