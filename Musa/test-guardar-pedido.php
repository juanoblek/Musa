<?php
// Test manual para la API de guardar pedidos
header('Content-Type: application/json');

$pedidoTest = [
    'pago_exitoso' => true,
    'productos' => [
        [
            'id' => 1,
            'nombre' => 'Camiseta Casual Test',
            'talla' => 'M',
            'color' => 'Azul',
            'precio' => 35999,
            'cantidad' => 1
        ],
        [
            'id' => 2,
            'nombre' => 'Pantalón Jean Test',
            'talla' => '32',
            'color' => 'Negro',
            'precio' => 42000,
            'cantidad' => 1
        ]
    ],
    'subtotal' => 77999,
    'envio' => 0, // ENVÍO GRATIS
    'total' => 77999, // Solo productos, sin envío
    'metodo_pago' => 'tarjeta_credito',
    'datos_pago' => [
        'tarjeta_enmascarada' => '•••• •••• •••• 1234',
        'titular' => 'Juan Pérez Test',
        'email' => 'test@example.com',
        'fecha_pago' => date('c')
    ],
    'envio' => [
        'nombre_completo' => 'Juan Pérez Test',
        'email' => 'test@example.com',
        'telefono' => '573001234567',
        'departamento' => 'Cundinamarca',
        'ciudad' => 'Bogotá',
        'direccion' => 'Calle 123 #45-67, Barrio Centro',
        'codigo_postal' => '110111',
        'notas_adicionales' => 'Entregar en horarios de oficina (8am-5pm)'
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/Musa/api/guardar-pedido.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pedidoTest));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo json_encode([
    'test_data' => $pedidoTest,
    'api_response' => json_decode($response, true),
    'http_code' => $httpCode
], JSON_PRETTY_PRINT);
?>