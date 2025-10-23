<?php
require_once 'database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $producto_id = $_POST['id'] ?? null;
    if (!$producto_id) {
        throw new Exception('ID de producto requerido para actualización');
    }

    $data = [
        'nombre' => $_POST['nombre'] ?? '',
        'codigo_producto' => $_POST['codigo_producto'] ?? '',
        'descripcion_corta' => $_POST['descripcion_corta'] ?? '',
        'categoria_id' => !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null,
        'precio' => (float)$_POST['precio'],
        'precio_oferta' => !empty($_POST['precio_oferta']) ? (float)$_POST['precio_oferta'] : null,
        'stock' => (int)($_POST['stock'] ?? 0),
        'stock_minimo' => (int)($_POST['stock_minimo'] ?? 5),
        'activo' => isset($_POST['activo']) ? (int)$_POST['activo'] : 1,
        'destacado' => isset($_POST['destacado']) ? (int)$_POST['destacado'] : 0,
        'nuevo' => isset($_POST['nuevo']) ? (int)$_POST['nuevo'] : 0,
        'oferta' => isset($_POST['oferta']) ? (int)$_POST['oferta'] : 0
    ];

    // Validaciones básicas
    if (empty($data['nombre'])) {
        throw new Exception('El nombre del producto es obligatorio');
    }

    if ($data['precio'] <= 0) {
        throw new Exception('El precio debe ser mayor a 0');
    }

    $productManager = new ProductManager();
    
    // Actualizar el producto
    if ($productManager->updateProduct($producto_id, $data)) {
        // Subir imagen principal si existe
        if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === 0) {
            $productManager->uploadProductImage($producto_id, $_FILES['imagen_principal'], 1);
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Producto actualizado correctamente',
            'producto_id' => $producto_id
        ]);
    } else {
        throw new Exception('Error al actualizar el producto en la base de datos');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Función para generar código de producto único
function generateProductCode() {
    return 'PROD-' . strtoupper(uniqid());
}
?>
