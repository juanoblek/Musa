<?php
/**
 * SCRIPT PARA VERIFICAR CAMPOS DE BASE DE DATOS
 * Muestra exactamente qué campos esperan las tablas envios y pedidos
 */

require_once 'config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h2>📊 ESTRUCTURA DE BASE DE DATOS - " . DB_NAME . "</h2>";
    
    // Verificar campos de tabla envios
    echo "<h3>📦 Tabla 'envios':</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    
    $stmt = $pdo->query("DESCRIBE envios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar campos de tabla pedidos
    echo "<h3>🛒 Tabla 'pedidos':</h3>";
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
    
    $stmt = $pdo->query("DESCRIBE pedidos");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>{$row['Field']}</strong></td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>🔍 MAPEO REQUERIDO:</h3>";
    echo "<h4>Para tabla 'envios':</h4>";
    echo "<ul>";
    echo "<li><strong>pedido_id</strong> - Se genera automáticamente</li>";
    echo "<li><strong>nombre_completo</strong> - Campo del formulario</li>";
    echo "<li><strong>email</strong> - Campo del formulario</li>";
    echo "<li><strong>telefono</strong> - Campo del formulario</li>";
    echo "<li><strong>departamento</strong> - Campo del formulario</li>";
    echo "<li><strong>ciudad</strong> - Campo del formulario</li>";
    echo "<li><strong>direccion</strong> - Campo del formulario</li>";
    echo "<li><strong>codigo_postal</strong> - Campo del formulario (opcional)</li>";
    echo "<li><strong>notas_adicionales</strong> - Campo del formulario (opcional)</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error conectando a la base de datos:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>