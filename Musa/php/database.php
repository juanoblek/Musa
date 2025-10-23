<?php
// Configuración de la base de datos para hosting
class DatabaseConfig {
    private static function getCredentials() {
        $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
        
        if ($hostname === 'localhost' || $hostname === '127.0.0.1') {
            // Configuración local - USANDO janithal_musa_moda en localhost
            return [
                'host' => 'localhost',
                'dbname' => 'janithal_musa_moda',
                'username' => 'root',
                'password' => ''
            ];
        } else {
            // Configuración para hosting musaarion.com
            return [
                'host' => 'localhost',
                'dbname' => 'janithal_musa_moda',
                'username' => 'janithal_usuario_musaarion_db',
                'password' => 'Chiguiro553021' // CONFIGURAR CON LA CONTRASEÑA REAL DEL HOSTING
            ];
        }
    }
    
    private static $connection = null;
    
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $credentials = self::getCredentials();
                
                self::$connection = new PDO(
                    "mysql:host=" . $credentials['host'] . ";dbname=" . $credentials['dbname'] . ";charset=utf8mb4",
                    $credentials['username'],
                    $credentials['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                    ]
                );
            } catch(PDOException $e) {
                error_log("Error de conexión a la base de datos: " . $e->getMessage());
                die("Error de conexión a la base de datos");
            }
        }
        return self::$connection;
    }
}

// Clase para manejar productos
class ProductManager {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    // Método para detectar qué columna de timestamp usar
    private function getTimestampColumn($tableName) {
        try {
            $stmt = $this->db->prepare("SHOW COLUMNS FROM $tableName");
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Prioridad de columnas de timestamp
            $timestampColumns = ['fecha_creacion', 'created_at', 'fecha_modificacion', 'updated_at', 'timestamp'];
            
            foreach ($timestampColumns as $col) {
                if (in_array($col, $columns)) {
                    return "p.$col";
                }
            }
            
            // Si no encuentra ninguna, usar ID como fallback
            return "p.id";
        } catch (Exception $e) {
            return "p.id";
        }
    }
    
