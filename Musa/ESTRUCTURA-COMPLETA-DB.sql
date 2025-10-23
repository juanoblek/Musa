-- =====================================================
-- M & A MODA - ESTRUCTURA COMPLETA DE BASE DE DATOS
-- Ejecutar en phpMyAdmin > SQL
-- TODAS LAS TABLAS INCLUIDAS
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- Eliminar tablas existentes (si existen)
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `shopping_cart`;
DROP TABLE IF EXISTS `product_reviews`;
DROP TABLE IF EXISTS `product_images`;
DROP TABLE IF EXISTS `customer_addresses`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `coupons`;
DROP TABLE IF EXISTS `shipping_methods`;
DROP TABLE IF EXISTS `payment_methods`;
DROP TABLE IF EXISTS `newsletter_subscribers`;
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `admin_sessions`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `admin_users`;
DROP TABLE IF EXISTS `system_settings`;

-- =====================================================
-- TABLA: categories
-- =====================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` varchar(50) NOT NULL,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `gender` enum('hombre','mujer','unisex') DEFAULT 'unisex',
    `description` text DEFAULT NULL,
    `icon` varchar(255) DEFAULT NULL,
    `sort_order` int(11) DEFAULT 0,
    `status` enum('active','inactive') DEFAULT 'active',
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: products
-- =====================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` varchar(50) NOT NULL,
    `name` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `short_description` varchar(500) DEFAULT NULL,
    `price` decimal(10,2) NOT NULL,
    `sale_price` decimal(10,2) DEFAULT NULL,
    `stock_quantity` int(11) DEFAULT 0,
    `min_stock` int(11) DEFAULT 5,
    `category_id` varchar(50) DEFAULT NULL,
    `gender` enum('hombre','mujer','unisex') DEFAULT 'unisex',
    `main_image` varchar(500) DEFAULT NULL,
    `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
    `colors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`colors`)),
    `sizes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`sizes`)),
    `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
    `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
    `discount_percentage` int(11) DEFAULT 0,
    `is_featured` tinyint(1) DEFAULT 0,
    `is_trending` tinyint(1) DEFAULT 0,
    `status` enum('active','inactive','draft') DEFAULT 'active',
    `meta_title` varchar(255) DEFAULT NULL,
    `meta_description` text DEFAULT NULL,
    `views_count` int(11) DEFAULT 0,
    `sales_count` int(11) DEFAULT 0,
    `rating_average` decimal(3,2) DEFAULT 0.00,
    `rating_count` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `products_category_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: admin_users
-- =====================================================
CREATE TABLE IF NOT EXISTS `admin_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(100) NOT NULL,
    `email` varchar(255) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `full_name` varchar(255) DEFAULT NULL,
    `role` enum('admin','manager','editor') DEFAULT 'admin',
    `status` enum('active','inactive') DEFAULT 'active',
    `last_login` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: system_settings (CONFIGURACIONES)
-- =====================================================
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) DEFAULT NULL,
    `setting_value` text DEFAULT NULL,
    `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
    `description` text DEFAULT NULL,
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: customers (CLIENTES)
-- =====================================================
CREATE TABLE IF NOT EXISTS `customers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `password_hash` varchar(255) NOT NULL,
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `date_of_birth` date DEFAULT NULL,
    `gender` enum('male','female','other') DEFAULT NULL,
    `status` enum('active','inactive','suspended') DEFAULT 'active',
    `email_verified` tinyint(1) DEFAULT 0,
    `phone_verified` tinyint(1) DEFAULT 0,
    `last_login` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: customer_addresses (DIRECCIONES DE CLIENTES)
-- =====================================================
CREATE TABLE IF NOT EXISTS `customer_addresses` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) NOT NULL,
    `type` enum('billing','shipping','both') DEFAULT 'both',
    `first_name` varchar(100) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `company` varchar(255) DEFAULT NULL,
    `address_line_1` varchar(255) NOT NULL,
    `address_line_2` varchar(255) DEFAULT NULL,
    `city` varchar(100) NOT NULL,
    `state` varchar(100) NOT NULL,
    `postal_code` varchar(20) NOT NULL,
    `country` varchar(100) DEFAULT 'Colombia',
    `phone` varchar(20) DEFAULT NULL,
    `is_default` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: product_images (IMÁGENES DE PRODUCTOS)
-- =====================================================
CREATE TABLE IF NOT EXISTS `product_images` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` varchar(50) NOT NULL,
    `image_url` varchar(500) NOT NULL,
    `alt_text` varchar(255) DEFAULT NULL,
    `sort_order` int(11) DEFAULT 0,
    `is_main` tinyint(1) DEFAULT 0,
    `file_size` int(11) DEFAULT NULL,
    `width` int(11) DEFAULT NULL,
    `height` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: product_reviews (RESEÑAS DE PRODUCTOS)
