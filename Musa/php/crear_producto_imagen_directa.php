<?php
require_once 'database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Debug logging
error_log("=== CREAR PRODUCTO (IMAGEN DIRECTA) - INICIO ===");
error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Conexión directa a la base de datos (PRODUCCIÓN HOSTING)
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = ''; // CONTRASEÑA REAL DEL HOSTING

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener datos del POST
    $nombre = $_POST['nombre'] ?? '';
    $codigo_producto = $_POST['codigo_producto'] ?? 'PROD-' . time();
    $descripcion = $_POST['descripcion_larga'] ?? $_POST['descripcion_corta'] ?? '';
    $descripcion_corta = $_POST['descripcion_corta'] ?? '';
    $precio = floatval($_POST['precio'] ?? 0);
    $precio_oferta = !empty($_POST['precio_oferta']) ? floatval($_POST['precio_oferta']) : null;
    $stock = intval($_POST['stock'] ?? 0);
    $stock_minimo = intval($_POST['stock_minimo'] ?? 5);
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    $genero = $_POST['genero'] ?? 'unisex';
    $activo = isset($_POST['activo']) ? 1 : 1; // Por defecto activo
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $nuevo = isset($_POST['nuevo']) ? 1 : 0;
    $oferta = isset($_POST['oferta']) ? 1 : 0;

    error_log("Datos procesados - Nombre: $nombre, Precio: $precio, Categoría: $categoria_id");

    // Validaciones básicas
    if (empty($nombre)) {
        throw new Exception('El nombre del producto es obligatorio');
    }
    
    if ($precio <= 0) {
        throw new Exception('El precio debe ser mayor a 0');
    }

    // Generar slug único
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $nombre), '-'));
    
    // Verificar si el slug existe y hacerlo único
    $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM productos WHERE slug = ?");
    $checkSlug->execute([$slug]);
    if ($checkSlug->fetchColumn() > 0) {
        $slug .= '-' . time() . '-' . rand(1000, 9999);
    }

    // Manejar subida de imagen
    $imagePath = null;
    
    // Buscar imagen en diferentes campos posibles
    $imageFile = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imageFile = $_FILES['imagen'];
        error_log("Imagen encontrada en campo 'imagen'");
    } elseif (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
        $imageFile = $_FILES['imagen_principal'];
        error_log("Imagen encontrada en campo 'imagen_principal'");
    } elseif (isset($_FILES['productImage']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
        $imageFile = $_FILES['productImage'];
        error_log("Imagen encontrada en campo 'productImage'");
    }

    if ($imageFile) {
        error_log("Procesando imagen: " . $imageFile['name'] . " (Tamaño: " . $imageFile['size'] . ")");
        
        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $imageFile['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, WEBP');
        }

        // Validar tamaño (máximo 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($imageFile['size'] > $maxSize) {
            throw new Exception('La imagen es demasiado grande. Máximo 5MB');
        }

        // Crear directorio si no existe
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de uploads');
            }
        }

        // Generar nombre único para la imagen
        $extension = strtolower(pathinfo($imageFile['name'], PATHINFO_EXTENSION));
        $filename = 'producto_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $imagePath = $uploadDir . $filename;

        // Mover archivo al directorio de uploads
        if (!move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
            throw new Exception('Error al subir la imagen');
        }
        
        error_log("Imagen subida exitosamente a: $imagePath");
    } else {
        error_log("No se encontró imagen para subir");
    }

    // Insertar producto en la base de datos
    $sql = "INSERT INTO productos (
        nombre, 
        slug, 
        codigo_producto,
        descripcion, 
        descripcion_corta, 
        imagen,
        precio, 
        precio_oferta, 
        stock, 
        stock_minimo,
        categoria_id, 
        genero,
        activo,
        destacado, 
        nuevo, 
        oferta
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $nombre,
        $slug,
        $codigo_producto,
        $descripcion,
        $descripcion_corta,
        $imagePath, // Guardar la ruta de la imagen directamente
        $precio,
        $precio_oferta,
        $stock,
        $stock_minimo,
        $categoria_id,
        $genero,
        $activo,
        $destacado,
        $nuevo,
        $oferta
    ]);

    if ($result) {
        $producto_id = $pdo->lastInsertId();
        
        // Obtener el producto recién creado para verificar
        $getProduct = $pdo->prepare("
            SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.id = ?
        ");
        $getProduct->execute([$producto_id]);
        $producto = $getProduct->fetch(PDO::FETCH_ASSOC);

        error_log("Producto creado exitosamente con ID: $producto_id");
        if ($imagePath) {
            error_log("Imagen guardada en BD: $imagePath");
        }

        echo json_encode([
            'success' => true,
            'message' => 'Producto creado exitosamente',
            'producto_id' => $producto_id,
            'imagen_path' => $imagePath,
            'producto' => $producto,
            'debug' => [
                'imagen_recibida' => $imageFile !== null,
                'imagen_size' => $imageFile ? $imageFile['size'] : 0,
                'imagen_name' => $imageFile ? $imageFile['name'] : 'ninguna',
                'imagen_guardada_en_bd' => $imagePath,
                'post_keys' => array_keys($_POST),
                'files_keys' => array_keys($_FILES)
            ]
        ]);
    } else {
        // Si falla la inserción, eliminar la imagen subida
        if ($imagePath && file_exists($imagePath)) {
            unlink($imagePath);
        }
        throw new Exception('Error al crear el producto en la base de datos');
    }

} catch (Exception $e) {
    // Si hay error, eliminar la imagen subida si existe
    if (isset($imagePath) && $imagePath && file_exists($imagePath)) {
        unlink($imagePath);
    }
    
    error_log("Exception: " . $e->getMessage());
    error_log("Exception trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => [
            'POST_data' => $_POST,
            'FILES_data' => array_keys($_FILES),
            'error_line' => $e->getLine(),
            'error_file' => $e->getFile()
        ]
    ]);
}

function generateProductCode() {
    return 'PROD-' . time() . '-' . rand(1000, 9999);
}
?>