    // Obtener todos los productos
    public function getAllProducts($limit = null, $offset = 0) {
        // Detectar qué columna de timestamp usar
        $timeColumn = $this->getTimestampColumn('productos');
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre, 
                       (SELECT ruta_imagen FROM producto_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.activo = 1 
                ORDER BY $timeColumn DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Crear nuevo producto
    public function createProduct($data) {
        $sql = "INSERT INTO productos (codigo_producto, nombre, slug, descripcion_corta, descripcion_larga, 
                categoria_id, precio, precio_oferta, stock, activo, destacado, nuevo, oferta) 
                VALUES (:codigo, :nombre, :slug, :desc_corta, :desc_larga, :categoria, :precio, :precio_oferta, 
                :stock, :activo, :destacado, :nuevo, :oferta)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':codigo' => $data['codigo_producto'] ?? null,
            ':nombre' => $data['nombre'],
            ':slug' => $this->generateSlug($data['nombre']),
            ':desc_corta' => $data['descripcion_corta'] ?? null,
            ':desc_larga' => $data['descripcion_larga'] ?? null,
            ':categoria' => $data['categoria_id'] ?? null,
            ':precio' => $data['precio'],
            ':precio_oferta' => $data['precio_oferta'] ?? null,
            ':stock' => $data['stock'] ?? 0,
            ':activo' => $data['activo'] ?? 1,
            ':destacado' => $data['destacado'] ?? 0,
            ':nuevo' => $data['nuevo'] ?? 0,
            ':oferta' => $data['oferta'] ?? 0
        ]);
    }
    
    // Subir imagen de producto
    public function uploadProductImage($producto_id, $archivo, $es_principal = 0) {
        $directorio = 'uploads/productos/';
        if (!is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        
        $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $nombre_archivo = $producto_id . '_' . time() . '.' . $extension;
        $ruta_completa = $directorio . $nombre_archivo;
        
        if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
            $sql = "INSERT INTO producto_imagenes (producto_id, ruta_imagen, alt_text, es_principal) 
                    VALUES (:producto_id, :ruta, :alt, :principal)";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':producto_id' => $producto_id,
                ':ruta' => $ruta_completa,
                ':alt' => 'Imagen del producto',
                ':principal' => $es_principal
            ]);
        }
        return false;
    }
    
    // Generar slug único
    private function generateSlug($texto) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $texto), '-'));
        
        // Verificar si existe
        $sql = "SELECT COUNT(*) FROM productos WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }
    
    // Obtener productos por categoría
    public function getProductsByCategory($categoria_id) {
        $sql = "SELECT p.*, 
                       (SELECT ruta_imagen FROM producto_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
                FROM productos p 
                WHERE p.categoria_id = :categoria AND p.activo = 1 
                ORDER BY p.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':categoria' => $categoria_id]);
        return $stmt->fetchAll();
    }
    
    
    // Obtener productos destacados
    public function getFeaturedProducts($limit = 8) {
        $timeColumn = $this->getTimestampColumn('productos');
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre, 
                       (SELECT ruta_imagen FROM producto_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.activo = 1 AND p.destacado = 1
                ORDER BY $timeColumn DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Obtener productos en oferta
    public function getOffersProducts($limit = 8) {
        $timeColumn = $this->getTimestampColumn('productos');
        
        $sql = "SELECT p.*, c.nombre as categoria_nombre, 
                       (SELECT ruta_imagen FROM producto_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                WHERE p.activo = 1 AND p.oferta = 1
                ORDER BY $timeColumn DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Obtener imágenes de un producto
    public function getProductImages($producto_id) {
        $sql = "SELECT * FROM producto_imagenes WHERE producto_id = :id ORDER BY es_principal DESC, orden_mostrar ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $producto_id]);
        $imagenes = $stmt->fetchAll();
        
        $resultado = [
            'principal' => null,
            'todas' => []
        ];
        
        foreach ($imagenes as $imagen) {
            if ($imagen['es_principal']) {
                $resultado['principal'] = $imagen['ruta_imagen'];
            }
            $resultado['todas'][] = $imagen['ruta_imagen'];
        }
        
        return $resultado;
    }
    
    // Obtener variantes de un producto
    public function getProductVariants($producto_id) {
        $sql = "SELECT * FROM producto_variantes WHERE producto_id = :id AND activo = 1 ORDER BY tipo_variante, nombre_variante";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $producto_id]);
        return $stmt->fetchAll();
    }
}

// Clase para manejar categorías
class CategoryManager {
    private $db;
    
    public function __construct() {
        $this->db = DatabaseConfig::getConnection();
    }
    
    public function getAllCategories() {
        try {
            // Primero intentar con columna activo y genero
            $sql = "SELECT * FROM categorias WHERE activo = 1 ORDER BY nombre ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $categories = $stmt->fetchAll();
        } catch (Exception $e) {
            // Si no existe la columna activo, obtener todas las categorías
            try {
                $sql = "SELECT * FROM categorias ORDER BY nombre ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $categories = $stmt->fetchAll();
            } catch (Exception $e2) {
                // Fallback básico
                $sql = "SELECT *, 'general' as genero FROM categorias ORDER BY nombre ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute();
                $categories = $stmt->fetchAll();
            }
        }
        
        // Asegurar que tengan todas las propiedades necesarias
        foreach ($categories as &$category) {
            // Asignar gender usando el valor de genero, limpiando espacios en blanco
            $generoValue = isset($category['genero']) ? trim($category['genero']) : 'general';
            $category['gender'] = !empty($generoValue) ? $generoValue : 'general';
            $category['activo'] = isset($category['activo']) ? (bool)$category['activo'] : true;
        }
        
        return $categories;
    }
    
    public function createCategory($data) {
        try {
            // Verificar qué columnas existen en la tabla
            $checkSql = "SHOW COLUMNS FROM categorias";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute();
            $columns = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasGenero = in_array('genero', $columns);
            $hasActivo = in_array('activo', $columns);
            $hasSlug = in_array('slug', $columns);
            
            // Construir consulta SQL dinámicamente
            $fields = ['nombre'];
            $placeholders = [':nombre'];
            $params = [':nombre' => $data['nombre']];
            
            if ($hasSlug) {
                $fields[] = 'slug';
                $placeholders[] = ':slug';
                $params[':slug'] = $this->generateSlug($data['nombre']);
            }
            
            if (isset($data['descripcion'])) {
                $fields[] = 'descripcion';
                $placeholders[] = ':descripcion';
                $params[':descripcion'] = $data['descripcion'];
            }
            
            if ($hasGenero) {
                $fields[] = 'genero';
                $placeholders[] = ':genero';
                $params[':genero'] = $data['genero'] ?? $data['gender'] ?? 'general';
            }
            
            if ($hasActivo) {
                $fields[] = 'activo';
                $placeholders[] = ':activo';
                $params[':activo'] = $data['activo'] ?? 1;
            }
            
            $sql = "INSERT INTO categorias (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->db->prepare($sql);
            
            if ($stmt->execute($params)) {
                return $this->db->lastInsertId();
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error creating category: " . $e->getMessage());
            return false;
        }
    }
    
    private function generateSlug($texto) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $texto), '-'));
        
        $sql = "SELECT COUNT(*) FROM categorias WHERE slug = :slug";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        
        if ($stmt->fetchColumn() > 0) {
            $slug .= '-' . time();
        }
        
        return $slug;
    }
    
    // Actualizar producto existente
    public function updateProduct($producto_id, $data) {
        $sql = "UPDATE productos SET 
                codigo_producto = :codigo,
                nombre = :nombre,
                slug = :slug,
                descripcion_corta = :desc_corta,
                descripcion_larga = :desc_larga,
                categoria_id = :categoria,
                precio = :precio,
                precio_oferta = :precio_oferta,
                stock = :stock,
                stock_minimo = :stock_minimo,
                activo = :activo,
                destacado = :destacado,
                nuevo = :nuevo,
                oferta = :oferta,
                fecha_actualizacion = NOW()
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $producto_id,
            ':codigo' => $data['codigo_producto'] ?? null,
            ':nombre' => $data['nombre'],
            ':slug' => $this->generateSlug($data['nombre'] . '-' . $producto_id),
            ':desc_corta' => $data['descripcion_corta'] ?? null,
            ':desc_larga' => $data['descripcion_larga'] ?? null,
            ':categoria' => $data['categoria_id'] ?? null,
            ':precio' => $data['precio'],
            ':precio_oferta' => $data['precio_oferta'] ?? null,
            ':stock' => $data['stock'] ?? 0,
            ':stock_minimo' => $data['stock_minimo'] ?? 5,
            ':activo' => $data['activo'] ?? 1,
            ':destacado' => $data['destacado'] ?? 0,
            ':nuevo' => $data['nuevo'] ?? 0,
            ':oferta' => $data['oferta'] ?? 0
        ]);
    }
    
    // Eliminar producto
    public function deleteProduct($producto_id) {
        try {
            $this->db->beginTransaction();
            
            // Eliminar imágenes del producto
            $sql = "DELETE FROM producto_imagenes WHERE producto_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $producto_id]);
            
            // Eliminar variantes del producto
            $sql = "DELETE FROM producto_variantes WHERE producto_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $producto_id]);
            
            // Eliminar el producto
            $sql = "DELETE FROM productos WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $producto_id]);
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateCategory($categoryId, $data) {
        try {
            // Verificar qué columnas existen en la tabla
            $checkSql = "SHOW COLUMNS FROM categorias";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute();
            $columns = $checkStmt->fetchAll(PDO::FETCH_COLUMN);
            
            $hasGenero = in_array('genero', $columns);
            $hasActivo = in_array('activo', $columns);
            $hasSlug = in_array('slug', $columns);
            
            // Construir consulta SQL dinámicamente
            $updateFields = ['nombre = :nombre'];
            $params = [
                ':id' => $categoryId,
                ':nombre' => $data['nombre']
            ];
            
            if ($hasSlug) {
                $updateFields[] = 'slug = :slug';
                $params[':slug'] = $this->generateSlug($data['nombre']);
            }
            
            if (isset($data['descripcion'])) {
                $updateFields[] = 'descripcion = :descripcion';
                $params[':descripcion'] = $data['descripcion'];
            }
            
            if ($hasGenero) {
                $updateFields[] = 'genero = :genero';
                $params[':genero'] = $data['genero'] ?? $data['gender'] ?? 'general';
            }
            
            if ($hasActivo) {
                $updateFields[] = 'activo = :activo';
                $params[':activo'] = $data['activo'] ?? 1;
            }
            
            // Agregar campos de timestamp si existen
            if (in_array('updated_at', $columns)) {
                $updateFields[] = 'updated_at = NOW()';
            }
            
            $sql = "UPDATE categorias SET " . implode(', ', $updateFields) . " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result && $stmt->rowCount() > 0) {
                return $categoryId;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Error actualizando categoría: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getCategoryById($categoryId) {
        try {
            $sql = "SELECT * FROM categorias WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $categoryId]);
            $category = $stmt->fetch();
            
            if ($category) {
                $category['gender'] = $category['gender'] ?? 'general';
                $category['activo'] = isset($category['activo']) ? (bool)$category['activo'] : true;
            }
            
            return $category;
        } catch (Exception $e) {
            error_log("Error obteniendo categoría por ID: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function deleteCategory($categoryId) {
        try {
            $sql = "DELETE FROM categorias WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([':id' => $categoryId]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error eliminando categoría: " . $e->getMessage());
            throw $e;
        }
    }
}

// Funciones de utilidad
function formatPrice($price) {
    return '$' . number_format($price, 0, ',', '.');
}

function generateProductCode() {
    return 'PROD-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}
?>
