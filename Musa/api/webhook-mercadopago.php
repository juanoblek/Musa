<?php
/**
 * 🔥 WEBHOOK MERCADOPAGO - PROCESAR NOTIFICACIONES AUTOMÁTICAS
 * Detecta cuando MercadoPago confirma un pago y guarda automáticamente en BD
 */

header('Content-Type: application/json; charset=utf-8');

// Log de debugging
function logWebhook($message, $data = null) {
    $log_file = '../logs/webhook-mercadopago.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $message";
    
    if ($data) {
        $log_entry .= " | Data: " . json_encode($data);
    }
    
    $log_entry .= "\n";
    
    // Crear directorio de logs si no existe
    $log_dir = dirname($log_file);
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}

try {
    logWebhook("🔔 Webhook recibido", $_GET);
    
    // Verificar que es una notificación válida
    if (!isset($_GET['type']) || !isset($_GET['data.id'])) {
        logWebhook("❌ Webhook inválido - faltan parámetros");
        http_response_code(400);
        echo json_encode(['error' => 'Parámetros inválidos']);
        exit();
    }
    
    $type = $_GET['type'];
    $data_id = $_GET['data.id'];
    
    logWebhook("📝 Tipo: $type | ID: $data_id");
    
    // Solo procesar notificaciones de pago
    if ($type !== 'payment') {
        logWebhook("ℹ️ Tipo de notificación ignorado: $type");
        http_response_code(200);
        echo json_encode(['status' => 'ok', 'message' => 'Tipo ignorado']);
        exit();
    }
    
    // ===== OBTENER INFORMACIÓN DEL PAGO DESDE MERCADOPAGO =====
    
    require_once '../config/mercadopago.php';
    
    if (!isMercadoPagoConfigured()) {
        throw new Exception("MercadoPago no está configurado correctamente");
    }
    
    $access_token = getMercadoPagoAccessToken();
    
    if (!$access_token) {
        throw new Exception("Access token de MercadoPago no disponible");
    }
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$data_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    if (curl_error($curl)) {
        throw new Exception('Error consultando MercadoPago: ' . curl_error($curl));
    }
    
    curl_close($curl);
    
    if ($http_code !== 200) {
        throw new Exception("Error HTTP al consultar pago: $http_code");
    }
    
    $payment_info = json_decode($response, true);
    
    if (!$payment_info) {
        throw new Exception("Respuesta inválida de MercadoPago");
    }
    
    logWebhook("💳 Información de pago obtenida", [
        'id' => $payment_info['id'],
        'status' => $payment_info['status'],
        'status_detail' => $payment_info['status_detail'],
        'external_reference' => $payment_info['external_reference'] ?? null
    ]);
    
    // ===== PROCESAR SEGÚN ESTADO DEL PAGO =====
    
    $payment_status = $payment_info['status'];
    $payment_id = $payment_info['id'];
    $external_reference = $payment_info['external_reference'] ?? null;
    
    if ($payment_status === 'approved') {
        // ✅ PAGO APROBADO - GUARDAR EN BASE DE DATOS
        $resultado = procesarPagoAprobado($payment_info);
        logWebhook("✅ Pago aprobado procesado", $resultado);
        
    } elseif ($payment_status === 'pending') {
        // ⏳ PAGO PENDIENTE - ACTUALIZAR ESTADO
        logWebhook("⏳ Pago pendiente: $payment_id");
        actualizarEstadoPago($payment_id, 'pendiente', $payment_info);
        
    } elseif ($payment_status === 'rejected') {
        // ❌ PAGO RECHAZADO - REGISTRAR
        logWebhook("❌ Pago rechazado: $payment_id");
        actualizarEstadoPago($payment_id, 'rechazado', $payment_info);
        
    } else {
        // 🤷 OTRO ESTADO
        logWebhook("🤷 Estado desconocido: $payment_status para pago $payment_id");
        actualizarEstadoPago($payment_id, $payment_status, $payment_info);
    }
    
    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'status' => 'ok',
        'message' => 'Webhook procesado correctamente',
        'payment_id' => $payment_id,
        'status' => $payment_status
    ]);
    
} catch (Exception $e) {
    logWebhook("❌ Error procesando webhook: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

/**
 * Procesar pago aprobado y guardar en base de datos
 */
function procesarPagoAprobado($payment_info) {
    require_once '../config/database.php';
    
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $payment_id = $payment_info['id'];
        $external_reference = $payment_info['external_reference'] ?? null;
        
        // Verificar si ya existe el pago
        $sql_check = "SELECT id FROM pedidos WHERE JSON_UNQUOTE(JSON_EXTRACT(datos_pago, '$.mercadopago_id')) = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$payment_id]);
        
        if ($stmt_check->fetch()) {
            logWebhook("⚠️ Pago ya procesado anteriormente: $payment_id");
            return ['status' => 'already_processed', 'payment_id' => $payment_id];
        }
        
        // Buscar datos de la preferencia guardada
        $order_data = null;
        
        try {
            $sql_pref = "SELECT order_data FROM mercadopago_preferencias WHERE external_reference = ? OR preference_id = ?";
            $stmt_pref = $pdo->prepare($sql_pref);
            $stmt_pref->execute([$external_reference, $payment_info['collector_id'] ?? '']);
            
            $pref_row = $stmt_pref->fetch(PDO::FETCH_ASSOC);
            if ($pref_row && $pref_row['order_data']) {
                $order_data = json_decode($pref_row['order_data'], true);
                logWebhook("📋 Datos de pedido encontrados en preferencia");
            }
        } catch (Exception $e) {
            logWebhook("⚠️ No se pudieron obtener datos de preferencia: " . $e->getMessage());
        }
        
        // Si no hay datos de preferencia, usar datos básicos del pago
        if (!$order_data) {
            $order_data = extraerDatosBasicosDePago($payment_info);
            logWebhook("📋 Usando datos básicos extraídos del pago");
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Generar ID único para el pedido
        $pedido_id = $external_reference ?: ('MUSA-WH-' . date('Ymd') . '-' . substr($payment_id, -6));
        
        // Preparar datos del pedido
        $productos = $order_data['productos'] ?? json_encode([]);
        $subtotal = floatval($order_data['subtotal'] ?? $payment_info['transaction_amount'] - 15000);
        $envio = floatval($order_data['envio'] ?? 15000);
        $total = floatval($payment_info['transaction_amount']);
        
        // Datos de pago con información completa de MP
        $datos_pago = json_encode([
            'mercadopago_id' => $payment_id,
            'status' => $payment_info['status'],
            'status_detail' => $payment_info['status_detail'],
            'payment_method_id' => $payment_info['payment_method_id'],
            'payment_type_id' => $payment_info['payment_type_id'],
            'card_last_four_digits' => $payment_info['card']['last_four_digits'] ?? null,
            'installments' => $payment_info['installments'] ?? 1,
            'transaction_amount' => $payment_info['transaction_amount'],
            'net_received_amount' => $payment_info['transaction_details']['net_received_amount'] ?? 0,
            'fecha_aprobacion' => $payment_info['date_approved'],
            'webhook_processed' => true,
            'external_reference' => $external_reference
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
            $total,
            'aprobado',
            'mercadopago_webhook',
            $datos_pago
        ]);
        
        // Insertar en tabla ENVIOS
        $nombre_completo = $order_data['fullName'] ?? $payment_info['payer']['first_name'] . ' ' . $payment_info['payer']['last_name'];
        $email = $order_data['email'] ?? $payment_info['payer']['email'];
        $telefono = $order_data['phone'] ?? $payment_info['payer']['phone']['number'] ?? '';
        
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
            $order_data['department'] ?? '',
            $order_data['city'] ?? '',
            $order_data['address'] ?? '',
            $order_data['postalCode'] ?? '',
            $order_data['notes'] ?? 'Pedido procesado via webhook MercadoPago',
            'pendiente'
        ]);
        
        // Tracking (opcional)
        try {
            $sql_tracking = "
                INSERT INTO pedido_tracking (pedido_id, estado_nuevo, comentario, fecha_cambio)
                VALUES (?, ?, ?, NOW())
            ";
            
            $stmt_tracking = $pdo->prepare($sql_tracking);
            $stmt_tracking->execute([
                $pedido_id,
                'pedido_creado_webhook',
                "Pedido creado automáticamente via webhook. Payment ID: $payment_id"
            ]);
        } catch (Exception $e) {
            logWebhook("⚠️ Error en tracking (no crítico): " . $e->getMessage());
        }
        
        // Actualizar estado de preferencia
        try {
            $sql_update_pref = "UPDATE mercadopago_preferencias SET estado = 'pagada' WHERE external_reference = ?";
            $stmt_update_pref = $pdo->prepare($sql_update_pref);
            $stmt_update_pref->execute([$external_reference]);
        } catch (Exception $e) {
            logWebhook("⚠️ Error actualizando preferencia (no crítico): " . $e->getMessage());
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        logWebhook("✅ Pedido creado exitosamente via webhook", [
            'pedido_id' => $pedido_id,
            'payment_id' => $payment_id,
            'total' => $total
        ]);
        
        return [
            'status' => 'success',
            'pedido_id' => $pedido_id,
            'payment_id' => $payment_id,
            'total' => $total,
            'message' => 'Pedido creado exitosamente'
        ];
        
    } catch (Exception $e) {
        $pdo->rollback();
        throw new Exception("Error guardando pedido: " . $e->getMessage());
    }
}

/**
 * Actualizar estado de pago sin crear pedido
 */
function actualizarEstadoPago($payment_id, $estado, $payment_info) {
    // Aquí podrías registrar estados de pagos para seguimiento
    // Por ahora solo logueamos
    logWebhook("📝 Estado actualizado", [
        'payment_id' => $payment_id,
        'estado' => $estado,
        'status_detail' => $payment_info['status_detail'] ?? null
    ]);
}

/**
 * Extraer datos básicos si no hay preferencia guardada
 */
function extraerDatosBasicosDePago($payment_info) {
    $payer = $payment_info['payer'] ?? [];
    
    return [
        'fullName' => ($payer['first_name'] ?? '') . ' ' . ($payer['last_name'] ?? ''),
        'email' => $payer['email'] ?? '',
        'phone' => $payer['phone']['number'] ?? '',
        'documentType' => $payer['identification']['type'] ?? 'CC',
        'documentNumber' => $payer['identification']['number'] ?? '',
        'address' => '', // No disponible en webhook
        'city' => '',    // No disponible en webhook
        'department' => '', // No disponible en webhook
        'productos' => json_encode([
            [
                'nombre' => 'Productos MUSA Fashion',
                'precio' => $payment_info['transaction_amount'] - 15000,
                'cantidad' => 1
            ]
        ]),
        'subtotal' => $payment_info['transaction_amount'] - 15000,
        'envio' => 15000,
        'total' => $payment_info['transaction_amount']
    ];
}

?>