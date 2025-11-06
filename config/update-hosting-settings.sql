-- Actualizar configuraciones del sistema para hosting
USE janithal_musa_moda;

-- Actualizar URLs del sistema
UPDATE system_settings SET setting_value = 'https://musaarion.com' WHERE setting_key = 'site_url';
UPDATE system_settings SET setting_value = 'MUSA MODA' WHERE setting_key = 'site_name';
UPDATE system_settings SET setting_value = 'production' WHERE setting_key = 'mercadopago_environment';

-- Actualizar configuraciones de envío
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'shipping_cost';
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'free_shipping_minimum';

-- Actualizar configuraciones de notificaciones
UPDATE system_settings SET setting_value = 'true' WHERE setting_key = 'email_notifications';

-- Insertar nuevas configuraciones si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('domain_name', 'musaarion.com', 'text', 'Dominio principal'),
('ssl_enabled', 'true', 'boolean', 'SSL habilitado'),
('maintenance_mode', 'false', 'boolean', 'Modo mantenimiento');

-- Actualizar índices importantes
ALTER TABLE products ADD INDEX idx_stock (stock_quantity);
ALTER TABLE pedidos ADD INDEX idx_fecha (fecha_creacion);
ALTER TABLE envios ADD INDEX idx_estado (estado_envio);