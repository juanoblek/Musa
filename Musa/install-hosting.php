<?php
/**
 * 🚀 INSTALADOR AUTOMÁTICO PARA HOSTING - MUSA MODA
 * ================================================
 * Este script configura automáticamente la plataforma en hosting
 * Ejecutar una sola vez después de subir archivos
 */

header('Content-Type: text/html; charset=UTF-8');

// Verificar si ya fue ejecutado
$lock_file = __DIR__ . '/install.lock';
if (file_exists($lock_file)) {
    die('
    <div style="background: #f8d7da; padding: 20px; border-radius: 8px; color: #721c24; font-family: Arial;">
        <h2>⚠️ Instalación ya completada</h2>
        <p>Este script ya fue ejecutado. Si necesitas reinstalar, elimina el archivo <code>install.lock</code> y vuelve a ejecutar.</p>
        <p><a href="index.html" style="color: #0066cc;">🏠 Ir a la tienda</a> | <a href="admin-panel.html" style="color: #0066cc;">🔧 Panel Admin</a></p>
    </div>
    ');
}

echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Instalador MUSA MODA</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; color: #155724; margin: 10px 0; }
        .error { background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24; margin: 10px 0; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404; margin: 10px 0; }
        .step { border-left: 4px solid #007bff; padding-left: 15px; margin: 20px 0; }
        code { background: #f1f1f1; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; margin: 10px 5px; }
        .button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalador Automático - MUSA MODA</h1>
        <p>Este script configurará automáticamente tu plataforma de moda en el hosting.</p>
';

$errors = [];
$warnings = [];
$success = [];

// PASO 1: Verificar configuración global
echo '<div class="step"><h3>📋 PASO 1: Verificando configuración</h3>';

try {
    require_once 'config/config-global.php';
    
    if (GlobalConfig::isProduction()) {
        $success[] = "✅ Entorno de PRODUCCIÓN detectado correctamente";
        echo '<div class="success">🌍 Ejecutándose en HOSTING DE PRODUCCIÓN</div>';
    } else {
        $warnings[] = "⚠️ Entorno de desarrollo detectado (localhost)";
        echo '<div class="warning">🏠 Ejecutándose en LOCALHOST - Esto es normal para pruebas</div>';
    }
    
    $domain = GlobalConfig::getDomain();
    echo "<div class=\"success\">🌐 Dominio detectado: <strong>$domain</strong></div>";
    
} catch (Exception $e) {
    $errors[] = "❌ Error cargando configuración global: " . $e->getMessage();
    echo '<div class="error">❌ Error en configuración global</div>';
}

echo '</div>';

// PASO 2: Verificar base de datos
echo '<div class="step"><h3>🗄️ PASO 2: Verificando base de datos</h3>';

try {
    require_once 'config/database.php';
    $connection = DatabaseConfig::getConnection();
    
    $success[] = "✅ Conexión a base de datos exitosa";
    echo '<div class="success">✅ Conexión a base de datos establecida</div>';
    
    $dbConfig = GlobalConfig::getDatabaseConfig();
    echo "<div class=\"success\">📊 Base de datos: <strong>{$dbConfig['dbname']}</strong></div>";
    
} catch (Exception $e) {
    $errors[] = "❌ Error conectando a base de datos: " . $e->getMessage();
    echo '<div class="error">❌ Error de conexión a base de datos</div>';
    echo '<div class="error">Verifica las credenciales en config/config-global.php</div>';
}

echo '</div>';

// PASO 3: Crear tablas si no existen
echo '<div class="step"><h3>🔨 PASO 3: Creando/verificando tablas</h3>';

if (isset($connection)) {
    try {
        // Configurar PDO para usar consultas bufferizadas
        $connection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        
        // Ejecutar estructura de base de datos
        $sqlFile = __DIR__ . '/ESTRUCTURA-COMPLETA-DB.sql';
        
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            $statements = explode(';', $sql);
            $created = 0;
            
            foreach ($statements as $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        // Asegurarse de que cualquier consulta previa esté completada
                        while ($connection->getAttribute(PDO::ATTR_SERVER_INFO)) {
                            $connection->getAttribute(PDO::ATTR_SERVER_INFO);
                        }
                        
                        // Preparar y ejecutar la consulta
                        $stmt = $connection->prepare($statement);
                        $stmt->execute();
                        $created++;
                        
                        // Asegurarse de que los resultados sean leídos
                        while ($stmt->fetch(PDO::FETCH_ASSOC));
                        $stmt->closeCursor();
                    } catch (PDOException $e) {
                        // Ignorar errores de tablas existentes
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            throw $e;
                        }
                    }
                }
            }
            
            $success[] = "✅ Estructura de base de datos verificada/creada";
            echo "<div class=\"success\">✅ Estructura de base de datos actualizada ($created declaraciones ejecutadas)</div>";
            
        } else {
            $warnings[] = "⚠️ Archivo ESTRUCTURA-COMPLETA-DB.sql no encontrado";
            echo '<div class="warning">⚠️ Archivo SQL no encontrado - continuando sin crear tablas</div>';
        }
        
    } catch (Exception $e) {
        $errors[] = "❌ Error creando tablas: " . $e->getMessage();
        echo '<div class="error">❌ Error ejecutando estructura de base de datos</div>';
    }
}

