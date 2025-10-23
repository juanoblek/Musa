<?php
// api/get-carousel-html.php - Genera el HTML del carrusel dinámicamente

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'musa_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener slides del carrusel
    $stmt = $pdo->prepare("SELECT * FROM carousel_slides ORDER BY slide_order ASC, id ASC");
    $stmt->execute();
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si no hay slides, usar los predeterminados
    if (empty($slides)) {
        $slides = [
            [
                'image_path' => 'images/slide/Coleccion2.png',
                'title' => 'Musa',
                'subtitle' => '',
                'location' => 'CLL 3 20A39 Madrid, Cundinamarca',
                'button_text' => '',
                'button_link' => '',
                'is_active' => 0
            ],
            [
                'image_path' => 'images/slide/Coleccion3.png',
                'title' => 'Arion',
                'subtitle' => '',
                'location' => '',
                'button_text' => '',
                'button_link' => '',
                'is_active' => 1
            ],
            [
                'image_path' => 'images/slide/Coleccion1.png',
                'title' => 'MA',
                'subtitle' => '',
                'location' => '',
                'button_text' => '',
                'button_link' => '',
                'is_active' => 0
            ]
        ];
    }
    
    // Asegurar que al menos un slide esté activo
    $hasActiveSlide = false;
    foreach ($slides as $slide) {
        if ($slide['is_active']) {
            $hasActiveSlide = true;
            break;
        }
    }
    
    if (!$hasActiveSlide && !empty($slides)) {
        $slides[0]['is_active'] = 1; // Activar el primer slide si ninguno está activo
    }
    
} catch (PDOException $e) {
    // En caso de error, usar slides predeterminados
    error_log("Error cargando carrusel: " . $e->getMessage());
    $slides = [
        [
            'image_path' => 'images/slide/Coleccion2.png',
            'title' => 'Musa',
            'subtitle' => '',
            'location' => 'CLL 3 20A39 Madrid, Cundinamarca',
            'button_text' => '',
            'button_link' => '',
            'is_active' => 0
        ],
        [
            'image_path' => 'images/slide/Coleccion3.png',
            'title' => 'Arion',
            'subtitle' => '',
            'location' => '',
            'button_text' => '',
            'button_link' => '',
            'is_active' => 1
        ],
        [
            'image_path' => 'images/slide/Coleccion1.png',
            'title' => 'MA',
            'subtitle' => '',
            'location' => '',
            'button_text' => '',
            'button_link' => '',
            'is_active' => 0
        ]
    ];
}

// Generar HTML del carrusel
echo '<div class="carousel-inner">' . "\n";

foreach ($slides as $index => $slide) {
    $activeClass = $slide['is_active'] ? 'active' : '';
    $imagePath = htmlspecialchars($slide['image_path']);
    $title = htmlspecialchars($slide['title']);
    $subtitle = htmlspecialchars($slide['subtitle']);
    $location = htmlspecialchars($slide['location']);
    $buttonText = htmlspecialchars($slide['button_text']);
    $buttonLink = htmlspecialchars($slide['button_link']);
    
    echo '    <div class="carousel-item ' . $activeClass . '">' . "\n";
    echo '        <div class="carousel-image-wrap">' . "\n";
    echo '            <img src="' . $imagePath . '" class="d-block w-100" alt="' . $title . '">' . "\n";
    echo '        </div>' . "\n";
    echo '        <div class="carousel-caption d-none d-md-block">' . "\n";
    
    // Mostrar ubicación si existe
    if (!empty($location)) {
        echo '            <span class="text-white">' . "\n";
        echo '                <i class="bi-geo-alt me-2"></i>' . $location . "\n";
        echo '            </span>' . "\n";
    }
    
    // Mostrar título si existe
    if (!empty($title)) {
        echo '            <h4 class="hero-text">' . $title . '</h4>' . "\n";
    }
    
    // Mostrar subtítulo si existe
    if (!empty($subtitle)) {
        echo '            <h5>' . $subtitle . '</h5>' . "\n";
    }
    
    // Mostrar botón si existe texto y enlace
    if (!empty($buttonText) && !empty($buttonLink)) {
        echo '            <a href="' . $buttonLink . '" class="btn btn-primary btn-lg mt-3">' . $buttonText . '</a>' . "\n";
    }
    
    echo '        </div>' . "\n";
    echo '    </div>' . "\n\n";
}

echo '</div>' . "\n";
?>