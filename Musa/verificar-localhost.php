<?php
/**
 * ===============================================
 * 🔍 VERIFICADOR DE CONFIGURACIÓN LOCALHOST
 * ===============================================
 * Este script verifica que toda la configuración
 * esté apuntando correctamente a localhost
 * ===============================================
 */

require_once 'config/config-global.php';
require_once 'config/database.php';
require_once 'config/localhost-config.php';

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>🏠 Verificador Configuración Localhost</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .status-ok { color: #28a745; }
        .status-error { color: #dc3545; }
        .status-warning { color: #ffc107; }
        .config-section { border-left: 4px solid #007bff; padding-left: 1rem; }
    </style>
</head>
<body class='bg-light'>
<div class='container mt-5'>
    <div class='row justify-content-center'>
        <div class='col-md-10'>
            <div class='card'>
                <div class='card-header bg-primary text-white'>
                    <h3 class='mb-0'>🏠 Verificador de Configuración Localhost</h3>
                    <small>Verificando que todo esté configurado para desarrollo local</small>
                </div>
                <div class='card-body'>";

// ===== VERIFICACIÓN DE ENTORNO =====
echo "<div class='config-section mb-4'>
        <h5>🌍 Verificación de Entorno</h5>";

$isProduction = GlobalConfig::isProduction();
$domain = GlobalConfig::getDomain();

echo "<div class='row'>
        <div class='col-md-6'>
            <strong>Modo de Producción:</strong> 
            <span class='" . ($isProduction ? "status-error" : "status-ok") . "'>
                " . ($isProduction ? "❌ SÍ (ERROR - debería ser NO)" : "✅ NO (Correcto)") . "
            </span>
        </div>
        <div class='col-md-6'>
            <strong>Dominio Detectado:</strong> 
            <span class='text-info'>{$domain}</span>
        </div>
      </div>";

echo "</div>";

// ===== VERIFICACIÓN DE BASE DE DATOS =====
echo "<div class='config-section mb-4'>
        <h5>🗄️ Configuración de Base de Datos</h5>";

$dbConfig = GlobalConfig::getDatabaseConfig();

echo "<table class='table table-sm'>
        <tr>
            <td><strong>Host:</strong></td>
            <td>{$dbConfig['host']}</td>
            <td><span class='" . ($dbConfig['host'] === 'localhost' ? "status-ok" : "status-error") . "'>
                " . ($dbConfig['host'] === 'localhost' ? "✅" : "❌") . "
            </span></td>
        </tr>
        <tr>
            <td><strong>Base de Datos:</strong></td>
            <td>{$dbConfig['dbname']}</td>
            <td><span class='" . ($dbConfig['dbname'] === 'musa_moda' ? "status-ok" : "status-warning") . "'>
                " . ($dbConfig['dbname'] === 'musa_moda' ? "✅" : "⚠️") . "
            </span></td>
        </tr>
        <tr>
            <td><strong>Usuario:</strong></td>
            <td>{$dbConfig['username']}</td>
            <td><span class='" . ($dbConfig['username'] === 'root' ? "status-ok" : "status-warning") . "'>
                " . ($dbConfig['username'] === 'root' ? "✅" : "⚠️") . "
            </span></td>
        </tr>
        <tr>
            <td><strong>Contraseña:</strong></td>
            <td>" . (empty($dbConfig['password']) ? '(vacía)' : '***') . "</td>
            <td><span class='" . (empty($dbConfig['password']) ? "status-ok" : "status-warning") . "'>
                " . (empty($dbConfig['password']) ? "✅" : "⚠️") . "
            </span></td>
        </tr>
      </table>";

// Intentar conexión
try {
    $connection = DatabaseConfig::getConnection();
    echo "<div class='alert alert-success'>✅ <strong>Conexión exitosa</strong> a la base de datos localhost</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>❌ <strong>Error de conexión:</strong> " . $e->getMessage() . "</div>";
}

echo "</div>";

// ===== VERIFICACIÓN DE MERCADOPAGO =====
echo "<div class='config-section mb-4'>
        <h5>💳 Configuración de MercadoPago</h5>";

$mpConfig = GlobalConfig::getMercadoPagoConfig();
$mpUrls = GlobalConfig::getMercadoPagoUrls();

echo "<table class='table table-sm'>
        <tr>
            <td><strong>Entorno:</strong></td>
            <td>{$mpConfig['environment']}</td>
            <td><span class='" . ($mpConfig['environment'] === 'sandbox' ? "status-ok" : "status-error") . "'>
                " . ($mpConfig['environment'] === 'sandbox' ? "✅ Sandbox (Correcto)" : "❌ Producción (ERROR)") . "
            </span></td>
        </tr>
        <tr>
            <td><strong>Public Key:</strong></td>
            <td>" . substr($mpConfig['public_key'], 0, 20) . "...</td>
            <td><span class='" . (strpos($mpConfig['public_key'], 'TEST-') === 0 ? "status-ok" : "status-error") . "'>
                " . (strpos($mpConfig['public_key'], 'TEST-') === 0 ? "✅ TEST" : "❌ PROD") . "
            </span></td>
        </tr>
        <tr>
            <td><strong>Access Token:</strong></td>
            <td>" . substr($mpConfig['access_token'], 0, 20) . "...</td>
            <td><span class='" . (strpos($mpConfig['access_token'], 'TEST-') === 0 ? "status-ok" : "status-error") . "'>
                " . (strpos($mpConfig['access_token'], 'TEST-') === 0 ? "✅ TEST" : "❌ PROD") . "
            </span></td>
        </tr>
      </table>";

echo "<h6>URLs de Retorno:</h6>
      <ul class='list-group list-group-flush'>";
foreach ($mpUrls as $type => $url) {
    $isLocalhost = strpos($url, 'localhost') !== false;
    echo "<li class='list-group-item d-flex justify-content-between'>
            <span><strong>" . ucfirst($type) . ":</strong> {$url}</span>
            <span class='" . ($isLocalhost ? "status-ok" : "status-error") . "'>
                " . ($isLocalhost ? "✅" : "❌") . "
            </span>
          </li>";
}
echo "</ul>";

echo "</div>";

// ===== VERIFICACIÓN DE ARCHIVOS =====
echo "<div class='config-section mb-4'>
        <h5>📁 Verificación de Archivos de Configuración</h5>";

$configFiles = [
    'config/config-global.php' => 'Configuración Global',
    'config/database.php' => 'Configuración de Base de Datos',
    'config/mercadopago.php' => 'Configuración MercadoPago',
    'config/localhost-config.php' => 'Configuración Localhost',
    'config/hosting-config-backup.php' => 'Backup Hosting',
];

echo "<div class='row'>";
foreach ($configFiles as $file => $description) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "<div class='col-md-6 mb-2'>
            <span class='" . ($exists ? "status-ok" : "status-error") . "'>
                " . ($exists ? "✅" : "❌") . " {$description}
            </span>
          </div>";
}
echo "</div>";

echo "</div>";

// ===== RESUMEN =====
$errors = 0;
$warnings = 0;

if ($isProduction) $errors++;
if ($dbConfig['host'] !== 'localhost') $errors++;
if ($mpConfig['environment'] !== 'sandbox') $errors++;
if (strpos($mpConfig['public_key'], 'TEST-') !== 0) $errors++;

if ($dbConfig['dbname'] !== 'musa_moda') $warnings++;
if ($dbConfig['username'] !== 'root') $warnings++;

$statusClass = $errors > 0 ? 'danger' : ($warnings > 0 ? 'warning' : 'success');
$statusIcon = $errors > 0 ? '❌' : ($warnings > 0 ? '⚠️' : '✅');
$statusText = $errors > 0 ? 'ERRORES ENCONTRADOS' : ($warnings > 0 ? 'ADVERTENCIAS' : 'CONFIGURACIÓN CORRECTA');

echo "<div class='alert alert-{$statusClass}'>
        <h5>{$statusIcon} {$statusText}</h5>";

if ($errors > 0) {
    echo "<p><strong>Errores:</strong> {$errors} - Requieren corrección inmediata</p>";
}
if ($warnings > 0) {
    echo "<p><strong>Advertencias:</strong> {$warnings} - Revisar configuración</p>";
}
if ($errors === 0 && $warnings === 0) {
    echo "<p>🎉 ¡Todo está configurado correctamente para localhost!</p>";
}

echo "</div>";

echo "        </div>
            </div>
        </div>
    </div>
</div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>