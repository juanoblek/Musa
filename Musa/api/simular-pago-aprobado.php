<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Para OPTIONS requests (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    // Obtener datos JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Log para debugging
    error_log('🧪 SIMULACIÓN PAGO DIRECTO: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
    
    // Conectar a la base de datos
    require_once 'config/database.php';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Validar campos requeridos
    $required = ['fullName', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }
    
    // Simular información de pago exitoso (como si viniera de MercadoPago)
    $simulatedPayment = [
        'status' => 'approved',
        'status_detail' => 'accredited',
        'payment_method_id' => 'visa',
        'payment_type_id' => 'credit_card',
        'card_last_four_digits' => '3704',
        'installments' => 1,
        'transaction_amount' => $data['total'] ?? 259800,
        'date_approved' => date('Y-m-d H:i:s'),
        'external_reference' => 'test_simulation_' . uniqid(),
        'simulated' => true
    ];
    
    // Preparar datos del pedido para la BD
    $pedidoData = [
        'nombre_completo' => $data['fullName'],
        'email' => $data['email'],
        'telefono' => $data['phone'] ?? '',
        'tipo_documento' => $data['documentType'] ?? 'CC',
        'numero_documento' => $data['documentNumber'] ?? '00000000',
        'direccion_envio' => $data['address'] ?? '',
        'ciudad' => $data['city'] ?? '',
        'departamento' => $data['department'] ?? '',
        'codigo_postal' => $data['postalCode'] ?? '',
        'notas_envio' => $data['notes'] ?? 'Simulación de pago directo',
        'productos_json' => $data['productos'] ?? json_encode([]),
        'subtotal' => $data['subtotal'] ?? 244800,
        'costo_envio' => $data['envio'] ?? 15000,
        'total' => $data['total'] ?? 259800,
        'estado_pago' => 'completado',
        'metodo_pago' => 'simulacion_directa',
        'estado_pedido' => 'pendiente',
        'token_pago' => $data['token'] ?? 'sim_' . uniqid(),
        'payment_details' => json_encode($simulatedPayment),
        'fecha_pedido' => date('Y-m-d H:i:s'),
        'origen' => 'test_directo_sin_sdk'
    ];
    
    // Preparar query de inserción
    $columns = implode(', ', array_keys($pedidoData));
    $placeholders = ':' . implode(', :', array_keys($pedidoData));
    
    $sql = "INSERT INTO pedidos ($columns) VALUES ($placeholders)";
    
    $stmt = $pdo->prepare($sql);
    
    // Ejecutar inserción
    if ($stmt->execute($pedidoData)) {
        $pedido_id = $pdo->lastInsertId();
        
        // Log de éxito
        error_log("✅ SIMULACIÓN EXITOSA: Pedido ID $pedido_id guardado");
        
        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Pago simulado guardado exitosamente',
            'pedido_id' => $pedido_id,
            'payment_status' => 'approved',
            'simulated' => true,
            'details' => [
                'total' => $pedidoData['total'],
                'metodo' => 'Simulación Directa',
                'fecha' => $pedidoData['fecha_pedido'],
                'card_ending' => '3704'
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        throw new Exception('Error al insertar en la base de datos');
    }
    
} catch (Exception $e) {
    error_log('❌ ERROR SIMULACIÓN: ' . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error en simulación: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>