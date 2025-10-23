<?php
/**
 * 🔥 VERIFICACIÓN FINAL DE CONFIGURACIÓN DE PRODUCCIÓN
 * Este script verifica que todas las credenciales de MercadoPago estén configuradas para producción
 */

require_once 'config/config-global.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔥 Verificación Final - Producción MercadoPago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-prod { color: #28a745; font-weight: bold; }
        .status-test { color: #dc3545; font-weight: bold; }
        .status-warning { color: #ffc107; font-weight: bold; }
        .config-card { margin-bottom: 20px; }
        .credentials { font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">🔥 Verificación Final - Producción MercadoPago</h1>
        
        <?php
        // ===== VERIFICACIÓN DE CONFIG-GLOBAL.PHP =====
        $mpConfig = GlobalConfig::getMercadoPagoConfig();
        $isProduction = GlobalConfig::isProduction();
        
        echo "<div class='config-card'>";
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5>📁 config-global.php</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        
        echo "<p><strong>Entorno:</strong> ";
        if ($isProduction) {
            echo "<span class='status-prod'>✅ PRODUCCIÓN</span>";
        } else {
            echo "<span class='status-test'>❌ DESARROLLO</span>";
        }
        echo "</p>";
        
        echo "<p><strong>Environment MercadoPago:</strong> ";
        if ($mpConfig['environment'] === 'production') {
            echo "<span class='status-prod'>✅ PRODUCTION</span>";
        } else {
            echo "<span class='status-test'>❌ " . strtoupper($mpConfig['environment']) . "</span>";
        }
        echo "</p>";
        
        echo "<div class='credentials'>";
        echo "<strong>Credenciales:</strong><br>";
        echo "Public Key: " . substr($mpConfig['public_key'], 0, 30) . "...<br>";
        echo "Access Token: " . substr($mpConfig['access_token'], 0, 30) . "...<br>";
        
        // Verificar si son credenciales de producción
        $isProdCredentials = (
            strpos($mpConfig['public_key'], 'APP_USR-') === 0 && 
            strpos($mpConfig['access_token'], 'APP_USR-') === 0 &&
            strpos($mpConfig['public_key'], 'TEST-') === false &&
            strpos($mpConfig['access_token'], 'TEST-') === false
        );
        
        if ($isProdCredentials) {
            echo "<span class='status-prod'>✅ Credenciales de PRODUCCIÓN correctas</span>";
        } else {
            echo "<span class='status-test'>❌ Credenciales NO son de producción</span>";
        }
        echo "</div>";
        
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        // ===== VERIFICACIÓN DE ARCHIVOS JAVASCRIPT =====
        $jsFiles = [
            'js/mercadoPago.js' => 'Archivo principal MercadoPago',
            'js/premium-payment-real.js' => 'Pago Premium Real',
            'config/mercadopago-config.js' => 'Configuración JavaScript'
        ];
        
        echo "<div class='config-card'>";
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5>📄 Archivos JavaScript</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        
        foreach ($jsFiles as $file => $description) {
            echo "<h6>$description ($file)</h6>";
            
            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                // Buscar credenciales de test
                $hasTestCredentials = (
                    strpos($content, 'TEST-') !== false &&
                    preg_match('/[\'"]TEST-[a-f0-9\-]+[\'"]/', $content)
                );
                
                // Buscar credenciales de producción
                $hasProdCredentials = (
                    strpos($content, 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577') !== false
                );
                
                if ($hasProdCredentials && !$hasTestCredentials) {
                    echo "<span class='status-prod'>✅ Configurado para PRODUCCIÓN</span><br>";
                } elseif ($hasProdCredentials && $hasTestCredentials) {
                    echo "<span class='status-warning'>⚠️ Tiene AMBAS credenciales (revisar)</span><br>";
                } elseif ($hasTestCredentials) {
                    echo "<span class='status-test'>❌ Usando credenciales de TEST</span><br>";
                } else {
                    echo "<span class='status-warning'>⚠️ No se encontraron credenciales claras</span><br>";
                }
            } else {
                echo "<span class='status-warning'>⚠️ Archivo no encontrado</span><br>";
            }
        }
        
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        // ===== VERIFICACIÓN DE URLs =====
        $urls = GlobalConfig::getMercadoPagoUrls();
        
        echo "<div class='config-card'>";
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5>🌐 URLs de Respuesta</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        
        foreach ($urls as $type => $url) {
            echo "<strong>" . ucfirst($type) . ":</strong> $url<br>";
        }
        
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        // ===== RESUMEN FINAL =====
        $allGood = (
            $isProduction && 
            $mpConfig['environment'] === 'production' && 
            $isProdCredentials
        );
        
        echo "<div class='config-card'>";
        echo "<div class='card " . ($allGood ? 'border-success' : 'border-danger') . "'>";
        echo "<div class='card-header " . ($allGood ? 'bg-success text-white' : 'bg-danger text-white') . "'>";
        echo "<h5>📊 Resumen Final</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        
        if ($allGood) {
            echo "<h4 class='status-prod'>🎉 ¡CONFIGURACIÓN DE PRODUCCIÓN CORRECTA!</h4>";
            echo "<p>Todas las credenciales están configuradas para PRODUCCIÓN. El sistema está listo para procesar pagos reales.</p>";
            
            echo "<div class='alert alert-success'>";
            echo "<h6>✅ Estado Actual:</h6>";
            echo "<ul>";
            echo "<li>Entorno: PRODUCCIÓN</li>";
            echo "<li>Credenciales: REALES</li>";
            echo "<li>MercadoPago: PRODUCTION MODE</li>";
            echo "<li>URLs: Configuradas</li>";
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<h4 class='status-test'>❌ CONFIGURACIÓN INCOMPLETA</h4>";
            echo "<p>Algunos elementos aún no están configurados para producción.</p>";
            
            echo "<div class='alert alert-warning'>";
            echo "<h6>⚠️ Elementos a revisar:</h6>";
            echo "<ul>";
            if (!$isProduction) echo "<li>Forzar modo producción en config-global.php</li>";
            if ($mpConfig['environment'] !== 'production') echo "<li>Environment de MercadoPago no es 'production'</li>";
            if (!$isProdCredentials) echo "<li>Credenciales no son de producción</li>";
            echo "</ul>";
            echo "</div>";
        }
        
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        // ===== CREDENCIALES ACTUALES PARA REFERENCIA =====
        echo "<div class='config-card'>";
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5>🔑 Credenciales de Producción (Referencia)</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<div class='credentials'>";
        echo "<strong>Public Key:</strong> APP_USR-5afce1ba-5244-42d4-939e-f9979851577<br>";
        echo "<strong>Access Token:</strong> APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340<br>";
        echo "<strong>Environment:</strong> production";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        ?>
        
        <div class="text-center mt-4">
            <a href="index.html" class="btn btn-primary">🏠 Volver al Inicio</a>
            <a href="verificar-localhost.php" class="btn btn-secondary">🔍 Verificar Sistema</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>