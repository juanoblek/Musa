#!/bin/bash
# Script para configurar permisos correctos en el hosting
# Ejecutar desde el directorio public_html via SSH o Terminal del hosting

echo "🔧 CONFIGURANDO PERMISOS PARA MUSA MODA..."

# Permisos para archivos PHP (ejecutables)
echo "📄 Configurando archivos PHP..."
find . -name "*.php" -exec chmod 755 {} \;

# Permisos para archivos HTML (legibles)
echo "🌐 Configurando archivos HTML..."
find . -name "*.html" -exec chmod 644 {} \;

# Permisos para archivos CSS y JS (legibles)
echo "🎨 Configurando archivos CSS y JS..."
find . -name "*.css" -exec chmod 644 {} \;
find . -name "*.js" -exec chmod 644 {} \;

# Permisos para imágenes (legibles)
echo "🖼️ Configurando imágenes..."
find . -name "*.jpg" -exec chmod 644 {} \;
find . -name "*.jpeg" -exec chmod 644 {} \;
find . -name "*.png" -exec chmod 644 {} \;
find . -name "*.gif" -exec chmod 644 {} \;
find . -name "*.webp" -exec chmod 644 {} \;

# Permisos para archivos de audio
echo "🔊 Configurando archivos de sonido..."
find . -name "*.mp3" -exec chmod 644 {} \;
find . -name "*.wav" -exec chmod 644 {} \;

# Permisos especiales para .htaccess
echo "⚙️ Configurando .htaccess..."
chmod 644 .htaccess

# Permisos para directorios
echo "📁 Configurando directorios..."
find . -type d -exec chmod 755 {} \;

# Permisos especiales para uploads (escritura)
echo "📤 Configurando directorio uploads..."
chmod 777 uploads/
chmod 777 uploads/products/

echo ""
echo "✅ PERMISOS CONFIGURADOS CORRECTAMENTE!"
echo ""
echo "📋 RESUMEN DE PERMISOS:"
echo "   📄 Archivos PHP: 755 (ejecutables)"
echo "   🌐 Archivos HTML: 644 (legibles)"
echo "   🎨 CSS/JS: 644 (legibles)"
echo "   🖼️ Imágenes: 644 (legibles)"
echo "   📁 Directorios: 755 (navegables)"
echo "   📤 Uploads: 777 (escritura completa)"
echo ""
echo "🎯 Ahora tu sitio debería funcionar correctamente!"