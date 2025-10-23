-- SCRIPT PARA ELIMINAR TODOS LOS COSTOS DE ENVÍO DE $12,000
-- Base de datos: janithal_musa_moda
USE janithal_musa_moda;

-- Actualizar configuraciones de shipping en la tabla system_settings
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'shipping_cost';
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'default_shipping_cost';
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'free_shipping_minimum';
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'free_shipping_min';
UPDATE system_settings SET setting_value = '0' WHERE setting_key LIKE '%shipping%';
UPDATE system_settings SET setting_value = '0' WHERE setting_key LIKE '%envio%';
UPDATE settings SET value = '0' WHERE setting_key = 'free_shipping_minimum';

-- ¡CRÍTICO! Cambiar el DEFAULT de la columna envio de 12000.00 a 0.00
ALTER TABLE pedidos MODIFY COLUMN envio DECIMAL(10,2) DEFAULT 0.00;

-- Actualizar todos los pedidos existentes que tengan envío de 12000
UPDATE pedidos SET envio = 0.00, total = subtotal WHERE envio = 12000.00;
UPDATE pedidos SET envio = 0, total = subtotal WHERE envio = 12000;

-- La tabla envios no tiene campo de costo, solo información de envío

-- Verificar si hay más pedidos que necesiten corrección
UPDATE pedidos SET total = subtotal WHERE envio = 0;

-- Verificar configuraciones actuales
SELECT 'CONFIGURACIONES ACTUALIZADAS:' as mensaje;
SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE '%shipping%' OR setting_key LIKE '%envio%';

SELECT 'PEDIDOS CORREGIDOS:' as mensaje;
SELECT COUNT(*) as pedidos_con_envio_gratis FROM pedidos WHERE envio = 0.00;