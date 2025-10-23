<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Configuración de base de datos
    $host = 'localhost';
    $dbname = 'janithal_musa_moda';
    $username = 'root';
    $password = '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener todos los pedidos con información completa
    $sql = "SELECT 
                pedido_id_interno as id,
                pedido_id,
                productos,
                subtotal,
                envio as costo_envio,
                total,
                estado_pago,
                metodo_pago,
                datos_pago,
                fecha_creacion,
                nombre_completo,
                email,
                telefono,
                departamento,
                ciudad,
                direccion,
                codigo_postal,
                notas_adicionales,
                estado_envio
            FROM vista_pedidos_completos
            ORDER BY fecha_creacion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Preparar datos para Excel
    $excel_data = [];
    
    // Encabezados de las columnas
    $headers = [
        'ID Pedido',
        'Referencia',
        'Fecha',
        'Cliente',
        'Email',
        'Teléfono',
        'Departamento',
        'Ciudad',
        'Dirección',
        'Código Postal',
        'Productos',
        'Cantidad Items',
        'Subtotal',
        'Envío',
        'Total',
        'Estado Pago',
        'Estado Envío',
        'Método Pago',
        'Notas'
    ];
    
    $excel_data[] = $headers;
    
    // Procesar cada pedido
    foreach ($pedidos as $pedido) {
        $productos = json_decode($pedido['productos'], true);
        $datos_pago = json_decode($pedido['datos_pago'], true);
        
        // Formatear productos para mostrar en Excel
        $productos_texto = '';
        $cantidad_total = 0;
        
        if ($productos && is_array($productos)) {
            $productos_lista = [];
            foreach ($productos as $producto) {
                $cantidad = $producto['quantity'] ?? 1;
                $cantidad_total += $cantidad;
                
                $producto_info = $producto['name'] ?? 'Producto sin nombre';
                if (isset($producto['size']) && $producto['size']) {
                    $producto_info .= ' (Talla: ' . $producto['size'] . ')';
                }
                if (isset($producto['color']) && $producto['color']) {
                    $producto_info .= ' (Color: ' . $producto['color'] . ')';
                }
                $producto_info .= ' x' . $cantidad;
                
                $productos_lista[] = $producto_info;
            }
            $productos_texto = implode('; ', $productos_lista);
        }
        
        // Formatear fecha
        $fecha_formateada = date('d/m/Y H:i', strtotime($pedido['fecha_creacion']));
        
        // Agregar fila de datos
        $excel_data[] = [
            $pedido['id'],
            $pedido['pedido_id'],
            $fecha_formateada,
            $pedido['nombre_completo'] ?: 'No especificado',
            $pedido['email'] ?: 'No especificado',
            $pedido['telefono'] ?: 'No especificado',
            $pedido['departamento'] ?: 'No especificado',
            $pedido['ciudad'] ?: 'No especificado',
            $pedido['direccion'] ?: 'No especificado',
            $pedido['codigo_postal'] ?: 'No especificado',
            $productos_texto,
            $cantidad_total,
            '$' . number_format($pedido['subtotal'], 0, ',', '.'),
            '$' . number_format($pedido['costo_envio'], 0, ',', '.'),
            '$' . number_format($pedido['total'], 0, ',', '.'),
            ucfirst($pedido['estado_pago']),
            ucfirst(str_replace('_', ' ', $pedido['estado_envio'])),
            ucfirst(str_replace('_', '/', $pedido['metodo_pago'])),
            $pedido['notas_adicionales'] ?: 'Sin notas'
        ];
    }
    
    // Generar nombre del archivo con fecha actual
    $fecha_actual = date('Y-m-d_H-i-s');
    $filename = "pedidos_musa_moda_{$fecha_actual}.csv";
    
    // Configurar headers para descarga
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    // Crear archivo CSV (compatible con Excel)
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8 (para que Excel abra correctamente caracteres especiales)
    fwrite($output, "\xEF\xBB\xBF");
    
    // Escribir todas las filas
    foreach ($excel_data as $row) {
        fputcsv($output, $row, ';'); // Usar punto y coma como separador para Excel en español
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log("Error en export-orders.php: " . $e->getMessage());
    
    // Si hay error, devolver JSON de error
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Error al generar el archivo: ' . $e->getMessage()
    ]);
}
?>