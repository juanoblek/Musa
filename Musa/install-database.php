<?php
/**
 * Script para crear todas las tablas en la base de datos de hosting
 * Ejecutar una sola vez después de crear la base de datos
 */

// Incluir la configuración de producción
require_once 'config/production-database.php';

echo "<h1>🗄️ Instalador de Base de Datos - M & A MODA</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
    .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; }
    .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

// CAMBIAR ESTA CONTRASEÑA POR LA REAL
$INSTALL_PASSWORD = 'musa2025'; // ← Cambiar por una contraseña segura

// Verificar contraseña de instalación
if (!isset($_GET['password']) || $_GET['password'] !== $INSTALL_PASSWORD) {
    echo "<div class='error'>❌ Acceso denegado. Usa: ?password=$INSTALL_PASSWORD</div>";
    exit;
}

try {
    echo "<div class='info'>🔌 Intentando conectar a la base de datos...</div>";
    
    $db = DatabaseProductionConfig::getConnection();
    
    echo "<div class='success'>✅ Conexión exitosa a la base de datos</div>";
    
    // SQL para crear todas las tablas
    $tables = [
        'categories' => "
            CREATE TABLE IF NOT EXISTS `categories` (
                `id` varchar(50) NOT NULL,
                `name` varchar(255) NOT NULL,
                `slug` varchar(255) NOT NULL,
                `gender` enum('hombre','mujer','unisex') DEFAULT 'unisex',
                `description` text DEFAULT NULL,
                `icon` varchar(255) DEFAULT NULL,
                `sort_order` int(11) DEFAULT 0,
                `status` enum('active','inactive') DEFAULT 'active',
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'products' => "
            CREATE TABLE IF NOT EXISTS `products` (
                `id` varchar(50) NOT NULL,
                `name` varchar(255) NOT NULL,
                `slug` varchar(255) NOT NULL,
                `description` text DEFAULT NULL,
                `short_description` varchar(500) DEFAULT NULL,
                `price` decimal(10,2) NOT NULL,
                `sale_price` decimal(10,2) DEFAULT NULL,
                `stock_quantity` int(11) DEFAULT 0,
                `min_stock` int(11) DEFAULT 5,
                `category_id` varchar(50) DEFAULT NULL,
                `gender` enum('hombre','mujer','unisex') DEFAULT 'unisex',
                `main_image` varchar(500) DEFAULT NULL,
                `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
                `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`colors`)),
                `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sizes`)),
                `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
                `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
                `discount_percentage` int(11) DEFAULT 0,
                `is_featured` tinyint(1) DEFAULT 0,
                `is_trending` tinyint(1) DEFAULT 0,
                `status` enum('active','inactive','draft') DEFAULT 'active',
                `meta_title` varchar(255) DEFAULT NULL,
                `meta_description` text DEFAULT NULL,
                `views_count` int(11) DEFAULT 0,
                `sales_count` int(11) DEFAULT 0,
                `rating_average` decimal(3,2) DEFAULT 0.00,
                `rating_count` int(11) DEFAULT 0,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`),
                KEY `category_id` (`category_id`),
                CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'admin_users' => "
            CREATE TABLE IF NOT EXISTS `admin_users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(100) NOT NULL,
                `email` varchar(255) NOT NULL,
                `password_hash` varchar(255) NOT NULL,
                `full_name` varchar(255) DEFAULT NULL,
                `role` enum('admin','manager','editor') DEFAULT 'admin',
                `status` enum('active','inactive') DEFAULT 'active',
                `last_login` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `username` (`username`),
                UNIQUE KEY `email` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'system_settings' => "
            CREATE TABLE IF NOT EXISTS `system_settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `setting_key` varchar(100) DEFAULT NULL,
                `setting_value` text DEFAULT NULL,
                `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
                `description` text DEFAULT NULL,
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                UNIQUE KEY `setting_key` (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    // Crear tablas
    foreach ($tables as $tableName => $sql) {
        try {
            $db->exec($sql);
            echo "<div class='success'>✅ Tabla '$tableName' creada exitosamente</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error creando tabla '$tableName': " . $e->getMessage() . "</div>";
        }
    }
    
    // Insertar categorías básicas
    echo "<div class='info'>📝 Insertando categorías básicas...</div>";
    
    $categories = [
        ['hombre', 'Ropa para Hombre', 'hombre', 'hombre', 'Colección completa de ropa masculina', 1],
        ['mujer', 'Ropa para Mujer', 'mujer', 'mujer', 'Colección completa de ropa femenina', 2],
        ['camisas', 'Camisas', 'camisas', 'unisex', 'Camisas elegantes y casuales', 3],
        ['pantalones', 'Pantalones', 'pantalones', 'unisex', 'Pantalones y jeans', 4],
        ['chaquetas', 'Chaquetas', 'chaquetas', 'unisex', 'Chaquetas y abrigos', 5],
        ['blazers', 'Blazers', 'blazers', 'unisex', 'Blazers elegantes', 6],
        ['accesorios', 'Accesorios', 'accesorios', 'unisex', 'Complementos y accesorios', 7]
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO categories (id, name, slug, gender, description, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($categories as $cat) {
        try {
            $stmt->execute($cat);
            echo "<div class='success'>✅ Categoría '{$cat[1]}' insertada</div>";
        } catch (Exception $e) {
            echo "<div class='warning'>⚠️ Categoría '{$cat[1]}' ya existe</div>";
        }
    }
    
    // Insertar usuario admin por defecto
    echo "<div class='info'>👤 Creando usuario administrador...</div>";
    
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT IGNORE INTO admin_users (username, email, password_hash, full_name) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute(['admin', 'admin@musaarion.com', $adminPassword, 'Administrador']);
        echo "<div class='success'>✅ Usuario admin creado - Usuario: 'admin' / Contraseña: 'admin123'</div>";
    } catch (Exception $e) {
        echo "<div class='warning'>⚠️ Usuario admin ya existe</div>";
    }
    
    // Configuraciones básicas del sistema
    echo "<div class='info'>⚙️ Insertando configuraciones del sistema...</div>";
    
    $settings = [
        ['site_name', 'M & A MODA ACTUAL', 'string', 'Nombre del sitio web'],
        ['currency', 'COP', 'string', 'Moneda del sitio'],
        ['currency_symbol', '$', 'string', 'Símbolo de la moneda'],
        ['contact_email', 'info@musaarion.com', 'string', 'Email de contacto'],
        ['contact_phone', '+57 300 123 4567', 'string', 'Teléfono de contacto']
    ];
    
    $stmt = $db->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
    
    foreach ($settings as $setting) {
        try {
            $stmt->execute($setting);
            echo "<div class='success'>✅ Configuración '{$setting[0]}' insertada</div>";
        } catch (Exception $e) {
            echo "<div class='warning'>⚠️ Configuración '{$setting[0]}' ya existe</div>";
        }
    }
    
    echo "<div class='success'>";
    echo "<h2>🎉 ¡INSTALACIÓN COMPLETADA!</h2>";
    echo "<p><strong>Base de datos configurada exitosamente</strong></p>";
    echo "<p>📊 <strong>Resumen:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Tablas creadas</li>";
    echo "<li>✅ Categorías insertadas</li>";
    echo "<li>✅ Usuario admin creado</li>";
    echo "<li>✅ Configuraciones básicas</li>";
    echo "</ul>";
    echo "<p>🔐 <strong>Datos de acceso:</strong></p>";
    echo "<ul>";
    echo "<li>Usuario: <code>admin</code></li>";
    echo "<li>Contraseña: <code>admin123</code></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div class='warning'>";
    echo "<h3>🔒 IMPORTANTE - SEGURIDAD</h3>";
    echo "<p>1. <strong>Elimina este archivo</strong> después de la instalación</p>";
    echo "<p>2. <strong>Cambia la contraseña</strong> del usuario admin</p>";
    echo "<p>3. <strong>Actualiza el archivo de configuración</strong> con tu contraseña real</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error durante la instalación: " . $e->getMessage() . "</div>";
    echo "<div class='info'>Verifica que:</div>";
    echo "<ul>";
    echo "<li>La base de datos existe</li>";
    echo "<li>El usuario tiene permisos</li>";
    echo "<li>La contraseña es correcta</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='admin-panel.html' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Ir al Panel Admin</a></p>";
?>
