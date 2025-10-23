<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "musa";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión a la base de datos exitosa\n<br>";
} catch(PDOException $e) {
    die("❌ Error de conexión: " . $e->getMessage());
}

$uploadsDir = __DIR__ . '/uploads/';
$imagesDir = __DIR__ . '/images/';

// Función para copiar una imagen con nombre único
function copyImageWithUniqueName($sourcePath, $uploadsDir) {
    if (!file_exists($sourcePath)) {
        return false;
    }
    
    $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
    $timestamp = time();
    $uniqueId = uniqid('product_68' . substr($timestamp, -6));
    $newName = $uniqueId . '_' . $timestamp . '.' . $extension;
    $newPath = $uploadsDir . $newName;
    
    if (copy($sourcePath, $newPath)) {
        return $newName;
    }
    
    return false;
}

// Obtener todas las imágenes disponibles en /images/
function getAllAvailableImages($imagesDir) {
    $images = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($imagesDir));
    
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $images[] = $file->getPathname();
        }
    }
    
    return $images;
}

echo "<h2>🔧 Solucionando imágenes faltantes...</h2>";

// Obtener todos los productos
$products = $pdo->query("SELECT * FROM productos")->fetchAll(PDO::FETCH_ASSOC);
echo "<p>📦 Productos encontrados: " . count($products) . "</p>";

// Obtener todas las imágenes disponibles
$availableImages = getAllAvailableImages($imagesDir);
echo "<p>🖼️ Imágenes disponibles: " . count($availableImages) . "</p>";

// Mezclar array para distribución aleatoria
shuffle($availableImages);

$processedCount = 0;
$imageIndex = 0;

foreach ($products as $product) {
    $currentImage = $product['imagen'];
    $currentImagePath = $uploadsDir . $currentImage;
    
    // Si la imagen no existe o es placeholder.svg
    if (!file_exists($currentImagePath) || strpos($currentImage, 'placeholder.svg') !== false || empty($currentImage)) {
        
        // Usar la siguiente imagen disponible (distribución circular)
        if (!empty($availableImages)) {
            $selectedImage = $availableImages[$imageIndex % count($availableImages)];
            $newImageName = copyImageWithUniqueName($selectedImage, $uploadsDir);
            
            if ($newImageName) {
                // Actualizar producto en la base de datos
                $updateQuery = "UPDATE productos SET imagen = ? WHERE id = ?";
                $stmt = $pdo->prepare($updateQuery);
                
                if ($stmt->execute([$newImageName, $product['id']])) {
                    $relativePath = str_replace($imagesDir, '', $selectedImage);
                    echo "✅ Producto #{$product['id']} '{$product['nombre']}': → {$newImageName} (desde: {$relativePath})<br>";
                    $processedCount++;
                } else {
                    echo "❌ Error actualizando producto #{$product['id']}<br>";
                }
            } else {
                echo "❌ Error copiando imagen para producto #{$product['id']}<br>";
            }
            
            $imageIndex++;
        } else {
            echo "⚠️ No hay imágenes disponibles para copiar<br>";
            break;
        }
    } else {
        echo "ℹ️ Producto #{$product['id']} ya tiene imagen válida: {$currentImage}<br>";
    }
}

echo "<h3>📊 Proceso completado:</h3>";
echo "<p>✅ Productos procesados: {$processedCount}</p>";

// Verificar total de imágenes en uploads
$totalUploadsImages = count(glob($uploadsDir . '*'));
echo "<p>📁 Total de imágenes en uploads/: {$totalUploadsImages}</p>";

echo "<h4>🎯 Resultado:</h4>";
echo "<p>Todos los productos ahora deberían tener imágenes válidas asignadas.</p>";
echo "<p><a href='index.html' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Probar página principal</a></p>";

?>