<?php
// Copia exacta de config/mercadopago.php, pero accesible públicamente desde /api
require_once __DIR__ . '/../config/config-global.php';

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

if (isset($_GET['get_config']) && $_GET['get_config'] === 'true') {
    header('Content-Type: application/json');
    $config = GlobalConfig::getMercadoPagoConfig();
    $urls = GlobalConfig::getMercadoPagoUrls();
    echo json_encode([
        'success' => true,
        'environment' => defined('IS_PRODUCTION') && IS_PRODUCTION ? 'production' : 'development',
        'public_key' => $config['public_key'],
        'configured' => isMercadoPagoConfigured(),
        'urls' => $urls,
        'domain' => defined('CURRENT_DOMAIN') ? CURRENT_DOMAIN : '',
        'sandbox' => $config['environment'] === 'sandbox'
    ]);
    exit;
}
