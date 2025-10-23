<?php
// install-db-simple.php - Instalación simple de la base de datos
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Instalación Simple de Base de Datos</h2>";

try {
    // Conectar a MySQL (sin base de datos específica)
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Conexión a MySQL establecida</p>";
    
    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS musa_moda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Base de datos 'musa_moda' creada</p>";
    
    // Seleccionar base de datos
    $pdo->exec("USE musa_moda");
    echo "<p>✅ Base de datos seleccionada</p>";
    
    // Desactivar verificación de claves foráneas
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Eliminar tablas existentes en orden inverso
    $tables_to_drop = [
        'order_items', 'orders', 'shopping_cart', 'product_reviews', 
        'product_images', 'customer_addresses', 'customers', 'coupons',
        'shipping_methods', 'payment_methods', 'newsletter_subscribers',
        'contact_messages', 'admin_sessions', 'activity_logs', 'products',
        'categories', 'admin_users', 'system_settings'
    ];
    
    foreach ($tables_to_drop as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
    }
    echo "<p>✅ Tablas anteriores eliminadas</p>";
    
    // Crear tabla categories
    $pdo->exec("
        CREATE TABLE `categories` (
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
    ");
    echo "<p>✅ Tabla 'categories' creada</p>";
    
    // Insertar categorías
    $categories = [
        ['hombre', 'Ropa para Hombre', 'hombre', 'hombre', 'Colección completa de ropa masculina', 1],
        ['mujer', 'Ropa para Mujer', 'mujer', 'mujer', 'Colección completa de ropa femenina', 2],
        ['camisas', 'Camisas', 'camisas', 'unisex', 'Camisas elegantes y casuales', 3],
        ['pantalones', 'Pantalones', 'pantalones', 'unisex', 'Pantalones y jeans', 4],
        ['chaquetas', 'Chaquetas', 'chaquetas', 'unisex', 'Chaquetas y abrigos', 5],
        ['blazers', 'Blazers', 'blazers', 'unisex', 'Blazers elegantes', 6],
        ['accesorios', 'Accesorios', 'accesorios', 'unisex', 'Complementos y accesorios', 7]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO `categories` (`id`, `name`, `slug`, `gender`, `description`, `sort_order`, `status`) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    echo "<p>✅ Categorías insertadas (" . count($categories) . ")</p>";
    
    // Crear tabla products
    $pdo->exec("
        CREATE TABLE `products` (
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
            `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
            `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
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
    ");
    echo "<p>✅ Tabla 'products' creada</p>";
    
    // Insertar productos de ejemplo
    $products = [
        [
            'camisa-elegante-001',
            'Camisa Elegante Clásica',
            'camisa-elegante-clasica',
            'Camisa elegante de corte clásico, perfecta para ocasiones formales y casuales elegantes.',
            'Camisa elegante de corte clásico',
            85000.00,
            'camisas',
            'unisex',
            25,
            1,
            'active'
        ],
        [
            'pantalon-formal-001',
            'Pantalón Formal Premium',
            'pantalon-formal-premium',
            'Pantalón formal de alta calidad, confeccionado con telas premium para un look profesional.',
            'Pantalón formal de alta calidad',
            120000.00,
            'pantalones',
            'unisex',
            20,
            1,
            'active'
        ],
        [
            'blazer-ejecutivo-001',
            'Blazer Ejecutivo',
            'blazer-ejecutivo',
            'Blazer ejecutivo de corte moderno, ideal para reuniones de negocios y eventos formales.',
            'Blazer ejecutivo de corte moderno',
            180000.00,
            'blazers',
            'unisex',
            15,
            1,
            'active'
        ],
        [
            'chaqueta-cuero-001',
            'Chaqueta de Cuero Premium',
            'chaqueta-cuero-premium',
            'Chaqueta de cuero genuino, perfecta para looks casuales y elegantes.',
            'Chaqueta de cuero genuino premium',
            350000.00,
            'chaquetas',
            'unisex',
            12,
            1,
            'active'
        ],
        [
            'vestido-elegante-001',
            'Vestido Elegante de Noche',
            'vestido-elegante-noche',
            'Vestido elegante perfecto para ocasiones especiales y eventos nocturnos.',
            'Vestido elegante para ocasiones especiales',
            280000.00,
            'mujer',
            'mujer',
            8,
            1,
            'active'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `price`, `category_id`, `gender`, `stock_quantity`, `is_featured`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $stmt->execute($product);
    }
    echo "<p>✅ Productos insertados (" . count($products) . ")</p>";
    
    // Crear tabla admin_users
    $pdo->exec("
        CREATE TABLE `admin_users` (
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
    ");
    echo "<p>✅ Tabla 'admin_users' creada</p>";
    
    // Insertar usuario administrador (contraseña: admin123)
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `full_name`, `role`, `status`) VALUES (?, ?, ?, ?, ?, ?)")
        ->execute(['admin', 'admin@musaarion.com', $password_hash, 'Administrador M&A', 'admin', 'active']);
    echo "<p>✅ Usuario administrador creado</p>";
    
    // Reactivar verificación de claves foráneas
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>🎉 ¡Instalación Completada!</h3>";
    echo "<p><strong>Base de datos configurada correctamente:</strong></p>";
    echo "<ul>";
    echo "<li>✅ " . count($categories) . " categorías creadas</li>";
    echo "<li>✅ " . count($products) . " productos de ejemplo</li>";
    echo "<li>✅ Usuario admin configurado</li>";
    echo "</ul>";
    echo "<p><strong>Credenciales de acceso:</strong></p>";
    echo "<ul>";
    echo "<li>Usuario: <code>admin</code></li>";
    echo "<li>Contraseña: <code>admin123</code></li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<p><a href='admin-panel.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Ir al Panel Administrativo</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 15px 0; border-radius: 5px;'>";
    echo "<h3>❌ Error de Base de Datos</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
