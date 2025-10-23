<?php
/**
 * ====================================================
 * 📗 CONFIGURACIÓN DE BASE DE DATOS - HOSTING PRODUCTION
 * ====================================================
 */

// Detectar si estamos en hosting o desarrollo local
$isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8080']);

if ($isLocalhost) {
    // Configuración para desarrollo local (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'musa_moda');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Configuración para hosting - CAMBIAR ESTOS VALORES
    define('DB_HOST', 'localhost'); // O la IP que te dé tu hosting
    define('DB_NAME', 'tu_usuario_db_name'); // Nombre de tu base de datos en hosting
    define('DB_USER', 'tu_usuario_db');      // Usuario de base de datos en hosting
    define('DB_PASS', 'tu_password_db');     // Contraseña de base de datos en hosting
}

define('DB_CHARSET', 'utf8mb4');

class DatabaseConfig {
    // Configuración de la base de datos
    private static $config = null;
    
    public static function getConfig() {
        if (self::$config === null) {
            $isLocalhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', 'localhost:8080']);
            
            if ($isLocalhost) {
                // Desarrollo local
                self::$config = [
                    'host' => 'localhost',
                    'dbname' => 'musa_moda',
                    'username' => 'root',
                    'password' => '',
                    'charset' => 'utf8mb4'
                ];
            } else {
                // Hosting production - CAMBIAR ESTOS VALORES
                self::$config = [
                    'host' => 'localhost',
                    'dbname' => 'tu_usuario_db_name', // Cambiar
                    'username' => 'tu_usuario_db',    // Cambiar
                    'password' => 'tu_password_db',   // Cambiar
                    'charset' => 'utf8mb4'
                ];
            }
        }
        
        return self::$config;
    }

    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $config = self::getConfig();
                
                $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
                
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
                
                // Log de conexión exitosa
                error_log("✅ Conexión a base de datos establecida - Host: {$config['host']}, DB: {$config['dbname']}");
                
            } catch(PDOException $e) {
                // Log detallado del error
                $config = self::getConfig();
                error_log("❌ Error de conexión a base de datos:");
                error_log("   Host: {$config['host']}");
                error_log("   Database: {$config['dbname']}");
                error_log("   Username: {$config['username']}");
                error_log("   Error: " . $e->getMessage());
                
                // En hosting, mostrar error más amigable
                if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
                    throw new PDOException("Error de conexión a la base de datos. Verificar configuración en hosting.");
                } else {
                    throw $e;
                }
            }
        }
        return self::$connection;
    }
    
    // Método para verificar la conexión
    public static function testConnection() {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query('SELECT 1');
            return ['success' => true, 'message' => 'Conexión exitosa'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Función de compatibilidad
function getDatabaseConnection() {
    return DatabaseConfig::getConnection();
}
?>
