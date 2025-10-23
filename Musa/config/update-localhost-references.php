<?php
/**
 * Script para actualizar referencias de localhost a musaarion.com
 * MUSA MODA - Configuración para hosting
 */

echo "🚀 Iniciando actualización de referencias de localhost...\n\n";

// Configuración
$domain = 'musaarion.com';
$protocol = 'https';
$oldUrls = [
    'http://localhost/Musa/',
    'http://localhost/Musa',
    'http://localhost:3002',
    'http://localhost:8000',
    'localhost/Musa',
];

$newBaseUrl = $protocol . '://' . $domain . '/';

// Archivos a actualizar (excluyendo el directorio de configuración actual)
$filesToUpdate = [
    // Archivos JavaScript principales
    'js/frontend-database.js',
    'js/admin-database-system.js',
    'js/mercadoPago.js',
    'js/product-loader.js',
    'js/admin-mysql-connection.js',
    
    // Archivos PHP principales  
    'php/database.php',
    'php/crear_producto_imagen_directa.php',
    'api/productos.php',
    'api/productos-v2.php',
    'api/guardar-pedido.php',
    'api/obtener-pedidos.php',
    
    // Archivos de configuración
    'install-hosting.php',
    'install-db-simple.php',
    
    // Archivos de prueba
    'test-guardar-pedido.php',
];

function updateFileReferences($filePath, $oldUrls, $newBaseUrl) {
    if (!file_exists($filePath)) {
        echo "❌ Archivo no encontrado: $filePath\n";
        return false;
    }
    
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Reemplazar URLs
    foreach ($oldUrls as $oldUrl) {
        $content = str_replace($oldUrl, rtrim($newBaseUrl, '/'), $content);
    }
    
    // Casos especiales para JavaScript
    if (strpos($filePath, '.js') !== false) {
        // Actualizar baseURL en configuraciones
        $content = preg_replace(
            "/baseURL:\s*['\"]http:\/\/localhost[^'\"]*['\"]/", 
            "baseURL: '{$newBaseUrl}api/'", 
            $content
        );
        
        // Actualizar API_BASE
        $content = preg_replace(
            "/const\s+API_BASE\s*=\s*['\"]http:\/\/localhost[^'\"]*['\"]/", 
            "const API_BASE = '{$newBaseUrl}api'", 
            $content
        );
    }
    
    // Casos especiales para PHP
    if (strpos($filePath, '.php') !== false) {
        // Actualizar constantes de host en PHP
        $content = preg_replace(
            "/private\s+const\s+HOST\s*=\s*['\"]localhost['\"]/", 
            "private const HOST = 'localhost'", // Mantener localhost para DB
            $content
        );
    }
    
    if ($content !== $originalContent) {
        if (file_put_contents($filePath, $content)) {
            echo "✅ Actualizado: $filePath\n";
            return true;
        } else {
            echo "❌ Error escribiendo: $filePath\n";
            return false;
        }
    } else {
        echo "ℹ️  Sin cambios: $filePath\n";
        return true;
    }
}

// Actualizar archivos
$updated = 0;
$total = count($filesToUpdate);

foreach ($filesToUpdate as $file) {
    if (updateFileReferences($file, $oldUrls, $newBaseUrl)) {
        $updated++;
    }
}

echo "\n📊 Resumen:\n";
echo "- Total archivos procesados: $total\n";
echo "- Archivos actualizados: $updated\n";

// Crear archivo de configuración específico para producción
$productionConfig = '<?php
/**
 * Configuración específica para producción
 * MUSA MODA - musaarion.com
 */

// URLs de producción
define("PRODUCTION_DOMAIN", "musaarion.com");
define("PRODUCTION_URL", "https://musaarion.com");
define("PRODUCTION_API_URL", "https://musaarion.com/api");

// Configuraciones de MercadoPago para producción
define("MP_PRODUCTION_MODE", true);
define("MP_SUCCESS_URL", "https://musaarion.com/success.html");
define("MP_PENDING_URL", "https://musaarion.com/pending.html");
define("MP_FAILURE_URL", "https://musaarion.com/failure.html");

// URLs premium
define("MP_SUCCESS_PREMIUM_URL", "https://musaarion.com/success-premium.html");
define("MP_PENDING_PREMIUM_URL", "https://musaarion.com/pending-premium.html");
define("MP_FAILURE_PREMIUM_URL", "https://musaarion.com/failure-premium.html");

// Configuraciones de email
define("ADMIN_EMAIL", "admin@musaarion.com");
define("NOREPLY_EMAIL", "noreply@musaarion.com");

// Configuraciones de seguridad
define("SECURE_COOKIES", true);
define("HTTPS_ONLY", true);

// Configuraciones de cache
define("CACHE_ENABLED", true);
define("CACHE_DURATION", 3600); // 1 hora

?>';

if (file_put_contents('config/production.php', $productionConfig)) {
    echo "✅ Creado archivo de configuración de producción\n";
} else {
    echo "❌ Error creando archivo de configuración de producción\n";
}

echo "\n🎉 Proceso completado!\n";
echo "📋 Próximos pasos:\n";
echo "1. Subir archivos al hosting\n";
echo "2. Ejecutar config/update-hosting-settings.sql en la base de datos\n";
echo "3. Configurar las credenciales de base de datos en config/database.php\n";
echo "4. Configurar las credenciales de MercadoPago\n";
echo "5. Probar la funcionalidad en el dominio\n";

?>