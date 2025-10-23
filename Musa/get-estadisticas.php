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
    
    // Estadísticas por estado
    $stmt = $pdo->query("
        SELECT 
            status,
            COUNT(*) as cantidad,
            SUM(amount) as total_monto
        FROM pedidos 
        GROUP BY status
    ");
    $estadisticas_estado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Estadísticas generales
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_pedidos,
            SUM(amount) as total_ventas,
            AVG(amount) as promedio_venta,
            MAX(amount) as venta_mayor,
            MIN(amount) as venta_menor
        FROM pedidos
    ");
    $estadisticas_generales = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Pedidos de hoy
    $stmt = $pdo->query("
        SELECT COUNT(*) as pedidos_hoy 
        FROM pedidos 
        WHERE DATE(created_at) = CURDATE()
    ");
    $pedidos_hoy = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Procesar estadísticas por estado
    $stats = [
        'aprobados' => 0,
        'pendientes' => 0,
        'rechazados' => 0,
        'total_pedidos' => intval($estadisticas_generales['total_pedidos']),
        'total_ventas' => floatval($estadisticas_generales['total_ventas']),
        'promedio_venta' => floatval($estadisticas_generales['promedio_venta']),
        'venta_mayor' => floatval($estadisticas_generales['venta_mayor']),
        'venta_menor' => floatval($estadisticas_generales['venta_menor']),
        'pedidos_hoy' => intval($pedidos_hoy['pedidos_hoy'])
    ];
    
    foreach ($estadisticas_estado as $estado) {
        switch ($estado['status']) {
            case 'approved':
                $stats['aprobados'] = intval($estado['cantidad']);
                break;
            case 'pending':
                $stats['pendientes'] = intval($estado['cantidad']);
                break;
            case 'rejected':
                $stats['rechazados'] = intval($estado['cantidad']);
                break;
        }
    }
    
    // Estadísticas de métodos de pago
    $stmt = $pdo->query("
        SELECT 
            payment_method,
            COUNT(*) as cantidad
        FROM pedidos 
        GROUP BY payment_method
        ORDER BY cantidad DESC
    ");
    $metodos_pago = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ventas por día (últimos 7 días)
    $stmt = $pdo->query("
        SELECT 
            DATE(created_at) as fecha,
            COUNT(*) as pedidos,
            SUM(amount) as ventas
        FROM pedidos 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY fecha DESC
    ");
    $ventas_diarias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'metodos_pago' => $metodos_pago,
        'ventas_diarias' => $ventas_diarias
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