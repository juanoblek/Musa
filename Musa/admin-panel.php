<?php
// admin-panel.php - Panel administrativo con validación de sesión
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Si no está autenticado, redirigir al login
    header('Location: login.php');
    exit();
}

// Si está autenticado, obtener el nombre de usuario
$admin_user = $_SESSION['admin_user'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - M & A MODA ACTUAL</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom Admin Styles -->
    <style>
        /* === ESTILOS DEL PANEL ADMINISTRATIVO === */
        :root {
            --admin-primary: #2c3e50;
            --admin-secondary: #34495e;
            --admin-accent: #3498db;
            --admin-success: #27ae60;
            --admin-warning: #f39c12;
            --admin-danger: #e74c3c;
            --admin-light: #ecf0f1;
            --admin-dark: #1a252f;
        }

        /* =================== ESTILOS MEJORADOS PARA MÚLTIPLES IMÁGENES =================== */
        .enhanced-upload .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
            position: relative;
            overflow: hidden;
        }
        
        .enhanced-upload .upload-area:hover {
            border-color: #007bff;
            background: rgba(0, 123, 255, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        }
        
        .enhanced-upload .upload-area.drag-over {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
            transform: scale(1.02);
        }
        
        .enhanced-upload .upload-content {
            position: relative;
            z-index: 2;
        }
        
        .image-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        /* Estilos para preview de medios (imágenes y videos) */
        .media-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .media-preview-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: move;
        }
        
        .media-preview-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .media-preview-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }
        
        .preview-video {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        
        .media-type-indicator {
            position: absolute;
            bottom: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .video-indicator {
            background: rgba(220, 53, 69, 0.9);
        }
        
        .image-indicator {
            background: rgba(40, 167, 69, 0.9);
        }
        
        /* Estilos para el carrusel en la vista del producto */
        .product-carousel {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .carousel-container {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        
        .carousel-slide.active {
            opacity: 1;
        }
        
        .carousel-slide img,
        .carousel-slide video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .carousel-controls {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .carousel-controls:hover {
            background: rgba(0, 0, 0, 0.8);
            transform: translateY(-50%) scale(1.1);
        }
        
        .carousel-prev {
            left: 10px;
        }
        
        .carousel-next {
            right: 10px;
        }
        
        .carousel-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }
        
        .carousel-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .carousel-indicator.active {
            background: white;
            transform: scale(1.2);
        }
        
        .preview-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: move;
        }
        
        .preview-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .preview-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
        }
        
        .preview-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-bottom: 1px solid #eee;
        }
        
        .preview-controls {
            position: absolute;
            top: 5px;
            right: 5px;
            display: flex;
            gap: 5px;
        }
        
        .preview-controls button {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: all 0.2s ease;
        }
        
        .remove-image {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }
        
        .remove-image:hover {
            background: #dc3545;
            transform: scale(1.1);
        }
        
        .move-image {
            background: rgba(108, 117, 125, 0.9);
            color: white;
            cursor: move;
        }
        
        .move-image:hover {
            background: #6c757d;
        }
        
        .preview-item .image-info {
            padding: 8px;
            font-size: 11px;
            color: #6c757d;
            background: #f8f9fa;
        }
        
        .image-order-indicator {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 123, 255, 0.9);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
        
        /* Badge de contador de imágenes */
        #additionalImagesCount {
            font-size: 0.75rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .image-preview-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 10px;
            }
            
            .preview-img {
                height: 100px;
            }
            
            .enhanced-upload .upload-area {
                padding: 1.5rem;
            }
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .admin-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            margin: 2rem auto;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .admin-sidebar {
            background: linear-gradient(180deg, var(--admin-primary) 0%, var(--admin-secondary) 100%);
            min-height: 100vh;
            padding: 0;
            position: relative;
            padding-bottom: 5rem; /* Espacio para el botón de logout */
        }

        .sidebar-header {
            background: var(--admin-dark);
            padding: 2rem;
            text-align: center;
            border-bottom: 3px solid var(--admin-accent);
        }

        .sidebar-header h3 {
            color: white;
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .sidebar-header p {
            color: var(--admin-light);
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            margin: 1rem;
            border-radius: 10px;
            text-align: center;
        }

        .user-info h5 {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .user-info small {
            color: var(--admin-light);
        }

        .logout-btn {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            right: 2rem;
        }

        .nav-pills .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0;
            padding: 1.2rem 2rem;
            margin: 0.2rem 0;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-pills .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--admin-accent);
        }

        .nav-pills .nav-link.active {
            background: var(--admin-accent);
            color: white;
            border-left-color: white;
            box-shadow: 0 0 20px rgba(52, 152, 219, 0.3);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
        }

        /* Main Content Styles */
        .admin-content {
            padding: 2rem;
            background: white;
            min-height: 100vh;
        }

        .content-header {
            border-bottom: 2px solid var(--admin-light);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .content-header h2 {
            color: var(--admin-primary);
            margin: 0;
            font-weight: 700;
        }

        .content-header p {
            color: #666;
            margin: 0.5rem 0 0 0;
        }

        /* Card Styles */
        .admin-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
        }

        .admin-card .card-header {
            background: var(--admin-primary);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .admin-card .card-body {
            padding: 2rem;
        }

        /* Button Styles */
        .btn-admin-primary {
            background: var(--admin-primary);
            border-color: var(--admin-primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-admin-primary:hover {
            background: var(--admin-secondary);
            border-color: var(--admin-secondary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-admin-accent {
            background: var(--admin-accent);
            border-color: var(--admin-accent);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-admin-accent:hover {
            background: #2980b9;
            border-color: #2980b9;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        /* Table Styles */
        .admin-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            min-width: 1200px !important; /* Ancho mínimo para mostrar todas las columnas */
            white-space: nowrap;
            margin-bottom: 0 !important;
        }

        .table-responsive {
            overflow-x: auto !important;
            overflow-y: visible !important;
            -webkit-overflow-scrolling: touch;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
        }

        .admin-table thead {
            background: var(--admin-primary);
            color: white;
        }
        
        /* =================== MEJORAS PARA TABLA DE PRODUCTOS RESPONSIVE =================== */
        
        /* Encabezados de tabla fijos y estilizados */
        .admin-table thead th {
            background: linear-gradient(135deg, var(--admin-primary), var(--admin-secondary)) !important;
            color: white !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border: none !important;
            padding: 12px 8px !important;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Celdas de tabla optimizadas */
        .admin-table tbody td {
            padding: 10px 8px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #dee2e6 !important;
            background: white;
        }
        
        /* Filas alternadas con hover effect */
        .admin-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .admin-table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05) !important;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }
        
        /* Columna de imagen optimizada */
        .admin-table td:first-child {
            width: 80px !important;
            min-width: 80px !important;
            max-width: 80px !important;
        }
        
        .admin-table td:first-child img {
            width: 60px !important;
            height: 60px !important;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Columna de producto (nombre + descripción) */
        .admin-table td:nth-child(2) {
            min-width: 200px !important;
            max-width: 250px !important;
        }
        
        .admin-table td:nth-child(2) strong {
            display: block;
            font-size: 0.9rem;
            color: var(--admin-primary);
            margin-bottom: 2px;
        }
        
        .admin-table td:nth-child(2) small {
            display: block;
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Columna de precio optimizada */
        .admin-table td:nth-child(3) {
            min-width: 120px !important;
            text-align: right;
        }
        
        /* Columnas de badges (categoría, género, etc.) */
        .admin-table td:nth-child(4),
        .admin-table td:nth-child(5) {
            min-width: 100px !important;
        }
        
        /* Columnas de colores y tallas */
        .admin-table td:nth-child(6),
        .admin-table td:nth-child(7) {
            min-width: 120px !important;
        }
        
        /* Columna de stock */
        .admin-table td:nth-child(8) {
            min-width: 80px !important;
            text-align: center;
        }
        
        /* Columna de estado */
        .admin-table td:nth-child(9) {
            min-width: 100px !important;
        }
        
        /* COLUMNA DE ACCIONES - LA MÁS IMPORTANTE */
        .admin-table td:last-child {
            min-width: 140px !important;
            width: 140px !important;
            position: sticky !important;
            right: 0 !important;
            background: white !important;
            box-shadow: -2px 0 4px rgba(0,0,0,0.1) !important;
            z-index: 5 !important;
        }
        
        .admin-table tbody tr:nth-child(even) td:last-child {
            background: #f8f9fa !important;
        }
        
        .admin-table tbody tr:hover td:last-child {
            background: rgba(0, 123, 255, 0.05) !important;
        }
        
        /* Grupo de botones de acción mejorado */
        .btn-group-sm {
            display: flex !important;
            gap: 2px !important;
            justify-content: center !important;
        }
        
        .btn-group-sm .btn {
            padding: 6px 8px !important;
            font-size: 0.75rem !important;
            border-radius: 4px !important;
            min-width: 32px !important;
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.2s ease !important;
        }
        
        .btn-group-sm .btn:hover {
            transform: translateY(-1px) !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
        }
        
        /* Colores específicos para botones de acción */
        .btn-outline-info:hover {
            background-color: #17a2b8 !important;
            border-color: #17a2b8 !important;
        }
        
        .btn-outline-primary:hover {
            background-color: #007bff !important;
            border-color: #007bff !important;
        }
        
        .btn-outline-danger:hover {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
        }
        
        /* =================== RESPONSIVE DESIGN PARA MÓVILES =================== */
        
        /* Tablets y pantallas medianas */
        @media (max-width: 992px) {
            .admin-table {
                min-width: 1000px !important;
                font-size: 0.85rem;
            }
            
            .admin-table thead th {
                padding: 10px 6px !important;
                font-size: 0.7rem;
            }
            
            .admin-table tbody td {
                padding: 8px 6px !important;
            }
            
            .btn-group-sm .btn {
                padding: 4px 6px !important;
                font-size: 0.7rem !important;
                min-width: 28px !important;
                height: 28px !important;
            }
        }
        
        /* Móviles grandes */
        @media (max-width: 768px) {
            .admin-card .card-header {
                flex-direction: column !important;
                gap: 15px !important;
                padding: 15px !important;
            }
            
            .admin-card .card-header h5 {
                margin-bottom: 0 !important;
                font-size: 1.1rem !important;
            }
            
            .admin-card .card-header .d-flex {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100% !important;
            }
            
            .admin-card .card-header select,
            .admin-card .card-header input {
                width: 100% !important;
            }
            
            .table-responsive {
                border-radius: 8px;
                margin: 0 -15px; /* Extender a los bordes en móvil */
            }
            
            .admin-table {
                min-width: 900px !important;
                font-size: 0.8rem;
            }
            
            .admin-table thead th {
                padding: 8px 4px !important;
                font-size: 0.65rem;
            }
            
            .admin-table tbody td {
                padding: 6px 4px !important;
            }
            
            /* Imagen más pequeña en móvil */
            .admin-table td:first-child {
                width: 60px !important;
                min-width: 60px !important;
                max-width: 60px !important;
            }
            
            .admin-table td:first-child img {
                width: 45px !important;
                height: 45px !important;
            }
            
            /* Nombre de producto más compacto */
            .admin-table td:nth-child(2) {
                min-width: 150px !important;
                max-width: 180px !important;
            }
            
            .admin-table td:nth-child(2) strong {
                font-size: 0.8rem;
            }
            
            .admin-table td:nth-child(2) small {
                font-size: 0.7rem;
            }
            
            /* Columna de acciones más compacta pero sticky */
            .admin-table td:last-child {
                min-width: 120px !important;
                width: 120px !important;
            }
            
            .btn-group-sm .btn {
                padding: 3px 5px !important;
                font-size: 0.65rem !important;
                min-width: 24px !important;
                height: 24px !important;
            }
        }
        
        /* Móviles pequeños */
        @media (max-width: 576px) {
            .admin-table {
                min-width: 800px !important;
                font-size: 0.75rem;
            }
            
            .admin-table thead th {
                padding: 6px 3px !important;
                font-size: 0.6rem;
            }
            
            .admin-table tbody td {
                padding: 5px 3px !important;
            }
            
            /* Ocultar algunas columnas menos importantes en pantallas muy pequeñas */
            .admin-table th:nth-child(6),
            .admin-table td:nth-child(6),  /* Colores */
            .admin-table th:nth-child(7),
            .admin-table td:nth-child(7) { /* Tallas */
                display: none !important;
            }
            
            .admin-table {
                min-width: 650px !important; /* Reducir ancho mínimo */
            }
        }
        
        /* Indicador de scroll para usuario */
        .table-responsive::after {
            content: "← Desliza para ver más →";
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.7rem;
            color: #6c757d;
            text-align: center;
            width: 100%;
            opacity: 0.8;
        }
        
        @media (min-width: 1200px) {
            .table-responsive::after {
                display: none;
            }
        }
        
        /* Scroll bar personalizado */
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--admin-accent);
            border-radius: 4px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--admin-primary);
        }

        /* =================== ESTILOS PARA GESTIÓN DE CARRUSEL =================== */
        
        .carousel-preview-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        
        .carousel-preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            min-height: 150px;
        }
        
        .carousel-slide-item {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: move;
            border: 2px solid transparent;
        }
        
        .carousel-slide-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            border-color: var(--admin-accent);
        }
        
        .carousel-slide-item.active {
            border-color: var(--admin-success);
            box-shadow: 0 4px 16px rgba(39, 174, 96, 0.2);
        }
        
        .carousel-slide-item.selected {
            border-color: var(--admin-primary);
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.3);
            transform: translateY(-3px) scale(1.02);
        }
        
        .carousel-slide-item.dragging {
            opacity: 0.6;
            transform: rotate(3deg) scale(0.95);
            z-index: 1000;
        }
        
        .carousel-slide-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            background: #f8f9fa;
        }
        
        .carousel-slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0,0,0,0.6) 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 12px;
            color: white;
        }
        
        .carousel-slide-title {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 2px;
            line-height: 1.2;
            max-height: 2.4em;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .carousel-slide-location {
            font-size: 0.7rem;
            opacity: 0.9;
            line-height: 1.1;
        }
        
        .carousel-slide-controls {
            position: absolute;
            top: 8px;
            right: 8px;
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        
        .carousel-slide-item:hover .carousel-slide-controls {
            opacity: 1;
        }
        
        .carousel-control-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .carousel-control-btn.edit {
            background: rgba(52, 152, 219, 0.9);
        }
        
        .carousel-control-btn.delete {
            background: rgba(231, 76, 60, 0.9);
        }
        
        .carousel-control-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .carousel-slide-order {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(44, 62, 80, 0.9);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .carousel-slide-status {
            position: absolute;
            bottom: 8px;
            right: 8px;
        }
        
        .carousel-slide-status .badge {
            font-size: 0.6rem;
            padding: 2px 6px;
        }
        
        .slide-config-panel {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        
        .help-panel {
            background: rgba(52, 152, 219, 0.05);
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid var(--admin-accent);
        }
        
        /* Placeholder para área vacía */
        .carousel-empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            background: white;
        }
        
        .carousel-empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        /* Animaciones para drag & drop */
        .carousel-preview-grid.drag-over {
            background: rgba(40, 167, 69, 0.05);
            border-color: var(--admin-success);
        }
        
        .carousel-preview-grid.drag-over::before {
            content: "Suelta aquí para reordenar";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: var(--admin-success);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            z-index: 10;
        }
        
        /* Responsive para gestión de carrusel */
        @media (max-width: 768px) {
            .carousel-preview-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
                gap: 10px;
            }
            
            .carousel-slide-image {
                height: 100px;
            }
            
            .carousel-slide-title {
                font-size: 0.75rem;
            }
            
            .carousel-slide-location {
                font-size: 0.65rem;
            }
            
            .slide-config-panel {
                margin-top: 20px;
            }
        }

        .admin-table thead th {
            border: none;
            padding: 1.5rem 1rem;
            font-weight: 600;
        }

        .admin-table tbody td {
            padding: 1rem;
            border-color: var(--admin-light);
        }

        /* Form Styles */
        .form-control {
            border-radius: 10px;
            border: 2px solid #e3e6f0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--admin-accent);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: var(--admin-primary);
            margin-bottom: 0.5rem;
        }

        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 5px solid var(--admin-accent);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--admin-primary);
            margin: 0;
        }

        .stats-card .stats-label {
            color: #666;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .stats-card .stats-icon {
            font-size: 3rem;
            color: var(--admin-accent);
            margin-bottom: 1rem;
        }

        /* Alert Styles */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1.5rem;
        }

        /* Loading Spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--admin-accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                margin: 1rem;
                border-radius: 10px;
            }
            
            .admin-sidebar {
                min-height: auto;
                padding-bottom: 2rem;
            }
            
            .sidebar-header {
                padding: 1rem;
            }
            
            .nav-pills .nav-link {
                padding: 1rem;
            }
            
            .admin-content {
                padding: 1rem;
            }
            
            .logout-btn {
                position: static;
                margin: 1rem;
            }
        }

        /* === ESTILOS PARA CARGA DE IMÁGENES === */
        .image-upload-container {
            position: relative;
        }

        .image-input {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .image-input:hover {
            border-color: var(--admin-accent);
            background-color: #e9ecef;
        }

        .image-preview {
            margin-top: 1rem;
        }

        .preview-item {
            position: relative;
            display: inline-block;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }

        .preview-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            border: 2px solid #fff;
        }

        .image-preview-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .upload-progress {
            width: 100%;
            height: 4px;
            background-color: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .upload-progress-bar {
            height: 100%;
            background-color: var(--admin-accent);
            transition: width 0.3s ease;
        }

        /* === ESTILOS ESPECÍFICOS PARA PEDIDOS === */
        .orders-table .btn-group-sm .btn {
            padding: 0.25rem 0.4rem;
            border-radius: 4px;
            margin: 0 1px;
        }
        
        .orders-stats-row .stats-card {
            transition: all 0.3s ease;
        }
        
        .orders-stats-row .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        
        .order-status-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
        }
        
        .order-payment-badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .orders-filters {
            background: var(--admin-light);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        
        .last-update-indicator {
            font-size: 0.8rem;
            color: #666;
            font-style: italic;
        }
        
        .auto-refresh-indicator {
            position: relative;
        }
        
        .auto-refresh-indicator.active::before {
            content: '';
            position: absolute;
            left: -15px;
            top: 50%;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .admin-table th {
            background: var(--admin-primary);
            color: white;
            border: none;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }
        
        .admin-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Responsive para tabla de pedidos */
        @media (max-width: 992px) {
            .admin-table th:nth-child(3),
            .admin-table td:nth-child(3) {
                display: none; /* Ocultar columna de productos en tablets */
            }
        }
        
        @media (max-width: 768px) {
            .admin-table th:nth-child(4),
            .admin-table td:nth-child(4),
            .admin-table th:nth-child(5),
            .admin-table td:nth-child(5),
            .admin-table th:nth-child(7),
            .admin-table td:nth-child(7) {
                display: none; /* Ocultar columnas de subtotal, envío y método pago en móviles */
            }
            
            .orders-filters .d-flex {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .orders-filters .form-select,
            .orders-filters .form-control {
                width: 100% !important;
            }
        }
        /* Estilos para modal de comprobante */
        .receipt-popup .swal2-popup {
            padding: 2rem !important;
        }
        
        .receipt-modal {
            text-align: center;
        }
        
        .receipt-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            border: 1px solid #dee2e6;
        }
        
        .receipt-image img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: transform 0.3s ease;
        }
        
        .receipt-image img:hover {
            transform: scale(1.05);
            cursor: pointer;
        }
        
        .btn-group .btn {
            margin: 0 2px;
        }
        
        /* Estilos para el modal de detalles del pedido */
        .order-details-popup .order-details-modal .section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        .order-details-popup .product-item {
            background-color: #ffffff;
            border: 1px solid #e9ecef !important;
        }
        
        .order-details-popup .product-item:hover {
            background-color: #f1f3f4;
        }
        
        .order-details-popup h6.fw-bold {
            border-bottom: 2px solid #007bff;
            padding-bottom: 0.5rem;
        }
        
        /* Estilos para el indicador de filtros */
        #filter-indicator {
            margin-top: 10px;
            border-left: 4px solid #007bff;
        }
        
        #filter-indicator .btn {
            font-size: 12px;
            padding: 2px 6px;
        }
        
        /* Estilos para las configuraciones generales */
        .admin-card .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        .admin-card .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .admin-card .form-check-input:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .admin-card h6.text-muted {
            color: #6c757d !important;
            font-weight: 600;
        }
        
        .admin-card .form-check-label {
            font-weight: 500;
        }
        
        .admin-card small.text-muted {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <!-- Alerta de bienvenida con información de sesión -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '¡Bienvenido!',
                text: 'Sesión iniciada como: <?php echo htmlspecialchars($admin_user); ?>',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        });
    </script>

    <div class="container-fluid admin-container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 admin-sidebar">
                <div class="sidebar-header">
                    <h3><i class="fas fa-store me-2"></i>M&A MODA</h3>
                    <p>Panel Administrativo</p>
                </div>
                
                <!-- Información del usuario -->
                <div class="user-info">
                    <i class="fas fa-user-circle fa-2x mb-2" style="color: white;"></i>
                    <h5><?php echo htmlspecialchars($admin_user); ?></h5>
                    <small>Administrador</small>
                </div>
                
                <!-- Navigation -->
                <ul class="nav nav-pills flex-column" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active w-100 text-start" id="v-pills-dashboard-tab" data-bs-toggle="pill" data-bs-target="#v-pills-dashboard" type="button" role="tab">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100 text-start" id="v-pills-productos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-productos" type="button" role="tab">
                            <i class="fas fa-box"></i>Productos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100 text-start" id="v-pills-categorias-tab" data-bs-toggle="pill" data-bs-target="#v-pills-categorias" type="button" role="tab">
                            <i class="fas fa-tags"></i>Categorías
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100 text-start" id="v-pills-pedidos-tab" data-bs-toggle="pill" data-bs-target="#v-pills-pedidos" type="button" role="tab">
                            <i class="fas fa-shopping-cart"></i>Pedidos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link w-100 text-start" id="v-pills-configuracion-tab" data-bs-toggle="pill" data-bs-target="#v-pills-configuracion" type="button" role="tab">
                            <i class="fas fa-cog"></i>Configuración
                        </button>
                    </li>
                </ul>
                
                <!-- Logout Button -->
                <div class="logout-btn">
                    <a href="logout.php" class="btn btn-danger w-100" onclick="return confirm('¿Estás seguro de que quieres cerrar sesión?')">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 admin-content">
                <div class="tab-content" id="v-pills-tabContent">
                    
                    <!-- Dashboard Tab -->
                    <div class="tab-pane fade show active" id="v-pills-dashboard" role="tabpanel">
                        <div class="content-header">
                            <h2><i class="fas fa-tachometer-alt me-3"></i>Dashboard</h2>
                            <p>Resumen general del sistema</p>
                        </div>
                        
                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <h3 class="stats-number" id="total-productos">0</h3>
                                    <p class="stats-label">Productos</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <h3 class="stats-number" id="total-categorias">0</h3>
                                    <p class="stats-label">Categorías</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h3 class="stats-number" id="total-pedidos">0</h3>
                                    <p class="stats-label">Pedidos</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="admin-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Acciones Rápidas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-admin-primary" onclick="switchTab('v-pills-productos')">
                                                <i class="fas fa-plus me-2"></i>Agregar Producto
                                            </button>
                                            <button class="btn btn-admin-accent" onclick="switchTab('v-pills-categorias')">
                                                <i class="fas fa-tags me-2"></i>Gestionar Categorías
                                            </button>
                                            <button class="btn btn-admin-primary" onclick="switchTab('v-pills-pedidos')">
                                                <i class="fas fa-eye me-2"></i>Ver Pedidos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="admin-card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Estado del Sistema</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            Sistema funcionando correctamente
                                        </div>
                                        <p><strong>Última actualización:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                                        <p><strong>Usuario activo:</strong> <?php echo htmlspecialchars($admin_user); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Productos Tab -->
                    <div class="tab-pane fade" id="v-pills-productos" role="tabpanel">
                        <div class="content-header">
                            <h2><i class="fas fa-box me-3"></i>Gestión de Productos</h2>
                            <p>Administra el catálogo de productos con categorías</p>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-admin-primary" onclick="showAddProductModal()">
                                        <i class="fas fa-plus me-2"></i>Nuevo Producto
                                    </button>
                                    <button class="btn btn-admin-accent" onclick="loadProducts()">
                                        <i class="fas fa-refresh me-2"></i>Actualizar
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-filter me-2"></i>Filtrar por Categoría
                                        </button>
                                        <ul class="dropdown-menu" id="category-filter-menu">
                                            <li><a class="dropdown-item" href="#" onclick="filterProductsByCategory('')">Todas las categorías</a></li>
                                            <!-- Se llenará dinámicamente -->
                                        </ul>
                                    </div>
                                    <button class="btn btn-outline-success" onclick="exportProducts()">
                                        <i class="fas fa-download me-2"></i>Exportar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas rápidas -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <h3 class="stats-number" id="total-products-stat">0</h3>
                                    <p class="stats-label">Total Productos</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h3 class="stats-number" id="active-products-stat">0</h3>
                                    <p class="stats-label">Activos</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <h3 class="stats-number" id="featured-products-stat">0</h3>
                                    <p class="stats-label">Destacados</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <h3 class="stats-number" id="categories-with-products-stat">0</h3>
                                    <p class="stats-label">Con Productos</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de productos -->
                        <div class="admin-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Productos</h5>
                                <div class="d-flex gap-2">
                                    <select class="form-select form-select-sm" id="products-per-page" onchange="changeProductsPerPage()">
                                        <option value="10">10 por página</option>
                                        <option value="25" selected>25 por página</option>
                                        <option value="50">50 por página</option>
                                    </select>
                                    <input type="search" class="form-control form-control-sm" placeholder="Buscar productos..." id="products-search" onkeyup="searchProducts()">
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table admin-table mb-0" id="products-table">
                                        <thead>
                                            <tr>
                                                <th width="80">Imagen</th>
                                                <th>Producto</th>
                                                <th>Precio</th>
                                                <th>Categoría</th>
                                                <th>Género</th>
                                                <th>Colores</th>
                                                <th>Tallas</th>
                                                <th>Stock</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="products-tbody">
                                            <tr>
                                                <td colspan="10" class="text-center p-4">
                                                    <div class="loading-spinner me-2"></div>
                                                    Cargando productos...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <span class="text-muted" id="products-info">Mostrando 0 de 0 productos</span>
                            </div>
                            <nav>
                                <ul class="pagination mb-0" id="products-pagination">
                                    <!-- Se generará dinámicamente -->
                                </ul>
                            </nav>
                        </div>
                    </div>
                    
                    <!-- Categorías Tab -->
                    <div class="tab-pane fade" id="v-pills-categorias" role="tabpanel">
                        <div class="content-header">
                            <h2><i class="fas fa-tags me-3"></i>Gestión de Categorías</h2>
                            <p>Administra las categorías de productos</p>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-admin-primary" onclick="showAddCategoryModal()">
                                        <i class="fas fa-plus me-2"></i>Nueva Categoría
                                    </button>
                                    <button class="btn btn-admin-accent" onclick="loadCategories()">
                                        <i class="fas fa-refresh me-2"></i>Actualizar
                                    </button>
                                    <button class="btn btn-outline-success" onclick="exportCategories()">
                                        <i class="fas fa-download me-2"></i>Exportar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de categorías -->
                        <div class="admin-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Categorías</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table admin-table mb-0" id="categories-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Orden</th>
                                                <th>Estado</th>
                                                <th>Productos</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categories-tbody">
                                            <tr>
                                                <td colspan="7" class="text-center p-4">
                                                    <div class="loading-spinner me-2"></div>
                                                    Cargando categorías...
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pedidos Tab -->
                    <div class="tab-pane fade" id="v-pills-pedidos" role="tabpanel">
                        <div class="content-header">
                            <h2><i class="fas fa-shopping-cart me-3"></i>Gestión de Pedidos</h2>
                            <p>Administra todos los pedidos y su información de envío</p>
                        </div>
                        
                        <!-- Estadísticas de Pedidos -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                    <h3 class="stats-number" id="total-orders-stat">0</h3>
                                    <p class="stats-label">Total Pedidos</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <h3 class="stats-number" id="pending-orders-stat">0</h3>
                                    <p class="stats-label">Pendientes</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <h3 class="stats-number" id="completed-orders-stat">0</h3>
                                    <p class="stats-label">Completados</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stats-card">
                                    <div class="stats-icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <h3 class="stats-number" id="total-revenue-stat">$0</h3>
                                    <p class="stats-label">Ingresos Total</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Filtros y Acciones -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <select class="form-select" id="order-status-filter" onchange="filterOrdersByStatus()" style="width: auto;">
                                        <option value="">Todos los estados</option>
                                        <optgroup label="Estados de Pago">
                                            <option value="pendiente">Pendiente</option>
                                            <option value="aprobado">Aprobado</option>
                                            <option value="rechazado">Rechazado</option>
                                        </optgroup>
                                        <optgroup label="Estados de Envío">
                                            <option value="env_pendiente" data-type="shipping" data-value="pendiente">Envío: Pendiente</option>
                                            <option value="env_en_proceso" data-type="shipping" data-value="en_proceso">Envío: En Proceso</option>
                                            <option value="env_enviado" data-type="shipping" data-value="enviado">Envío: Enviado</option>
                                            <option value="env_entregado" data-type="shipping" data-value="entregado">Envío: Entregado</option>
                                        </optgroup>
                                    </select>
                                    
                                    <input type="date" class="form-control" id="order-date-filter" onchange="filterOrdersByDate()" style="width: auto;">
                                    
                                    <input type="search" class="form-control" placeholder="Buscar por cliente, email o ID..." id="orders-search" onkeyup="searchOrders()" style="min-width: 250px;">
                                    
                                    <button class="btn btn-admin-accent" onclick="refreshOrders()" title="Actualizar">
                                        <i class="fas fa-refresh"></i>
                                    </button>
                                    
                                    <button class="btn btn-outline-success" onclick="exportOrders()" title="Exportar">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    
                                    <div class="form-check form-switch ms-auto">
                                        <input class="form-check-input" type="checkbox" id="auto-refresh-orders" checked onchange="toggleAutoRefresh()">
                                        <label class="form-check-label" for="auto-refresh-orders">
                                            Auto-actualizar (30s)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de Pedidos -->
                        <div class="admin-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Pedidos</h5>
                                <div class="d-flex gap-2 align-items-center">
                                    <span class="badge bg-info" id="orders-count">0 pedidos</span>
                                    <span class="text-muted" id="last-update">Última actualización: --</span>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table admin-table mb-0" id="orders-table">
                                        <thead>
                                            <tr>
                                                <th width="80">#ID</th>
                                                <th>Cliente</th>
                                                <th>Productos</th>
                                                <th>Subtotal</th>
                                                <th>Envío</th>
                                                <th>Total</th>
                                                <th>Método Pago</th>
                                                <th>Comprobante</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th width="150">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orders-tbody">
                                            <tr>
                                                <td colspan="11" class="text-center p-4">
                                                    <div class="d-flex justify-content-center align-items-center">
                                                        <div class="spinner-border text-primary me-2" role="status"></div>
                                                        <span>Cargando pedidos...</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información de Paginación -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <span class="text-muted" id="orders-info">Mostrando 0 de 0 pedidos</span>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <label for="orders-per-page" class="form-label mb-0">Mostrar:</label>
                                <select class="form-select form-select-sm" id="orders-per-page" onchange="changeOrdersPerPage()" style="width: auto;">
                                    <option value="10">10</option>
                                    <option value="25" selected>25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <span class="text-muted">por página</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Configuración Tab -->
                    <div class="tab-pane fade" id="v-pills-configuracion" role="tabpanel">
                        <div class="content-header">
                            <h2><i class="fas fa-cog me-3"></i>Configuración del Sistema</h2>
                            <p>Gestiona las configuraciones generales del sitio web</p>
                        </div>
                        
                        <!-- Configuración del Carrusel Principal -->
                        <div class="admin-card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-images me-2"></i>Carrusel Principal (Página de Inicio)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="text-muted mb-4">
                                            Configura las imágenes que aparecen en el carrusel principal de la página de inicio. 
                                            Puedes reordenar las diapositivas arrastrándolas.
                                        </p>
                                        
                                        <!-- Área de Upload para Carrusel -->
                                        <div class="enhanced-upload mb-4">
                                            <div class="upload-area" onclick="document.getElementById('carouselImages').click()">
                                                <div class="upload-content">
                                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                                    <p class="mb-2"><strong>Arrastra imágenes del carrusel aquí</strong></p>
                                                    <p class="text-muted small">o haz clic para seleccionar archivos</p>
                                                    <small class="text-muted">JPG, PNG, GIF, WebP (máx. 8MB c/u)</small>
                                                    <div class="mt-3">
                                                        <span class="badge bg-info me-2">Tamaño recomendado: 1920x800px</span>
                                                        <span class="badge bg-warning">Formato landscape</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="file" class="form-control" id="carouselImages" 
                                                   multiple accept="image/*" style="display: none;" 
                                                   onchange="previewCarouselImages(this)">
                                        </div>
                                        
                                        <!-- Preview Grid del Carrusel -->
                                        <div class="carousel-preview-container">
                                            <h6 class="mb-3">
                                                <i class="fas fa-eye me-2"></i>Vista Previa del Carrusel
                                                <span class="badge bg-secondary ms-2" id="carousel-count">0 diapositivas</span>
                                            </h6>
                                            <div class="carousel-preview-grid" id="carouselPreviewGrid">
                                                <!-- Slides actuales del carrusel se cargarán aquí -->
                                            </div>
                                        </div>
                                        
                                        <!-- Botones de acción -->
                                        <div class="d-flex gap-2 mt-4">
                                            <button type="button" class="btn btn-admin-primary" onclick="saveCarouselChanges()">
                                                <i class="fas fa-save me-2"></i>Guardar Cambios
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="resetCarouselChanges()">
                                                <i class="fas fa-undo me-2"></i>Descartar Cambios
                                            </button>
                                            <button type="button" class="btn btn-outline-info" onclick="previewCarousel()">
                                                <i class="fas fa-external-link-alt me-2"></i>Ver en Sitio Web
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Panel de configuración de slide -->
                                    <div class="col-md-4">
                                        <div class="slide-config-panel" style="display: none;" id="slideConfigPanel">
                                            <h6 class="border-bottom pb-2 mb-3">
                                                <i class="fas fa-edit me-2"></i>Editar Diapositiva
                                            </h6>
                                            
                                            <form id="slideConfigForm">
                                                <input type="hidden" id="currentSlideIndex">
                                                
                                                <div class="mb-3">
                                                    <label for="slideTitle" class="form-label">Título</label>
                                                    <input type="text" class="form-control" id="slideTitle" 
                                                           placeholder="Título de la diapositiva">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="slideSubtitle" class="form-label">Subtítulo</label>
                                                    <input type="text" class="form-control" id="slideSubtitle" 
                                                           placeholder="Subtítulo o descripción">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="slideLocation" class="form-label">Ubicación</label>
                                                    <input type="text" class="form-control" id="slideLocation" 
                                                           placeholder="Dirección o ubicación"
                                                           value="CLL 3 20A39 Madrid, Cundinamarca">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="slideButtonText" class="form-label">Texto del Botón</label>
                                                    <input type="text" class="form-control" id="slideButtonText" 
                                                           placeholder="Texto del botón de acción">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="slideButtonLink" class="form-label">Enlace del Botón</label>
                                                    <input type="url" class="form-control" id="slideButtonLink" 
                                                           placeholder="https://ejemplo.com">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="slideActive" checked>
                                                        <label class="form-check-label" for="slideActive">
                                                            Diapositiva activa
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="saveSlideConfig()">
                                                        <i class="fas fa-check"></i> Aplicar
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="cancelSlideConfig()">
                                                        <i class="fas fa-times"></i> Cancelar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <!-- Ayuda y consejos -->
                                        <div class="help-panel mt-4">
                                            <h6 class="text-primary">
                                                <i class="fas fa-lightbulb me-2"></i>Consejos
                                            </h6>
                                            <ul class="list-unstyled small text-muted">
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    Usa imágenes de alta calidad (1920x800px)
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    Máximo 5 diapositivas para mejor rendimiento
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    Comprime las imágenes antes de subir
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    Haz clic en una diapositiva para editarla
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Otras Configuraciones -->
                        <div class="admin-card">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-sliders-h me-2"></i>Configuraciones Generales
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <!-- Información de la Empresa -->
                                    <div class="col-md-6">
                                        <h6 class="text-muted border-bottom pb-2 mb-3">
                                            <i class="fas fa-building me-2"></i>Información de la Empresa
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label for="companyName" class="form-label">Nombre de la Empresa</label>
                                            <input type="text" class="form-control" id="companyName" 
                                                   value="M & A MODA ACTUAL">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="companyAddress" class="form-label">Dirección</label>
                                            <input type="text" class="form-control" id="companyAddress" 
                                                   value="CLL 3 20A39 Madrid, Cundinamarca">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="companyPhone" class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control" id="companyPhone" 
                                                   placeholder="Número de teléfono">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="companyEmail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="companyEmail" 
                                                   placeholder="email@empresa.com">
                                        </div>
                                    </div>
                                    
                                    <!-- Configuraciones del Sitio -->
                                    <div class="col-md-6">
                                        <h6 class="text-muted border-bottom pb-2 mb-3">
                                            <i class="fas fa-globe me-2"></i>Configuraciones del Sitio
                                        </h6>
                                        
                                        <div class="mb-3">
                                            <label for="siteTitle" class="form-label">Título del Sitio</label>
                                            <input type="text" class="form-control" id="siteTitle" 
                                                   value="M & A MODA ACTUAL">
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="siteDescription" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="siteDescription" rows="3" 
                                                      placeholder="Descripción del sitio web"></textarea>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="siteMaintenanceMode">
                                                <label class="form-check-label" for="siteMaintenanceMode">
                                                    Modo mantenimiento
                                                </label>
                                            </div>
                                            <small class="text-muted">
                                                Activa esta opción para mostrar una página de mantenimiento
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="allowRegistrations" checked>
                                                <label class="form-check-label" for="allowRegistrations">
                                                    Permitir registros
                                                </label>
                                            </div>
                                            <small class="text-muted">
                                                Permite que nuevos usuarios se registren
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Botón para guardar configuraciones generales -->
                                <div class="border-top pt-3 mt-4">
                                    <button type="button" class="btn btn-admin-primary" onclick="saveGeneralSettings()">
                                        <i class="fas fa-save me-2"></i>Guardar Configuraciones
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar/Editar Producto -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalTitle">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">
                        <i class="fas fa-plus me-2"></i>Nuevo Producto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm">
                        <input type="hidden" id="productId">
                        
                        <div class="row g-3">
                            <!-- Información básica -->
                            <div class="col-12">
                                <h6 class="text-muted border-bottom pb-2 mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Información Básica
                                </h6>
                            </div>
                            
                            <div class="col-md-8">
                                <label for="productName" class="form-label">Nombre del Producto *</label>
                                <input type="text" class="form-control" id="productName" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="productCategory" class="form-label">Categoría *</label>
                                <select class="form-select" id="productCategory" required>
                                    <option value="">Seleccionar categoría...</option>
                                    <!-- Se llenará dinámicamente -->
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label for="productDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="productDescription" rows="3"></textarea>
                            </div>
                            
                            <!-- Precios y Stock -->
                            <div class="col-12">
                                <h6 class="text-muted border-bottom pb-2 mb-3">
                                    <i class="fas fa-dollar-sign me-2"></i>Precios y Stock
                                </h6>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="productPrice" class="form-label">Precio *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="productPrice" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="productOriginalPrice" class="form-label">Precio Original</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="productOriginalPrice" step="0.01" min="0">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <label for="productStock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="productStock" min="0" value="0">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="productSku" class="form-label">SKU</label>
                                <input type="text" class="form-control" id="productSku">
                            </div>
                            
                            <!-- Atributos del Producto -->
                            <div class="col-12">
                                <h6 class="text-muted border-bottom pb-2 mb-3">
                                    <i class="fas fa-tags me-2"></i>Atributos del Producto
                                </h6>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="productGender" class="form-label">Género</label>
                                <select class="form-select" id="productGender">
                                    <option value="">Sin especificar</option>
                                    <option value="mujer">Mujer</option>
                                    <option value="hombre">Hombre</option>
                                    <option value="unisex">Unisex</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="productSizes" class="form-label">Tallas Disponibles</label>
                                <input type="text" class="form-control" id="productSizes" placeholder="XS,S,M,L,XL">
                                <div class="form-text">Separar con comas</div>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="productColors" class="form-label">Colores Disponibles</label>
                                <input type="text" class="form-control" id="productColors" placeholder="Rojo,Azul,Negro">
                                <div class="form-text">Separar con comas</div>
                            </div>
                            
                            <!-- Imágenes -->
                            <div class="col-12">
                                <h6 class="text-muted border-bottom pb-2 mb-3">
                                    <i class="fas fa-images me-2"></i>Imágenes del Producto
                                </h6>
                            </div>
                            
                            <div class="col-12">
                                <label for="productMainImage" class="form-label">
                                    <i class="fas fa-photo-video me-2"></i>Imagen o Video Principal
                                    <small class="text-muted">(opcional si subes medios adicionales)</small>
                                </label>
                                <div class="image-upload-container">
                                    <input type="file" class="form-control media-input" id="productMainImage" accept="image/*,video/*" onchange="previewMainMedia(this, 'mainImagePreview')">
                                    <div class="image-preview mt-2" id="mainImagePreview" style="display: none;">
                                        <div class="preview-item">
                                            <img src="" alt="Vista previa" class="preview-img" style="display: none;">
                                            <video src="" class="preview-video" muted style="display: none;"></video>
                                            <div class="media-type-indicator" id="mainMediaTypeIndicator" style="display: none;"></div>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-image" onclick="removeImagePreview('productMainImage', 'mainImagePreview')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="form-text">Formatos permitidos: JPG, PNG, GIF, WebP, MP4, MOV, AVI (sin límite de tamaño)</div>
                                </div>
                                <!-- Campo oculto para almacenar la URL de la imagen principal -->
                                <input type="hidden" id="productMainImageUrl">
                            </div>
                            
                            <div class="col-12">
                                <label for="productAdditionalMedia" class="form-label">
                                    <i class="fas fa-photo-video me-2"></i>Imágenes y Videos Adicionales
                                    <span class="badge bg-info ms-2" id="additionalMediaCount">0 archivos</span>
                                </label>
                                <div class="image-upload-container enhanced-upload">
                                    <div class="upload-area" id="additionalUploadArea" 
                                         onclick="document.getElementById('productAdditionalMedia').click()"
                                         ondrop="handleDrop(event, 'productAdditionalMedia')"
                                         ondragover="handleDragOver(event)"
                                         ondragleave="handleDragLeave(event)">
                                        <div class="upload-content">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <p class="mb-2"><strong>Arrastra imágenes y videos aquí</strong></p>
                                            <p class="text-muted small">o haz clic para seleccionar archivos</p>
                                            <small class="text-muted">JPG, PNG, GIF, WebP, MP4, MOV, AVI (sin límite de tamaño)</small>
                                        </div>
                                    </div>
                                    <input type="file" class="form-control media-input" id="productAdditionalMedia" 
                                           multiple accept="image/*,video/*" style="display: none;" 
                                           onchange="previewMultipleMediaEnhanced(this, 'additionalMediaPreview')">
                                    <div class="media-preview-grid mt-3" id="additionalMediaPreview"></div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Las imágenes y videos aparecerán en un carrusel en la vista del producto.
                                        Los videos se reproducirán automáticamente sin controles.
                                        Puedes reordenar arrastrando los elementos.
                                    </div>
                                </div>
                                <!-- Campo oculto para almacenar las URLs de los medios adicionales -->
                                <input type="hidden" id="productAdditionalMediaUrls">
                            </div>
                            
                            <!-- Estado y Configuración -->
                            <div class="col-12">
                                <h6 class="text-muted border-bottom pb-2 mb-3">
                                    <i class="fas fa-cog me-2"></i>Configuración
                                </h6>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="productActive" checked>
                                    <label class="form-check-label" for="productActive">
                                        Producto activo
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="productFeatured">
                                    <label class="form-check-label" for="productFeatured">
                                        Producto destacado
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="productOnSale">
                                    <label class="form-check-label" for="productOnSale">
                                        En oferta
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-admin-primary" onclick="saveProduct()">
                        <i class="fas fa-save me-2"></i>Guardar Producto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Agregar/Editar Categoría -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalTitle">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalTitle">
                        <i class="fas fa-plus me-2"></i>Nueva Categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <input type="hidden" id="categoryId">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="categoryName" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="categoryName" required>
                                <div class="form-text">Ejemplo: Pantalones, Camisas, etc.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categorySlug" class="form-label">Slug</label>
                                <input type="text" class="form-control" id="categorySlug">
                                <div class="form-text">Se genera automáticamente si se deja vacío</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="categoryDescription" class="form-label">Descripción</label>
                                <textarea class="form-control" id="categoryDescription" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categoryParent" class="form-label">Categoría Padre</label>
                                <select class="form-select" id="categoryParent">
                                    <option value="">-- Sin categoría padre --</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="categorySortOrder" class="form-label">Orden</label>
                                <input type="number" class="form-control" id="categorySortOrder" value="0" min="0">
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="categoryActive" checked>
                                    <label class="form-check-label" for="categoryActive">
                                        Categoría activa
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-admin-primary" onclick="saveCategory()">
                        <i class="fas fa-save me-2"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Admin Scripts -->
    <script>
        // Función para cambiar de pestaña programáticamente
        function switchTab(tabId) {
            const tab = document.getElementById(tabId + '-tab');
            if (tab) {
                tab.click();
            }
        }
        
        // Cargar estadísticas del dashboard
        async function loadDashboardStats() {
            console.log('🚀 Iniciando carga de estadísticas del dashboard...');
            
            // Cargar estadísticas reales de productos desde el API
            updateProductsStats();
            
            // Cargar estadísticas reales de pedidos usando MercadoPago
            try {
                const ordersResponse = await fetch('get-estadisticas.php');
                const ordersResult = await ordersResponse.json();
                
                if (ordersResult.success && ordersResult.stats) {
                    const stats = ordersResult.stats;
                    document.getElementById('total-pedidos').textContent = stats.total_pedidos || 0;
                    console.log(`✅ Estadísticas MercadoPago cargadas: ${stats.total_pedidos} pedidos, $${stats.total_ventas} en ventas`);
                } else {
                    document.getElementById('total-pedidos').textContent = '0';
                    console.log('⚠️ No se pudieron cargar estadísticas de MercadoPago');
                }
            } catch (error) {
                console.error('❌ Error cargando estadísticas de MercadoPago:', error);
                document.getElementById('total-pedidos').textContent = '0';
            }
            
            // Simular otros datos mientras no tengamos endpoints específicos
            setTimeout(() => {
                const clientesElement = document.getElementById('total-clientes');
                if (clientesElement) {
                    clientesElement.textContent = '12';
                }
            }, 500);
        }
        
        // Inicializar dashboard al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
        });
        
        // Manejar el cambio de pestañas
        document.addEventListener('shown.bs.tab', function (event) {
            const target = event.target.getAttribute('data-bs-target');
            
            // Cargar contenido específico según la pestaña
            switch(target) {
                case '#v-pills-productos':
                    loadProductosContent();
                    break;
                case '#v-pills-categorias':
                    loadCategoriasContent();
                    break;
                case '#v-pills-configuracion':
                    console.log('🔧 Inicializando sección de configuración...');
                    loadCurrentCarouselSlides();
                    setupCarouselUploadDragDrop();
                    loadGeneralSettings(); // Cargar configuraciones generales
                    break;
                // Agregar más casos según sea necesario
            }
        });
        
        function loadProductosContent() {
            loadProducts();
            loadCategoryFilterMenu();
        }
        
        function loadCategoriasContent() {
            loadCategories();
        }
        
        // === GESTIÓN DE CATEGORÍAS ===
        
        async function loadCategories() {
            const tbody = document.getElementById('categories-tbody');
            
            try {
                // Mostrar loading
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center p-4">
                            <div class="loading-spinner me-2"></div>
                            Cargando categorías...
                        </td>
                    </tr>
                `;
                
                const response = await fetch('api/categorias.php?admin=true');
                const result = await response.json();
                
                console.log('🔍 Respuesta de la API:', result);
                
                if (result.success && result.data && result.data.length > 0) {
                    tbody.innerHTML = result.data.map(category => `
                        <tr>
                            <td><code>${category.id}</code></td>
                            <td><strong>${category.name}</strong></td>
                            <td>${category.description || '<em>Sin descripción</em>'}</td>
                            <td><span class="badge bg-info">${category.sort_order}</span></td>
                            <td>
                                <span class="badge ${category.is_active == 1 ? 'bg-success' : 'bg-secondary'}">
                                    ${category.is_active == 1 ? 'Activa' : 'Inactiva'}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary" id="count-${category.id}">
                                    <i class="fas fa-spinner fa-spin"></i>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="editCategory('${category.id}')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteCategory('${category.id}', '${category.name}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                    
                    // Cargar conteo de productos para cada categoría
                    result.data.forEach(category => {
                        loadCategoryProductCount(category.id);
                    });
                    
                } else {
                    console.log('⚠️ Sin categorías o error en la respuesta:', result);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center p-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ${result.message || 'No hay categorías registradas.'} 
                                    <br><small>Respuesta completa: ${JSON.stringify(result)}</small>
                                    <button class="btn btn-sm btn-primary ms-2" onclick="showAddCategoryModal()">
                                        Crear primera categoría
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                
            } catch (error) {
                console.error('Error al cargar categorías:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center p-4">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar categorías: ${error.message}
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
        
        async function loadCategoryProductCount(categoryId) {
            try {
                const response = await fetch(`api/productos-v2.php?category=${categoryId}`);
                const result = await response.json();
                const count = result.success ? result.data.length : 0;
                
                const countElement = document.getElementById(`count-${categoryId}`);
                if (countElement) {
                    countElement.innerHTML = count;
                    countElement.className = `badge ${count > 0 ? 'bg-primary' : 'bg-secondary'}`;
                }
            } catch (error) {
                const countElement = document.getElementById(`count-${categoryId}`);
                if (countElement) {
                    countElement.innerHTML = '?';
                    countElement.className = 'badge bg-warning';
                }
            }
        }
        
        function showAddCategoryModal() {
            // Limpiar formulario
            document.getElementById('categoryForm').reset();
            document.getElementById('categoryId').value = '';
            document.getElementById('categoryActive').checked = true;
            
            // Cambiar título
            document.getElementById('categoryModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Nueva Categoría';
            
            // Cargar categorías padre
            loadParentCategories();
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        }
        
        async function editCategory(categoryId) {
            try {
                const response = await fetch(`api/categorias.php?id=${categoryId}`);
                const result = await response.json();
                
                if (result.success) {
                    const category = result.data;
                    
                    // Llenar formulario
                    document.getElementById('categoryId').value = category.id;
                    document.getElementById('categoryName').value = category.name;
                    document.getElementById('categorySlug').value = category.slug || '';
                    document.getElementById('categoryDescription').value = category.description || '';
                    document.getElementById('categoryParent').value = category.parent_id || '';
                    document.getElementById('categorySortOrder').value = category.sort_order || 0;
                    document.getElementById('categoryActive').checked = category.is_active == 1;
                    
                    // Cambiar título
                    document.getElementById('categoryModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Categoría';
                    
                    // Cargar categorías padre
                    await loadParentCategories(category.id);
                    
                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
                    modal.show();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                console.error('Error al cargar categoría:', error);
                Swal.fire('Error', 'No se pudo cargar la categoría', 'error');
            }
        }
        
        async function loadParentCategories(excludeId = null) {
            const select = document.getElementById('categoryParent');
            select.innerHTML = '<option value="">-- Sin categoría padre --</option>';
            
            try {
                const response = await fetch('api/categorias.php?admin=true');
                const result = await response.json();
                
                if (result.success) {
                    result.data
                        .filter(cat => cat.id !== excludeId)
                        .forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            select.appendChild(option);
                        });
                }
            } catch (error) {
                console.error('Error al cargar categorías padre:', error);
            }
        }
        
        async function saveCategory() {
            const form = document.getElementById('categoryForm');
            
            // Validación básica
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const categoryData = {
                id: document.getElementById('categoryId').value,
                name: document.getElementById('categoryName').value.trim(),
                slug: document.getElementById('categorySlug').value.trim(),
                description: document.getElementById('categoryDescription').value.trim(),
                parent_id: document.getElementById('categoryParent').value || null,
                sort_order: parseInt(document.getElementById('categorySortOrder').value) || 0,
                is_active: document.getElementById('categoryActive').checked ? 1 : 0
            };
            
            // Auto-generar slug si está vacío
            if (!categoryData.slug) {
                categoryData.slug = categoryData.name.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-');
            }
            
            // Auto-generar ID si está vacío (modo crear)
            if (!categoryData.id) {
                categoryData.id = categoryData.slug;
            }
            
            try {
                const isEdit = document.getElementById('categoryId').value !== '';
                const method = isEdit ? 'PUT' : 'POST';
                const url = 'api/categorias.php';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(categoryData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: result.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('categoryModal')).hide();
                    
                    // Recargar categorías
                    loadCategories();
                    
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
                
            } catch (error) {
                console.error('Error al guardar categoría:', error);
                Swal.fire('Error', 'No se pudo guardar la categoría', 'error');
            }
        }
        
        async function deleteCategory(categoryId, categoryName) {
            const result = await Swal.fire({
                title: '¿Eliminar categoría?',
                text: `¿Estás seguro de eliminar la categoría "${categoryName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`api/categorias.php?id=${categoryId}`, {
                        method: 'DELETE'
                    });
                    
                    const deleteResult = await response.json();
                    
                    if (deleteResult.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminada!',
                            text: deleteResult.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        loadCategories();
                    } else {
                        Swal.fire('Error', deleteResult.message, 'error');
                    }
                    
                } catch (error) {
                    console.error('Error al eliminar categoría:', error);
                    Swal.fire('Error', 'No se pudo eliminar la categoría', 'error');
                }
            }
        }
        
        function exportCategories() {
            // Implementar exportación de categorías
            Swal.fire({
                title: 'Exportar Categorías',
                text: 'Esta funcionalidad estará disponible próximamente',
                icon: 'info'
            });
        }
        
        // Auto-generar slug cuando se escribe el nombre
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('categoryName');
            const slugInput = document.getElementById('categorySlug');
            
            if (nameInput && slugInput) {
                nameInput.addEventListener('input', function() {
                    if (!slugInput.value) {
                        const slug = this.value.toLowerCase()
                            .replace(/[^a-z0-9\s-]/g, '')
                            .replace(/\s+/g, '-');
                        slugInput.value = slug;
                    }
                });
            }
        });

        // === GESTIÓN DE PRODUCTOS ===
        
        let currentProductsPage = 1;
        let currentProductsPerPage = 25;
        let currentCategoryFilter = '';
        let currentSearchQuery = '';
        
        async function loadProducts(page = 1) {
            const tbody = document.getElementById('products-tbody');
            currentProductsPage = page;
            
            try {
                // Mostrar loading
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center p-4">
                            <div class="loading-spinner me-2"></div>
                            Cargando productos...
                        </td>
                    </tr>
                `;
                
                // Construir URL con filtros
                let url = `api/productos-v2.php?page=${page}&limit=${currentProductsPerPage}`;
                if (currentCategoryFilter) {
                    url += `&category=${currentCategoryFilter}`;
                }
                if (currentSearchQuery) {
                    url += `&search=${encodeURIComponent(currentSearchQuery)}`;
                }
                
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    tbody.innerHTML = result.data.map(product => `
                        <tr>
                            <td>
                                ${product.image || product.main_image ? 
                                    (() => {
                                        const imageSrc = product.image || product.main_image;
                                        const isVideo = /\.(mp4|mov|avi|webm)$/i.test(imageSrc);
                                        return isVideo 
                                            ? `<video src="${imageSrc}" 
                                                     alt="${product.name}" 
                                                     class="img-fluid rounded" 
                                                     style="width: 60px; height: 60px; object-fit: cover;"
                                                     muted autoplay loop playsinline>
                                               </video>`
                                            : `<img src="${imageSrc}" 
                                                   alt="${product.name}" 
                                                   class="img-fluid rounded" 
                                                   style="width: 60px; height: 60px; object-fit: cover;">`;
                                    })() :
                                    `<div class="d-flex align-items-center justify-content-center bg-light rounded" 
                                         style="width: 60px; height: 60px; font-size: 24px; color: #6c757d;">
                                         <i class="fas fa-image"></i>
                                     </div>`
                                }
                            </td>
                            <td>
                                <strong>${product.name}</strong>
                                <br>
                                <small class="text-muted">${product.description ? (product.description.length > 50 ? product.description.substring(0, 50) + '...' : product.description) : ''}</small>
                                ${product.sku ? '<br><code>SKU: ' + product.sku + '</code>' : ''}
                            </td>
                            <td>
                                <strong class="text-success">$${formatPrice(product.price)}</strong>
                                ${product.sale_price && product.sale_price > 0 ? 
                                    '<br><small class="text-muted text-decoration-line-through">$' + formatPrice(product.sale_price) + '</small>' : ''}
                            </td>
                            <td>
                                <span class="badge bg-info">${product.category_name || product.category_id || 'Sin categoría'}</span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">${product.gender || 'Sin género'}</span>
                            </td>
                            <td>
                                ${product.colors && Array.isArray(product.colors) && product.colors.length > 0 ? 
                                    product.colors.map(color => `<span class="badge bg-info me-1">${color}</span>`).join('') : 
                                    '<span class="text-muted">Sin colores</span>'
                                }
                            </td>
                            <td>
                                ${product.sizes && Array.isArray(product.sizes) && product.sizes.length > 0 ? 
                                    product.sizes.map(size => `<span class="badge bg-dark me-1">${size}</span>`).join('') : 
                                    '<span class="text-muted">Sin tallas</span>'
                                }
                            </td>
                            <td>
                                <span class="badge ${product.stock_quantity > 0 ? (product.stock_quantity > 10 ? 'bg-success' : 'bg-warning') : 'bg-danger'}">
                                    ${product.stock_quantity || 0}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                        ${product.status === 'active' ? 'Activo' : 'Inactivo'}
                                    </span>
                                    ${product.is_featured ? '<span class="badge bg-warning">Destacado</span>' : ''}
                                </div>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick="viewProduct('${product.id}')" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="editProduct('${product.id}')" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="deleteProduct('${product.id}', '${product.name.replace(/'/g, '\\\'')}')" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('');
                    
                    // Actualizar información de paginación
                    updateProductsPagination(result.pagination || {});
                    updateProductsStats();
                    
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center p-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ${currentCategoryFilter || currentSearchQuery ? 'No se encontraron productos con los filtros aplicados.' : 'No hay productos registrados.'}
                                    <br>
                                    <button class="btn btn-sm btn-primary mt-2" onclick="showAddProductModal()">
                                        <i class="fas fa-plus me-1"></i>Agregar primer producto
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }
                
            } catch (error) {
                console.error('Error al cargar productos:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center p-4">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error al cargar productos: ${error.message}
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
        
        async function updateProductsStats() {
            try {
                // Obtener estadísticas generales
                const response = await fetch('api/productos-v2.php?stats=true');
                const result = await response.json();
                
                console.log('📊 Estadísticas recibidas:', result);
                
                if (result.success && result.data) {
                    const stats = result.data;
                    document.getElementById('total-products-stat').textContent = stats.total || 0;
                    document.getElementById('active-products-stat').textContent = stats.active || 0;
                    document.getElementById('featured-products-stat').textContent = stats.featured || 0;
                    document.getElementById('categories-with-products-stat').textContent = stats.categoriesWithProducts || 0;
                } else {
                    // Si no hay endpoint de stats, usar valores por defecto
                    document.getElementById('total-products-stat').textContent = '?';
                    document.getElementById('active-products-stat').textContent = '?';
                    document.getElementById('featured-products-stat').textContent = '?';
                    document.getElementById('categories-with-products-stat').textContent = '?';
                }
            } catch (error) {
                console.error('Error al cargar estadísticas:', error);
                // Mostrar ? en caso de error
                document.getElementById('total-products-stat').textContent = '?';
                document.getElementById('active-products-stat').textContent = '?';
                document.getElementById('featured-products-stat').textContent = '?';
                document.getElementById('categories-with-products-stat').textContent = '?';
            }
        }
        
        function updateProductsPagination(pagination) {
            const paginationEl = document.getElementById('products-pagination');
            const infoEl = document.getElementById('products-info');
            
            if (pagination && pagination.totalItems) {
                const start = ((pagination.currentPage - 1) * pagination.itemsPerPage) + 1;
                const end = Math.min(start + pagination.itemsPerPage - 1, pagination.totalItems);
                
                infoEl.textContent = `Mostrando ${start}-${end} de ${pagination.totalItems} productos`;
                
                // Generar paginación
                let paginationHtml = '';
                
                // Botón anterior
                if (pagination.currentPage > 1) {
                    paginationHtml += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadProducts(${pagination.currentPage - 1})">Anterior</a>
                    </li>`;
                }
                
                // Números de página
                for (let i = Math.max(1, pagination.currentPage - 2); i <= Math.min(pagination.totalPages, pagination.currentPage + 2); i++) {
                    paginationHtml += `<li class="page-item ${i === pagination.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="loadProducts(${i})">${i}</a>
                    </li>`;
                }
                
                // Botón siguiente
                if (pagination.currentPage < pagination.totalPages) {
                    paginationHtml += `<li class="page-item">
                        <a class="page-link" href="#" onclick="loadProducts(${pagination.currentPage + 1})">Siguiente</a>
                    </li>`;
                }
                
                paginationEl.innerHTML = paginationHtml;
            } else {
                infoEl.textContent = 'Mostrando 0 de 0 productos';
                paginationEl.innerHTML = '';
            }
        }
        
        async function loadCategoryFilterMenu() {
            const menu = document.getElementById('category-filter-menu');
            
            try {
                const response = await fetch('api/categorias.php');
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    let menuHtml = '<li><a class="dropdown-item" href="#" onclick="filterProductsByCategory(\'\')">Todas las categorías</a></li><li><hr class="dropdown-divider"></li>';
                    
                    result.data.forEach(category => {
                        menuHtml += `<li><a class="dropdown-item" href="#" onclick="filterProductsByCategory('${category.id}')">${category.name}</a></li>`;
                    });
                    
                    menu.innerHTML = menuHtml;
                }
            } catch (error) {
                console.error('Error al cargar menú de categorías:', error);
            }
        }
        
        function filterProductsByCategory(categoryId) {
            currentCategoryFilter = categoryId;
            currentProductsPage = 1;
            loadProducts();
        }
        
        function searchProducts() {
            const searchInput = document.getElementById('products-search');
            currentSearchQuery = searchInput.value.trim();
            currentProductsPage = 1;
            loadProducts();
        }
        
        function changeProductsPerPage() {
            const select = document.getElementById('products-per-page');
            currentProductsPerPage = parseInt(select.value);
            currentProductsPage = 1;
            loadProducts();
        }
        
        function showAddProductModal() {
            // Limpiar formulario completamente
            clearProductForm();
            
            // Establecer valores por defecto
            document.getElementById('productId').value = '';
            document.getElementById('productActive').checked = true;
            
            // Cambiar título
            document.getElementById('productModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Nuevo Producto';
            
            // Cargar categorías en el select
            loadProductCategories();
            
            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        }
        
        async function loadProductCategories() {
            const select = document.getElementById('productCategory');
            select.innerHTML = '<option value="">Seleccionar categoría...</option>';
            
            try {
                const response = await fetch('api/categorias.php');
                const result = await response.json();
                
                if (result.success && result.data.length > 0) {
                    result.data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.name;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar categorías:', error);
                select.innerHTML = '<option value="">Error al cargar categorías</option>';
            }
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('es-CO', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(price || 0);
        }
        
        async function saveProduct() {
            const form = document.getElementById('productForm');
            
            // Validación básica
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Verificar que hay al menos una imagen o video
            const mainImageInput = document.getElementById('productMainImage');
            const mainImageUrlInput = document.getElementById('productMainImageUrl');
            const additionalMediaInput = document.getElementById('productAdditionalMedia');
            const isEdit = document.getElementById('productId').value !== '';
            
            const hasMainImage = mainImageInput.files[0] || mainImageUrlInput.value;
            const hasAdditionalMedia = additionalMediaInput.files && additionalMediaInput.files.length > 0;
            
            if (!isEdit && !hasMainImage && !hasAdditionalMedia) {
                Swal.fire('Error', 'Debes seleccionar al menos una imagen o video para el producto', 'error');
                return;
            }

            try {
                // 1. Subir imágenes primero
                let imageUrls = null;
                if (mainImageInput.files[0] || (document.getElementById('productAdditionalMedia').files && document.getElementById('productAdditionalMedia').files.length > 0)) {
                    imageUrls = await uploadProductImages();
                    if (imageUrls === null) {
                        // Error en la carga de imágenes, ya se mostró el mensaje
                        return;
                    }
                }

                // 2. Preparar datos del producto en el formato correcto para el API
                const productData = {
                    name: document.getElementById('productName').value.trim(),
                    description: document.getElementById('productDescription').value.trim(),
                    category_id: document.getElementById('productCategory').value,
                    price: parseFloat(document.getElementById('productPrice').value) || 0,
                    sale_price: parseFloat(document.getElementById('productOriginalPrice').value) || null,
                    stock_quantity: parseInt(document.getElementById('productStock').value) || 0,
                    sku: document.getElementById('productSku').value.trim(),
                    gender: document.getElementById('productGender').value,
                    sizes: document.getElementById('productSizes').value.trim().split(',').filter(s => s.trim()),
                    colors: document.getElementById('productColors').value.trim().split(',').filter(c => c.trim()),
                    main_image: imageUrls ? imageUrls.mainImage : mainImageUrlInput.value,
                    gallery: imageUrls ? imageUrls.additionalImages : (document.getElementById('productAdditionalMediaUrls').value ? document.getElementById('productAdditionalMediaUrls').value.split(',').filter(img => img.trim()) : []),
                    status: document.getElementById('productActive').checked ? 'active' : 'inactive',
                    is_featured: document.getElementById('productFeatured').checked,
                    discount_percentage: document.getElementById('productOnSale').checked ? 10 : 0 // Valor por defecto si está en oferta
                };

                // Si es edición, agregar el ID
                if (isEdit) {
                    productData.id = document.getElementById('productId').value;
                }

                // 3. Guardar producto en la base de datos
                Swal.fire({
                    title: isEdit ? 'Actualizando producto...' : 'Guardando producto...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const method = isEdit ? 'PUT' : 'POST';
                const url = isEdit ? `api/productos-v2.php?id=${productData.id}` : 'api/productos-v2.php';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(productData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: result.message || (isEdit ? 'Producto actualizado correctamente' : 'Producto creado correctamente'),
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
                    
                    // Limpiar formulario
                    clearProductForm();
                    
                    // Recargar productos
                    loadProducts(currentProductsPage);
                    
                } else {
                    Swal.fire('Error', result.message || 'No se pudo guardar el producto', 'error');
                }
                
            } catch (error) {
                console.error('Error al guardar producto:', error);
                Swal.fire('Error', 'No se pudo guardar el producto', 'error');
            }
        }

        // Función para limpiar el formulario de productos
        function clearProductForm() {
            const form = document.getElementById('productForm');
            form.reset();
            
            // Limpiar vistas previas
            const mainImagePreview = document.getElementById('mainImagePreview');
            if (mainImagePreview) {
                mainImagePreview.style.display = 'none';
            }
            
            const additionalMediaPreview = document.getElementById('additionalMediaPreview');
            if (additionalMediaPreview) {
                additionalMediaPreview.innerHTML = '';
            }
            
            // Limpiar badge de contador
            const additionalMediaCount = document.getElementById('additionalMediaCount');
            if (additionalMediaCount) {
                additionalMediaCount.textContent = '0 archivos';
                additionalMediaCount.className = 'badge bg-info ms-2';
            }
            
            // Limpiar campos ocultos
            const productMainImageUrl = document.getElementById('productMainImageUrl');
            if (productMainImageUrl) {
                productMainImageUrl.value = '';
            }
            
            const productAdditionalMediaUrls = document.getElementById('productAdditionalMediaUrls');
            if (productAdditionalMediaUrls) {
                productAdditionalMediaUrls.value = '';
            }
        }
        
        async function editProduct(productId) {
            try {
                const response = await fetch(`api/productos-v2.php?id=${productId}`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const product = result.data;
                    
                    // Limpiar formulario primero
                    clearProductForm();
                    
                    // Llenar formulario básico
                    document.getElementById('productId').value = product.id;
                    document.getElementById('productName').value = product.name || '';
                    document.getElementById('productDescription').value = product.description || '';
                    document.getElementById('productCategory').value = product.category_id || '';
                    document.getElementById('productPrice').value = product.price || '';
                    document.getElementById('productOriginalPrice').value = product.original_price || '';
                    document.getElementById('productStock').value = product.stock || 0;
                    document.getElementById('productSku').value = product.sku || '';
                    document.getElementById('productGender').value = product.gender || '';
                    document.getElementById('productSizes').value = product.sizes || '';
                    document.getElementById('productColors').value = product.colors || '';
                    document.getElementById('productActive').checked = product.status === 'active';
                    document.getElementById('productFeatured').checked = product.is_featured == 1;
                    document.getElementById('productOnSale').checked = product.on_sale == 1;
                    
                    // Manejar imagen principal existente
                    if (product.main_image) {
                        document.getElementById('productMainImageUrl').value = product.main_image;
                        const mainPreview = document.getElementById('mainImagePreview');
                        const previewImg = mainPreview.querySelector('.preview-img');
                        const previewVideo = mainPreview.querySelector('.preview-video');
                        const typeIndicator = mainPreview.querySelector('#mainMediaTypeIndicator');
                        
                        // Detectar si es video
                        const isVideo = /\.(mp4|mov|avi|webm)$/i.test(product.main_image);
                        
                        if (isVideo) {
                            previewImg.style.display = 'none';
                            previewVideo.src = product.main_image;
                            previewVideo.style.display = 'block';
                            if (typeIndicator) {
                                typeIndicator.textContent = 'VIDEO';
                                typeIndicator.className = 'media-type-indicator video-indicator';
                                typeIndicator.style.display = 'block';
                            }
                        } else {
                            previewVideo.style.display = 'none';
                            previewImg.src = product.main_image;
                            previewImg.style.display = 'block';
                            if (typeIndicator) {
                                typeIndicator.textContent = 'IMG';
                                typeIndicator.className = 'media-type-indicator image-indicator';
                                typeIndicator.style.display = 'block';
                            }
                        }
                        
                        mainPreview.style.display = 'block';
                    }
                    
                    // Manejar medios adicionales existentes (imágenes y videos)
                    if (product.images) {
                        const additionalMedia = product.images.split('\n').filter(img => img.trim() !== '');
                        if (additionalMedia.length > 0) {
                            document.getElementById('productAdditionalMediaUrls').value = additionalMedia.join(',');
                            const additionalPreview = document.getElementById('additionalMediaPreview');
                            if (additionalPreview) {
                                additionalPreview.innerHTML = '';
                                
                                additionalMedia.forEach((mediaUrl, index) => {
                                    const isVideo = mediaUrl.toLowerCase().match(/\.(mp4|mov|avi|webm)$/);
                                    const previewItem = document.createElement('div');
                                    previewItem.className = 'media-preview-item';
                                    
                                    let mediaElement = '';
                                    let typeIndicator = '';
                                    
                                    if (isVideo) {
                                        mediaElement = `<video src="${mediaUrl}" class="preview-video" muted></video>`;
                                        typeIndicator = `<div class="media-type-indicator video-indicator">VIDEO</div>`;
                                    } else {
                                        mediaElement = `<img src="${mediaUrl}" alt="Media ${index + 1}" class="preview-img">`;
                                        typeIndicator = `<div class="media-type-indicator image-indicator">IMG</div>`;
                                    }
                                    
                                    previewItem.innerHTML = `
                                        <div class="image-order-indicator">${index + 1}</div>
                                        ${mediaElement}
                                        ${typeIndicator}
                                        <div class="preview-controls">
                                            <button type="button" class="remove-image" onclick="removeExistingAdditionalMedia(${index})" title="Eliminar archivo">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    `;
                                    additionalPreview.appendChild(previewItem);
                                });
                                
                                // Actualizar contador
                                const additionalMediaCount = document.getElementById('additionalMediaCount');
                                if (additionalMediaCount) {
                                    const imageCount = additionalMedia.filter(url => !url.toLowerCase().match(/\.(mp4|mov|avi|webm)$/)).length;
                                    const videoCount = additionalMedia.filter(url => url.toLowerCase().match(/\.(mp4|mov|avi|webm)$/)).length;
                                    let countText = '';
                                    
                                    if (imageCount > 0 && videoCount > 0) {
                                        countText = `${imageCount} imagen${imageCount !== 1 ? 'es' : ''} y ${videoCount} video${videoCount !== 1 ? 's' : ''}`;
                                    } else if (imageCount > 0) {
                                        countText = `${imageCount} imagen${imageCount !== 1 ? 'es' : ''}`;
                                    } else if (videoCount > 0) {
                                        countText = `${videoCount} video${videoCount !== 1 ? 's' : ''}`;
                                    }
                                    
                                    additionalMediaCount.textContent = countText;
                                    additionalMediaCount.className = 'badge bg-success ms-2';
                                }
                            }
                        }
                    }
                    
                    // Cambiar título
                    document.getElementById('productModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Producto';
                    
                    // Cargar categorías
                    await loadProductCategories();
                    document.getElementById('productCategory').value = product.category_id || '';
                    
                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('productModal'));
                    modal.show();
                } else {
                    Swal.fire('Error', result.message || 'No se pudo cargar el producto', 'error');
                }
            } catch (error) {
                console.error('Error al cargar producto:', error);
                Swal.fire('Error', 'No se pudo cargar el producto', 'error');
            }
        }

        // Función para remover imagen adicional existente
        function removeExistingAdditionalImage(index) {
            const urlsInput = document.getElementById('productAdditionalImagesUrls');
            const urls = urlsInput.value.split(',').filter(url => url.trim() !== '');
            urls.splice(index, 1);
            urlsInput.value = urls.join(',');
            
            // Recrear preview
            const additionalPreview = document.getElementById('additionalImagesPreview');
            additionalPreview.innerHTML = '';
            
            urls.forEach((imageUrl, i) => {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = `
                    <img src="${imageUrl}" alt="Imagen adicional ${i + 1}" class="preview-img">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-image" onclick="removeExistingAdditionalImage(${i})">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                additionalPreview.appendChild(previewItem);
            });
        }
        
        // Nueva función para eliminar medios existentes (imágenes y videos)
        function removeExistingAdditionalMedia(index) {
            const urlsInput = document.getElementById('productAdditionalMediaUrls');
            const urls = urlsInput.value.split(',').filter(url => url.trim() !== '');
            urls.splice(index, 1);
            urlsInput.value = urls.join(',');
            
            // Recrear preview
            const additionalPreview = document.getElementById('additionalMediaPreview');
            if (additionalPreview) {
                additionalPreview.innerHTML = '';
                
                urls.forEach((mediaUrl, i) => {
                    const isVideo = mediaUrl.toLowerCase().match(/\.(mp4|mov|avi|webm)$/);
                    const previewItem = document.createElement('div');
                    previewItem.className = 'media-preview-item';
                    
                    let mediaElement = '';
                    let typeIndicator = '';
                    
                    if (isVideo) {
                        mediaElement = `<video src="${mediaUrl}" class="preview-video" muted></video>`;
                        typeIndicator = `<div class="media-type-indicator video-indicator">VIDEO</div>`;
                    } else {
                        mediaElement = `<img src="${mediaUrl}" alt="Media ${i + 1}" class="preview-img">`;
                        typeIndicator = `<div class="media-type-indicator image-indicator">IMG</div>`;
                    }
                    
                    previewItem.innerHTML = `
                        <div class="image-order-indicator">${i + 1}</div>
                        ${mediaElement}
                        ${typeIndicator}
                        <div class="preview-controls">
                            <button type="button" class="remove-image" onclick="removeExistingAdditionalMedia(${i})" title="Eliminar archivo">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    additionalPreview.appendChild(previewItem);
                });
                
                // Actualizar contador
                const additionalMediaCount = document.getElementById('additionalMediaCount');
                if (additionalMediaCount) {
                    const imageCount = urls.filter(url => !url.toLowerCase().match(/\.(mp4|mov|avi|webm)$/)).length;
                    const videoCount = urls.filter(url => url.toLowerCase().match(/\.(mp4|mov|avi|webm)$/)).length;
                    let countText = '';
                    
                    if (imageCount > 0 && videoCount > 0) {
                        countText = `${imageCount} imagen${imageCount !== 1 ? 'es' : ''} y ${videoCount} video${videoCount !== 1 ? 's' : ''}`;
                    } else if (imageCount > 0) {
                        countText = `${imageCount} imagen${imageCount !== 1 ? 'es' : ''}`;
                    } else if (videoCount > 0) {
                        countText = `${videoCount} video${videoCount !== 1 ? 's' : ''}`;
                    } else {
                        countText = '0 archivos';
                    }
                    
                    additionalMediaCount.textContent = countText;
                    additionalMediaCount.className = urls.length > 0 ? 'badge bg-success ms-2' : 'badge bg-info ms-2';
                }
            }
        }
        
        async function deleteProduct(productId, productName) {
            const result = await Swal.fire({
                title: '¿Eliminar producto?',
                text: `¿Estás seguro de eliminar "${productName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            
            if (result.isConfirmed) {
                try {
                    const response = await fetch(`api/productos-v2.php?id=${productId}`, {
                        method: 'DELETE'
                    });
                    
                    const deleteResult = await response.json();
                    
                    if (deleteResult.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminado!',
                            text: deleteResult.message || 'Producto eliminado correctamente',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        
                        loadProducts(currentProductsPage);
                    } else {
                        Swal.fire('Error', deleteResult.message || 'No se pudo eliminar el producto', 'error');
                    }
                    
                } catch (error) {
                    console.error('Error al eliminar producto:', error);
                    Swal.fire('Error', 'No se pudo eliminar el producto', 'error');
                }
            }
        }
        
        function viewProduct(productId) {
            // Abrir el producto en una nueva ventana/tab
            window.open(`index.html?product=${productId}`, '_blank');
        }
        
        function exportProducts() {
            Swal.fire({
                title: 'Exportar Productos',
                text: 'Esta funcionalidad estará disponible próximamente',
                icon: 'info'
            });
        }

        // === FUNCIONES PARA MANEJO DE IMÁGENES ===

        // Solucionar problema de accesibilidad del modal
        document.addEventListener('DOMContentLoaded', function() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                modal.addEventListener('shown.bs.modal', function() {
                    // Remover aria-hidden cuando el modal se muestra
                    this.removeAttribute('aria-hidden');
                });
                modal.addEventListener('hidden.bs.modal', function() {
                    // Restaurar aria-hidden cuando el modal se oculta
                    this.setAttribute('aria-hidden', 'true');
                });
            });
        });

        // Vista previa de imagen individual
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const previewImg = preview.querySelector('.preview-img');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }

        // Nueva función para vista previa de imagen o video principal
        function previewMainMedia(input, previewId) {
            const preview = document.getElementById(previewId);
            const previewImg = preview.querySelector('.preview-img');
            const previewVideo = preview.querySelector('.preview-video');
            const typeIndicator = preview.querySelector('#mainMediaTypeIndicator');
            
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const isImage = file.type.startsWith('image/');
                const isVideo = file.type.startsWith('video/');
                
                if (!isImage && !isVideo) {
                    alert('Por favor, selecciona solo archivos de imagen o video.');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Ocultar ambos elementos primero
                    previewImg.style.display = 'none';
                    previewVideo.style.display = 'none';
                    typeIndicator.style.display = 'none';
                    
                    if (isImage) {
                        previewImg.src = e.target.result;
                        previewImg.style.display = 'block';
                        typeIndicator.textContent = 'IMG';
                        typeIndicator.className = 'media-type-indicator image-indicator';
                    } else if (isVideo) {
                        previewVideo.src = e.target.result;
                        previewVideo.style.display = 'block';
                        typeIndicator.textContent = 'VIDEO';
                        typeIndicator.className = 'media-type-indicator video-indicator';
                    }
                    
                    typeIndicator.style.display = 'block';
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        }

        // =================== FUNCIONES MEJORADAS PARA MÚLTIPLES IMÁGENES ===================
        
        // Función mejorada para preview de múltiples imágenes con drag & drop
        function previewMultipleImagesEnhanced(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            const countBadge = document.getElementById('additionalImagesCount');
            
            if (!previewContainer) return;
            
            // Limpiar preview actual
            previewContainer.innerHTML = '';
            
            const files = Array.from(input.files);
            
            // Actualizar contador
            if (countBadge) {
                countBadge.textContent = `${files.length} imagen${files.length !== 1 ? 'es' : ''}`;
                countBadge.className = files.length > 0 ? 'badge bg-success ms-2' : 'badge bg-info ms-2';
            }
            
            if (files.length === 0) return;
            
            console.log(`📸 Procesando ${files.length} imágenes adicionales...`);
            
            files.forEach((file, index) => {
                // Validar archivo
                if (!file.type.startsWith('image/')) {
                    console.warn('⚠️ Archivo no válido:', file.name);
                    return;
                }
                
                // Eliminada validación de tamaño para permitir imágenes de cualquier tamaño
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'preview-item';
                    previewItem.draggable = true;
                    previewItem.dataset.index = index;
                    
                    previewItem.innerHTML = `
                        <div class="image-order-indicator">${index + 1}</div>
                        <img src="${e.target.result}" alt="Vista previa ${index + 1}" class="preview-img">
                        <div class="preview-controls">
                            <button type="button" class="move-image" title="Mover imagen">
                                <i class="fas fa-arrows-alt"></i>
                            </button>
                            <button type="button" class="remove-image" onclick="removeAdditionalImageEnhanced(${index})" title="Eliminar imagen">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="image-info">
                            <div><strong>${file.name}</strong></div>
                            <div>${formatFileSize(file.size)}</div>
                        </div>
                    `;
                    
                    // Agregar eventos de drag & drop
                    setupDragAndDrop(previewItem, previewContainer);
                    
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        }
        
        // Nueva función para preview de múltiples medios (imágenes y videos)
        function previewMultipleMediaEnhanced(input, previewId) {
            const previewContainer = document.getElementById(previewId);
            const countBadge = document.getElementById('additionalMediaCount');
            
            if (!previewContainer) return;
            
            // Limpiar preview actual
            previewContainer.innerHTML = '';
            
            const files = Array.from(input.files);
            
            // Actualizar contador
            if (countBadge) {
                const imageCount = files.filter(f => f.type.startsWith('image/')).length;
                const videoCount = files.filter(f => f.type.startsWith('video/')).length;
                let countText = '';
                
                if (imageCount > 0 && videoCount > 0) {
                    countText = `${imageCount} imagen${imageCount !== 1 ? 'es' : ''} y ${videoCount} video${videoCount !== 1 ? 's' : ''}`;
                } else if (imageCount > 0) {
                    countText = `${imageCount} imagen${imageCount !== 1 ? 'es' : ''}`;
                } else if (videoCount > 0) {
                    countText = `${videoCount} video${videoCount !== 1 ? 's' : ''}`;
                } else {
                    countText = '0 archivos';
                }
                
                countBadge.textContent = countText;
                countBadge.className = files.length > 0 ? 'badge bg-success ms-2' : 'badge bg-info ms-2';
            }
            
            if (files.length === 0) return;
            
            console.log(`📸 Procesando ${files.length} archivos multimedia...`);
            
            files.forEach((file, index) => {
                // Validar archivo
                const isImage = file.type.startsWith('image/');
                const isVideo = file.type.startsWith('video/');
                
                if (!isImage && !isVideo) {
                    console.warn('⚠️ Archivo no válido:', file.name);
                    return;
                }
                
                // Eliminada validación de tamaño - permite archivos de cualquier tamaño
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewItem = document.createElement('div');
                    previewItem.className = 'media-preview-item';
                    previewItem.draggable = true;
                    previewItem.dataset.index = index;
                    previewItem.dataset.type = isVideo ? 'video' : 'image';
                    
                    let mediaElement = '';
                    let typeIndicator = '';
                    
                    if (isImage) {
                        mediaElement = `<img src="${e.target.result}" alt="Vista previa ${index + 1}" class="preview-img">`;
                        typeIndicator = `<div class="media-type-indicator image-indicator">IMG</div>`;
                    } else if (isVideo) {
                        mediaElement = `<video src="${e.target.result}" class="preview-video" muted></video>`;
                        typeIndicator = `<div class="media-type-indicator video-indicator">VIDEO</div>`;
                    }
                    
                    previewItem.innerHTML = `
                        <div class="image-order-indicator">${index + 1}</div>
                        ${mediaElement}
                        ${typeIndicator}
                        <div class="preview-controls">
                            <button type="button" class="move-image" title="Mover archivo">
                                <i class="fas fa-arrows-alt"></i>
                            </button>
                            <button type="button" class="remove-image" onclick="removeAdditionalMediaEnhanced(${index})" title="Eliminar archivo">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="image-info">
                            <div><strong>${file.name}</strong></div>
                            <div>${formatFileSize(file.size)}</div>
                        </div>
                    `;
                    
                    // Agregar eventos de drag & drop
                    setupDragAndDrop(previewItem, previewContainer);
                    
                    previewContainer.appendChild(previewItem);
                };
                reader.readAsDataURL(file);
            });
        }
        
        // Función para eliminar archivo multimedia del preview
        function removeAdditionalMediaEnhanced(index) {
            const input = document.getElementById('productAdditionalMedia');
            const previewContainer = document.getElementById('additionalMediaPreview');
            
            if (!input || !previewContainer) return;
            
            const dt = new DataTransfer();
            const files = Array.from(input.files);
            
            // Agregar todos los archivos excepto el que se quiere eliminar
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            input.files = dt.files;
            
            // Actualizar preview
            previewMultipleMediaEnhanced(input, 'additionalMediaPreview');
            
            console.log(`🗑️ Archivo ${index + 1} eliminado del preview`);
        }
        
        // Función para manejar drag & drop en el área de subida
        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            e.target.closest('.upload-area').classList.add('drag-over');
        }
        
        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!e.target.closest('.upload-area').contains(e.relatedTarget)) {
                e.target.closest('.upload-area').classList.remove('drag-over');
            }
        }
        
        function handleDrop(e, inputId) {
            e.preventDefault();
            e.stopPropagation();
            
            const uploadArea = e.target.closest('.upload-area');
            uploadArea.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            const input = document.getElementById(inputId);
            
            if (files.length > 0) {
                // Crear un DataTransfer para asignar los archivos al input
                const dt = new DataTransfer();
                Array.from(files).forEach(file => {
                    // Para el nuevo input de medios, aceptar tanto imágenes como videos
                    if (inputId === 'productAdditionalMedia') {
                        if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                            dt.items.add(file);
                        }
                    } else {
                        // Para otros inputs, solo imágenes (compatibilidad hacia atrás)
                        if (file.type.startsWith('image/')) {
                            dt.items.add(file);
                        }
                    }
                });
                
                input.files = dt.files;
                
                // Trigger el preview correspondiente
                if (inputId === 'productAdditionalMedia') {
                    previewMultipleMediaEnhanced(input, 'additionalMediaPreview');
                }
                
                console.log(`📁 ${files.length} archivos arrastrados y procesados`);
            }
        }
        
        // Función para configurar drag & drop en elementos de preview
        function setupDragAndDrop(element, container) {
            element.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('text/plain', this.dataset.index);
                this.classList.add('dragging');
            });
            
            element.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });
            
            element.addEventListener('dragover', function(e) {
                e.preventDefault();
                const dragging = container.querySelector('.dragging');
                const afterElement = getDragAfterElement(container, e.clientY);
                
                if (afterElement == null) {
                    container.appendChild(dragging);
                } else {
                    container.insertBefore(dragging, afterElement);
                }
            });
        }
        
        // Función auxiliar para drag & drop
        function getDragAfterElement(container, y) {
            const draggableElements = [...container.querySelectorAll('.preview-item:not(.dragging)')];
            
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }
        
        // Función para formatear tamaño de archivo
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Función mejorada para remover imagen adicional (redirige a la nueva función de medios)
        function removeAdditionalImageEnhanced(index) {
            // Redirigir a la nueva función que maneja medios
            removeAdditionalMediaEnhanced(index);
        }
        
        // Función original de preview múltiple (redirige a la nueva función de medios)
        function previewMultipleImages(input, previewId) {
            // Redirigir a la nueva función que maneja medios
            if (previewId === 'additionalImagesPreview') {
                previewMultipleMediaEnhanced(input, 'additionalMediaPreview');
            } else {
                previewMultipleMediaEnhanced(input, previewId);
            }
        }

        // Remover vista previa de imagen individual
        function removeImagePreview(inputId, previewId) {
            document.getElementById(inputId).value = '';
            document.getElementById(previewId).style.display = 'none';
            document.getElementById(inputId + 'Url').value = '';
        }

        // Remover imagen adicional
        function removeAdditionalImage(index) {
            const input = document.getElementById('productAdditionalImages');
            const dt = new DataTransfer();
            
            // Recrear el FileList sin el archivo eliminado
            Array.from(input.files).forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            input.files = dt.files;
            previewMultipleImages(input, 'additionalImagesPreview');
        }

        // Subir imagen individual
        async function uploadImage(file) {
            const formData = new FormData();
            formData.append('image', file);

            try {
                const response = await fetch('api/upload-image.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    return result.data;
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error al subir imagen:', error);
                throw error;
            }
        }

        // Subir todas las imágenes del producto
        async function uploadProductImages() {
            const mainImageInput = document.getElementById('productMainImage');
            const additionalMediaInput = document.getElementById('productAdditionalMedia');
            
            let mainImageUrl = '';
            let additionalImageUrls = [];

            // Subir imagen principal si existe
            if (mainImageInput.files && mainImageInput.files[0]) {
                try {
                    Swal.fire({
                        title: 'Subiendo imagen principal...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const mainImageData = await uploadImage(mainImageInput.files[0]);
                    mainImageUrl = mainImageData.url;
                    document.getElementById('productMainImageUrl').value = mainImageUrl;
                } catch (error) {
                    Swal.fire('Error', 'Error al subir la imagen principal: ' + error.message, 'error');
                    return null;
                }
            }

            // Subir medios adicionales (imágenes y videos) si existen
            if (additionalMediaInput.files && additionalMediaInput.files.length > 0) {
                try {
                    Swal.fire({
                        title: 'Subiendo archivos multimedia...',
                        html: '<div id="uploadProgress">Procesando archivo 1 de ' + additionalMediaInput.files.length + '</div>',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    for (let i = 0; i < additionalMediaInput.files.length; i++) {
                        const file = additionalMediaInput.files[i];
                        const fileType = file.type.startsWith('video/') ? 'video' : 'imagen';
                        document.getElementById('uploadProgress').innerHTML = `Procesando ${fileType} ${i + 1} de ${additionalMediaInput.files.length}`;
                        
                        const mediaData = await uploadImage(file); // La función uploadImage también maneja videos
                        additionalImageUrls.push(mediaData.url);
                    }

                    document.getElementById('productAdditionalMediaUrls').value = additionalImageUrls.join(',');
                } catch (error) {
                    Swal.fire('Error', 'Error al subir archivos multimedia: ' + error.message, 'error');
                    return null;
                }
            }

            // Si no hay imagen principal pero sí hay medios adicionales, usar el primer medio como principal
            if (!mainImageUrl && additionalImageUrls.length > 0) {
                mainImageUrl = additionalImageUrls[0];
                document.getElementById('productMainImageUrl').value = mainImageUrl;
                console.log('📸 Usando el primer archivo multimedia como imagen principal:', mainImageUrl);
            }

            return {
                mainImage: mainImageUrl,
                additionalImages: additionalImageUrls
            };
        }
        
        // Función para generar HTML de carrusel para la galería de productos
        function generateProductCarousel(mediaUrls, productName = 'Producto') {
            if (!mediaUrls || mediaUrls.length === 0) {
                return '<div class="no-media-message">No hay archivos multimedia para mostrar</div>';
            }
            
            let carouselHTML = `
                <div class="product-carousel">
                    <div class="carousel-container">
            `;
            
            // Generar slides
            mediaUrls.forEach((url, index) => {
                const isVideo = url.toLowerCase().match(/\.(mp4|mov|avi|webm)$/);
                const activeClass = index === 0 ? 'active' : '';
                
                if (isVideo) {
                    carouselHTML += `
                        <div class="carousel-slide ${activeClass}">
                            <video autoplay muted loop playsinline>
                                <source src="${url}" type="video/mp4">
                                Tu navegador no soporta videos HTML5.
                            </video>
                        </div>
                    `;
                } else {
                    carouselHTML += `
                        <div class="carousel-slide ${activeClass}">
                            <img src="${url}" alt="${productName} - Imagen ${index + 1}" loading="lazy">
                        </div>
                    `;
                }
            });
            
            // Agregar controles solo si hay más de un elemento
            if (mediaUrls.length > 1) {
                carouselHTML += `
                        <button class="carousel-controls carousel-prev" onclick="changeSlide(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="carousel-controls carousel-next" onclick="changeSlide(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                `;
                
                // Agregar indicadores
                carouselHTML += '<div class="carousel-indicators">';
                mediaUrls.forEach((_, index) => {
                    const activeClass = index === 0 ? 'active' : '';
                    carouselHTML += `<div class="carousel-indicator ${activeClass}" onclick="goToSlide(${index})"></div>`;
                });
                carouselHTML += '</div>';
            }
            
            carouselHTML += `
                    </div>
                </div>
            `;
            
            return carouselHTML;
        }
        
        // Variables globales para el carrusel
        let currentSlide = 0;
        let totalSlides = 0;
        
        // Función para cambiar slide
        function changeSlide(direction) {
            const slides = document.querySelectorAll('.carousel-slide');
            const indicators = document.querySelectorAll('.carousel-indicator');
            
            if (slides.length === 0) return;
            
            // Remover clase active del slide actual
            slides[currentSlide].classList.remove('active');
            if (indicators[currentSlide]) {
                indicators[currentSlide].classList.remove('active');
            }
            
            // Calcular nuevo slide
            currentSlide += direction;
            
            if (currentSlide >= slides.length) {
                currentSlide = 0;
            } else if (currentSlide < 0) {
                currentSlide = slides.length - 1;
            }
            
            // Activar nuevo slide
            slides[currentSlide].classList.add('active');
            if (indicators[currentSlide]) {
                indicators[currentSlide].classList.add('active');
            }
        }
        
        // Función para ir a un slide específico
        function goToSlide(slideIndex) {
            const slides = document.querySelectorAll('.carousel-slide');
            const indicators = document.querySelectorAll('.carousel-indicator');
            
            if (slides.length === 0 || slideIndex < 0 || slideIndex >= slides.length) return;
            
            // Remover clase active del slide actual
            slides[currentSlide].classList.remove('active');
            if (indicators[currentSlide]) {
                indicators[currentSlide].classList.remove('active');
            }
            
            // Activar nuevo slide
            currentSlide = slideIndex;
            slides[currentSlide].classList.add('active');
            if (indicators[currentSlide]) {
                indicators[currentSlide].classList.add('active');
            }
        }
        
        // === GESTIÓN DE PEDIDOS ===
        
        let ordersRefreshInterval;
        let currentOrdersPage = 1;
        let currentOrdersPerPage = 25;
        let currentOrderStatusFilter = '';
        let currentOrderDateFilter = '';
        let currentOrderSearchQuery = '';
        
        // Variable global para almacenar todos los pedidos
        window.allOrders = [];
        
        // Cargar pedidos desde la API
        async function loadOrders(page = 1) {
            try {
                const tbody = document.getElementById('orders-tbody');
                currentOrdersPage = page;
                
                // Mostrar loading
                tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center p-4">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="spinner-border text-primary me-2" role="status"></div>
                                <span>Cargando pedidos...</span>
                            </div>
                        </td>
                    </tr>
                `;
                
                // Construir URL con filtros - USANDO NUEVO SISTEMA MERCADOPAGO
                let url = `get-pedidos.php?page=${page}&limit=${currentOrdersPerPage}`;
                if (currentOrderStatusFilter) {
                    // Mapear estados del panel a estados de MercadoPago
                    const statusMap = {
                        'pendiente': 'pending',
                        'aprobado': 'approved', 
                        'rechazado': 'rejected'
                    };
                    const mappedStatus = statusMap[currentOrderStatusFilter] || currentOrderStatusFilter;
                    url += `&estado=${mappedStatus}`;
                }
                if (currentOrderDateFilter) {
                    url += `&fecha=${currentOrderDateFilter}`;
                }
                if (currentOrderSearchQuery) {
                    url += `&buscar=${encodeURIComponent(currentOrderSearchQuery)}`;
                }
                
                console.log('🔄 Cargando pedidos desde:', url);
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                console.log('📦 Datos recibidos:', result);
                
                if (result.success && result.pedidos) {
                    console.log('✅ Procesando', result.pedidos.length, 'pedidos');
                    
                    // Guardar pedidos en variable global
                    window.allOrders = result.pedidos;
                    
                    renderOrdersMercadoPago(result.pedidos);
                    updateOrdersStatsMercadoPago(result.pedidos);
                    updateLastRefreshTime();
                } else {
                    showEmptyOrdersMessage();
                    console.error('❌ Error cargando pedidos:', result.message);
                }
                
            } catch (error) {
                console.error('❌ Error de red cargando pedidos:', error);
                showEmptyOrdersMessage();
            }
        }
        
        // Renderizar tabla de pedidos
        function renderOrders(orders) {
            try {
                console.log('🎨 Renderizando pedidos:', orders);
                
                const tbody = document.getElementById('orders-tbody');
                const ordersCount = document.getElementById('orders-count');
                const ordersInfo = document.getElementById('orders-info');
                
                if (!Array.isArray(orders) || orders.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                                    <h5>No hay pedidos</h5>
                                    <p>Los pedidos aparecerán aquí cuando los clientes realicen compras.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    ordersCount.textContent = '0 pedidos';
                    ordersInfo.textContent = 'No hay pedidos para mostrar';
                    return;
                }
                
                tbody.innerHTML = orders.map((order, index) => {
                    console.log(`🔍 Procesando pedido ${index + 1}:`, order);
                    
                    // Los productos ya vienen parseados desde la API
                    let productos = [];
                    try {
                        if (Array.isArray(order.productos)) {
                            productos = order.productos;
                        } else if (typeof order.productos === 'string' && order.productos.trim() !== '') {
                            productos = JSON.parse(order.productos);
                        } else if (order.productos && typeof order.productos === 'object') {
                            // Si es un objeto, lo convertimos a array
                            productos = [order.productos];
                        }
                    } catch (e) {
                        console.warn('⚠️ Error parseando productos del pedido', order.id, ':', e);
                        productos = [];
                    }
                    
                    const productsList = productos.length > 0 ? productos.map(p => 
                        `${p.nombre || p.name || 'Producto'} (${p.cantidad || p.quantity || 1}x)`
                    ).join(', ') : 'Sin productos';
                
                const statusBadge = getOrderStatusBadge(order.estado);
                const paymentMethod = getPaymentMethodBadge(order.metodo_pago);
                
                return `
                    <tr>
                        <td class="fw-bold text-primary">#${order.id}</td>
                        <td>
                            <div>
                                <strong>${order.nombre_completo}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-envelope me-1"></i>${order.email}
                                </small>
                                ${order.telefono ? `<br><small class="text-muted"><i class="fas fa-phone me-1"></i>${order.telefono}</small>` : ''}
                            </div>
                        </td>
                        <td>
                            <small class="text-muted" title="${productsList}">
                                ${productsList.length > 40 ? productsList.substring(0, 40) + '...' : productsList}
                                <br>
                                <span class="badge bg-secondary">${productos.length} item${productos.length !== 1 ? 's' : ''}</span>
                            </small>
                        </td>
                        <td class="text-success fw-bold">$${formatPrice(order.subtotal)}</td>
                        <td class="text-info">$${formatPrice(order.costo_envio)}</td>
                        <td class="text-success fw-bold">$${formatPrice(order.total)}</td>
                        <td>${paymentMethod}</td>
                        <td>${getReceiptColumn(order)}</td>
                        <td>
                            <small>
                                ${new Date(order.fecha_pedido).toLocaleDateString('es-ES')}
                                <br>
                                <span class="text-muted">
                                    ${new Date(order.fecha_pedido).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'})}
                                </span>
                            </small>
                        </td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" onclick="viewOrderDetails(${order.id})" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                ${order.has_receipt ? `
                                <button class="btn btn-outline-success" onclick="viewReceipt('${order.id}')" title="Ver comprobante">
                                    <i class="fas fa-image"></i>
                                </button>
                                ` : ''}
                                
                                <!-- Dropdown para estados de pago -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-warning dropdown-toggle" data-bs-toggle="dropdown" title="Cambiar estado de pago">
                                        <i class="fas fa-credit-card"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'pendiente')">
                                            <i class="fas fa-clock text-warning me-2"></i>Pendiente
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'aprobado')">
                                            <i class="fas fa-check text-success me-2"></i>Aprobado
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'rechazado')">
                                            <i class="fas fa-times text-danger me-2"></i>Rechazado
                                        </a></li>
                                    </ul>
                                </div>
                                
                                <!-- Dropdown para estados de envío -->
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-info dropdown-toggle" data-bs-toggle="dropdown" title="Cambiar estado de envío">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'pendiente')">
                                            <i class="fas fa-clock text-secondary me-2"></i>Pendiente
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'en_proceso')">
                                            <i class="fas fa-cog text-warning me-2"></i>En Proceso
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'enviado')">
                                            <i class="fas fa-shipping-fast text-info me-2"></i>Enviado
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateOrderStatus(${order.id}, 'entregado')">
                                            <i class="fas fa-check-circle text-success me-2"></i>Entregado
                                        </a></li>
                                    </ul>
                                </div>
                                
                                <button class="btn btn-outline-secondary" onclick="printOrder(${order.id})" title="Imprimir">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
            
            // Actualizar contadores
            ordersCount.textContent = `${orders.length} pedido${orders.length !== 1 ? 's' : ''}`;
            ordersInfo.textContent = `Mostrando ${orders.length} de ${orders.length} pedidos`;
            
            } catch (error) {
                console.error('❌ Error en renderOrders:', error);
                const tbody = document.getElementById('orders-tbody');
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <div class="text-danger">
                                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                <h5>Error renderizando pedidos</h5>
                                <p>Hubo un problema mostrando los pedidos. Error: ${error.message}</p>
                                <button class="btn btn-outline-primary" onclick="loadOrders()">
                                    <i class="fas fa-refresh me-2"></i>Reintentar
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            }
        }
        
        // Obtener badge de estado
        function getOrderStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pendiente</span>',
                'processing': '<span class="badge bg-info"><i class="fas fa-cog me-1"></i>En Proceso</span>',
                'completed': '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Completado</span>',
                'aprobado': '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Aprobado</span>',
                'pendiente': '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Pendiente</span>',
                'rechazado': '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Rechazado</span>',
                'cancelled': '<span class="badge bg-danger"><i class="fas fa-times me-1"></i>Cancelado</span>'
            };
            return badges[status] || '<span class="badge bg-secondary"><i class="fas fa-question me-1"></i>Desconocido</span>';
        }
        
        // Obtener badge de método de pago
        function getPaymentMethodBadge(method) {
            const badges = {
                'tarjeta_credito': '<span class="badge bg-primary"><i class="fas fa-credit-card me-1"></i>Tarjeta</span>',
                'mercadopago': '<span class="badge bg-warning text-dark"><i class="fas fa-money-bill me-1"></i>MercadoPago</span>',
                'transferencia': '<span class="badge bg-info"><i class="fas fa-university me-1"></i>Transferencia</span>',
                'efectivo': '<span class="badge bg-success"><i class="fas fa-money-bill-wave me-1"></i>Efectivo</span>',
                'nequi_daviplata': '<span class="badge bg-success"><i class="fas fa-mobile-alt me-1"></i>Nequi/Daviplata</span>'
            };
            return badges[method] || '<span class="badge bg-secondary">' + (method || 'N/A') + '</span>';
        }
        
        // Obtener columna de comprobante
        function getReceiptColumn(order) {
            if (order.metodo_pago === 'nequi_daviplata') {
                return `
                    <div class="text-center">
                        <button class="btn btn-sm btn-outline-primary" onclick="viewReceipt('${order.id}')" title="Ver comprobante">
                            <i class="fas fa-image me-1"></i>Ver
                        </button>
                    </div>
                `;
            }
            return '<div class="text-center text-muted"><small>N/A</small></div>';
        }
        
        // ========= NUEVAS FUNCIONES PARA DATOS DE MERCADOPAGO =========
        
        // Renderizar tabla de pedidos con datos de MercadoPago
        function renderOrdersMercadoPago(orders) {
            try {
                console.log('🎨 Renderizando pedidos MercadoPago:', orders);
                
                const tbody = document.getElementById('orders-tbody');
                const ordersCount = document.getElementById('orders-count');
                const ordersInfo = document.getElementById('orders-info');
                
                if (!Array.isArray(orders) || orders.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 opacity-50"></i>
                                    <h5>No hay pedidos</h5>
                                    <p>Los pedidos de MercadoPago aparecerán aquí automáticamente.</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    ordersCount.textContent = '0 pedidos';
                    ordersInfo.textContent = 'No hay pedidos para mostrar';
                    return;
                }
                
                tbody.innerHTML = orders.map((order, index) => {
                    console.log(`🔍 Procesando pedido MercadoPago ${index + 1}:`, order);
                    
                    const statusBadge = getMercadoPagoStatusBadge(order.status);
                    const paymentMethod = getMercadoPagoPaymentMethodBadge(order.payment_method);
                    const dateFormatted = formatOrderDate(order.created_at);
                    const shippingBadge = getShippingStatusBadge(order.shipping_status);
                    
                    return `
                        <tr>
                            <td class="fw-bold text-primary">#${order.payment_id}</td>
                            <td>
                                <div>
                                    <strong>${order.payer_name || 'N/A'}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>${order.payer_email || 'N/A'}
                                    </small>
                                    ${order.payer_phone ? `<br><small class="text-muted"><i class="fas fa-phone me-1"></i>${order.payer_phone}</small>` : ''}
                                </div>
                                <div class="mt-2 text-muted">
                                    <small>
                                        <strong>Dirección:</strong><br>
                                        ${order.shipping_address ? `
                                            <i class="fas fa-map-marker-alt me-1"></i>${order.shipping_address}<br>
                                            ${order.shipping_city}, ${order.shipping_state}<br>
                                            ${order.shipping_country} ${order.shipping_zip}
                                        ` : '<span class="text-warning">Sin dirección</span>'}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted">ID: ${order.external_reference || 'N/A'}</small>
                                <div class="mt-2">
                                    <span class="badge bg-info">
                                        <i class="fas fa-truck me-1"></i>${order.shipping_method || 'Estándar'}
                                    </span>
                                    <br>
                                    ${shippingBadge}
                                </div>
                            </td>
                            <td>$${formatPrice(order.amount)}</td>
                            <td>
                                <span class="badge bg-secondary">GRATIS</span>
                            </td>
                            <td class="fw-bold">$${formatPrice(order.amount)}</td>
                            <td>${paymentMethod}</td>
                            <td>
                                ${order.payment_id ? `<span class="badge bg-success">MP-${order.payment_id}</span>` : '<span class="badge bg-warning">Pendiente</span>'}
                            </td>
                            <td>${dateFormatted}</td>
                            <td>${statusBadge}</td>
                            <td>
                                <div class="btn-group-vertical btn-group-sm">
                                    <button class="btn btn-outline-info mb-1" onclick="viewOrderDetailsMercadoPago('${order.payment_id}')" title="Ver detalles">
                                        <i class="fas fa-eye"></i> Detalles
                                    </button>
                                    <button class="btn btn-outline-success mb-1" onclick="updateShippingStatus('${order.payment_id}')" title="Actualizar envío">
                                        <i class="fas fa-truck"></i> Estado Envío
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="viewShippingFormData('${order.payment_id}')" title="Ver datos de envío">
                                        <i class="fas fa-box"></i> Datos Envío
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                }).join('');
                
                ordersCount.textContent = `${orders.length} pedido${orders.length !== 1 ? 's' : ''}`;
                ordersInfo.textContent = `Mostrando ${orders.length} de ${orders.length} pedidos`;
                updateLastRefreshTime();
                
            } catch (error) {
                console.error('❌ Error renderizando pedidos MercadoPago:', error);
                showEmptyOrdersMessage();
            }
        }
        
        // Actualizar estadísticas con datos de MercadoPago
        function updateOrdersStatsMercadoPago(orders) {
            const totalOrders = orders.length;
            const approvedOrders = orders.filter(o => o.status === 'approved').length;
            const pendingOrders = orders.filter(o => o.status === 'pending').length;
            const rejectedOrders = orders.filter(o => o.status === 'rejected').length;
            const totalRevenue = orders.filter(o => o.status === 'approved')
                .reduce((sum, o) => sum + parseFloat(o.amount), 0);
            
            document.getElementById('total-orders-stat').textContent = totalOrders;
            document.getElementById('pending-orders-stat').textContent = pendingOrders;
            document.getElementById('completed-orders-stat').textContent = approvedOrders;
            document.getElementById('total-revenue-stat').textContent = '$' + formatPrice(totalRevenue);
        }
        
        // Función para obtener badge de estado de MercadoPago
        function getMercadoPagoStatusBadge(status) {
            const statusConfig = {
                'approved': { class: 'bg-success', icon: 'check-circle', text: 'APROBADO' },
                'pending': { class: 'bg-warning', icon: 'clock', text: 'PENDIENTE' },
                'rejected': { class: 'bg-danger', icon: 'times-circle', text: 'RECHAZADO' },
                'cancelled': { class: 'bg-secondary', icon: 'ban', text: 'CANCELADO' }
            };
            
            const config = statusConfig[status] || { class: 'bg-secondary', icon: 'question', text: status?.toUpperCase() || 'DESCONOCIDO' };
            
            return `<span class="badge ${config.class}">
                <i class="fas fa-${config.icon} me-1"></i>${config.text}
            </span>`;
        }
        
        // Función para obtener badge de método de pago de MercadoPago
        function getMercadoPagoPaymentMethodBadge(method) {
            const methodConfig = {
                'credit_card': { class: 'bg-primary', icon: 'credit-card', text: 'Tarjeta Crédito' },
                'debit_card': { class: 'bg-info', icon: 'credit-card', text: 'Tarjeta Débito' },
                'bank_transfer': { class: 'bg-success', icon: 'university', text: 'PSE' },
                'account_money': { class: 'bg-warning', icon: 'wallet', text: 'Dinero Cuenta' }
            };
            
            const config = methodConfig[method] || { class: 'bg-secondary', icon: 'credit-card', text: method || 'N/A' };
            
            return `<span class="badge ${config.class}">
                <i class="fas fa-${config.icon} me-1"></i>${config.text}
            </span>`;
        }
        
        // Formatear fecha de pedido
        function formatOrderDate(dateString) {
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('es-CO', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } catch (e) {
                return dateString || 'N/A';
            }
        }
        
        // Ver detalles de pedido de MercadoPago
        function viewOrderDetailsMercadoPago(paymentId) {
            Swal.fire({
                title: `Detalles del Pago #${paymentId}`,
                html: `
                    <div class="text-start">
                        <p><strong>ID de Pago:</strong> ${paymentId}</p>
                        <p><strong>Estado:</strong> Verificando...</p>
                        <div class="text-center">
                            <div class="spinner-border" role="status"></div>
                            <p class="mt-2">Consultando detalles en MercadoPago...</p>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            // Aquí podrías hacer una llamada a la API de MercadoPago para obtener más detalles
            setTimeout(() => {
                Swal.update({
                    html: `
                        <div class="text-start">
                            <p><strong>ID de Pago:</strong> ${paymentId}</p>
                            <p><strong>Estado:</strong> <span class="badge bg-success">APROBADO</span></p>
                            <p><strong>Procesado por:</strong> MercadoPago</p>
                            <div class="alert alert-info">
                                Para más detalles completos, consulta el dashboard de MercadoPago.
                            </div>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'Cerrar'
                });
            }, 2000);
        }
        
        // Función para badge de estado de envío
        function getShippingStatusBadge(status) {
            const statusConfig = {
                'pending': { class: 'bg-warning', icon: 'clock', text: 'Pendiente de Envío' },
                'processing': { class: 'bg-info', icon: 'box', text: 'Preparando' },
                'shipped': { class: 'bg-primary', icon: 'truck', text: 'Enviado' },
                'delivered': { class: 'bg-success', icon: 'check-circle', text: 'Entregado' },
                'cancelled': { class: 'bg-danger', icon: 'times-circle', text: 'Cancelado' }
            };
            
            const config = statusConfig[status] || statusConfig['pending'];
            
            return `<span class="badge ${config.class}">
                <i class="fas fa-${config.icon} me-1"></i>${config.text}
            </span>`;
        }

        // Actualizar estado de envío
        function updateShippingStatus(paymentId) {
            Swal.fire({
                title: 'Actualizar Estado de Envío',
                html: `
                    <div class="text-start">
                        <p><strong>Pedido #${paymentId}</strong></p>
                        <form id="shipping-form">
                            <div class="mb-3">
                                <label class="form-label">Estado de Envío:</label>
                                <select class="form-select" id="shipping-status">
                                    <option value="pending">Pendiente de Envío</option>
                                    <option value="processing">Preparando</option>
                                    <option value="shipped">Enviado</option>
                                    <option value="delivered">Entregado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Método de Envío:</label>
                                <select class="form-select" id="shipping-method">
                                    <option value="standard">Envío Estándar</option>
                                    <option value="express">Envío Express</option>
                                    <option value="pickup">Retirar en Tienda</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notas de Envío:</label>
                                <textarea class="form-control" id="shipping-notes" rows="2"></textarea>
                            </div>
                        </form>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Actualizar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    return {
                        status: document.getElementById('shipping-status').value,
                        method: document.getElementById('shipping-method').value,
                        notes: document.getElementById('shipping-notes').value
                    };
                }
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('actualizar-envio.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                payment_id: paymentId,
                                shipping_status: result.value.status,
                                shipping_method: result.value.method,
                                shipping_notes: result.value.notes
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            Swal.fire({
                                title: '¡Actualizado!',
                                text: 'Estado de envío actualizado correctamente.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Recargar pedidos
                            loadOrders(currentOrdersPage);
                        } else {
                            throw new Error(data.message || 'Error actualizando envío');
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo actualizar el estado del envío: ' + error.message,
                            icon: 'error'
                        });
                    }
                }
            });
        }
        
        // Función para ver datos del formulario de envío
        function viewShippingFormData(paymentId) {
            Swal.fire({
                title: 'Cargando datos de envío...',
                html: `
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                allowOutsideClick: false
            });

            fetch(`obtener-datos-envio.php?payment_id=${paymentId}`)
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        const data = result.data;
                        Swal.fire({
                            title: 'Datos de Envío',
                            html: `
                                <div class="text-start p-3">
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary text-white">
                                            <i class="fas fa-user me-2"></i>Datos del Cliente
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Nombre:</strong> ${data.cliente.nombre_completo}</p>
                                            <p><strong>Email:</strong> ${data.cliente.email}</p>
                                            <p><strong>Teléfono:</strong> ${data.cliente.telefono}</p>
                                            <p><strong>Teléfono:</strong> ${data.cliente.telefono}</p>
                                        </div>
                                    </div>

                                    <div class="card mb-3">
                                        <div class="card-header bg-success text-white">
                                            <i class="fas fa-shipping-fast me-2"></i>Datos de Envío
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Dirección:</strong> ${data.envio.direccion}</p>
                                            <p><strong>Ciudad:</strong> ${data.envio.ciudad}</p>
                                            <p><strong>Departamento:</strong> ${data.envio.departamento}</p>
                                            <p><strong>Código Postal:</strong> ${data.envio.codigo_postal}</p>
                                            <p><strong>Notas adicionales:</strong> ${data.envio.notas || 'Sin notas'}</p>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header bg-info text-white">
                                            <i class="fas fa-box me-2"></i>Datos del Pedido
                                        </div>
                                        <div class="card-body">
                                            <p><strong>ID Pago:</strong> ${data.pedido.id}</p>
                                            <p><strong>Referencia:</strong> ${data.pedido.referencia}</p>
                                            <p><strong>Estado Pago:</strong> <span class="badge bg-${data.pedido.estado === 'approved' ? 'success' : 'warning'}">${data.pedido.estado.toUpperCase()}</span></p>
                                            <p><strong>Estado Envío:</strong> <span class="badge bg-${getShippingStatusClass(data.envio.estado_envio || 'pendiente')}">${(data.envio.estado_envio || 'pendiente').toUpperCase()}</span></p>
                                            <p><strong>Método Envío:</strong> ${data.pedido.metodo_envio || 'Estándar'}</p>
                                            <p><strong>Monto:</strong> $${formatPrice(data.pedido.monto)}</p>
                                        </div>
                                    </div>
                                </div>
                            `,
                            width: '800px',
                            showCloseButton: true,
                            showConfirmButton: false,
                            allowOutsideClick: true
                        });
                    } else {
                        throw new Error(result.message || 'Error obteniendo datos de envío');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error'
                    });
                });
        }

        // Función auxiliar para obtener clase de estado de envío
        function getShippingStatusClass(status) {
            const statusClasses = {
                'pending': 'warning',
                'processing': 'info',
                'shipped': 'primary',
                'delivered': 'success',
                'cancelled': 'danger'
            };
            return statusClasses[status] || 'secondary';
        }

        // ========= FIN FUNCIONES MERCADOPAGO =========
        
        // Actualizar estadísticas de pedidos (FUNCIÓN ORIGINAL)
        function updateOrdersStats(orders) {
            const totalOrders = orders.length;
            const pendingOrders = orders.filter(o => ['pendiente', 'pending'].includes(o.estado)).length;
            const completedOrders = orders.filter(o => ['aprobado', 'completed'].includes(o.estado)).length;
            const totalRevenue = orders.filter(o => ['aprobado', 'completed'].includes(o.estado))
                .reduce((sum, o) => sum + parseFloat(o.total), 0);
            
            document.getElementById('total-orders-stat').textContent = totalOrders;
            document.getElementById('pending-orders-stat').textContent = pendingOrders;
            document.getElementById('completed-orders-stat').textContent = completedOrders;
            document.getElementById('total-revenue-stat').textContent = '$' + formatPrice(totalRevenue);
        }
        
        // Mostrar mensaje cuando no hay pedidos
        function showEmptyOrdersMessage() {
            const tbody = document.getElementById('orders-tbody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <div class="text-danger">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>Error cargando pedidos</h5>
                            <p>No se pudieron cargar los pedidos. Verifica la conexión con la base de datos.</p>
                            <button class="btn btn-outline-primary" onclick="loadOrders()">
                                <i class="fas fa-refresh me-2"></i>Reintentar
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }
        
        // Actualizar hora de última actualización
        function updateLastRefreshTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('es-ES');
            document.getElementById('last-update').textContent = `Última actualización: ${timeString}`;
        }
        
        // Funciones de filtrado y búsqueda
        function filterOrdersByStatus() {
            const selectElement = document.getElementById('order-status-filter');
            const selectedValue = selectElement.value;
            
            console.log('🔍 Filtro de estado cambiado a:', selectedValue);
            
            // Resetear página a 1 cuando se cambia el filtro
            currentOrdersPage = 1;
            
            // Determinar el tipo de filtro (pago o envío)
            if (selectedValue.startsWith('env_')) {
                // Es un filtro de envío, extraer el valor real
                const realValue = selectedValue.replace('env_', '');
                currentOrderStatusFilter = `shipping:${realValue}`;
                console.log('📦 Filtro de envío aplicado:', realValue);
            } else if (selectedValue) {
                // Es un filtro de pago
                currentOrderStatusFilter = `payment:${selectedValue}`;
                console.log('💳 Filtro de pago aplicado:', selectedValue);
            } else {
                // Sin filtro
                currentOrderStatusFilter = '';
                console.log('🔄 Filtro removido - mostrando todos los pedidos');
            }
            
            // Actualizar el indicador visual del filtro activo
            updateFilterIndicator();
            
            // Cargar pedidos con el nuevo filtro
            loadOrders(1);
        }
        
        function updateFilterIndicator() {
            // Buscar si existe un indicador, si no, crearlo
            let indicator = document.getElementById('filter-indicator');
            
            if (!indicator) {
                // Crear el indicador si no existe
                const filtersContainer = document.querySelector('.d-flex.gap-2.align-items-center.flex-wrap');
                if (filtersContainer) {
                    indicator = document.createElement('div');
                    indicator.id = 'filter-indicator';
                    indicator.className = 'alert alert-info d-none py-2 px-3 mb-0';
                    indicator.style.fontSize = '14px';
                    filtersContainer.parentNode.insertBefore(indicator, filtersContainer.nextSibling);
                }
            }
            
            if (indicator) {
                const hasStatusFilter = currentOrderStatusFilter !== '';
                const hasDateFilter = currentOrderDateFilter !== '';
                const hasSearchFilter = currentOrderSearchQuery !== '';
                
                if (hasStatusFilter || hasDateFilter || hasSearchFilter) {
                    let filterText = 'Filtros activos: ';
                    const filters = [];
                    
                    if (hasStatusFilter) {
                        const [type, value] = currentOrderStatusFilter.split(':');
                        const typeText = type === 'payment' ? 'Pago' : 'Envío';
                        const valueText = value.replace('_', ' ');
                        filters.push(`${typeText}: ${valueText}`);
                    }
                    
                    if (hasDateFilter) {
                        filters.push(`Fecha: ${currentOrderDateFilter}`);
                    }
                    
                    if (hasSearchFilter) {
                        filters.push(`Búsqueda: "${currentOrderSearchQuery}"`);
                    }
                    
                    indicator.innerHTML = `
                        <i class="fas fa-filter me-2"></i>
                        ${filterText}${filters.join(' | ')}
                        <button class="btn btn-sm btn-outline-secondary ms-2" onclick="clearAllFilters()" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    indicator.classList.remove('d-none');
                } else {
                    indicator.classList.add('d-none');
                }
            }
        }
        
        function clearAllFilters() {
            // Limpiar todos los filtros
            currentOrderStatusFilter = '';
            currentOrderDateFilter = '';
            currentOrderSearchQuery = '';
            
            // Resetear los elementos del formulario
            document.getElementById('order-status-filter').value = '';
            document.getElementById('order-date-filter').value = '';
            document.getElementById('orders-search').value = '';
            
            // Actualizar indicador
            updateFilterIndicator();
            
            // Recargar pedidos
            console.log('🧹 Todos los filtros limpiados');
            loadOrders(1);
        }
        
        function filterOrdersByDate() {
            currentOrderDateFilter = document.getElementById('order-date-filter').value;
            console.log('📅 Filtro de fecha cambiado a:', currentOrderDateFilter);
            
            // Actualizar indicador visual
            updateFilterIndicator();
            
            loadOrders(1);
        }
        
        function searchOrders() {
            currentOrderSearchQuery = document.getElementById('orders-search').value.trim();
            console.log('🔍 Búsqueda cambiada a:', currentOrderSearchQuery);
            
            // Actualizar indicador visual
            updateFilterIndicator();
            
            // Aplicar debounce para evitar demasiadas consultas
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                loadOrders(1);
            }, 500);
        }
        
        function changeOrdersPerPage() {
            currentOrdersPerPage = parseInt(document.getElementById('orders-per-page').value);
            loadOrders(1);
        }
        
        // Funciones de acción
        function refreshOrders() {
            console.log('🔄 Refrescando pedidos manualmente...');
            loadOrders(currentOrdersPage);
        }
        
        async function viewOrderDetails(orderId) {
            try {
                console.log('📋 Cargando detalles del pedido:', orderId);
                
                // Obtener los detalles del pedido desde el array de pedidos cargados
                const orderData = window.allOrders.find(order => order.id == orderId);
                
                if (!orderData) {
                    throw new Error('No se encontraron los detalles del pedido');
                }
                
                // Formatear productos
                let productsList = '';
                if (orderData.productos && orderData.productos.length > 0) {
                    productsList = orderData.productos.map(product => `
                        <div class="product-item d-flex align-items-center mb-2 p-2 border rounded">
                            <div class="product-info flex-grow-1">
                                <strong>${product.name || product.nombre || 'Producto sin nombre'}</strong><br>
                                <small class="text-muted">
                                    Cantidad: ${product.quantity || product.cantidad || 1} | 
                                    Precio: $${Number(product.price || product.precio || 0).toLocaleString('es-CO')}
                                    ${(product.size || product.talla) ? `| Talla: ${product.size || product.talla}` : ''}
                                    ${product.color ? `| Color: ${product.color}` : ''}
                                </small>
                            </div>
                        </div>
                    `).join('');
                } else {
                    productsList = '<p class="text-muted">No hay productos registrados</p>';
                }
                
                Swal.fire({
                    title: `📋 Detalles del Pedido #${orderId}`,
                    html: `
                        <div class="order-details-modal text-start">
                            <!-- Información del Pedido -->
                            <div class="section mb-4">
                                <h6 class="fw-bold text-primary mb-3">📦 Información del Pedido</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">ID Pedido:</small><br>
                                        <strong>${orderData.pedido_id || 'N/A'}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Fecha:</small><br>
                                        <strong>${orderData.fecha_creacion_formateada || 'N/A'}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted">Estado Pago:</small><br>
                                        <span class="badge bg-${orderData.estado === 'aprobado' ? 'success' : orderData.estado === 'pendiente' ? 'warning' : 'danger'}">
                                            ${orderData.estado || 'N/A'}
                                        </span>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted">Estado Envío:</small><br>
                                        <span class="badge bg-${orderData.estado_envio === 'entregado' ? 'success' : orderData.estado_envio === 'enviado' ? 'info' : orderData.estado_envio === 'en_proceso' ? 'warning' : 'secondary'}">
                                            ${orderData.estado_envio || 'N/A'}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de Envío -->
                            <div class="section mb-4">
                                <h6 class="fw-bold text-primary mb-3">🚚 Información de Envío</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small class="text-muted">Nombre completo:</small><br>
                                        <strong>${orderData.nombre_completo || 'No especificado'}</strong>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Teléfono:</small><br>
                                        <strong>${orderData.telefono || 'No especificado'}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted">Email:</small><br>
                                        <strong>${orderData.email || 'No especificado'}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted">Departamento:</small><br>
                                        <strong>${orderData.departamento || 'No especificado'}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted">Ciudad:</small><br>
                                        <strong>${orderData.ciudad || 'No especificado'}</strong>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <small class="text-muted">Código Postal:</small><br>
                                        <strong>${orderData.codigo_postal || 'No especificado'}</strong>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted">Dirección:</small><br>
                                        <strong>${orderData.direccion || 'No especificado'}</strong>
                                    </div>
                                    ${orderData.notas_adicionales ? `
                                    <div class="col-12 mt-2">
                                        <small class="text-muted">Notas adicionales:</small><br>
                                        <strong>${orderData.notas_adicionales}</strong>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>

                            <!-- Productos -->
                            <div class="section mb-4">
                                <h6 class="fw-bold text-primary mb-3">🛍️ Productos (${orderData.cantidad_productos || 0})</h6>
                                <div class="products-list">
                                    ${productsList}
                                </div>
                            </div>

                            <!-- Resumen de Pago -->
                            <div class="section mb-3">
                                <h6 class="fw-bold text-primary mb-3">💰 Resumen de Pago</h6>
                                <div class="row">
                                    <div class="col-4">
                                        <small class="text-muted">Subtotal:</small><br>
                                        <strong>${orderData.subtotal_formateado || '$0'}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Envío:</small><br>
                                        <strong>${orderData.envio_formateado || '$0'}</strong>
                                    </div>
                                    <div class="col-4">
                                        <small class="text-muted">Total:</small><br>
                                        <strong class="text-success fs-5">${orderData.total_formateado || '$0'}</strong>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <small class="text-muted">Método de pago:</small><br>
                                        <strong>${orderData.metodo_pago || 'No especificado'}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    width: '800px',
                    showCancelButton: false,
                    confirmButtonText: 'Cerrar',
                    confirmButtonColor: '#007bff',
                    customClass: {
                        popup: 'order-details-popup'
                    }
                });
                
            } catch (error) {
                console.error('❌ Error cargando detalles del pedido:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar los detalles del pedido.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        }
        
        async function updateOrderStatus(orderId, newStatus) {
            try {
                // Determinar el tipo de estado y el mensaje apropiado
                let statusType, statusText, confirmText;
                
                if (newStatus === 'aprobado' || newStatus === 'rechazado') {
                    statusType = 'payment';
                    statusText = newStatus === 'aprobado' ? 'aprobado' : 'rechazado';
                    confirmText = `¿Estás seguro de marcar el pago del pedido #${orderId} como ${statusText}?`;
                } else {
                    statusType = 'shipping';
                    statusText = newStatus;
                    const statusNames = {
                        'pendiente': 'pendiente',
                        'en_proceso': 'en proceso',
                        'enviado': 'enviado', 
                        'entregado': 'entregado'
                    };
                    confirmText = `¿Estás seguro de marcar el envío del pedido #${orderId} como ${statusNames[newStatus] || newStatus}?`;
                }
                
                const result = await Swal.fire({
                    title: '¿Cambiar estado del pedido?',
                    text: confirmText,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: async () => {
                        try {
                            const response = await fetch('api/update-order-status.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    order_id: orderId,
                                    status_type: statusType,
                                    new_status: newStatus
                                })
                            });
                            
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            }
                            
                            const result = await response.json();
                            
                            if (result.error) {
                                throw new Error(result.message);
                            }
                            
                            return result;
                            
                        } catch (error) {
                            Swal.showValidationMessage(`Error: ${error.message}`);
                            return false;
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                });
                
                if (result.isConfirmed && result.value) {
                    console.log('✅ Estado actualizado:', result.value);
                    
                    await Swal.fire({
                        title: '¡Actualizado!',
                        text: result.value.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    
                    // Recargar la lista de pedidos para mostrar los cambios
                    loadOrders(currentOrdersPage);
                }
                
            } catch (error) {
                console.error('❌ Error actualizando estado:', error);
                
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo actualizar el estado del pedido. Inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        }
        
        async function printOrder(orderId) {
            try {
                console.log('🖨️ Preparando impresión del pedido:', orderId);
                
                // Obtener los detalles del pedido desde el array de pedidos cargados
                const orderData = window.allOrders.find(order => order.id == orderId);
                
                if (!orderData) {
                    throw new Error('No se encontraron los detalles del pedido');
                }
                
                // Formatear productos para impresión
                let productsList = '';
                let totalItems = 0;
                
                if (orderData.productos && orderData.productos.length > 0) {
                    productsList = orderData.productos.map((product, index) => {
                        const quantity = product.quantity || product.cantidad || 1;
                        totalItems += quantity;
                        const price = Number(product.price || product.precio || 0);
                        const total = price * quantity;
                        
                        return `
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">${index + 1}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                    <strong>${product.name || product.nombre || 'Producto sin nombre'}</strong><br>
                                    <small style="color: #666;">
                                        ${(product.size || product.talla) ? `Talla: ${product.size || product.talla}` : ''}
                                        ${product.color ? ` | Color: ${product.color}` : ''}
                                    </small>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">${quantity}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right;">$${price.toLocaleString('es-CO')}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right; font-weight: bold;">$${total.toLocaleString('es-CO')}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    productsList = '<tr><td colspan="5" style="padding: 20px; text-align: center; color: #666;">No hay productos registrados</td></tr>';
                }
                
                // Formatear fecha actual para la impresión
                const now = new Date();
                const printDate = now.toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
                
                // Crear el contenido HTML para impresión
                const printContent = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <meta charset="utf-8">
                        <title>Pedido #${orderId} - Musa Moda</title>
                        <style>
                            @page {
                                margin: 2cm;
                                size: A4;
                            }
                            * {
                                box-sizing: border-box;
                            }
                            body {
                                font-family: Arial, sans-serif;
                                line-height: 1.4;
                                color: #333;
                                margin: 0;
                                padding: 0;
                            }
                            .header {
                                text-align: center;
                                margin-bottom: 30px;
                                border-bottom: 3px solid #007bff;
                                padding-bottom: 20px;
                            }
                            .company-name {
                                font-size: 28px;
                                font-weight: bold;
                                color: #007bff;
                                margin: 0;
                            }
                            .document-title {
                                font-size: 20px;
                                margin: 10px 0 5px 0;
                                color: #333;
                            }
                            .print-date {
                                font-size: 12px;
                                color: #666;
                            }
                            .section {
                                margin-bottom: 25px;
                                background: #f8f9fa;
                                padding: 15px;
                                border-radius: 5px;
                            }
                            .section-title {
                                font-size: 16px;
                                font-weight: bold;
                                color: #007bff;
                                margin: 0 0 15px 0;
                                border-bottom: 1px solid #dee2e6;
                                padding-bottom: 5px;
                            }
                            .info-row {
                                display: flex;
                                margin-bottom: 8px;
                            }
                            .info-label {
                                font-weight: bold;
                                width: 150px;
                                color: #495057;
                            }
                            .info-value {
                                flex: 1;
                                color: #212529;
                            }
                            .products-table {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 10px;
                                background: white;
                            }
                            .products-table th {
                                background: #007bff;
                                color: white;
                                padding: 12px 8px;
                                text-align: left;
                                font-weight: bold;
                            }
                            .products-table th:nth-child(3),
                            .products-table th:nth-child(4),
                            .products-table th:nth-child(5) {
                                text-align: right;
                            }
                            .totals-section {
                                margin-top: 20px;
                                text-align: right;
                            }
                            .total-row {
                                margin: 5px 0;
                                font-size: 14px;
                            }
                            .total-final {
                                font-size: 18px;
                                font-weight: bold;
                                color: #28a745;
                                border-top: 2px solid #dee2e6;
                                padding-top: 10px;
                                margin-top: 10px;
                            }
                            .status-badge {
                                display: inline-block;
                                padding: 4px 12px;
                                border-radius: 20px;
                                font-size: 12px;
                                font-weight: bold;
                                text-transform: uppercase;
                            }
                            .status-pendiente { background: #fff3cd; color: #856404; }
                            .status-aprobado { background: #d4edda; color: #155724; }
                            .status-rechazado { background: #f8d7da; color: #721c24; }
                            .status-en_proceso { background: #ffeaa7; color: #6c5ce7; }
                            .status-enviado { background: #74b9ff; color: #0984e3; }
                            .status-entregado { background: #00b894; color: #00cec9; }
                            .footer {
                                margin-top: 40px;
                                text-align: center;
                                font-size: 12px;
                                color: #666;
                                border-top: 1px solid #dee2e6;
                                padding-top: 20px;
                            }
                            @media print {
                                .no-print { display: none !important; }
                                body { print-color-adjust: exact; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="header">
                            <h1 class="company-name">MUSA MODA</h1>
                            <h2 class="document-title">Pedido #${orderId}</h2>
                            <p class="print-date">Impreso el: ${printDate}</p>
                        </div>

                        <div class="section">
                            <h3 class="section-title">📋 Información del Pedido</h3>
                            <div class="info-row">
                                <div class="info-label">ID Pedido:</div>
                                <div class="info-value">${orderData.pedido_id || 'N/A'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Fecha:</div>
                                <div class="info-value">${orderData.fecha_creacion_formateada || 'N/A'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Estado Pago:</div>
                                <div class="info-value">
                                    <span class="status-badge status-${orderData.estado || 'pendiente'}">
                                        ${orderData.estado || 'N/A'}
                                    </span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Estado Envío:</div>
                                <div class="info-value">
                                    <span class="status-badge status-${orderData.estado_envio || 'pendiente'}">
                                        ${(orderData.estado_envio || 'N/A').replace('_', ' ')}
                                    </span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Método de Pago:</div>
                                <div class="info-value">${(orderData.metodo_pago || 'N/A').replace('_', '/')}</div>
                            </div>
                        </div>

                        <div class="section">
                            <h3 class="section-title">🚚 Información de Envío</h3>
                            <div class="info-row">
                                <div class="info-label">Cliente:</div>
                                <div class="info-value">${orderData.nombre_completo || 'No especificado'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Teléfono:</div>
                                <div class="info-value">${orderData.telefono || 'No especificado'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">${orderData.email || 'No especificado'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Ubicación:</div>
                                <div class="info-value">${orderData.ciudad || 'No especificado'}, ${orderData.departamento || 'No especificado'}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Dirección:</div>
                                <div class="info-value">${orderData.direccion || 'No especificado'}</div>
                            </div>
                            ${orderData.codigo_postal ? `
                            <div class="info-row">
                                <div class="info-label">Código Postal:</div>
                                <div class="info-value">${orderData.codigo_postal}</div>
                            </div>
                            ` : ''}
                            ${orderData.notas_adicionales ? `
                            <div class="info-row">
                                <div class="info-label">Notas:</div>
                                <div class="info-value">${orderData.notas_adicionales}</div>
                            </div>
                            ` : ''}
                        </div>

                        <div class="section">
                            <h3 class="section-title">🛍️ Productos Pedidos (${totalItems} items)</h3>
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Producto</th>
                                        <th style="width: 80px;">Cant.</th>
                                        <th style="width: 120px;">Precio Unit.</th>
                                        <th style="width: 120px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${productsList}
                                </tbody>
                            </table>
                            
                            <div class="totals-section">
                                <div class="total-row">
                                    <strong>Subtotal: ${orderData.subtotal_formateado || '$0'}</strong>
                                </div>
                                <div class="total-row">
                                    <strong>Envío: ${orderData.envio_formateado || '$0'}</strong>
                                </div>
                                <div class="total-final">
                                    <strong>TOTAL: ${orderData.total_formateado || '$0'}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="footer">
                            <p>MUSA MODA - Sistema de Gestión de Pedidos</p>
                            <p>Este documento fue generado automáticamente el ${printDate}</p>
                        </div>
                    </body>
                    </html>
                `;

                // Crear ventana de impresión
                const printWindow = window.open('', '_blank', 'width=800,height=600,scrollbars=yes');
                
                if (!printWindow) {
                    throw new Error('No se pudo abrir la ventana de impresión. Verifica que los popups estén habilitados.');
                }

                // Escribir contenido y configurar impresión
                printWindow.document.write(printContent);
                printWindow.document.close();

                // Esperar a que se cargue y luego imprimir
                printWindow.onload = function() {
                    printWindow.focus();
                    setTimeout(() => {
                        printWindow.print();
                        // Opcional: cerrar ventana después de imprimir
                        // printWindow.close();
                    }, 500);
                };

                console.log('✅ Vista de impresión generada para pedido:', orderId);

            } catch (error) {
                console.error('❌ Error preparando impresión:', error);
                
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo preparar la impresión del pedido: ' + error.message,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        }
        
        // Ver comprobante de pago
        async function viewReceipt(orderId) {
            try {
                console.log('📸 Cargando comprobante para pedido:', orderId);
                
                const response = await fetch(`api/get-receipt.php?order_id=${orderId}`);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success && result.receipt) {
                    const receiptData = result.receipt;
                    const imageUrl = `uploads/comprobantes/${receiptData.comprobante_imagen}`;
                    
                    Swal.fire({
                        title: `Comprobante - Pedido #${orderId}`,
                        html: `
                            <div class="receipt-modal">
                                <div class="receipt-info mb-3">
                                    <div class="row text-start">
                                        <div class="col-6">
                                            <small class="text-muted">Remitente:</small><br>
                                            <strong>${receiptData.nombre_remitente}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">Monto:</small><br>
                                            <strong>$${parseFloat(receiptData.monto).toLocaleString('es-CO')}</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <small class="text-muted">Número destino:</small><br>
                                            <strong>${receiptData.numero_destino}</strong>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <small class="text-muted">Referencia:</small><br>
                                            <strong>${receiptData.referencia || 'N/A'}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="receipt-image">
                                    <img src="${imageUrl}" alt="Comprobante" style="max-width: 100%; max-height: 400px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                                </div>
                                <div class="mt-3">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="approvePayment('${orderId}')">
                                            <i class="fas fa-check me-1"></i>Aprobar
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="rejectPayment('${orderId}')">
                                            <i class="fas fa-times me-1"></i>Rechazar
                                        </button>
                                        <a href="${imageUrl}" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="fas fa-external-link-alt me-1"></i>Ver completa
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `,
                        width: '600px',
                        showConfirmButton: false,
                        showCloseButton: true,
                        customClass: {
                            popup: 'receipt-popup'
                        }
                    });
                } else {
                    throw new Error(result.message || 'No se encontró el comprobante');
                }
                
            } catch (error) {
                console.error('❌ Error cargando comprobante:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo cargar el comprobante: ' + error.message,
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        }
        
        // Aprobar pago
        async function approvePayment(orderId) {
            try {
                const response = await fetch('api/update-payment-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        status: 'approved'
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        title: 'Pago Aprobado',
                        text: 'El pago ha sido aprobado exitosamente',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadOrders(currentOrdersPage);
                } else {
                    throw new Error(result.message);
                }
                
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo aprobar el pago: ' + error.message,
                    icon: 'error'
                });
            }
        }
        
        // Rechazar pago
        async function rejectPayment(orderId) {
            const { value: reason } = await Swal.fire({
                title: 'Rechazar Pago',
                input: 'textarea',
                inputLabel: 'Motivo del rechazo',
                inputPlaceholder: 'Ingresa el motivo del rechazo...',
                showCancelButton: true,
                confirmButtonText: 'Rechazar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            });
            
            if (reason) {
                try {
                    const response = await fetch('api/update-payment-status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            order_id: orderId,
                            status: 'rejected',
                            reason: reason
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        Swal.fire({
                            title: 'Pago Rechazado',
                            text: 'El pago ha sido rechazado',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadOrders(currentOrdersPage);
                    } else {
                        throw new Error(result.message);
                    }
                    
                } catch (error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'No se pudo rechazar el pago: ' + error.message,
                        icon: 'error'
                    });
                }
            }
        }
        
        async function exportOrders() {
            try {
                console.log('📊 Iniciando exportación de pedidos...');
                
                // Mostrar mensaje de progreso
                Swal.fire({
                    title: 'Exportando pedidos...',
                    text: 'Generando archivo Excel, por favor espera.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Crear un enlace temporal para descargar el archivo
                const link = document.createElement('a');
                link.href = 'api/export-orders.php';
                link.download = `pedidos_musa_moda_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.csv`;
                
                // Agregar el enlace al DOM, hacer clic y remover
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                // Esperar un momento para que inicie la descarga
                setTimeout(() => {
                    Swal.fire({
                        title: '¡Exportación completada!',
                        text: 'El archivo de pedidos se está descargando. Puedes abrirlo con Excel.',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    console.log('✅ Exportación de pedidos completada');
                }, 1000);
                
            } catch (error) {
                console.error('❌ Error exportando pedidos:', error);
                
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo exportar los pedidos. Inténtalo de nuevo.',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
            }
        }
        
        // Auto-refresh de pedidos
        function toggleAutoRefresh() {
            const checkbox = document.getElementById('auto-refresh-orders');
            if (checkbox.checked) {
                startOrdersAutoRefresh();
            } else {
                stopOrdersAutoRefresh();
            }
        }
        
        function startOrdersAutoRefresh() {
            if (ordersRefreshInterval) {
                clearInterval(ordersRefreshInterval);
            }
            
            // Refrescar cada 30 segundos
            ordersRefreshInterval = setInterval(() => {
                console.log('🔄 Auto-refresh de pedidos...');
                loadOrders(currentOrdersPage);
            }, 30000);
            
            console.log('✅ Auto-refresh de pedidos activado (30s)');
        }
        
        function stopOrdersAutoRefresh() {
            if (ordersRefreshInterval) {
                clearInterval(ordersRefreshInterval);
                ordersRefreshInterval = null;
                console.log('⏹️ Auto-refresh de pedidos desactivado');
            }
        }
        
        // Formatear precios
        function formatPrice(price) {
            return parseFloat(price).toLocaleString('es-CO');
        }
        
        // Event listener para detectar cuando se muestra la tab de pedidos
        document.addEventListener('DOMContentLoaded', function() {
            const ordersTab = document.getElementById('v-pills-pedidos-tab');
            if (ordersTab) {
                ordersTab.addEventListener('shown.bs.tab', function() {
                    console.log('📊 Tab de pedidos activada - Cargando datos...');
                    loadOrders(1);
                    
                    // Activar auto-refresh si está habilitado
                    const autoRefreshCheckbox = document.getElementById('auto-refresh-orders');
                    if (autoRefreshCheckbox && autoRefreshCheckbox.checked) {
                        startOrdersAutoRefresh();
                    }
                });
                
                // Detener auto-refresh cuando se sale de la tab
                const allTabs = document.querySelectorAll('[data-bs-toggle="pill"]');
                allTabs.forEach(tab => {
                    tab.addEventListener('shown.bs.tab', function() {
                        if (tab.id !== 'v-pills-pedidos-tab') {
                            stopOrdersAutoRefresh();
                        }
                    });
                });
            }
        });

        // =================== FUNCIONES DE GESTIÓN DE CARRUSEL ===================
        
        // Variable global para almacenar los slides del carrusel
        let carouselSlides = [];
        let selectedSlideIndex = -1;
        
        // Cargar slides actuales del carrusel al abrir la pestaña de configuración
        async function loadCurrentCarouselSlides() {
            console.log('🔄 Cargando slides actuales del carrusel...');
            
            try {
                const response = await fetch('api/carousel-manager.php');
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    carouselSlides = result.slides;
                    console.log('✅ Slides cargados:', carouselSlides);
                    renderCarouselPreview();
                } else {
                    throw new Error(result.message || 'Error desconocido al cargar slides');
                }
                
            } catch (error) {
                console.error('❌ Error cargando slides:', error.message);
                
                // Usar datos predeterminados en caso de error
                carouselSlides = [
                        {
                            id: 1,
                            image: 'images/slide/Coleccion2.png',
                            title: 'Musa',
                            subtitle: '',
                            location: 'CLL 3 20A39 Madrid, Cundinamarca',
                            buttonText: '',
                            buttonLink: '',
                            active: false,
                            order: 1
                        },
                        {
                            id: 2,
                            image: 'images/slide/Coleccion3.png',
                            title: 'Arion',
                            subtitle: '',
                            location: '',
                            buttonText: '',
                            buttonLink: '',
                            active: true,
                            order: 2
                        },
                        {
                            id: 3,
                            image: 'images/slide/Coleccion1.png',
                            title: 'MA',
                            subtitle: '',
                            location: '',
                            buttonText: '',
                            buttonLink: '',
                            active: false,
                            order: 3
                        }
                    ];
                }
            
            renderCarouselPreview();
        }
        
        // Renderizar preview del carrusel
        function renderCarouselPreview() {
            const grid = document.getElementById('carouselPreviewGrid');
            const countBadge = document.getElementById('carousel-count');
            
            if (!grid) return;
            
            countBadge.textContent = `${carouselSlides.length} diapositivas`;
            
            if (carouselSlides.length === 0) {
                grid.innerHTML = `
                    <div class="carousel-empty-state">
                        <i class="fas fa-images"></i>
                        <p class="mb-2"><strong>No hay diapositivas en el carrusel</strong></p>
                        <p class="small text-muted">Arrastra imágenes aquí o usa el botón de arriba para comenzar</p>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = carouselSlides.map((slide, index) => `
                <div class="carousel-slide-item ${slide.active ? 'active' : ''} ${selectedSlideIndex === index ? 'selected' : ''}" 
                     data-index="${index}" 
                     onclick="selectSlide(${index})"
                     draggable="true"
                     ondragstart="handleDragStart(event, ${index})"
                     ondragover="handleDragOver(event)"
                     ondrop="handleDrop(event, ${index})">
                     
                    <img src="${slide.image}" alt="${slide.title}" class="carousel-slide-image" 
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDIwMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTIwIiBmaWxsPSIjRjhGOUZBIi8+CjxwYXRoIGQ9Ik04NSA0NUg4MEw5MCA2MEw5NSA1MEgxMDBMMTEwIDYwTDEwNSA0NUgxMTVWNDBIODVWNDVaIiBmaWxsPSIjREVFMkU2Ii8+CjwvdGV4dD4="'>
                    
                    <div class="carousel-slide-overlay">
                        <div class="carousel-slide-title">${slide.title || 'Sin título'}</div>
                        ${slide.location ? `<div class="carousel-slide-location"><i class="bi-geo-alt me-1"></i>${slide.location}</div>` : ''}
                    </div>
                    
                    <div class="carousel-slide-order">${index + 1}</div>
                    
                    <div class="carousel-slide-controls">
                        <button type="button" class="carousel-control-btn edit" onclick="event.stopPropagation(); editSlide(${index})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="carousel-control-btn delete" onclick="event.stopPropagation(); removeSlide(${index})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="carousel-slide-status">
                        <span class="badge ${slide.active ? 'bg-success' : 'bg-secondary'}">${slide.active ? 'Activa' : 'Inactiva'}</span>
                    </div>
                </div>
            `).join('');
        }
        
        // Previsualizar imágenes del carrusel cuando se suben
        async function previewCarouselImages(input) {
            if (!input.files || input.files.length === 0) return;
            
            const files = Array.from(input.files);
            
            for (const file of files) {
                // Validar archivo
                if (!file.type.startsWith('image/')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo Inválido',
                        text: `${file.name} no es una imagen válida`
                    });
                    continue;
                }
                
                if (file.size > 8 * 1024 * 1024) { // 8MB
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo Muy Grande',
                        text: `${file.name} excede el tamaño máximo de 8MB`
                    });
                    continue;
                }
                
                // Crear preview inmediato
                const reader = new FileReader();
                reader.onload = function(e) {
                    const newSlide = {
                        id: Date.now() + Math.random(),
                        image: e.target.result,
                        title: file.name.split('.')[0],
                        subtitle: '',
                        location: 'CLL 3 20A39 Madrid, Cundinamarca',
                        buttonText: '',
                        buttonLink: '',
                        active: carouselSlides.length === 0, // Primera imagen activa por defecto
                        order: carouselSlides.length + 1,
                        isNew: true,
                        file: file // Guardar referencia al archivo para upload posterior
                    };
                    
                    carouselSlides.push(newSlide);
                    renderCarouselPreview();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Imagen Agregada',
                        text: `${file.name} se agregó al carrusel`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                };
                reader.readAsDataURL(file);
            }
            
            // Limpiar input
            input.value = '';
        }
        
        // Configurar drag & drop para área de upload del carrusel
        function setupCarouselUploadDragDrop() {
            const uploadArea = document.querySelector('#carouselImages');
            if (!uploadArea) return;
            
            const uploadAreaParent = uploadArea.parentElement.querySelector('.upload-area');
            if (!uploadAreaParent) return;
            
            uploadAreaParent.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadAreaParent.classList.add('drag-over');
            });
            
            uploadAreaParent.addEventListener('dragleave', (e) => {
                e.preventDefault();
                uploadAreaParent.classList.remove('drag-over');
            });
            
            uploadAreaParent.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadAreaParent.classList.remove('drag-over');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const input = document.getElementById('carouselImages');
                    input.files = files;
                    previewCarouselImages(input);
                }
            });
        }
        
        // Seleccionar slide para editar
        function selectSlide(index) {
            selectedSlideIndex = index;
            populateSlideConfig(carouselSlides[index]);
            document.getElementById('slideConfigPanel').style.display = 'block';
            renderCarouselPreview();
        }
        
        // Rellenar formulario de configuración de slide
        function populateSlideConfig(slide) {
            document.getElementById('currentSlideIndex').value = selectedSlideIndex;
            document.getElementById('slideTitle').value = slide.title || '';
            document.getElementById('slideSubtitle').value = slide.subtitle || '';
            document.getElementById('slideLocation').value = slide.location || '';
            document.getElementById('slideButtonText').value = slide.buttonText || '';
            document.getElementById('slideButtonLink').value = slide.buttonLink || '';
            document.getElementById('slideActive').checked = slide.active || false;
        }
        
        // Editar slide
        function editSlide(index) {
            selectSlide(index);
        }
        
        // Guardar configuración de slide
        function saveSlideConfig() {
            const index = parseInt(document.getElementById('currentSlideIndex').value);
            if (index < 0 || index >= carouselSlides.length) return;
            
            const isActive = document.getElementById('slideActive').checked;
            
            // Si se marca como activa, desactivar las demás
            if (isActive) {
                carouselSlides.forEach(slide => slide.active = false);
            }
            
            carouselSlides[index] = {
                ...carouselSlides[index],
                title: document.getElementById('slideTitle').value,
                subtitle: document.getElementById('slideSubtitle').value,
                location: document.getElementById('slideLocation').value,
                buttonText: document.getElementById('slideButtonText').value,
                buttonLink: document.getElementById('slideButtonLink').value,
                active: isActive
            };
            
            renderCarouselPreview();
            cancelSlideConfig();
            
            Swal.fire({
                icon: 'success',
                title: 'Diapositiva Actualizada',
                text: 'Los cambios se han aplicado correctamente',
                timer: 2000,
                showConfirmButton: false
            });
        }
        
        // Cancelar edición de slide
        function cancelSlideConfig() {
            selectedSlideIndex = -1;
            document.getElementById('slideConfigPanel').style.display = 'none';
            document.getElementById('slideConfigForm').reset();
            renderCarouselPreview();
        }
        
        // Eliminar slide
        function removeSlide(index) {
            Swal.fire({
                title: '¿Eliminar diapositiva?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    carouselSlides.splice(index, 1);
                    
                    // Si se eliminó la diapositiva activa y hay más, activar la primera
                    if (carouselSlides.length > 0 && !carouselSlides.some(slide => slide.active)) {
                        carouselSlides[0].active = true;
                    }
                    
                    selectedSlideIndex = -1;
                    document.getElementById('slideConfigPanel').style.display = 'none';
                    renderCarouselPreview();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Diapositiva Eliminada',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        // Manejo de drag and drop para reordenar
        let draggedSlideIndex = -1;
        
        function handleDragStart(event, index) {
            draggedSlideIndex = index;
            event.target.classList.add('dragging');
            event.dataTransfer.effectAllowed = 'move';
        }
        
        function handleDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
        }
        
        function handleDrop(event, targetIndex) {
            event.preventDefault();
            
            if (draggedSlideIndex === -1 || draggedSlideIndex === targetIndex) return;
            
            // Reordenar slides
            const draggedSlide = carouselSlides[draggedSlideIndex];
            carouselSlides.splice(draggedSlideIndex, 1);
            carouselSlides.splice(targetIndex, 0, draggedSlide);
            
            // Actualizar órdenes
            carouselSlides.forEach((slide, index) => {
                slide.order = index + 1;
            });
            
            draggedSlideIndex = -1;
            renderCarouselPreview();
            
            Swal.fire({
                icon: 'success',
                title: 'Orden Actualizado',
                text: 'Las diapositivas se han reordenado',
                timer: 2000,
                showConfirmButton: false
            });
        }
        
        // Guardar cambios del carrusel
        async function saveCarouselChanges() {
            try {
                Swal.fire({
                    title: 'Guardando cambios...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Subir nuevas imágenes primero
                for (let i = 0; i < carouselSlides.length; i++) {
                    const slide = carouselSlides[i];
                    if (slide.file && slide.isNew) {
                        console.log(`📤 Subiendo imagen ${i + 1}/${carouselSlides.length}: ${slide.file.name}`);
                        
                        const formData = new FormData();
                        formData.append('image', slide.file);
                        formData.append('action', 'upload_image');
                        
                        const uploadResponse = await fetch('api/carousel-manager.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const uploadResult = await uploadResponse.json();
                        
                        if (uploadResult.success) {
                            // Actualizar la ruta de la imagen
                            carouselSlides[i].image = uploadResult.path;
                            delete carouselSlides[i].file;
                            delete carouselSlides[i].isNew;
                            console.log(`✅ Imagen subida: ${uploadResult.path}`);
                        } else {
                            throw new Error(`Error subiendo ${slide.file.name}: ${uploadResult.message}`);
                        }
                    }
                }
                
                // Ahora guardar todos los slides
                const response = await fetch('api/carousel-manager.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slides: carouselSlides })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cambios Guardados',
                        text: 'El carrusel se ha actualizado exitosamente',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    console.log('💾 Carrusel guardado exitosamente');
                    await loadCurrentCarouselSlides(); // Recargar datos
                } else {
                    throw new Error(result.message || 'Error desconocido al guardar carrusel');
                }
                
            } catch (error) {
                console.error('❌ Error guardando carrusel:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Guardar',
                    text: error.message || 'No se pudieron guardar los cambios del carrusel'
                });
            }
        }
        
        // Descartar cambios del carrusel
        function resetCarouselChanges() {
            Swal.fire({
                title: '¿Descartar cambios?',
                text: 'Se perderán todos los cambios no guardados',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, descartar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    loadCurrentCarouselSlides();
                    cancelSlideConfig();
                    
                    Swal.fire({
                        icon: 'info',
                        title: 'Cambios Descartados',
                        text: 'Se ha restaurado el carrusel original',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
        
        // Previsualizar carrusel en el sitio web
        function previewCarousel() {
            const newWindow = window.open('index.html', '_blank');
            if (newWindow) {
                Swal.fire({
                    icon: 'info',
                    title: 'Vista Previa',
                    text: 'El sitio web se abrió en una nueva pestaña',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }
        
        // Cargar configuraciones generales
        async function loadGeneralSettings() {
            try {
                console.log('📊 Cargando configuraciones generales...');
                
                const response = await fetch('api/settings.php');
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success && result.settings) {
                    const settings = result.settings;
                    
                    // Cargar valores en los campos del formulario
                    document.getElementById('companyName').value = settings.company_name || 'M & A MODA ACTUAL';
                    document.getElementById('companyAddress').value = settings.company_address || 'CLL 3 20A39 Madrid, Cundinamarca';
                    document.getElementById('companyPhone').value = settings.company_phone || '';
                    document.getElementById('companyEmail').value = settings.company_email || '';
                    document.getElementById('siteTitle').value = settings.site_title || 'M & A MODA ACTUAL';
                    document.getElementById('siteDescription').value = settings.site_description || '';
                    document.getElementById('siteMaintenanceMode').checked = Boolean(settings.site_maintenance_mode);
                    document.getElementById('allowRegistrations').checked = Boolean(settings.allow_registrations);
                    
                    console.log('✅ Configuraciones cargadas:', settings);
                } else {
                    throw new Error(result.message || 'Error al cargar configuraciones');
                }
                
            } catch (error) {
                console.error('❌ Error cargando configuraciones:', error);
                
                // No mostrar error al usuario, usar valores por defecto
                console.log('📝 Usando valores por defecto');
            }
        }
        
        // Guardar configuraciones generales
        async function saveGeneralSettings() {
            try {
                console.log('💾 Guardando configuraciones generales...');
                
                // Obtener valores del formulario
                const settings = {
                    company_name: document.getElementById('companyName').value.trim(),
                    company_address: document.getElementById('companyAddress').value.trim(),
                    company_phone: document.getElementById('companyPhone').value.trim(),
                    company_email: document.getElementById('companyEmail').value.trim(),
                    site_title: document.getElementById('siteTitle').value.trim(),
                    site_description: document.getElementById('siteDescription').value.trim(),
                    site_maintenance_mode: document.getElementById('siteMaintenanceMode').checked,
                    allow_registrations: document.getElementById('allowRegistrations').checked
                };
                
                // Validaciones básicas
                if (!settings.company_name) {
                    throw new Error('El nombre de la empresa es requerido');
                }
                
                if (!settings.site_title) {
                    throw new Error('El título del sitio es requerido');
                }
                
                if (settings.company_email && !isValidEmail(settings.company_email)) {
                    throw new Error('El email de la empresa no es válido');
                }
                
                // Mostrar loading
                Swal.fire({
                    title: 'Guardando configuraciones...',
                    text: 'Por favor espera mientras se guardan los cambios.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar datos al servidor
                const response = await fetch('api/settings.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ settings })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const result = await response.json();
                
                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Configuraciones Guardadas!',
                        text: result.message || 'Los cambios se han aplicado correctamente',
                        timer: 3000,
                        showConfirmButton: false
                    });
                    
                    console.log('✅ Configuraciones guardadas correctamente:', result);
                } else {
                    throw new Error(result.message || 'Error al guardar configuraciones');
                }
                
            } catch (error) {
                console.error('❌ Error guardando configuraciones:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Guardar',
                    text: error.message || 'No se pudieron guardar las configuraciones. Inténtalo de nuevo.',
                    confirmButtonText: 'Entendido'
                });
            }
        }
        
        // Función auxiliar para validar email
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
        
        // Inicializar gestión de carrusel cuando se abre la pestaña de configuración
        document.addEventListener('DOMContentLoaded', function() {
            // Detectar si ya estamos en la pestaña de configuración al cargar
            const configTab = document.getElementById('v-pills-configuracion-tab');
            if (configTab && configTab.classList.contains('active')) {
                console.log('🔧 Pestaña de configuración activa al cargar - inicializando...');
                loadCurrentCarouselSlides();
                setupCarouselUploadDragDrop();
                loadGeneralSettings(); // Cargar configuraciones generales
            }
        });
    </script>
</body>
</html>
