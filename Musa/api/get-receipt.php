<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Conectar a la base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (!isset($_GET['order_id'])) {
        throw new Exception('ID de pedido requerido');
    }
    
    $order_id = $_GET['order_id'];
    
    // Buscar el comprobante en la tabla pedidos (donde realmente se guardan)
    $stmt = $pdo->prepare("SELECT pedido_id, datos_pago, metodo_pago, total FROM pedidos WHERE id = ?");
    $stmt->execute([$order_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        throw new Exception('No se encontró el pedido');
    }
    
    // Decodificar datos_pago
    $datos_pago = json_decode($pedido['datos_pago'], true);
    
    if (!$datos_pago || !isset($datos_pago['receipt_image'])) {
        throw new Exception('No se encontró comprobante para este pedido');
    }
    
    // Verificar que existe el archivo de imagen
    $image_path = '../uploads/comprobantes/' . $datos_pago['receipt_image'];
    if (!file_exists($image_path)) {
        throw new Exception('El archivo del comprobante no existe');
    }
    
    // Preparar datos del receipt con toda la información necesaria
    $receipt = [
        'pedido_id' => $pedido['pedido_id'],
        'metodo_pago' => $pedido['metodo_pago'],
        'comprobante_imagen' => $datos_pago['receipt_image'],
        'nombre_remitente' => $datos_pago['sender_name'] ?? 'No especificado',
        'monto' => $pedido['total'],
        'numero_destino' => '3232212316', // Número fijo de Nequi/Daviplata
        'referencia' => $pedido['pedido_id'],
        'payment_data' => $datos_pago
    ];
    
    echo json_encode([
        'success' => true,
        'receipt' => $receipt,
        'image_path' => 'uploads/comprobantes/' . $receipt['comprobante_imagen']
    ]);
    
} catch (Exception $e) {
    error_log("Error en get-receipt.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>