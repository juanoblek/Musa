<?php
// Verificar que janithal_musa_moda existe en localhost
try {
    $pdo = new PDO("mysql:host=localhost", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // Verificar si la BD existe
    $stmt = $pdo->query("SHOW DATABASES LIKE 'janithal_musa_moda'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "✅ La base de datos 'janithal_musa_moda' EXISTE en localhost\n";
        
        // Conectar a la BD específica
        $pdo = new PDO("mysql:host=localhost;dbname=janithal_musa_moda", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "📋 Tablas disponibles (" . count($tables) . "): " . implode(', ', $tables) . "\n";
        
        // Verificar productos
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
        $result = $stmt->fetch();
        echo "🛍️ Total de productos: " . $result['total'] . "\n";
        
    } else {
        echo "❌ La base de datos 'janithal_musa_moda' NO EXISTE en localhost\n";
        echo "💡 Necesitas importar el archivo SQL primero\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>