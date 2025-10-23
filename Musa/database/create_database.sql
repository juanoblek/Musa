-- =====================================================
-- SISTEMA DE BASE DE DATOS COMPLETO PARA MUSA & ARION
-- =====================================================
-- Ejecutar este archivo completo en phpMyAdmin
-- =====================================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS musa_arion_store 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE musa_arion_store;

-- =====================================================
-- TABLA DE USUARIOS ADMINISTRADORES
-- =====================================================
CREATE TABLE usuarios_admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- TABLA DE CATEGORÍAS
-- =====================================================
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    activa TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_activa (activa)
);

-- =====================================================
-- TABLA DE PRODUCTOS
-- =====================================================
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    slug VARCHAR(300) UNIQUE NOT NULL,
    descripcion TEXT,
    descripcion_corta VARCHAR(500),
    precio DECIMAL(10,2) NOT NULL,
    precio_oferta DECIMAL(10,2) NULL,
    sku VARCHAR(100) UNIQUE,
    stock INT DEFAULT 0,
    stock_minimo INT DEFAULT 5,
    categoria_id INT,
    peso DECIMAL(8,2) DEFAULT 0,
    dimensiones VARCHAR(100),
    material VARCHAR(255),
    color VARCHAR(100),
    talla VARCHAR(50),
    genero ENUM('hombre', 'mujer', 'unisex') DEFAULT 'unisex',
    temporada ENUM('verano', 'invierno', 'primavera', 'otoño', 'todo_año') DEFAULT 'todo_año',
    destacado TINYINT(1) DEFAULT 0,
    nuevo TINYINT(1) DEFAULT 0,
    oferta TINYINT(1) DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    visualizaciones INT DEFAULT 0,
    ventas_totales INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_categoria (categoria_id),
    INDEX idx_activo (activo),
    INDEX idx_destacado (destacado),
    INDEX idx_precio (precio),
    INDEX idx_stock (stock),
    FULLTEXT idx_busqueda (nombre, descripcion, descripcion_corta)
);

-- =====================================================
-- TABLA DE IMÁGENES DE PRODUCTOS
-- =====================================================
CREATE TABLE productos_imagenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    imagen VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255),
    es_principal TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_producto (producto_id),
    INDEX idx_principal (es_principal)
);

-- =====================================================
-- TABLA DE VARIANTES DE PRODUCTOS
-- =====================================================
CREATE TABLE productos_variantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    producto_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL, -- Ej: "Talla S - Rojo"
    sku VARCHAR(100) UNIQUE,
    precio DECIMAL(10,2),
    stock INT DEFAULT 0,
    atributos JSON, -- {"talla": "S", "color": "rojo"}
    imagen VARCHAR(255),
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    INDEX idx_producto (producto_id),
    INDEX idx_activa (activa)
);

-- =====================================================
-- TABLA DE CLIENTES
-- =====================================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    documento VARCHAR(20),
    fecha_nacimiento DATE,
    genero ENUM('masculino', 'femenino', 'otro'),
    activo TINYINT(1) DEFAULT 1,
    acepta_marketing TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_activo (activo)
);

-- =====================================================
-- TABLA DE DIRECCIONES DE CLIENTES
-- =====================================================
CREATE TABLE clientes_direcciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    nombre VARCHAR(100), -- "Casa", "Oficina", etc.
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(10),
    pais VARCHAR(50) DEFAULT 'Colombia',
    telefono VARCHAR(20),
    es_principal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id)
);

-- =====================================================
-- TABLA DE PEDIDOS
-- =====================================================
CREATE TABLE pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_pedido VARCHAR(50) UNIQUE NOT NULL,
    cliente_id INT,
    cliente_nombre VARCHAR(200), -- Para pedidos sin registro
    cliente_email VARCHAR(150),
    cliente_telefono VARCHAR(20),
    
    -- Dirección de envío
    direccion_envio TEXT NOT NULL,
    ciudad_envio VARCHAR(100) NOT NULL,
    departamento_envio VARCHAR(100) NOT NULL,
    codigo_postal_envio VARCHAR(10),
    
    -- Totales
    subtotal DECIMAL(10,2) NOT NULL,
    descuento DECIMAL(10,2) DEFAULT 0,
    envio DECIMAL(10,2) DEFAULT 0,
    impuestos DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    
    -- Estado y seguimiento
    estado ENUM('pendiente', 'confirmado', 'procesando', 'enviado', 'entregado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50),
    estado_pago ENUM('pendiente', 'pagado', 'fallido', 'reembolsado') DEFAULT 'pendiente',
    
    -- Notas y seguimiento
    notas_cliente TEXT,
    notas_admin TEXT,
    codigo_seguimiento VARCHAR(100),
    
    fecha_envio TIMESTAMP NULL,
    fecha_entrega TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    INDEX idx_numero_pedido (numero_pedido),
    INDEX idx_cliente (cliente_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (created_at)
);

-- =====================================================
-- TABLA DE ITEMS DE PEDIDOS
-- =====================================================
CREATE TABLE pedidos_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT,
    variante_id INT,
    nombre_producto VARCHAR(255) NOT NULL,
    sku VARCHAR(100),
    precio_unitario DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    atributos JSON, -- Talla, color, etc. al momento de la compra
    
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL,
    FOREIGN KEY (variante_id) REFERENCES productos_variantes(id) ON DELETE SET NULL,
    INDEX idx_pedido (pedido_id),
    INDEX idx_producto (producto_id)
);

