<?php
// Test para asignar imagen a un producto existente
require_once __DIR__ . '/config/database.php';

try {
    $db = DatabaseConfig::getConnection();
    
    // Actualizar el primer producto con una imagen de prueba
    $stmt = $db->prepare("UPDATE products SET main_image = ? WHERE id = ?");
    $stmt->execute(['uploads/product_68bb5f1ee8e73_1757110046.jpeg', 'chaqueta-cuero-001']);
    
    echo "✅ Producto actualizado con imagen<br>";
    
    // Verificar el resultado
    $stmt = $db->prepare("SELECT id, name, main_image FROM products WHERE id = ?");
    $stmt->execute(['chaqueta-cuero-001']);
    $product = $stmt->fetch();
    
    echo "<h3>📋 Producto actualizado:</h3>";
    echo "<p><strong>ID:</strong> " . $product['id'] . "</p>";
    echo "<p><strong>Nombre:</strong> " . $product['name'] . "</p>";
    echo "<p><strong>Imagen:</strong> " . $product['main_image'] . "</p>";
    
    if ($product['main_image']) {
        echo "<p><img src='" . $product['main_image'] . "' style='max-width: 200px; height: auto;'></p>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
