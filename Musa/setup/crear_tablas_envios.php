<?php
/**
 * CREAR TABLAS DE ENVÍOS Y PEDIDOS
 * Se ejecuta en la base de datos musa_moda existente
 */

require_once '../config/database.php';

try {
    // Conectar a la base de datos existente
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "🔗 Conectado a la base de datos: " . DB_NAME . "\n";

    // 1. Crear tabla de envíos
    $sql_envios = "
    CREATE TABLE IF NOT EXISTS envios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id VARCHAR(50) UNIQUE NOT NULL,
        nombre_completo VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefono VARCHAR(20) NOT NULL,
        departamento VARCHAR(50) NOT NULL,
        ciudad VARCHAR(100) NOT NULL,
        direccion TEXT NOT NULL,
        codigo_postal VARCHAR(10),
        notas_adicionales TEXT,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        estado_envio ENUM('pendiente', 'en_proceso', 'enviado', 'entregado') DEFAULT 'pendiente',
        INDEX idx_pedido_id (pedido_id),
        INDEX idx_fecha_creacion (fecha_creacion),
        INDEX idx_estado_envio (estado_envio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql_envios);
    echo "✅ Tabla 'envios' creada exitosamente\n";

    // 2. Crear tabla de pedidos
    $sql_pedidos = "
    CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id VARCHAR(50) UNIQUE NOT NULL,
        productos JSON NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        envio DECIMAL(10,2) DEFAULT 0.00, -- ENVÍO GRATIS
        total DECIMAL(10,2) NOT NULL,
        estado_pago ENUM('aprobado', 'rechazado', 'pendiente') DEFAULT 'aprobado',
        metodo_pago VARCHAR(50) DEFAULT 'tarjeta_credito',
        datos_pago JSON,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_pedido_id (pedido_id),
        INDEX idx_fecha_creacion (fecha_creacion),
        INDEX idx_estado_pago (estado_pago)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql_pedidos);
    echo "✅ Tabla 'pedidos' creada exitosamente\n";

    // 3. Crear tabla de tracking
    $sql_tracking = "
    CREATE TABLE IF NOT EXISTS pedido_tracking (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id VARCHAR(50) NOT NULL,
        estado_anterior VARCHAR(50),
        estado_nuevo VARCHAR(50) NOT NULL,
        comentario TEXT,
        fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pedido_id (pedido_id),
        INDEX idx_fecha_cambio (fecha_cambio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql_tracking);
    echo "✅ Tabla 'pedido_tracking' creada exitosamente\n";

    // 4. Crear vista combinada
    $sql_vista = "
    CREATE OR REPLACE VIEW vista_pedidos_completos AS
    SELECT 
        p.id as pedido_id_interno,
        p.pedido_id,
        e.nombre_completo,
        e.email,
        e.telefono,
        e.departamento,
        e.ciudad,
        e.direccion,
        e.codigo_postal,
        e.notas_adicionales,
        p.productos,
        p.subtotal,
        p.envio,
        p.total,
        p.estado_pago,
        e.estado_envio,
        p.metodo_pago,
        p.datos_pago,
        p.fecha_creacion,
        p.fecha_actualizacion
    FROM pedidos p
    INNER JOIN envios e ON p.pedido_id = e.pedido_id
    ORDER BY p.fecha_creacion DESC;
    ";

    $pdo->exec($sql_vista);
    echo "✅ Vista 'vista_pedidos_completos' creada exitosamente\n";

    // 5. Verificar tablas creadas
    $stmt = $pdo->query("SHOW TABLES LIKE '%envios%' OR SHOW TABLES LIKE '%pedidos%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n📋 Tablas relacionadas con pedidos y envíos:\n";
    foreach ($tables as $table) {
        echo "   - $table\n";
    }

    echo "\n🎉 ¡Todas las tablas de envíos y pedidos creadas exitosamente en la base de datos 'musa_moda'!\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>