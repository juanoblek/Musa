<?php
/**
 * SCRIPT PARA ACTUALIZAR ENVÍO A GRATIS EN BASE DE DATOS EXISTENTE
 * Ejecutar este archivo para actualizar todos los registros existentes
 */

require_once 'config/database.php';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 Actualizando configuración de envío a GRATIS...\n\n";
    
    // 1. Actualizar la columna default en la tabla pedidos
    $sql_alter = "ALTER TABLE pedidos ALTER COLUMN envio SET DEFAULT 0.00";
    $pdo->exec($sql_alter);
    echo "✅ Valor por defecto de envío actualizado a 0.00\n";
    
    // 2. Actualizar todos los pedidos existentes para reducir el total
    $sql_update = "
        UPDATE pedidos 
        SET 
            envio = 0.00,
            total = subtotal + 0.00
        WHERE envio > 0
    ";
    $stmt = $pdo->exec($sql_update);
    echo "✅ Actualizados $stmt pedidos existentes - envío ahora es gratis\n";
    
    // 3. Mostrar resumen de pedidos actualizados
    $sql_check = "SELECT COUNT(*) as total_pedidos, SUM(subtotal) as suma_subtotales, SUM(total) as suma_totales FROM pedidos";
    $stmt = $pdo->query($sql_check);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n📊 RESUMEN DE PEDIDOS:\n";
    echo "   - Total de pedidos: " . $result['total_pedidos'] . "\n";
    echo "   - Suma de subtotales: $" . number_format($result['suma_subtotales'], 0, ',', '.') . "\n";
    echo "   - Suma de totales: $" . number_format($result['suma_totales'], 0, ',', '.') . "\n";
    echo "   - Ahorro en envíos: $" . number_format(($result['total_pedidos'] * 12000), 0, ',', '.') . "\n";
    
    echo "\n🎉 ¡ENVÍO GRATIS CONFIGURADO EXITOSAMENTE!\n";
    echo "   ✅ Todos los nuevos pedidos tendrán envío gratuito\n";
    echo "   ✅ Pedidos existentes actualizados\n";
    echo "   ✅ Base de datos configurada correctamente\n";
    
} catch (PDOException $e) {
    echo "❌ Error actualizando base de datos: " . $e->getMessage() . "\n";
    echo "💡 Asegúrate de que:\n";
    echo "   - La base de datos 'musa_moda' exista\n";
    echo "   - La tabla 'pedidos' esté creada\n";
    echo "   - Los archivos de configuración sean correctos\n";
}
?>