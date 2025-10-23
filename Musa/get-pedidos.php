<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'musa_store';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener parámetros de filtro
    $estado = $_GET['estado'] ?? '';
    $fecha = $_GET['fecha'] ?? '';
    $buscar = $_GET['buscar'] ?? '';
    
    // Construir consulta SQL
    $sql = "SELECT * FROM pedidos WHERE 1=1";
    $params = [];
    
    if (!empty($estado)) {
        $sql .= " AND status = :estado";
        $params[':estado'] = $estado;
    }
    
    if (!empty($fecha)) {
        $sql .= " AND DATE(created_at) = :fecha";
        $params[':fecha'] = $fecha;
    }
    
    if (!empty($buscar)) {
        $sql .= " AND (payment_id LIKE :buscar OR payer_email LIKE :buscar OR external_reference LIKE :buscar)";
        $params[':buscar'] = "%$buscar%";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 100";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'pedidos' => $pedidos,
        'total' => count($pedidos)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>