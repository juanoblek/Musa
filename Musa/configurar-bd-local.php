<?php
// Configuración inicial usando root
try {
    // Primero conectar como root
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado como root\n";
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS janithal_musa_moda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de datos janithal_musa_moda creada/verificada\n";
    
    // Crear usuario si no existe y darle permisos
    $pdo->exec("
        CREATE USER IF NOT EXISTS 'janithal_usuario_musaarion_db'@'localhost' IDENTIFIED BY 'Chiguiro553021';
        GRANT ALL PRIVILEGES ON janithal_musa_moda.* TO 'janithal_usuario_musaarion_db'@'localhost';
        FLUSH PRIVILEGES;
    ");
    echo "✅ Usuario janithal_usuario_musaarion_db creado y permisos otorgados\n";
    
    // Conectar a la base de datos específica
    $pdo = new PDO("mysql:host=localhost;dbname=janithal_musa_moda;charset=utf8mb4", "root", "");
    
    // Crear tablas necesarias
    $pdo->exec("
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
            shipping_address VARCHAR(255),
            shipping_city VARCHAR(100),
            shipping_state VARCHAR(100),
            shipping_country VARCHAR(100),
            shipping_zip VARCHAR(20),
            shipping_method VARCHAR(50) DEFAULT 'standard',
            shipping_status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            INDEX idx_payment_id (payment_id)
        ) ENGINE=InnoDB;
    ");
    echo "✅ Tabla pedidos creada/verificada\n";
    
    $pdo->exec("
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
        ) ENGINE=InnoDB;
    ");
    echo "✅ Tabla envios creada/verificada\n";
    
    // Probar conexión con el nuevo usuario
    $pdo = new PDO(
        "mysql:host=localhost;dbname=janithal_musa_moda;charset=utf8mb4",
        "janithal_usuario_musaarion_db",
        "Chiguiro553021"
    );
    echo "✅ Conexión exitosa con el usuario janithal_usuario_musaarion_db\n";
    
    // Verificar tablas
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "\n📋 Tablas en la base de datos:\n";
    foreach ($tables as $table) {
        echo "  - $table\n";
    }
    
    echo "\n✨ Configuración completada exitosamente!\n";
    echo "Puedes usar la base de datos con las siguientes credenciales:\n";
    echo "Host: localhost\n";
    echo "Database: janithal_musa_moda\n";
    echo "Username: janithal_usuario_musaarion_db\n";
    echo "Password: Chiguiro553021\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), "Access denied for user 'root'") !== false) {
        echo "\n⚠️ IMPORTANTE: Necesitas proporcionar la contraseña de root correcta.\n";
        echo "1. Abre XAMPP Control Panel\n";
        echo "2. Haz clic en el botón 'Shell'\n";
        echo "3. Ejecuta: mysql -u root -p\n";
        echo "4. Si no has establecido contraseña, solo presiona Enter\n";
        echo "5. Ejecuta: ALTER USER 'root'@'localhost' IDENTIFIED BY '';\n";
        echo "6. Luego vuelve a ejecutar este script\n";
    }
}
?>