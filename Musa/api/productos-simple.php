<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $db = DatabaseConfig::getConnection();
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Log de la petición para debug
    error_log("API Request: $method " . $_SERVER['REQUEST_URI']);
    
    switch ($method) {
        case 'GET':
            handleGet($db);
            break;
        case 'POST':
            handlePost($db);
            break;
        case 'PUT':
            handlePut($db);
            break;
        case 'DELETE':
            handleDelete($db);
            break;
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método no permitido: ' . $method]);
    }
} catch (Exception $e) {
    error_log("Error crítico en API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor', 'debug' => $e->getMessage()]);
}

function handleGet($db) {
    try {
        // Estadísticas
        if (isset($_GET['stats'])) {
            $stats = [
                'total_products' => getCount($db, "SELECT COUNT(*) FROM products"),
                'active_products' => getCount($db, "SELECT COUNT(*) FROM products WHERE status = 'active'"),
                'featured_products' => getCount($db, "SELECT COUNT(*) FROM products WHERE is_featured = 1"),
                'low_stock' => getCount($db, "SELECT COUNT(*) FROM products WHERE stock_quantity <= 5")
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $stats,
                'timestamp' => date('c')
            ]);
            return;
        }
        
        // Producto individual
        if (isset($_GET['id'])) {
            $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                                FROM products p 
                                LEFT JOIN product_categories c ON p.category_id = c.id 
                                WHERE p.id = :id");
            $stmt->bindParam(':id', $_GET['id']);
            $stmt->execute();
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                echo json_encode([
                    'success' => true,
                    'data' => formatProduct($product),
                    'timestamp' => date('c')
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            }
            return;
        }
        
        // Listar productos (comportamiento por defecto)
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;
        
        // Validar que page y limit sean positivos
        if ($page < 1) $page = 1;
        if ($limit < 1) $limit = 20;
        if ($limit > 100) $limit = 100; // Máximo 100 por página
        
        $where = [];
        $params = [];
        
        if (!empty($_GET['category'])) {
            $where[] = "p.category_id = :category";
            $params['category'] = $_GET['category'];
        }
        
        if (!empty($_GET['gender'])) {
            $where[] = "p.gender = :gender";
            $params['gender'] = $_GET['gender'];
        }
        
        if (!empty($_GET['status'])) {
            $where[] = "p.status = :status";
            $params['status'] = $_GET['status'];
        } else {
            // Por defecto solo mostrar productos activos
            $where[] = "p.status = 'active'";
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Total count
        $countSql = "SELECT COUNT(*) FROM products p $whereClause";
        $countStmt = $db->prepare($countSql);
        foreach ($params as $key => $value) {
            $countStmt->bindValue(":$key", $value);
        }
        $countStmt->execute();
        $total = intval($countStmt->fetchColumn());
        
        // Products
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN product_categories c ON p.category_id = c.id 
                $whereClause 
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = formatProduct($row);
        }
        
        echo json_encode([
            'success' => true,
            'data' => [
                'products' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $total,
                    'last_page' => max(1, ceil($total / $limit))
                ]
            ],
            'timestamp' => date('c')
        ]);
        
    } catch (Exception $e) {
        error_log("Error en handleGet: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
}

function handlePost($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }
        
        // Validar campos requeridos
        if (empty($input['name']) || empty($input['price']) || empty($input['category_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Campos requeridos: name, price, category_id']);
            return;
        }
        
        $id = 'prod-' . uniqid();
        $slug = generateSlug($input['name']);
        
        $sql = "INSERT INTO products (id, name, slug, description, price, sale_price, stock_quantity, 
                                    category_id, gender, main_image, gallery, colors, sizes, 
                                    is_featured, status, discount_percentage, created_at, updated_at) 
                VALUES (:id, :name, :slug, :description, :price, :sale_price, :stock_quantity, 
                       :category_id, :gender, :main_image, :gallery, :colors, :sizes, 
                       :is_featured, :status, :discount_percentage, NOW(), NOW())";
        
        $stmt = $db->prepare($sql);
        
        $discount = 0;
        if (!empty($input['sale_price']) && $input['price'] > 0) {
            $discount = round((1 - $input['sale_price'] / $input['price']) * 100);
        }
        
        $stmt->execute([
            ':id' => $id,
            ':name' => $input['name'],
            ':slug' => $slug,
            ':description' => $input['description'] ?? '',
            ':price' => floatval($input['price']),
            ':sale_price' => !empty($input['sale_price']) ? floatval($input['sale_price']) : null,
            ':stock_quantity' => intval($input['stock_quantity'] ?? 0),
            ':category_id' => $input['category_id'],
            ':gender' => $input['gender'] ?? 'unisex',
            ':main_image' => $input['main_image'] ?? null,
            ':gallery' => !empty($input['gallery']) ? json_encode($input['gallery']) : null,
            ':colors' => !empty($input['colors']) ? json_encode($input['colors']) : null,
            ':sizes' => !empty($input['sizes']) ? json_encode($input['sizes']) : null,
            ':is_featured' => !empty($input['is_featured']) ? 1 : 0,
            ':status' => $input['status'] ?? 'active',
            ':discount_percentage' => $discount
        ]);
        
        // Obtener el producto creado
        $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                            FROM products p 
                            LEFT JOIN product_categories c ON p.category_id = c.id 
                            WHERE p.id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'data' => formatProduct($product),
            'timestamp' => date('c')
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error creando producto: ' . $e->getMessage()]);
    }
}

