<?php
// ====================================================================
// ELIMINADOR EXTREMO DE PRODUCTOS - BASE DE DATOS
// ====================================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'janithal_usuario_musaarion_db';
    $password = 'Chiguiro553021';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🗑️ ELIMINACIÓN EXTREMA INICIADA...\n";
    
    // PASO 1: Eliminar TODOS los productos sin excepción
    $stmt = $pdo->prepare("DELETE FROM products");
    $stmt->execute();
    $productosEliminados = $stmt->rowCount();
    
    echo "💀 Productos eliminados: $productosEliminados\n";
    
    // PASO 2: Eliminar imágenes relacionadas
    $stmt = $pdo->prepare("DELETE FROM product_images");
    $stmt->execute();
    $imagenesEliminadas = $stmt->rowCount();
    
    echo "🖼️ Imágenes eliminadas: $imagenesEliminadas\n";
    
    // PASO 3: Eliminar variantes
    $stmt = $pdo->prepare("DELETE FROM product_variants");
    $stmt->execute();
    $variantesEliminadas = $stmt->rowCount();
    
    echo "🎨 Variantes eliminadas: $variantesEliminadas\n";
    
    // PASO 4: Eliminar categorías de productos
    $stmt = $pdo->prepare("DELETE FROM product_categories");
    $stmt->execute();
    $categoriasEliminadas = $stmt->rowCount();
    
    echo "📂 Relaciones de categorías eliminadas: $categoriasEliminadas\n";
    
    // PASO 5: Reiniciar AUTO_INCREMENT
    $pdo->exec("ALTER TABLE products AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE product_images AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE product_variants AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE product_categories AUTO_INCREMENT = 1");
    
    echo "🔄 Contadores AUTO_INCREMENT reiniciados\n";
    
    // PASO 6: Verificar que esté completamente vacío
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['total'] == 0) {
        echo "✅ CONFIRMADO: Base de datos completamente limpia\n";
        echo "📊 Total de productos: 0\n";
        echo "🎯 Sistema listo para productos nuevos\n";
    } else {
        echo "❌ ERROR: Aún hay {$resultado['total']} productos\n";
    }
    
    // Respuesta JSON
    echo json_encode([
        'success' => true,
        'message' => 'Eliminación extrema completada',
        'products_deleted' => $productosEliminados,
        'images_deleted' => $imagenesEliminadas,
        'variants_deleted' => $variantesEliminadas,
        'categories_deleted' => $categoriasEliminadas,
        'total_remaining' => $resultado['total']
    ]);
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
