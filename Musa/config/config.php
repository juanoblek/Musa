<?php
/**
 * Archivo principal de configuración para producción
 * MUSA MODA - musaarion.com
 */

// Incluir configuración de base de datos
require_once __DIR__ . '/database.php';

// Detectar entorno
$isProduction = !($_SERVER['SERVER_NAME'] === 'localhost' || 
                   $_SERVER['SERVER_NAME'] === '127.0.0.1' || 
                   strpos($_SERVER['SERVER_NAME'], 'localhost') !== false);

// Configuraciones globales
define('IS_PRODUCTION', $isProduction);
define('ENVIRONMENT', $isProduction ? 'production' : 'development');

// Configuraciones de error
if (IS_PRODUCTION) {
    // Producción: logs de errores, sin mostrar errores
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    // Desarrollo: mostrar todos los errores
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// Configuraciones de sesión
if (IS_PRODUCTION) {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
}

// Configuraciones de seguridad
if (IS_PRODUCTION) {
    // Headers de seguridad
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// Configuraciones de MercadoPago
if (IS_PRODUCTION) {
    define('MP_ENVIRONMENT', 'production');
    define('MP_PUBLIC_KEY', ''); // Colocar clave pública de producción
    define('MP_ACCESS_TOKEN', ''); // Colocar token de acceso de producción
} else {
    define('MP_ENVIRONMENT', 'sandbox');
    define('MP_PUBLIC_KEY', 'TEST-70da85e6-8bcb-4f2c-9c62-d8532ae88a4a');
    define('MP_ACCESS_TOKEN', 'TEST-8523658394993428-092015-8ff4c20cde7db85df50dab36bb3f982f-623735630');
}

// URLs de respuesta de MercadoPago
$baseUrl = IS_PRODUCTION ? 'https://musaarion.com' : 'http://localhost/Musa';
define('MP_SUCCESS_URL', $baseUrl . '/success.html');
define('MP_PENDING_URL', $baseUrl . '/pending.html');
define('MP_FAILURE_URL', $baseUrl . '/failure.html');
define('MP_SUCCESS_PREMIUM_URL', $baseUrl . '/success-premium.html');
define('MP_PENDING_PREMIUM_URL', $baseUrl . '/pending-premium.html');
define('MP_FAILURE_PREMIUM_URL', $baseUrl . '/failure-premium.html');

// Configuración de CORS
function setCorsHeaders() {
    $allowedOrigins = [
        'https://musaarion.com',
        'http://localhost:3000',
        'http://localhost'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

// Función para logging
function logError($message, $context = []) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'server' => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? '',
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
            'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]
    ];
    
    $logFile = __DIR__ . '/../logs/application.log';
    $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($logData) . PHP_EOL;
    
    // Crear directorio de logs si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Función para respuestas JSON estandarizadas
function jsonResponse($data, $status = 200, $message = '') {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success' => $status >= 200 && $status < 300,
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c'),
        'environment' => ENVIRONMENT
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Función para validar API key (opcional)
function validateApiKey() {
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
    $validKeys = [
        'musa_dev_key_2025',
        'musa_prod_key_2025'
    ];
    
    if (IS_PRODUCTION && !in_array($apiKey, $validKeys)) {
        jsonResponse(null, 401, 'API key requerida');
    }
}

// Configuraciones de subida de archivos
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');

// Función para limpiar uploads antiguos
function cleanOldUploads($days = 30) {
    $uploadPath = UPLOAD_PATH;
    $cutoffTime = time() - ($days * 24 * 60 * 60);
    
    if (is_dir($uploadPath)) {
        $files = glob($uploadPath . '*');
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoffTime) {
                unlink($file);
            }
        }
    }
}

// Configuraciones de cache
define('CACHE_ENABLED', IS_PRODUCTION);
define('CACHE_DURATION', 3600); // 1 hora

// Función simple de cache
function getCached($key, $callback, $duration = CACHE_DURATION) {
    if (!CACHE_ENABLED) {
        return $callback();
    }
    
    $cacheFile = __DIR__ . '/../cache/' . md5($key) . '.cache';
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $duration) {
        return unserialize(file_get_contents($cacheFile));
    }
    
    $data = $callback();
    
    // Crear directorio de cache si no existe
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    file_put_contents($cacheFile, serialize($data));
    return $data;
}

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>