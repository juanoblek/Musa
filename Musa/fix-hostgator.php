<?php
/**
 * Script específico para resolver problemas de Hostgator
 * Elimina carpetas .trash y archivos problemáticos
 */

echo "<h1>🛠️ Solucionador de Problemas Hostgator</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; border: 1px solid #e9ecef; }
    .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
</style>";

$basePath = dirname(__FILE__);
$fixed = 0;
$errors = 0;
$messages = [];

function addMessage($type, $message) {
    global $messages;
    $messages[] = ['type' => $type, 'message' => $message];
}

function displayMessages() {
    global $messages;
    foreach ($messages as $msg) {
        echo "<div class='{$msg['type']}'>{$msg['message']}</div>";
    }
}

function forceDelete($path) {
    if (!file_exists($path)) {
        return true;
    }
    
    if (is_file($path)) {
        return @unlink($path);
    }
    
    if (is_dir($path)) {
        $files = @scandir($path);
        if ($files !== false) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $path . DIRECTORY_SEPARATOR . $file;
                    forceDelete($filePath);
                }
            }
        }
        return @rmdir($path);
    }
    
    return false;
}

echo "<div class='info'>🏠 <strong>Servidor:</strong> " . $_SERVER['SERVER_NAME'] . "<br>";
echo "📁 <strong>Directorio:</strong> " . $basePath . "</div>";

echo "<h2>🗑️ Paso 1: Eliminar Carpetas Problemáticas</h2>";

// Lista de carpetas problemáticas en Hostgator
$problematicFolders = [
    '.trash',
    '.git', 
    '.vscode',
    'node_modules',
    'backend/node_modules',
    '.DS_Store',
    '.htaccess.bak'
];

foreach ($problematicFolders as $folder) {
    $folderPath = $basePath . DIRECTORY_SEPARATOR . $folder;
    
    if (file_exists($folderPath)) {
        if (forceDelete($folderPath)) {
            addMessage('success', "✅ Eliminado: $folder");
            $fixed++;
        } else {
            addMessage('error', "❌ No se pudo eliminar: $folder");
            $errors++;
        }
    }
}

echo "<h2>🔐 Paso 2: Arreglar Permisos (Hostgator)</h2>";

// Función recursiva para arreglar permisos
function fixHostgatorPermissions($path) {
    global $fixed, $errors;
    
    if (is_dir($path)) {
        // Permisos para directorios: 755
        if (@chmod($path, 0755)) {
            $fixed++;
        } else {
            $errors++;
        }
        
        $files = @scandir($path);
        if ($files !== false) {
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $path . DIRECTORY_SEPARATOR . $file;
                    fixHostgatorPermissions($filePath);
                }
            }
        }
    } elseif (is_file($path)) {
        // Permisos para archivos: 644
        if (@chmod($path, 0644)) {
            $fixed++;
        } else {
            $errors++;
        }
    }
}

fixHostgatorPermissions($basePath);
addMessage('success', "🔐 Permisos arreglados: $fixed archivos/carpetas");

if ($errors > 0) {
    addMessage('warning', "⚠️ Errores de permisos: $errors (normal en algunos archivos del sistema)");
}

echo "<h2>🧹 Paso 3: Limpiar Archivos Temporales</h2>";

// Patrones de archivos temporales
$tempPatterns = [
    '*.tmp', '*.temp', '*.bak', '*.backup', '*.old',
    '*.log', '.DS_Store', 'Thumbs.db', '*.swp'
];

function cleanTempFiles($dir, $patterns) {
    $cleaned = 0;
    
    if (!is_dir($dir)) return $cleaned;
    
    $files = @scandir($dir);
    if ($files === false) return $cleaned;
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $filePath = $dir . DIRECTORY_SEPARATOR . $file;
        
        if (is_dir($filePath)) {
            $cleaned += cleanTempFiles($filePath, $patterns);
        } elseif (is_file($filePath)) {
            foreach ($patterns as $pattern) {
                if (fnmatch($pattern, $file)) {
                    if (@unlink($filePath)) {
                        $cleaned++;
                        addMessage('success', "🗑️ Archivo temporal eliminado: $file");
                    }
                    break;
                }
            }
        }
    }
    
    return $cleaned;
}

