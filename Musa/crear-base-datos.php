<?php
// Script para crear la base de datos y tablas necesarias
header('Content-Type: application/json');

try {
    // Conectar a MySQL sin especificar base de datos
    $host = 'localhost';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS musa_store CHARACTER SET utf8 COLLATE utf8_general_ci");
    
    // Conectar a la base de datos específica
    $pdo = new PDO("mysql:host=$host;dbname=musa_store;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla de pedidos
    $create_pedidos = "
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            payment_id VARCHAR(50) UNIQUE NOT NULL,
            external_reference VARCHAR(100),
            status ENUM('approved', 'pending', 'rejected', 'cancelled') NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'COP',
            payment_method VARCHAR(50),
            payer_name VARCHAR(100),
            payer_email VARCHAR(100),
            payer_phone VARCHAR(20),
            payment_data JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_payer_email (payer_email)
        ) ENGINE=InnoDB
    ";
    
    $pdo->exec($create_pedidos);
    
    // Insertar datos de prueba
    $test_orders = [
        [
            'payment_id' => '1325162832',
            'external_reference' => 'MUSA-' . time() . '-1',
            'status' => 'approved',
            'amount' => 1500.00,
            'payment_method' => 'credit_card',
            'payer_name' => 'Juan Pérez',
            'payer_email' => 'juan.perez@email.com',
            'payer_phone' => '+57 300 123 4567'
        ],
        [
            'payment_id' => '1325162833',
            'external_reference' => 'MUSA-' . (time()+1) . '-2',
            'status' => 'approved',
            'amount' => 2500.00,
            'payment_method' => 'bank_transfer',
            'payer_name' => 'María García',
            'payer_email' => 'maria.garcia@email.com',
            'payer_phone' => '+57 310 987 6543'
        ],
        [
            'payment_id' => '1325162834',
            'external_reference' => 'MUSA-' . (time()+2) . '-3',
            'status' => 'pending',
            'amount' => 3200.00,
            'payment_method' => 'debit_card',
            'payer_name' => 'Carlos López',
            'payer_email' => 'carlos.lopez@email.com',
            'payer_phone' => '+57 320 456 7890'
        ],
        [
            'payment_id' => '1325162835',
            'external_reference' => 'MUSA-' . (time()+3) . '-4',
            'status' => 'approved',
            'amount' => 1800.00,
            'payment_method' => 'account_money',
            'payer_name' => 'Ana Rodríguez',
            'payer_email' => 'ana.rodriguez@email.com',
            'payer_phone' => '+57 315 234 5678'
        ]
    ];
    
    $inserted = 0;
    
    foreach ($test_orders as $order) {
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO pedidos (
                payment_id, external_reference, status, amount, currency, 
                payment_method, payer_name, payer_email, payer_phone, payment_data
            ) VALUES (
                :payment_id, :external_reference, :status, :amount, 'COP',
                :payment_method, :payer_name, :payer_email, :payer_phone, '{\"test\": true}'
            )
        ");
        
        if ($stmt->execute($order)) {
            $inserted++;
        }
    }
    
    // Obtener estadísticas finales
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pedidos");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM pedidos GROUP BY status");
    $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Base de datos y datos de prueba creados correctamente',
        'database' => 'musa_store',
        'table' => 'pedidos',
        'inserted' => $inserted,
        'total_orders' => $total,
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>