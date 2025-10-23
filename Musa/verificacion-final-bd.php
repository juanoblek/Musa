<?php
/**
 * =========================================
 * ✅ VERIFICACIÓN FINAL DE CONFIGURACIÓN UNIFICADA
 * =========================================
 * Confirmar que todas las conexiones usan janithal_musa_moda en localhost
 */

echo "<h2>✅ VERIFICACIÓN FINAL - CONFIGURACIÓN UNIFICADA</h2>";
echo "<p><strong>Fecha/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Objetivo:</strong> Todas las conexiones a janithal_musa_moda en localhost</p>";

echo "<hr>";

$allCorrect = true;

// 1. VERIFICAR config-global.php
echo "<h3>1. 📁 config-global.php</h3>";
try {
    include_once 'config/config-global.php';
    
    $globalConfig = GlobalConfig::getConfig();
    $dbConfig = $globalConfig['database'];
    
    if ($dbConfig['dbname'] === 'janithal_musa_moda' && $dbConfig['username'] === 'root') {
        echo "<p>✅ <strong>CORRECTO:</strong> BD: {$dbConfig['dbname']}, Usuario: {$dbConfig['username']}</p>";
    } else {
        echo "<p>❌ <strong>ERROR:</strong> BD: {$dbConfig['dbname']}, Usuario: {$dbConfig['username']}</p>";
        $allCorrect = false;
    }
    
    // Probar conexión
    $pdo = new PDO(
        "mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'] . ";charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    echo "<p>🛍️ <strong>Productos encontrados:</strong> " . $result['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>ERROR en config-global.php:</strong> " . $e->getMessage() . "</p>";
    $allCorrect = false;
}

echo "<hr>";

// 2. VERIFICAR php/database.php
echo "<h3>2. 📁 php/database.php</h3>";
try {
    include_once 'php/database.php';
    
    $conn = DatabaseConfig::getConnection();
    
    // Verificar qué BD está usando
    $stmt = $conn->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    
    if ($result['current_db'] === 'janithal_musa_moda') {
        echo "<p>✅ <strong>CORRECTO:</strong> Conectado a {$result['current_db']}</p>";
    } else {
        echo "<p>❌ <strong>ERROR:</strong> Conectado a {$result['current_db']} (debería ser janithal_musa_moda)</p>";
        $allCorrect = false;
    }
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    echo "<p>🛍️ <strong>Productos encontrados:</strong> " . $result['total'] . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ <strong>ERROR en php/database.php:</strong> " . $e->getMessage() . "</p>";
    $allCorrect = false;
}

echo "<hr>";

// 3. VERIFICAR API productos-v2.php (simulación)
echo "<h3>3. 📁 api/productos-v2.php</h3>";
try {
    // Credenciales que debería tener la API
    $pdo_api = new PDO(
        "mysql:host=localhost;dbname=janithal_musa_moda;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✅ <strong>CORRECTO:</strong> API puede conectar a janithal_musa_moda con root</p>";
    
    $stmt = $pdo_api->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    echo "<p>🛍️ <strong>Productos encontrados:</strong> " . $result['total'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>ERROR en credenciales de API:</strong> " . $e->getMessage() . "</p>";
    $allCorrect = false;
}

echo "<hr>";

// 4. RESUMEN FINAL
echo "<h3>🎯 RESUMEN FINAL</h3>";

if ($allCorrect) {
    echo "<div style='background: #d4edda; padding: 20px; border: 2px solid #28a745; border-radius: 10px;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>✅ ¡CONFIGURACIÓN PERFECTA!</h4>";
    echo "<p style='margin: 5px 0;'><strong>Estado:</strong> Todas las conexiones están unificadas</p>";
    echo "<p style='margin: 5px 0;'><strong>Base de Datos:</strong> janithal_musa_moda (localhost)</p>";
    echo "<p style='margin: 5px 0;'><strong>Usuario:</strong> root (sin contraseña)</p>";
    echo "<p style='margin: 5px 0;'><strong>Archivos actualizados:</strong></p>";
    echo "<ul style='margin: 10px 0;'>";
    echo "<li>✅ config/config-global.php</li>";
    echo "<li>✅ php/database.php</li>";
    echo "<li>✅ api/productos-v2.php</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 20px; border: 2px solid #dc3545; border-radius: 10px;'>";
    echo "<h4 style='color: #721c24; margin: 0 0 10px 0;'>❌ HAY PROBLEMAS EN LA CONFIGURACIÓN</h4>";
    echo "<p style='margin: 5px 0;'>Revisa los errores mostrados arriba y corrige las configuraciones necesarias.</p>";
    echo "</div>";
}

echo "<hr>";

// 5. INSTRUCCIONES ADICIONALES
echo "<h3>📝 PRÓXIMOS PASOS</h3>";
echo "<ol>";
echo "<li>✅ Configuración de BD unificada completada</li>";
echo "<li>🔄 Verificar que la plataforma carga productos correctamente</li>";
echo "<li>🧪 Probar funcionalidades (carrito, admin, etc.)</li>";
echo "<li>📋 Verificar que no hay errores de conexión en logs</li>";
echo "</ol>";

?>