-- =====================================================
CREATE TABLE IF NOT EXISTS `product_reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` varchar(50) NOT NULL,
    `customer_id` int(11) DEFAULT NULL,
    `customer_name` varchar(255) NOT NULL,
    `customer_email` varchar(255) NOT NULL,
    `rating` int(11) NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `title` varchar(255) DEFAULT NULL,
    `comment` text DEFAULT NULL,
    `status` enum('pending','approved','rejected') DEFAULT 'pending',
    `helpful_count` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `product_id` (`product_id`),
    KEY `customer_id` (`customer_id`),
    CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: shopping_cart (CARRITO DE COMPRAS)
-- =====================================================
CREATE TABLE IF NOT EXISTS `shopping_cart` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `customer_id` int(11) DEFAULT NULL,
    `session_id` varchar(255) DEFAULT NULL,
    `product_id` varchar(50) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 1,
    `selected_color` varchar(50) DEFAULT NULL,
    `selected_size` varchar(50) DEFAULT NULL,
    `price_at_time` decimal(10,2) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `customer_id` (`customer_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: coupons (CUPONES DE DESCUENTO)
-- =====================================================
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(50) NOT NULL,
    `type` enum('percentage','fixed','free_shipping') NOT NULL,
    `value` decimal(10,2) NOT NULL,
    `minimum_amount` decimal(10,2) DEFAULT NULL,
    `maximum_discount` decimal(10,2) DEFAULT NULL,
    `usage_limit` int(11) DEFAULT NULL,
    `used_count` int(11) DEFAULT 0,
    `start_date` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `status` enum('active','inactive','expired') DEFAULT 'active',
    `description` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: shipping_methods (MÉTODOS DE ENVÍO)
-- =====================================================
CREATE TABLE IF NOT EXISTS `shipping_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `cost` decimal(10,2) NOT NULL,
    `free_shipping_min` decimal(10,2) DEFAULT NULL,
    `estimated_days_min` int(11) DEFAULT NULL,
    `estimated_days_max` int(11) DEFAULT NULL,
    `status` enum('active','inactive') DEFAULT 'active',
    `sort_order` int(11) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: payment_methods (MÉTODOS DE PAGO)
-- =====================================================
CREATE TABLE IF NOT EXISTS `payment_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text DEFAULT NULL,
    `type` enum('cash','transfer','credit_card','digital_wallet','crypto') NOT NULL,
    `provider` varchar(100) DEFAULT NULL,
    `fee_percentage` decimal(5,2) DEFAULT 0.00,
    `fee_fixed` decimal(10,2) DEFAULT 0.00,
    `status` enum('active','inactive') DEFAULT 'active',
    `sort_order` int(11) DEFAULT 0,
    `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: orders (PEDIDOS)
-- =====================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_number` varchar(50) NOT NULL,
    `customer_id` int(11) DEFAULT NULL,
    `customer_email` varchar(255) NOT NULL,
    `customer_name` varchar(255) NOT NULL,
    `customer_phone` varchar(20) DEFAULT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    `tax_amount` decimal(10,2) DEFAULT 0.00,
    `shipping_cost` decimal(10,2) DEFAULT 0.00,
    `discount_amount` decimal(10,2) DEFAULT 0.00,
    `total_amount` decimal(10,2) NOT NULL,
    `currency` varchar(3) DEFAULT 'COP',
    `status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
    `payment_status` enum('pending','paid','failed','refunded','partial') DEFAULT 'pending',
    `payment_method_id` int(11) DEFAULT NULL,
    `shipping_method_id` int(11) DEFAULT NULL,
    `coupon_code` varchar(50) DEFAULT NULL,
    `billing_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`billing_address`)),
    `shipping_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`shipping_address`)),
    `notes` text DEFAULT NULL,
    `tracking_number` varchar(255) DEFAULT NULL,
    `shipped_at` timestamp NULL DEFAULT NULL,
    `delivered_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `order_number` (`order_number`),
    KEY `customer_id` (`customer_id`),
    KEY `payment_method_id` (`payment_method_id`),
    KEY `shipping_method_id` (`shipping_method_id`),
    CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`) ON DELETE SET NULL,
    CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: order_items (ITEMS DE PEDIDOS)
