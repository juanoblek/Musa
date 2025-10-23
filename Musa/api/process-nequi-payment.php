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
    $required_fields = ['payment_method', 'amount', 'sender_name', 'receipt_image', 'items', 'shipping'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }
    
    // Procesar imagen del comprobante
    $receipt_image = $data['receipt_image'];
    $image_data = '';
    $image_type = '';
    
    if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $receipt_image, $matches)) {
        $image_type = $matches[1];
        $image_data = base64_decode($matches[2]);
        
        // Generar nombre único para la imagen
        $image_filename = 'comprobante_' . uniqid() . '.' . $image_type;
        $image_path = '../uploads/comprobantes/' . $image_filename;
        
        // Crear directorio si no existe
        if (!file_exists('../uploads/comprobantes/')) {
            mkdir('../uploads/comprobantes/', 0777, true);
        }
        
        // Guardar imagen
        if (!file_put_contents($image_path, $image_data)) {
            throw new Exception('Error al guardar la imagen del comprobante');
        }
    } else {
        throw new Exception('Formato de imagen inválido');
    }
    
    // Generar ID único para el pedido
    $pedido_id = 'NQ' . date('YmdHis') . rand(100, 999);
    
    // Preparar datos del pedido
    $shipping = $data['shipping'];
    $productos_json = json_encode($data['items']);
    
    // DEBUG: Log para verificar los datos recibidos
    error_log("DEBUG - Shipping data recibidos: " . json_encode($shipping));
    
    // Obtener email (del shipping o buyer_email si existe)
    $buyer_email = $data['buyer_email'] ?? $shipping['email'] ?? '';
    
    // Insertar en tabla pedidos
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (
            pedido_id, productos, subtotal, envio, total, 
            estado_pago, metodo_pago, datos_pago
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $pedido_id,
        $productos_json,
        $data['amount'], // subtotal
        0, // envío gratis
        $data['amount'], // total
        'pendiente',
        'nequi_daviplata',
        json_encode(['sender_name' => $data['sender_name'], 'receipt_image' => $image_filename])
    ]);
    
    // Obtener el ID del pedido insertado
    $inserted_id = $pdo->lastInsertId();
    
    // Insertar en tabla envios
    $stmt = $pdo->prepare("
        INSERT INTO envios (
            pedido_id, nombre_completo, email, telefono, departamento, 
            ciudad, direccion, codigo_postal, notas_adicionales, estado_envio
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $pedido_id,
        $shipping['nombre_completo'] ?? '',
        $buyer_email,
        $shipping['telefono'] ?? '',
        $shipping['departamento'] ?? '',
        $shipping['ciudad'] ?? '',
        $shipping['direccion'] ?? '',
        $shipping['codigo_postal'] ?? '',
        $shipping['notas_adicionales'] ?? '',
        'pendiente'
    ]);
    
    // Insertar en tabla pagos_nequi
    $stmt = $pdo->prepare("
        INSERT INTO pagos_nequi (
            payment_id, external_reference, transaction_amount, phone_number, 
            status, payment_data
        ) VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $payment_data = json_encode([
        'sender_name' => $data['sender_name'],
        'receipt_image' => $image_filename,
        'buyer_email' => $buyer_email,
        'pedido_id' => $pedido_id,
        'shipping_data' => $shipping
    ]);
    
    $stmt->execute([
        $pedido_id, // payment_id
        $pedido_id, // external_reference 
        $data['amount'], // transaction_amount
        '3232212316', // phone_number destino
        'pending', // status
        $payment_data // payment_data como JSON
    ]);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pago registrado exitosamente',
        'pedido_id' => $pedido_id,
        'status' => 'pending_confirmation'
    ]);
    
} catch (Exception $e) {
    error_log("Error en process-nequi-payment.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>