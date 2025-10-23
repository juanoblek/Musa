<?php
/**
 * 🔥 INTEGRACIÓN REAL MERCADOPAGO + BASE DE DATOS
 * Solo guarda en BD cuando MercadoPago confirma pago exitoso
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
require_once '../config/mercadopago.php';

try {
    // 🔑 OBTENER CREDENCIALES DESDE CONFIGURACIÓN
    if (!isMercadoPagoConfigured()) {
        throw new Exception("MercadoPago no está configurado correctamente");
    }
    
    $access_token = getMercadoPagoAccessToken();
    $public_key = getMercadoPagoPublicKey();
    
    if (!$access_token || !$public_key) {
        throw new Exception("Credenciales de MercadoPago no disponibles");
    }
    
    // Obtener datos del formulario
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception("No se recibieron datos válidos");
    }
    
    // ===== PASO 1: PREPARAR DATOS PARA MERCADOPAGO =====
    
    $cliente_email = trim($data['email'] ?? '');
    $cliente_nombre = trim($data['fullName'] ?? '');
    $cliente_documento = trim($data['documentNumber'] ?? $data['idNumber'] ?? '');
    $cliente_tipo_documento = trim($data['documentType'] ?? 'CC');
    
    $monto_total = floatval($data['total'] ?? 0);
    
    // Validar datos mínimos
    if (empty($cliente_email) || empty($cliente_nombre) || $monto_total <= 0) {
        throw new Exception("Faltan datos obligatorios para el pago");
    }
    
    // Generar ID único para el pedido
    $pedido_id = 'MUSA-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // ===== PASO 2: CREAR PAGO EN MERCADOPAGO =====
    
    $datos_pago_mp = [
        'transaction_amount' => $monto_total,
        'description' => "Pedido Musa Moda - $pedido_id",
        'payment_method_id' => 'visa', // Cambiar según el método
        'installments' => intval($data['installments'] ?? 1),
        'issuer_id' => null,
        'token' => $data['token'] ?? '', // Token del card generado en frontend
        'payer' => [
            'email' => $cliente_email,
            'identification' => [
                'type' => $cliente_tipo_documento,
                'number' => $cliente_documento
            ]
        ],
        'external_reference' => $pedido_id,
        'metadata' => [
            'pedido_id' => $pedido_id,
            'cliente_nombre' => $cliente_nombre,
            'origen' => 'musa_moda_web'
        ]
    ];
    
    // Enviar a MercadoPago
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($datos_pago_mp),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
            'X-Idempotency-Key: ' . uniqid() // Evitar pagos duplicados
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response_mp = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_error($curl)) {
        throw new Exception('Error de conexión con MercadoPago: ' . curl_error($curl));
    }
    
    curl_close($curl);
    
    $resultado_mp = json_decode($response_mp, true);
    
    // ===== PASO 3: EVALUAR RESPUESTA DE MERCADOPAGO =====
    
    if ($http_code !== 201 && $http_code !== 200) {
        // Error en MercadoPago
        $mensaje_error = $resultado_mp['message'] ?? 'Error desconocido en MercadoPago';
        
        echo json_encode([
            'success' => false,
            'pago_exitoso' => false,
            'message' => "Error en el pago: $mensaje_error",
            'mercadopago_error' => true,
            'error_details' => $resultado_mp,
            'datos_guardados' => false
        ]);
        exit();
    }
    
    // Verificar estado del pago
    $mp_payment_id = $resultado_mp['id'] ?? null;
    $mp_status = $resultado_mp['status'] ?? 'unknown';
    $mp_status_detail = $resultado_mp['status_detail'] ?? '';
    
    // ===== PASO 4: DECIDIR SI GUARDAR EN BASE DE DATOS =====
    
    $pago_aprobado = ($mp_status === 'approved');
    
    if (!$pago_aprobado) {
        // Pago NO aprobado - NO guardar en BD
        $mensaje_estado = [
            'pending' => 'Tu pago está siendo procesado. Te notificaremos cuando sea confirmado.',
            'in_process' => 'Estamos procesando tu pago. En breve te confirmaremos.',
            'rejected' => 'Tu pago fue rechazado. Verifica los datos de tu tarjeta e intenta nuevamente.',
            'cancelled' => 'El pago fue cancelado.'
        ];
        
        echo json_encode([
            'success' => false,
            'pago_exitoso' => false,
            'estado_pago' => $mp_status,
            'mercadopago_id' => $mp_payment_id,
            'message' => $mensaje_estado[$mp_status] ?? 'Estado de pago desconocido',
            'status_detail' => $mp_status_detail,
            'datos_guardados' => false,
            'pedido_id' => null
        ]);
        exit();
    }
    
    // ===== PASO 5: PAGO APROBADO - GUARDAR EN BASE DE DATOS =====
    
    // Crear conexión a BD
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    try {
        // Extraer datos del cliente
        $nombre_completo = trim($data['fullName'] ?? '');
        $email = $cliente_email;
        $telefono = trim($data['phone'] ?? '');
        $departamento = trim($data['department'] ?? '');
        $ciudad = trim($data['city'] ?? '');
        $direccion = trim($data['address'] ?? '');
        $codigo_postal = trim($data['postalCode'] ?? '');
        $notas_adicionales = trim($data['notes'] ?? '');
        
        // Preparar datos del pedido
        $productos = $data['productos'] ?? $data['items'] ?? '[]';
        $subtotal = floatval($data['subtotal'] ?? 0);
        $envio = floatval($data['envio'] ?? 15000);
        $metodo_pago = 'mercadopago_' . ($data['paymentMethod'] ?? 'credit_card');
        
        // Datos de pago con información real de MP
        $datos_pago_bd = json_encode([
            'mercadopago_id' => $mp_payment_id,
            'status' => $mp_status,
            'status_detail' => $mp_status_detail,
            'payment_method' => $resultado_mp['payment_method_id'] ?? '',
            'card_last_four_digits' => $resultado_mp['card']['last_four_digits'] ?? '',
            'installments' => $resultado_mp['installments'] ?? 1,
            'transaction_amount' => $resultado_mp['transaction_amount'] ?? $monto_total,
            'net_received_amount' => $resultado_mp['transaction_details']['net_received_amount'] ?? 0,
            'fecha_aprobacion' => $resultado_mp['date_approved'] ?? date('Y-m-d H:i:s')
        ]);
        
        // Insertar en tabla PEDIDOS
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
            $monto_total,
            'aprobado',
            $metodo_pago,
            $datos_pago_bd
        ]);
        
        // Insertar en tabla ENVIOS
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
        
        // Opcional: Insertar tracking
        try {
            $sql_tracking = "
                INSERT INTO pedido_tracking (pedido_id, estado_nuevo, comentario, fecha_cambio)
                VALUES (?, ?, ?, NOW())
            ";
            
            $stmt_tracking = $pdo->prepare($sql_tracking);
            $stmt_tracking->execute([
                $pedido_id,
                'pedido_creado',
                "Pedido creado con pago aprobado por MercadoPago. Payment ID: $mp_payment_id"
            ]);
        } catch (Exception $e) {
            // Si no existe tabla tracking, continuar
            error_log("Tracking no disponible: " . $e->getMessage());
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        // ===== PASO 6: RESPUESTA EXITOSA =====
        
        echo json_encode([
            'success' => true,
            'pago_exitoso' => true,
            'estado_pago' => 'approved',
            'message' => '¡Pago aprobado y pedido creado exitosamente!',
            'pedido_id' => $pedido_id,
            'mercadopago_id' => $mp_payment_id,
            'total_pagado' => $monto_total,
            'datos_guardados' => true,
            'aparece_en_panel' => true,
            'payment_details' => [
                'method' => $resultado_mp['payment_method_id'] ?? '',
                'installments' => $resultado_mp['installments'] ?? 1,
                'card_last_digits' => $resultado_mp['card']['last_four_digits'] ?? '',
                'net_amount' => $resultado_mp['transaction_details']['net_received_amount'] ?? 0
            ]
        ]);
        
    } catch (Exception $e) {
        // Error en BD - Rollback
        $pdo->rollback();
        
        // El pago YA fue aprobado en MP, así que logeamos el error
        error_log("ERROR CRÍTICO: Pago aprobado en MP pero falló BD. Payment ID: $mp_payment_id. Error: " . $e->getMessage());
        
        echo json_encode([
            'success' => false,
            'pago_exitoso' => true, // Pago sí fue exitoso en MP
            'estado_pago' => 'approved',
            'message' => 'Pago aprobado pero error al guardar pedido. Contacta soporte.',
            'mercadopago_id' => $mp_payment_id,
            'error_critico' => true,
            'soporte_requerido' => true,
            'datos_guardados' => false
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'pago_exitoso' => false,
        'message' => 'Error general: ' . $e->getMessage(),
        'datos_guardados' => false,
        'error' => true
    ]);
}
?>