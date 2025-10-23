<?php
// Configurar logs detallados
ini_set('log_errors', 1);
ini_set('error_log', 'webhook_debug.log');
date_default_timezone_set('America/Bogota');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Función para log con timestamp
function logDebug($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data) {
        $logMessage .= ": " . print_r($data, true);
    }
    error_log($logMessage);
}

logDebug("=== NUEVO WEBHOOK MERCADOPAGO RECIBIDO ===");
logDebug("Método", $_SERVER['REQUEST_METHOD']);
logDebug("Headers", getallheaders());

// Obtener datos del webhook
$input = file_get_contents('php://input');
$data = json_decode($input, true);

logDebug("Datos webhook recibidos", $data);

try {
    // Verificar que sea una notificación de pago
    if (!empty($data['type']) && $data['type'] === 'payment') {
        $paymentId = $data['data']['id'];
        logDebug("ID de pago recibido", $paymentId);
        
        // Obtener detalles del pago desde MercadoPago
        $paymentDetails = getPaymentDetails($paymentId);
        logDebug("Detalles de pago obtenidos", $paymentDetails);
        
        if ($paymentDetails) {
            // Guardar el pedido en la base de datos
            $saved = saveOrderToDatabase($paymentDetails);
            
            if ($saved) {
                logDebug("✅ Pedido guardado exitosamente");
                echo json_encode(['status' => 'success', 'message' => 'Pedido guardado']);
            } else {
                logDebug("❌ Error guardando pedido");
                echo json_encode(['status' => 'error', 'message' => 'Error guardando pedido']);
            }
        }
    } else {
        logDebug("❌ Tipo de notificación no soportado o vacío", $data['type'] ?? 'VACÍO');
    }
    
} catch (Exception $e) {
    logDebug("❌ Error en webhook", $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

function getPaymentDetails($paymentId) {
    logDebug("🔍 Obteniendo detalles del pago", $paymentId);
    
    // Credenciales de MercadoPago
    $accessToken = 'APP_USR-6153740999578703-091219-3ce9553b42e997ddafbec8c4ee0c75c2-2567098533';
    
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$paymentId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer $accessToken"
        ]
    ]);
    
    $response = curl_exec($curl);
    $error = curl_error($curl);
    
    if ($error) {
        logDebug("❌ Error CURL obteniendo detalles", $error);
        return null;
    }
    
    curl_close($curl);
    $payment = json_decode($response, true);
    
    if (!$payment) {
        logDebug("❌ Error decodificando respuesta MP");
        return null;
    }
    
    logDebug("✅ Detalles de pago recibidos", $payment);
    return $payment;
}

