-- CORRECCIÓN CRÍTICA: ELIMINAR DEFAULT DE 12000 EN TABLA PEDIDOS
USE janithal_musa_moda;

-- 1. CAMBIAR EL DEFAULT DEL CAMPO ENVIO DE 12000.00 A 0.00
ALTER TABLE pedidos MODIFY COLUMN envio DECIMAL(10,2) DEFAULT 0.00;

-- 2. ACTUALIZAR TODOS LOS PEDIDOS EXISTENTES CON ENVIO DE 12000
UPDATE pedidos SET envio = 0.00, total = subtotal WHERE envio = 12000.00;

-- 3. ACTUALIZAR CONFIGURACIONES EN SYSTEM_SETTINGS
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'shipping_cost';
UPDATE system_settings SET setting_value = '0' WHERE setting_key = 'default_shipping_cost';
UPDATE system_settings SET setting_value = '0' WHERE setting_key LIKE '%shipping%' AND setting_value IN ('10000', '12000', '15000');

-- 4. VERIFICAR RESULTADOS
SELECT 'DEFAULT DE TABLA PEDIDOS CORREGIDO' as status;
SHOW CREATE TABLE pedidos;

SELECT 'PEDIDOS CON ENVIO GRATIS:' as status;
SELECT COUNT(*) as total_pedidos_envio_gratis FROM pedidos WHERE envio = 0.00;

SELECT 'CONFIGURACIONES DE ENVIO:' as status;
SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE '%shipping%' OR setting_key LIKE '%envio%';