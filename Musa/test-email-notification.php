<?php
/**
 * Verificar sistema de notificaciones de email
 */
try {
    require_once 'api/email-notification-api.php';
    
    echo "<h2>Test del Sistema de Notificación por Email</h2>";
    
    // Datos de prueba
    $pedidoTest = [
        'pedido_id' => 'TEST-' . date('Ymd-His'),
        'envio' => [
            'nombre_completo' => 'Cliente de Prueba',
            'email' => 'cliente@test.com',
            'telefono' => '123456789',
            'direccion' => 'Calle 123',
            'ciudad' => 'Bogotá',
            'departamento' => 'Cundinamarca'
        ],
        'productos' => [
            [
                'nombre' => 'Producto de Prueba',
                'precio' => 50000,
                'cantidad' => 2,
                'color' => 'Azul',
                'talla' => 'M'
            ]
        ],
        'subtotal' => 100000,
        'costo_envio' => 0,
        'total' => 100000
    ];
    
    echo "<h3>Datos del pedido de prueba:</h3>";
    echo "<pre>" . json_encode($pedidoTest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    
    // Enviar notificación
    $emailNotification = new EmailNotificationAPI();
    $result = $emailNotification->sendNewOrderNotification($pedidoTest);
    
    echo "<h3>Resultado:</h3>";
    if ($result) {
        echo "<p style='color: green;'>✅ Notificación procesada exitosamente</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Notificación guardada en archivo de respaldo</p>";
    }
    
    // Verificar archivo de log
    $logFile = 'logs/email-notifications.log';
    if (file_exists($logFile)) {
        echo "<h3>Contenido del archivo de notificaciones:</h3>";
        echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
        echo htmlspecialchars(file_get_contents($logFile));
        echo "</pre>";
    } else {
        echo "<p>No se encontró archivo de log (esto es normal si el envío fue exitoso)</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>Nota:</strong> En desarrollo, el sistema simula el envío de emails y los registra en logs.</p>";
    echo "<p><strong>En producción:</strong> Configurar un servicio real como SendGrid, Formspree, o SMTP.</p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>