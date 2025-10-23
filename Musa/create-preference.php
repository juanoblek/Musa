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

require_once 'vendor/autoload.php';
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;

try {
    // Detectar entorno automáticamente
    $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
    
    if ($isLocalhost) {
        // LOCALHOST - Credenciales TEST
        $accessToken = 'TEST-7893407008649567-102820-8e3c07b34a34ec2a23b5f71d1bf8a53a-2020782919';
        $environment = 'TEST';
    } else {
        // HOSTING - Credenciales PRODUCCIÓN
        $accessToken = 'APP_USR-7893407008649567-102820-9b2c6b8d7e5a8c8b23a5f61d1af7a42c-2020782919';
        $environment = 'PRODUCTION';
    }
    
    echo "<!-- DEBUG: Usando ambiente $environment para " . $_SERVER['HTTP_HOST'] . " -->\n";
    
    // Configurar MercadoPago
    MercadoPagoConfig::setAccessToken($accessToken);
    
    // Obtener datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos de preferencia inválidos');
    }
    
    // Validar items requeridos
    if (empty($data['items'])) {
        throw new Exception('Items requeridos para crear la preferencia');
    }
    
    // Preparar datos de la preferencia
    $preferenceData = [
        'items' => $data['items'],
        'payer' => $data['payer'] ?? [],
        'payment_methods' => $data['payment_methods'] ?? [
            'excluded_payment_types' => ["credit_card", "debit_card", "ticket", "bank_transfer"],
            'included_payment_methods' => ["pse"]
        ],
        'back_urls' => $data['back_urls'] ?? [
            'success' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/Musa/confirmacion-premium.html',
            'failure' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/Musa/failure-premium.html',
            'pending' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/Musa/pending-premium.html'
        ],
        'auto_return' => 'approved',
        'external_reference' => $data['external_reference'] ?? 'ORDER_' . time(),
        'statement_descriptor' => 'MUSA FASHION',
        'additional_info' => $data['additional_info'] ?? []
    ];
    
    // Agregar notification_url si no es localhost
    if (!$isLocalhost && isset($data['notification_url'])) {
        $preferenceData['notification_url'] = $data['notification_url'];
    }
    
    // Crear cliente de preferencias
    $client = new PreferenceClient();
    
    // Crear la preferencia
    $preference = $client->create($preferenceData);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'preference' => [
            'id' => $preference->id,
            'init_point' => $preference->init_point,
            'sandbox_init_point' => $preference->sandbox_init_point
        ],
        'environment' => $environment,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>