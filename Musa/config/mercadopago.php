<?php
/**
 * 🔑 CONFIGURACIÓN DE CREDENCIALES MERCADOPAGO - AUTO-DETECT
 * Este archivo detecta automáticamente el entorno y usa las credenciales apropiadas
 */

require_once __DIR__ . '/config-global.php';

// ===== FUNCIONES HELPER =====

function getMercadoPagoPublicKey() {
    $config = GlobalConfig::getMercadoPagoConfig();
    return $config['public_key'];
}

function getMercadoPagoAccessToken() {
    $config = GlobalConfig::getMercadoPagoConfig();
    return $config['access_token'];
}

function getMercadoPagoEnvironment() {
    $config = GlobalConfig::getMercadoPagoConfig();
    return $config['environment'];
}

function isMercadoPagoConfigured() {
    $publicKey = getMercadoPagoPublicKey();
    $accessToken = getMercadoPagoAccessToken();
    
    return !empty($publicKey) && !empty($accessToken);
}

function getMercadoPagoUrls() {
    return GlobalConfig::getMercadoPagoUrls();
}

// ===== RESPUESTA JSON PARA FRONTEND =====
if (isset($_GET['get_config']) && $_GET['get_config'] === 'true') {
    header('Content-Type: application/json');
    
    $config = GlobalConfig::getMercadoPagoConfig();
    $urls = GlobalConfig::getMercadoPagoUrls();
    
    echo json_encode([
        'success' => true,
        'environment' => IS_PRODUCTION ? 'production' : 'development',
        'public_key' => $config['public_key'],
        'configured' => isMercadoPagoConfigured(),
        'urls' => $urls,
        'domain' => CURRENT_DOMAIN,
        'sandbox' => $config['environment'] === 'sandbox'
    ]);
    exit;
}
// (Eliminado bloque duplicado y líneas sobrantes)
}

?>