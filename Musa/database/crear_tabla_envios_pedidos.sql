-- TABLA DE ENVÍOS Y PEDIDOS
-- Solo se insertan datos cuando el pago es exitoso

-- Tabla para almacenar información de envíos
CREATE TABLE IF NOT EXISTS envios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id VARCHAR(50) UNIQUE NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    codigo_postal VARCHAR(10),
    notas_adicionales TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado_envio ENUM('pendiente', 'en_proceso', 'enviado', 'entregado') DEFAULT 'pendiente',
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_fecha_creacion (fecha_creacion),
    INDEX idx_estado_envio (estado_envio)
);

-- Tabla para almacenar los pedidos completos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id VARCHAR(50) UNIQUE NOT NULL,
    productos JSON NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    envio DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    estado_pago ENUM('aprobado', 'rechazado', 'pendiente') DEFAULT 'aprobado',
    metodo_pago VARCHAR(50) DEFAULT 'tarjeta_credito',
    datos_pago JSON,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pedido_id (pedido_id),
    INDEX idx_fecha_creacion (fecha_creacion),
    INDEX idx_estado_pago (estado_pago),
    FOREIGN KEY (pedido_id) REFERENCES envios(pedido_id) ON DELETE CASCADE
);

-- Tabla para el tracking de estados
CREATE TABLE IF NOT EXISTS pedido_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id VARCHAR(50) NOT NULL,
    estado_anterior VARCHAR(50),
    estado_nuevo VARCHAR(50) NOT NULL,
    comentario TEXT,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pedido_id (pedido_id),
    FOREIGN KEY (pedido_id) REFERENCES envios(pedido_id) ON DELETE CASCADE
);

-- Insertar registro inicial de tracking
DELIMITER //
CREATE TRIGGER after_pedido_insert 
    AFTER INSERT ON pedidos 
    FOR EACH ROW 
BEGIN
    INSERT INTO pedido_tracking (pedido_id, estado_nuevo, comentario) 
    VALUES (NEW.pedido_id, 'pedido_creado', 'Pedido creado exitosamente');
END//
DELIMITER ;

-- Vista combinada para consultas fáciles
CREATE VIEW vista_pedidos_completos AS
SELECT 
    p.id as pedido_id_interno,
    p.pedido_id,
    e.nombre_completo,
    e.email,
    e.telefono,
    e.departamento,
    e.ciudad,
    e.direccion,
    e.codigo_postal,
    e.notas_adicionales,
    p.productos,
    p.subtotal,
    p.envio,
    p.total,
    p.estado_pago,
    e.estado_envio,
    p.metodo_pago,
    p.datos_pago,
    p.fecha_creacion,
    p.fecha_actualizacion
FROM pedidos p
INNER JOIN envios e ON p.pedido_id = e.pedido_id
ORDER BY p.fecha_creacion DESC;