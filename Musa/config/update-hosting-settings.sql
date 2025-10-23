-- Script para actualizar configuraciones de sistema para hosting
-- MUSA MODA - musaarion.com
-- Ejecutar en el hosting después de importar la base de datos

USE janithal_musa_moda;

-- Actualizar configuraciones básicas del sitio
UPDATE system_settings SET setting_value = 'https://musaarion.com' WHERE setting_key = 'site_url';
UPDATE system_settings SET setting_value = 'admin@musaarion.com' WHERE setting_key = 'admin_email';
UPDATE system_settings SET setting_value = 'production' WHERE setting_key = 'mercadopago_environment';

-- Agregar configuraciones adicionales si no existen
INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('hosting_setup_date', NOW(), 'text', 'Fecha de configuración en hosting'),
('domain_name', 'musaarion.com', 'text', 'Dominio principal del sitio'),
('ssl_enabled', 'true', 'boolean', 'SSL habilitado'),
('cache_enabled', 'true', 'boolean', 'Cache habilitado'),
('debug_mode', 'false', 'boolean', 'Modo debug deshabilitado en producción'),
('max_upload_size', '5MB', 'text', 'Tamaño máximo de archivo'),
('allowed_file_types', 'jpg,jpeg,png,gif,webp', 'text', 'Tipos de archivo permitidos'),
('backup_frequency', 'weekly', 'text', 'Frecuencia de respaldos'),
('timezone', 'America/Bogota', 'text', 'Zona horaria del sitio');

-- Actualizar rutas de imágenes de productos para que funcionen con el nuevo dominio
UPDATE products 
SET main_image = REPLACE(main_image, 'http://localhost/Musa/', 'https://musaarion.com/')
WHERE main_image LIKE '%localhost%';

-- Actualizar datos de productos con imágenes localhost
UPDATE pedidos 
SET productos = REPLACE(productos, 'http://localhost/Musa/', 'https://musaarion.com/')
WHERE productos LIKE '%localhost%';

-- Verificar que las configuraciones se aplicaron correctamente
SELECT setting_key, setting_value, description 
FROM system_settings 
WHERE setting_key IN ('site_url', 'admin_email', 'mercadopago_environment', 'domain_name')
ORDER BY setting_key;

-- Verificar productos con nuevas rutas
SELECT id, name, main_image 
FROM products 
WHERE main_image IS NOT NULL 
LIMIT 5;