function saveOrderToDatabase($payment) {
    try {
        logDebug("💾 Guardando pedido en base de datos", $payment['id']);
        
        // Conexión a la base de datos
        $host = 'localhost';
        $dbname = 'janithal_musa_moda';
        $username = 'janithal_usuario_musaarion_db';
        $password = 'Chiguiro553021';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Actualizar tabla pedidos para incluir campos de envío
        $pdo->exec("
            ALTER TABLE pedidos 
            ADD COLUMN IF NOT EXISTS shipping_address VARCHAR(255),
            ADD COLUMN IF NOT EXISTS shipping_city VARCHAR(100),
            ADD COLUMN IF NOT EXISTS shipping_state VARCHAR(100),
            ADD COLUMN IF NOT EXISTS shipping_country VARCHAR(100),
            ADD COLUMN IF NOT EXISTS shipping_zip VARCHAR(20),
            ADD COLUMN IF NOT EXISTS shipping_method VARCHAR(50),
            ADD COLUMN IF NOT EXISTS shipping_status VARCHAR(50) DEFAULT 'pending'
        ");
        
        // Extraer información de envío del pago
        $shippingInfo = $payment['additional_info']['shipments'] ?? [];
        $shippingAddress = $shippingInfo['receiver_address'] ?? [];
        
        // Preparar datos para inserción
        $orderData = [
            'payment_id' => $payment['id'],
            'external_reference' => $payment['external_reference'],
            'status' => $payment['status'],
            'amount' => $payment['transaction_amount'],
            'currency' => $payment['currency_id'],
            'payment_method' => $payment['payment_method_id'],
            'payer_name' => $payment['payer']['first_name'] . ' ' . $payment['payer']['last_name'],
            'payer_email' => $payment['payer']['email'],
            'payer_phone' => $payment['payer']['phone']['number'] ?? null,
            'payment_data' => json_encode($payment),
            'shipping_address' => $shippingAddress['street_name'] ?? null,
            'shipping_city' => $shippingAddress['city_name'] ?? null,
            'shipping_state' => $shippingAddress['state_name'] ?? null,
            'shipping_country' => $shippingAddress['country_name'] ?? null,
            'shipping_zip' => $shippingAddress['zip_code'] ?? null,
            'shipping_method' => 'standard',
            'shipping_status' => 'pending'
        ];
        
        // Query de inserción
        $sql = "
            INSERT INTO pedidos (
                payment_id, external_reference, status, amount, currency,
                payment_method, payer_name, payer_email, payer_phone,
                payment_data, shipping_address, shipping_city, shipping_state,
                shipping_country, shipping_zip, shipping_method, shipping_status
            ) VALUES (
                :payment_id, :external_reference, :status, :amount, :currency,
                :payment_method, :payer_name, :payer_email, :payer_phone,
                :payment_data, :shipping_address, :shipping_city, :shipping_state,
                :shipping_country, :shipping_zip, :shipping_method, :shipping_status
            )
            ON DUPLICATE KEY UPDATE
                status = VALUES(status),
                amount = VALUES(amount),
                payment_data = VALUES(payment_data),
                shipping_status = VALUES(shipping_status),
                updated_at = CURRENT_TIMESTAMP
        ";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute($orderData);
        
        logDebug($result ? "✅ Pedido guardado exitosamente" : "❌ Error guardando pedido");
        return $result;
        
    } catch (Exception $e) {
        logDebug("❌ Error en base de datos", $e->getMessage());
        return false;
    }
}
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/$paymentId",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return null;
}

function saveOrderToDatabase($paymentDetails) {
    try {
        // Configuración de base de datos
        $host = 'localhost';
        $dbname = 'musa_fashion'; // Cambiar por tu base de datos
        $username = 'root';
        $password = '';
        
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crear tabla si no existe
        $createTable = "
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id VARCHAR(50) UNIQUE,
            external_reference VARCHAR(100),
            status VARCHAR(20),
            amount DECIMAL(10,2),
            currency VARCHAR(5),
            payer_email VARCHAR(255),
            payer_name VARCHAR(255),
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            items_json TEXT,
            mercadopago_data JSON
        )";
        
        $pdo->exec($createTable);
        
        // Insertar o actualizar pedido
        $sql = "
        INSERT INTO pedidos (
            payment_id, external_reference, status, amount, currency,
            payer_email, payer_name, payment_method, items_json, mercadopago_data
        ) VALUES (
            :payment_id, :external_reference, :status, :amount, :currency,
            :payer_email, :payer_name, :payment_method, :items_json, :mercadopago_data
        ) ON DUPLICATE KEY UPDATE
            status = VALUES(status),
            updated_at = CURRENT_TIMESTAMP
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'payment_id' => $paymentDetails['id'],
            'external_reference' => $paymentDetails['external_reference'] ?? '',
            'status' => $paymentDetails['status'],
            'amount' => $paymentDetails['transaction_amount'],
            'currency' => $paymentDetails['currency_id'],
            'payer_email' => $paymentDetails['payer']['email'] ?? '',
            'payer_name' => ($paymentDetails['payer']['first_name'] ?? '') . ' ' . ($paymentDetails['payer']['last_name'] ?? ''),
            'payment_method' => $paymentDetails['payment_method_id'] ?? '',
            'items_json' => json_encode($paymentDetails['additional_info']['items'] ?? []),
            'mercadopago_data' => json_encode($paymentDetails)
        ]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error BD: " . $e->getMessage());
        return false;
    }
}

http_response_code(200);
?>