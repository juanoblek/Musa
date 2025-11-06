
<?php
// productos-v2.php - API de productos versión 2 (sin caché)

// Importar configuración global
require_once __DIR__ . '/../config/config-global.php';

// Obtener configuración de base de datos según el entorno
$dbConfig = GlobalConfig::getDatabaseConfig();
$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];
$charset = $dbConfig['charset'];

// Headers anti-caché
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log de debug
error_log("=== API V2 REQUEST ===");
error_log("Entorno: " . (GlobalConfig::isProduction() ? 'PRODUCCIÓN' : 'DESARROLLO'));
error_log("Base de datos: $host / $dbname");
error_log("Method: " . $_SERVER['REQUEST_METHOD']);
error_log("URI: " . $_SERVER['REQUEST_URI']);
error_log("Query: " . json_encode($_GET));

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    $method = $_SERVER['REQUEST_METHOD'];
    
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
            sendError("Método no permitido: $method", 405);
    }
} catch (Exception $e) {
    error_log("ERROR API V2: " . $e->getMessage());
    sendError("Error interno: " . $e->getMessage(), 500);
}

function handleGet($db) {
    try {
        // Estadísticas
        if (isset($_GET['stats'])) {
            $stats = [
                'total' => (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
                'active' => (int)$db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
                'featured' => (int)$db->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn(),
                'categoriesWithProducts' => (int)$db->query("SELECT COUNT(DISTINCT category_id) FROM products WHERE category_id IS NOT NULL")->fetchColumn()
            ];
            
            sendSuccess($stats);
            return;
        }
        
        // Producto individual
        if (!empty($_GET['id'])) {
            $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                                FROM products p 
                                LEFT JOIN categories c ON p.category_id = c.id 
                                WHERE p.id = ?");
            $stmt->execute([$_GET['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                sendSuccess(formatProduct($product));
            } else {
                sendError("Producto no encontrado", 404);
            }
            return;
        }
        
        // Listar productos
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        $where = ["1=1"]; // Permitir todos los productos en admin
        $params = [];
        
        if (!empty($_GET['category'])) {
            $where[] = "(p.category_id = ? OR p.category_id = ?)";
            $params[] = $_GET['category'];
            $params[] = $_GET['category']; // Intentar tanto con ID como con slug
        }
        
        if (!empty($_GET['gender'])) {
            $where[] = "p.gender = ?";
            $params[] = $_GET['gender'];
        }
        
        if (!empty($_GET['search'])) {
            $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = '%' . $_GET['search'] . '%';
            $params[] = '%' . $_GET['search'] . '%';
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        // Total
        $countSql = "SELECT COUNT(*) FROM products p $whereClause";
        $countStmt = $db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        // Productos
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                $whereClause 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
            $params[] = $limit;
            $params[] = $offset;
        
            $stmt = $db->prepare($sql);
            // Forzar los dos últimos parámetros como enteros (LIMIT y OFFSET)
            $paramCount = count($params);
            for ($i = 0; $i < $paramCount - 2; $i++) {
                $stmt->bindValue($i + 1, $params[$i]);
            }
            $stmt->bindValue($paramCount - 1, (int)$params[$paramCount - 2], PDO::PARAM_INT); // LIMIT
            $stmt->bindValue($paramCount, (int)$params[$paramCount - 1], PDO::PARAM_INT);     // OFFSET
            $stmt->execute();
        
            $products = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $products[] = formatProduct($row);
            }
        
            sendSuccess($products);
        
    } catch (Exception $e) {
        error_log("Error en handleGet: " . $e->getMessage());
        sendError("Error en consulta: " . $e->getMessage(), 500);
    }
}

function handlePost($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        error_log("POST Data: " . json_encode($input));
        
        if (!$input) {
            sendError("Datos JSON inválidos", 400);
            return;
        }
        
        // Validaciones
        if (empty($input['name']) || empty($input['price']) || empty($input['category_id'])) {
            sendError("Campos requeridos: name, price, category_id", 400);
            return;
        }
        
        $id = 'prod-' . uniqid();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['name']), '-'));
        
        $sql = "INSERT INTO products (id, name, slug, description, price, sale_price, stock_quantity, 
                                    category_id, gender, main_image, gallery, colors, sizes, 
                                    is_featured, status, discount_percentage, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $discount = 0;
        if (!empty($input['sale_price']) && $input['price'] > 0) {
            $discount = round((1 - $input['sale_price'] / $input['price']) * 100);
        }
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $id,
            $input['name'],
            $slug,
            $input['description'] ?? '',
            (float)$input['price'],
            !empty($input['sale_price']) ? (float)$input['sale_price'] : null,
            (int)($input['stock_quantity'] ?? 0),
            $input['category_id'],
            $input['gender'] ?? 'unisex',
            $input['main_image'] ?? null,
            !empty($input['gallery']) ? json_encode($input['gallery']) : null,
            !empty($input['colors']) ? json_encode($input['colors']) : null,
            !empty($input['sizes']) ? json_encode($input['sizes']) : null,
            !empty($input['is_featured']) ? 1 : 0,
            $input['status'] ?? 'active',
            $discount
        ]);
        
        if ($result) {
            // Obtener el producto creado
            $stmt = $db->prepare("SELECT p.*, c.name as category_name 
                                FROM products p 
                                LEFT JOIN product_categories c ON p.category_id = c.id 
                                WHERE p.id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendSuccess(formatProduct($product), 201);
        } else {
            sendError("Error al crear producto", 500);
        }
        
    } catch (Exception $e) {
        error_log("Error en handlePost: " . $e->getMessage());
        sendError("Error creando producto: " . $e->getMessage(), 500);
    }
}

