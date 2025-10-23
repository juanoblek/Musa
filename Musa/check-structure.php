<?php
// Verificar estructura de la tabla pedidos y vista
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=janithal_musa_moda;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<h2>Verificación de tablas y estructura:</h2>";
    
    // Verificar vista pedidos completos
    echo "<h3>Vista vista_pedidos_completos:</h3>";
    try {
        $result = $pdo->query("SHOW CREATE VIEW vista_pedidos_completos");
        $view = $result->fetch();
        echo "<pre>" . htmlspecialchars($view['Create View']) . "</pre>";
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    
    // Verificar tabla pedidos
    echo "<h3>Estructura tabla pedidos:</h3>";
    try {
        $result = $pdo->query("DESCRIBE pedidos");
        $columns = $result->fetchAll();
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p>Error tabla pedidos: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
    
    // Verificar tabla envios
    echo "<h3>Estructura tabla envios:</h3>";
    try {
        $result = $pdo->query("DESCRIBE envios");
        $columns = $result->fetchAll();
        echo "<table border='1'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . $col['Field'] . "</td>";
            echo "<td>" . $col['Type'] . "</td>";
            echo "<td>" . $col['Null'] . "</td>";
            echo "<td>" . $col['Key'] . "</td>";
            echo "<td>" . $col['Default'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p>Error tabla envios: " . $e->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<h3>Error de conexión:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>