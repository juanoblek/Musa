<?php
// Verificador rápido de pedidos
header('Content-Type: text/html; charset=utf-8');

try {
    require_once 'config/database.php';
    
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Obtener últimos 10 pedidos
    $stmt = $pdo->query("
        SELECT id, nombre_completo, email, total, estado_pago, metodo_pago, 
               fecha_pedido, origen 
        FROM pedidos 
        ORDER BY fecha_pedido DESC 
        LIMIT 10
    ");
    
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Verificador de Pedidos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f2f2f2; font-weight: bold; }
        .success { background: #d4edda; }
        .warning { background: #fff3cd; }
        .error { background: #f8d7da; }
        .info { background: #d1ecf1; }
        .refresh-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>📊 Verificador de Pedidos - Base de Datos</h1>
    
    <button class="refresh-btn" onclick="location.reload()">🔄 Actualizar</button>
    
    <p><strong>Total de pedidos:</strong> <?= $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn() ?></p>
    
    <h2>📋 Últimos 10 Pedidos</h2>
    
    <?php if (empty($pedidos)): ?>
        <div class="warning" style="padding: 15px; border-radius: 5px;">
            ⚠️ No hay pedidos en la base de datos aún.
            <br><br>
            <a href="test-directo-sin-sdk.html" target="_blank">🧪 Hacer prueba con simulador directo</a>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Email</th>
                    <th>Total</th>
                    <th>Estado Pago</th>
                    <th>Método</th>
                    <th>Fecha</th>
                    <th>Origen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr class="<?= $pedido['estado_pago'] === 'completado' ? 'success' : 'warning' ?>">
                        <td><?= htmlspecialchars($pedido['id']) ?></td>
                        <td><?= htmlspecialchars($pedido['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($pedido['email']) ?></td>
                        <td>$<?= number_format($pedido['total'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($pedido['estado_pago']) ?></td>
                        <td><?= htmlspecialchars($pedido['metodo_pago']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></td>
                        <td><?= htmlspecialchars($pedido['origen']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <hr>
    <h3>🔗 Enlaces Rápidos</h3>
    <ul>
        <li><a href="test-directo-sin-sdk.html" target="_blank">🧪 Test Directo (Sin SDK)</a></li>
        <li><a href="test-simple-funcional.html" target="_blank">🧪 Test Simple Funcional</a></li>
        <li><a href="admin-panel.php" target="_blank">👨‍💼 Panel Administrativo</a></li>
        <li><a href="verificar-pedidos.php" target="_blank">🔄 Actualizar esta página</a></li>
    </ul>
    
    <script>
        // Auto-refresh cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        console.log('📊 Verificador de pedidos cargado');
        console.log('🔄 Auto-refresh cada 30 segundos');
    </script>
</body>
</html>

<?php
} catch (Exception $e) {
    echo "<h1>❌ Error</h1>";
    echo "<p>Error al conectar con la base de datos:</p>";
    echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<p><a href='setup-database.php'>🔧 Configurar base de datos</a></p>";
}
?>