<?php
/**
 * Configuración de MercadoPago para hosting
 */

class MercadoPagoConfig {
    private static $config = [
        'public_key' => 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577',
        'access_token' => 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340',
        'environment' => 'production',
        'success_url' => 'https://musaarion.com/success.php',
        'failure_url' => 'https://musaarion.com/failure.php',
        'pending_url' => 'https://musaarion.com/pending.php',
        'webhook_url' => 'https://musaarion.com/webhook.php'
    ];

    public static function getConfig() {
        return self::$config;
    }

    public static function initialize() {
        MercadoPago\SDK::setAccessToken(self::$config['access_token']);
    }
}