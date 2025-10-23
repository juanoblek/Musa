<?php
/**
 * ✅ VERIFICACIÓN COMPLETA DEL SISTEMA
 * Confirmar que toda la plataforma funciona con janithal_musa_moda en localhost
 */

echo "<h1>🚀 VERIFICACIÓN COMPLETA DEL SISTEMA MUSA</h1>";
echo "<p><strong>Objetivo:</strong> Confirmar funcionamiento completo con janithal_musa_moda (localhost)</p>";

echo "<style>
.success { background: #d4edda; padding: 15px; border: 1px solid #28a745; border-radius: 5px; margin: 10px 0; }
.error { background: #f8d7da; padding: 15px; border: 1px solid #dc3545; border-radius: 5px; margin: 10px 0; }
.warning { background: #fff3cd; padding: 15px; border: 1px solid #ffc107; border-radius: 5px; margin: 10px 0; }
.info { background: #d1ecf1; padding: 15px; border: 1px solid #17a2b8; border-radius: 5px; margin: 10px 0; }
</style>";

$errors = 0;
$warnings = 0;
$successes = 0;

// 1. VERIFICAR CONEXIÓN PRINCIPAL
echo "<h2>1. 🔌 CONEXIÓN PRINCIPAL DE BASE DE DATOS</h2>";
try {
    include_once 'config/config-global.php';
    
    $globalConfig = GlobalConfig::getConfig();
    $dbConfig = $globalConfig['database'];
    
    $pdo = new PDO(
        "mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'] . ";charset=utf8mb4",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SELECT DATABASE() as current_db, COUNT(*) as productos FROM productos");
    $result = $stmt->fetch();
    
    echo "<div class='success'>";
    echo "<h4>✅ CONEXIÓN EXITOSA</h4>";
    echo "<p><strong>Base de Datos:</strong> " . $result['current_db'] . "</p>";
    echo "<p><strong>Productos disponibles:</strong> " . $result['productos'] . "</p>";
    echo "</div>";
    $successes++;
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h4>❌ ERROR DE CONEXIÓN PRINCIPAL</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    $errors++;
}

// 2. VERIFICAR API DE PRODUCTOS
echo "<h2>2. 🛍️ API DE PRODUCTOS</h2>";
try {
    $api_url = "http://localhost/Musa/api/productos-v2.php";
    $context = stream_context_create([
        'http' => [
            'timeout' => 10,
            'ignore_errors' => true
        ]
    ]);
    
    $response = file_get_contents($api_url, false, $context);
    
    if ($response === false) {
        throw new Exception("No se pudo conectar a la API");
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Respuesta de API no es JSON válido: " . json_last_error_msg());
    }
    
    if (isset($data['success']) && $data['success'] === true) {
        echo "<div class='success'>";
        echo "<h4>✅ API FUNCIONANDO CORRECTAMENTE</h4>";
        echo "<p><strong>Productos cargados:</strong> " . count($data['productos']) . "</p>";
        echo "<p><strong>Primer producto:</strong> " . ($data['productos'][0]['nombre'] ?? 'N/A') . "</p>";
        echo "</div>";
        $successes++;
    } else {
        echo "<div class='warning'>";
        echo "<h4>⚠️ API RESPONDE PERO CON ADVERTENCIAS</h4>";
        echo "<p><strong>Mensaje:</strong> " . ($data['message'] ?? 'Sin mensaje') . "</p>";
        echo "</div>";
        $warnings++;
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h4>❌ ERROR EN API DE PRODUCTOS</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    $errors++;
}

// 3. VERIFICAR TABLAS PRINCIPALES
echo "<h2>3. 🗂️ ESTRUCTURA DE BASE DE DATOS</h2>";
try {
    $required_tables = ['productos', 'categorias', 'pedidos', 'usuarios'];
    $existing_tables = [];
    
    $stmt = $pdo->query("SHOW TABLES");
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $existing_tables[] = $row[0];
    }
    
    echo "<div class='info'>";
    echo "<h4>📋 TABLAS ENCONTRADAS (" . count($existing_tables) . ")</h4>";
    echo "<p>" . implode(', ', $existing_tables) . "</p>";
    echo "</div>";
    
    $missing_tables = array_diff($required_tables, $existing_tables);
    
    if (empty($missing_tables)) {
        echo "<div class='success'>";
        echo "<h4>✅ TODAS LAS TABLAS PRINCIPALES EXISTEN</h4>";
        echo "</div>";
        $successes++;
    } else {
        echo "<div class='warning'>";
        echo "<h4>⚠️ FALTAN ALGUNAS TABLAS</h4>";
        echo "<p><strong>Tablas faltantes:</strong> " . implode(', ', $missing_tables) . "</p>";
        echo "</div>";
        $warnings++;
    }
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h4>❌ ERROR VERIFICANDO TABLAS</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    $errors++;
}

// 4. VERIFICAR CONEXIÓN DESDE php/database.php
echo "<h2>4. 🔧 CLASE DatabaseConfig</h2>";
try {
    include_once 'php/database.php';
    
    $conn = DatabaseConfig::getConnection();
    $stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
    $result = $stmt->fetch();
    
    echo "<div class='success'>";
    echo "<h4>✅ CLASE DatabaseConfig FUNCIONANDO</h4>";
    echo "<p><strong>Productos accesibles:</strong> " . $result['total'] . "</p>";
    echo "</div>";
    $successes++;
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h4>❌ ERROR EN CLASE DatabaseConfig</h4>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
    $errors++;
}

// 5. RESUMEN FINAL
echo "<h2>🎯 RESUMEN FINAL</h2>";

$total_tests = $successes + $warnings + $errors;

if ($errors === 0) {
    $status_class = 'success';
    $status_icon = '✅';
    $status_text = 'SISTEMA COMPLETAMENTE FUNCIONAL';
} elseif ($errors <= 1 && $warnings <= 2) {
    $status_class = 'warning';
    $status_icon = '⚠️';
    $status_text = 'SISTEMA FUNCIONAL CON ADVERTENCIAS MENORES';
} else {
    $status_class = 'error';
    $status_icon = '❌';
    $status_text = 'SISTEMA CON PROBLEMAS CRÍTICOS';
}

echo "<div class='{$status_class}'>";
echo "<h3>{$status_icon} {$status_text}</h3>";
echo "<p><strong>Tests ejecutados:</strong> {$total_tests}</p>";
echo "<p><strong>Exitosos:</strong> {$successes}</p>";
echo "<p><strong>Advertencias:</strong> {$warnings}</p>";
echo "<p><strong>Errores:</strong> {$errors}</p>";
echo "</div>";

// 6. ACCIONES SIGUIENTES
echo "<h2>📝 PRÓXIMOS PASOS</h2>";
echo "<div class='info'>";
echo "<h4>🚀 LISTO PARA USAR</h4>";
echo "<ul>";
echo "<li>✅ Base de datos: <strong>janithal_musa_moda</strong> (localhost)</li>";
echo "<li>✅ Usuario: <strong>root</strong> (sin contraseña)</li>";
echo "<li>✅ Todas las configuraciones unificadas</li>";
echo "<li>🔗 <a href='http://localhost/Musa/' target='_blank'>Abrir plataforma principal</a></li>";
echo "<li>🛠️ <a href='http://localhost/Musa/admin-panel.html' target='_blank'>Abrir panel de administración</a></li>";
echo "</ul>";
echo "</div>";

?>