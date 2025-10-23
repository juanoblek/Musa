-- Agregar columnas para pagos de Nequi/Daviplata a la tabla pedidos
ALTER TABLE pedidos 
ADD COLUMN sender_name VARCHAR(255) NULL COMMENT 'Nombre de quien envía la transferencia',
ADD COLUMN transfer_reference VARCHAR(100) NULL COMMENT 'Referencia de la transferencia',
ADD COLUMN receipt_image VARCHAR(255) NULL COMMENT 'Nombre del archivo del comprobante',
ADD COLUMN buyer_email VARCHAR(255) NULL COMMENT 'Email del comprador para Nequi/Daviplata';

-- Crear tabla para almacenar metadatos de los comprobantes
CREATE TABLE IF NOT EXISTS comprobantes_nequi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255),
    file_size INT,
    mime_type VARCHAR(100),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified BOOLEAN DEFAULT FALSE,
    verified_by INT NULL,
    verification_date TIMESTAMP NULL,
    notes TEXT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Crear índices para mejor rendimiento
CREATE INDEX idx_pedidos_metodo_pago ON pedidos(metodo_pago);
CREATE INDEX idx_pedidos_estado ON pedidos(estado);
CREATE INDEX idx_comprobantes_pedido ON comprobantes_nequi(pedido_id);
CREATE INDEX idx_comprobantes_verified ON comprobantes_nequi(verified);