<?php
/**
 * API para manejo de imágenes - M & A MODA ACTUAL
 */

require_once '../config/database.php';

// Headers CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class ImageAPI {
    private $uploadDir;
    private $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    private $maxFileSize = 10 * 1024 * 1024; // 10MB

    public function __construct() {
        $this->uploadDir = dirname(__DIR__) . '/images/';
        
        // Crear directorio si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
        
        error_log("✅ ImageAPI iniciado - Upload dir: " . $this->uploadDir);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        try {
            switch ($method) {
                case 'POST':
                    $this->uploadImage();
                    break;
                case 'GET':
                    $this->getImages();
                    break;
                case 'DELETE':
                    $this->deleteImage();
                    break;
                default:
                    $this->sendError('Método no permitido', 405);
            }
        } catch (Exception $e) {
            error_log("❌ Error en ImageAPI: " . $e->getMessage());
            $this->sendError($e->getMessage(), 500);
        }
    }

    /**
     * Subir imagen
     */
    private function uploadImage() {
        if (!isset($_FILES['image'])) {
            $this->sendError('No se recibió ninguna imagen', 400);
            return;
        }

        $file = $_FILES['image'];
        
        // Validar archivo
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->sendError('Error al subir archivo: ' . $file['error'], 400);
            return;
        }

        if ($file['size'] > $this->maxFileSize) {
            $this->sendError('Archivo demasiado grande. Máximo: 10MB', 400);
            return;
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $this->allowedTypes)) {
            $this->sendError('Tipo de archivo no permitido', 400);
            return;
        }

        // Generar nombre único
        $fileName = 'product_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $this->uploadDir . $fileName;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->sendError('Error al guardar archivo', 500);
            return;
        }

        // Optimizar imagen si es necesario
        $this->optimizeImage($targetPath, $extension);

        $imageUrl = '/images/' . $fileName;
        
        error_log("✅ Imagen subida: " . $imageUrl);
        
        $this->sendSuccess([
            'image_url' => $imageUrl,
            'file_name' => $fileName,
            'size' => $file['size'],
            'type' => $file['type']
        ]);
    }

    /**
     * Obtener lista de imágenes
     */
    private function getImages() {
        $images = [];
        $directory = new DirectoryIterator($this->uploadDir);

        foreach ($directory as $file) {
            if ($file->isDot()) continue;
            
            $extension = strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
            if (in_array($extension, $this->allowedTypes)) {
                $images[] = [
                    'name' => $file->getFilename(),
                    'url' => '/images/' . $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            }
        }

        // Ordenar por fecha de modificación
        usort($images, function($a, $b) {
            return strtotime($b['modified']) - strtotime($a['modified']);
        });

        $this->sendSuccess(['images' => $images]);
    }

    /**
     * Eliminar imagen
     */
    private function deleteImage() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['filename'])) {
            $this->sendError('Nombre de archivo requerido', 400);
            return;
        }

        $fileName = basename($input['filename']); // Seguridad
        $filePath = $this->uploadDir . $fileName;

        if (!file_exists($filePath)) {
            $this->sendError('Archivo no encontrado', 404);
            return;
        }

        if (!unlink($filePath)) {
            $this->sendError('Error al eliminar archivo', 500);
            return;
        }

        error_log("✅ Imagen eliminada: " . $fileName);
        $this->sendSuccess(['message' => 'Imagen eliminada correctamente']);
    }

    /**
     * Optimizar imagen
     */
    private function optimizeImage($path, $extension) {
        // Redimensionar si es muy grande
        $maxWidth = 1200;
        $maxHeight = 1200;

        list($width, $height) = getimagesize($path);
        
        if ($width <= $maxWidth && $height <= $maxHeight) {
            return; // No necesita optimización
        }

        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = intval($width * $ratio);
        $newHeight = intval($height * $ratio);

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($path);
                imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagejpeg($newImage, $path, 85);
                break;
            case 'png':
                $source = imagecreatefrompng($path);
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagepng($newImage, $path, 8);
                break;
        }

        imagedestroy($source);
        imagedestroy($newImage);
    }

    /**
     * Enviar respuesta exitosa
     */
    private function sendSuccess($data) {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
    }

    /**
     * Enviar error
     */
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ]);
    }
}

// Ejecutar API
$api = new ImageAPI();
$api->handleRequest();
?>
