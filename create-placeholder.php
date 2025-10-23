<?php
/**
 * Crear imagen placeholder simple
 */

// Crear una imagen placeholder simple
$width = 300;
$height = 300;

$image = imagecreate($width, $height);

// Colores
$white = imagecolorallocate($image, 255, 255, 255);
$gray = imagecolorallocate($image, 220, 220, 220);
$darkgray = imagecolorallocate($image, 100, 100, 100);

// Fondo
imagefill($image, 0, 0, $gray);

// Bordes
imagerectangle($image, 0, 0, $width-1, $height-1, $darkgray);

// Texto centrado
$text = "IMAGEN";
$text2 = "NO DISPONIBLE";

// Calcular posición centrada
$x1 = ($width - strlen($text) * 10) / 2;
$y1 = $height / 2 - 20;
$x2 = ($width - strlen($text2) * 10) / 2;
$y2 = $height / 2 + 10;

imagestring($image, 4, $x1, $y1, $text, $darkgray);
imagestring($image, 4, $x2, $y2, $text2, $darkgray);

// Asegurar que el directorio existe
$targetDir = __DIR__ . '/Musa/images/';
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$targetPath = $targetDir . 'placeholder.jpg';

// Guardar
if (imagejpeg($image, $targetPath, 80)) {
    echo "✅ Imagen placeholder creada: $targetPath<br>";
    echo "<img src='Musa/images/placeholder.jpg' alt='Placeholder' style='border: 1px solid #ccc;'>";
} else {
    echo "❌ Error creando imagen placeholder<br>";
}

imagedestroy($image);
?>