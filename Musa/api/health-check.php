<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = [
    'ok' => true,
    'time' => date('c'),
    'checks' => []
];

try {
    $cfgPaths = [
        __DIR__ . '/../config/config-global.php',
        __DIR__ . '/config/config-global.php',
        dirname(__DIR__) . '/config/config-global.php'
    ];
    $found = null;
    foreach ($cfgPaths as $p) {
        if (file_exists($p)) { $found = $p; break; }
    }
    $response['checks']['config_file_found'] = (bool)$found;
    $response['checks']['config_path'] = $found;

    if ($found) {
        require_once $found;
        $mp = GlobalConfig::getMercadoPagoConfig();
        $response['checks']['mp_public_key_present'] = !empty($mp['public_key']);
        $response['checks']['mp_access_token_present'] = !empty($mp['access_token']);
        $response['checks']['environment'] = GlobalConfig::isProduction() ? 'production' : 'development';
        $response['checks']['domain'] = GlobalConfig::getDomain();
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
    exit;
}

echo json_encode($response);
