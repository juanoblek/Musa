<?php
/**
 * Verificar pedidos guardados en la base de datos
 */
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=janithal_musa_moda;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>Pedidos en la base de datos:</h2>";
    
    // Verificar si existe la tabla pedidos_premium
    $tables = $pdo->query("SHOW TABLES LIKE 'pedidos_premium'")->fetchAll();
    
    if (count($tables) > 0) {
        echo "<h3>Tabla pedidos_premium existe ✅</h3>";
        
        $pedidos = $pdo->query("SELECT * FROM pedidos_premium ORDER BY created_at DESC LIMIT 10")->fetchAll();
        
        if (count($pedidos) > 0) {
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f0f0f0;'>";
            echo "<th>ID</th><th>Pedido ID</th><th>Cliente</th><th>Email</th><th>Total</th><th>Fecha</th><th>Estado</th>";
            echo "</tr>";
            
            foreach ($pedidos as $pedido) {
                echo "<tr>";
                echo "<td>" . $pedido['id'] . "</td>";
                echo "<td>" . $pedido['pedido_id'] . "</td>";
                echo "<td>" . $pedido['nombre_completo'] . "</td>";
                echo "<td>" . $pedido['email'] . "</td>";
                echo "<td>$" . number_format($pedido['total']) . "</td>";
                echo "<td>" . $pedido['created_at'] . "</td>";
                echo "<td>" . $pedido['estado'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No hay pedidos en la tabla pedidos_premium</p>";
        }
    } else {
        echo "<h3>Tabla pedidos_premium NO existe ❌</h3>";
    }
    
    // Verificar tabla pedidos original
    echo "<hr>";
    $tables = $pdo->query("SHOW TABLES LIKE 'pedidos'")->fetchAll();
    
    if (count($tables) > 0) {
        echo "<h3>Tabla pedidos original existe ✅</h3>";
        
        $pedidos = $pdo->query("SELECT * FROM pedidos ORDER BY fecha_pedido DESC LIMIT 5")->fetchAll();
        
        if (count($pedidos) > 0) {
            echo "<p>Últimos 5 pedidos en tabla original:</p>";
            foreach ($pedidos as $pedido) {
                echo "<p>- Pedido: " . ($pedido['id'] ?? 'N/A') . " - " . ($pedido['fecha_pedido'] ?? 'N/A') . "</p>";
            }
        } else {
            echo "<p>No hay pedidos en la tabla original</p>";
        }
    } else {
        echo "<h3>Tabla pedidos original NO existe ❌</h3>";
    }

} catch (Exception $e) {
    echo "<h3>Error de conexión:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>