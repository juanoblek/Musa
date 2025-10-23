<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit();
}

require_once 'database.php';

try {
    // Conexión directa a la base de datos (PRODUCCIÓN HOSTING)
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = ''; // CONTRASEÑA REAL DEL HOSTING

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener ID del producto desde la URL
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if (!$product_id) {
        throw new Exception('ID del producto requerido');
    }
    
    $productManager = new ProductManager();
    
    // Verificar que el producto existe
    $existing_products = $productManager->getAllProducts();
    $product_exists = false;
    
    foreach ($existing_products as $product) {
        if ($product['id'] == $product_id) {
            $product_exists = true;
            break;
        }
    }
    
    if (!$product_exists) {
        throw new Exception('Producto no encontrado');
    }
    
    // Eliminar el producto
    $success = $productManager->deleteProduct($product_id);
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto eliminado correctamente',
            'id' => $product_id
        ]);
    } else {
        throw new Exception('Error al eliminar el producto');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'success' => false
    ]);
}
?>
