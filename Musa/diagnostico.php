<?php
/**
 * Script de diagnóstico completo para el servidor
 * Súbelo a public_html/diagnostico.php
 */

header('Content-Type: application/json; charset=utf-8');

$diagnostico = [
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
];

// 1. Verificar estructura de archivos
$diagnostico['archivos'] = [
    'index.php' => file_exists(__DIR__ . '/index.php'),
    'api/' => is_dir(__DIR__ . '/api'),
    'api/productos-v2.php' => file_exists(__DIR__ . '/api/productos-v2.php'),
    'api/categorias.php' => file_exists(__DIR__ . '/api/categorias.php'),
    'api/navigation-categories.php' => file_exists(__DIR__ . '/api/navigation-categories.php'),
    'config/' => is_dir(__DIR__ . '/config'),
    'config/config-global.php' => file_exists(__DIR__ . '/config/config-global.php')
];

// 2. Verificar conexión a base de datos
try {
    if (file_exists(__DIR__ . '/config/config-global.php')) {
        require_once __DIR__ . '/config/config-global.php';
        $dbConfig = GlobalConfig::getDatabaseConfig();
        
        $diagnostico['database']['config_encontrada'] = true;
        $diagnostico['database']['host'] = $dbConfig['host'];
        $diagnostico['database']['dbname'] = $dbConfig['dbname'];
        $diagnostico['database']['username'] = $dbConfig['username'];
        
        // Intentar conectar
        try {
            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $diagnostico['database']['conexion'] = 'OK';
            
            // Verificar tablas
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $diagnostico['database']['tablas_encontradas'] = count($tables);
            $diagnostico['database']['tablas'] = $tables;
            
        } catch (PDOException $e) {
            $diagnostico['database']['conexion'] = 'ERROR';
            $diagnostico['database']['error'] = $e->getMessage();
        }
    } else {
        $diagnostico['database']['config_encontrada'] = false;
    }
} catch (Exception $e) {
    $diagnostico['database']['error_general'] = $e->getMessage();
}

// 3. Probar endpoint de productos
try {
    $productos_file = __DIR__ . '/api/productos-v2.php';
    if (file_exists($productos_file)) {
        ob_start();
        $_GET = []; // Simular request limpio
        include $productos_file;
        $output = ob_get_clean();
        
        $json = json_decode($output, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $diagnostico['api']['productos-v2'] = 'OK';
            $diagnostico['api']['productos_count'] = count($json ?? []);
        } else {
            $diagnostico['api']['productos-v2'] = 'ERROR_JSON';
            $diagnostico['api']['productos_output'] = substr($output, 0, 200);
        }
    } else {
        $diagnostico['api']['productos-v2'] = 'NO_EXISTE';
    }
} catch (Exception $e) {
    $diagnostico['api']['productos_error'] = $e->getMessage();
}

// 4. Verificar permisos
$diagnostico['permisos'] = [
    'api/' => is_writable(__DIR__ . '/api') ? 'writable' : 'read-only',
    'uploads/' => file_exists(__DIR__ . '/uploads') ? (is_writable(__DIR__ . '/uploads') ? 'writable' : 'read-only') : 'no_existe',
    'logs/' => file_exists(__DIR__ . '/logs') ? (is_writable(__DIR__ . '/logs') ? 'writable' : 'read-only') : 'no_existe'
];

// 5. PHP Extensions
$diagnostico['php_extensions'] = [
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'curl' => extension_loaded('curl'),
    'mbstring' => extension_loaded('mbstring'),
    'json' => extension_loaded('json')
];

echo json_encode($diagnostico, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
