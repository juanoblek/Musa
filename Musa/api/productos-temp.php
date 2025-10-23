<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Manejar OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuración de base de datos
require_once __DIR__ . '/../config/database.php';

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/api_errors.log');

class ProductsAPI {
    private $db;
    
    public function __construct() {
        try {
            $this->db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            error_log("✅ Conexión a BD exitosa");
        } catch (Exception $e) {
            error_log("❌ Error conexión BD: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }
    
    public function handleRequest() {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = $_GET['path'] ?? '';
            
            error_log("🔄 API Request: $method $path");
            
            switch ($method) {
                case 'GET':
                    $this->handleGet();
                    break;
                case 'POST':
                    $this->handlePost();
                    break;
                case 'PUT':
                    $this->handlePut();
                    break;
                case 'DELETE':
                    $this->handleDelete();
                    break;
                default:
                    throw new Exception("Método no permitido", 405);
            }
        } catch (Exception $e) {
            error_log("❌ Error en handleRequest: " . $e->getMessage());
            $code = is_int($e->getCode()) && $e->getCode() > 0 ? $e->getCode() : 500;
            http_response_code($code);
            echo json_encode([
                'error' => $e->getMessage(),
                'code' => $code
            ]);
        }
    }
    
    private function handleGet() {
        if (isset($_GET['id'])) {
            $this->getProduct($_GET['id']);
        } else {
            $this->getProducts();
        }
    }
    
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

            // Construir query SIN JOIN - versión temporal
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
            
            if ($featured !== '') {
                $whereClause[] = "featured = :featured";
                $params[':featured'] = $featured;
            }
            
            if ($search) {
                $whereClause[] = "(name LIKE :search OR description LIKE :search)";
                $params[':search'] = "%$search%";
            }

            // Query principal SIN JOIN
            $sql = "SELECT * FROM products WHERE " . implode(' AND ', $whereClause) . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            
            error_log("🔍 DEBUG SQL: " . $sql);
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $products = $stmt->fetchAll();

            error_log("🔍 DEBUG productos encontrados: " . count($products));

            // Contar total
            $countSql = "SELECT COUNT(*) as total FROM products WHERE " . implode(' AND ', $whereClause);
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
                    'total_pages' => ceil($total / $limit)
                ]
            ];

