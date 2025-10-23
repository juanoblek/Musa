<?php
// setup-navigation-database.php - Configuración de base de datos para navegación dinámica
header('Content-Type: text/html; charset=utf-8');

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'musa_db';
$username = 'root';
$password = '';

try {
    echo "<h2>🔧 Configurando Base de Datos para Navegación Dinámica</h2>";
    echo "<pre>";
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión a base de datos establecida\n";
    
    // 1. Crear tabla de categorías
    echo "\n📁 Creando tabla de categorías...\n";
    $createCategories = "
        CREATE TABLE IF NOT EXISTS categories (
            id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            gender ENUM('mujer', 'hombre', 'unisex') DEFAULT 'unisex',
            sort_order INT DEFAULT 0,
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_gender (gender),
            INDEX idx_status (status),
            INDEX idx_sort_order (sort_order)
        )
    ";
    $pdo->exec($createCategories);
    echo "✅ Tabla 'categories' creada/verificada\n";
    
    // 2. Verificar si existen categorías
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "\n📦 Insertando categorías predeterminadas...\n";
        insertDefaultCategories($pdo);
        echo "✅ Categorías predeterminadas insertadas\n";
    } else {
        echo "✅ Ya existen $count categorías en la base de datos\n";
    }
    
    // 3. Crear/verificar tabla de productos
    echo "\n📦 Verificando tabla de productos...\n";
    $createProducts = "
        CREATE TABLE IF NOT EXISTS products (
            id VARCHAR(50) PRIMARY KEY,
            name VARCHAR(200) NOT NULL,
            slug VARCHAR(200) NOT NULL UNIQUE,
            description TEXT,
            price DECIMAL(10,2) NOT NULL DEFAULT 0,
            sale_price DECIMAL(10,2) NULL,
            discount_percentage INT DEFAULT 0,
            stock_quantity INT DEFAULT 0,
            category_id VARCHAR(50),
            gender ENUM('mujer', 'hombre', 'unisex') DEFAULT 'unisex',
            main_image VARCHAR(500),
            gallery JSON,
            colors JSON,
            sizes JSON,
            rating DECIMAL(3,2) DEFAULT NULL,
            is_featured BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category_id),
            INDEX idx_gender (gender),
            INDEX idx_status (status),
            INDEX idx_featured (is_featured),
            INDEX idx_price (price),
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ";
    $pdo->exec($createProducts);
    echo "✅ Tabla 'products' creada/verificada\n";
    
    // 4. Verificar productos existentes
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products");
    $stmt->execute();
    $productCount = $stmt->fetchColumn();
    
    if ($productCount == 0) {
        echo "\n🎯 Insertando productos de ejemplo...\n";
        insertSampleProducts($pdo);
        echo "✅ Productos de ejemplo insertados\n";
    } else {
        echo "✅ Ya existen $productCount productos en la base de datos\n";
    }
    
    // 5. Mostrar resumen
    echo "\n📊 RESUMEN DE LA CONFIGURACIÓN:\n";
    echo "═══════════════════════════════════════\n";
    
    $stmt = $pdo->prepare("SELECT gender, COUNT(*) as count FROM categories WHERE status = 'active' GROUP BY gender");
    $stmt->execute();
    echo "📁 CATEGORÍAS POR GÉNERO:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   • " . ucfirst($row['gender']) . ": " . $row['count'] . " categorías\n";
    }
    
    $stmt = $pdo->prepare("SELECT gender, COUNT(*) as count FROM products WHERE status = 'active' GROUP BY gender");
    $stmt->execute();
    echo "\n📦 PRODUCTOS POR GÉNERO:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   • " . ucfirst($row['gender']) . ": " . $row['count'] . " productos\n";
    }
    
    echo "\n✅ ¡Configuración completada exitosamente!\n";
    echo "🌐 Puedes acceder a:\n";
    echo "   • API Categorías: api/navigation-categories.php\n";
    echo "   • API Productos: api/productos-api-v2.php\n";
    echo "   • Sitio principal: index.html\n";
    
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "<pre>";
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    echo "</pre>";
}