-- =====================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` varchar(50) NOT NULL,
    `product_name` varchar(255) NOT NULL,
    `product_sku` varchar(100) DEFAULT NULL,
    `quantity` int(11) NOT NULL,
    `unit_price` decimal(10,2) NOT NULL,
    `total_price` decimal(10,2) NOT NULL,
    `selected_color` varchar(50) DEFAULT NULL,
    `selected_size` varchar(50) DEFAULT NULL,
    `product_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`product_data`)),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `order_id` (`order_id`),
    KEY `product_id` (`product_id`),
    CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: newsletter_subscribers (SUSCRIPTORES)
-- =====================================================
CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) NOT NULL,
    `name` varchar(255) DEFAULT NULL,
    `status` enum('subscribed','unsubscribed','bounced') DEFAULT 'subscribed',
    `subscription_source` varchar(100) DEFAULT 'website',
    `preferences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`preferences`)),
    `unsubscribe_token` varchar(255) DEFAULT NULL,
    `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `unsubscribed_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: contact_messages (MENSAJES DE CONTACTO)
-- =====================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `phone` varchar(20) DEFAULT NULL,
    `subject` varchar(255) NOT NULL,
    `message` text NOT NULL,
    `status` enum('new','read','replied','closed') DEFAULT 'new',
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `replied_at` timestamp NULL DEFAULT NULL,
    `replied_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `replied_by` (`replied_by`),
    CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`replied_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: admin_sessions (SESIONES DE ADMIN)
-- =====================================================
CREATE TABLE IF NOT EXISTS `admin_sessions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_user_id` int(11) NOT NULL,
    `session_token` varchar(255) NOT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `expires_at` timestamp NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `session_token` (`session_token`),
    KEY `admin_user_id` (`admin_user_id`),
    CONSTRAINT `admin_sessions_ibfk_1` FOREIGN KEY (`admin_user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: activity_logs (LOGS DE ACTIVIDAD)
