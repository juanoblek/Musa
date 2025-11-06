CREATE VIEW `vista_pedidos_completos` AS
SELECT 
    `p`.`id` AS `pedido_id_interno`,
    `p`.`pedido_id` AS `pedido_id`,
    `e`.`nombre_completo` AS `nombre_completo`,
    `e`.`email` AS `email`,
    `e`.`telefono` AS `telefono`,
    `e`.`departamento` AS `departamento`,
    `e`.`ciudad` AS `ciudad`,
    `e`.`direccion` AS `direccion`,
    `e`.`codigo_postal` AS `codigo_postal`,
    `e`.`notas_adicionales` AS `notas_adicionales`,
    `p`.`productos` AS `productos`,
    `p`.`subtotal` AS `subtotal`,
    `p`.`envio` AS `envio`,
    `p`.`total` AS `total`,
    `p`.`estado_pago` AS `estado_pago`,
    `e`.`estado_envio` AS `estado_envio`,
    `p`.`metodo_pago` AS `metodo_pago`,
    `p`.`datos_pago` AS `datos_pago`,
    `p`.`fecha_creacion` AS `fecha_creacion`,
    `p`.`fecha_actualizacion` AS `fecha_actualizacion`
FROM 
    `pedidos` `p`
    JOIN `envios` `e` ON `p`.`pedido_id` = `e`.`pedido_id`
ORDER BY 
    `p`.`fecha_creacion` DESC;