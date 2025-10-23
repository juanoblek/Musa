<?php
/**
 * API PARA GUARDAR PEDIDOS EN TABLA ORIGINAL PEDIDOS
 * Usa la estructura existente del sistema
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

    // Log de inicio
    error_log('📥 Recibiendo petición de guardado de pedido...');

    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    error_log('📦 Datos recibidos: ' . json_encode($input));
    
    if (!$input) {
        throw new Exception('Datos JSON inválidos');
    }

    // Validar que el pago sea exitoso
    if (!isset($input['pago_exitoso']) || !$input['pago_exitoso']) {
        error_log('❌ Pago no exitoso o campo faltante');
        throw new Exception('Solo se guardan pedidos con pago exitoso');
    }
    
    error_log('✅ Validación de pago exitoso completada');

    // Obtener datos del carrito y otros datos
    $carrito = $input['carrito'] ?? [];
    $datos_pago = $input['datos_pago'] ?? [];
    $metodo_pago = $input['metodo_pago'] ?? 'mercadopago';
    
    error_log('🛒 Carrito recibido: ' . json_encode($carrito));
    error_log('💳 Datos de pago: ' . json_encode($datos_pago));
    error_log('🔧 Método de pago: ' . $metodo_pago);
    
    // Validaciones básicas
    if (empty($carrito)) {
        error_log('❌ Carrito vacío');
        throw new Exception('El carrito no puede estar vacío');
    }
    
    error_log('✅ Validaciones básicas completadas');

    // Conectar a la base de datos usando configuración automática
    error_log('🔌 Conectando a la base de datos...');
    
    require_once '../config/config-global.php';
    $dbConfig = GlobalConfig::getDatabaseConfig();
    
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    error_log('✅ Conexión a base de datos exitosa (' . ($dbConfig['username'] === 'root' ? 'LOCALHOST' : 'PRODUCCIÓN') . ')');

    // Generar ID único del pedido (igual que el original)
    $pedido_id = 'MUSA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    
    error_log('🆔 ID del pedido generado: ' . $pedido_id);

    // Validar datos requeridos de envío
    $envio = $input['envio'] ?? [];
    $required_shipping = ['nombre_completo', 'email', 'telefono', 'departamento', 'ciudad', 'direccion'];
    foreach ($required_shipping as $field) {
        if (empty($envio[$field])) {
            error_log("❌ Campo requerido faltante en envío: $field");
            throw new Exception("Campo requerido faltante en envío: $field");
        }
    }
    
    error_log('✅ Validación de datos de envío completada');

    // Preparar datos (USAR CARRITO COMO PRODUCTOS)
    $productos = $carrito; // Usar el carrito que ya validamos
    $subtotal = floatval($input['subtotal'] ?? 0);
    $costo_envio = floatval($input['costo_envio'] ?? 0);
    $total = floatval($input['total'] ?? 0);
    
    error_log('💰 Datos financieros: subtotal=' . $subtotal . ', envio=' . $costo_envio . ', total=' . $total);

    // Determinar método de pago y datos específicos
    $metodo_pago = $input['metodo_pago'] ?? 'mercadopago';
    $datos_pago = $input['datos_pago'] ?? [];
    
    error_log('🔧 Método de pago procesado: ' . $metodo_pago);
    
    // Si no hay datos de pago específicos, usar el payment_id como referencia
    if (empty($datos_pago)) {
        $datos_pago = ['payment_id' => $input['payment_id'] ?? 'TEST_PAYMENT'];
    }
    
    error_log('💳 Datos de pago finales: ' . json_encode($datos_pago));

    // Iniciar transacción
    error_log('🔄 Iniciando transacción en base de datos...');
    $pdo->beginTransaction();

    // 1. INSERTAR EN PEDIDOS PRIMERO (para evitar foreign key error)
    error_log('📝 Insertando en tabla pedidos...');
    $sql_pedido = "
        INSERT INTO pedidos (
            pedido_id, productos, subtotal, envio, total, 
            estado_pago, metodo_pago, datos_pago
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt_pedido = $pdo->prepare($sql_pedido);
    $resultado_pedido = $stmt_pedido->execute([
        $pedido_id,
        json_encode($productos),
        $subtotal,
        $costo_envio,
        $total,
        'aprobado',
        $metodo_pago,
        json_encode($datos_pago)
    ]);
    
    error_log('✅ Pedido insertado exitosamente: ' . ($resultado_pedido ? 'SI' : 'NO'));

    // 2. INSERTAR EN ENVIOS DESPUÉS
    error_log('📦 Insertando datos de envío...');
    $sql_envio = "
        INSERT INTO envios (
            pedido_id, nombre_completo, email, telefono, 
            departamento, ciudad, direccion, codigo_postal, notas_adicionales
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt_envio = $pdo->prepare($sql_envio);
    $resultado_envio = $stmt_envio->execute([
        $pedido_id,
        $envio['nombre_completo'],
        $envio['email'],
        $envio['telefono'],
        $envio['departamento'],
        $envio['ciudad'],
        $envio['direccion'],
        $envio['codigo_postal'] ?? null,
        $envio['notas_adicionales'] ?? null
    ]);
    
    error_log('✅ Envío insertado exitosamente: ' . ($resultado_envio ? 'SI' : 'NO'));

    // 3. INSERTAR TRACKING (si la tabla existe)
    try {
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
    } catch (Exception $e) {
        error_log("⚠️ No se pudo crear tracking: " . $e->getMessage());
        // Continuar sin tracking si la tabla no existe
    }

    // Confirmar transacción
    $pdo->commit();

    error_log("✅ PEDIDO GUARDADO EN TABLAS ORIGINALES: " . $pedido_id);
    error_log("- Cliente: " . $envio['nombre_completo']);
    error_log("- Total: $" . number_format($total));

    // 🚀 ENVIAR NOTIFICACIÓN POR EMAIL
    try {
        require_once 'email-notification-api.php';
        $emailNotification = new EmailNotificationAPI();
        
        $pedidoData = [
            'pedido_id' => $pedido_id,
            'envio' => $envio,
            'productos' => $productos,
            'subtotal' => $subtotal,
            'costo_envio' => $costo_envio,
            'total' => $total,
            'payment_id' => $input['payment_id'] ?? 'TEST_PAYMENT'
        ];
        
        $emailSent = $emailNotification->sendNewOrderNotification($pedidoData);
        error_log($emailSent ? "✅ Notificación de email procesada" : "⚠️ Notificación guardada en archivo");
        
    } catch (Exception $emailError) {
        error_log("⚠️ Error en sistema de notificación: " . $emailError->getMessage());
        // No fallar el pedido por error de email
        $emailSent = false;
    }

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Pedido guardado exitosamente en sistema original',
        'pedido_id' => $pedido_id,
        'total' => $total,
        'cliente' => $envio['nombre_completo'],
        'database' => 'original',
        'email_sent' => $emailSent ?? false
    ]);

} catch (Exception $e) {
    // Rollback en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log('❌ Error guardando pedido en tablas originales: ' . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'database' => 'original'
    ]);
}
?>