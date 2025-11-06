<?php
/**
 * Script de instalación para hosting V2
 * MUSA MODA - musaarion.com
 * Con soporte mejorado para consultas bufferizadas
 */

require_once 'config/config-global.php';

function executeSQL($pdo, $sql) {
    // Asegurarse de que cualquier consulta previa sea completada
    while ($pdo->getAttribute(PDO::ATTR_SERVER_INFO)) {
        $pdo->getAttribute(PDO::ATTR_SERVER_INFO);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createTables($pdo) {
    $tables = [
        "productos" => "
            CREATE TABLE IF NOT EXISTS productos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255) NOT NULL,
                descripcion TEXT,
                precio DECIMAL(10,2),
                imagenes JSON,
                videos JSON,
                caracteristicas JSON,
                precios JSON,
                categoria VARCHAR(100),
                activo BOOLEAN DEFAULT 1,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        "categorias" => "
            CREATE TABLE IF NOT EXISTS categorias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(100) NOT NULL,
                descripcion TEXT,
                activo BOOLEAN DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ",
        "pedidos" => "
            CREATE TABLE IF NOT EXISTS pedidos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT,
                total DECIMAL(10,2),
                estado VARCHAR(50),
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        "
    ];
    
    foreach ($tables as $tableName => $sql) {
        try {
            $pdo->exec($sql);
            echo "<div class='status-item status-success'>
                    <span class='icon'>✅</span>
                    <span>Tabla {$tableName} creada/verificada</span>
                  </div>";
        } catch (PDOException $e) {
            throw new Exception("Error creando tabla {$tableName}: " . $e->getMessage());
        }
    }
}

// Iniciar la instalación
try {
    $dbConfig = GlobalConfig::getDatabaseConfig();
    
    // Configurar PDO para usar consultas bufferizadas
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        $options
    );
    
    // Crear las tablas
    createTables($pdo);
    
    echo "<div class='status-item status-success'>
            <span class='icon'>✅</span>
            <span>Base de datos configurada exitosamente</span>
          </div>";
    
} catch (Exception $e) {
    echo "<div class='status-item status-error'>
            <span class='icon'>❌</span>
            <span>Error: " . htmlspecialchars($e->getMessage()) . "</span>
          </div>";
    exit;
}

// Verificar que las tablas se crearon correctamente
try {
    $tables = ['productos', 'categorias', 'pedidos'];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '{$table}'");
        if ($result->rowCount() > 0) {
            echo "<div class='status-item status-success'>
                    <span class='icon'>✅</span>
                    <span>Tabla {$table} existe y está accesible</span>
                  </div>";
        }
    }
} catch (Exception $e) {
    echo "<div class='status-item status-error'>
            <span class='icon'>❌</span>
            <span>Error verificando tablas: " . htmlspecialchars($e->getMessage()) . "</span>
          </div>";
}