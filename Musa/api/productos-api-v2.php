<?php
// productos-api-v2.php - API completamente nueva para evitar problemas de caché
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en JSON

try {
    // Conexión a BD
    require_once __DIR__ . '/../config/database.php';
    $db = DatabaseConfig::getConnection();
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetRequest($db);
            break;
        case 'POST':
            handlePostRequest($db);
            break;
        case 'PUT':
            handlePutRequest($db);
            break;
        case 'DELETE':
            handleDeleteRequest($db);
            break;
        default:
            respondError('Método no permitido', 405);
    }
    
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    respondError('Error interno del servidor', 500);
}

function handleGetRequest($db) {
    try {
        // Estadísticas
        if (isset($_GET['stats'])) {
            $stats = [
                'total_products' => (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
                'active_products' => (int)$db->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn(),
                'featured_products' => (int)$db->query("SELECT COUNT(*) FROM products WHERE is_featured = 1")->fetchColumn(),
                'low_stock' => (int)$db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5")->fetchColumn()
            ];
            
            respondSuccess($stats);
            return;
        }
        
        // Producto individual
        if (!empty($_GET['id'])) {
            $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$_GET['id']]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                respondSuccess(formatProduct($product));
            } else {
                respondError('Producto no encontrado', 404);
            }
            return;
        }
        
        // Lista de productos
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;
        
        // Construir query con filtros avanzados
        $where = ["p.status = 'active'"];
        $params = [];
        
        // Filtro por género
        if (!empty($_GET['gender'])) {
            $where[] = "p.gender = ?";
            $params[] = $_GET['gender'];
        }
        
        // Filtro por categoría (slug o ID)
        if (!empty($_GET['category'])) {
            $where[] = "(c.slug = ? OR p.category_id = ?)";
            $params[] = $_GET['category'];
            $params[] = $_GET['category'];
        }
        
        // Filtro por búsqueda de texto
        if (!empty($_GET['search'])) {
            $searchTerm = '%' . $_GET['search'] . '%';
            $where[] = "(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Filtro por precio
        if (!empty($_GET['precio_min'])) {
            $where[] = "p.price >= ?";
            $params[] = (float)$_GET['precio_min'];
        }
        
        if (!empty($_GET['precio_max'])) {
            $where[] = "p.price <= ?";
            $params[] = (float)$_GET['precio_max'];
        }
        
        // Filtro por productos destacados
        if (!empty($_GET['featured'])) {
            $where[] = "p.is_featured = 1";
        }
        
        $whereClause = "WHERE " . implode(" AND ", $where);
        
        // Definir orden
        $orderBy = "ORDER BY ";
        switch ($_GET['ordenar'] ?? 'recientes') {
            case 'precio_asc':
                $orderBy .= "p.price ASC";
                break;
            case 'precio_desc':
                $orderBy .= "p.price DESC";
                break;
            case 'nombre':
                $orderBy .= "p.name ASC";
                break;
            case 'populares':
                $orderBy .= "p.is_featured DESC, p.created_at DESC";
                break;
            default:
                $orderBy .= "p.created_at DESC";
        }
        
        // Contar total
        $countQuery = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause";
        $countStmt = $db->prepare($countQuery);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();
        
        // Obtener productos
        $query = "SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereClause $orderBy LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        
        $products = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $products[] = formatProduct($row);
        }
        
        $result = [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'last_page' => max(1, ceil($total / $limit))
            ]
        ];
        
        respondSuccess($result);
        
    } catch (Exception $e) {
        error_log("GET Error: " . $e->getMessage());
        respondError('Error en consulta', 500);
    }
}

function handlePostRequest($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || empty($input['name']) || empty($input['price']) || empty($input['category_id'])) {
            respondError('Datos inválidos. Requeridos: name, price, category_id', 400);
            return;
        }
        
        $id = 'prod-' . uniqid();
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['name']), '-'));
        
        $query = "INSERT INTO products (id, name, slug, description, price, sale_price, stock_quantity, category_id, gender, main_image, gallery, colors, sizes, is_featured, status, discount_percentage, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $discount = 0;
        if (!empty($input['sale_price']) && $input['price'] > 0) {
            $discount = round((1 - $input['sale_price'] / $input['price']) * 100);
        }
        
        $params = [
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
        ];
        
        $stmt = $db->prepare($query);
        if ($stmt->execute($params)) {
            // Obtener el producto creado
            $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN product_categories c ON p.category_id = c.id WHERE p.id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            respondSuccess(formatProduct($product), 201);
        } else {
            respondError('Error al crear producto', 500);
        }
        
    } catch (Exception $e) {
        error_log("POST Error: " . $e->getMessage());
        respondError('Error al procesar solicitud', 500);
    }
}

