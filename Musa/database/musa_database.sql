-- ====================================================
-- 🗄️ BASE DE DATOS COMPLETA - M & A MODA ACTUAL
-- ====================================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS musa_moda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE musa_moda;

-- Tabla de categorías
CREATE TABLE categories (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    gender ENUM('hombre', 'mujer', 'unisex') DEFAULT 'unisex',
    description TEXT,
    icon VARCHAR(255),
    sort_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_gender (gender),
    INDEX idx_status (status),
    INDEX idx_slug (slug)
);

-- Tabla de productos
CREATE TABLE products (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    stock_quantity INT DEFAULT 0,
    min_stock INT DEFAULT 5,
    category_id VARCHAR(50),
    gender ENUM('hombre', 'mujer', 'unisex') DEFAULT 'unisex',
    main_image VARCHAR(500),
    gallery JSON, -- Array de imágenes adicionales
    colors JSON, -- Array de colores disponibles
    sizes JSON, -- Array de tallas disponibles
    tags JSON, -- Array de etiquetas para búsqueda
    features JSON, -- Características del producto
    discount_percentage INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_trending BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'draft') DEFAULT 'active',
    meta_title VARCHAR(255),
    meta_description TEXT,
    views_count INT DEFAULT 0,
    sales_count INT DEFAULT 0,
    rating_average DECIMAL(3,2) DEFAULT 0.00,
    rating_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_category (category_id),
    INDEX idx_gender (gender),
    INDEX idx_status (status),
    INDEX idx_price (price),
    INDEX idx_featured (is_featured),
    INDEX idx_trending (is_trending),
    INDEX idx_slug (slug),
    FULLTEXT idx_search (name, description, short_description)
);

-- Tabla de imágenes de productos
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(50),
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_main (is_main)
);

-- Tabla de variantes de productos (colores/tallas específicas)
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id VARCHAR(50),
    sku VARCHAR(100) UNIQUE,
    color VARCHAR(100),
    color_code VARCHAR(7), -- Código hexadecimal
    size VARCHAR(20),
    price_modifier DECIMAL(10,2) DEFAULT 0.00,
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(500),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_sku (sku),
    INDEX idx_color (color),
    INDEX idx_size (size)
);

-- Tabla de pedidos
CREATE TABLE orders (
    id VARCHAR(50) PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50),
    customer_document VARCHAR(50),
    shipping_address TEXT,
    shipping_city VARCHAR(100),
    shipping_department VARCHAR(100),
    shipping_postal_code VARCHAR(20),
    billing_address TEXT,
    subtotal DECIMAL(10,2) NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(100),
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_reference VARCHAR(255),
    tracking_number VARCHAR(255),
    notes TEXT,
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_payment_status (payment_status),
    INDEX idx_customer_email (customer_email),
    INDEX idx_created_at (created_at)
);

-- Tabla de items de pedidos
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50),
    product_id VARCHAR(50),
    variant_id INT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(500),
    color VARCHAR(100),
    size VARCHAR(20),
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_product (product_id)
);

-- Tabla de configuración del sistema
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de usuarios admin
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    role ENUM('admin', 'manager', 'editor') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ====================================================
-- DATOS INICIALES
-- ====================================================

-- Insertar categorías por defecto
INSERT INTO categories (id, name, slug, gender, description, sort_order) VALUES
('hombre', 'Ropa para Hombre', 'hombre', 'hombre', 'Colección completa de ropa masculina', 1),
('mujer', 'Ropa para Mujer', 'mujer', 'mujer', 'Colección completa de ropa femenina', 2),
('camisas', 'Camisas', 'camisas', 'unisex', 'Camisas elegantes y casuales', 3),
('pantalones', 'Pantalones', 'pantalones', 'unisex', 'Pantalones y jeans', 4),
('chaquetas', 'Chaquetas', 'chaquetas', 'unisex', 'Chaquetas y abrigos', 5),
('blazers', 'Blazers', 'blazers', 'unisex', 'Blazers elegantes', 6),
('accesorios', 'Accesorios', 'accesorios', 'unisex', 'Complementos y accesorios', 7);

