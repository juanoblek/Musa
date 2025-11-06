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
     * MODO FLEXIBLE - PERMITE PRUEBAS EN LOCALHOST
     */
    public static function isProduction() {
        if (self::$isProduction === null) {
            // TEMPORAL: Forzar modo TEST hasta obtener credenciales válidas
            // TODO: Cambiar a true cuando tengas access_token de PRODUCCIÓN válido
            self::$isProduction = false;
        }
        return self::$isProduction;
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
     */
    public static function getDatabaseConfig() {
        if (self::isProduction()) {
            // HOSTING - Configuración de producción
            return [
                'host' => 'localhost', // Host para conexión a base de datos en hosting
                'dbname' => 'janithal_musa_moda',
                'username' => 'janithal_usuario_musaarion_db',
                'password' => 'Chiguiro553021',
                'charset' => 'utf8mb4'
            ];
        }
        
        // LOCALHOST - Configuración de desarrollo
        return [
            'host' => 'localhost',
            'dbname' => 'janithal_musa_moda', // Base de datos local (misma que hosting)
            'username' => 'root',
            'password' => 'root', // Contraseña por defecto para XAMPP
            'charset' => 'utf8mb4'
        ];
    }
    
    /**
     * Obtener credenciales de Mercado Pago según entorno
     * TEST en localhost, PRODUCCIÓN en hosting
     */
    public static function getMercadoPagoConfig() {
        if (self::isProduction()) {
            // HOSTING - Credenciales de PRODUCCIÓN
            return [
                'public_key' => 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577',
                'access_token' => 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340',
                'environment' => 'production'
            ];
        } else {
            // LOCALHOST - Credenciales de TEST para pruebas
            return [
                'public_key' => 'TEST-70da85e6-8bcb-4f2c-9c62-d8532ae88a4a',
                'access_token' => 'TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501',
                'environment' => 'test'
            ];
        }
    }

    /**
     * Configuración temporal para pruebas PSE - SOLO PARA TESTING
     * Esta función permite probar PSE en sandbox sin afectar otras funcionalidades
     */
    public static function getPSETestConfig() {
        return [
            'public_key' => 'TEST-70da85e6-8bcb-4f2c-9c62-d8532ae88a4a',
            'access_token' => 'TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501',
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
            'success' => $domain . $basePath . '/Musa/success.html',
            'failure' => $domain . $basePath . '/Musa/failure.html',
            'pending' => $domain . $basePath . '/Musa/pending.html'
        ];
    }
    
    /**
     * Obtener configuración de email según entorno
     */
    public static function getEmailConfig() {
        if (self::isProduction()) {
            // HOSTING - Configuración de email real
            return [
                'method' => 'smtp',
                'host' => 'mail.tudominio.com',
                'port' => 587,
                'username' => 'noreply@tudominio.com',
                'password' => 'password_email',
                'from_email' => 'noreply@tudominio.com',
                'from_name' => 'Musa & Arion'
            ];
        }
        
        // LOCALHOST - Configuración de email simulado
        return [
            'method' => 'file',
            'log_path' => '../logs/emails.log',
            'from_email' => 'noreply@localhost',
            'from_name' => 'Musa & Arion (Local)'
        ];
    }
    
    /**
     * Obtener configuración completa del sistema
     */
    public static function getSystemConfig() {
        return [
            'environment' => self::isProduction() ? 'production' : 'development',
            'domain' => self::getDomain(),
            'database' => self::getDatabaseConfig(),
            'mercadopago' => self::getMercadoPagoConfig(),
            'urls' => self::getMercadoPagoUrls(),
            'email' => self::getEmailConfig(),
            'debug' => !self::isProduction()
        ];
    }
    
    /**
     * Verificar si la configuración es válida
     */
    public static function isConfigValid() {
        $mpConfig = self::getMercadoPagoConfig();
        return !empty($mpConfig['public_key']) && !empty($mpConfig['access_token']);
    }
    
    /**
     * Obtener información de debug
     */
    public static function getDebugInfo() {
        return [
            'environment' => self::isProduction() ? 'production' : 'development',
            'domain' => self::getDomain(),
            'mp_environment' => self::getMercadoPagoConfig()['environment'],
            'mp_key_preview' => substr(self::getMercadoPagoConfig()['public_key'], 0, 20) . '...',
            'config_valid' => self::isConfigValid(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// Funciones de compatibilidad para código legacy
function isLocalhost() {
    return !GlobalConfig::isProduction();
}

function getMercadoPagoEnvironment() {
    return GlobalConfig::getMercadoPagoConfig()['environment'];
}
?>