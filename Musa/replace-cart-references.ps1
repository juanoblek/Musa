# Script para reemplazar todas las referencias de 'cart' por 'carrito' en index.html
$filePath = "c:\xampp\htdocs\Musa\index.html"

# Leer el contenido del archivo
$content = Get-Content $filePath -Raw

# Reemplazar todas las referencias
$content = $content -replace "localStorage\.getItem\('cart'\)", "localStorage.getItem('carrito')"
$content = $content -replace "localStorage\.setItem\('cart'", "localStorage.setItem('carrito'"
$content = $content -replace "localStorage\.removeItem\('cart'\)", "localStorage.removeItem('carrito')"

# Escribir el contenido actualizado
Set-Content $filePath $content -Encoding UTF8

Write-Host "✅ Reemplazos completados en index.html"
Write-Host "- localStorage.getItem('cart') → localStorage.getItem('carrito')"
Write-Host "- localStorage.setItem('cart' → localStorage.setItem('carrito'"
Write-Host "- localStorage.removeItem('cart') → localStorage.removeItem('carrito')"
