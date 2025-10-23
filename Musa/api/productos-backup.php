<?php
require_once __DIR__ . '/../config/database.php';

// Headers para API
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Log de conexión
error_log("✅ Conexión a base de datos establecida correctamente");

class ProductsAPI {
    private $db;

    public function __construct() {
        try {
            $this->db = DatabaseConfig::getConnection();
        } catch (Exception $e) {
            $this->sendError("Error de conexión a base de datos: " . $e->getMessage(), 500);
        }
    }

    /**
     * Manejar la petición de la API
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // DEBUG: Agregar logging temporal
        error_log("🔍 DEBUG handleRequest:");
        error_log("   Method: " . $method);
        error_log("   Path: " . $path);
        error_log("   REQUEST_URI: " . $_SERVER['REQUEST_URI']);
        
        // Extraer ID del path después de productos.php
        $id = null;
        if (preg_match('/\/productos\.php\/([^\/\?]+)/', $path, $matches)) {
            $id = $matches[1];
            // Validar que el ID tenga formato correcto (evitar parámetros del navegador)
            if (strlen($id) < 10 || strpos($id, 'vscode') !== false || strpos($id, '?') !== false) {
                $id = null;
            }
        }
        
        error_log("   Detected ID: " . ($id ? $id : 'NULL'));

        try {
            switch ($method) {
                case 'GET':
                    if ($id) {
                        $this->getProduct($id);
                    } else {
                        $this->getProducts();
                    }
                    break;

                case 'POST':
                    $this->createProduct();
                    break;

                case 'PUT':
                    if ($id) {
                        $this->updateProduct($id);
                    } else {
                        $this->sendError("ID de producto requerido para actualización", 400);
                    }
                    break;

                case 'DELETE':
                    if ($id) {
                        $this->deleteProduct($id);
                    } else {
                        $this->sendError("ID de producto requerido para eliminación", 400);
                    }
                    break;

                default:
                    $this->sendError("Método no permitido", 405);
            }

        } catch (Exception $e) {
            error_log("Error en ProductsAPI: " . $e->getMessage());
            $this->sendError("Error interno del servidor: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener todos los productos con filtros opcionales
     */
    private function getProducts() {
        try {
            error_log("🔍 DEBUG getProducts() iniciado");
            
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 20;
            $offset = ($page - 1) * $limit;
            
            $category = $_GET['category'] ?? '';
            $gender = $_GET['gender'] ?? '';
            $status = $_GET['status'] ?? 'active';
            $featured = $_GET['featured'] ?? '';
            $search = $_GET['search'] ?? '';

            error_log("   Filtros: status=$status, category=$category, gender=$gender");

            // Construir query dinámicamente
            $whereClause = ["status = :status"];
            $params = [':status' => $status];
            
            if ($category) {
                $whereClause[] = "category_id = :category";
                $params[':category'] = $category;
            }
            
            if ($gender) {
                $whereClause[] = "gender = :gender";
                $params[':gender'] = $gender;
            }
            
            if ($featured) {
                $whereClause[] = "is_featured = 1";
            }
            
            if ($search) {
                $whereClause[] = "(name LIKE :search OR description LIKE :search)";
                $params[':search'] = "%$search%";
            }

            // Query principal
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN product_categories c ON p.category_id = c.id 
                    WHERE " . implode(' AND ', $whereClause) . " 
                    ORDER BY p.created_at DESC 
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            
            // Bind parámetros
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $products = $stmt->fetchAll();

            error_log("🔍 DEBUG productos encontrados: " . count($products));

            // Contar total
            $countSql = "SELECT COUNT(*) as total FROM products p WHERE " . implode(' AND ', $whereClause);
            $countStmt = $this->db->prepare($countSql);
            foreach ($params as $key => $value) {
                if ($key !== ':limit' && $key !== ':offset') {
                    $countStmt->bindValue($key, $value);
                }
            }
            $countStmt->execute();
            $total = $countStmt->fetch()['total'];

            error_log("🔍 DEBUG total de productos: " . $total);

            // Formatear productos
            $formattedProducts = array_map([$this, 'formatProduct'], $products);

            $response = [
                'products' => $formattedProducts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => (int)$total,
                    'last_page' => ceil($total / $limit)
                ]
            ];

            error_log("🔍 DEBUG parámetros: " . print_r([
                'status' => $status,
                'limit' => $limit,
                'offset' => $offset
            ], true));

            error_log("🔍 DEBUG productos procesados: " . count($formattedProducts));

            $this->sendSuccess($response);

        } catch (Exception $e) {
            error_log("Error en getProducts: " . $e->getMessage());
            $this->sendError("Error obteniendo productos: " . $e->getMessage(), 500);
        }
    }

    /**
     * Obtener un producto específico
     */
    private function getProduct($id) {
        try {
            $sql = "SELECT p.*, c.name as category_name 
                    FROM products p 
                    LEFT JOIN product_categories c ON p.category_id = c.id 
                    WHERE p.id = :id AND p.status != 'deleted'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $product = $stmt->fetch();
            
            if ($product) {
                $this->sendSuccess(['product' => $this->formatProduct($product)]);
            } else {
                $this->sendError("Producto no encontrado", 404);
            }
        } catch (Exception $e) {
            $this->sendError("Error obteniendo producto: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crear un nuevo producto
     */
    private function createProduct() {
        try {
            error_log("🔧 CreateProduct iniciado");
            
            // Verificar si es un upload con imagen
            if (!empty($_FILES) && isset($_FILES['main_image'])) {
                error_log("📸 Procesando FormData con imagen");
                // Es un FormData con archivo
                $input = $_POST; // Los datos vienen en $_POST cuando es FormData
                
                // Procesar upload de imagen
                $uploadResult = $this->handleImageUpload($_FILES['main_image']);
                if (!$uploadResult['success']) {
                    error_log("❌ Error en upload: " . $uploadResult['message']);
                    $this->sendError($uploadResult['message'], 400);
                    return;
                }
                
                $input['main_image'] = $uploadResult['url'];
                error_log("✅ Imagen subida: " . $uploadResult['url']);
                
            } else {
                error_log("📋 Procesando JSON normal");
                // Es JSON normal
                $input = json_decode(file_get_contents('php://input'), true);
            }
            
            if (!$input) {
                error_log("❌ Datos de entrada inválidos");
                $this->sendError("Datos de entrada inválidos", 400);
                return;
            }

            error_log("📊 Datos recibidos: " . print_r($input, true));

            // Validar campos requeridos
            $required = ['name', 'price', 'category_id'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    error_log("❌ Campo requerido faltante: $field");
                    $this->sendError("El campo '$field' es requerido", 400);
                    return;
                }
            }

            // Generar ID y slug únicos
            $id = $this->generateUniqueId();
            $slug = $this->generateUniqueSlug($input['name']);

            // Preparar datos
            $data = [
                'id' => $id,
                'name' => trim($input['name']),
                'slug' => $slug,
                'description' => $input['description'] ?? '',
                'short_description' => $input['short_description'] ?? '',
                'price' => floatval($input['price']),
                'sale_price' => !empty($input['sale_price']) ? floatval($input['sale_price']) : null,
                'stock_quantity' => intval($input['stock_quantity'] ?? 0),
                'min_stock' => intval($input['min_stock'] ?? 5),
                'category_id' => $input['category_id'],
                'gender' => $input['gender'] ?? 'unisex',
                'main_image' => $input['main_image'] ?? null,
                'gallery' => !empty($input['gallery']) ? json_encode($input['gallery']) : null,
                'colors' => !empty($input['colors']) ? json_encode($input['colors']) : null,
                'sizes' => !empty($input['sizes']) ? json_encode($input['sizes']) : null,
                'tags' => !empty($input['tags']) ? json_encode($input['tags']) : null,
                'features' => !empty($input['features']) ? json_encode($input['features']) : null,
                'is_featured' => !empty($input['is_featured']) ? 1 : 0,
                'is_trending' => !empty($input['is_trending']) ? 1 : 0,
                'status' => $input['status'] ?? 'active',
                'meta_title' => $input['meta_title'] ?? $input['name'],
                'meta_description' => $input['meta_description'] ?? ''
            ];

            // Procesar arrays que pueden venir como strings JSON desde FormData
            if (isset($input['sizes']) && is_string($input['sizes'])) {
                $data['sizes'] = $input['sizes']; // Ya está en formato JSON
            }
            if (isset($input['colors']) && is_string($input['colors'])) {
                $data['colors'] = $input['colors']; // Ya está en formato JSON
            }

            // Calcular descuento si hay precio de oferta
            if ($data['sale_price'] && $data['price'] > 0) {
                $data['discount_percentage'] = round((1 - $data['sale_price'] / $data['price']) * 100);
            }

            error_log("💾 Guardando producto en BD: " . $data['name']);

            $result = insertRecord('products', $data);

            if ($result['success']) {
                // Obtener el producto recién creado con información de categoría
                $stmt = $this->db->prepare("SELECT p.*, c.name as category_name 
                                          FROM products p 
                                          LEFT JOIN product_categories c ON p.category_id = c.id 
                                          WHERE p.id = :id");
                $stmt->bindParam(':id', $id);
                $stmt->execute();
                $product = $stmt->fetch();

                $this->sendSuccess([
                    'message' => 'Producto creado exitosamente',
                    'product' => $this->formatProduct($product)
                ], 201);
            } else {
                $this->sendError("Error creando producto: " . $result['error'], 500);
            }

        } catch (Exception $e) {
            $this->sendError("Error creando producto: " . $e->getMessage(), 500);
        }
    }

    /**
     * Manejar upload de imagen
     */
    private function handleImageUpload($file) {
        try {
            // Validar errores de upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'Error al subir la imagen: ' . $file['error']];
            }

            // Validar tamaño (máximo 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'message' => 'La imagen es demasiado grande. Máximo 5MB.'];
            }

            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            // Usar múltiples métodos para validar tipo de archivo
            $fileType = null;
            if (function_exists('mime_content_type')) {
                $fileType = mime_content_type($file['tmp_name']);
            } else {
                $fileType = $file['type']; // Fallback al tipo reportado por el navegador
            }
            
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'message' => "Tipo de archivo no permitido ($fileType). Solo JPG, PNG, GIF y WebP."];
            }

            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = 'product_' . uniqid() . '_' . time() . '.' . strtolower($extension);
            
            // Directorio de destino
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    return ['success' => false, 'message' => 'No se pudo crear el directorio de uploads'];
                }
            }
            
            $targetPath = $uploadDir . $fileName;
            
            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                return ['success' => false, 'message' => 'Error al guardar la imagen en el servidor'];
            }

            // URL relativa
            $imageUrl = 'uploads/' . $fileName;

            return [
                'success' => true,
                'url' => $imageUrl,
                'fileName' => $fileName
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error procesando imagen: ' . $e->getMessage()];
        }
    }

    /**
     * Actualizar producto existente
     */
    private function updateProduct($id) {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $this->sendError("Datos de entrada inválidos", 400);
            }

            $result = updateRecord('products', $input, ['id' => $id]);

            if ($result['success']) {
                $this->sendSuccess(['message' => 'Producto actualizado exitosamente']);
            } else {
                $this->sendError("Error actualizando producto: " . $result['error'], 500);
            }

        } catch (Exception $e) {
            $this->sendError("Error actualizando producto: " . $e->getMessage(), 500);
        }
    }

    /**
     * Eliminar producto
     */
    private function deleteProduct($id) {
        try {
            $result = deleteRecord('products', ['id' => $id]);

            if ($result['success']) {
                $this->sendSuccess(['message' => 'Producto eliminado exitosamente']);
            } else {
                $this->sendError("Error eliminando producto: " . $result['error'], 500);
            }

        } catch (Exception $e) {
            $this->sendError("Error eliminando producto: " . $e->getMessage(), 500);
        }
    }

    /**
     * Formatear producto para respuesta
     */
    private function formatProduct($product) {
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
            'discount_percentage' => (int)($product['discount_percentage'] ?? 0),
            'is_featured' => (bool)$product['is_featured'],
            'status' => $product['status'],
            'created_at' => $product['created_at'],
            'updated_at' => $product['updated_at']
        ];
    }

    /**
     * Generar ID único
     */
    private function generateUniqueId() {
        return 'prod_' . time() . '_' . substr(md5(uniqid(rand(), true)), 0, 8);
    }

    /**
     * Generar slug único
     */
    private function generateUniqueSlug($name) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        // Verificar si existe
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE slug = :slug");
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }

    /**
     * Enviar respuesta de éxito
     */
    private function sendSuccess($data, $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        exit();
    }

    /**
     * Enviar respuesta de error
     */
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ]);
        exit();
    }
}

// Inicializar y manejar la petición
$api = new ProductsAPI();
$api->handleRequest();
?>
