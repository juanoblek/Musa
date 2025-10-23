<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Conectar a la base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['order_id']) || !isset($input['status'])) {
        throw new Exception('Datos requeridos faltantes');
    }
    
    $order_id = $input['order_id'];
    $status = $input['status'];
    $reason = isset($input['reason']) ? $input['reason'] : null;
    
    // Comenzar transacción
    $pdo->beginTransaction();
    
    // Mapear estado para las diferentes tablas
    $payment_status = '';
    $order_status = '';
    
    switch ($status) {
        case 'approved':
            $payment_status = 'aprobado';
            $order_status = 'aprobado';
            break;
        case 'rejected':
            $payment_status = 'rechazado';
            $order_status = 'rechazado';
            break;
        default:
            throw new Exception('Estado no válido');
    }
    
    // Actualizar estado en la tabla de pedidos
    $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
    $success = $stmt->execute([$order_status, $order_id]);
    
    if (!$success) {
        throw new Exception('No se pudo actualizar el estado del pedido');
    }
    
    // Actualizar estado en pagos_nequi (intentar primero)
    $stmt = $pdo->prepare("UPDATE pagos_nequi SET estado = ?, motivo_rechazo = ?, fecha_actualizacion = NOW() WHERE pedido_id = ?");
    $nequi_updated = $stmt->execute([$payment_status, $reason, $order_id]);
    
    // Si no se actualizó en pagos_nequi, intentar en pagos_daviplata
    if ($stmt->rowCount() === 0) {
        $stmt = $pdo->prepare("UPDATE pagos_daviplata SET estado = ?, motivo_rechazo = ?, fecha_actualizacion = NOW() WHERE pedido_id = ?");
        $daviplata_updated = $stmt->execute([$payment_status, $reason, $order_id]);
        
        if ($stmt->rowCount() === 0) {
            throw new Exception('No se encontró el registro de pago para actualizar');
        }
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Si se aprueba el pago, se puede enviar notificación por email (opcional)
    if ($status === 'approved') {
        // Aquí se podría agregar lógica para enviar email de confirmación
        error_log("Pago aprobado para pedido: $order_id");
    } elseif ($status === 'rejected') {
        error_log("Pago rechazado para pedido: $order_id - Motivo: $reason");
    }
    
    echo json_encode([
        'success' => true,
        'message' => $status === 'approved' ? 'Pago aprobado exitosamente' : 'Pago rechazado',
        'order_id' => $order_id,
        'new_status' => $order_status
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Error en update-payment-status.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>