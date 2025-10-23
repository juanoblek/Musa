<?php
/**
 * Script de instalación para hosting
 * MUSA MODA - musaarion.com
 * 
 * Este script facilita la configuración inicial en el servidor de hosting
 */

// Incluir configuración
require_once 'config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación MUSA MODA - Hosting Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #ddd;
        }
        
        .status-success {
            background: #d4edda;
            border-left-color: #28a745;
            color: #155724;
        }
        
        .status-warning {
            background: #fff3cd;
            border-left-color: #ffc107;
            color: #856404;
        }
        
        .status-error {
            background: #f8d7da;
            border-left-color: #dc3545;
            color: #721c24;
        }
        
        .icon {
            margin-right: 15px;
            font-size: 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
        
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
            margin: 10px 0;
        }
        
        .step-list {
            counter-reset: step-counter;
            list-style: none;
        }
        
        .step-list li {
            counter-increment: step-counter;
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            position: relative;
            padding-left: 50px;
        }
        
        .step-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 15px;
            top: 15px;
            background: #007bff;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🚀 MUSA MODA - Instalación en Hosting</h1>
            <p>Configuración automática para musaarion.com</p>
        </div>
        
        <div class="content">
            <?php
            // Verificaciones del sistema
            $checks = [];
            $warnings = [];
            $errors = [];
            
            // Verificar PHP
            if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
                $checks[] = "✅ PHP " . PHP_VERSION . " (Compatible)";
            } else {
                $errors[] = "❌ PHP " . PHP_VERSION . " (Requiere 7.4+)";
            }
            
            // Verificar extensiones PHP
            $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'curl', 'mbstring'];
            foreach ($requiredExtensions as $ext) {
                if (extension_loaded($ext)) {
                    $checks[] = "✅ Extensión $ext cargada";
                } else {
                    $errors[] = "❌ Extensión $ext no encontrada";
                }
            }
            
            // Verificar permisos de escritura
            $writableDirectories = ['uploads', 'logs', 'cache'];
            foreach ($writableDirectories as $dir) {
                $dirPath = __DIR__ . '/' . $dir;
                if (!is_dir($dirPath)) {
                    mkdir($dirPath, 0755, true);
                }
                
                if (is_writable($dirPath)) {
                    $checks[] = "✅ Directorio $dir escribible";
                } else {
                    $errors[] = "❌ Directorio $dir sin permisos de escritura";
                }
            }
            
            // Verificar configuración de base de datos
            try {
                $pdo = getDatabase();
                $checks[] = "✅ Conexión a base de datos exitosa";
                
                // Verificar tablas principales
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $requiredTables = ['products', 'categories', 'pedidos', 'envios', 'system_settings'];
                
                foreach ($requiredTables as $table) {
                    if (in_array($table, $tables)) {
                        $checks[] = "✅ Tabla $table existe";
                    } else {
                        $errors[] = "❌ Tabla $table no encontrada";
                    }
                }
                
            } catch (Exception $e) {
                $errors[] = "❌ Error de conexión a base de datos: " . $e->getMessage();
            }
            
            // Verificar entorno
            if (IS_PRODUCTION) {
                $checks[] = "✅ Entorno de producción detectado";
            } else {
                $warnings[] = "⚠️ Entorno de desarrollo detectado (localhost)";
            }
            
            // Verificar configuraciones de MercadoPago
            if (defined('MP_PUBLIC_KEY') && !empty(MP_PUBLIC_KEY)) {
                if (strpos(MP_PUBLIC_KEY, 'TEST-') === 0) {
                    $warnings[] = "⚠️ Usando credenciales de test de MercadoPago";
                } else {
                    $checks[] = "✅ Credenciales de producción de MercadoPago configuradas";
                }
            } else {
                $errors[] = "❌ Credenciales de MercadoPago no configuradas";
            }
            ?>
            
            <div class="section">
                <h2>🔍 Verificación del Sistema</h2>
                
                <?php foreach ($checks as $check): ?>
                    <div class="status-item status-success">
                        <span class="icon">✅</span>
                        <span><?= htmlspecialchars($check) ?></span>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($warnings as $warning): ?>
                    <div class="status-item status-warning">
                        <span class="icon">⚠️</span>
                        <span><?= htmlspecialchars($warning) ?></span>
                    </div>
                <?php endforeach; ?>
                
                <?php foreach ($errors as $error): ?>
                    <div class="status-item status-error">
                        <span class="icon">❌</span>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($errors)): ?>
                <div class="section">
                    <h2>🎉 ¡Instalación Completada!</h2>
                    <p>El sistema está listo para funcionar en el hosting.</p>
                    
                    <div style="margin: 20px 0;">
                        <a href="index.html" class="btn btn-success">🏠 Ir al Sitio Web</a>
                        <a href="admin-panel.html" class="btn">👨‍💼 Panel de Administración</a>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="section">
                <h2>📋 Configuraciones Manuales Restantes</h2>
                <ol class="step-list">
                    <li>
                        <strong>Configurar credenciales de MercadoPago:</strong><br>
                        Editar <code>config/config.php</code> con las credenciales de producción
                    </li>
                    <li>
                        <strong>Configurar contraseña de base de datos:</strong><br>
                        Editar <code>config/database.php</code> línea 30 con la contraseña real
                    </li>
                    <li>
                        <strong>Configurar email:</strong><br>
                        Actualizar configuraciones SMTP en <code>config/config.php</code>
                    </li>
                    <li>
                        <strong>Ejecutar script SQL:</strong><br>
                        Importar <code>config/update-hosting-settings.sql</code> en phpMyAdmin
                    </li>
                    <li>
                        <strong>Verificar SSL:</strong><br>
                        Asegurar que HTTPS esté funcionando correctamente
                    </li>
                </ol>
            </div>
            
            <div class="section">
                <h2>🛠️ Información del Sistema</h2>
                <div class="code-block">
                    <strong>Dominio:</strong> <?= $_SERVER['HTTP_HOST'] ?? 'No detectado' ?><br>
                    <strong>PHP Version:</strong> <?= PHP_VERSION ?><br>
                    <strong>Entorno:</strong> <?= ENVIRONMENT ?><br>
                    <strong>Base URL:</strong> <?= BASE_URL ?><br>
                    <strong>Zona Horaria:</strong> <?= date_default_timezone_get() ?><br>
                    <strong>Timestamp:</strong> <?= date('Y-m-d H:i:s') ?>
                </div>
            </div>
            
            <div class="section">
                <h2>🔗 Enlaces Útiles</h2>
                <ul>
                    <li><a href="https://musaarion.com" target="_blank">Sitio Web Principal</a></li>
                    <li><a href="api/productos.php" target="_blank">API de Productos</a></li>
                    <li><a href="admin-panel.html">Panel de Administración</a></li>
                    <li><a href="config/update-hosting-settings.sql" target="_blank">Script SQL</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>