# Script para preparar archivos corregidos para FTP
Write-Host "🔍 Preparando archivos corregidos para subir al hosting..." -ForegroundColor Cyan

$sourceBase = "c:\xampp\htdocs\Musa\Musa"
$outputFolder = "c:\xampp\htdocs\Musa\ARCHIVOS-SUBIR-VIDEO-FIX"

# Crear carpeta de salida si no existe
if (-not (Test-Path $outputFolder)) {
    New-Item -Path $outputFolder -ItemType Directory -Force | Out-Null
    Write-Host "✅ Carpeta creada: $outputFolder" -ForegroundColor Green
}

# Archivos a copiar
$files = @(
    @{
        Source = "$sourceBase\js\force-video-conversion.js"
        Dest = "$outputFolder\force-video-conversion.js"
        FTPPath = "/public_html/js/force-video-conversion.js"
    },
    @{
        Source = "$sourceBase\index.php"
        Dest = "$outputFolder\index.php"
        FTPPath = "/public_html/index.php"
    }
)

Write-Host "`n📦 Copiando archivos..." -ForegroundColor Yellow

foreach ($file in $files) {
    if (Test-Path $file.Source) {
        Copy-Item -Path $file.Source -Destination $file.Dest -Force
        $size = (Get-Item $file.Dest).Length / 1KB
        Write-Host "  ✅ $(Split-Path $file.Dest -Leaf)" -ForegroundColor Green
        Write-Host "     Tamaño: $([math]::Round($size, 2)) KB" -ForegroundColor Gray
        Write-Host "     Destino FTP: $($file.FTPPath)" -ForegroundColor Gray
    } else {
        Write-Host "  ❌ No encontrado: $(Split-Path $file.Source -Leaf)" -ForegroundColor Red
    }
}

Write-Host "`n✅ Archivos listos en: $outputFolder" -ForegroundColor Green
Write-Host "`n📋 Próximos pasos:" -ForegroundColor Cyan
Write-Host "  1. Conectar via FTP a ftp.musaarion.com:21" -ForegroundColor White
Write-Host "  2. Usuario: usuario_musaarion_db@musaarion.com" -ForegroundColor White
Write-Host "  3. Subir los archivos a las rutas indicadas" -ForegroundColor White
Write-Host "  4. Limpiar caché del navegador (Ctrl+Shift+R)" -ForegroundColor White
Write-Host "  5. Verificar https://musaarion.com" -ForegroundColor White

Write-Host "`n⚠️  IMPORTANTE: Hacer backup de index.php antes de reemplazarlo!" -ForegroundColor Yellow

# Abrir carpeta de salida
Start-Process explorer.exe -ArgumentList $outputFolder