function insertDefaultCategories($pdo) {
    $defaultCategories = [
        // Categorías para Mujer (MUSA)
        ['id' => 'vestidos-mujer', 'name' => 'Vestidos', 'slug' => 'vestidos-mujer', 'gender' => 'mujer', 'sort_order' => 1],
        ['id' => 'blusas-mujer', 'name' => 'Blusas', 'slug' => 'blusas-mujer', 'gender' => 'mujer', 'sort_order' => 2],
        ['id' => 'camisas-mujer', 'name' => 'Camisas', 'slug' => 'camisas-mujer', 'gender' => 'mujer', 'sort_order' => 3],
        ['id' => 'blazer-mujer', 'name' => 'Blazer', 'slug' => 'blazer-mujer', 'gender' => 'mujer', 'sort_order' => 4],
        ['id' => 'chaquetas-mujer', 'name' => 'Chaquetas', 'slug' => 'chaquetas-mujer', 'gender' => 'mujer', 'sort_order' => 5],
        ['id' => 'pantalones-mujer', 'name' => 'Pantalones', 'slug' => 'pantalones-mujer', 'gender' => 'mujer', 'sort_order' => 6],
        ['id' => 'faldas-mujer', 'name' => 'Faldas', 'slug' => 'faldas-mujer', 'gender' => 'mujer', 'sort_order' => 7],
        ['id' => 'tejidos-mujer', 'name' => 'Tejidos', 'slug' => 'tejidos-mujer', 'gender' => 'mujer', 'sort_order' => 8],
        ['id' => 'bodys-mujer', 'name' => 'Bodys', 'slug' => 'bodys-mujer', 'gender' => 'mujer', 'sort_order' => 9],
        ['id' => 'accesorios-mujer', 'name' => 'Accesorios', 'slug' => 'accesorios-mujer', 'gender' => 'mujer', 'sort_order' => 10],
        
        // Categorías para Hombre (ARION)
        ['id' => 'camisas-hombre', 'name' => 'Camisas', 'slug' => 'camisas-hombre', 'gender' => 'hombre', 'sort_order' => 1],
        ['id' => 'camisetas-hombre', 'name' => 'Camisetas', 'slug' => 'camisetas-hombre', 'gender' => 'hombre', 'sort_order' => 2],
        ['id' => 'buzos-hombre', 'name' => 'Buzos', 'slug' => 'buzos-hombre', 'gender' => 'hombre', 'sort_order' => 3],
        ['id' => 'chaquetas-hombre', 'name' => 'Chaquetas', 'slug' => 'chaquetas-hombre', 'gender' => 'hombre', 'sort_order' => 4],
        ['id' => 'pantalones-hombre', 'name' => 'Pantalones', 'slug' => 'pantalones-hombre', 'gender' => 'hombre', 'sort_order' => 5],
        ['id' => 'jeans-hombre', 'name' => 'Jeans', 'slug' => 'jeans-hombre', 'gender' => 'hombre', 'sort_order' => 6],
        ['id' => 'deportiva-hombre', 'name' => 'Ropa Deportiva', 'slug' => 'deportiva-hombre', 'gender' => 'hombre', 'sort_order' => 7],
        ['id' => 'accesorios-hombre', 'name' => 'Accesorios', 'slug' => 'accesorios-hombre', 'gender' => 'hombre', 'sort_order' => 8],
        
        // Categorías Unisex
        ['id' => 'zapatillas', 'name' => 'Zapatillas', 'slug' => 'zapatillas', 'gender' => 'unisex', 'sort_order' => 1],
        ['id' => 'bolsos', 'name' => 'Bolsos', 'slug' => 'bolsos', 'gender' => 'unisex', 'sort_order' => 2],
        ['id' => 'gafas', 'name' => 'Gafas', 'slug' => 'gafas', 'gender' => 'unisex', 'sort_order' => 3]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO categories (id, name, slug, gender, sort_order, status) 
        VALUES (?, ?, ?, ?, ?, 'active')
    ");
    
    foreach ($defaultCategories as $category) {
        $stmt->execute([
            $category['id'],
            $category['name'],
            $category['slug'],
            $category['gender'],
            $category['sort_order']
        ]);
        echo "   ✓ " . $category['name'] . " (" . $category['gender'] . ")\n";
    }
}

function insertSampleProducts($pdo) {
    $sampleProducts = [
        // Productos para Mujer
        [
            'id' => 'vestido-elegante-001',
            'name' => 'Vestido Elegante Negro',
            'slug' => 'vestido-elegante-negro-001',
            'description' => 'Vestido elegante perfecto para ocasiones especiales. Confeccionado en tela de alta calidad.',
            'price' => 125000,
            'category_id' => 'vestidos-mujer',
            'gender' => 'mujer',
            'main_image' => 'images/vestido-negro-elegante.jpg',
            'colors' => '["Negro", "Azul marino"]',
            'sizes' => '["XS", "S", "M", "L", "XL"]',
            'stock_quantity' => 15,
            'is_featured' => true
        ],
        [
            'id' => 'blusa-casual-002',
            'name' => 'Blusa Casual Blanca',
            'slug' => 'blusa-casual-blanca-002',
            'description' => 'Blusa casual perfecta para el día a día. Cómoda y versátil.',
            'price' => 85000,
            'sale_price' => 68000,
            'discount_percentage' => 20,
            'category_id' => 'blusas-mujer',
            'gender' => 'mujer',
            'main_image' => 'images/blusa-blanca-casual.jpg',
            'colors' => '["Blanco", "Rosa", "Celeste"]',
            'sizes' => '["S", "M", "L", "XL"]',
            'stock_quantity' => 20
        ],
        [
            'id' => 'pantalon-formal-003',
            'name' => 'Pantalón Formal Gris',
            'slug' => 'pantalon-formal-gris-003',
            'description' => 'Pantalón formal de corte recto, ideal para la oficina.',
            'price' => 95000,
            'category_id' => 'pantalones-mujer',
            'gender' => 'mujer',
            'main_image' => 'images/pantalon-gris-formal.jpg',
            'colors' => '["Gris", "Negro", "Azul marino"]',
            'sizes' => '["XS", "S", "M", "L", "XL"]',
            'stock_quantity' => 12
        ],
        
        // Productos para Hombre
        [
            'id' => 'camisa-formal-004',
            'name' => 'Camisa Formal Blanca',
            'slug' => 'camisa-formal-blanca-004',
            'description' => 'Camisa formal clásica, perfecta para el trabajo y eventos especiales.',
            'price' => 75000,
            'category_id' => 'camisas-hombre',
            'gender' => 'hombre',
            'main_image' => 'images/camisa-blanca-formal.jpg',
            'colors' => '["Blanco", "Celeste", "Rosa claro"]',
            'sizes' => '["S", "M", "L", "XL", "XXL"]',
            'stock_quantity' => 18,
            'is_featured' => true
        ],
        [
            'id' => 'buzo-deportivo-005',
            'name' => 'Buzo Deportivo Negro',
            'slug' => 'buzo-deportivo-negro-005',
            'description' => 'Buzo deportivo cómodo, ideal para ejercitarse o uso casual.',
            'price' => 65000,
            'sale_price' => 52000,
            'discount_percentage' => 20,
            'category_id' => 'buzos-hombre',
            'gender' => 'hombre',
            'main_image' => 'images/buzo-negro-deportivo.jpg',
            'colors' => '["Negro", "Gris", "Azul marino"]',
            'sizes' => '["M", "L", "XL", "XXL"]',
            'stock_quantity' => 25
        ],
        [
            'id' => 'jean-clasico-006',
            'name' => 'Jean Clásico Azul',
            'slug' => 'jean-clasico-azul-006',
            'description' => 'Jean clásico de corte recto, versátil y duradero.',
            'price' => 89000,
            'category_id' => 'jeans-hombre',
            'gender' => 'hombre',
            'main_image' => 'images/jean-azul-clasico.jpg',
            'colors' => '["Azul clásico", "Azul oscuro", "Negro"]',
            'sizes' => '["30", "32", "34", "36", "38"]',
            'stock_quantity' => 22
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO products (id, name, slug, description, price, sale_price, discount_percentage, 
                             category_id, gender, main_image, colors, sizes, stock_quantity, is_featured, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");
    
    foreach ($sampleProducts as $product) {
        $stmt->execute([
            $product['id'],
            $product['name'],
            $product['slug'],
            $product['description'],
            $product['price'],
            $product['sale_price'] ?? null,
            $product['discount_percentage'] ?? 0,
            $product['category_id'],
            $product['gender'],
            $product['main_image'],
            $product['colors'],
            $product['sizes'],
            $product['stock_quantity'],
            $product['is_featured'] ?? false
        ]);
        echo "   ✓ " . $product['name'] . " (" . $product['gender'] . ")\n";
    }
}
?>