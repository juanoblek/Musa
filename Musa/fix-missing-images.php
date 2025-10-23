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

// Lista de imágenes que están dando error 404 (extraídas del log)
$missingImages = [
    'product_68cde06f66167_1758322799.png',
    'product_68cdda08b7e15_1758321160.png',
    'product_68cdd22301d39_1758319139.jpeg',
    'product_68cdcbafca5c6_1758317487.jpeg',
    'product_68cdca160e3ee_1758317078.jpeg',
    'product_68cd84b47c092_1758299316.png',
    'product_68cd833beff2e_1758298939.png',
    'product_68cd82c6d6505_1758298822.png',
    'product_68cd822f9a07a_1758298671.png',
    'product_68cd80e0a709f_1758298336.jpeg',
    'product_68cca7c346234_1758242755.jpeg',
    'product_68cca61c711bd_1758242332.jpeg',
    'product_68cc9fcd3f4dc_1758240717.jpeg',
    'product_68cc99d3463b0_1758239187.jpeg',
    'product_68cc8e364fd75_1758236214.jpeg',
    'product_68cc8d697941a_1758236009.jpeg',
    'product_68cc8c54958e7_1758235732.jpeg',
    'product_68cc873def95a_1758234429.jpeg',
    'product_68cc7ab5a98a5_1758231221.jpeg'
];

echo "<h2>🔍 Analizando productos y creando imágenes faltantes...</h2>";

// Obtener todos los productos de la base de datos
$productQuery = "SELECT * FROM productos ORDER BY id DESC";
$products = $pdo->query($productQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>📋 Productos en la base de datos: " . count($products) . "</h3>";

// Directorios
$imagesDir = __DIR__ . '/images/';
$uploadsDir = __DIR__ . '/uploads/';

// Imágenes disponibles en el directorio images
$availableImages = [];

// Función para buscar imágenes recursivamente
function findImages($dir) {
    $images = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $relativePath = str_replace($dir, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);
            $images[] = [
                'path' => $file->getPathname(),
                'relative_path' => $relativePath,
                'name' => $file->getFilename(),
                'folder' => dirname($relativePath)
            ];
        }
    }
    
    return $images;
}

$availableImages = findImages($imagesDir);
echo "<p>📸 Imágenes disponibles en /images/: " . count($availableImages) . "</p>";

// Función para generar imagen única
function generateUniqueProductImage($originalName, $uploadsDir) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $timestamp = time();
    $uniqueId = uniqid('product_68' . substr($timestamp, -6));
    return $uniqueId . '_' . $timestamp . '.' . $extension;
}

// Función para encontrar la mejor imagen para un producto
function findBestImageForProduct($product, $availableImages) {
    $productName = strtolower($product['nombre']);
    $bestMatches = [];
    
    foreach ($availableImages as $image) {
        $imageName = strtolower($image['name']);
        $folderName = strtolower($image['folder']);
        
        // Buscar coincidencias en el nombre del producto
        $score = 0;
        
        // Palabras clave del producto
        $productWords = preg_split('/[\s\-_]+/', $productName);
        
        foreach ($productWords as $word) {
            if (strlen($word) > 2) {
                if (strpos($imageName, $word) !== false) $score += 3;
                if (strpos($folderName, $word) !== false) $score += 2;
            }
        }
        
        if ($score > 0) {
            $bestMatches[] = [
                'image' => $image,
                'score' => $score
            ];
        }
    }
    
    // Ordenar por puntuación
    usort($bestMatches, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    return !empty($bestMatches) ? $bestMatches[0]['image'] : null;
}

// Procesar cada producto
$createdImages = 0;
$updatedProducts = 0;

foreach ($products as $product) {
    $currentImage = $product['imagen'];
    $currentImagePath = $uploadsDir . $currentImage;
    
    // Si la imagen no existe o es placeholder
    if (!file_exists($currentImagePath) || strpos($currentImage, 'placeholder') !== false) {
        
        // Buscar la mejor imagen disponible
        $bestImage = findBestImageForProduct($product, $availableImages);
        
        if ($bestImage) {
            // Generar nombre único
            $newImageName = generateUniqueProductImage($bestImage['name'], $uploadsDir);
            $newImagePath = $uploadsDir . $newImageName;
            
            // Copiar imagen
            if (copy($bestImage['path'], $newImagePath)) {
                // Actualizar base de datos
                $updateQuery = "UPDATE productos SET imagen = ? WHERE id = ?";
                $stmt = $pdo->prepare($updateQuery);
                
                if ($stmt->execute([$newImageName, $product['id']])) {
                    echo "✅ Producto #{$product['id']} '{$product['nombre']}': {$bestImage['relative_path']} → {$newImageName}<br>";
                    $createdImages++;
                    $updatedProducts++;
                } else {
                    echo "❌ Error actualizando producto #{$product['id']}<br>";
                }
            } else {
                echo "❌ Error copiando imagen para producto #{$product['id']}<br>";
            }
        } else {
            echo "⚠️ No se encontró imagen adecuada para producto #{$product['id']} '{$product['nombre']}'<br>";
        }
    } else {
        echo "ℹ️ Producto #{$product['id']} ya tiene imagen válida: {$currentImage}<br>";
    }
}

echo "<h3>📊 Resumen:</h3>";
echo "<p>✅ Imágenes creadas: {$createdImages}</p>";
echo "<p>✅ Productos actualizados: {$updatedProducts}</p>";

// Verificar imágenes en uploads después del proceso
$finalImageCount = count(glob($uploadsDir . '*'));
echo "<p>📁 Total de imágenes en uploads/: {$finalImageCount}</p>";

echo "<h4>🔄 Refrescar la página para ver los cambios</h4>";
echo "<p><a href='index.html' target='_blank'>🚀 Ir a la página principal</a></p>";

?>