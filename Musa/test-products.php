<?php
// Test de productos - Verificar que los productos se cargan correctamente
require_once __DIR__ . '/config/database.php';

try {
    $db = DatabaseConfig::getConnection();
    echo "<h2>🔍 Test de Base de Datos</h2>";
    
    // Verificar conexión
    echo "<p>✅ Conexión a base de datos: OK</p>";
    
    // Verificar tablas
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>📊 Tablas disponibles:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Verificar productos
    $productsCount = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    echo "<h3>🛍️ Productos en base de datos: $productsCount</h3>";
    
    if ($productsCount > 0) {
        $products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id LIMIT 5")->fetchAll();
        echo "<h3>📋 Primeros 5 productos:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($product['id']) . "</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>" . htmlspecialchars($product['category_name'] ?? 'N/A') . "</td>";
            echo "<td>$" . number_format($product['price'], 0) . "</td>";
            echo "<td>" . $product['stock_quantity'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test del API directamente
    echo "<h3>🔗 Test del API:</h3>";
    echo "<p><a href='api/productos.php' target='_blank'>Ver API de productos</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
