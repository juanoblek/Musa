<?php
/**
 * Sistema de notificación por email para nuevos pedidos
 * Envía correo a musa.arion24@gmail.com cuando hay un pedido nuevo
 */

require_once '../config/database.php';

class EmailNotification {
    private $adminEmail = 'musa.arion24@gmail.com';
    private $fromEmail = 'noreply@musaarion.com';
    private $fromName = 'Musa & Arion - Sistema de Pedidos';
    
    /**
     * Enviar notificación de nuevo pedido
     */
    public function sendNewOrderNotification($pedidoData) {
        try {
            // Preparar el contenido del email
            $subject = "🛍️ Nuevo Pedido - " . $pedidoData['pedido_id'];
            $emailContent = $this->generateEmailContent($pedidoData);
            
            // Headers del email
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'X-Mailer: PHP/' . phpversion()
            ];
            
            // Enviar el email
            $success = mail(
                $this->adminEmail,
                $subject,
                $emailContent,
                implode("\r\n", $headers)
            );
            
            if ($success) {
                error_log("✅ Email enviado exitosamente a " . $this->adminEmail);
                return true;
            } else {
                error_log("❌ Error enviando email a " . $this->adminEmail);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ Error en sistema de email: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar contenido HTML del email
     */
    private function generateEmailContent($data) {
        $envio = $data['envio'] ?? [];
        $productos = $data['productos'] ?? [];
        $pedidoId = $data['pedido_id'] ?? 'N/A';
        $total = number_format($data['total'] ?? 0);
        $fecha = date('d/m/Y H:i:s');
        
        // Generar lista de productos
        $productosHtml = '';
        foreach ($productos as $index => $producto) {
            $nombre = $producto['nombre'] ?? $producto['name'] ?? 'Producto sin nombre';
            $precio = number_format($producto['precio'] ?? $producto['price'] ?? 0);
            $cantidad = $producto['cantidad'] ?? $producto['quantity'] ?? 1;
            $talla = $producto['talla'] ?? $producto['size'] ?? '';
            $color = $producto['color'] ?? '';
            
            $productosHtml .= "
            <tr style='border-bottom: 1px solid #eee;'>
                <td style='padding: 10px; border-right: 1px solid #eee;'>" . ($index + 1) . "</td>
                <td style='padding: 10px; border-right: 1px solid #eee;'>
                    <strong>$nombre</strong><br>
                    <small style='color: #666;'>
                        " . ($talla ? "Talla: $talla" : '') . "
                        " . ($color ? " | Color: $color" : '') . "
                    </small>
                </td>
                <td style='padding: 10px; border-right: 1px solid #eee; text-align: center;'>$cantidad</td>
                <td style='padding: 10px; text-align: right;'>$$precio</td>
            </tr>";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Nuevo Pedido - Musa & Arion</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center;'>
                <h1 style='margin: 0; font-size: 24px;'>🛍️ Nuevo Pedido Recibido</h1>
                <p style='margin: 10px 0 0 0; opacity: 0.9;'>Musa & Arion</p>
            </div>
            
            <div style='background: #f8f9fa; padding: 20px; border: 1px solid #dee2e6;'>
                
                <!-- Información del Pedido -->
                <div style='margin-bottom: 25px;'>
                    <h3 style='color: #495057; border-bottom: 2px solid #007bff; padding-bottom: 5px;'>📋 Información del Pedido</h3>
                    <p><strong>ID Pedido:</strong> $pedidoId</p>
                    <p><strong>Fecha:</strong> $fecha</p>
                    <p><strong>Total:</strong> <span style='color: #28a745; font-size: 18px; font-weight: bold;'>$$total</span></p>
                    <p><strong>Estado:</strong> <span style='color: #28a745;'>✅ Pago Aprobado</span></p>
                </div>
                
                <!-- Información del Cliente -->
                <div style='margin-bottom: 25px;'>
                    <h3 style='color: #495057; border-bottom: 2px solid #28a745; padding-bottom: 5px;'>👤 Datos del Cliente</h3>
                    <p><strong>Nombre:</strong> " . ($envio['nombre_completo'] ?? 'N/A') . "</p>
                    <p><strong>Email:</strong> " . ($envio['email'] ?? 'N/A') . "</p>
                    <p><strong>Teléfono:</strong> " . ($envio['telefono'] ?? 'N/A') . "</p>
                    <p><strong>Dirección:</strong> " . ($envio['direccion'] ?? 'N/A') . "</p>
                    <p><strong>Ciudad:</strong> " . ($envio['ciudad'] ?? 'N/A') . ", " . ($envio['departamento'] ?? 'N/A') . "</p>
                </div>
                
                <!-- Productos -->
                <div style='margin-bottom: 25px;'>
                    <h3 style='color: #495057; border-bottom: 2px solid #ffc107; padding-bottom: 5px;'>🛒 Productos</h3>
                    <table style='width: 100%; border-collapse: collapse; background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);'>
                        <thead>
                            <tr style='background: #343a40; color: white;'>
                                <th style='padding: 12px; text-align: left;'>#</th>
                                <th style='padding: 12px; text-align: left;'>Producto</th>
                                <th style='padding: 12px; text-align: center;'>Cantidad</th>
                                <th style='padding: 12px; text-align: right;'>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            $productosHtml
                        </tbody>
                    </table>
                </div>
                
                <!-- Acciones -->
                <div style='text-align: center; margin-top: 30px; padding: 20px; background: white; border-radius: 5px; border: 2px dashed #007bff;'>
                    <p style='margin: 0; color: #495057; font-weight: bold;'>🚀 Acciones Recomendadas:</p>
                    <p style='margin: 5px 0; color: #6c757d;'>
                        1. Verificar disponibilidad de productos<br>
                        2. Confirmar datos de envío<br>
                        3. Procesar envío<br>
                        4. Notificar al cliente
                    </p>
                </div>
                
            </div>
            
            <div style='background: #343a40; color: #adb5bd; padding: 15px; border-radius: 0 0 10px 10px; text-align: center; font-size: 12px;'>
                <p style='margin: 0;'>Este email fue generado automáticamente por el sistema de Musa & Arion</p>
                <p style='margin: 5px 0 0 0;'>📧 Sistema de Notificaciones | 🕒 $fecha</p>
            </div>
            
        </body>
        </html>";
    }
}
?>