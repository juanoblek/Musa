<?php
/**
 * API UNIFICADA: PROCESAR PAGO + GUARDAR PEDIDO
 * Solo guarda en BD si el pago es exitoso
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

require_once '../config/database.php';

try {
    // Obtener datos del formulario
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception("No se recibieron datos válidos");
    }
    
    // ===== PASO 1: SIMULAR PROCESAMIENTO DE PAGO =====
    
    $pago_exitoso = false;
    $pago_id = null;
    $estado_pago = 'rejected';
    
    // Simular lógica de pago (aquí conectarías con MercadoPago, PayU, etc.)
    $metodo_pago = $data['paymentMethod'] ?? 'credit_card';
    $monto = floatval($data['total'] ?? 0);
    
    // SIMULACIÓN DE PAGO (reemplaza esto con tu API de pago real)
    if ($monto > 0 && !empty($data['cardNumber'])) {
        // Simular diferentes escenarios según número de tarjeta
        $numero_tarjeta = preg_replace('/\D/', '', $data['cardNumber']);
        
        if (substr($numero_tarjeta, -1) === '1') {
            // Tarjetas terminadas en 1 = Pago exitoso
            $pago_exitoso = true;
            $estado_pago = 'approved';
            $pago_id = 'MP_' . date('YmdHis') . '_' . rand(1000, 9999);
        } else if (substr($numero_tarjeta, -1) === '2') {
            // Tarjetas terminadas en 2 = Pago pendiente
            $pago_exitoso = false;
            $estado_pago = 'pending';
            $pago_id = 'MP_PENDING_' . date('YmdHis');
        } else {
            // Otros números = Pago rechazado
            $pago_exitoso = false;
            $estado_pago = 'rejected';
            $pago_id = 'MP_REJECTED_' . date('YmdHis');
        }
    }
    
    // ===== PASO 2: RESPUESTA SI PAGO FALLA =====
    
    if (!$pago_exitoso) {
        echo json_encode([
            'success' => false,
            'pago_exitoso' => false,
            'estado_pago' => $estado_pago,
            'message' => 'El pago no pudo ser procesado. Verifica los datos de tu tarjeta.',
            'pago_id' => $pago_id,
            'datos_guardados' => false
        ]);
        exit();
    }
    
    // ===== PASO 3: PAGO EXITOSO - GUARDAR EN BASE DE DATOS =====
    
    // Crear conexión a BD
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Comenzar transacción
    $pdo->beginTransaction();
    
    // Generar ID único del pedido
    $pedido_id = 'MUSA-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Extraer y validar datos
    $nombre_completo = trim($data['fullName'] ?? '');
    $email = trim($data['email'] ?? '');
    $telefono = trim($data['phone'] ?? '');
    $departamento = trim($data['department'] ?? '');
    $ciudad = trim($data['city'] ?? '');
    $direccion = trim($data['address'] ?? '');
    $codigo_postal = trim($data['postalCode'] ?? '');
    $notas_adicionales = trim($data['notes'] ?? '');
    
    // Validaciones
    if (empty($nombre_completo) || empty($email) || empty($telefono) || empty($direccion)) {
        throw new Exception("Faltan datos obligatorios del cliente");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }
    
    // Preparar datos del pedido
    $productos = $data['productos'] ?? $data['items'] ?? '[]';
    $subtotal = floatval($data['subtotal'] ?? 0);
    $envio = floatval($data['envio'] ?? 15000);
    $total = floatval($data['total'] ?? ($subtotal + $envio));
    
    // Datos de pago (sin información sensible)
    $datos_pago = json_encode([
        'pago_id' => $pago_id,
        'metodo' => $metodo_pago,
        'ultimos_4_digitos' => substr(preg_replace('/\D/', '', $data['cardNumber'] ?? ''), -4),
        'titular' => $data['cardHolder'] ?? '',
        'tipo_documento' => $data['documentType'] ?? '',
        'numero_documento' => $data['documentNumber'] ?? $data['idNumber'] ?? '',
        'cuotas' => $data['installments'] ?? 1,
        'fecha_pago' => date('Y-m-d H:i:s')
    ]);
    
    // ===== INSERTAR EN TABLA PEDIDOS =====
    $sql_pedido = "
        INSERT INTO pedidos (
            pedido_id, productos, subtotal, envio, total,
            estado_pago, metodo_pago, datos_pago,
            fecha_creacion, fecha_actualizacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ";
    
    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([
        $pedido_id,
        $productos,
        $subtotal,
        $envio,
        $total,
        'aprobado', // Solo llega aquí si el pago fue exitoso
        $metodo_pago,
        $datos_pago
    ]);
    
    // ===== INSERTAR EN TABLA ENVIOS =====
    $sql_envio = "
        INSERT INTO envios (
            pedido_id, nombre_completo, email, telefono,
            departamento, ciudad, direccion, codigo_postal,
            notas_adicionales, estado_envio, fecha_creacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt_envio = $pdo->prepare($sql_envio);
    $stmt_envio->execute([
        $pedido_id,
        $nombre_completo,
        $email,
        $telefono,
        $departamento,
        $ciudad,
        $direccion,
        $codigo_postal,
        $notas_adicionales,
        'pendiente'
    ]);
    
    // ===== INSERTAR TRACKING (OPCIONAL) =====
    $sql_tracking = "
        INSERT INTO pedido_tracking (pedido_id, estado_nuevo, comentario, fecha_cambio)
        VALUES (?, ?, ?, NOW())
    ";
    
    $stmt_tracking = $pdo->prepare($sql_tracking);
    $stmt_tracking->execute([
        $pedido_id,
        'pedido_creado',
        'Pedido creado exitosamente con pago aprobado. ID de pago: ' . $pago_id
    ]);
    
    // Confirmar transacción
    $pdo->commit();
    
    // ===== RESPUESTA EXITOSA =====
    echo json_encode([
        'success' => true,
        'pago_exitoso' => true,
        'estado_pago' => 'approved',
        'message' => '¡Pago exitoso! Tu pedido ha sido creado.',
        'pedido_id' => $pedido_id,
        'pago_id' => $pago_id,
        'total' => $total,
        'datos_guardados' => true,
        'siguiente_paso' => 'El pedido aparecerá en tu panel de administración'
    ]);
    
} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'pago_exitoso' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'datos_guardados' => false,
        'error' => true
    ]);
}
?>