function handlePut($db) {
    try {
        if (empty($_GET['id'])) {
            sendError("ID requerido para actualizar", 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            sendError("Datos JSON inválidos", 400);
            return;
        }
        
        $sql = "UPDATE products SET name = ?, description = ?, price = ?, sale_price = ?, 
                stock_quantity = ?, category_id = ?, gender = ?, main_image = ?, 
                gallery = ?, colors = ?, sizes = ?, is_featured = ?, status = ?, 
                discount_percentage = ?, updated_at = NOW() WHERE id = ?";
        
        $discount = 0;
        if (!empty($input['sale_price']) && $input['price'] > 0) {
            $discount = round((1 - $input['sale_price'] / $input['price']) * 100);
        }
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $input['name'],
            $input['description'] ?? '',
            (float)$input['price'],
            !empty($input['sale_price']) ? (float)$input['sale_price'] : null,
            (int)($input['stock_quantity'] ?? 0),
            $input['category_id'],
            $input['gender'] ?? 'unisex',
            $input['main_image'] ?? null,
            !empty($input['gallery']) ? json_encode($input['gallery']) : null,
            !empty($input['colors']) ? json_encode($input['colors']) : null,
            !empty($input['sizes']) ? json_encode($input['sizes']) : null,
            !empty($input['is_featured']) ? 1 : 0,
            $input['status'] ?? 'active',
            $discount,
            $_GET['id']
        ]);
        
        if ($result) {
            sendSuccess(['message' => 'Producto actualizado correctamente']);
        } else {
            sendError("Error actualizando producto", 500);
        }
        
    } catch (Exception $e) {
        error_log("Error en handlePut: " . $e->getMessage());
        sendError("Error: " . $e->getMessage(), 500);
    }
}

function handleDelete($db) {
    try {
        if (empty($_GET['id'])) {
            sendError("ID requerido para eliminar", 400);
            return;
        }
        
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        $result = $stmt->execute([$_GET['id']]);
        
        if ($result && $stmt->rowCount() > 0) {
            sendSuccess(['message' => 'Producto eliminado correctamente']);
        } else {
            sendError("Producto no encontrado", 404);
        }
        
    } catch (Exception $e) {
        error_log("Error en handleDelete: " . $e->getMessage());
        sendError("Error: " . $e->getMessage(), 500);
    }
}

function formatProduct($product) {
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'],
        'description' => $product['description'],
        'short_description' => $product['short_description'],
        'price' => (float)$product['price'],
        'sale_price' => $product['sale_price'] ? (float)$product['sale_price'] : null,
        'stock_quantity' => (int)$product['stock_quantity'],
        'category_id' => $product['category_id'],
        'category_name' => $product['category_name'] ?? '',
        'gender' => $product['gender'],
        'main_image' => $product['main_image'],
        'gallery' => $product['gallery'] ? json_decode($product['gallery'], true) : [],
        'colors' => $product['colors'] ? json_decode($product['colors'], true) : [],
        'sizes' => $product['sizes'] ? json_decode($product['sizes'], true) : [],
        'discount_percentage' => (int)$product['discount_percentage'],
        'is_featured' => (int)$product['is_featured'] === 1,
        'status' => $product['status'],
        'created_at' => $product['created_at'],
        'updated_at' => $product['updated_at']
    ];
}

function sendSuccess($data, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('c')
    ]);
}

function sendError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'timestamp' => date('c')
    ]);
}
?>
