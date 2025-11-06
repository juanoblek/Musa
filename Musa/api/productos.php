<?php
/**
 * API de Productos - MUSA MODA
 * Endpoint para obtener y gestionar productos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config-global.php';

try {
    // Obtener conexión a la base de datos
    $dbConfig = GlobalConfig::getDatabaseConfig();
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Método GET - Obtener productos
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("SELECT * FROM productos WHERE activo = 1 ORDER BY id DESC");
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Procesar imágenes y URLs
        foreach ($productos as &$producto) {
            $producto['imagenes'] = json_decode($producto['imagenes'] ?? '[]');
            $producto['videos'] = json_decode($producto['videos'] ?? '[]');
            $producto['caracteristicas'] = json_decode($producto['caracteristicas'] ?? '[]');
            $producto['precios'] = json_decode($producto['precios'] ?? '{}');
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $productos
        ]);
    } 
    // Método POST - Crear producto
    else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $stmt = $pdo->prepare("
            INSERT INTO productos (
                nombre, descripcion, precio, imagenes, videos, 
                caracteristicas, precios, categoria, activo
            ) VALUES (
                :nombre, :descripcion, :precio, :imagenes, :videos,
                :caracteristicas, :precios, :categoria, 1
            )
        ");
        
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':descripcion' => $data['descripcion'],
            ':precio' => $data['precio'],
            ':imagenes' => json_encode($data['imagenes'] ?? []),
            ':videos' => json_encode($data['videos'] ?? []),
            ':caracteristicas' => json_encode($data['caracteristicas'] ?? []),
            ':precios' => json_encode($data['precios'] ?? []),
            ':categoria' => $data['categoria']
        ]);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Producto creado correctamente',
            'id' => $pdo->lastInsertId()
        ]);
    }
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}