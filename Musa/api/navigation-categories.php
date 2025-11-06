<?php
// api/navigation-categories.php - API para categorías de navegación

// Importar configuración global
require_once __DIR__ . '/../config/config-global.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Obtener configuración de base de datos según el entorno
$dbConfig = GlobalConfig::getDatabaseConfig();
$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

error_log("=== API NAVIGATION-CATEGORIES ===");
error_log("Entorno: " . (GlobalConfig::isProduction() ? 'PRODUCCIÓN' : 'DESARROLLO'));
error_log("Base de datos: $host / $dbname");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla de categorías si no existe
    $createTable = "
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
    $pdo->exec($createTable);
    
    // Verificar si existen categorías, si no, insertar las predeterminadas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        insertDefaultCategories($pdo);
    }
    
    // Obtener categorías por género (excluyendo unisex)
    $stmt = $pdo->prepare("
        SELECT id, name, slug, gender, sort_order 
        FROM categories 
        WHERE status = 'active' AND gender != 'unisex'
        ORDER BY gender ASC, sort_order ASC, name ASC
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar por género (excluyendo unisex)
    $categoriesByGender = [
        'mujer' => [],
        'hombre' => []
    ];
    
    foreach ($categories as $category) {
        // Solo incluir categorías de mujer y hombre, excluir unisex
        if ($category['gender'] !== 'unisex') {
            $categoriesByGender[$category['gender']][] = $category;
        }
    }
    
    echo json_encode([
        'success' => true,
        'categories' => $categoriesByGender,
        'total' => count($categories)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos',
        'error' => $e->getMessage()
    ]);
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
    }
}
?>