-- =====================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_type` enum('admin','customer','system') NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `action` varchar(100) NOT NULL,
    `entity_type` varchar(50) DEFAULT NULL,
    `entity_id` varchar(50) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
    `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
    `ip_address` varchar(45) DEFAULT NULL,
    `user_agent` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_type_id` (`user_type`, `user_id`),
    KEY `entity` (`entity_type`, `entity_id`),
    KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES: CATEGORÍAS
-- =====================================================
INSERT INTO `categories` (`id`, `name`, `slug`, `gender`, `description`, `sort_order`, `status`) VALUES
('hombre', 'Ropa para Hombre', 'hombre', 'hombre', 'Colección completa de ropa masculina', 1, 'active'),
('mujer', 'Ropa para Mujer', 'mujer', 'mujer', 'Colección completa de ropa femenina', 2, 'active'),
('camisas', 'Camisas', 'camisas', 'unisex', 'Camisas elegantes y casuales', 3, 'active'),
('pantalones', 'Pantalones', 'pantalones', 'unisex', 'Pantalones y jeans', 4, 'active'),
('chaquetas', 'Chaquetas', 'chaquetas', 'unisex', 'Chaquetas y abrigos', 5, 'active'),
('blazers', 'Blazers', 'blazers', 'unisex', 'Blazers elegantes', 6, 'active'),
('accesorios', 'Accesorios', 'accesorios', 'unisex', 'Complementos y accesorios', 7, 'active');

-- =====================================================
-- DATOS INICIALES: USUARIO ADMINISTRADOR
-- =====================================================
INSERT INTO `admin_users` (`username`, `email`, `password_hash`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@musaarion.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador M&A', 'admin', 'active');

-- Nota: La contraseña por defecto es "admin123"
-- ¡IMPORTANTE! Cambiar después del primer acceso

-- =====================================================
-- DATOS INICIALES: CONFIGURACIONES DEL SISTEMA
-- =====================================================
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'M & A MODA ACTUAL', 'string', 'Nombre del sitio web'),
('currency', 'COP', 'string', 'Moneda del sitio'),
('currency_symbol', '$', 'string', 'Símbolo de la moneda'),
('contact_email', 'info@musaarion.com', 'string', 'Email de contacto'),
('contact_phone', '+57 300 123 4567', 'string', 'Teléfono de contacto'),
('store_address', 'Colombia', 'string', 'Dirección de la tienda'),
('tax_rate', '19', 'number', 'Porcentaje de IVA'),
('shipping_cost', '0', 'number', 'Costo de envío estándar - SIEMPRE GRATIS'),
('free_shipping_min', '0', 'number', 'Monto mínimo para envío gratis - SIEMPRE GRATIS'),
('products_per_page', '12', 'number', 'Productos por página'),
('enable_reviews', '1', 'boolean', 'Habilitar sistema de reseñas'),
('enable_wishlist', '1', 'boolean', 'Habilitar lista de deseos'),
('maintenance_mode', '0', 'boolean', 'Modo de mantenimiento'),
('google_analytics', '', 'string', 'Código de Google Analytics'),
('facebook_pixel', '', 'string', 'Código de Facebook Pixel');

-- =====================================================
-- PRODUCTOS DE EJEMPLO (OPCIONAL)
-- =====================================================
INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `price`, `category_id`, `gender`, `stock_quantity`, `is_featured`, `status`) VALUES
('camisa-elegante-001', 'Camisa Elegante Clásica', 'camisa-elegante-clasica', 'Camisa elegante de corte clásico, perfecta para ocasiones formales y casuales elegantes.', 'Camisa elegante de corte clásico', 85000.00, 'camisas', 'unisex', 25, 1, 'active'),
('pantalon-formal-001', 'Pantalón Formal Premium', 'pantalon-formal-premium', 'Pantalón formal de alta calidad, confeccionado con telas premium para un look profesional.', 'Pantalón formal de alta calidad', 120000.00, 'pantalones', 'unisex', 20, 1, 'active'),
('blazer-ejecutivo-001', 'Blazer Ejecutivo', 'blazer-ejecutivo', 'Blazer ejecutivo de corte moderno, ideal para reuniones de negocios y eventos formales.', 'Blazer ejecutivo de corte moderno', 180000.00, 'blazers', 'unisex', 15, 1, 'active');

-- =====================================================
-- DATOS INICIALES: MÉTODOS DE ENVÍO
-- =====================================================
INSERT INTO `shipping_methods` (`name`, `description`, `cost`, `free_shipping_min`, `estimated_days_min`, `estimated_days_max`, `status`, `sort_order`) VALUES
('Envío Estándar', 'Entrega en 3-5 días hábiles', 15000.00, 100000.00, 3, 5, 'active', 1),
('Envío Express', 'Entrega en 1-2 días hábiles', 25000.00, 200000.00, 1, 2, 'active', 2),
('Recogida en Tienda', 'Recoge tu pedido en nuestra tienda física', 0.00, NULL, 1, 1, 'active', 3),
('Envío Contraentrega', 'Paga al recibir tu pedido', 20000.00, NULL, 3, 7, 'active', 4);

-- =====================================================
-- DATOS INICIALES: MÉTODOS DE PAGO
-- =====================================================
INSERT INTO `payment_methods` (`name`, `description`, `type`, `provider`, `fee_percentage`, `fee_fixed`, `status`, `sort_order`) VALUES
('Transferencia Bancaria', 'Pago mediante transferencia bancaria', 'transfer', 'manual', 0.00, 0.00, 'active', 1),
('Pago Contraentrega', 'Paga en efectivo al recibir tu pedido', 'cash', 'manual', 0.00, 0.00, 'active', 2),
('Tarjeta de Crédito/Débito', 'Visa, Mastercard, American Express', 'credit_card', 'stripe', 2.90, 0.00, 'active', 3),
('PSE', 'Pago Seguro en Línea', 'digital_wallet', 'epayco', 3.49, 0.00, 'active', 4),
('Nequi', 'Pago con billetera digital Nequi', 'digital_wallet', 'nequi', 1.95, 0.00, 'active', 5),
('Daviplata', 'Pago con billetera digital Daviplata', 'digital_wallet', 'daviplata', 1.95, 0.00, 'active', 6);

-- =====================================================
-- DATOS INICIALES: CUPONES DE EJEMPLO
-- =====================================================
INSERT INTO `coupons` (`code`, `type`, `value`, `minimum_amount`, `usage_limit`, `start_date`, `end_date`, `status`, `description`) VALUES
('BIENVENIDO10', 'percentage', 10.00, 50000.00, 100, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 'active', 'Descuento del 10% para nuevos clientes'),
('ENVIOGRATIS', 'free_shipping', 0.00, 80000.00, NULL, '2024-01-01 00:00:00', '2025-12-31 23:59:59', 'active', 'Envío gratis en compras superiores a $80.000'),
('VERANO2024', 'fixed', 20000.00, 100000.00, 50, '2024-06-01 00:00:00', '2024-08-31 23:59:59', 'active', 'Descuento fijo de $20.000 en compras de verano');

-- =====================================================
-- DATOS INICIALES: CONFIGURACIONES ADICIONALES
-- =====================================================
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('instagram_url', 'https://instagram.com/musaarion', 'string', 'URL de Instagram'),
('facebook_url', 'https://facebook.com/musaarion', 'string', 'URL de Facebook'),
('whatsapp_number', '+573001234567', 'string', 'Número de WhatsApp'),
('business_hours', '{"monday":"9:00-18:00","tuesday":"9:00-18:00","wednesday":"9:00-18:00","thursday":"9:00-18:00","friday":"9:00-18:00","saturday":"9:00-17:00","sunday":"closed"}', 'json', 'Horarios de atención'),
('return_policy_days', '15', 'number', 'Días para devoluciones'),
('warranty_days', '30', 'number', 'Días de garantía'),
('max_cart_items', '50', 'number', 'Máximo de items en carrito'),
('auto_approve_reviews', '0', 'boolean', 'Auto-aprobar reseñas'),
('require_account_purchase', '0', 'boolean', 'Requerir cuenta para comprar'),
('inventory_tracking', '1', 'boolean', 'Seguimiento de inventario'),
('low_stock_threshold', '5', 'number', 'Umbral de stock bajo'),
('backup_frequency', 'daily', 'string', 'Frecuencia de respaldos'),
('site_logo', '/assets/images/logo.png', 'string', 'Ruta del logo del sitio'),
('favicon', '/assets/images/favicon.ico', 'string', 'Ruta del favicon'),
('default_meta_description', 'M&A Moda Actual - Ropa elegante y moderna para hombre y mujer. Calidad premium, diseños únicos y envíos a toda Colombia.', 'string', 'Meta descripción por defecto'),
('seo_keywords', 'ropa, moda, elegante, hombre, mujer, colombia, online, tienda', 'string', 'Palabras clave SEO');

-- =====================================================
-- DATOS INICIALES: PRODUCTOS ADICIONALES
-- =====================================================
INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `category_id`, `gender`, `stock_quantity`, `colors`, `sizes`, `is_featured`, `is_trending`, `status`) VALUES
('chaqueta-cuero-001', 'Chaqueta de Cuero Premium', 'chaqueta-cuero-premium', 'Chaqueta de cuero genuino, perfecta para looks casuales y elegantes. Confeccionada con materiales de alta calidad.', 'Chaqueta de cuero genuino premium', 350000.00, 280000.00, 'chaquetas', 'unisex', 12, '["Negro", "Marrón", "Cognac"]', '["S", "M", "L", "XL"]', 1, 1, 'active'),
('vestido-elegante-001', 'Vestido Elegante de Noche', 'vestido-elegante-noche', 'Vestido elegante perfecto para ocasiones especiales y eventos nocturnos. Diseño sofisticado y corte favorecedor.', 'Vestido elegante para ocasiones especiales', 280000.00, NULL, 'mujer', 'mujer', 8, '["Negro", "Azul Marino", "Rojo"]', '["XS", "S", "M", "L"]', 1, 0, 'active'),
('zapatos-formales-001', 'Zapatos Formales Clásicos', 'zapatos-formales-clasicos', 'Zapatos formales de cuero genuino, ideales para el trabajo y ocasiones elegantes. Cómodos y duraderos.', 'Zapatos formales de cuero genuino', 220000.00, NULL, 'accesorios', 'hombre', 15, '["Negro", "Marrón"]', '["38", "39", "40", "41", "42", "43", "44"]', 0, 1, 'active'),
('bolso-elegante-001', 'Bolso Elegante de Mujer', 'bolso-elegante-mujer', 'Bolso elegante perfecto para complementar cualquier outfit. Diseño moderno con compartimentos funcionales.', 'Bolso elegante y funcional', 150000.00, 120000.00, 'accesorios', 'mujer', 20, '["Negro", "Beige", "Rojo"]', '["Único"]', 0, 0, 'active');

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- VERIFICACIÓN DE INSTALACIÓN
-- =====================================================
-- Ejecutar estas consultas para verificar que todo está bien:

SELECT 'TABLAS CREADAS:' as info;
SELECT 
    TABLE_NAME as tabla,
    TABLE_ROWS as filas
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = DATABASE() 
ORDER BY TABLE_NAME;

SELECT 'CONTEO DE DATOS:' as info;
SELECT COUNT(*) as total_categories FROM categories;
SELECT COUNT(*) as total_products FROM products;
SELECT COUNT(*) as total_admin_users FROM admin_users;
SELECT COUNT(*) as total_settings FROM system_settings;
SELECT COUNT(*) as total_shipping_methods FROM shipping_methods;
SELECT COUNT(*) as total_payment_methods FROM payment_methods;
SELECT COUNT(*) as total_coupons FROM coupons;

-- =====================================================
-- INSTALACIÓN COMPLETADA EXITOSAMENTE
-- =====================================================
-- 
-- 📊 RESUMEN DE TABLAS CREADAS:
-- 
-- 🏢 CORE (Principales):
-- - categories (categorías)
-- - products (productos)
-- - admin_users (usuarios administradores)
-- - system_settings (configuraciones)
-- 
-- 👥 CLIENTES:
-- - customers (clientes)
-- - customer_addresses (direcciones)
-- - shopping_cart (carrito)
-- 
-- 🛒 PEDIDOS Y VENTAS:
-- - orders (pedidos)
-- - order_items (items de pedidos)
-- - coupons (cupones)
-- - shipping_methods (envíos)
-- - payment_methods (pagos)
-- 
-- 📝 CONTENIDO:
-- - product_images (imágenes)
-- - product_reviews (reseñas)
-- - newsletter_subscribers (suscriptores)
-- - contact_messages (mensajes)
-- 
-- 🔐 SEGURIDAD Y LOGS:
-- - admin_sessions (sesiones)
-- - activity_logs (logs de actividad)
-- 
-- =====================================================
-- DATOS DE ACCESO:
-- =====================================================
-- 
-- 🔑 PANEL ADMINISTRATIVO:
-- URL: https://tudominio.com/admin-panel.html
-- Usuario: admin
-- Contraseña: admin123
-- Email: admin@musaarion.com
-- 
-- 📧 CONFIGURACIÓN:
-- - 7 categorías básicas
-- - 7 productos de ejemplo
-- - 4 métodos de envío
-- - 6 métodos de pago
-- - 3 cupones de ejemplo
-- - 20+ configuraciones del sistema
-- 
-- ⚠️ IMPORTANTE - PRÓXIMOS PASOS:
-- 1. Cambiar contraseña del administrador
-- 2. Configurar métodos de pago reales
-- 3. Personalizar configuraciones del sitio
-- 4. Subir imágenes de productos
-- 5. Configurar envíos según tu ubicación
-- 
-- =====================================================
