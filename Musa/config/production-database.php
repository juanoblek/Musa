<?php
/**
 * Configuración específica para hosting de producción
 * Reemplaza los datos con los de tu hosting real
 */

// INSTRUCCIONES:
// 1. Reemplaza 'TU_CONTRASEÑA_AQUI' con tu contraseña real
// 2. Verifica que el nombre de la base de datos sea correcto
// 3. Sube este archivo a tu hosting

define('DB_HOST_PRODUCTION', 'localhost');
define('DB_NAME_PRODUCTION', 'janithal_musa_moda');
define('DB_USER_PRODUCTION', 'janithal_usuario_musaarion_db');
define('DB_PASS_PRODUCTION', 'Chiguiro553021'); // ✅ CONTRASEÑA REAL

class DatabaseProductionConfig {
    public static function getConnection() {
        try {
            $dsn = "mysql:host=" . DB_HOST_PRODUCTION . ";dbname=" . DB_NAME_PRODUCTION . ";charset=utf8mb4";
            
            $connection = new PDO(
                $dsn,
                DB_USER_PRODUCTION,
                DB_PASS_PRODUCTION,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            return $connection;
            
        } catch(PDOException $e) {
            error_log("Error DB: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
}
?>
