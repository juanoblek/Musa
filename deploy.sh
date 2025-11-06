#!/bin/bash

# 🚀 Script de despliegue automático - Musa E-commerce
# Este script se ejecuta cada vez que haces git push

echo "🔄 Iniciando despliegue..."

# Variables (AJUSTAR según tu hosting)
REPO_DIR="$HOME/repos/musa-ecommerce"
PUBLIC_DIR="$HOME/public_html"
BACKUP_DIR="$HOME/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Crear directorios si no existen
mkdir -p "$BACKUP_DIR"
mkdir -p "$PUBLIC_DIR"

# Crear backup antes de desplegar
echo "💾 Creando backup..."
if [ -d "$PUBLIC_DIR/Musa" ]; then
    tar -czf "$BACKUP_DIR/backup_$TIMESTAMP.tar.gz" -C "$PUBLIC_DIR" Musa/ 2>/dev/null || true
    echo "✅ Backup creado: backup_$TIMESTAMP.tar.gz"
fi

# Ir al repositorio
cd "$REPO_DIR" || exit 1

# Actualizar desde GitHub
echo "📥 Descargando última versión desde GitHub..."
git fetch origin main
git reset --hard origin/main
echo "✅ Código actualizado a último commit"

# Copiar archivos al directorio público (excluyendo sensibles)
echo "📋 Copiando archivos a public_html..."
rsync -av --delete \
  --exclude='.git' \
  --exclude='.gitignore' \
  --exclude='logs/' \
  --exclude='test-*.json' \
  --exclude='.env' \
  --exclude='config/config-global.php' \
  --exclude='README.md' \
  --exclude='*.md' \
  "$REPO_DIR/Musa/" "$PUBLIC_DIR/Musa/"

echo "✅ Archivos copiados"

# Establecer permisos correctos
echo "🔐 Configurando permisos..."
find "$PUBLIC_DIR/Musa" -type d -exec chmod 755 {} \; 2>/dev/null
find "$PUBLIC_DIR/Musa" -type f -exec chmod 644 {} \; 2>/dev/null
chmod 755 "$PUBLIC_DIR/Musa/api/"*.php 2>/dev/null || true
echo "✅ Permisos configurados"

# Mantener solo últimos 5 backups
echo "🧹 Limpiando backups antiguos..."
cd "$BACKUP_DIR" || exit 1
ls -t backup_*.tar.gz 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
echo "✅ Backups antiguos eliminados"

echo ""
echo "✅ ¡Despliegue completado exitosamente!"
echo "📊 Archivos desplegados en: $PUBLIC_DIR/Musa/"
echo "💾 Backup guardado en: $BACKUP_DIR/backup_$TIMESTAMP.tar.gz"
echo "🕐 Hora: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""
