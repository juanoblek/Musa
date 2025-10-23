<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si la tabla system_settings existe, si no crearla
    $checkTable = $pdo->query("SHOW TABLES LIKE 'system_settings'");
    if ($checkTable->rowCount() == 0) {
        $createTableSQL = "
            CREATE TABLE system_settings (
                id INT(11) NOT NULL AUTO_INCREMENT,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT,
                setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                INDEX idx_setting_key (setting_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $pdo->exec($createTableSQL);
        
        // Insertar configuraciones por defecto
        $defaultSettings = [
            ['company_name', 'M & A MODA ACTUAL', 'string', 'Nombre de la empresa'],
            ['company_address', 'CLL 3 20A39 Madrid, Cundinamarca', 'string', 'Dirección de la empresa'],
            ['company_phone', '', 'string', 'Teléfono de la empresa'],
            ['company_email', '', 'string', 'Email de la empresa'],
            ['site_title', 'M & A MODA ACTUAL', 'string', 'Título del sitio web'],
            ['site_description', '', 'string', 'Descripción del sitio web'],
            ['site_maintenance_mode', '0', 'boolean', 'Modo mantenimiento activado'],
            ['allow_registrations', '1', 'boolean', 'Permitir nuevos registros']
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($defaultSettings as $setting) {
            $insertStmt->execute($setting);
        }
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Obtener todas las configuraciones
        $stmt = $pdo->prepare("SELECT setting_key, setting_value, setting_type FROM system_settings ORDER BY setting_key");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear configuraciones para el frontend
        $formattedSettings = [];
        foreach ($settings as $setting) {
            $value = $setting['setting_value'];
            
            // Convertir tipos
            if ($setting['setting_type'] === 'boolean') {
                $value = (bool)$value;
            } elseif ($setting['setting_type'] === 'number') {
                $value = is_numeric($value) ? (float)$value : 0;
            } elseif ($setting['setting_type'] === 'json') {
                $value = json_decode($value, true);
            }
            
            $formattedSettings[$setting['setting_key']] = $value;
        }
        
        echo json_encode([
            'success' => true,
            'settings' => $formattedSettings
        ]);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Guardar configuraciones
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data || !isset($data['settings'])) {
            throw new Exception('Datos de configuración inválidos');
        }
        
        $settings = $data['settings'];
        $updatedCount = 0;
        
        // Mapeo de configuraciones con sus tipos
        $settingTypes = [
            'company_name' => 'string',
            'company_address' => 'string',
            'company_phone' => 'string',
            'company_email' => 'string',
            'site_title' => 'string',
            'site_description' => 'string',
            'site_maintenance_mode' => 'boolean',
            'allow_registrations' => 'boolean'
        ];
        
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value, setting_type, description) 
            VALUES (?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value), 
            updated_at = CURRENT_TIMESTAMP
        ");
        
        foreach ($settings as $key => $value) {
            if (isset($settingTypes[$key])) {
                $type = $settingTypes[$key];
                
                // Convertir el valor según el tipo
                if ($type === 'boolean') {
                    $value = $value ? '1' : '0';
                } elseif ($type === 'json') {
                    $value = json_encode($value);
                }
                
                $description = '';
                switch ($key) {
                    case 'company_name': $description = 'Nombre de la empresa'; break;
                    case 'company_address': $description = 'Dirección de la empresa'; break;
                    case 'company_phone': $description = 'Teléfono de la empresa'; break;
                    case 'company_email': $description = 'Email de la empresa'; break;
                    case 'site_title': $description = 'Título del sitio web'; break;
                    case 'site_description': $description = 'Descripción del sitio web'; break;
                    case 'site_maintenance_mode': $description = 'Modo mantenimiento activado'; break;
                    case 'allow_registrations': $description = 'Permitir nuevos registros'; break;
                }
                
                $stmt->execute([$key, $value, $type, $description]);
                $updatedCount++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Se actualizaron {$updatedCount} configuraciones correctamente",
            'updated_count' => $updatedCount
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error en settings.php: " . $e->getMessage());
    
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>