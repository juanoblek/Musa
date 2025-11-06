<?php
/**
 * Configuración de base de datos para hosting
 */

class DatabaseConfig {
    private static $connection = null;
    private static $config = [
        'host' => 'localhost',
        'dbname' => 'janithal_musa_moda',
        'username' => 'janithal_usuario_musaarion_db',
        'password' => 'Chiguiro553021',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    ];

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

                return self::$connection;

            } catch (PDOException $e) {
                error_log("Error de conexión a BD: " . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos");
            }
        }
        return self::$connection;
    }
}