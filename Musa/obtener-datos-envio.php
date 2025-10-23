<?php
// Desactivar la visualización de errores
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    // Validar ID de pago
    if (!isset($_GET['payment_id'])) {
        throw new Exception('ID de pago no proporcionado');
    }
    
    $payment_id = $_GET['payment_id'];
    
    // Conexión a la base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener datos de la vista que ya tiene la unión correcta
    $sql = "
        SELECT *
        FROM vista_pedidos_completos 
        WHERE pedido_id = :pedido_id
    ";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['pedido_id' => $payment_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        // Asegurarse de que todos los campos existan
        $data = array_merge([
            'nombre_completo' => 'N/A',
            'email' => 'N/A',
            'telefono' => 'N/A',
            'direccion' => 'N/A',
            'ciudad' => 'N/A',
            'departamento' => 'N/A',
            'codigo_postal' => 'N/A',
            'estado_envio' => 'pendiente',
            'notas_adicionales' => '',
            'productos' => '[]',
            'subtotal' => 0,
            'envio' => 0,
            'total' => 0,
            'estado_pago' => 'pendiente',
            'metodo_pago' => 'N/A'
        ], $data);

        // Formatear datos de envío
        $shippingData = [
            'cliente' => [
                'nombre_completo' => $data['nombre_completo'],
                'email' => $data['email'],
                'telefono' => $data['telefono']
            ],
            'envio' => [
                'direccion' => $data['direccion'] ?? 'N/A',
                'ciudad' => $data['ciudad'] ?? 'N/A',
                'departamento' => $data['departamento'] ?? 'N/A',
                'codigo_postal' => $data['codigo_postal'] ?? 'N/A',
                'estado_envio' => $data['estado_envio'] ?? 'pendiente',
                'notas' => $data['notas_adicionales'] ?? ''
            ],
            'pedido' => [
                'id' => $data['pedido_id'],
                'referencia' => $data['pedido_id'],
                'estado' => $data['estado_pago'],
                'metodo_pago' => $data['metodo_pago'],
                'productos' => json_decode($data['productos'], true),
                'subtotal' => $data['subtotal'],
                'envio' => $data['envio'],
                'total' => $data['total']
            ]
                'notas' => $data['notas']
            ],
            'pedido' => [
                'id' => $data['payment_id'],
                'referencia' => $data['external_reference'],
                'estado' => $data['status'],
                'estado_envio' => $data['shipping_status'],
                'metodo_envio' => $data['shipping_method'],
                'monto' => $data['amount']
            ]
        ];
        
        echo json_encode([
            'success' => true,
            'data' => $shippingData
        ]);
    } else {
        throw new Exception('No se encontraron datos de envío para este pedido');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>