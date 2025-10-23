# Script para eliminar secciones de garantías
$filePath = "c:\xampp\htdocs\Musa\Musa\index.html"

# Leer el contenido del archivo
$content = Get-Content $filePath -Raw -Encoding UTF8

# Patrón para encontrar y eliminar las secciones de garantías
$pattern = '(?s)<!-- Garant[íi]as destacadas -->\s*<div class="guarantees mt-3">.*?</div>\s*</div>'
$replacement = '<!-- Sección de garantías eliminada -->'

# Reemplazar todas las ocurrencias
$newContent = $content -replace $pattern, $replacement

# Guardar el archivo
$newContent | Set-Content $filePath -Encoding UTF8

Write-Host "✅ Secciones de garantías eliminadas del archivo index.html"
Write-Host "🔍 Verificando cambios..."

# Contar cuántas secciones de garantías quedan
$remaining = ($newContent | Select-String -Pattern "guarantees mt-3" -AllMatches).Matches.Count
Write-Host "📊 Secciones de garantías restantes: $remaining"