-- =====================================================
-- TABLA DE CUPONES DE DESCUENTO
-- =====================================================
CREATE TABLE cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    tipo ENUM('porcentaje', 'fijo') NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    minimo_compra DECIMAL(10,2) DEFAULT 0,
    maximo_descuento DECIMAL(10,2),
    usos_maximos INT,
    usos_actuales INT DEFAULT 0,
    fecha_inicio TIMESTAMP NOT NULL,
    fecha_fin TIMESTAMP NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_codigo (codigo),
    INDEX idx_activo (activo),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
);

-- =====================================================
-- TABLA DE CONFIGURACIÓN DEL SITIO
-- =====================================================
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    tipo ENUM('text', 'number', 'boolean', 'json') DEFAULT 'text',
    descripcion VARCHAR(255),
    categoria VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_clave (clave),
    INDEX idx_categoria (categoria)
);

-- =====================================================
-- TABLA DE LOGS DE ACTIVIDAD
-- =====================================================
CREATE TABLE logs_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    tabla VARCHAR(50),
    registro_id INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios_admin(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at)
);

-- =====================================================
-- TABLA DE ESTADÍSTICAS
-- =====================================================
CREATE TABLE estadisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- 'ventas', 'visitas', 'productos'
    valor DECIMAL(15,2) NOT NULL,
    datos_adicionales JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_fecha_tipo (fecha, tipo),
    INDEX idx_fecha (fecha),
    INDEX idx_tipo (tipo)
);

-- =====================================================
-- INSERTAR DATOS INICIALES
-- =====================================================

-- Usuario administrador por defecto
INSERT INTO usuarios_admin (nombre, email, password, rol) VALUES 
('Administrador', 'admin@musayarion.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');
-- Contraseña: password (cambiarla después)

-- Categorías iniciales
INSERT INTO categorias (nombre, slug, descripcion, activa, orden) VALUES 
('Vestidos', 'vestidos', 'Vestidos elegantes y casuales para toda ocasión', 1, 1),
('Tops & Blusas', 'tops-blusas', 'Blusas, tops y camisetas modernas', 1, 2),
('Pantalones', 'pantalones', 'Pantalones y jeans de moda', 1, 3),
('Chaquetas', 'chaquetas', 'Chaquetas y abrigos de temporada', 1, 4),
('Accesorios', 'accesorios', 'Complementos y accesorios de moda', 1, 5),
('Zapatos', 'zapatos', 'Calzado femenino de tendencia', 1, 6);

-- Configuración inicial del sitio
INSERT INTO configuracion (clave, valor, tipo, descripcion, categoria) VALUES 
('sitio_nombre', 'Musa & Arion Moda Actual', 'text', 'Nombre del sitio web', 'general'),
('sitio_descripcion', 'Moda contemporánea que fusiona diferentes estilos', 'text', 'Descripción del sitio', 'general'),
('moneda', 'COP', 'text', 'Moneda del sitio', 'tienda'),
('simbolo_moneda', '$', 'text', 'Símbolo de la moneda', 'tienda'),
('iva_porcentaje', '19', 'number', 'Porcentaje de IVA', 'tienda'),
('envio_gratis_minimo', '150000', 'number', 'Monto mínimo para envío gratis', 'envios'),
('costo_envio_estandar', '15000', 'number', 'Costo de envío estándar', 'envios'),
('productos_por_pagina', '12', 'number', 'Productos por página', 'tienda'),
('email_contacto', 'info@musayarion.com', 'text', 'Email de contacto', 'contacto'),
('telefono_contacto', '+57 323 2212 316', 'text', 'Teléfono de contacto', 'contacto'),
('direccion_tienda', 'CLL 3C #20A-39, Madrid, Cundinamarca', 'text', 'Dirección de la tienda física', 'contacto');

-- =====================================================
-- VISTAS ÚTILES PARA REPORTES
-- =====================================================

