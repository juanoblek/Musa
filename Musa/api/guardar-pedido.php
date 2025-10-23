<?php
/**
 * API PARA GUARDAR PEDIDOS Y ENVÍOS
 * ⚠️ CRÍTICO: Solo guarda cuando el pago es EXITOSO/APROBADO
 * Este endpoint se llama DESPUÉS de confirmar el pago exitoso
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

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

    // 🔍 DEBUG: Log de datos recibidos
    error_log("📡 DATOS RECIBIDOS EN BACKEND:");
    error_log("Input completo: " . json_encode($input, JSON_PRETTY_PRINT));
    if (isset($input['envio'])) {
        error_log("Datos de envío: " . json_encode($input['envio'], JSON_PRETTY_PRINT));
    } else {
        error_log("❌ NO HAY DATOS DE ENVÍO");
    }

    // Validar que el pago sea exitoso
    if (!isset($input['pago_exitoso']) || !$input['pago_exitoso']) {
        throw new Exception('Solo se guardan pedidos con pago exitoso');
    }

    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Generar ID único del pedido
    $pedido_id = 'MUSA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

    // Validar datos requeridos de envío
    $required_shipping = ['nombre_completo', 'email', 'telefono', 'departamento', 'ciudad', 'direccion'];
    foreach ($required_shipping as $field) {
        if (empty($input['envio'][$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }

    // Validar datos requeridos de pago
    $required_payment = ['productos', 'subtotal', 'total'];
    foreach ($required_payment as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Campo requerido faltante: $field");
        }
    }

    // Iniciar transacción
    $pdo->beginTransaction();

    // 1. Insertar datos de envío
    $sql_envio = "
        INSERT INTO envios (
            pedido_id, nombre_completo, email, telefono, 
            departamento, ciudad, direccion, codigo_postal, notas_adicionales
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt_envio = $pdo->prepare($sql_envio);
    $stmt_envio->execute([
        $pedido_id,
        $input['envio']['nombre_completo'],
        $input['envio']['email'],
        $input['envio']['telefono'],
        $input['envio']['departamento'],
        $input['envio']['ciudad'],
        $input['envio']['direccion'],
        $input['envio']['codigo_postal'] ?? null,
        $input['envio']['notas_adicionales'] ?? null
    ]);

    // 2. Insertar datos del pedido
    $sql_pedido = "
        INSERT INTO pedidos (
            pedido_id, productos, subtotal, envio, total, 
            estado_pago, metodo_pago, datos_pago
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt_pedido = $pdo->prepare($sql_pedido);
    $stmt_pedido->execute([
        $pedido_id,
        json_encode($input['productos']),
        $input['subtotal'],
        $input['costo_envio'] ?? 12000,
        $input['total'],
        'aprobado',
        $input['metodo_pago'] ?? 'tarjeta_credito',
        json_encode($input['datos_pago'] ?? [])
    ]);

    // 3. Insertar tracking inicial
    $sql_tracking = "
        INSERT INTO pedido_tracking (pedido_id, estado_nuevo, comentario) 
        VALUES (?, ?, ?)
    ";

    $stmt_tracking = $pdo->prepare($sql_tracking);
    $stmt_tracking->execute([
        $pedido_id,
        'pedido_creado',
        'Pedido creado exitosamente con pago aprobado'
    ]);

    // Confirmar transacción
    $pdo->commit();

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pedido guardado exitosamente',
        'pedido_id' => $pedido_id,
        'data' => [
            'envio_guardado' => true,
            'pedido_guardado' => true,
            'tracking_creado' => true
        ]
    ]);

} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => true
    ]);
}
?>