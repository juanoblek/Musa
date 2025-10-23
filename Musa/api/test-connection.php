<?php
/**
 * API para probar la conexión y funcionalidad de vista_pedidos_completos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Configuración de la base de datos
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "janithal_musa_moda";

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4", $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $action = $data['action'] ?? 'test_connection';
    
    switch ($action) {
        case 'test_connection':
            // Verificar conexión y estructura
            $tablas = [];
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tablas[] = $row[0];
            }
            
            // Verificar si existe la vista
            $vista_exists = in_array('vista_pedidos_completos', $tablas);
            
            // Verificar tablas base
            $pedidos_exists = in_array('pedidos', $tablas);
            $envios_exists = in_array('envios', $tablas);
            
            echo json_encode([
                'success' => true,
                'message' => 'Conexión exitosa',
                'database' => $base_datos,
                'tables' => $tablas,
                'vista_exists' => $vista_exists,
                'tables_status' => [
                    'pedidos' => $pedidos_exists,
                    'envios' => $envios_exists,
                    'vista_pedidos_completos' => $vista_exists
                ]
            ]);
            break;
            
        case 'query_vista':
            // Consultar vista_pedidos_completos
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM vista_pedidos_completos");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                $stmt = $pdo->query("
                    SELECT pedido_id, nombre_completo, total, fecha_creacion 
                    FROM vista_pedidos_completos 
                    ORDER BY fecha_creacion DESC 
                    LIMIT 5
                ");
                $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'count' => $count,
                    'registros' => $registros,
                    'message' => 'Vista consultada exitosamente'
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al consultar vista: ' . $e->getMessage(),
                    'suggestion' => 'Ejecuta el archivo crear_tablas_base_vista.sql en phpMyAdmin'
                ]);
            }
            break;
            
        case 'create_tables':
            // Crear tablas base si no existen
            $sql_pedidos = "
                CREATE TABLE IF NOT EXISTS pedidos (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pedido_id VARCHAR(50) UNIQUE NOT NULL,
                    productos JSON NOT NULL,
                    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
                    envio DECIMAL(10,2) NOT NULL DEFAULT 0,
                    total DECIMAL(10,2) NOT NULL DEFAULT 0,
                    estado_pago ENUM('pendiente', 'aprobado', 'rechazado', 'cancelado') NOT NULL DEFAULT 'pendiente',
                    metodo_pago VARCHAR(50) NOT NULL,
                    datos_pago JSON,
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ";
            
            $sql_envios = "
                CREATE TABLE IF NOT EXISTS envios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pedido_id VARCHAR(50) NOT NULL,
                    nombre_completo VARCHAR(100) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    telefono VARCHAR(20) NOT NULL,
                    departamento VARCHAR(50) NOT NULL,
                    ciudad VARCHAR(50) NOT NULL,
                    direccion TEXT NOT NULL,
                    codigo_postal VARCHAR(10),
                    notas_adicionales TEXT,
                    estado_envio ENUM('pendiente', 'preparando', 'enviado', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
                    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (pedido_id) REFERENCES pedidos(pedido_id) ON DELETE CASCADE
                ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
            ";
            
            $pdo->exec($sql_pedidos);
            $pdo->exec($sql_envios);
            
            echo json_encode([
                'success' => true,
                'message' => 'Tablas base creadas exitosamente',
                'tables_created' => ['pedidos', 'envios']
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'error_type' => 'database'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error_type' => 'general'
    ]);
}
?>