-- Insertar configuración del sistema
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'M & A MODA ACTUAL', 'string', 'Nombre del sitio web'),
('site_logo', '/images/logo.png', 'string', 'URL del logo del sitio'),
('currency', 'COP', 'string', 'Moneda del sitio'),
('currency_symbol', '$', 'string', 'Símbolo de la moneda'),
('tax_rate', '19', 'number', 'Porcentaje de IVA'),
('free_shipping_minimum', '0', 'number', 'Monto mínimo para envío gratis - SIEMPRE GRATIS'),
('default_shipping_cost', '0', 'number', 'Costo de envío por defecto - SIEMPRE GRATIS'),
('contact_email', 'info@mamoda.com', 'string', 'Email de contacto'),
('contact_phone', '+57 300 123 4567', 'string', 'Teléfono de contacto'),
('business_address', 'Bogotá, Colombia', 'string', 'Dirección del negocio');

-- Insertar usuario admin por defecto (password: admin123)
INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@mamoda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin');

-- ====================================================
-- PRODUCTOS DE MUESTRA
-- ====================================================

-- Productos existentes migrados
INSERT INTO products (
    id, name, slug, description, price, sale_price, stock_quantity, 
    category_id, gender, main_image, colors, sizes, discount_percentage, 
    is_featured, status
) VALUES 
(
    'pantalon-tela-galleta-verde',
    'Pantalón Tela Galleta Slim Fit',
    'pantalon-tela-galleta-slim-fit',
    'Pantalón elegante de tela galleta en corte slim fit, perfecto para ocasiones formales e informales.',
    149000,
    119000,
    25,
    'pantalones',
    'hombre',
    '/images/Pantalon Tela Galleta/PANTALON TELA GALLETA VERDE 1.jpg',
    '["#2E7D32", "#8BC34A", "#4CAF50"]',
    '["28", "30", "32", "34", "36"]',
    20,
    true,
    'active'
),
(
    'pantalon-drill-liso',
    'Pantalón Drill Liso Slim Fit',
    'pantalon-drill-liso-slim-fit',
    'Pantalón de drill liso en corte slim fit, cómodo y versátil para el uso diario.',
    139000,
    111000,
    30,
    'pantalones',
    'hombre',
    '/images/Pantalon Drill Liso/PANTALON DRILL LISO VERDE 1.jpg',
    '["#2E7D32", "#1565C0", "#424242"]',
    '["28", "30", "32", "34", "36"]',
    20,
    true,
    'active'
),
(
    'chaqueta-deportiva-blue-ox',
    'Chaqueta Deportiva Blue Ox',
    'chaqueta-deportiva-blue-ox',
    'Chaqueta deportiva de alta calidad, perfecta para actividades al aire libre y uso casual.',
    199000,
    159000,
    15,
    'chaquetas',
    'unisex',
    '/images/Chaqueta Deportiva Blue Ox/CHAQUETA DEPORTIVA BLUE OX 1.jpg',
    '["#1565C0", "#0D47A1", "#42A5F5"]',
    '["S", "M", "L", "XL"]',
    20,
    true,
    'active'
),
(
    'blazer-elegante-premium',
    'Blazer Elegante Premium',
    'blazer-elegante-premium',
    'Blazer elegante de corte clásico, ideal para reuniones de negocios y eventos formales.',
    299000,
    239000,
    10,
    'blazers',
    'hombre',
    '/images/BLAZER.png',
    '["#212121", "#424242", "#1565C0"]',
    '["38", "40", "42", "44", "46"]',
    20,
    true,
    'active'
),
(
    'camisa-premium-collection',
    'Camisa Premium Collection',
    'camisa-premium-collection',
    'Camisa de alta calidad con acabados premium, perfecta para cualquier ocasión especial.',
    129000,
    103000,
    20,
    'camisas',
    'hombre',
    '/images/placeholder.svg',
    '["#FFFFFF", "#E3F2FD", "#FFECB3"]',
    '["S", "M", "L", "XL"]',
    20,
    false,
    'active'
),
(
    'chaqueta-moonlit-collection',
    'Chaqueta Moonlit Collection',
    'chaqueta-moonlit-collection',
    'Chaqueta de la colección Moonlit con diseño moderno y materiales de primera calidad.',
    249000,
    199000,
    12,
    'chaquetas',
    'mujer',
    '/images/placeholder.svg',
    '["#9C27B0", "#E1BEE7", "#CE93D8"]',
    '["XS", "S", "M", "L"]',
    20,
    false,
    'active'
);
