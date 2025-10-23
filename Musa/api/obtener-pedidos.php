<?php
/**
 * API PARA OBTENER PEDIDOS EN TIEMPO REAL
 * Para la sección de administración
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Función para enviar respuesta JSON
function sendJsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Detectar entorno y configurar base de datos
$hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocal = ($hostname === 'localhost' || $hostname === '127.0.0.1');


// Configuración fija para hosting
$host = 'localhost';
$dbname = 'janithal_musa_moda';
$username = 'root';
$password = '';

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Obtener parámetros de consulta
    $limite = $_GET['limit'] ?? $_GET['limite'] ?? 50;
    $estado = $_GET['status'] ?? $_GET['estado'] ?? null;
    $fecha = $_GET['date'] ?? $_GET['fecha'] ?? null;
    $busqueda = $_GET['search'] ?? $_GET['busqueda'] ?? null;

    // Usar la vista que ya funciona
    $sql = "SELECT 
                pedido_id_interno as id,
                pedido_id,
                productos,
                subtotal,
                envio as costo_envio,
                total,
                estado_pago as estado,
                metodo_pago,
                datos_pago,
                fecha_creacion as fecha_pedido,
                nombre_completo,
                email,
                telefono,
                departamento,
                ciudad,
                direccion,
                codigo_postal,
                notas_adicionales,
                estado_envio,
                NULL as nequi_comprobante,
                NULL as nequi_remitente,
                NULL as nequi_estado,
                NULL as daviplata_comprobante,
                NULL as daviplata_remitente,
                NULL as daviplata_estado
            FROM vista_pedidos_completos
            WHERE 1=1";
    $params = [];

    // Filtros opcionales mejorados
    if ($estado) {
        // Verificar si es un filtro de tipo específico (payment: o shipping:)
        if (strpos($estado, ':') !== false) {
            $parts = explode(':', $estado, 2);
            $filterType = $parts[0]; // 'payment' o 'shipping'
            $filterValue = $parts[1]; // el valor del estado
            
            if ($filterType === 'payment') {
                $sql .= " AND estado_pago = ?";
                $params[] = $filterValue;
            } elseif ($filterType === 'shipping') {
                $sql .= " AND estado_envio = ?";
                $params[] = $filterValue;
            }
        } else {
            // Filtro de estado de pago por defecto (compatibilidad hacia atrás)
            $sql .= " AND estado_pago = ?";
            $params[] = $estado;
        }
    }

    if ($fecha) {
        $sql .= " AND DATE(fecha_creacion) = ?";
        $params[] = $fecha;
    }

    if ($busqueda) {
        $sql .= " AND (nombre_completo LIKE ? OR email LIKE ? OR pedido_id_interno = ?)";
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
        $params[] = $busqueda;
    }

    // Ordenar por fecha más reciente
    $sql .= " ORDER BY fecha_creacion DESC";

    // Limitar resultados (sin usar parámetros en LIMIT)
    $limite = (int)$limite; // Convertir a entero para seguridad
    if ($limite > 0) {
        $sql .= " LIMIT $limite";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll();

    // Decodificar JSON para productos y datos_pago
    foreach ($pedidos as &$pedido) {
        $pedido['productos'] = json_decode($pedido['productos'], true);
        $pedido['datos_pago'] = json_decode($pedido['datos_pago'], true);
        
        // Formatear fechas - usar la fecha que está disponible
        $fechaPedido = $pedido['fecha_pedido'] ?? $pedido['fecha_creacion'] ?? date('Y-m-d H:i:s');
        $pedido['fecha_creacion_formateada'] = date('d/m/Y H:i', strtotime($fechaPedido));
        $pedido['fecha_actualizacion_formateada'] = date('d/m/Y H:i', strtotime($fechaPedido));
        
        // Contar productos
        $pedido['cantidad_productos'] = count($pedido['productos']);
        
        // Consolidar información de comprobantes de Nequi/Daviplata desde datos_pago
        $pedido['has_receipt'] = false;
        $pedido['receipt_info'] = null;
        
        if ($pedido['datos_pago'] && isset($pedido['datos_pago']['receipt_image'])) {
            $pedido['has_receipt'] = true;
            $pedido['receipt_info'] = [
                'comprobante_imagen' => $pedido['datos_pago']['receipt_image'],
                'nombre_remitente' => $pedido['datos_pago']['sender_name'] ?? 'No especificado',
                'estado' => $pedido['estado'] ?? 'pendiente',
                'tipo' => $pedido['metodo_pago'] === 'nequi_daviplata' ? 'nequi_daviplata' : 'otros'
            ];
        }
        
        // Limpiar campos temporales
        unset($pedido['nequi_comprobante'], $pedido['nequi_remitente'], $pedido['nequi_estado']);
        unset($pedido['daviplata_comprobante'], $pedido['daviplata_remitente'], $pedido['daviplata_estado']);
        
        // Formatear montos - usar los campos correctos
        $pedido['subtotal_formateado'] = '$' . number_format($pedido['subtotal'], 0, ',', '.');
        $costoEnvio = $pedido['costo_envio'] ?? ($pedido['envio'] ?? 0);
        $pedido['envio_formateado'] = '$' . number_format($costoEnvio, 0, ',', '.');
        $pedido['total_formateado'] = '$' . number_format($pedido['total'], 0, ',', '.');
    }

    // Obtener estadísticas generales usando tabla pedidos directamente
    $stats_sql = "
        SELECT 
            COUNT(*) as total_pedidos,
            SUM(CASE WHEN estado_pago = 'aprobado' THEN 1 ELSE 0 END) as pedidos_aprobados,
            SUM(CASE WHEN estado_pago = 'pendiente' THEN 1 ELSE 0 END) as pedidos_pendientes,
            SUM(CASE WHEN estado_pago = 'rechazado' THEN 1 ELSE 0 END) as pedidos_rechazados,
            SUM(CASE WHEN estado_envio = 'pendiente' THEN 1 ELSE 0 END) as envios_pendientes,
            SUM(CASE WHEN estado_envio = 'en_proceso' THEN 1 ELSE 0 END) as envios_proceso,
            SUM(CASE WHEN estado_envio = 'enviado' THEN 1 ELSE 0 END) as envios_enviados,
            SUM(CASE WHEN estado_envio = 'entregado' THEN 1 ELSE 0 END) as envios_entregados,
            SUM(CASE WHEN estado_pago = 'aprobado' THEN total ELSE 0 END) as total_ventas,
            AVG(CASE WHEN estado_pago = 'aprobado' THEN total ELSE NULL END) as promedio_venta
        FROM vista_pedidos_completos
    ";

    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_stmt->execute();
    $estadisticas = $stats_stmt->fetch();

    // Formatear estadísticas
    $estadisticas['total_ventas_formateado'] = '$' . number_format($estadisticas['total_ventas'], 0, ',', '.');
    $estadisticas['promedio_venta_formateado'] = '$' . number_format($estadisticas['promedio_venta'], 0, ',', '.');

    // Respuesta exitosa
    sendJsonResponse([
        'success' => true,
        'pedidos' => $pedidos,
        'estadisticas' => $estadisticas,
        'total_resultados' => count($pedidos),
        'filtros_aplicados' => [
            'estado' => $estado,
            'fecha' => $fecha,
            'busqueda' => $busqueda,
            'limite' => $limite
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en obtener-pedidos.php: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Error al obtener pedidos',
        'error' => $e->getMessage()
    ], 500);
}
?>