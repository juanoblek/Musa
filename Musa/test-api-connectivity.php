<?php
/**
 * Archivo de prueba para verificar la API
 * Acceder desde: http://localhost/Musa/test-api-connectivity.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

echo "<h1>🔧 Test de Conectividad API - MUSA MODA</h1>";

// Test 1: Verificar configuración PHP
echo "<h2>📋 Información PHP</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server:</strong> " . ($_SERVER['SERVER_NAME'] ?? 'Unknown') . "</p>";
echo "<p><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</p>";

// Test 2: Verificar archivos de configuración
echo "<h2>📁 Verificación de Archivos</h2>";

$files_to_check = [
    'php/database.php',
    'config/database.php',
    'config/config.php',
    'api/productos.php',
    'api/categorias.php',
    'api/obtener-pedidos.php'
];

foreach ($files_to_check as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p>✅ <strong>$file</strong> - Existe</p>";
    } else {
        echo "<p>❌ <strong>$file</strong> - NO EXISTE</p>";
    }
}

// Test 3: Verificar conexión a base de datos
echo "<h2>🗄️ Test de Base de Datos</h2>";

try {
    require_once __DIR__ . '/php/database.php';
    
    $pdo = DatabaseConfig::getConnection();
    echo "<p>✅ <strong>Conexión exitosa</strong></p>";
    
    // Verificar tablas
    $tables = ['products', 'categories', 'pedidos', 'envios'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "<p>✅ <strong>Tabla $table:</strong> {$result['count']} registros</p>";
        } catch (Exception $e) {
            echo "<p>❌ <strong>Tabla $table:</strong> Error - " . $e->getMessage() . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</p>";
}

// Test 4: Verificar endpoints de API
echo "<h2>🌐 Test de Endpoints API</h2>";

$base_url = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

$endpoints = [
    'api/productos.php',
    'api/categorias.php',
    'api/obtener-pedidos.php'
];

foreach ($endpoints as $endpoint) {
    $url = $base_url . '/' . $endpoint;
    echo "<p><strong>$endpoint:</strong> <a href='$url' target='_blank'>$url</a></p>";
    
    // Test básico con cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code === 200) {
        echo "<p>✅ <strong>HTTP $http_code</strong> - Endpoint funcional</p>";
        
        // Verificar si es JSON válido
        $json = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<p>✅ <strong>JSON válido</strong></p>";
        } else {
            echo "<p>⚠️ <strong>Respuesta no JSON:</strong> " . substr($response, 0, 100) . "...</p>";
        }
    } else {
        echo "<p>❌ <strong>HTTP $http_code</strong> - Error en endpoint</p>";
    }
    
    echo "<hr>";
}

// Test 5: Información del sistema
echo "<h2>⚙️ Información del Sistema</h2>";
echo "<p><strong>OS:</strong> " . PHP_OS . "</p>";
echo "<p><strong>Memory Limit:</strong> " . ini_get('memory_limit') . "</p>";
echo "<p><strong>Max Execution Time:</strong> " . ini_get('max_execution_time') . "s</p>";
echo "<p><strong>Upload Max Filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";

// Test 6: Extensiones requeridas
echo "<h2>🔧 Extensiones PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p>✅ <strong>$ext</strong> - Cargada</p>";
    } else {
        echo "<p>❌ <strong>$ext</strong> - NO CARGADA</p>";
    }
}

echo "<br><br>";
echo "<p><strong>🕐 Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>🌐 URL actual:</strong> " . $_SERVER['REQUEST_URI'] . "</p>";

?>