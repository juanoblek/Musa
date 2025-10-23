<?php
// verificar-productos-videos.php - Verificar productos con videos en la base de datos

header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$dbname = 'janithal_musa_moda';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<h1>🔍 Verificación de Productos con Videos</h1>";
    echo "<style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
        .video-product { background: #e8f5e8; border-left: 4px solid #28a745; padding: 15px; margin: 10px 0; }
        .image-product { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 10px 0; }
        .no-media { background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; }
        .success { color: #28a745; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        .danger { color: #dc3545; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
        .media-preview { max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 5px; }
    </style>";
    
    // 1. Verificar estructura de la tabla
    echo "<div class='container'>";
    echo "<h2>📋 Estructura de la tabla 'products'</h2>";
    
    $columns = $db->query("DESCRIBE products")->fetchAll();
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // 2. Contar productos por tipo de media
    echo "<div class='container'>";
    echo "<h2>📊 Estadísticas de Productos</h2>";
    
    $total = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $withMainImage = $db->query("SELECT COUNT(*) FROM products WHERE main_image IS NOT NULL AND main_image != ''")->fetchColumn();
    $withGallery = $db->query("SELECT COUNT(*) FROM products WHERE gallery IS NOT NULL AND gallery != '' AND gallery != '[]'")->fetchColumn();
    
    // Productos con videos (extensiones .mp4, .mov, .avi, .webm)
    $withVideos = $db->query("SELECT COUNT(*) FROM products WHERE main_image LIKE '%.mp4' OR main_image LIKE '%.mov' OR main_image LIKE '%.avi' OR main_image LIKE '%.webm'")->fetchColumn();
    
    echo "<table>";
    echo "<tr><th>Estadística</th><th>Cantidad</th></tr>";
    echo "<tr><td>Total de productos</td><td class='success'>$total</td></tr>";
    echo "<tr><td>Con imagen principal</td><td class='warning'>$withMainImage</td></tr>";
    echo "<tr><td>Con galería</td><td class='warning'>$withGallery</td></tr>";
    echo "<tr><td>Con videos como main_image</td><td class='success'>$withVideos</td></tr>";
    echo "</table>";
    echo "</div>";
    
    // 3. Listar productos con videos
    echo "<div class='container'>";
    echo "<h2>🎬 Productos con Videos</h2>";
    
    $videoProducts = $db->query("
        SELECT id, name, main_image, gallery, created_at 
        FROM products 
        WHERE main_image LIKE '%.mp4' OR main_image LIKE '%.mov' OR main_image LIKE '%.avi' OR main_image LIKE '%.webm'
        ORDER BY created_at DESC
    ")->fetchAll();
    
    if (count($videoProducts) > 0) {
        echo "<p class='success'>✅ Encontrados " . count($videoProducts) . " productos con videos</p>";
        
        foreach ($videoProducts as $product) {
            echo "<div class='video-product'>";
            echo "<h4>🎬 {$product['name']}</h4>";
            echo "<p><strong>ID:</strong> {$product['id']}</p>";
            echo "<p><strong>Video:</strong> {$product['main_image']}</p>";
            echo "<p><strong>Galería:</strong> " . ($product['gallery'] ? $product['gallery'] : 'Sin galería') . "</p>";
            echo "<p><strong>Creado:</strong> {$product['created_at']}</p>";
            
            // Verificar si el archivo existe
            $videoPath = __DIR__ . '/' . $product['main_image'];
            if (file_exists($videoPath)) {
                echo "<p class='success'>✅ Archivo de video existe en el servidor</p>";
                echo "<p><strong>Tamaño:</strong> " . formatBytes(filesize($videoPath)) . "</p>";
                
                // Mostrar preview del video
                echo "<video class='media-preview' controls>";
                echo "<source src='{$product['main_image']}' type='video/mp4'>";
                echo "Tu navegador no soporta videos.";
                echo "</video>";
            } else {
                echo "<p class='danger'>❌ Archivo de video NO existe en el servidor</p>";
                echo "<p><strong>Ruta buscada:</strong> $videoPath</p>";
            }
            echo "</div>";
        }
    } else {
        echo "<div class='no-media'>";
        echo "<p class='danger'>❌ No se encontraron productos con videos en main_image</p>";
        echo "</div>";
    }
    echo "</div>";
    
    // 4. Listar productos recientes
    echo "<div class='container'>";
    echo "<h2>🕒 Productos Recientes (últimos 10)</h2>";
    
    $recentProducts = $db->query("
        SELECT id, name, main_image, gallery, created_at 
        FROM products 
        ORDER BY created_at DESC 
        LIMIT 10
    ")->fetchAll();
    
    foreach ($recentProducts as $product) {
        $isVideo = preg_match('/\.(mp4|mov|avi|webm)$/i', $product['main_image']);
        $cssClass = $isVideo ? 'video-product' : ($product['main_image'] ? 'image-product' : 'no-media');
        $icon = $isVideo ? '🎬' : ($product['main_image'] ? '🖼️' : '❌');
        
        echo "<div class='$cssClass'>";
        echo "<h4>$icon {$product['name']}</h4>";
        echo "<p><strong>ID:</strong> {$product['id']}</p>";
        echo "<p><strong>Media principal:</strong> " . ($product['main_image'] ?: 'Sin media') . "</p>";
        echo "<p><strong>Tipo:</strong> " . ($isVideo ? 'VIDEO' : ($product['main_image'] ? 'IMAGEN' : 'SIN MEDIA')) . "</p>";
        echo "<p><strong>Creado:</strong> {$product['created_at']}</p>";
        
        if ($product['main_image']) {
            $mediaPath = __DIR__ . '/' . $product['main_image'];
            if (file_exists($mediaPath)) {
                echo "<p class='success'>✅ Archivo existe</p>";
                
                if ($isVideo) {
                    echo "<video class='media-preview' controls>";
                    echo "<source src='{$product['main_image']}' type='video/mp4'>";
                    echo "</video>";
                } else {
                    echo "<img class='media-preview' src='{$product['main_image']}' alt='Preview'>";
                }
            } else {
                echo "<p class='danger'>❌ Archivo NO existe</p>";
            }
        }
        echo "</div>";
    }
    echo "</div>";
    
    // 5. Verificar archivos en uploads
    echo "<div class='container'>";
    echo "<h2>📁 Archivos en /uploads</h2>";
    
    $uploadsDir = __DIR__ . '/uploads';
    if (is_dir($uploadsDir)) {
        $files = scandir($uploadsDir);
        $videoFiles = array_filter($files, function($file) {
            return preg_match('/\.(mp4|mov|avi|webm)$/i', $file);
        });
        
        echo "<p>Total de archivos en uploads: " . (count($files) - 2) . "</p>";
        echo "<p class='success'>Videos en uploads: " . count($videoFiles) . "</p>";
        
        if (count($videoFiles) > 0) {
            echo "<h4>🎬 Videos encontrados:</h4>";
            foreach ($videoFiles as $video) {
                $size = filesize($uploadsDir . '/' . $video);
                echo "<p>• $video (" . formatBytes($size) . ")</p>";
            }
        }
    } else {
        echo "<p class='danger'>❌ Directorio /uploads no existe</p>";
    }
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='container'>";
    echo "<h2 class='danger'>❌ Error de Conexión</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Verificar:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP esté ejecutándose</li>";
    echo "<li>MySQL esté activo</li>";
    echo "<li>La base de datos 'janithal_musa_moda' exista</li>";
    echo "</ul>";
    echo "</div>";
}

function formatBytes($size, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}
?>

<div style="text-align: center; margin: 30px 0; padding: 20px; background: #e3f2fd; border-radius: 10px;">
    <h3>🔗 Enlaces de prueba:</h3>
    <p>
        <a href="admin-panel.php" target="_blank" style="margin: 5px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;">📝 Admin Panel</a>
        <a href="index.php" target="_blank" style="margin: 5px; padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;">🏪 Tienda</a>
        <a href="forzar-actualizacion-videos.html" target="_blank" style="margin: 5px; padding: 10px 20px; background: #ffc107; color: black; text-decoration: none; border-radius: 5px;">🔄 Limpiar Cache</a>
    </p>
</div>