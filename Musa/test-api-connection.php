<?php
/**
 * TEST DE CONEXIÓN API - DIAGNÓSTICO DE PRODUCTOS Y CATEGORÍAS
 * Este archivo prueba las conexiones a la base de datos y APIs
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test de Conexión API - MUSA</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        h2 {
            color: #764ba2;
            margin-top: 30px;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Test de Conexión API - MUSA MODA</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<p><strong>Servidor:</strong> " . $_SERVER['HTTP_HOST'] . "</p>";

// 1. TEST DE CONFIGURACIÓN GLOBAL
echo "<h2>1️⃣ Configuración Global</h2>";

require_once __DIR__ . '/config/config-global.php';

try {
    $dbConfig = GlobalConfig::getDatabaseConfig();
    $isProduction = GlobalConfig::isProduction();
    
    echo "<div class='info'>";
    echo "<strong>✅ Configuración cargada correctamente</strong><br>";
    echo "<strong>Entorno:</strong> " . ($isProduction ? "🌐 PRODUCCIÓN (Hosting)" : "💻 DESARROLLO (Localhost)") . "<br>";
    echo "<strong>Host:</strong> <code>{$dbConfig['host']}</code><br>";
    echo "<strong>Base de datos:</strong> <code>{$dbConfig['dbname']}</code><br>";
    echo "<strong>Usuario:</strong> <code>{$dbConfig['username']}</code><br>";
    echo "<strong>Charset:</strong> <code>{$dbConfig['charset']}</code>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error en configuración:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

// 2. TEST DE CONEXIÓN A BASE DE DATOS
echo "<h2>2️⃣ Conexión a Base de Datos</h2>";

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "<div class='success'>";
    echo "<strong>✅ Conexión exitosa a la base de datos</strong><br>";
    echo "Base de datos operativa";
    echo "</div>";
    
    // Verificar tablas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<div class='info'>";
    echo "<strong>📋 Tablas encontradas:</strong> " . count($tables) . "<br>";
    echo implode(', ', array_map(function($t) { return "<code>$t</code>"; }, $tables));
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error de conexión:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
    echo "</div></body></html>";
    exit;
}

// 3. TEST DE PRODUCTOS
echo "<h2>3️⃣ Test de Productos</h2>";

try {
    // Verificar tabla products
    $countProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();
    
    echo "<div class='info'>";
    echo "<strong>📦 Total de productos:</strong> $countProducts<br>";
    echo "<strong>✅ Productos activos:</strong> $activeProducts";
    echo "</div>";
    
    if ($activeProducts > 0) {
        // Mostrar algunos productos
        $products = $pdo->query("SELECT id, name, price, status, created_at FROM products WHERE status = 'active' LIMIT 10")->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Estado</th><th>Creado</th></tr>";
        foreach ($products as $product) {
            echo "<tr>";
            echo "<td>{$product['id']}</td>";
            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
            echo "<td>\${$product['price']}</td>";
            echo "<td><span class='badge badge-success'>Activo</span></td>";
            echo "<td>" . date('Y-m-d', strtotime($product['created_at'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>";
        echo "⚠️ No hay productos activos en la base de datos";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error al consultar productos:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

// 4. TEST DE CATEGORÍAS
echo "<h2>4️⃣ Test de Categorías</h2>";

try {
    $countCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $activeCategories = $pdo->query("SELECT COUNT(*) FROM categories WHERE status = 'active'")->fetchColumn();
    
    echo "<div class='info'>";
    echo "<strong>📂 Total de categorías:</strong> $countCategories<br>";
    echo "<strong>✅ Categorías activas:</strong> $activeCategories";
    echo "</div>";
    
    if ($activeCategories > 0) {
        $categories = $pdo->query("SELECT id, name, slug, gender, status FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Slug</th><th>Género</th><th>Estado</th></tr>";
        foreach ($categories as $category) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($category['id']) . "</td>";
            echo "<td>" . htmlspecialchars($category['name']) . "</td>";
            echo "<td><code>" . htmlspecialchars($category['slug']) . "</code></td>";
            echo "<td>" . htmlspecialchars($category['gender']) . "</td>";
            echo "<td><span class='badge badge-success'>Activo</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>";
        echo "⚠️ No hay categorías activas en la base de datos";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<strong>❌ Error al consultar categorías:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

// 5. TEST DE APIs
echo "<h2>5️⃣ Test de APIs (Endpoints)</h2>";

$domain = GlobalConfig::getDomain();
$apis = [
    'Productos V2' => $domain . '/api/productos-v2.php',
    'Productos' => $domain . '/api/productos.php',
    'Categorías' => $domain . '/api/categorias.php',
    'Navegación Categorías' => $domain . '/api/navigation-categories.php'
];

echo "<table>";
echo "<tr><th>API</th><th>URL</th><th>Acciones</th></tr>";
foreach ($apis as $name => $url) {
    echo "<tr>";
    echo "<td><strong>$name</strong></td>";
    echo "<td><code>" . htmlspecialchars($url) . "</code></td>";
    echo "<td><a href='$url' target='_blank' class='btn'>Probar</a></td>";
    echo "</tr>";
}
echo "</table>";

// 6. RESUMEN Y RECOMENDACIONES
echo "<h2>6️⃣ Resumen y Recomendaciones</h2>";

$allOk = $countProducts > 0 && $countCategories > 0;

if ($allOk) {
    echo "<div class='success'>";
    echo "<strong>✅ TODO ESTÁ FUNCIONANDO CORRECTAMENTE</strong><br>";
    echo "• Base de datos conectada<br>";
    echo "• Productos encontrados: $countProducts ($activeProducts activos)<br>";
    echo "• Categorías encontradas: $countCategories ($activeCategories activas)<br>";
    echo "<br><strong>🎉 Tu e-commerce está listo para funcionar!</strong>";
    echo "</div>";
} else {
    echo "<div class='warning'>";
    echo "<strong>⚠️ CONFIGURACIÓN INCOMPLETA</strong><br>";
    if ($countProducts == 0) {
        echo "• No hay productos en la base de datos. Debes agregar productos.<br>";
    }
    if ($countCategories == 0) {
        echo "• No hay categorías en la base de datos. Debes agregar categorías.<br>";
    }
    echo "</div>";
}

echo "<h2>🔗 Enlaces Útiles</h2>";
echo "<a href='index.php' class='btn'>🏠 Ir al Inicio</a>";
echo "<a href='admin-panel.php' class='btn'>⚙️ Panel de Administración</a>";
echo "<a href='test-api-connection.php' class='btn'>🔄 Recargar Test</a>";

echo "</div></body></html>";
?>