echo '</div>';

// PASO 4: Verificar Mercado Pago
echo '<div class="step"><h3>💳 PASO 4: Verificando Mercado Pago</h3>';

try {
    $mpConfig = GlobalConfig::getMercadoPagoConfig();
    $urls = GlobalConfig::getMercadoPagoUrls();
    
    if (GlobalConfig::isProduction()) {
        if (strpos($mpConfig['public_key'], 'APP_USR-') === 0) {
            $success[] = "✅ Credenciales de PRODUCCIÓN configuradas";
            echo '<div class="success">✅ Credenciales de PRODUCCIÓN de Mercado Pago configuradas</div>';
        } else {
            $errors[] = "❌ Credenciales de producción incorrectas";
            echo '<div class="error">❌ Credenciales de producción no válidas</div>';
        }
    } else {
        echo '<div class="warning">⚠️ Usando credenciales de TEST (desarrollo)</div>';
    }
    
    echo "<div class=\"success\">🔗 URLs configuradas para: <strong>{$urls['success']}</strong></div>";
    
} catch (Exception $e) {
    $errors[] = "❌ Error configuración Mercado Pago: " . $e->getMessage();
    echo '<div class="error">❌ Error en configuración de Mercado Pago</div>';
}

echo '</div>';

// PASO 5: Verificar archivos críticos
echo '<div class="step"><h3>📁 PASO 5: Verificando archivos críticos</h3>';

$critical_files = [
    'index.html' => 'Página principal',
    'admin-panel.html' => 'Panel administrativo',
    'pago-premium.html' => 'Página de pagos',
    'api/productos.php' => 'API de productos',
    'api/categorias.php' => 'API de categorías',
    'config/config-global.php' => 'Configuración global',
    '.htaccess' => 'Configuración del servidor'
];

$missing_files = [];
foreach ($critical_files as $file => $desc) {
    if (file_exists($file)) {
        echo "<div class=\"success\">✅ $desc: <code>$file</code></div>";
    } else {
        $missing_files[] = "$desc ($file)";
        echo "<div class=\"error\">❌ Falta: <code>$file</code> - $desc</div>";
    }
}

if (empty($missing_files)) {
    $success[] = "✅ Todos los archivos críticos encontrados";
} else {
    $errors[] = "❌ Archivos faltantes: " . implode(', ', $missing_files);
}

echo '</div>';

// PASO 6: Finalizar instalación
echo '<div class="step"><h3>🎯 PASO 6: Finalizando instalación</h3>';

if (empty($errors)) {
    // Crear archivo de bloqueo
    file_put_contents($lock_file, date('Y-m-d H:i:s') . " - Instalación completada\n");
    
    echo '<div class="success">🎉 ¡INSTALACIÓN COMPLETADA EXITOSAMENTE!</div>';
    echo '<div class="success">📝 Se creó el archivo install.lock para prevenir ejecuciones futuras</div>';
    
    $success[] = "✅ Instalación completada exitosamente";
    
    // Mostrar enlaces importantes
    echo '<h3>🔗 Enlaces importantes:</h3>';
    echo '<a href="index.html" class="button">🏠 Ver Tienda</a>';
    echo '<a href="admin-panel.html" class="button">🔧 Panel Admin</a>';
    echo '<a href="pago-premium.html" class="button">💳 Página de Pagos</a>';
    
} else {
    echo '<div class="error">❌ Instalación incompleta - corrige los errores y vuelve a ejecutar</div>';
}

echo '</div>';

// RESUMEN FINAL
echo '<div class="step"><h3>📊 RESUMEN FINAL</h3>';

if (!empty($success)) {
    echo '<h4 style="color: green;">✅ Éxitos:</h4><ul>';
    foreach ($success as $item) {
        echo "<li>$item</li>";
    }
    echo '</ul>';
}

if (!empty($warnings)) {
    echo '<h4 style="color: orange;">⚠️ Advertencias:</h4><ul>';
    foreach ($warnings as $item) {
        echo "<li>$item</li>";
    }
    echo '</ul>';
}

if (!empty($errors)) {
    echo '<h4 style="color: red;">❌ Errores:</h4><ul>';
    foreach ($errors as $item) {
        echo "<li>$item</li>";
    }
    echo '</ul>';
} else {
    echo '<div class="success"><h3>🚀 ¡PLATAFORMA LISTA PARA USO!</h3></div>';
}

echo '</div>';

echo '
    </div>
</body>
</html>';
?>