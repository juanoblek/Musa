<?php
/**
 * Script para verificar el estado de la base de datos
 */

try {
    // Conectar a MySQL sin especificar base de datos
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    
    echo "<h2>✅ Conexión a MySQL exitosa</h2>";
    
    // Verificar si la base de datos existe
    $stmt = $pdo->query("SHOW DATABASES LIKE 'musa_moda'");
    $dbExists = $stmt->fetch();
    
    if ($dbExists) {
        echo "<h3>✅ Base de datos 'musa_moda' existe</h3>";
        
        // Conectar a la base de datos específica
        $pdo = new PDO('mysql:host=localhost;dbname=musa_moda', 'root', '');
        
        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h4>📊 Tablas en la base de datos:</h4>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Verificar datos en la tabla products
        if (in_array('products', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
            $result = $stmt->fetch();
            echo "<h4>📦 Productos en la base de datos: " . $result['total'] . "</h4>";
            
            if ($result['total'] > 0) {
                $stmt = $pdo->query("SELECT id, name, price, status FROM products LIMIT 5");
                $products = $stmt->fetchAll();
                
                echo "<h5>Primeros 5 productos:</h5>";
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Estado</th></tr>";
                foreach ($products as $product) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($product['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                    echo "<td>$" . number_format($product['price'], 0) . "</td>";
                    echo "<td>" . htmlspecialchars($product['status']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        }
        
        // Verificar categorías
        if (in_array('categories', $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
            $result = $stmt->fetch();
            echo "<h4>🏷️ Categorías en la base de datos: " . $result['total'] . "</h4>";
        }
        
    } else {
        echo "<h3>❌ Base de datos 'musa_moda' NO existe</h3>";
        echo "<p>Necesitas crear la base de datos ejecutando el script SQL.</p>";
    }
    
} catch (PDOException $e) {
    echo "<h2>❌ Error de conexión</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que MySQL esté ejecutándose en XAMPP.</p>";
}
?>
