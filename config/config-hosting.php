<?php
/**
 * Configuración principal para hosting
 * musaarion.com
 */

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'janithal_musa_moda');
define('DB_USER', 'janithal_usuario_musaarion_db');
define('DB_PASS', 'Chiguiro553021');
define('DB_CHARSET', 'utf8mb4');

// Configuración de URLs
define('BASE_URL', 'https://musaarion.com');
define('ADMIN_URL', 'https://musaarion.com/admin');
define('API_URL', 'https://musaarion.com/api');

// Configuración de MercadoPago
define('MP_PUBLIC_KEY', 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577');
define('MP_ACCESS_TOKEN', 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340');
define('MP_ENVIRONMENT', 'production');

// Configuración de notificaciones
define('SUCCESS_URL', BASE_URL . '/success.php');
define('FAILURE_URL', BASE_URL . '/failure.php');
define('PENDING_URL', BASE_URL . '/pending.php');
define('WEBHOOK_URL', BASE_URL . '/webhook.php');

// Configuración de seguridad
define('DEBUG_MODE', false);
define('ERROR_REPORTING', E_ERROR | E_WARNING);
define('DISPLAY_ERRORS', false);
define('LOG_ERRORS', true);
define('ERROR_LOG_FILE', '/logs/error.log');

// Configuración de carga de archivos
define('UPLOAD_PATH', '/uploads');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Zona horaria
date_default_timezone_set('America/Bogota');