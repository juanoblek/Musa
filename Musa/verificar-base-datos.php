<?php
/**
 * Verificación de Base de Datos y Estructura de Pedidos
 */

echo "🔍 VERIFICACIÓN DE BASE DE DATOS PARA SISTEMA DE PEDIDOS\n";
echo "======================================================\n\n";

try {
    // Conectar a la base de datos
    $connection = new mysqli('localhost', 'root', '', 'janithal_musa_moda');
    
    if ($connection->connect_error) {
        die("❌ Error de conexión: " . $connection->connect_error . "\n");
    }
    
    echo "✅ Conexión exitosa a la base de datos 'janithal_musa_moda'\n\n";
    
    // Verificar tablas existentes
    $result = $connection->query('SHOW TABLES');
    echo "📋 Tablas existentes:\n";
    $tables = [];
    while($row = $result->fetch_array()) {
        $tables[] = $row[0];
        echo "  - " . $row[0] . "\n";
    }
    echo "\n";
    
    // Verificar si existe tabla pedidos
    if (in_array('pedidos', $tables)) {
        echo "✅ Tabla 'pedidos' encontrada\n\n";
        
        // Mostrar estructura de pedidos
        $result = $connection->query('DESCRIBE pedidos');
        echo "📊 Estructura de tabla 'pedidos':\n";
        while($row = $result->fetch_array()) {
            echo "  - " . $row[0] . " (" . $row[1] . ")\n";
        }
        echo "\n";
        
        // Mostrar últimos pedidos
        $result = $connection->query('SELECT * FROM pedidos ORDER BY fecha_creacion DESC LIMIT 5');
        echo "📦 Últimos 5 pedidos:\n";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "  ID: " . $row['id'] . " | Pedido: " . $row['pedido_id'] . " | Total: $" . number_format($row['total']) . " | Estado Pago: " . $row['estado_pago'] . " | Fecha: " . $row['fecha_creacion'] . "\n";
            }
        } else {
            echo "  ⚠️ No hay pedidos en la base de datos\n";
        }
        
    } else {
        echo "❌ Tabla 'pedidos' NO existe\n";
        echo "🔧 Creando tabla 'pedidos'...\n";
        
        $sql = "CREATE TABLE pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT,
            total DECIMAL(10,2) NOT NULL,
            estado VARCHAR(50) DEFAULT 'pendiente',
            metodo_pago VARCHAR(100),
            referencia_pago VARCHAR(255),
            payment_id VARCHAR(255),
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            datos_envio JSON,
            datos_productos JSON,
            datos_pago JSON
        )";
        
        if ($connection->query($sql) === TRUE) {
            echo "✅ Tabla 'pedidos' creada exitosamente\n";
        } else {
            echo "❌ Error creando tabla: " . $connection->error . "\n";
        }
    }
    
    // Verificar tabla envios
    echo "\n";
    if (in_array('envios', $tables)) {
        echo "✅ Tabla 'envios' encontrada\n";
        
        // Mostrar últimos envíos
        $result = $connection->query('SELECT * FROM envios ORDER BY fecha_creacion DESC LIMIT 3');
        echo "🚚 Últimos 3 envíos:\n";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "  ID: " . $row['id'] . " | Dirección: " . $row['direccion'] . " | Ciudad: " . $row['ciudad'] . "\n";
            }
        } else {
            echo "  ⚠️ No hay envíos registrados\n";
        }
    } else {
        echo "❌ Tabla 'envios' NO existe\n";
    }
    
    $connection->close();
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 SIGUIENTE PASO: Verificar que guardar-pedido.php funcione correctamente\n";
?>