            error_log("✅ DEBUG getProducts() completado exitosamente");
            echo json_encode($response);

        } catch (Exception $e) {
            error_log("❌ Error en getProducts: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function getProduct($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch();
            
            if (!$product) {
                throw new Exception("Producto no encontrado", 404);
            }
            
            echo json_encode($this->formatProduct($product));
        } catch (Exception $e) {
            error_log("❌ Error en getProduct: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function handlePost() {
        try {
            // Detectar si es FormData (con imagen) o JSON
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'multipart/form-data') !== false) {
                // Es FormData - con imagen
                $input = $_POST;
                
                // Manejar la subida de imagen si existe
                if (isset($_FILES['main_image'])) {
                    $imageUrl = $this->handleImageUpload($_FILES['main_image']);
                    if ($imageUrl) {
                        $input['images'] = [$imageUrl];
                    }
                }
                
                // Decodificar arrays JSON que vienen como strings
                if (isset($input['sizes']) && is_string($input['sizes'])) {
                    $input['sizes'] = json_decode($input['sizes'], true);
                }
                if (isset($input['colors']) && is_string($input['colors'])) {
                    $input['colors'] = json_decode($input['colors'], true);
                }
                
            } else {
                // Es JSON normal
                $input = json_decode(file_get_contents('php://input'), true);
            }
            
            if (!$input) {
                throw new Exception("Datos inválidos", 400);
            }
            
            error_log("🔄 Creando producto: " . json_encode($input));
            
            // CORREGIR: Generar ID único y slug para campos obligatorios
            $productId = 'prod_' . time() . '_' . uniqid();
            $slug = $this->generateSlug($input['name']);
            
            $requiredFields = ['name', 'price'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    throw new Exception("Campo requerido: $field", 400);
                }
            }
            
            // Preparar valores con ID, slug e imagen generados automáticamente
            $mainImage = null;
            $gallery = null;
            
            // Procesar imagen si existe
            if (isset($input['images']) && is_array($input['images']) && !empty($input['images'])) {
                $mainImage = $input['images'][0]; // Primera imagen como principal
                if (count($input['images']) > 1) {
                    $gallery = json_encode($input['images']); // Todas las imágenes en gallery
                }
            }
            
            $values = [
                $productId,                    // id
                $input['name'],               // name  
                $slug,                        // slug
                $input['description'] ?? '',  // description
                $input['price'],             // price
                $mainImage,                  // main_image
                $gallery                     // gallery
            ];
            
            error_log("🔍 DEBUG - Valores para insertar: " . json_encode($values));
            
            $stmt = $this->db->prepare("
                INSERT INTO products (id, name, slug, description, price, main_image, gallery) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute($values);
            
            if ($result) {
                $productId = $values[0]; // Usar el ID que generamos
                error_log("✅ Producto creado con ID: $productId");
                
                echo json_encode([
                    'success' => true,
                    'id' => $productId,
                    'message' => 'Producto creado exitosamente'
                ]);
            } else {
                throw new Exception("Error al crear el producto", 500);
            }
        } catch (Exception $e) {
            error_log("❌ Error en handlePost: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function handlePut() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception("ID de producto requerido", 400);
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception("Datos inválidos", 400);
            }
            
            error_log("🔄 Actualizando producto ID: $id");
            
            $stmt = $this->db->prepare("
                UPDATE products SET 
                name = ?, description = ?, price = ?, category = ?, subcategory = ?,
                brand = ?, color = ?, size = ?, gender = ?, status = ?, featured = ?,
                images = ?, stock_quantity = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $result = $stmt->execute([
                $input['name'],
                $input['description'] ?? '',
                $input['price'],
                $input['category'],
                $input['subcategory'] ?? null,
                $input['brand'] ?? null,
                $input['color'] ?? null,
                $input['size'] ?? null,
                $input['gender'] ?? 'unisex',
                $input['status'] ?? 'active',
                $input['featured'] ?? 0,
                isset($input['images']) ? json_encode($input['images']) : json_encode([]),
                $input['stock_quantity'] ?? 0,
                $id
            ]);
            
            if ($result) {
                error_log("✅ Producto ID $id actualizado");
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente'
                ]);
            } else {
                throw new Exception("Error al actualizar el producto", 500);
            }
        } catch (Exception $e) {
            error_log("❌ Error en handlePut: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function handleDelete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            throw new Exception("ID de producto requerido", 400);
        }
        
        try {
            $stmt = $this->db->prepare("UPDATE products SET status = 'deleted' WHERE id = ?");
            $result = $stmt->execute([$id]);
            
            if ($result) {
                error_log("✅ Producto ID $id eliminado (soft delete)");
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto eliminado exitosamente'
                ]);
            } else {
                throw new Exception("Error al eliminar el producto", 500);
            }
        } catch (Exception $e) {
            error_log("❌ Error en handleDelete: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function handleImageUpload($file) {
        try {
            // Verificar que se subió correctamente
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log("❌ Error en upload: " . $file['error']);
                return null;
            }
            
            // Verificar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                error_log("❌ Tipo de archivo no permitido: " . $file['type']);
                return null;
            }
            
            // Crear directorio uploads si no existe
            $uploadsDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            // Generar nombre único
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $filepath = $uploadsDir . '/' . $filename;
            
            // Mover archivo
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                error_log("✅ Imagen subida: " . $filename);
                return '/uploads/' . $filename;
            } else {
                error_log("❌ Error moviendo archivo");
                return null;
            }
        } catch (Exception $e) {
            error_log("❌ Error en handleImageUpload: " . $e->getMessage());
            return null;
        }
    }
    
    private function generateSlug($name) {
        // Convertir a minúsculas
        $slug = strtolower($name);
        
        // Remover caracteres especiales y convertir espacios a guiones
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Si está vacío, generar uno basado en timestamp
        if (empty($slug)) {
            $slug = 'producto-' . time();
        }
        
        // Asegurar que es único
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    private function slugExists($slug) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function formatProduct($product) {
        // Procesar imágenes reales de la base de datos
        $images = [];
        
        // Si hay imagen principal
        if (!empty($product['main_image'])) {
            $imageUrl = $product['main_image'];
            // Normalizar URL de imagen
            if (strpos($imageUrl, 'http') === 0) {
                $images[] = $imageUrl;
            } elseif (strpos($imageUrl, '/uploads/') === 0) {
                $images[] = 'http://localhost/Musa' . $imageUrl;
            } else {
                $images[] = 'http://localhost/Musa/uploads/' . ltrim($imageUrl, '/');
            }
        }
        
        // Si hay galería de imágenes
        if (!empty($product['gallery'])) {
            $galleryImages = json_decode($product['gallery'], true);
            if (is_array($galleryImages)) {
                foreach ($galleryImages as $img) {
                    if (strpos($img, 'http') === 0) {
                        $images[] = $img;
                    } elseif (strpos($img, '/uploads/') === 0) {
                        $images[] = 'http://localhost/Musa' . $img;
                    } else {
                        $images[] = 'http://localhost/Musa/uploads/' . ltrim($img, '/');
                    }
                }
            }
        }
        
        // Si no hay imágenes, usar placeholder
        if (empty($images)) {
            $images = ['images/placeholder.svg'];
        }
        
        return [
            'id' => $product['id'] ?? '', // Ahora es varchar, no int
            'name' => $product['name'] ?? '',
            'description' => $product['description'] ?? '',
            'price' => (float)($product['price'] ?? 0),
            'category' => $product['category_id'] ?? '',
            'subcategory' => '',
            'brand' => '',
            'color' => '',
            'size' => '',
            'gender' => $product['gender'] ?? 'unisex',
            'status' => 'active',
            'featured' => false,
            'images' => $images, // Procesado arriba
            'stock_quantity' => 0,
            'created_at' => $product['created_at'] ?? '',
            'updated_at' => $product['updated_at'] ?? ''
        ];
    }
}

// Ejecutar API
try {
    $api = new ProductsAPI();
    $api->handleRequest();
} catch (Exception $e) {
    error_log("💥 Error fatal en API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>