$tempCleaned = cleanTempFiles($basePath, $tempPatterns);
addMessage('info', "📊 Archivos temporales eliminados: $tempCleaned");

echo "<h2>📁 Paso 4: Eliminar Directorios Vacíos</h2>";

function removeEmptyDirectories($path) {
    $removed = 0;
    
    if (!is_dir($path)) return $removed;
    
    $files = @scandir($path);
    if ($files === false) return $removed;
    
    $isEmpty = true;
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($filePath)) {
                $removed += removeEmptyDirectories($filePath);
                // Verificar si ahora está vacío
                $subFiles = @scandir($filePath);
                if ($subFiles !== false && count($subFiles) <= 2) {
                    if (@rmdir($filePath)) {
                        $removed++;
                        addMessage('success', "📁 Directorio vacío eliminado: " . basename($filePath));
                    }
                } else {
                    $isEmpty = false;
                }
            } else {
                $isEmpty = false;
            }
        }
    }
    
    return $removed;
}

$emptyRemoved = removeEmptyDirectories($basePath);

echo "<h2>📊 Paso 5: Estadísticas Finales</h2>";

// Calcular estadísticas
function getDirectoryStats($path) {
    $fileCount = 0;
    $dirCount = 0;
    $totalSize = 0;
    
    if (!is_dir($path)) return ['files' => 0, 'dirs' => 0, 'size' => 0];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $fileCount++;
            $totalSize += $file->getSize();
        } elseif ($file->isDir()) {
            $dirCount++;
        }
    }
    
    return ['files' => $fileCount, 'dirs' => $dirCount, 'size' => $totalSize];
}

$stats = getDirectoryStats($basePath);

addMessage('info', "📈 <strong>Estadísticas Finales:</strong><br>" .
    "📄 Archivos totales: {$stats['files']}<br>" .
    "📁 Directorios totales: {$stats['dirs']}<br>" .
    "💾 Tamaño total: " . round($stats['size'] / 1024 / 1024, 2) . " MB");

echo "<h2>💡 Comandos SSH para Hostgator</h2>";

echo "<div class='code'>";
echo "<strong>Si tienes acceso SSH, ejecuta estos comandos:</strong><br><br>";
echo "# Conectar por SSH<br>";
echo "ssh tu_usuario@shared16.hostgator.co<br><br>";
echo "# Ir al directorio<br>";
echo "cd public_html/Musa<br><br>";
echo "# Limpiar forzadamente<br>";
echo "rm -rf .trash .git .vscode node_modules<br><br>";
echo "# Arreglar permisos<br>";
echo "find . -type d -exec chmod 755 {} \\;<br>";
echo "find . -type f -exec chmod 644 {} \\;<br><br>";
echo "# Eliminar directorios vacíos<br>";
echo "find . -type d -empty -delete<br>";
echo "</div>";

// Mostrar todos los mensajes
displayMessages();

echo "<div class='success'>";
echo "<h3>✅ Proceso Completado</h3>";
echo "<p>Intenta ahora eliminar archivos desde File Manager de Hostgator.</p>";
echo "<p>Si el problema persiste, usa los comandos SSH o contacta al soporte de Hostgator.</p>";
echo "</div>";

echo "<div style='text-align: center; margin: 30px 0;'>";
echo "<a href='admin-panel.html' class='btn'>🏠 Panel Admin</a>";
echo "<a href='index.html' class='btn'>🛍️ Tienda</a>";
echo "<a href='.' class='btn'>📁 Explorar Archivos</a>";
echo "</div>";
?>