function handlePut($db) {
    try {
        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requerido para actualizar']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }
        
        $id = $_GET['id'];
        
        $sql = "UPDATE products SET name = :name, description = :description, price = :price, 
                sale_price = :sale_price, stock_quantity = :stock_quantity, category_id = :category_id, 
                gender = :gender, main_image = :main_image, gallery = :gallery, colors = :colors, 
                sizes = :sizes, is_featured = :is_featured, status = :status, 
                discount_percentage = :discount_percentage, updated_at = NOW() 
                WHERE id = :id";
        
        $stmt = $db->prepare($sql);
        
        $discount = 0;
        if (!empty($input['sale_price']) && $input['price'] > 0) {
            $discount = round((1 - $input['sale_price'] / $input['price']) * 100);
        }
        
        $result = $stmt->execute([
            ':id' => $id,
            ':name' => $input['name'],
            ':description' => $input['description'] ?? '',
            ':price' => floatval($input['price']),
            ':sale_price' => !empty($input['sale_price']) ? floatval($input['sale_price']) : null,
            ':stock_quantity' => intval($input['stock_quantity'] ?? 0),
            ':category_id' => $input['category_id'],
            ':gender' => $input['gender'] ?? 'unisex',
            ':main_image' => $input['main_image'] ?? null,
            ':gallery' => !empty($input['gallery']) ? json_encode($input['gallery']) : null,
            ':colors' => !empty($input['colors']) ? json_encode($input['colors']) : null,
            ':sizes' => !empty($input['sizes']) ? json_encode($input['sizes']) : null,
            ':is_featured' => !empty($input['is_featured']) ? 1 : 0,
            ':status' => $input['status'] ?? 'active',
            ':discount_percentage' => $discount
        ]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto actualizado exitosamente',
                'timestamp' => date('c')
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error actualizando producto']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function handleDelete($db) {
    try {
        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID requerido para eliminar']);
            return;
        }
        
        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $result = $stmt->execute([':id' => $_GET['id']]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
                'timestamp' => date('c')
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}

function formatProduct($product) {
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'description' => $product['description'],
        'short_description' => $product['short_description'],
        'price' => floatval($product['price']),
        'sale_price' => $product['sale_price'] ? floatval($product['sale_price']) : null,
        'stock_quantity' => intval($product['stock_quantity']),
        'category_id' => $product['category_id'],
        'category_name' => $product['category_name'] ?? '',
        'gender' => $product['gender'],
        'main_image' => $product['main_image'],
        'gallery' => $product['gallery'] ? json_decode($product['gallery'], true) : [],
        'colors' => $product['colors'] ? json_decode($product['colors'], true) : [],
        'sizes' => $product['sizes'] ? json_decode($product['sizes'], true) : [],
        'discount_percentage' => intval($product['discount_percentage']),
        'is_featured' => intval($product['is_featured']) === 1,
        'status' => $product['status'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at']
    ];
}

function generateSlug($name) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
}

function getCount($db, $sql) {
    return intval($db->query($sql)->fetchColumn());
}
?>
