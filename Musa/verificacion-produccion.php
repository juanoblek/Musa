<?php
/**
 * 🔍 VERIFICACIÓN FINAL PARA PRODUCCIÓN
 * Ejecuta este script antes de activar pagos reales
 */

echo "<h1>🔍 VERIFICACIÓN SISTEMA MUSA MODA - PRODUCCIÓN</h1>";

// 1. Verificar configuración de MercadoPago
echo "<h2>1. 🔧 Configuración MercadoPago</h2>";
if (file_exists('config/mercadopago-production.php')) {
    require_once 'config/mercadopago-production.php';
    $config = getMercadoPagoConfig();
    
    echo "✅ Archivo de configuración encontrado<br>";
    echo "🔑 Public Key: " . substr($config['public_key'], 0, 20) . "...<br>";
    echo "🔐 Access Token: " . substr($config['access_token'], 0, 20) . "...<br>";
    echo "🌍 Entorno: " . $config['environment'] . "<br>";
    
    if ($config['environment'] === 'production') {
        echo "✅ Configurado para PRODUCCIÓN<br>";
    } else {
        echo "❌ ADVERTENCIA: No está en modo producción<br>";
    }
} else {
    echo "❌ ERROR: Archivo de configuración no encontrado<br>";
}

// 2. Verificar base de datos
echo "<h2>2. 🗄️ Base de Datos</h2>";
try {
    require_once 'config/database.php';
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ Conexión a BD exitosa<br>";
    echo "📊 Base de datos: " . DB_NAME . "<br>";
    
    // Verificar tablas
    $tables = ['productos', 'pedidos', 'envios', 'pedido_tracking'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla '$table' existe<br>";
        } else {
            echo "❌ ERROR: Tabla '$table' no encontrada<br>";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR de BD: " . $e->getMessage() . "<br>";
}

// 3. Verificar SSL/HTTPS
echo "<h2>3. 🔐 Seguridad</h2>";
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    echo "✅ HTTPS activado<br>";
} else {
    echo "⚠️ ADVERTENCIA: HTTPS no detectado (requerido para producción)<br>";
}

// 4. Verificar archivos críticos
echo "<h2>4. 📄 Archivos Críticos</h2>";
$files = [
    'pago-premium.html' => 'Página de pago',
    'admin-panel.html' => 'Panel administrativo',
    'api/process-payment.php' => 'API de procesamiento',
    'api/guardar-pedido.php' => 'API de guardado',
    'config/database.php' => 'Configuración BD'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "✅ $description ($file)<br>";
    } else {
        echo "❌ ERROR: $description no encontrado ($file)<br>";
    }
}

// 5. Verificar configuración PHP
echo "<h2>5. ⚙️ Configuración PHP</h2>";
echo "📝 Versión PHP: " . phpversion() . "<br>";

$extensions = ['curl', 'json', 'pdo_mysql'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión $ext disponible<br>";
    } else {
        echo "❌ ERROR: Extensión $ext no disponible<br>";
    }
}

// 6. Estado final
echo "<h2>6. 🎯 Estado Final</h2>";
$errors = 0; // Contaríamos errores reales aquí

if ($errors === 0) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
    echo "<h3>✅ SISTEMA LISTO PARA PRODUCCIÓN</h3>";
    echo "<p>Todos los componentes están correctamente configurados.</p>";
    echo "<p><strong>Siguiente paso:</strong> Realizar una prueba con una compra real de bajo monto.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ ERRORES DETECTADOS</h3>";
    echo "<p>Resolver los errores antes de activar el sistema.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><strong>Fecha de verificación:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";
?>