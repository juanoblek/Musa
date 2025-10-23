<?php
/**
 * ===============================================
 * 🔄 CAMBIADOR DE CONFIGURACIÓN
 * ===============================================
 * Script para cambiar rápidamente entre localhost y hosting
 * ===============================================
 */

if (!isset($_GET['action'])) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>🔄 Cambiador de Configuración</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3>🔄 Cambiador de Configuración MUSA</h3>
                            <small>Cambiar entre localhost y hosting</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white">
                                            <h5>🏠 Configuración Localhost</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li>✅ Base de datos: <code>musa_moda</code></li>
                                                <li>✅ Usuario: <code>root</code> (sin contraseña)</li>
                                                <li>✅ MercadoPago: Sandbox/TEST</li>
                                                <li>✅ URLs: localhost</li>
                                                <li>✅ Debug: Habilitado</li>
                                            </ul>
                                            <a href="?action=localhost" class="btn btn-success w-100">
                                                🏠 Cambiar a Localhost
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-dark">
                                            <h5>🏢 Configuración Hosting</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li>⚠️ Base de datos: <code>janithal_musa_moda</code></li>
                                                <li>⚠️ Usuario: <code>janithal_usuario_*</code></li>
                                                <li>⚠️ MercadoPago: Producción</li>
                                                <li>⚠️ URLs: dominio real</li>
                                                <li>⚠️ Debug: Deshabilitado</li>
                                            </ul>
                                            <a href="?action=hosting" class="btn btn-warning w-100">
                                                🏢 Cambiar a Hosting
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="text-center">
                                <a href="verificar-localhost.php" class="btn btn-info">
                                    🔍 Verificar Configuración Actual
                                </a>
                                <a href="index.html" class="btn btn-outline-primary">
                                    🏠 Ir a la Tienda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Procesar cambio de configuración
$action = $_GET['action'];

if ($action === 'localhost') {
    // Cambiar a localhost
    $configGlobal = file_get_contents('config/config-global.php');
    
    // Forzar modo desarrollo
    $configGlobal = preg_replace(
        '/return false; \/\/ Siempre localhost/',
        'return false; // FORZADO A LOCALHOST',
        $configGlobal
    );
    
    file_put_contents('config/config-global.php', $configGlobal);
    
    $message = "✅ Configuración cambiada a LOCALHOST";
    $alertType = "success";
    
} elseif ($action === 'hosting') {
    // Cambiar a hosting (descomentar configuraciones)
    $configGlobal = file_get_contents('config/config-global.php');
    
    // Restaurar detección automática (comentar for localhost)
    $configGlobal = str_replace(
        'return false; // FORZADO A LOCALHOST',
        '// return false; // LOCALHOST COMENTADO - ACTIVAR AUTO-DETECT',
        $configGlobal
    );
    
    file_put_contents('config/config-global.php', $configGlobal);
    
    $message = "⚠️ Configuración cambiada a HOSTING - ¡REVISAR CREDENCIALES!";
    $alertType = "warning";
    
} else {
    $message = "❌ Acción no válida";
    $alertType = "danger";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Cambiada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-<?= $alertType ?> text-center">
                    <h4><?= $message ?></h4>
                    <hr>
                    <a href="?" class="btn btn-primary">🔄 Volver al Cambiador</a>
                    <a href="verificar-localhost.php" class="btn btn-info">🔍 Verificar</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>