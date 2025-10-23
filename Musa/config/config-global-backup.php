<?php
/**
 * ====================================================
 * 🌍 CONFIGURACIÓN GLOBAL - DETECCIÓN AUTOMÁTICA DE ENTORNO
 * ====================================================
 * Este archivo detecta automáticamente si está en localhost o hosting
 * y configura todo el sistema en consecuencia
 */

class GlobalConfig {
    private static $isProduction = null;
    private static $domain = null;
    
    /**
     * Detectar si estamos en producción (hosting) o desarrollo (localhost)
     * FORZADO A PRODUCCIÓN
     */
    public static function isProduction() {
        // FORZAR MODO PRODUCCIÓN PARA PAGOS REALES
        return true;
    }

class GlobalConfig {
    private static $isProduction = null;
    private static $domain = null;
    
    /**
     * Detectar si estamos en producción (hosting) o desarrollo (localhost)
     * FORZADO A LOCALHOST/DESARROLLO
     */
    public static function isProduction() {
        // FORZAR LOCALHOST - Comentar hosting config
        /*
        if (self::$isProduction === null) {
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            self::$isProduction = !in_array($host, [
                'localhost', 
                '127.0.0.1', 
                'localhost:8080',
                'localhost:3000',
                '::1'
            ]);
        }
        return self::$isProduction;
        */
        
        // FORZAR MODO DESARROLLO
        return false; // Siempre localhost
    }
    
    /**
     * Obtener el dominio actual
     */
    public static function getDomain() {
        if (self::$domain === null) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            self::$domain = $protocol . $host;
        }
        return self::$domain;
    }
    
    /**
     * Obtener configuración de base de datos según entorno
     * FORZADO A LOCALHOST
     */
    public static function getDatabaseConfig() {
        // CONFIGURACIÓN DE HOSTING COMENTADA:
        /*
        if (self::isProduction()) {
            // HOSTING - Configuración real
            return [
                'host' => 'localhost',
                'dbname' => 'janithal_musa_moda',
                'username' => 'janithal_usuario_musaarion_db',
                'password' => 'Chiguiro553021',
                'charset' => 'utf8mb4'
            ];
        }
        */
        
        // FORZAR LOCALHOST - Configuración de desarrollo
        return [
            'host' => 'localhost',
            'dbname' => 'janithal_musa_moda', // Base de datos local (misma que hosting)
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ];
    }
    
    /**
     * Obtener credenciales de Mercado Pago según entorno
     * HABILITADO PARA PRODUCCIÓN
     */
    public static function getMercadoPagoConfig() {
        if (self::isProduction()) {
            // HOSTING - Credenciales de producción REALES
            return [
                'public_key' => 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577',
                'access_token' => 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340',
                'environment' => 'production'
            ];
        }
        
        // LOCALHOST - Credenciales de test para desarrollo
        return [
            'public_key' => 'TEST-4317f0d7-1b87-4e24-b76b-e1d5a3b2c4d5',
            'access_token' => 'TEST-4317f0d7-1b87-4e24-b76b-e1d5a3b2c4d5',
            'environment' => 'sandbox'
        ];
    }
    
    /**
     * Obtener URLs de retorno para Mercado Pago
     */
    public static function getMercadoPagoUrls() {
        $domain = self::getDomain();
        $basePath = self::isProduction() ? '' : '/Musa';
        
        return [
            'success' => $domain . $basePath . '/success.html',
            'failure' => $domain . $basePath . '/failure.html',
            'pending' => $domain . $basePath . '/pending.html',
            'notification' => $domain . $basePath . '/api/webhook-mercadopago.php'
        ];
    }
    
    /**
     * Obtener configuración completa del sistema
     */
    public static function getConfig() {
        return [
            'environment' => self::isProduction() ? 'production' : 'development',
            'domain' => self::getDomain(),
            'database' => self::getDatabaseConfig(),
            'mercadopago' => self::getMercadoPagoConfig(),
            'urls' => self::getMercadoPagoUrls(),
            'debug' => !self::isProduction(),
            'logs_enabled' => true,
            'secure_only' => self::isProduction()
        ];
    }
    
    /**
     * Log de depuración
     */
    public static function log($message, $data = []) {
        if (!self::isProduction()) {
            error_log("[MUSA-DEBUG] " . $message . " - " . json_encode($data));
        }
    }
    
    /**
     * Log de transacciones (siempre activo)
     */
    public static function logTransaction($message, $data = []) {
        error_log("[MUSA-TRANSACTION] " . $message . " - " . json_encode($data));
    }
}

// Definir constantes globales para compatibilidad
$config = GlobalConfig::getConfig();
$dbConfig = $config['database'];
$mpConfig = $config['mercadopago'];

define('DB_HOST', $dbConfig['host']);
define('DB_NAME', $dbConfig['dbname']);
define('DB_USER', $dbConfig['username']);
define('DB_PASS', $dbConfig['password']);
define('DB_CHARSET', $dbConfig['charset']);

define('MP_PUBLIC_KEY', $mpConfig['public_key']);
define('MP_ACCESS_TOKEN', $mpConfig['access_token']);
define('MP_ENVIRONMENT', $mpConfig['environment']);

define('IS_PRODUCTION', GlobalConfig::isProduction());
define('CURRENT_DOMAIN', GlobalConfig::getDomain());

?>