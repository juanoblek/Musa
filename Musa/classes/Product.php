<?php
/**
 * Clase para gestionar Productos
 * Archivo: classes/Product.php
 */

require_once __DIR__ . '/../config/database.php';

class Product {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Obtener todos los productos con paginación
     */
    public function getAll($page = 1, $limit = 20, $filters = []) {
        $offset = ($page - 1) * $limit;
        $where = "WHERE p.id > 0";
        $params = [];

        // Aplicar filtros
        if (!empty($filters['categoria_id'])) {
            $where .= " AND p.categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }

        if (!empty($filters['activo'])) {
            $where .= " AND p.activo = ?";
            $params[] = $filters['activo'];
        }

        if (!empty($filters['buscar'])) {
            $where .= " AND (p.nombre LIKE ? OR p.descripcion LIKE ?)";
            $search = '%' . $filters['buscar'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql = "
            SELECT p.*, c.nombre as categoria_nombre,
                   (SELECT pi.imagen FROM productos_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            {$where}
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->db->select($sql, $params);
    }

    /**
     * Contar total de productos
     */
    public function getCount($filters = []) {
        $where = "WHERE id > 0";
        $params = [];

        if (!empty($filters['categoria_id'])) {
            $where .= " AND categoria_id = ?";
            $params[] = $filters['categoria_id'];
        }

        if (!empty($filters['activo'])) {
            $where .= " AND activo = ?";
            $params[] = $filters['activo'];
        }

        if (!empty($filters['buscar'])) {
            $where .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
            $search = '%' . $filters['buscar'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $sql = "SELECT COUNT(*) as total FROM productos {$where}";
        $result = $this->db->selectOne($sql, $params);
        
        return $result['total'];
    }

    /**
     * Obtener producto por ID
     */
    public function getById($id) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.id = ?
        ";
        
        return $this->db->selectOne($sql, [$id]);
    }

    /**
     * Obtener producto por slug
     */
    public function getBySlug($slug) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.slug = ? AND p.activo = 1
        ";
        
        return $this->db->selectOne($sql, [$slug]);
    }

    /**
     * Crear nuevo producto
     */
    public function create($data) {
        // Generar slug único
        $slug = $this->generateUniqueSlug($data['nombre']);
        
        $sql = "
            INSERT INTO productos (
                nombre, slug, descripcion, descripcion_corta, precio, precio_oferta,
                sku, stock, stock_minimo, categoria_id, peso, dimensiones,
                material, color, talla, genero, temporada, destacado, nuevo, oferta,
                activo, meta_title, meta_description
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $params = [
            $data['nombre'],
            $slug,
            $data['descripcion'] ?? '',
            $data['descripcion_corta'] ?? '',
            $data['precio'],
            $data['precio_oferta'] ?? null,
            $data['sku'] ?? null,
            $data['stock'] ?? 0,
            $data['stock_minimo'] ?? 5,
            $data['categoria_id'] ?? null,
            $data['peso'] ?? 0,
            $data['dimensiones'] ?? '',
            $data['material'] ?? '',
            $data['color'] ?? '',
            $data['talla'] ?? '',
            $data['genero'] ?? 'unisex',
            $data['temporada'] ?? 'todo_año',
            isset($data['destacado']) ? 1 : 0,
            isset($data['nuevo']) ? 1 : 0,
            isset($data['oferta']) ? 1 : 0,
            isset($data['activo']) ? 1 : 0,
            $data['meta_title'] ?? $data['nombre'],
            $data['meta_description'] ?? $data['descripcion_corta'] ?? ''
        ];

        return $this->db->insert($sql, $params);
    }

    /**
     * Actualizar producto
     */
    public function update($id, $data) {
        $sql = "
            UPDATE productos SET
                nombre = ?, descripcion = ?, descripcion_corta = ?, precio = ?, precio_oferta = ?,
                sku = ?, stock = ?, stock_minimo = ?, categoria_id = ?, peso = ?, dimensiones = ?,
                material = ?, color = ?, talla = ?, genero = ?, temporada = ?, destacado = ?,
                nuevo = ?, oferta = ?, activo = ?, meta_title = ?, meta_description = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ";

        $params = [
            $data['nombre'],
            $data['descripcion'] ?? '',
            $data['descripcion_corta'] ?? '',
            $data['precio'],
            $data['precio_oferta'] ?? null,
            $data['sku'] ?? null,
            $data['stock'] ?? 0,
            $data['stock_minimo'] ?? 5,
            $data['categoria_id'] ?? null,
            $data['peso'] ?? 0,
            $data['dimensiones'] ?? '',
            $data['material'] ?? '',
            $data['color'] ?? '',
            $data['talla'] ?? '',
            $data['genero'] ?? 'unisex',
            $data['temporada'] ?? 'todo_año',
            isset($data['destacado']) ? 1 : 0,
            isset($data['nuevo']) ? 1 : 0,
            isset($data['oferta']) ? 1 : 0,
            isset($data['activo']) ? 1 : 0,
            $data['meta_title'] ?? $data['nombre'],
            $data['meta_description'] ?? $data['descripcion_corta'] ?? '',
            $id
        ];

        return $this->db->update($sql, $params);
    }

    /**
     * Eliminar producto
     */
    public function delete($id) {
        // Primero eliminar imágenes asociadas
        $this->deleteProductImages($id);
        
        $sql = "DELETE FROM productos WHERE id = ?";
        return $this->db->delete($sql, [$id]);
    }

    /**
     * Obtener imágenes de un producto
     */
    public function getImages($productId) {
        $sql = "
            SELECT * FROM productos_imagenes 
            WHERE producto_id = ? 
            ORDER BY es_principal DESC, orden ASC
        ";
        
        return $this->db->select($sql, [$productId]);
    }

    /**
     * Agregar imagen a producto
     */
    public function addImage($productId, $imagePath, $altText = '', $esPrincipal = false, $orden = 0) {
        // Si es imagen principal, quitar flag de las demás
        if ($esPrincipal) {
            $this->db->update(
                "UPDATE productos_imagenes SET es_principal = 0 WHERE producto_id = ?",
                [$productId]
            );
        }

        $sql = "
            INSERT INTO productos_imagenes (producto_id, imagen, alt_text, es_principal, orden)
            VALUES (?, ?, ?, ?, ?)
        ";

        return $this->db->insert($sql, [
            $productId,
            $imagePath,
            $altText,
            $esPrincipal ? 1 : 0,
            $orden
        ]);
    }

    /**
     * Eliminar imagen de producto
     */
    public function deleteImage($imageId) {
        // Obtener información de la imagen antes de eliminarla
        $image = $this->db->selectOne("SELECT * FROM productos_imagenes WHERE id = ?", [$imageId]);
        
        if ($image) {
            // Eliminar archivo físico
            $imagePath = PRODUCTS_IMAGE_PATH . $image['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Eliminar de base de datos
            return $this->db->delete("DELETE FROM productos_imagenes WHERE id = ?", [$imageId]);
        }
        
        return false;
    }

    /**
     * Eliminar todas las imágenes de un producto
     */
    private function deleteProductImages($productId) {
        $images = $this->getImages($productId);
        
        foreach ($images as $image) {
            $imagePath = PRODUCTS_IMAGE_PATH . $image['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        return $this->db->delete("DELETE FROM productos_imagenes WHERE producto_id = ?", [$productId]);
    }

    /**
     * Generar slug único
     */
    private function generateUniqueSlug($nombre) {
        $baseSlug = generateSlug($nombre);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verificar si slug existe
     */
    private function slugExists($slug) {
        $result = $this->db->selectOne("SELECT id FROM productos WHERE slug = ?", [$slug]);
        return $result !== false;
    }

    /**
     * Obtener productos destacados
     */
    public function getFeatured($limit = 8) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre,
                   (SELECT pi.imagen FROM productos_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.destacado = 1 AND p.activo = 1
            ORDER BY p.created_at DESC
            LIMIT ?
        ";

        return $this->db->select($sql, [$limit]);
    }

    /**
     * Obtener productos más vendidos
     */
    public function getBestSellers($limit = 8) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre,
                   (SELECT pi.imagen FROM productos_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 AND p.ventas_totales > 0
            ORDER BY p.ventas_totales DESC
            LIMIT ?
        ";

        return $this->db->select($sql, [$limit]);
    }

    /**
     * Buscar productos
     */
    public function search($query, $limit = 20) {
        $sql = "
            SELECT p.*, c.nombre as categoria_nombre,
                   (SELECT pi.imagen FROM productos_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal,
                   MATCH(p.nombre, p.descripcion, p.descripcion_corta) AGAINST(? IN NATURAL LANGUAGE MODE) as relevancia
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 AND (
                MATCH(p.nombre, p.descripcion, p.descripcion_corta) AGAINST(? IN NATURAL LANGUAGE MODE) OR
                p.nombre LIKE ? OR 
                p.descripcion LIKE ?
            )
            ORDER BY relevancia DESC, p.created_at DESC
            LIMIT ?
        ";

        $searchTerm = '%' . $query . '%';
        
        return $this->db->select($sql, [$query, $query, $searchTerm, $searchTerm, $limit]);
    }

    /**
     * Incrementar visualizaciones
     */
    public function incrementViews($id) {
        $sql = "UPDATE productos SET visualizaciones = visualizaciones + 1 WHERE id = ?";
        return $this->db->update($sql, [$id]);
    }

    /**
     * Verificar stock disponible
     */
    public function checkStock($id, $cantidad = 1) {
        $producto = $this->getById($id);
        
        if (!$producto) {
            return false;
        }

        return $producto['stock'] >= $cantidad;
    }

    /**
     * Reducir stock (para ventas)
     */
    public function reduceStock($id, $cantidad) {
        $sql = "
            UPDATE productos 
            SET stock = stock - ?, ventas_totales = ventas_totales + ? 
            WHERE id = ? AND stock >= ?
        ";
        
        return $this->db->update($sql, [$cantidad, $cantidad, $id, $cantidad]);
    }
}

?>
