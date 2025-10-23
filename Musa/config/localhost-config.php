<?php
/**
 * ===============================================
 * 🏠 CONFIGURACIÓN LOCALHOST - DESARROLLO
 * ===============================================
 * Configuración específica para desarrollo local
 * Todas las configuraciones de hosting están comentadas
 * ===============================================
 */

// ===== CONFIGURACIÓN DE BASE DE DATOS LOCALHOST =====
define('LOCALHOST_DB_HOST', 'localhost');
define('LOCALHOST_DB_NAME', 'musa_moda');
define('LOCALHOST_DB_USER', 'root');
define('LOCALHOST_DB_PASS', '');
define('LOCALHOST_DB_CHARSET', 'utf8mb4');

// ===== CONFIGURACIÓN MERCADOPAGO LOCALHOST =====
define('LOCALHOST_MP_PUBLIC_KEY', 'TEST-4317f0d7-1b87-4e24-b76b-e1d5a3b2c4d5');
define('LOCALHOST_MP_ACCESS_TOKEN', 'TEST-4317f0d7-1b87-4e24-b76b-e1d5a3b2c4d5');
define('LOCALHOST_MP_ENVIRONMENT', 'sandbox');

// ===== URLS LOCALHOST =====
define('LOCALHOST_BASE_URL', 'http://localhost/Musa');
define('LOCALHOST_SUCCESS_URL', 'http://localhost/Musa/success-premium.html');
define('LOCALHOST_FAILURE_URL', 'http://localhost/Musa/failure-premium.html');
define('LOCALHOST_PENDING_URL', 'http://localhost/Musa/pending-premium.html');
define('LOCALHOST_WEBHOOK_URL', 'http://localhost/Musa/api/webhook-mercadopago.php');

// ===== CONFIGURACIÓN DE DESARROLLO =====
define('LOCALHOST_DEBUG', true);
define('LOCALHOST_ERROR_REPORTING', E_ALL);
define('LOCALHOST_LOG_ERRORS', true);
define('LOCALHOST_DISPLAY_ERRORS', true);

// ===== CONFIGURACIÓN COMENTADA DE HOSTING =====
/*
HOSTING DATABASE CONFIG:
define('HOSTING_DB_HOST', 'localhost');
define('HOSTING_DB_NAME', 'janithal_musa_moda');
define('HOSTING_DB_USER', 'janithal_usuario_musaarion_db');
define('HOSTING_DB_PASS', 'Chiguiro553021');
define('HOSTING_DB_CHARSET', 'utf8mb4');

HOSTING MERCADOPAGO CONFIG:
define('HOSTING_MP_PUBLIC_KEY', 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577');
define('HOSTING_MP_ACCESS_TOKEN', 'APP_USR-3757332100534516-071917-0a67a6a614cae908dff22da6254a0763-285063501');
define('HOSTING_MP_ENVIRONMENT', 'production');

HOSTING URLS:
define('HOSTING_BASE_URL', 'https://tudominio.com');
define('HOSTING_SUCCESS_URL', 'https://tudominio.com/success.html');
define('HOSTING_FAILURE_URL', 'https://tudominio.com/failure.html');
define('HOSTING_PENDING_URL', 'https://tudominio.com/pending.html');
define('HOSTING_WEBHOOK_URL', 'https://tudominio.com/api/webhook-mercadopago.php');

HOSTING SECURITY:
define('HOSTING_DEBUG', false);
define('HOSTING_ERROR_REPORTING', E_ERROR | E_WARNING);
define('HOSTING_LOG_ERRORS', true);
define('HOSTING_DISPLAY_ERRORS', false);
define('HOSTING_SSL_REQUIRED', true);
*/

// ===== APLICAR CONFIGURACIÓN LOCALHOST =====
ini_set('display_errors', LOCALHOST_DISPLAY_ERRORS);
ini_set('log_errors', LOCALHOST_LOG_ERRORS);
error_reporting(LOCALHOST_ERROR_REPORTING);

// ===== FUNCIONES HELPER LOCALHOST =====
function getLocalhostDBConnection() {
    try {
        $dsn = sprintf(
            "mysql:host=%s;dbname=%s;charset=%s",
            LOCALHOST_DB_HOST,
            LOCALHOST_DB_NAME,
            LOCALHOST_DB_CHARSET
        );
        
        $pdo = new PDO($dsn, LOCALHOST_DB_USER, LOCALHOST_DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . LOCALHOST_DB_CHARSET
        ]);
        
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Error conexión localhost: " . $e->getMessage());
    }
}

function getLocalhostMercadoPagoConfig() {
    return [
        'public_key' => LOCALHOST_MP_PUBLIC_KEY,
        'access_token' => LOCALHOST_MP_ACCESS_TOKEN,
        'environment' => LOCALHOST_MP_ENVIRONMENT
    ];
}

function getLocalhostUrls() {
    return [
        'base' => LOCALHOST_BASE_URL,
        'success' => LOCALHOST_SUCCESS_URL,
        'failure' => LOCALHOST_FAILURE_URL,
        'pending' => LOCALHOST_PENDING_URL,
        'webhook' => LOCALHOST_WEBHOOK_URL
    ];
}

// ===== LOG DE CONFIGURACIÓN =====
if (LOCALHOST_DEBUG) {
    error_log("=== MUSA LOCALHOST CONFIG LOADED ===");
    error_log("Database: " . LOCALHOST_DB_NAME);
    error_log("MercadoPago: " . LOCALHOST_MP_ENVIRONMENT);
    error_log("Base URL: " . LOCALHOST_BASE_URL);
}

?>