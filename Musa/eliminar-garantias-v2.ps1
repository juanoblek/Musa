# Script mejorado para eliminar secciones de garantías
$filePath = "c:\xampp\htdocs\Musa\Musa\index.html"

Write-Host "🔧 Eliminando secciones de garantías..."

# Leer el contenido completo
$content = Get-Content $filePath -Raw -Encoding UTF8

# Patrón simple para eliminar divs de garantías
$pattern1 = '<div class="guarantees mt-3">[\s\S]*?</div>[\s\S]*?</div>'
$pattern2 = '<!-- Garant[íiÃ­]as destacadas -->'

# Hacer los reemplazos
$newContent = $content -replace $pattern1, '<!-- Sección de garantías eliminada -->'
$newContent = $newContent -replace $pattern2, '<!-- Sección de garantías eliminada -->'

# Guardar el archivo
$newContent | Set-Content $filePath -Encoding UTF8

Write-Host "✅ Procesamiento completado"

# Verificar cambios
$remaining = ($newContent | Select-String -Pattern "guarantees mt-3" -AllMatches).Matches.Count
Write-Host "📊 Secciones de garantías restantes: $remaining"