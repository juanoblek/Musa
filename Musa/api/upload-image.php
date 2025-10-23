<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    if (!isset($_FILES['image'])) {
        throw new Exception('No se recibió ningún archivo', 400);
    }

    $file = $_FILES['image'];
    
    // Validar errores de upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error al subir el archivo: ' . $file['error'], 400);
    }

    // Eliminada validación de tamaño - permite archivos de cualquier tamaño

    // Validar tipo de archivo (imágenes y videos)
    $allowedTypes = [
        // Imágenes
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        // Videos
        'video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo', 'video/webm'
    ];
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipo de archivo no permitido. Solo JPG, PNG, GIF, WebP, MP4, MOV, AVI, WebM.', 400);
    }

    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $prefix = str_starts_with($file['type'], 'video/') ? 'video_' : 'product_';
    $fileName = $prefix . uniqid() . '_' . time() . '.' . strtolower($extension);
    
    // Directorio de destino
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $targetPath = $uploadDir . $fileName;
    
    // Mover archivo
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Error al guardar el archivo en el servidor', 500);
    }

    // Optimizar solo si es imagen
    if (str_starts_with($file['type'], 'image/')) {
        optimizeImage($targetPath, $file['type']);
    }

    // URL relativa para la base de datos
    $fileUrl = 'uploads/' . $fileName;
    
    $fileType = str_starts_with($file['type'], 'video/') ? 'video' : 'image';

    echo json_encode([
        'success' => true,
        'message' => ucfirst($fileType) . ' subido correctamente',
        'data' => [
            'fileName' => $fileName,
            'url' => $fileUrl,
            'fullUrl' => $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/Musa/uploads/' . $fileName,
            'size' => $file['size'],
            'type' => $file['type'],
            'fileType' => $fileType
        ]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $e->getCode() ?: 500
    ]);
}

function optimizeImage($filePath, $mimeType) {
    try {
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($filePath);
                if ($image) {
                    imagejpeg($image, $filePath, 85); // 85% quality
                    imagedestroy($image);
                }
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                if ($image) {
                    imagepng($image, $filePath, 6); // Compression level 6
                    imagedestroy($image);
                }
                break;
        }
    } catch (Exception $e) {
        // Si falla la optimización, continuar con la imagen original
        error_log('Error optimizando imagen: ' . $e->getMessage());
    }
}
?>
