<?php
/**
 * Script para exportar datos de XAMPP local a SQL
 * Ejecutar desde XAMPP para generar archivo de importación
 */

require_once 'config/database.php';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="musa_backup_' . date('Y-m-d_H-i-s') . '.sql"');

echo "-- M & A MODA - Backup de Base de Datos\n";
echo "-- Generado: " . date('Y-m-d H:i:s') . "\n";
echo "-- Archivo: musa_backup_" . date('Y-m-d_H-i-s') . ".sql\n\n";

echo "SET FOREIGN_KEY_CHECKS = 0;\n";
echo "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
echo "SET time_zone = '+00:00';\n\n";

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tablas a exportar
    $tables = ['categories', 'products', 'admin_users', 'system_settings'];
    
    foreach ($tables as $table) {
        try {
            // Verificar si la tabla existe
            $stmt = $db->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                echo "-- Tabla '$table' no encontrada, saltando...\n";
                continue;
            }
            
            echo "-- Datos de la tabla '$table'\n";
            
            // Obtener estructura de la tabla
            $stmt = $db->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            echo $createTable['Create Table'] . ";\n\n";
            
            // Obtener datos
            $stmt = $db->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($rows) > 0) {
                echo "INSERT INTO `$table` (";
                
                // Nombres de columnas
                $columns = array_keys($rows[0]);
                echo "`" . implode("`, `", $columns) . "`";
                echo ") VALUES\n";
                
                foreach ($rows as $index => $row) {
                    echo "(";
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . addslashes($value) . "'";
                        }
                    }
                    echo implode(", ", $values);
                    echo ")";
                    
                    if ($index < count($rows) - 1) {
                        echo ",";
                    }
                    echo "\n";
                }
                echo ";\n\n";
            } else {
                echo "-- Sin datos en la tabla '$table'\n\n";
            }
            
        } catch (Exception $e) {
            echo "-- Error exportando tabla '$table': " . $e->getMessage() . "\n";
        }
    }
    
    echo "SET FOREIGN_KEY_CHECKS = 1;\n";
    echo "\n-- Fin del backup\n";
    
} catch (Exception $e) {
    echo "-- ERROR: " . $e->getMessage() . "\n";
}
?>
