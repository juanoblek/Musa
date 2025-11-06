<?php
/**
 * Script de verificación de configuración MercadoPago
 * Úsalo para diagnosticar problemas de credenciales
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Cargar configuración
$cfgPaths = [
    __DIR__ . '/../config/config-global.php',
    __DIR__ . '/config/config-global.php',
    dirname(__DIR__) . '/config/config-global.php'
];

$found = null;
foreach ($cfgPaths as $p) {
    if (file_exists($p)) {
        $found = $p;
        require_once $p;
        break;
    }
}

if (!$found) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Config no encontrada',
        'paths_checked' => $cfgPaths
    ]);
    exit;
}

try {
    $mpConfig = GlobalConfig::getMercadoPagoConfig();
    $isProduction = GlobalConfig::isProduction();
    
    $response = [
        'ok' => true,
        'environment' => $isProduction ? 'PRODUCTION' : 'DEVELOPMENT',
        'domain' => GlobalConfig::getDomain(),
        'config_file' => $found,
        'mercadopago' => [
            'public_key_present' => !empty($mpConfig['public_key']),
            'public_key_prefix' => substr($mpConfig['public_key'], 0, 20) . '...',
            'access_token_present' => !empty($mpConfig['access_token']),
            'access_token_prefix' => substr($mpConfig['access_token'], 0, 20) . '...',
            'environment' => $mpConfig['environment']
        ]
    ];
    
    // Verificar si el access_token es válido haciendo una petición de prueba a MercadoPago
    if (!empty($mpConfig['access_token'])) {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.mercadopago.com/v1/payment_methods',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $mpConfig['access_token']
            ]
        ]);
        
        $result = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $response['mercadopago']['token_validation'] = [
            'tested' => true,
            'http_code' => $httpCode,
            'valid' => ($httpCode === 200),
            'message' => ($httpCode === 200) 
                ? '✅ Token válido y funcional' 
                : '❌ Token inválido o vencido (HTTP ' . $httpCode . ')'
        ];
        
        if ($httpCode !== 200) {
            $errorData = json_decode($result, true);
            $response['mercadopago']['token_validation']['error_detail'] = $errorData;
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
