<?php
/**
 * ====================================================
 * 📗 CONFIGURACIÓN DE BASE DE DATOS - LOCALHOST FORZADO
 * ====================================================
 * Este archivo está configurado para localhost únicamente
 * La configuración de hosting está comentada para respaldo
 */

require_once __DIR__ . '/config-global.php';

class DatabaseConfig {
    private static $connection = null;

    /**
     * Obtener conexión a la base de datos (LOCALHOST FORZADO)
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                // CONFIGURACIÓN DE HOSTING COMENTADA:
                /*
                $config = [
                    'host' => 'localhost',
                    'dbname' => 'janithal_musa_moda',
                    'username' => 'janithal_usuario_musaarion_db',
                    'password' => 'Chiguiro553021',
                    'charset' => 'utf8mb4'
                ];
                */
                
                // CONFIGURACIÓN LOCALHOST FORZADA:
                $config = GlobalConfig::getDatabaseConfig();
                
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    $config['host'],
                    $config['dbname'],
                    $config['charset']
                );

                self::$connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );

                error_log('Base de datos conectada (modo localhost forzado)');

            } catch(PDOException $e) {
                error_log('Error conexión BD: ' . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    /**
     * Obtener configuración (localhost)
     */
    public static function getConfig() {
        return GlobalConfig::getDatabaseConfig();
    }

    /**
     * Alias para conexión
     */
    public static function connect() {
        return self::getConnection();
    }
}

/**
 * Función helper para obtener conexión rápidamente
 */
function getDBConnection() {
    return DatabaseConfig::getConnection();
}
?>
