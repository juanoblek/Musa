<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Obtener datos POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos inválidos');
    }
    
    // Validar campos requeridos
    if (empty($data['payment_id']) || empty($data['shipping_status'])) {
        throw new Exception('Faltan campos requeridos');
    }
    
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'musa_store';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Actualizar estado de envío
    $sql = "
        UPDATE pedidos 
        SET 
            shipping_status = :status,
            shipping_method = :method,
            payment_data = JSON_SET(
                payment_data,
                '$.shipping_notes',
                :notes
            ),
            updated_at = CURRENT_TIMESTAMP
        WHERE payment_id = :payment_id
    ";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'status' => $data['shipping_status'],
        'method' => $data['shipping_method'] ?? 'standard',
        'notes' => $data['shipping_notes'] ?? '',
        'payment_id' => $data['payment_id']
    ]);
    
    if ($result && $stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Estado de envío actualizado correctamente'
        ]);
    } else {
        throw new Exception('No se pudo actualizar el estado de envío');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>