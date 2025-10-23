<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test - Sistema de Imágenes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .info { color: blue; }
        .test-item { margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Test del Sistema de Imágenes</h1>
    
    <div class="test-item">
        <h3>📂 Directorio de Subidas</h3>
        <p class="info">Verificando directorio uploads/...</p>
        <?php
        $uploadDir = __DIR__ . '/uploads/';
        if (is_dir($uploadDir) && is_writable($uploadDir)) {
            echo '<p class="success">✅ Directorio uploads/ existe y es escribible</p>';
            
            $files = glob($uploadDir . '*');
            echo '<p class="info">📁 Archivos encontrados: ' . count($files) . '</p>';
            
            foreach ($files as $file) {
                $fileName = basename($file);
                echo '<div style="margin: 5px; padding: 5px; background: #f5f5f5;">';
                echo '<strong>' . $fileName . '</strong><br>';
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    echo '<img src="uploads/' . $fileName . '" style="max-width: 100px; height: auto; margin: 5px 0;">';
                }
                echo '</div>';
            }
        } else {
            echo '<p style="color: red;">❌ Problema con directorio uploads/</p>';
        }
        ?>
    </div>
    
    <div class="test-item">
        <h3>📊 Productos con Imágenes</h3>
        <?php
        require_once __DIR__ . '/config/database.php';
        try {
            $db = DatabaseConfig::getConnection();
            $stmt = $db->query("SELECT id, name, main_image FROM products WHERE main_image IS NOT NULL AND main_image != ''");
            $products = $stmt->fetchAll();
            
            echo '<p class="info">🛍️ Productos con imagen: ' . count($products) . '</p>';
            
            foreach ($products as $product) {
                echo '<div style="margin: 10px; padding: 10px; background: #f8f9fa; border-radius: 5px;">';
                echo '<strong>' . htmlspecialchars($product['name']) . '</strong><br>';
                echo '<small>ID: ' . htmlspecialchars($product['id']) . '</small><br>';
                echo '<small>Imagen: ' . htmlspecialchars($product['main_image']) . '</small><br>';
                if ($product['main_image']) {
                    echo '<img src="' . htmlspecialchars($product['main_image']) . '" style="max-width: 150px; height: auto; margin: 5px 0; border: 1px solid #ddd;">';
                }
                echo '</div>';
            }
        } catch (Exception $e) {
            echo '<p style="color: red;">❌ Error: ' . $e->getMessage() . '</p>';
        }
        ?>
    </div>
    
    <div class="test-item">
        <h3>🔗 Enlaces de Test</h3>
        <p><a href="admin-panel.php" target="_blank">🏢 Panel Administrativo</a></p>
        <p><a href="api/productos-v2.php" target="_blank">📡 API de Productos</a></p>
        <p><a href="api/upload-image.php" target="_blank">📤 API de Subida de Imágenes</a> (solo POST)</p>
    </div>
    
    <div class="test-item">
        <h3>💡 Instrucciones</h3>
        <ol>
            <li>Ve al Panel Administrativo</li>
            <li>Haz clic en "➕ Nuevo Producto"</li>
            <li>Llena los datos del producto</li>
            <li>Selecciona una imagen en "Imagen Principal"</li>
            <li>Guarda el producto</li>
            <li>Verifica que aparezca con imagen en la lista</li>
        </ol>
    </div>
</body>
</html>
