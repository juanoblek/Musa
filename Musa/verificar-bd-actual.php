<?php
/**
 * =========================================
 * 🔍 VERIFICADOR DE CONEXIÓN DE BASE DE DATOS
 * =========================================
 * Este script revisa a qué base de datos está conectada actualmente
 */

echo "<h2>🔍 VERIFICACIÓN DE CONEXIÓN DE BASE DE DATOS</h2>";
echo "<p><strong>Fecha/Hora:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<hr>";

// 1. VERIFICAR CONFIG-GLOBAL.PHP
echo "<h3>1. 📁 Configuración desde config-global.php</h3>";
try {
    include_once 'config/config-global.php';
    
    $globalConfig = GlobalConfig::getConfig();
    $dbConfig = $globalConfig['database'];
    
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><td><strong>Host:</strong></td><td>" . $dbConfig['host'] . "</td></tr>";
    echo "<tr><td><strong>Base de Datos:</strong></td><td>" . $dbConfig['dbname'] . "</td></tr>";
    echo "<tr><td><strong>Usuario:</strong></td><td>" . $dbConfig['username'] . "</td></tr>";
    echo "<tr><td><strong>Entorno:</strong></td><td>" . ($globalConfig['environment']) . "</td></tr>";
    echo "</table>";
    
    echo "<p>✅ <strong>Configuración cargada correctamente desde config-global.php</strong></p>";
    
    // Intentar conexión con esta configuración
    try {
        $pdo = new PDO(
            "mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'] . ";charset=utf8mb4",
            $dbConfig['username'],
            $dbConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p>✅ <strong>CONEXIÓN EXITOSA con config-global.php</strong></p>";
        
        // Verificar qué base de datos está usando
        $stmt = $pdo->query("SELECT DATABASE() as current_db");
        $result = $stmt->fetch();
        echo "<p>🎯 <strong>Base de datos actual:</strong> " . $result['current_db'] . "</p>";
        
        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>📋 <strong>Tablas disponibles (" . count($tables) . "):</strong> " . implode(', ', $tables) . "</p>";
        
    } catch (PDOException $e) {
        echo "<p>❌ <strong>ERROR de conexión con config-global.php:</strong> " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>ERROR cargando config-global.php:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 2. VERIFICAR php/database.php
echo "<h3>2. 📁 Configuración desde php/database.php</h3>";
try {
    include_once 'php/database.php';
    
    $conn = DatabaseConfig::getConnection();
    
    if ($conn) {
        echo "<p>✅ <strong>CONEXIÓN EXITOSA con php/database.php</strong></p>";
        
        // Verificar qué base de datos está usando
        $stmt = $conn->query("SELECT DATABASE() as current_db");
        $result = $stmt->fetch();
        echo "<p>🎯 <strong>Base de datos actual:</strong> " . $result['current_db'] . "</p>";
        
        // Verificar tablas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<p>📋 <strong>Tablas disponibles (" . count($tables) . "):</strong> " . implode(', ', $tables) . "</p>";
        
    } else {
        echo "<p>❌ <strong>ERROR: No se pudo obtener conexión</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ <strong>ERROR con php/database.php:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 3. VERIFICAR API productos-v2.php configuración
echo "<h3>3. 📁 Configuración desde API productos-v2.php</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><td><strong>Host:</strong></td><td>localhost</td></tr>";
echo "<tr><td><strong>Base de Datos:</strong></td><td>janithal_musa_moda</td></tr>";
echo "<tr><td><strong>Usuario:</strong></td><td>janithal_usuario_musaarion_db</td></tr>";
echo "<tr><td><strong>Archivo:</strong></td><td>api/productos-v2.php</td></tr>";
echo "</table>";

try {
    $pdo_api = new PDO(
        "mysql:host=localhost;dbname=janithal_musa_moda;charset=utf8mb4",
        'janithal_usuario_musaarion_db',
        'Chiguiro553021',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✅ <strong>CONEXIÓN EXITOSA con credenciales de API</strong></p>";
    
    // Verificar qué base de datos está usando
    $stmt = $pdo_api->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch();
    echo "<p>🎯 <strong>Base de datos actual:</strong> " . $result['current_db'] . "</p>";
    
    // Verificar productos
    $stmt = $pdo_api->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    echo "<p>🛍️ <strong>Total de productos:</strong> " . $result['total'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>ERROR con credenciales de API:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 4. VERIFICAR BASE DE DATOS LOCAL musa_moda
echo "<h3>4. 📁 Verificación de BD Local (musa_moda)</h3>";
try {
    $pdo_local = new PDO(
        "mysql:host=localhost;dbname=musa_moda;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<p>✅ <strong>CONEXIÓN EXITOSA a BD local musa_moda</strong></p>";
    
    // Verificar productos
    $stmt = $pdo_local->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    echo "<p>🛍️ <strong>Total de productos en BD local:</strong> " . $result['total'] . "</p>";
    
} catch (PDOException $e) {
    echo "<p>❌ <strong>ERROR con BD local:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 5. RESUMEN FINAL
echo "<h3>🎯 RESUMEN FINAL</h3>";
echo "<div style='background: #f0f8ff; padding: 15px; border: 1px solid #4CAF50;'>";
echo "<p><strong>Estado actual del sistema:</strong></p>";
echo "<ul>";
echo "<li>✅ <strong>config-global.php</strong> está FORZADO a localhost (BD: musa_moda)</li>";
echo "<li>⚠️ <strong>php/database.php</strong> está conectado a BD del hosting (janithal_musa_moda)</li>";
echo "<li>⚠️ <strong>api/productos-v2.php</strong> está conectado a BD del hosting (janithal_musa_moda)</li>";
echo "<li>📝 <strong>CONFLICTO:</strong> Diferentes archivos usan diferentes BDs</li>";
echo "</ul>";
echo "</div>";

echo "<h4>🔧 ACCIONES RECOMENDADAS:</h4>";
echo "<ol>";
echo "<li>Unificar todas las conexiones para usar la misma BD</li>";
echo "<li>Si quieres usar BD local, cambiar api/productos-v2.php y php/database.php</li>";
echo "<li>Si quieres usar BD del hosting, cambiar config-global.php</li>";
echo "</ol>";

?>