-- Vista de productos con información completa
CREATE VIEW v_productos_completos AS
SELECT 
    p.id,
    p.nombre,
    p.slug,
    p.precio,
    p.precio_oferta,
    p.stock,
    p.destacado,
    p.nuevo,
    p.oferta,
    p.activo,
    c.nombre as categoria_nombre,
    c.slug as categoria_slug,
    (SELECT imagen FROM productos_imagenes pi WHERE pi.producto_id = p.id AND pi.es_principal = 1 LIMIT 1) as imagen_principal,
    p.visualizaciones,
    p.ventas_totales,
    p.created_at
FROM productos p
LEFT JOIN categorias c ON p.categoria_id = c.id;

-- Vista de pedidos con información del cliente
CREATE VIEW v_pedidos_completos AS
SELECT 
    p.id,
    p.numero_pedido,
    p.cliente_nombre,
    p.cliente_email,
    p.total,
    p.estado,
    p.estado_pago,
    p.metodo_pago,
    p.created_at,
    COUNT(pi.id) as total_items
FROM pedidos p
LEFT JOIN pedidos_items pi ON p.id = pi.pedido_id
GROUP BY p.id;

-- =====================================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- =====================================================

DELIMITER $$

-- Procedimiento para generar número de pedido único
CREATE PROCEDURE GenerarNumeroPedido(OUT nuevo_numero VARCHAR(50))
BEGIN
    DECLARE contador INT;
    DECLARE fecha_actual DATE;
    
    SET fecha_actual = CURDATE();
    
    SELECT COUNT(*) + 1 INTO contador 
    FROM pedidos 
    WHERE DATE(created_at) = fecha_actual;
    
    SET nuevo_numero = CONCAT('MA', DATE_FORMAT(fecha_actual, '%Y%m%d'), LPAD(contador, 4, '0'));
END$$

-- Procedimiento para actualizar stock después de una venta
CREATE PROCEDURE ActualizarStockVenta(IN pedido_id INT)
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE prod_id INT;
    DECLARE var_id INT;
    DECLARE cantidad INT;
    
    DECLARE cur CURSOR FOR 
        SELECT producto_id, variante_id, cantidad 
        FROM pedidos_items 
        WHERE pedidos_items.pedido_id = pedido_id;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN cur;
    
    read_loop: LOOP
        FETCH cur INTO prod_id, var_id, cantidad;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Actualizar stock del producto
        IF prod_id IS NOT NULL THEN
            UPDATE productos 
            SET stock = stock - cantidad,
                ventas_totales = ventas_totales + cantidad
            WHERE id = prod_id;
        END IF;
        
        -- Actualizar stock de la variante si existe
        IF var_id IS NOT NULL THEN
            UPDATE productos_variantes 
            SET stock = stock - cantidad
            WHERE id = var_id;
        END IF;
        
    END LOOP;
    
    CLOSE cur;
END$$

DELIMITER ;

-- =====================================================
-- TRIGGERS PARA AUDITORÍA
-- =====================================================

-- Trigger para auditar cambios en productos
DELIMITER $$
CREATE TRIGGER productos_audit_update
AFTER UPDATE ON productos
FOR EACH ROW
BEGIN
    INSERT INTO logs_actividad (accion, tabla, registro_id, datos_anteriores, datos_nuevos)
    VALUES (
        'UPDATE',
        'productos',
        NEW.id,
        JSON_OBJECT('nombre', OLD.nombre, 'precio', OLD.precio, 'stock', OLD.stock),
        JSON_OBJECT('nombre', NEW.nombre, 'precio', NEW.precio, 'stock', NEW.stock)
    );
END$$
DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONALES PARA OPTIMIZACIÓN
-- =====================================================

-- Índices compuestos para consultas frecuentes
CREATE INDEX idx_productos_categoria_activo ON productos(categoria_id, activo);
CREATE INDEX idx_productos_destacado_activo ON productos(destacado, activo);
CREATE INDEX idx_pedidos_estado_fecha ON pedidos(estado, created_at);

-- =====================================================
-- CONSULTAS DE VERIFICACIÓN
-- =====================================================

-- Verificar que todas las tablas se crearon correctamente
SELECT 
    TABLE_NAME as 'Tabla',
    TABLE_ROWS as 'Filas',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) as 'Tamaño_MB'
FROM information_schema.tables 
WHERE table_schema = 'musa_arion_store'
ORDER BY TABLE_NAME;

-- =====================================================
-- SCRIPT COMPLETADO
-- =====================================================
-- Este script crea una base de datos completa para ecommerce
-- Incluye todas las tablas necesarias para:
-- - Gestión de productos y categorías
-- - Sistema de pedidos y clientes
-- - Panel administrativo
-- - Cupones y descuentos
-- - Auditoría y logs
-- - Estadísticas y reportes
-- =====================================================
