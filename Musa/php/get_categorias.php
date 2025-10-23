<?php
require_once 'php/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $categoryManager = new CategoryManager();
    $categorias = $categoryManager->getAllCategories();
    
    echo json_encode([
        'success' => true,
        'categories' => $categorias,  // Frontend espera 'categories'
        'categorias' => $categorias,  // Mantenemos compatibilidad
        'total' => count($categorias)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage(),
        'categorias' => []
    ]);
}
?>
