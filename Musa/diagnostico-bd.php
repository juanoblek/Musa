<?php
/**
 * Diagnóstico rápido de base de datos
 * Verificar qué bases de datos existen y cuáles tablas contienen
 */

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔍 Diagnóstico de Base de Datos - MUSA MODA</h1>";

// Configuraciones posibles

$configs = [
    ['host' => 'localhost', 'dbname' => 'janithal_musa_moda', 'user' => 'janithal_usuario_musaarion_db', 'pass' => 'Chiguiro553021'],
];

foreach ($configs as $index => $config) {
    echo "<h2>🗄️ Probando configuración " . ($index + 1) . ": {$config['dbname']}</h2>";
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
            $config['user'],
            $config['pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p>✅ <strong>Conexión exitosa</strong></p>";
        
        // Listar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p>⚠️ La base de datos está vacía</p>";
        } else {
            echo "<p>📋 <strong>Tablas encontradas:</strong></p><ul>";
            foreach ($tables as $table) {
                // Contar registros
                try {
                    $count_stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                    $count = $count_stmt->fetchColumn();
                    echo "<li><strong>$table</strong> - $count registros</li>";
                } catch (Exception $e) {
                    echo "<li><strong>$table</strong> - Error contando</li>";
                }
            }
            echo "</ul>";
            
            // Verificar tablas importantes
            $important_tables = ['products', 'categories', 'pedidos', 'envios'];
            $found_tables = array_intersect($important_tables, $tables);
            
            if (count($found_tables) === count($important_tables)) {
                echo "<p>🎉 <strong>TODAS LAS TABLAS IMPORTANTES ENCONTRADAS</strong></p>";
                echo "<p>✅ Esta configuración es válida para el proyecto</p>";
                
                // Mostrar algunos productos de ejemplo
                try {
                    $products_stmt = $pdo->query("SELECT id, name, price FROM products LIMIT 3");
                    $products = $products_stmt->fetchAll();
                    
                    if (!empty($products)) {
                        echo "<p><strong>Productos de ejemplo:</strong></p><ul>";
                        foreach ($products as $product) {
                            echo "<li>{$product['name']} - \${$product['price']}</li>";
                        }
                        echo "</ul>";
                    }
                } catch (Exception $e) {
                    echo "<p>⚠️ Error leyendo productos: " . $e->getMessage() . "</p>";
                }
                
            } else {
                $missing = array_diff($important_tables, $found_tables);
                echo "<p>⚠️ <strong>Faltan tablas:</strong> " . implode(', ', $missing) . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}

// Mostrar todas las bases de datos disponibles
echo "<h2>📊 Todas las Bases de Datos Disponibles</h2>";
try {
    $pdo = new PDO("mysql:host=localhost", "janithal_usuario_musaarion_db", "Chiguiro553021", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<ul>";
    foreach ($databases as $db) {
        if (!in_array($db, ['information_schema', 'mysql', 'performance_schema', 'sys'])) {
            echo "<li><strong>$db</strong></li>";
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>❌ Error listando bases de datos: " . $e->getMessage() . "</p>";
}

echo "<br><p><strong>🕐 Ejecutado:</strong> " . date('Y-m-d H:i:s') . "</p>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    p { margin: 10px 0; }
    ul { margin: 10px 0 20px 20px; }
    hr { margin: 30px 0; border: 1px solid #ddd; }
</style>