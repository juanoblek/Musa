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

// Directorios
$imagesDir = __DIR__ . '/images/';
$uploadsDir = __DIR__ . '/uploads/';

// Crear directorio uploads si no existe
if (!file_exists($uploadsDir)) {
    mkdir($uploadsDir, 0777, true);
    echo "📁 Directorio uploads creado\n<br>";
}

// Función para limpiar nombres de archivos
function cleanFileName($fileName) {
    // Remover caracteres especiales y espacios
    $clean = preg_replace('/[^A-Za-z0-9\-\.]/', '_', $fileName);
    // Remover múltiples guiones bajos consecutivos
    $clean = preg_replace('/_+/', '_', $clean);
    // Remover guiones bajos al inicio y final
    $clean = trim($clean, '_');
    return strtolower($clean);
}

// Función para generar nombre único
function generateUniqueFileName($originalName, $uploadsDir) {
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
    $cleanBaseName = cleanFileName($baseName);
    
    $uniqueId = uniqid('product_' . time() . '_');
    return $uniqueId . '.' . $extension;
}

// Función recursiva para escanear directorios
function scanImagesDirectory($dir, $uploadsDir, $pdo) {
    $migratedImages = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    
    foreach ($iterator as $file) {
        if ($file->isFile() && in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $originalPath = $file->getPathname();
            $relativePath = str_replace($dir, '', $originalPath);
            $relativePath = str_replace('\\', '/', $relativePath);
            
            // Generar nombre único para el archivo
            $uniqueName = generateUniqueFileName($file->getFilename(), $uploadsDir);
            $newPath = $uploadsDir . $uniqueName;
            
            // Copiar archivo al directorio uploads
            if (copy($originalPath, $newPath)) {
                $migratedImages[] = [
                    'original_path' => 'images/' . $relativePath,
                    'new_path' => 'uploads/' . $uniqueName,
                    'file_size' => filesize($originalPath),
                    'original_name' => $file->getFilename()
                ];
                
                echo "✅ Migrado: {$relativePath} → uploads/{$uniqueName}\n<br>";
            } else {
                echo "❌ Error migrando: {$relativePath}\n<br>";
            }
        }
    }
    
    return $migratedImages;
}

echo "<h2>🔄 Iniciando migración de imágenes...</h2>";

// Migrar todas las imágenes
$migratedImages = scanImagesDirectory($imagesDir, $uploadsDir, $pdo);

echo "<h3>📊 Resumen de migración:</h3>";
echo "Total de imágenes migradas: " . count($migratedImages) . "<br><br>";

// Crear tabla de mapeo si no existe
$createMappingTable = "
CREATE TABLE IF NOT EXISTS image_migration_mapping (
    id INT AUTO_INCREMENT PRIMARY KEY,
    original_path VARCHAR(500) NOT NULL,
    new_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_original_path (original_path),
    INDEX idx_new_path (new_path)
)";

try {
    $pdo->exec($createMappingTable);
    echo "✅ Tabla de mapeo creada/verificada<br>";
} catch(PDOException $e) {
    echo "❌ Error creando tabla de mapeo: " . $e->getMessage() . "<br>";
}

// Guardar mapeo en la base de datos
$insertMapping = "INSERT INTO image_migration_mapping (original_path, new_path, original_name, file_size) VALUES (?, ?, ?, ?)";
$stmt = $pdo->prepare($insertMapping);

foreach ($migratedImages as $image) {
    try {
        $stmt->execute([
            $image['original_path'],
            $image['new_path'],
            $image['original_name'],
            $image['file_size']
        ]);
    } catch(PDOException $e) {
        echo "⚠️ Error guardando mapeo para {$image['original_path']}: " . $e->getMessage() . "<br>";
    }
}

echo "<h3>✅ Migración completada!</h3>";
echo "<p>Las imágenes han sido copiadas al directorio uploads/ con nombres únicos.</p>";
echo "<p>El mapeo de rutas originales a nuevas rutas ha sido guardado en la tabla 'image_migration_mapping'.</p>";

// Mostrar algunos ejemplos del mapeo
echo "<h4>📋 Ejemplos de mapeo:</h4>";
$exampleQuery = "SELECT * FROM image_migration_mapping ORDER BY id DESC LIMIT 10";
$examples = $pdo->query($exampleQuery)->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Ruta Original</th><th>Nueva Ruta</th><th>Nombre Original</th></tr>";
foreach ($examples as $example) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($example['original_path']) . "</td>";
    echo "<td>" . htmlspecialchars($example['new_path']) . "</td>";
    echo "<td>" . htmlspecialchars($example['original_name']) . "</td>";
    echo "</tr>";
}
echo "</table>";

?>