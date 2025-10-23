<?php
/**
 * 🔥 CREAR PREFERENCIA MERCADOPAGO PARA CHECKOUT PRO
 * Crea preferencias de pago que redirigen a MercadoPago
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit();
}

try {
    // 🔑 OBTENER CREDENCIALES DESDE CONFIGURACIÓN
    require_once '../config/mercadopago.php';
    
    if (!isMercadoPagoConfigured()) {
        throw new Exception("MercadoPago no está configurado correctamente");
    }
    
    $access_token = getMercadoPagoAccessToken();
    
    if (!$access_token) {
        throw new Exception("Access token de MercadoPago no disponible");
    }
    
    // Obtener datos
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data || !isset($data['preference'])) {
        throw new Exception("No se recibieron datos de preferencia válidos");
    }
    
    $preference_data = $data['preference'];
    $order_data = $data['order_data'] ?? [];
    
    // Validar datos mínimos
    if (empty($preference_data['items']) || empty($preference_data['payer']['email'])) {
        throw new Exception("Faltan datos obligatorios para crear la preferencia");
    }
    
    // ===== CREAR PREFERENCIA EN MERCADOPAGO =====
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preference_data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_error($curl)) {
        throw new Exception('Error de conexión con MercadoPago: ' . curl_error($curl));
    }
    
    curl_close($curl);
    
    $resultado = json_decode($response, true);
    
    if ($http_code !== 201 && $http_code !== 200) {
        $error_msg = $resultado['message'] ?? 'Error desconocido al crear preferencia';
        throw new Exception("Error en MercadoPago: $error_msg");
    }
    
    // ===== GUARDAR PREFERENCIA EN SESIÓN/BD (OPCIONAL) =====
    
    $preference_id = $resultado['id'] ?? null;
    $init_point = $resultado['init_point'] ?? null;
    
    if (!$preference_id || !$init_point) {
        throw new Exception("Respuesta inválida de MercadoPago");
    }
    
    // Opcional: Guardar en base de datos para tracking
    try {
        require_once '../config/database.php';
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear tabla de preferencias si no existe
        $sql_create_table = "
            CREATE TABLE IF NOT EXISTS mercadopago_preferencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                preference_id VARCHAR(255) UNIQUE NOT NULL,
                external_reference VARCHAR(255),
                cliente_email VARCHAR(255),
                cliente_nombre VARCHAR(255),
                total_amount DECIMAL(10,2),
                order_data JSON,
                estado ENUM('creada', 'pagada', 'cancelada', 'expirada') DEFAULT 'creada',
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        
        $pdo->exec($sql_create_table);
        
        // Insertar preferencia
        $sql_insert = "
            INSERT INTO mercadopago_preferencias 
            (preference_id, external_reference, cliente_email, cliente_nombre, total_amount, order_data)
            VALUES (?, ?, ?, ?, ?, ?)
        ";
        
        $total_amount = 0;
        foreach ($preference_data['items'] as $item) {
            $total_amount += $item['unit_price'] * $item['quantity'];
        }
        
        $stmt = $pdo->prepare($sql_insert);
        $stmt->execute([
            $preference_id,
            $preference_data['external_reference'] ?? null,
            $preference_data['payer']['email'],
            $preference_data['payer']['name'] ?? '',
            $total_amount,
            json_encode($order_data)
        ]);
        
        error_log("✅ Preferencia guardada en BD: $preference_id");
        
    } catch (Exception $e) {
        // No es crítico si falla el guardado en BD
        error_log("⚠️ Error guardando preferencia en BD: " . $e->getMessage());
    }
    
    // ===== RESPUESTA EXITOSA =====
    
    echo json_encode([
        'success' => true,
        'preference_id' => $preference_id,
        'init_point' => $init_point,
        'sandbox_init_point' => $resultado['sandbox_init_point'] ?? null,
        'collector_id' => $resultado['collector_id'] ?? null,
        'client_id' => $resultado['client_id'] ?? null,
        'external_reference' => $preference_data['external_reference'] ?? null,
        'message' => 'Preferencia creada exitosamente',
        'debug_info' => [
            'total_items' => count($preference_data['items']),
            'total_amount' => $total_amount ?? 0,
            'payer_email' => $preference_data['payer']['email']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Log del error
    error_log("❌ Error en crear-preferencia.php: " . $e->getMessage());
}
?>