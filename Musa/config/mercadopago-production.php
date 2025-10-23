<?php
/**
 * CONFIGURACIÓN DE PRODUCCIÓN MERCADOPAGO
 * ⚠️ IMPORTANTE: Este archivo contiene credenciales REALES de producción
 * 🔴 NUNCA subir a repositorios públicos
 */

// 🔴 CREDENCIALES DE PRODUCCIÓN REALES
define('MP_PRODUCTION_PUBLIC_KEY', 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577');
define('MP_PRODUCTION_ACCESS_TOKEN', 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340');

// 🔧 CONFIGURACIÓN DE ENTORNO
define('MP_ENVIRONMENT', 'production'); // 'sandbox' o 'production'
define('MP_COUNTRY', 'CO'); // Colombia
define('MP_CURRENCY', 'COP'); // Peso Colombiano

// 🌐 URLs DE PRODUCCIÓN
define('PRODUCTION_DOMAIN', 'https://tudominio.com'); // Cambiar por tu dominio real
define('SUCCESS_URL', PRODUCTION_DOMAIN . '/Musa/success.html');
define('FAILURE_URL', PRODUCTION_DOMAIN . '/Musa/failure.html');
define('PENDING_URL', PRODUCTION_DOMAIN . '/Musa/pending.html');

// 🔐 CONFIGURACIÓN DE SEGURIDAD
define('WEBHOOK_SECRET', 'MUSA_WEBHOOK_SECRET_2024'); // Cambiar por una clave secreta real
define('ALLOWED_IPS', [
    '209.225.49.173', // IP de MercadoPago
    '72.9.138.60',    // IP de MercadoPago
    // Agregar más IPs permitidas según documentación de MP
]);

// 📊 LOGGING
define('ENABLE_DEBUG_LOGS', false); // Desactivar en producción
define('LOG_TRANSACTIONS', true);    // Mantener logs de transacciones

// 🚨 VALIDACIONES
if (MP_ENVIRONMENT === 'production') {
    // Verificar que no se estén usando credenciales de test
    if (strpos(MP_PRODUCTION_PUBLIC_KEY, 'TEST-') === 0 || 
        strpos(MP_PRODUCTION_ACCESS_TOKEN, 'TEST-') === 0) {
        die('❌ ERROR: Credenciales de TEST detectadas en entorno de PRODUCCIÓN');
    }
    
    // Verificar HTTPS en producción (solo en servidores remotos)
    if (!isset($_SERVER['HTTPS']) && 
        isset($_SERVER['SERVER_NAME']) && 
        $_SERVER['SERVER_NAME'] !== 'localhost' && 
        $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
        die('❌ ERROR: HTTPS es requerido en producción');
    }
}

// 🔧 FUNCIONES HELPER
function getMercadoPagoConfig() {
    return [
        'public_key' => MP_PRODUCTION_PUBLIC_KEY,
        'access_token' => MP_PRODUCTION_ACCESS_TOKEN,
        'environment' => MP_ENVIRONMENT,
        'country' => MP_COUNTRY,
        'currency' => MP_CURRENCY,
        'success_url' => SUCCESS_URL,
        'failure_url' => FAILURE_URL,
        'pending_url' => PENDING_URL
    ];
}

function isProduction() {
    return MP_ENVIRONMENT === 'production';
}

function logTransaction($message, $data = []) {
    if (LOG_TRANSACTIONS) {
        error_log("[MUSA-MP] " . $message . " - " . json_encode($data));
    }
}

function debugLog($message, $data = []) {
    if (ENABLE_DEBUG_LOGS) {
        error_log("[MUSA-DEBUG] " . $message . " - " . json_encode($data));
    }
}
?>