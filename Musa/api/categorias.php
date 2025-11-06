<?php
// api/categorias.php - API para gestión de categorías

// Importar configuración global
require_once __DIR__ . '/../config/config-global.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Función para enviar respuesta JSON
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Obtener configuración de base de datos según el entorno
$dbConfig = GlobalConfig::getDatabaseConfig();
$host = $dbConfig['host'];
$dbname = $dbConfig['dbname'];
$username = $dbConfig['username'];
$password = $dbConfig['password'];

error_log("=== API CATEGORIAS ===");
error_log("Entorno: " . (GlobalConfig::isProduction() ? 'PRODUCCIÓN' : 'DESARROLLO'));
error_log("Base de datos: $host / $dbname");

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
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
            if (isset($_GET['id'])) {
                // Obtener una categoría específica
                $id = $_GET['id'];
                $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $categoria = $stmt->fetch();
                
                if ($categoria) {
                    sendJsonResponse(['success' => true, 'data' => $categoria]);
                } else {
                    sendJsonResponse(['success' => false, 'message' => 'Categoría no encontrada'], 404);
                }
            } else {
                // Obtener todas las categorías
                $showAll = isset($_GET['admin']) && $_GET['admin'] === 'true';
                
                if ($showAll) {
                    $stmt = $db->prepare("SELECT * FROM categories ORDER BY sort_order ASC, name ASC");
                } else {
                    $stmt = $db->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY sort_order ASC, name ASC");
                }
                
                $stmt->execute();
                $categorias = $stmt->fetchAll();
                
                sendJsonResponse(['success' => true, 'data' => $categorias, 'count' => count($categorias)]);
            }
            break;
            
        case 'POST':
            // Crear nueva categoría
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['name']) || empty(trim($input['name']))) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                break;
            }
            
            $id = $input['id'] ?? strtolower(str_replace(' ', '-', trim($input['name'])));
            $name = trim($input['name']);
            $slug = $input['slug'] ?? strtolower(str_replace(' ', '-', $name));
            $description = $input['description'] ?? '';
            $gender = $input['gender'] ?? 'unisex';
            $sort_order = $input['sort_order'] ?? 0;
            $status = $input['status'] ?? 'active';
            
            try {
                $stmt = $db->prepare("INSERT INTO categories (id, name, slug, description, gender, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $name, $slug, $description, $gender, $sort_order, $status]);
                
                echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente', 'id' => $id]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                    echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese ID o slug']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear categoría: ' . $e->getMessage()]);
                }
            }
            break;
            
        case 'PUT':
            // Actualizar categoría
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($input['id']) || empty($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
                break;
            }
            
            $id = $input['id'];
            $name = $input['name'] ?? '';
            $slug = $input['slug'] ?? '';
            $description = $input['description'] ?? '';
            $gender = $input['gender'] ?? 'unisex';
            $sort_order = $input['sort_order'] ?? 0;
            $status = $input['status'] ?? 'active';
            
            if (empty(trim($name))) {
                echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
                break;
            }
            
            try {
                $stmt = $db->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, gender = ?, sort_order = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $gender, $sort_order, $status, $id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Categoría actualizada exitosamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Categoría no encontrada o sin cambios']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar categoría: ' . $e->getMessage()]);
            }
            break;
            
        case 'DELETE':
            // Eliminar categoría
            if (!isset($_GET['id']) || empty($_GET['id'])) {
                sendJsonResponse(['success' => false, 'message' => 'ID de categoría requerido'], 400);
            }
            
            $id = $_GET['id'];
            
            try {
                // Verificar si hay productos asociados
                $stmt = $db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
                $stmt->execute([$id]);
                $productCount = $stmt->fetchColumn();
                
                if ($productCount > 0) {
                    sendJsonResponse(['success' => false, 'message' => "No se puede eliminar la categoría porque tiene $productCount producto(s) asociado(s)"], 400);
                }
                
                // Eliminar categoría
                $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                
                if ($stmt->rowCount() > 0) {
                    sendJsonResponse(['success' => true, 'message' => 'Categoría eliminada exitosamente']);
                } else {
                    sendJsonResponse(['success' => false, 'message' => 'Categoría no encontrada'], 404);
                }
            } catch (PDOException $e) {
                error_log("Error eliminando categoría: " . $e->getMessage());
                sendJsonResponse(['success' => false, 'message' => 'Error al eliminar categoría'], 500);
            }
            break;
            
        default:
            sendJsonResponse(['success' => false, 'message' => 'Método no permitido'], 405);
            break;
    }
    
} catch (Exception $e) {
    error_log("Error en categorias.php: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Error del servidor'], 500);
}
?>
