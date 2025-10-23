#!/bin/bash

echo "🧹 LIMPIEZA AUTOMÁTICA PARA CPANEL"
echo "=================================="

# Directorio base (ajustar según tu estructura)
BASE_DIR="public_html/Musa"

echo "📍 Trabajando en: $BASE_DIR"

# Función para logging
log_action() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1"
}

# Cambiar al directorio correcto
cd ~/$BASE_DIR || { echo "❌ Error: No se puede acceder al directorio $BASE_DIR"; exit 1; }

log_action "🔐 Arreglando permisos..."

# Arreglar permisos de carpetas y archivos
find . -type d -exec chmod 755 {} \; 2>/dev/null
find . -type f -exec chmod 644 {} \; 2>/dev/null

log_action "✅ Permisos arreglados"

# Eliminar archivos temporales y de respaldo
log_action "🗑️ Eliminando archivos temporales..."

# Archivos temporales comunes
find . -name "*.tmp" -delete 2>/dev/null
find . -name "*.temp" -delete 2>/dev/null
find . -name "*.bak" -delete 2>/dev/null
find . -name "*.backup" -delete 2>/dev/null
find . -name "*.old" -delete 2>/dev/null
find . -name ".DS_Store" -delete 2>/dev/null
find . -name "Thumbs.db" -delete 2>/dev/null

log_action "🗂️ Eliminando carpetas problemáticas..."

# Eliminar carpetas problemáticas si existen
rm -rf node_modules 2>/dev/null
rm -rf .git 2>/dev/null
rm -rf .vscode 2>/dev/null
rm -rf backend/node_modules 2>/dev/null

log_action "📁 Eliminando directorios vacíos..."

# Eliminar directorios vacíos (ejecutar varias veces para directorios anidados)
for i in {1..3}; do
    find . -type d -empty -delete 2>/dev/null
done

log_action "🧹 Limpiando archivos de log antiguos..."

# Eliminar logs antiguos (más de 30 días)
find . -name "*.log" -mtime +30 -delete 2>/dev/null

log_action "📊 Mostrando estadísticas finales..."

# Mostrar estadísticas
echo ""
echo "📈 ESTADÍSTICAS:"
echo "Archivos totales: $(find . -type f | wc -l)"
echo "Directorios totales: $(find . -type d | wc -l)"
echo "Tamaño total: $(du -sh . | cut -f1)"

echo ""
echo "📋 CARPETAS MÁS PESADAS:"
du -sh */ 2>/dev/null | sort -hr | head -5

echo ""
echo "✅ LIMPIEZA COMPLETADA"
echo "Intenta ahora eliminar carpetas desde cPanel File Manager"
