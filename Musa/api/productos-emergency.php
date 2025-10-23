<?php
/**
 * API simplificada para productos - EMERGENCY FIX
 * Funciona independientemente de la configuración de BD
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Intentar múltiples configuraciones de BD
function getWorkingConnection() {
    $configs = [
        ['host' => 'localhost', 'dbname' => 'janithal_musa_moda', 'user' => 'janithal_usuario_musaarion_db', 'pass' => 'Chiguiro553021'],
    ];
    
    foreach ($configs as $config) {
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Verificar que la tabla products existe
            $stmt = $pdo->query("SHOW TABLES LIKE 'products'");
            if ($stmt->rowCount() > 0) {
                return $pdo;
            }
            
        } catch (Exception $e) {
            continue;
        }
    }
    
    return null;
}

try {
    $pdo = getWorkingConnection();
    
    if (!$pdo) {
        throw new Exception("No se pudo conectar a ninguna base de datos");
    }
    
    // Obtener productos
    $sql = "SELECT 
                id,
                name,
                slug,
                description,
                price,
                sale_price,
                stock_quantity,
                category_id,
                gender,
                main_image,
                colors,
                sizes,
                is_featured,
                status,
                created_at
            FROM products 
            WHERE status = 'active'
            ORDER BY created_at DESC
            LIMIT 50";
    
    $stmt = $pdo->query($sql);
    $products = $stmt->fetchAll();
    
    // Procesar los productos
    foreach ($products as &$product) {
        // Decodificar JSON si es necesario
        if (is_string($product['colors'])) {
            $product['colors'] = json_decode($product['colors'], true) ?: [];
        }
        if (is_string($product['sizes'])) {
            $product['sizes'] = json_decode($product['sizes'], true) ?: [];
        }
        
        // Asegurar tipos correctos
        $product['price'] = (float) $product['price'];
        $product['sale_price'] = $product['sale_price'] ? (float) $product['sale_price'] : null;
        $product['stock_quantity'] = (int) $product['stock_quantity'];
        $product['is_featured'] = (bool) $product['is_featured'];
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total' => count($products),
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'products' => [],
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE);
}
?>