<?php
require_once 'database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


    // Agregar logging de debug
    error_log("=== CREAR PRODUCTO - INICIO ===");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));

    // Conexión directa a la base de datos (PRODUCCIÓN HOSTING)
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = ''; // CONTRASEÑA REAL DEL HOSTING

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $data = [
        'nombre' => $_POST['nombre'] ?? '',
        'codigo_producto' => $_POST['codigo_producto'] ?? generateProductCode(),
        'descripcion_corta' => $_POST['descripcion_corta'] ?? '',
        'descripcion_larga' => $_POST['descripcion_larga'] ?? '',
        'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
        'precio' => (float)$_POST['precio'],
        'precio_oferta' => !empty($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null,
        'stock' => (int)($_POST['stock'] ?? 0),
        'activo' => 1,
        'destacado' => isset($_POST['destacado']) ? 1 : 0,
        'nuevo' => isset($_POST['nuevo']) ? 1 : 0,
        'oferta' => isset($_POST['oferta']) ? 1 : 0
    ];

    error_log("Datos procesados: " . print_r($data, true));

    // Validaciones básicas
    if (empty($data['nombre'])) {
        throw new Exception('El nombre del producto es obligatorio');
    }

    if ($data['precio'] <= 0) {
        throw new Exception('El precio debe ser mayor a 0');
    }

    error_log("Validaciones básicas pasadas");
    
    $productManager = new ProductManager();
    error_log("ProductManager creado");
    
    // Crear el producto
    if ($productManager->createProduct($data)) {
        error_log("Producto creado exitosamente");
        $db = DatabaseConfig::getConnection();
        $producto_id = $db->lastInsertId();
        
        // Subir imagen principal si existe
        if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === 0) {
            error_log("Subiendo imagen principal");
            $productManager->uploadProductImage($producto_id, $_FILES['imagen_principal'], 1);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'producto_id' => $producto_id
        ]);
    } else {
        error_log("Error: createProduct devolvió false");
        throw new Exception('Error al crear el producto en la base de datos');
    }

} catch (Exception $e) {
    error_log("Exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Error: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
