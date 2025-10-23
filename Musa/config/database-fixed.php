<?php
/**
 * ====================================================
 * 📗 CONFIGURACIÓN DE BASE DE DATOS - M & A MODA ACTUAL
 * ====================================================
 */

// Configuración de base de datos con constantes
define('DB_HOST', 'localhost');
define('DB_NAME', 'musa_moda');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

class DatabaseConfig {
    // Configuración de la base de datos
    private static $config = [
        'host' => 'localhost',
        'dbname' => 'musa_moda',
        'username' => 'root',
        'password' => '', // Cambiar por tu password de MySQL
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    ];

    private static $connection = null;

    /**
     * Obtener conexión a la base de datos
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    self::$config['host'],
                    self::$config['dbname'],
                    self::$config['charset']
                );

                self::$connection = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    self::$config['options']
                );

                // Log de conexión exitosa
                error_log("✅ Conexión a base de datos establecida correctamente");     

            } catch (PDOException $e) {
                error_log("❌ Error de conexión a BD: " . $e->getMessage());
                throw new Exception("No se pudo conectar a la base de datos: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    /**
     * Obtener configuración
     */
    public static function getConfig($key = null) {
        if ($key) {
            return self::$config[$key] ?? null;
        }
        return self::$config;
    }
}

/**
 * Función helper para obtener conexión rápidamente
 */
function getDBConnection() {
    return DatabaseConfig::getConnection();
}
?>
