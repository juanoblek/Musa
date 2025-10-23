<?php
// api/carousel-manager.php - Gestión del carrusel principal
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'janithal_musa_moda';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Error de conexión: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit;
}

// Crear tabla de carrusel si no existe
try {
    $createTable = "
        CREATE TABLE IF NOT EXISTS carousel_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_path VARCHAR(500) NOT NULL,
            title VARCHAR(200) DEFAULT '',
            subtitle VARCHAR(300) DEFAULT '',
            location VARCHAR(200) DEFAULT '',
            button_text VARCHAR(100) DEFAULT '',
            button_link VARCHAR(500) DEFAULT '',
            is_active BOOLEAN DEFAULT FALSE,
            slide_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ";
    $pdo->exec($createTable);
} catch (PDOException $e) {
    error_log("Error creando tabla carousel_slides: " . $e->getMessage());
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getCarouselSlides($pdo);
        break;
    case 'POST':
        // Verificar si es subida de imagen o guardado de slides
        if (isset($_POST['action']) && $_POST['action'] === 'upload_image') {
            $result = uploadCarouselImage();
            echo json_encode($result);
        } else {
            saveCarouselSlides($pdo);
        }
        break;
    case 'DELETE':
        deleteCarouselSlide($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        break;
}

function getCarouselSlides($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM carousel_slides ORDER BY slide_order ASC, id ASC");
        $stmt->execute();
        $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Si no hay slides, insertar los predeterminados
        if (empty($slides)) {
            insertDefaultSlides($pdo);
            $stmt->execute();
            $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Transformar los datos para el frontend
        $slidesFormatted = array_map(function($slide) {
            return [
                'id' => $slide['id'],
                'image' => $slide['image_path'],
                'title' => $slide['title'],
                'subtitle' => $slide['subtitle'],
                'location' => $slide['location'],
                'buttonText' => $slide['button_text'],
                'buttonLink' => $slide['button_link'],
                'active' => (bool)$slide['is_active'],
                'order' => $slide['slide_order']
            ];
        }, $slides);
        
        echo json_encode([
            'success' => true,
            'slides' => $slidesFormatted
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener slides del carrusel',
            'error' => $e->getMessage()
        ]);
    }
}

function saveCarouselSlides($pdo) {
    try {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
            return;
        }
        
        if (!isset($input['slides']) || !is_array($input['slides'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Datos de slides inválidos. Se esperaba un array de slides.'
            ]);
            return;
        }
        
        $slides = $input['slides'];
        
        // Comenzar transacción
        $pdo->beginTransaction();
        
        // Limpiar slides existentes
        $pdo->exec("DELETE FROM carousel_slides");
        
        // Insertar nuevos slides
        $stmt = $pdo->prepare("
            INSERT INTO carousel_slides (
                image_path, title, subtitle, location, 
                button_text, button_link, is_active, slide_order
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($slides as $index => $slide) {
            $stmt->execute([
                $slide['image'] ?? '',
                $slide['title'] ?? '',
                $slide['subtitle'] ?? '',
                $slide['location'] ?? '',
                $slide['buttonText'] ?? '',
                $slide['buttonLink'] ?? '',
                $slide['active'] ?? false,
                $index + 1
            ]);
        }
        
        // Confirmar transacción
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Carrusel actualizado exitosamente',
            'slides_count' => count($slides)
        ]);
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al guardar slides del carrusel',
            'error' => $e->getMessage()
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Error en los datos proporcionados',
            'error' => $e->getMessage()
        ]);
    }
}

function deleteCarouselSlide($pdo) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de slide requerido']);
            return;
        }
        
        $slideId = $input['id'];
        
        $stmt = $pdo->prepare("DELETE FROM carousel_slides WHERE id = ?");
        $stmt->execute([$slideId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Slide eliminado exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Slide no encontrado'
            ]);
        }
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar slide',
            'error' => $e->getMessage()
        ]);
    }
}

function insertDefaultSlides($pdo) {
    $defaultSlides = [
        [
            'image_path' => 'images/slide/Coleccion2.png',
            'title' => 'Musa',
            'subtitle' => '',
            'location' => 'CLL 3 20A39 Madrid, Cundinamarca',
            'button_text' => '',
            'button_link' => '',
            'is_active' => false,
            'slide_order' => 1
        ],
        [
            'image_path' => 'images/slide/Coleccion3.png',
            'title' => 'Arion',
            'subtitle' => '',
            'location' => '',
            'button_text' => '',
            'button_link' => '',
            'is_active' => true,
            'slide_order' => 2
        ],
        [
            'image_path' => 'images/slide/Coleccion1.png',
            'title' => 'MA',
            'subtitle' => '',
            'location' => '',
            'button_text' => '',
            'button_link' => '',
            'is_active' => false,
            'slide_order' => 3
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO carousel_slides (
            image_path, title, subtitle, location, 
            button_text, button_link, is_active, slide_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    foreach ($defaultSlides as $slide) {
        $stmt->execute([
            $slide['image_path'],
            $slide['title'],
            $slide['subtitle'],
            $slide['location'],
            $slide['button_text'],
            $slide['button_link'],
            $slide['is_active'],
            $slide['slide_order']
        ]);
    }
}

// Función para subir nuevas imágenes del carrusel
function uploadCarouselImage() {
    if (!isset($_FILES['image'])) {
        return ['success' => false, 'message' => 'No se recibió imagen'];
    }
    
    $file = $_FILES['image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 8 * 1024 * 1024; // 8MB
    
    // Validaciones
    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de archivo no permitido'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Archivo demasiado grande (máx. 8MB)'];
    }
    
    // Crear directorio si no existe
    $uploadDir = '../images/slide/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generar nombre único
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'carousel_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => 'images/slide/' . $filename,
            'url' => 'images/slide/' . $filename
        ];
    } else {
        return ['success' => false, 'message' => 'Error al subir archivo'];
    }
}

// Manejo de upload de imágenes
if (isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    $result = uploadCarouselImage();
    echo json_encode($result);
    exit;
}
?>