<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Detectar entorno automáticamente
    $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
    
    if ($isLocalhost) {
        // ===== CONFIGURACIÓN PARA SANDBOX =====
        // 1. Regístrate en: https://www.mercadopago.com.ar/developers/
        // 2. Ve a "Tus integraciones" → "Crear aplicación"
        // 3. Copia tu "Access Token" de PRUEBAS (TEST)
        // 4. Pégalo aquí abajo:
        
        $SANDBOX_MODE = true; // Cambiar a true para habilitar Sandbox
        
        if ($SANDBOX_MODE) {
            // 🏖️ SANDBOX MODE - Token TEST real configurado
            $accessToken = 'APP_USR-6153740999578703-091219-3ce9553b42e997ddafbec8c4ee0c75c2-2567098533';
            $environment = 'SANDBOX';
            
            error_log("🏖️ SANDBOX ACTIVADO - Redirección real a MercadoPago TEST");
            
            // Verificar si el token fue configurado
            if ($accessToken === 'PEGA_TU_TOKEN_TEST_AQUI') {
                throw new Exception('⚠️ CONFIGURA TU TOKEN SANDBOX: Ve a https://www.mercadopago.com.ar/developers/ y obtén tu Access Token TEST');
            }
            
            error_log("🏖️ SANDBOX MODE ACTIVO - Redirección real habilitada");
            
        } else {
            // 🎮 SIMULACIÓN LOCAL
            $environment = 'SIMULATION';
            error_log("🎮 MODO SIMULACIÓN - Para sandbox real configura $SANDBOX_MODE = true");
            
            $simulatedResponse = [
                'success' => true,
                'preference_id' => 'SIMULATION-' . time(),
                'init_point' => 'https://www.mercadopago.com.co/checkout/v1/redirect?pref_id=SIMULATION-' . time(),
                'sandbox_init_point' => 'https://sandbox.mercadopago.com.co/checkout/v1/redirect?pref_id=SIMULATION-' . time(),
                'environment' => 'SIMULATION',
                'message' => 'Simulación local activa - Configura Sandbox para pruebas reales'
            ];
            
            echo json_encode($simulatedResponse);
            exit();
        }
        
    } else {
        // HOSTING - Credenciales PRODUCCIÓN
        $accessToken = 'APP_USR-TU_ACCESS_TOKEN_PRODUCCION_AQUI';
        $environment = 'PRODUCTION';
    }
    
    // Obtener datos del POST
    $input = file_get_contents('php://input');
    
    // Log para debug
    error_log("INPUT RECIBIDO: " . $input);
    
    $data = json_decode($input, true);
    
    // Log para debug
    error_log("DATA DECODIFICADO: " . print_r($data, true));
    
    if (!$data) {
        error_log("ERROR: Datos de preferencia inválidos. Input: " . $input);
        throw new Exception('Datos de preferencia inválidos. Input recibido: ' . substr($input, 0, 200));
    }
    
    // Validar items requeridos
    if (empty($data['items'])) {
        throw new Exception('Items requeridos para crear la preferencia');
    }
    
    // Preparar datos de la preferencia para COLOMBIA
    $preferenceData = [
        'items' => $data['items'],
        'payer' => $data['payer'] ?? [
            'name' => 'Test',
            'surname' => 'User',
            'email' => 'test_user_123456@testuser.com'
        ],
        'back_urls' => [
            'success' => 'https://httpbin.org/get?status=success',
            'failure' => 'https://httpbin.org/get?status=failure', 
            'pending' => 'https://httpbin.org/get?status=pending'
        ],
        'external_reference' => $data['external_reference'] ?? 'order_' . time(),
        'payment_methods' => [
            'excluded_payment_types' => [],
            'excluded_payment_methods' => [],
            'installments' => 36 // Colombia permite hasta 36 cuotas
        ],
        'marketplace' => 'NONE'
    ];
    
    // Crear preferencia usando cURL
    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/checkout/preferences',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($preferenceData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'X-Integrator-Id: dev_24c65fb163bf11ea96500242ac130004'
        ],
    ]);
    
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    
    curl_close($curl);
    
    if ($error) {
        throw new Exception('Error cURL: ' . $error);
    }
    
    $responseData = json_decode($response, true);
    
    if ($httpCode !== 201) {
        throw new Exception('Error MercadoPago: ' . json_encode($responseData));
    }
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'preference_id' => $responseData['id'],
        'init_point' => $responseData['init_point'],
        'sandbox_init_point' => $responseData['sandbox_init_point'] ?? null,
        'environment' => $environment,
        'message' => 'Preferencia creada exitosamente'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'environment' => $environment ?? 'unknown'
    ]);
}
?>