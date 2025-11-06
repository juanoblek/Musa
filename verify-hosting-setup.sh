#!/bin/bash

# Script de verificación de configuración de hosting

echo "🔍 Verificando configuración de hosting..."

# Verificar archivos de configuración
echo "📁 Verificando archivos de configuración..."
files=(
    "config/config-hosting.php"
    "config/database-hosting.php"
    "config/mercadopago-hosting.php"
)

for file in "${files[@]}"; do
    if [ -f "$file" ]; then
        echo "✅ $file existe"
    else
        echo "❌ $file no existe"
    fi
done

# Verificar permisos de directorios
echo -e "\n📂 Verificando permisos de directorios..."
directories=(
    "uploads"
    "logs"
    "cache"
)

for dir in "${directories[@]}"; do
    if [ -d "$dir" ]; then
        chmod 755 "$dir"
        echo "✅ $dir permisos establecidos a 755"
    else
        echo "❌ $dir no existe"
    fi
done

# Verificar archivos críticos
echo -e "\n🔒 Verificando archivos críticos..."
chmod 644 config/config-hosting.php
chmod 644 config/database-hosting.php
echo "✅ Permisos de archivos de configuración establecidos"

echo -e "\n✅ Verificación completada"