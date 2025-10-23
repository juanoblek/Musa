param(
    [string]$filePath = "c:\xampp\htdocs\Musa\index.html"
)

# Leer el contenido del archivo
$content = Get-Content $filePath -Raw

# Corregir los estilos CSS malformados
$content = $content -replace 'rgba\(241, 235, 235, 0\.856\)0\.856\)', 'rgba(241, 235, 235, 0.856)'

# Escribir el contenido corregido
$content | Set-Content $filePath -NoNewline

Write-Host "✅ Errores CSS corregidos en $filePath"
