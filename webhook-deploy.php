<?php
/**
 * 🔗 Webhook de despliegue automático desde GitHub
 * Este archivo recibe notificaciones de GitHub y ejecuta el script de deploy
 * 
 * INSTALACIÓN:
 * 1. Sube este archivo a: public_html/webhook-deploy.php
 * 2. Cambia SECRET_TOKEN por algo único y seguro
 * 3. Ajusta DEPLOY_SCRIPT con la ruta correcta de tu hosting
 * 4. Crea la carpeta logs: mkdir ~/logs
 * 5. Configura el webhook en GitHub con esta URL y el mismo SECRET_TOKEN
 */

// ⚙️ CONFIGURACIÓN - AJUSTAR SEGÚN TU HOSTING
define('SECRET_TOKEN', 'CAMBIA_ESTE_TOKEN_POR_ALGO_UNICO_Y_LARGO'); // Genera uno aleatorio: openssl rand -hex 32
define('DEPLOY_SCRIPT', '/home/TU_USUARIO/repos/musa-ecommerce/deploy.sh'); // Ajusta "TU_USUARIO"
define('LOG_FILE', '/home/TU_USUARIO/logs/webhook-deploy.log'); // Ajusta "TU_USUARIO"

// Función para logging seguro
function logMessage($message, $level = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logDir = dirname(LOG_FILE);
    
    // Crear carpeta logs si no existe
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logEntry = "[$timestamp] [$level] $message\n";
    @file_put_contents(LOG_FILE, $logEntry, FILE_APPEND | LOCK_EX);
    
    // También enviar a output para debug
    echo $logEntry;
}

// Headers de respuesta
header('Content-Type: application/json');

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    logMessage('Método no permitido: ' . $_SERVER['REQUEST_METHOD'], 'WARN');
    die(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}

// Obtener payload
$payload = file_get_contents('php://input');

if (empty($payload)) {
    http_response_code(400);
    logMessage('Payload vacío recibido', 'ERROR');
    die(json_encode(['status' => 'error', 'message' => 'Empty payload']));
}

// Verificar firma de GitHub (seguridad crítica)
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (empty($signature)) {
    http_response_code(403);
    logMessage('Sin firma de GitHub - Acceso denegado', 'ERROR');
    die(json_encode(['status' => 'error', 'message' => 'Missing signature']));
}

// Validar firma
$expected = 'sha256=' . hash_hmac('sha256', $payload, SECRET_TOKEN);
if (!hash_equals($expected, $signature)) {
    http_response_code(403);
    logMessage('Firma inválida - Posible intento de acceso no autorizado', 'ERROR');
    die(json_encode(['status' => 'error', 'message' => 'Invalid signature']));
}

// Parsear el payload
$data = json_decode($payload, true);

if (!$data) {
    http_response_code(400);
    logMessage('JSON inválido en payload', 'ERROR');
    die(json_encode(['status' => 'error', 'message' => 'Invalid JSON']));
}

// Verificar que sea un push al branch main
$ref = $data['ref'] ?? '';
if ($ref !== 'refs/heads/main') {
    logMessage("Evento ignorado - Branch: $ref (esperado: refs/heads/main)", 'INFO');
    die(json_encode(['status' => 'ignored', 'message' => 'Not a push to main branch']));
}

// Log del evento
$commits = count($data['commits'] ?? []);
$pusher = $data['pusher']['name'] ?? 'unknown';
$commitMessages = [];

foreach (($data['commits'] ?? []) as $commit) {
    $commitMessages[] = substr($commit['message'] ?? '', 0, 50);
}

logMessage("Push recibido de '$pusher' con $commits commit(s)", 'INFO');
if (!empty($commitMessages)) {
    logMessage("Últimos commits: " . implode(' | ', $commitMessages), 'INFO');
}

// Verificar que el script de deploy existe y es ejecutable
if (!file_exists(DEPLOY_SCRIPT)) {
    http_response_code(500);
    logMessage('Script de deploy no encontrado: ' . DEPLOY_SCRIPT, 'ERROR');
    die(json_encode(['status' => 'error', 'message' => 'Deploy script not found']));
}

if (!is_executable(DEPLOY_SCRIPT)) {
    logMessage('Script de deploy no es ejecutable, intentando dar permisos...', 'WARN');
    @chmod(DEPLOY_SCRIPT, 0755);
}

// Ejecutar script de despliegue en background
$command = escapeshellcmd(DEPLOY_SCRIPT) . ' >> ' . escapeshellarg(LOG_FILE) . ' 2>&1 &';
logMessage("Ejecutando: $command", 'INFO');

exec($command, $output, $return);

if ($return === 0) {
    logMessage('Script de despliegue ejecutado exitosamente', 'SUCCESS');
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Deployment triggered successfully',
        'commits' => $commits,
        'pusher' => $pusher
    ]);
} else {
    logMessage("Error al ejecutar script de despliegue (exit code: $return)", 'ERROR');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to execute deployment script',
        'exit_code' => $return
    ]);
}
?>
