<?php
header('Content-Type: text/html; charset=utf-8');

// Conectar a la base de datos
$conn = new mysqli('localhost', 'root', '', 'janithal_musa_moda');

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Buscar el producto Chaqueta See
$result = $conn->query("SELECT * FROM productos WHERE name LIKE '%Chaqueta%' OR name LIKE '%chaqueta%'");

echo "<h1>🔍 Debug - Producto Chaqueta See</h1>";

if ($result->num_rows > 0) {
    echo "<h2>📋 PRODUCTOS ENCONTRADOS:</h2>";
    while($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 8px;'>";
        echo "<h3>ID: " . $row['id'] . " - " . htmlspecialchars($row['name']) . "</h3>";
        echo "<p><strong>Main Image:</strong> " . htmlspecialchars($row['main_image']) . "</p>";
        echo "<p><strong>Image:</strong> " . htmlspecialchars($row['image']) . "</p>";
        
        // Verificar si es video
        $mainImage = $row['main_image'];
        $isVideo = preg_match('/\.(mp4|mov|avi|webm)$/i', $mainImage);
        
        echo "<p><strong>¿Es video?:</strong> " . ($isVideo ? "✅ SÍ" : "❌ NO") . "</p>";
        
        // Verificar si el archivo existe
        $filePath = $mainImage;
        if (file_exists($filePath)) {
            echo "<p><strong>Archivo existe:</strong> ✅ SÍ</p>";
            echo "<p><strong>Tamaño:</strong> " . filesize($filePath) . " bytes</p>";
        } else {
            echo "<p><strong>Archivo existe:</strong> ❌ NO - Ruta: " . $filePath . "</p>";
        }
        
        // Mostrar vista previa
        echo "<h4>🎬 Vista previa:</h4>";
        if ($isVideo) {
            echo "<video src='" . htmlspecialchars($mainImage) . "' controls style='max-width: 300px; max-height: 200px;'></video>";
        } else {
            echo "<img src='" . htmlspecialchars($mainImage) . "' alt='Vista previa' style='max-width: 300px; max-height: 200px;'>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p>❌ No se encontraron productos con 'Chaqueta' en el nombre</p>";
}

// Buscar todos los productos con videos
echo "<h2>🎬 TODOS LOS PRODUCTOS CON VIDEOS:</h2>";
$videoResult = $conn->query("SELECT id, name, main_image FROM productos WHERE main_image LIKE '%.mp4' OR main_image LIKE '%.mov' OR main_image LIKE '%.avi' OR main_image LIKE '%.webm'");

if ($videoResult->num_rows > 0) {
    while($row = $videoResult->fetch_assoc()) {
        echo "<div style='border: 1px solid #28a745; padding: 10px; margin: 5px 0; border-radius: 4px;'>";
        echo "<strong>" . htmlspecialchars($row['name']) . "</strong> - " . htmlspecialchars($row['main_image']);
        echo "</div>";
    }
} else {
    echo "<p>❌ No se encontraron productos con videos</p>";
}

$conn->close();
?>

<script>
// Test del detector inteligente
console.log('🧪 Testing detector inteligente...');

// Verificar si el detector está cargado
if (typeof generarMediaHTMLSincrono !== 'undefined') {
    console.log('✅ Detector inteligente cargado correctamente');
    
    // Test con URL de video
    const testVideo = 'uploads/video_test.mp4';
    const videoHTML = generarMediaHTMLSincrono(testVideo, 'Test Video', 'test-class', 'max-width: 200px;');
    console.log('🎬 HTML generado para video:', videoHTML);
    
} else {
    console.log('❌ Detector inteligente NO cargado');
}
</script>

<script src="js/detector-inteligente-media.js"></script>