function handlePutRequest($db) {
    try {
        if (empty($_GET['id'])) {
            respondError('ID requerido para actualizar', 400);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            respondError('Datos inválidos', 400);
            return;
        }
        
        $query = "UPDATE products SET name = ?, description = ?, price = ?, sale_price = ?, stock_quantity = ?, category_id = ?, gender = ?, main_image = ?, gallery = ?, colors = ?, sizes = ?, is_featured = ?, status = ?, discount_percentage = ?, updated_at = NOW() WHERE id = ?";
        
        $discount = 0;
        if (!empty($input['sale_price']) && $input['price'] > 0) {
            $discount = round((1 - $input['sale_price'] / $input['price']) * 100);
        }
        
        $params = [
            $input['name'] ?? '',
            $input['description'] ?? '',
            (float)($input['price'] ?? 0),
            !empty($input['sale_price']) ? (float)$input['sale_price'] : null,
            (int)($input['stock_quantity'] ?? 0),
            $input['category_id'] ?? '',
            $input['gender'] ?? 'unisex',
            $input['main_image'] ?? null,
            !empty($input['gallery']) ? json_encode($input['gallery']) : null,
            !empty($input['colors']) ? json_encode($input['colors']) : null,
            !empty($input['sizes']) ? json_encode($input['sizes']) : null,
            !empty($input['is_featured']) ? 1 : 0,
            $input['status'] ?? 'active',
            $discount,
            $_GET['id']
        ];
        
        $stmt = $db->prepare($query);
        if ($stmt->execute($params) && $stmt->rowCount() > 0) {
            respondSuccess(['message' => 'Producto actualizado exitosamente']);
        } else {
            respondError('Producto no encontrado o sin cambios', 404);
        }
        
    } catch (Exception $e) {
        error_log("PUT Error: " . $e->getMessage());
        respondError('Error al actualizar', 500);
    }
}

function handleDeleteRequest($db) {
    try {
        if (empty($_GET['id'])) {
            respondError('ID requerido para eliminar', 400);
            return;
        }
        
        $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt->execute([$_GET['id']]) && $stmt->rowCount() > 0) {
            respondSuccess(['message' => 'Producto eliminado exitosamente']);
        } else {
            respondError('Producto no encontrado', 404);
        }
        
    } catch (Exception $e) {
        error_log("DELETE Error: " . $e->getMessage());
        respondError('Error al eliminar', 500);
    }
}

function formatProduct($product) {
    // Procesar imágenes
    $images = [];
    if (!empty($product['main_image'])) {
        $images[] = $product['main_image'];
    }
    if (!empty($product['gallery'])) {
        $gallery = json_decode($product['gallery'], true);
        if (is_array($gallery)) {
            $images = array_merge($images, $gallery);
        }
    }
    
    // Calcular precio con descuento
    $precio = (float)$product['price'];
    $precio_oferta = null;
    if (!empty($product['sale_price']) && $product['sale_price'] > 0) {
        $precio_oferta = (float)$product['sale_price'];
    } elseif (!empty($product['discount_percentage']) && $product['discount_percentage'] > 0) {
        $descuento = (float)$product['discount_percentage'];
        $precio_oferta = $precio * (1 - $descuento / 100);
    }
    
    return [
        'id' => $product['id'],
        'name' => $product['name'],
        'slug' => $product['slug'] ?? '',
        'description' => $product['description'] ?? '',
        'precio' => $precio,
        'price' => $precio, // Compatibilidad
        'precio_oferta' => $precio_oferta,
        'sale_price' => $precio_oferta, // Compatibilidad
        'descuento' => (int)($product['discount_percentage'] ?? 0),
        'discount_percentage' => (int)($product['discount_percentage'] ?? 0),
        'stock_quantity' => (int)($product['stock_quantity'] ?? 0),
        'category_id' => $product['category_id'] ?? '',
        'categoria' => $product['category_slug'] ?? $product['category_id'] ?? '',
        'category_name' => $product['category_name'] ?? '',
        'category_slug' => $product['category_slug'] ?? '',
        'gender' => $product['gender'] ?? 'unisex',
        'image' => $product['main_image'] ?? '', // Imagen principal
        'main_image' => $product['main_image'] ?? '',
        'images' => $images, // Array de todas las imágenes
        'gallery' => $product['gallery'] ? json_decode($product['gallery'], true) : [],
        'colors' => $product['colors'] ? json_decode($product['colors'], true) : [],
        'sizes' => $product['sizes'] ? json_decode($product['sizes'], true) : [],
        'rating' => isset($product['rating']) ? (float)$product['rating'] : null,
        'is_featured' => (int)($product['is_featured'] ?? 0) === 1,
        'status' => $product['status'] ?? 'active',
        'created_at' => $product['created_at'] ?? '',
        'updated_at' => $product['updated_at'] ?? ''
    ];
}

function respondSuccess($data, $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('c')
    ]);
}

function respondError($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'timestamp' => date('c')
    ]);
}
?>
