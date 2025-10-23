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

    // Conexión directa a la base de datos (PRODUCCIÓN HOSTING)
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = ''; // CONTRASEÑA REAL DEL HOSTING

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = [
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];

    // Validaciones básicas
    if (empty($data['nombre'])) {
        throw new Exception('El nombre de la categoría es obligatorio');
    }

    $categoryManager = new CategoryManager();
    
    if ($categoryManager->createCategory($data)) {
        echo json_encode([
            'success' => true,
            'message' => 'Categoría creada exitosamente'
        ]);
    } else {
        throw new Exception('Error al crear la categoría en la base de datos');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
