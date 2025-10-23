<?php
// check-php-config.php - Verificar configuraciones de PHP para uploads
echo "<h2>Configuraciones de PHP para Uploads</h2>";

echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th style='padding: 10px; background: #f0f0f0;'>Configuración</th><th style='padding: 10px; background: #f0f0f0;'>Valor Actual</th><th style='padding: 10px; background: #f0f0f0;'>Recomendado</th></tr>";

$configs = [
    'file_uploads' => ['actual' => ini_get('file_uploads') ? 'On' : 'Off', 'recomendado' => 'On'],
    'upload_max_filesize' => ['actual' => ini_get('upload_max_filesize'), 'recomendado' => '1024M o mayor'],
    'post_max_size' => ['actual' => ini_get('post_max_size'), 'recomendado' => '1024M o mayor'],
    'max_execution_time' => ['actual' => ini_get('max_execution_time'), 'recomendado' => '300 o 0'],
    'max_input_time' => ['actual' => ini_get('max_input_time'), 'recomendado' => '300 o -1'],
    'memory_limit' => ['actual' => ini_get('memory_limit'), 'recomendado' => '512M o mayor'],
    'max_file_uploads' => ['actual' => ini_get('max_file_uploads'), 'recomendado' => '20 o mayor']
];

foreach ($configs as $config => $valores) {
    $color = '#ffffff';
    if ($config === 'file_uploads' && $valores['actual'] === 'Off') {
        $color = '#ffcccc';
    } elseif (in_array($config, ['upload_max_filesize', 'post_max_size'])) {
        $actual_bytes = return_bytes($valores['actual']);
        if ($actual_bytes < return_bytes('100M')) {
            $color = '#ffffcc'; // Amarillo para advertencia
        }
    }
    
    echo "<tr style='background: $color;'>";
    echo "<td style='padding: 10px;'><strong>$config</strong></td>";
    echo "<td style='padding: 10px;'>{$valores['actual']}</td>";
    echo "<td style='padding: 10px;'>{$valores['recomendado']}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>Notas:</h3>";
echo "<ul>";
echo "<li>Para permitir videos grandes sin límite, se recomienda:</li>";
echo "<li style='margin-left: 20px;'>• upload_max_filesize = 0 (sin límite) o un valor grande como 1024M</li>";
echo "<li style='margin-left: 20px;'>• post_max_size = 0 (sin límite) o un valor grande como 1024M</li>";
echo "<li style='margin-left: 20px;'>• max_execution_time = 0 (sin límite) o 300 segundos</li>";
echo "<li>Estas configuraciones se pueden cambiar en el archivo php.ini de XAMPP</li>";
echo "<li>Ruta típica: C:\\xampp\\php\\php.ini</li>";
echo "</ul>";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = intval($val);
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

echo "<hr>";
echo "<h3>Configuraciones actuales completas:</h3>";
echo "<pre>";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "</pre>";
?>