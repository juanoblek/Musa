<?php
/**
 * Script para arreglar permisos de archivos y carpetas en cPanel
 * Soluciona problemas de "Directory not empty" y permisos
 */

echo "<h1>🔧 Arreglar Permisos de Archivos - cPanel</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; }
    .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .info { color: #17a2b8; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
    .code { background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; }
</style>";

$basePath = __DIR__;

echo "<div class='info'>📍 Trabajando en: <strong>$basePath</strong></div>";

// Función para cambiar permisos recursivamente
function fixPermissions($path, $dirPermission = 0755, $filePermission = 0644) {
    $fixed = 0;
    $errors = 0;
    
    if (is_dir($path)) {
        // Cambiar permisos del directorio
        if (chmod($path, $dirPermission)) {
            $fixed++;
        } else {
            $errors++;
        }
        
        // Obtener contenido del directorio
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item != '.' && $item != '..') {
                $itemPath = $path . DIRECTORY_SEPARATOR . $item;
                $result = fixPermissions($itemPath, $dirPermission, $filePermission);
                $fixed += $result['fixed'];
                $errors += $result['errors'];
            }
        }
    } elseif (is_file($path)) {
        // Cambiar permisos del archivo
        if (chmod($path, $filePermission)) {
            $fixed++;
        } else {
            $errors++;
        }
    }
    
    return ['fixed' => $fixed, 'errors' => $errors];
}

// Función para eliminar carpetas vacías
function removeEmptyDirs($path) {
    $removed = 0;
    
    if (!is_dir($path)) {
        return $removed;
    }
    
    $items = scandir($path);
    $hasContent = false;
    
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                $removed += removeEmptyDirs($itemPath);
                // Verificar si el directorio está vacío después de la recursión
                if (count(scandir($itemPath)) == 2) { // Solo . y ..
                    if (rmdir($itemPath)) {
                        $removed++;
                        echo "<div class='success'>✅ Directorio vacío eliminado: " . basename($itemPath) . "</div>";
                    }
                } else {
                    $hasContent = true;
                }
            } else {
                $hasContent = true;
            }
        }
    }
    
    return $removed;
}

echo "<h2>🔐 Paso 1: Arreglar Permisos</h2>";

try {
    $result = fixPermissions($basePath);
    
    echo "<div class='success'>
        ✅ <strong>Permisos arreglados:</strong><br>
        📁 Archivos/carpetas corregidos: {$result['fixed']}<br>
        ❌ Errores: {$result['errors']}
    </div>";
    
    if ($result['errors'] > 0) {
        echo "<div class='warning'>⚠️ Algunos archivos no pudieron ser corregidos. Esto es normal para archivos del sistema.</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error arreglando permisos: " . $e->getMessage() . "</div>";
}

echo "<h2>🗑️ Paso 2: Eliminar Directorios Vacíos</h2>";

try {
    $removed = removeEmptyDirs($basePath);
    echo "<div class='success'>✅ Directorios vacíos eliminados: $removed</div>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error eliminando directorios: " . $e->getMessage() . "</div>";
}

echo "<h2>🧹 Paso 3: Limpiar Archivos Temporales</h2>";

// Buscar y eliminar archivos temporales específicos
$tempPatterns = [
    '*.tmp',
    '*.temp', 
    '*.bak',
    '*.backup',
    '*.old',
    '.DS_Store',
    'Thumbs.db',
    '*.log'
];

$tempFilesRemoved = 0;

function findAndRemoveTempFiles($dir, $patterns) {
    $removed = 0;
    
    if (!is_dir($dir)) {
        return $removed;
    }
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $itemPath = $dir . DIRECTORY_SEPARATOR . $item;
            
            if (is_dir($itemPath)) {
                $removed += findAndRemoveTempFiles($itemPath, $patterns);
            } elseif (is_file($itemPath)) {
                foreach ($patterns as $pattern) {
                    if (fnmatch($pattern, $item)) {
                        if (unlink($itemPath)) {
                            $removed++;
                            echo "<div class='success'>🗑️ Archivo temporal eliminado: $item</div>";
                        }
                        break;
                    }
                }
            }
        }
    }
    
    return $removed;
}

$tempFilesRemoved = findAndRemoveTempFiles($basePath, $tempPatterns);
echo "<div class='info'>📊 Archivos temporales eliminados: $tempFilesRemoved</div>";

echo "<h2>📋 Paso 4: Información del Sistema</h2>";

// Mostrar información útil
echo "<div class='code'>";
echo "<strong>Información del servidor:</strong><br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Usuario actual: " . get_current_user() . "<br>";
echo "Directorio actual: " . getcwd() . "<br>";

if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $userInfo = posix_getpwuid(posix_geteuid());
    echo "Usuario del proceso: " . $userInfo['name'] . "<br>";
}

echo "Permisos del directorio actual: " . substr(sprintf('%o', fileperms('.')), -4) . "<br>";
echo "</div>";

echo "<h2>💡 Comandos para cPanel Terminal (si está disponible)</h2>";

echo "<div class='code'>";
echo "<strong>Si tienes acceso a terminal SSH en cPanel, ejecuta estos comandos:</strong><br><br>";

echo "# Arreglar permisos de carpetas (755)<br>";
echo "find " . $basePath . " -type d -exec chmod 755 {} \\;<br><br>";

echo "# Arreglar permisos de archivos (644)<br>";
echo "find " . $basePath . " -type f -exec chmod 644 {} \\;<br><br>";

echo "# Eliminar directorios vacíos<br>";
echo "find " . $basePath . " -type d -empty -delete<br><br>";

echo "# Eliminar archivos temporales<br>";
echo "find " . $basePath . " -name '*.tmp' -delete<br>";
echo "find " . $basePath . " -name '*.temp' -delete<br>";
echo "find " . $basePath . " -name '*.bak' -delete<br>";
echo "</div>";

echo "<h2>🎯 Comandos específicos para tu problema</h2>";

echo "<div class='code'>";
echo "<strong>Si el problema persiste, ejecuta estos comandos en orden:</strong><br><br>";

echo "# 1. Arreglar permisos de la carpeta Musa completa<br>";
echo "chmod -R 755 " . $basePath . "<br><br>";

echo "# 2. Cambiar owner (solo si tienes permisos de root)<br>";
echo "chown -R \$USER:\$USER " . $basePath . "<br><br>";

echo "# 3. Forzar eliminación de archivos problemáticos<br>";
echo "rm -rf " . $basePath . "/.git 2>/dev/null || true<br>";
echo "rm -rf " . $basePath . "/node_modules 2>/dev/null || true<br>";
echo "rm -rf " . $basePath . "/.vscode 2>/dev/null || true<br>";
echo "</div>";

echo "<div class='warning'>
⚠️ <strong>Importante:</strong><br>
- Si estás en hosting compartido, algunos comandos pueden no estar disponibles<br>
- Los archivos del sistema (.htaccess, etc.) son importantes, no los elimines<br>
- Si el problema persiste, contacta al soporte de tu hosting
</div>";

echo "<div class='success'>
✅ <strong>Proceso completado</strong><br>
Intenta ahora eliminar las carpetas desde el administrador de archivos de cPanel.
</div>";

echo "<hr>";
echo "<p><a href='admin-panel.html' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>← Volver al Panel Admin</a></p>";
?>
