SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

INSERT INTO `carousel_slides` (`image_path`, `title`, `subtitle`, `location`, `button_text`, `button_link`, `is_active`, `slide_order`) VALUES
('images/slide/Musa.png', 'Musa Moda', 'Estilo y Elegancia', 'Centro', 'Ver Catálogo', '#productos', 1, 1);

INSERT INTO `categories` (`id`, `name`, `slug`, `gender`, `description`, `sort_order`, `status`) VALUES
('camisas', 'Camisas Mujer', 'camisas', 'unisex', 'Camisas elegantes y casuales', 3, 'active'),
('Camisas Ejecutivas', 'Camisas Ejecutivas Hombre', 'Camisas Ejecutivas', 'unisex', '', 0, 'active'),
('Chaqueta', 'Chaquetas Hombre', 'Chaqueta', 'unisex', 'Chaquetas de Hombre', 1, 'active'),
('chaquetas', 'Chaquetas Mujer', 'chaquetas', 'unisex', 'Chaquetas y abrigos', 5, 'active'),
('Hoddies', 'Hoddies', 'Hoddies', 'unisex', '', 0, 'active'),
('pantalones', 'Pantalones Hombre', 'pantalones', 'unisex', 'Pantalones y jeans', 4, 'active'),
('sueter', 'Sueter', 'sueter', 'unisex', '', 0, 'active'),
('tops', 'Tops', 'tops', 'unisex', '', 0, 'active');

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'MUSA MODA', 'text', 'Nombre del sitio web'),
('site_url', 'https://tu-dominio.com', 'text', 'URL principal del sitio'),
('maintenance_mode', 'false', 'boolean', 'Modo de mantenimiento'),
('shipping_cost', '0', 'number', 'Costo de envío por defecto'),
('free_shipping_minimum', '0', 'number', 'Monto mínimo para envío gratis'),
('mercadopago_environment', 'production', 'text', 'Entorno de MercadoPago (sandbox/production)'),
('email_notifications', 'true', 'boolean', 'Habilitar notificaciones por email'),
('admin_email', 'admin@tu-dominio.com', 'text', 'Email del administrador'),
('company_name', 'MUSA MODA', '', 'Nombre de la empresa'),
('company_address', 'CLL 3 20A39 Madrid, Cundinamarca', '', 'Dirección de la empresa'),
('site_title', 'MUSA MODA', '', 'Título del sitio web');

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `sale_price`, `stock_quantity`, `category_id`, `gender`, `main_image`, `colors`, `sizes`, `discount_percentage`, `is_featured`, `status`) VALUES
('prod-68cb454f16af8', 'Camisa \"Rastro Andino\"', 'camisa-rastro-andino', 'La Rastro Andino es una camisa tipo leñadora de corte relajado...', 199999.00, 129999.00, 1, 'camisas', 'mujer', 'uploads/product_68cb454e28ae7_1758152014.png', '[\"Rojo\"]', '[\"M\"]', 35, 1, 'active'),
('prod-68cc7ab5d55ac', 'Top Zipper Noir', 'top-zipper-noir', 'Diseño limpio, actitud decidida...', 69999.00, 54999.00, 1, 'tops', 'mujer', 'uploads/product_68cc7ab5a98a5_1758231221.jpeg', '[\"Negro\"]', '[\"U\"]', 21, 1, 'active'),
('prod-68cca61c962fb', 'Pantalon Azul Tela Galleta', 'pantalon-azul-tela-galleta', 'Azul Preciso es un pantalón de corte slim...', 159999.00, 109999.00, 12, 'pantalones', 'hombre', 'uploads/product_68cca61c711bd_1758242332.jpeg', '[\"Azul\"]', '[\"28\",\"30\",\"32\",\"34\",\"36\"]', 31, 1, 'active');

COMMIT;