<?php
// Script para verificar estructura de tablas
try {
    require_once '../config/database.php';
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== ESTRUCTURA DE TABLA PEDIDOS ===\n";
    $result = $pdo->query("SHOW CREATE TABLE pedidos");
    $row = $result->fetch();
    echo $row[1] . "\n\n";
    
    echo "=== ESTRUCTURA DE TABLA ENVIOS ===\n";
    $result = $pdo->query("SHOW CREATE TABLE envios");
    $row = $result->fetch();
    echo $row[1] . "\n\n";
    
    echo "=== FOREIGN KEYS ===\n";
    $result = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                          FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                          WHERE REFERENCED_TABLE_SCHEMA = '" . DB_NAME . "' 
                          AND TABLE_NAME IN ('pedidos', 'envios')");
    
    while ($row = $result->fetch()) {
        echo "Tabla: {$row['TABLE_NAME']}, Columna: {$row['COLUMN_NAME']}, Referencias: {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>