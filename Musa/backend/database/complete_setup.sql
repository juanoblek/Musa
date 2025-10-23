-- Musa & Arion Database Setup Script
-- Complete initialization with sample data

BEGIN;

-- Create database if it doesn't exist (run this separately)
-- CREATE DATABASE musa_arion;

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users table (for admin authentication)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'admin',
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    parent_id INTEGER REFERENCES categories(id),
    sort_order INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    sale_price DECIMAL(10, 2),
    sku VARCHAR(100) UNIQUE,
    category_id INTEGER REFERENCES categories(id),
    gender VARCHAR(20) CHECK (gender IN ('hombre', 'mujer', 'unisex')),
    is_active BOOLEAN DEFAULT true,
    featured BOOLEAN DEFAULT false,
    stock_quantity INTEGER DEFAULT 0,
    weight DECIMAL(8, 2),
    dimensions VARCHAR(100),
    material VARCHAR(255),
    care_instructions TEXT,
    meta_title VARCHAR(255),
    meta_description TEXT,
    meta_keywords TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product images table
CREATE TABLE IF NOT EXISTS product_images (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    sort_order INTEGER DEFAULT 0,
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product colors table
CREATE TABLE IF NOT EXISTS product_colors (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    color_name VARCHAR(50) NOT NULL,
    color_code VARCHAR(7), -- hex color code
    additional_price DECIMAL(10, 2) DEFAULT 0,
    stock_quantity INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Product sizes table
CREATE TABLE IF NOT EXISTS product_sizes (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    size_name VARCHAR(20) NOT NULL,
    size_order INTEGER DEFAULT 0,
    additional_price DECIMAL(10, 2) DEFAULT 0,
    stock_quantity INTEGER DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20),
    shipping_address JSONB NOT NULL,
    billing_address JSONB,
    subtotal DECIMAL(10, 2) NOT NULL,
    shipping_cost DECIMAL(10, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_id VARCHAR(255),
    order_status VARCHAR(50) DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id),
    product_name VARCHAR(255) NOT NULL,
    product_sku VARCHAR(100),
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    selected_color VARCHAR(50),
    selected_size VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_gender ON products(gender);
CREATE INDEX IF NOT EXISTS idx_products_featured ON products(featured);
CREATE INDEX IF NOT EXISTS idx_products_active ON products(is_active);
CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(order_status);
CREATE INDEX IF NOT EXISTS idx_orders_customer ON orders(customer_email);
CREATE INDEX IF NOT EXISTS idx_product_images_product ON product_images(product_id);
CREATE INDEX IF NOT EXISTS idx_product_colors_product ON product_colors(product_id);
CREATE INDEX IF NOT EXISTS idx_product_sizes_product ON product_sizes(product_id);

-- Insert sample categories
INSERT INTO categories (name, slug, description, sort_order) VALUES 
('Camisas', 'camisas', 'Camisas elegantes y casuales', 1),
('Chaquetas', 'chaquetas', 'Chaquetas y abrigos', 2),
('Pantalones', 'pantalones', 'Pantalones y jeans', 3),
('Blazers', 'blazers', 'Blazers y sacos', 4),
('Accesorios', 'accesorios', 'Accesorios de moda', 5),
('Tejidos', 'tejidos', 'Productos tejidos', 6)
ON CONFLICT (slug) DO NOTHING;

-- Insert sample products (using existing image structure)
INSERT INTO products (name, slug, description, price, sale_price, sku, category_id, gender, featured, stock_quantity, meta_title, meta_description) VALUES 
('Camisa Blanco Purista', 'camisa-blanco-purista', 'Camisa elegante de color blanco, perfecta para ocasiones formales', 89900, 79900, 'CAM-BP-001', 1, 'unisex', true, 25, 'Camisa Blanco Purista - Musa & Arion', 'Camisa elegante blanca de alta calidad'),
('Camisa Elegancia Escarlata', 'camisa-elegancia-escarlata', 'Camisa de color escarlata con estilo refinado', 92900, 82900, 'CAM-EE-001', 1, 'unisex', true, 20, 'Camisa Elegancia Escarlata - Musa & Arion', 'Camisa escarlata elegante y sofisticada'),
('Camisa Gris Vanguardista', 'camisa-gris-vanguardista', 'Camisa gris con diseño vanguardista y moderno', 94900, 84900, 'CAM-GV-001', 1, 'unisex', true, 18, 'Camisa Gris Vanguardista - Musa & Arion', 'Camisa gris de diseño vanguardista'),
('Camisa Noir Refinado Negra', 'camisa-noir-refinado-negra', 'Camisa negra de estilo refinado y elegante', 96900, 86900, 'CAM-NR-001', 1, 'unisex', true, 22, 'Camisa Noir Refinado Negra - Musa & Arion', 'Camisa negra refinada y elegante'),
('Chaqueta Beige Botones Dorados', 'chaqueta-beige-botones-dorados', 'Chaqueta beige con botones dorados de lujo', 189900, 169900, 'CHA-BBD-001', 2, 'mujer', true, 15, 'Chaqueta Beige Botones Dorados - Musa & Arion', 'Chaqueta beige de lujo con botones dorados'),
('Chaqueta Deportiva Blue Ox', 'chaqueta-deportiva-blue-ox', 'Chaqueta deportiva azul de alta calidad', 149900, 129900, 'CHA-DBO-001', 2, 'hombre', true, 30, 'Chaqueta Deportiva Blue Ox - Musa & Arion', 'Chaqueta deportiva azul de alta calidad'),
('Blazer Cuadrado', 'blazer-cuadrado', 'Blazer con patrón cuadrado elegante', 199900, 179900, 'BLZ-CUA-001', 4, 'unisex', true, 12, 'Blazer Cuadrado - Musa & Arion', 'Blazer elegante con patrón cuadrado'),
('Blazer Morado', 'blazer-morado', 'Blazer de color morado vibrante', 199900, 179900, 'BLZ-MOR-001', 4, 'unisex', true, 10, 'Blazer Morado - Musa & Arion', 'Blazer morado vibrante y elegante'),
('Pantalón Drill Liso', 'pantalon-drill-liso', 'Pantalón de drill liso, cómodo y versátil', 79900, 69900, 'PAN-DL-001', 3, 'unisex', false, 35, 'Pantalón Drill Liso - Musa & Arion', 'Pantalón de drill liso y cómodo'),
('Pantalón Tela Galleta', 'pantalon-tela-galleta', 'Pantalón de tela galleta, suave y cómodo', 84900, 74900, 'PAN-TG-001', 3, 'unisex', false, 28, 'Pantalón Tela Galleta - Musa & Arion', 'Pantalón de tela galleta suave')
ON CONFLICT (slug) DO NOTHING;

-- Insert sample product images (using existing image paths)
INSERT INTO product_images (product_id, image_url, alt_text, sort_order, is_primary) VALUES 
(1, 'images/Camisa Blanco Purista/Camisa Blanco Purista.jpeg', 'Camisa Blanco Purista', 0, true),
(2, 'images/Camisa Elegancia Escarlata/Camisa Elegancia Escarlata.jpeg', 'Camisa Elegancia Escarlata', 0, true),
(3, 'images/Camisa Gris Vanguardista/Camisa Gris Vanguardista.jpeg', 'Camisa Gris Vanguardista', 0, true),
(4, 'images/Camisa Noir Refinado Negra/Camisa Noir Refinado Negra.jpeg', 'Camisa Noir Refinado Negra', 0, true),
(5, 'images/Chaqueta Beige Botones Dorados/1.jpeg', 'Chaqueta Beige Botones Dorados', 0, true),
(6, 'images/Chaqueta Deportiva Blue Ox/1.jpeg', 'Chaqueta Deportiva Blue Ox', 0, true),
(7, 'images/BLAZER CUADRADO.png', 'Blazer Cuadrado', 0, true),
(8, 'images/BLAZER MORADO.png', 'Blazer Morado', 0, true),
(9, 'images/Pantalon Drill Liso/1.jpeg', 'Pantalón Drill Liso', 0, true),
(10, 'images/Pantalon Tela Galleta/1.jpeg', 'Pantalón Tela Galleta', 0, true)
ON CONFLICT DO NOTHING;

-- Insert sample colors
INSERT INTO product_colors (product_id, color_name, color_code, stock_quantity) VALUES 
(1, 'Blanco', '#FFFFFF', 25),
(2, 'Escarlata', '#DC143C', 20),
(3, 'Gris', '#808080', 18),
(4, 'Negro', '#000000', 22),
(5, 'Beige', '#F5F5DC', 15),
(6, 'Azul', '#0000FF', 30),
(7, 'Gris Cuadrado', '#A9A9A9', 12),
(8, 'Morado', '#800080', 10),
(9, 'Khaki', '#F0E68C', 35),
(10, 'Galleta', '#DEB887', 28)
ON CONFLICT DO NOTHING;

-- Insert sample sizes
INSERT INTO product_sizes (product_id, size_name, size_order, stock_quantity) VALUES 
-- Camisas
(1, 'S', 1, 8), (1, 'M', 2, 10), (1, 'L', 3, 7),
(2, 'S', 1, 6), (2, 'M', 2, 8), (2, 'L', 3, 6),
(3, 'S', 1, 5), (3, 'M', 2, 8), (3, 'L', 3, 5),
(4, 'S', 1, 7), (4, 'M', 2, 9), (4, 'L', 3, 6),
-- Chaquetas
(5, 'S', 1, 4), (5, 'M', 2, 6), (5, 'L', 3, 5),
(6, 'M', 2, 10), (6, 'L', 3, 12), (6, 'XL', 4, 8),
-- Blazers
(7, 'M', 2, 4), (7, 'L', 3, 5), (7, 'XL', 4, 3),
(8, 'S', 1, 3), (8, 'M', 2, 4), (8, 'L', 3, 3),
-- Pantalones
(9, '30', 1, 12), (9, '32', 2, 15), (9, '34', 3, 8),
(10, '30', 1, 10), (10, '32', 2, 12), (10, '34', 3, 6)
ON CONFLICT DO NOTHING;

-- Insert sample admin user (password: admin123)
INSERT INTO users (email, password_hash, role, name) VALUES 
('admin@musaarion.com', '$2b$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administrador Principal')
ON CONFLICT (email) DO NOTHING;

-- Create triggers for updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Apply triggers to tables
DROP TRIGGER IF EXISTS update_products_updated_at ON products;
CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_categories_updated_at ON categories;
CREATE TRIGGER update_categories_updated_at BEFORE UPDATE ON categories
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_orders_updated_at ON orders;
CREATE TRIGGER update_orders_updated_at BEFORE UPDATE ON orders
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_users_updated_at ON users;
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

COMMIT;

-- Display summary
SELECT 
    'Categories' as table_name, 
    COUNT(*) as records 
FROM categories 
WHERE is_active = true
UNION ALL
SELECT 
    'Products' as table_name, 
    COUNT(*) as records 
FROM products 
WHERE is_active = true
UNION ALL
SELECT 
    'Product Images' as table_name, 
    COUNT(*) as records 
FROM product_images
UNION ALL
SELECT 
    'Product Colors' as table_name, 
    COUNT(*) as records 
FROM product_colors
UNION ALL
SELECT 
    'Product Sizes' as table_name, 
    COUNT(*) as records 
FROM product_sizes
UNION ALL
SELECT 
    'Users' as table_name, 
    COUNT(*) as records 
FROM users;

-- Success message
SELECT 'Database setup completed successfully!' as message;
