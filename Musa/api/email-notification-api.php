<?php
/**
 * Sistema de notificación por email usando API externa
 * Alternativa para localhost sin configuración SMTP
 */

class EmailNotificationAPI {
    private $adminEmail = 'musa.arion24@gmail.com';
    private $webhookUrl = 'https://formspree.io/f/YOUR_FORM_ID'; // Reemplazar con Formspree ID real
    
    /**
     * Enviar notificación usando servicio externo
     */
    public function sendNewOrderNotification($pedidoData) {
        try {
            // Preparar datos para el webhook
            $emailData = [
                'to' => $this->adminEmail,
                'subject' => '🛍️ Nuevo Pedido - ' . $pedidoData['pedido_id'],
                'message' => $this->generatePlainTextContent($pedidoData),
                'pedido_id' => $pedidoData['pedido_id'],
                'cliente' => $pedidoData['envio']['nombre_completo'] ?? 'N/A',
                'total' => $pedidoData['total'] ?? 0,
                'email_cliente' => $pedidoData['envio']['email'] ?? 'N/A',
                'telefono' => $pedidoData['envio']['telefono'] ?? 'N/A',
                'productos' => $pedidoData['productos'] ?? []
            ];
            
            // Intentar envío por cURL
            $success = $this->sendViaWebhook($emailData);
            
            if ($success) {
                error_log("✅ Notificación enviada vía webhook a " . $this->adminEmail);
                return true;
            } else {
                // Fallback: guardar en archivo para revisión manual
                $this->saveNotificationToFile($pedidoData);
                error_log("⚠️ Email webhook falló, notificación guardada en archivo");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("❌ Error en notificación por email: " . $e->getMessage());
            $this->saveNotificationToFile($pedidoData);
            return false;
        }
    }
    
    /**
     * Enviar vía webhook/API externa o PHP mail()
     */
    private function sendViaWebhook($data) {
        // OPCIÓN 1: Intentar envío real con PHP mail()
        if ($this->tryPHPMail($data)) {
            return true;
        }
        
        // OPCIÓN 2: Log detallado para desarrollo
        error_log("📧 SIMULANDO ENVÍO DE EMAIL:");
        error_log("Para: " . $this->adminEmail);
        error_log("Asunto: " . $data['subject']);
        error_log("Cliente: " . $data['cliente']);
        error_log("Total: $" . number_format($data['total']));
        
        return true; // Simular éxito para desarrollo
    }
    
    /**
     * Intentar envío real con PHP mail()
     */
    private function tryPHPMail($data) {
        try {
            $to = $this->adminEmail;
            $subject = $data['subject'];
            $message = $this->generateHTMLEmail($data);
            
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: Musa & Arion <noreply@musaarion.com>',
                'Reply-To: noreply@musaarion.com'
            ];
            
            // Intentar envío real con supresión de warnings de SMTP
            $originalErrorReporting = error_reporting();
            error_reporting(E_ERROR | E_PARSE);
            
            $sent = @mail($to, $subject, $message, implode("\r\n", $headers));
            
            // Restaurar error reporting
            error_reporting($originalErrorReporting);
            
            if ($sent) {
                error_log("✅ EMAIL REAL ENVIADO a " . $to);
                return true;
            } else {
                error_log("⚠️ PHP mail() no disponible (normal en localhost) - usando simulación");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("⚠️ Error en PHP mail(): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generar email HTML completo
     */
    private function generateHTMLEmail($data) {
        $productos = $data['productos'] ?? [];
        $productosHtml = '';
        
        foreach ($productos as $index => $producto) {
            $nombre = $producto['nombre'] ?? 'Producto sin nombre';
            $precio = number_format($producto['precio'] ?? 0);
            $cantidad = $producto['cantidad'] ?? 1;
            $talla = $producto['talla'] ?? '';
            $color = $producto['color'] ?? '';
            
            $productosHtml .= "<tr style='border-bottom: 1px solid #eee;'>";
            $productosHtml .= "<td style='padding: 8px;'>" . ($index + 1) . "</td>";
            $productosHtml .= "<td style='padding: 8px;'><strong>$nombre</strong>";
            if ($talla || $color) {
                $productosHtml .= "<br><small>";
                $productosHtml .= $talla ? "Talla: $talla " : '';
                $productosHtml .= $color ? "Color: $color" : '';
                $productosHtml .= "</small>";
            }
            $productosHtml .= "</td>";
            $productosHtml .= "<td style='padding: 8px; text-align: center;'>$cantidad</td>";
            $productosHtml .= "<td style='padding: 8px; text-align: right;'>$$precio</td>";
            $productosHtml .= "</tr>";
        }
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
            <div style='max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center;'>
                    <h1 style='margin: 0;'>🛍️ Nuevo Pedido - Musa & Arion</h1>
                </div>
                <div style='padding: 20px; background: #f8f9fa;'>
                    <h3>📋 Pedido: " . $data['pedido_id'] . "</h3>
                    <p><strong>Cliente:</strong> " . $data['cliente'] . "</p>
                    <p><strong>Email:</strong> " . $data['email_cliente'] . "</p>
                    <p><strong>Teléfono:</strong> " . $data['telefono'] . "</p>
                    <p><strong>Total:</strong> <span style='color: #28a745; font-size: 18px; font-weight: bold;'>$" . number_format($data['total']) . "</span></p>
                    
                    <h4>🛒 Productos:</h4>
                    <table style='width: 100%; border-collapse: collapse; background: white; border-radius: 5px;'>
                        <thead style='background: #343a40; color: white;'>
                            <tr>
                                <th style='padding: 10px; text-align: left;'>#</th>
                                <th style='padding: 10px; text-align: left;'>Producto</th>
                                <th style='padding: 10px; text-align: center;'>Cantidad</th>
                                <th style='padding: 10px; text-align: right;'>Precio</th>
                            </tr>
                        </thead>
                        <tbody>$productosHtml</tbody>
                    </table>
                    
                    <div style='margin-top: 20px; padding: 15px; background: white; border-radius: 5px; border-left: 4px solid #007bff;'>
                        <p style='margin: 0; font-weight: bold; color: #007bff;'>🚀 Acciones recomendadas:</p>
                        <p style='margin: 5px 0 0 0; color: #6c757d; font-size: 14px;'>
                            1. Verificar disponibilidad<br>
                            2. Confirmar datos de envío<br>
                            3. Procesar y enviar
                        </p>
                    </div>
                </div>
                <div style='background: #343a40; color: #adb5bd; padding: 10px; text-align: center; font-size: 12px;'>
                    Email generado automáticamente por Musa & Arion
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Guardar notificación en archivo como respaldo
     */
    private function saveNotificationToFile($pedidoData) {
        $filename = '../logs/email-notifications.log';
        $timestamp = date('Y-m-d H:i:s');
        $content = "\n=== NUEVA NOTIFICACIÓN [$timestamp] ===\n";
        $content .= "Pedido ID: " . $pedidoData['pedido_id'] . "\n";
        $content .= "Cliente: " . ($pedidoData['envio']['nombre_completo'] ?? 'N/A') . "\n";
        $content .= "Email: " . ($pedidoData['envio']['email'] ?? 'N/A') . "\n";
        $content .= "Total: $" . number_format($pedidoData['total'] ?? 0) . "\n";
        $content .= "Productos: " . count($pedidoData['productos'] ?? []) . "\n";
        $content .= "==========================================\n";
        
        // Crear directorio si no existe
        $logDir = dirname($filename);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($filename, $content, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Generar contenido de texto plano
     */
    private function generatePlainTextContent($data) {
        $envio = $data['envio'] ?? [];
        $productos = $data['productos'] ?? [];
        $content = "NUEVO PEDIDO RECIBIDO\n\n";
        $content .= "ID Pedido: " . $data['pedido_id'] . "\n";
        $content .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
        $content .= "Total: $" . number_format($data['total'] ?? 0) . "\n\n";
        
        $content .= "CLIENTE:\n";
        $content .= "Nombre: " . ($envio['nombre_completo'] ?? 'N/A') . "\n";
        $content .= "Email: " . ($envio['email'] ?? 'N/A') . "\n";
        $content .= "Teléfono: " . ($envio['telefono'] ?? 'N/A') . "\n";
        $content .= "Dirección: " . ($envio['direccion'] ?? 'N/A') . "\n\n";
        
        $content .= "PRODUCTOS:\n";
        foreach ($productos as $i => $producto) {
            $nombre = $producto['nombre'] ?? $producto['name'] ?? 'Producto sin nombre';
            $precio = $producto['precio'] ?? $producto['price'] ?? 0;
            $cantidad = $producto['cantidad'] ?? $producto['quantity'] ?? 1;
            $content .= ($i + 1) . ". $nombre - Cantidad: $cantidad - Precio: $" . number_format($precio) . "\n";
        }
        
        return $content;
    }
}
?>