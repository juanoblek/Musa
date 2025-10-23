# ========================================
# 📦 PREPARAR ARCHIVOS PARA HOSTING
# ========================================
# Script para crear paquete listo para subir al hosting

Write-Host "🚀 PREPARANDO ARCHIVOS PARA HOSTING - MUSA MODA" -ForegroundColor Green
Write-Host "=================================================" -ForegroundColor Green

# Crear carpeta de salida
$outputDir = "HOSTING-READY"
$timestamp = Get-Date -Format "yyyy-MM-dd_HH-mm"
$finalDir = "$outputDir-$timestamp"

if (Test-Path $outputDir) {
    Remove-Item $outputDir -Recurse -Force
}
New-Item -ItemType Directory -Path $outputDir -Force | Out-Null

Write-Host "📁 Creando carpeta: $outputDir" -ForegroundColor Yellow

# ===== ARCHIVOS PRINCIPALES =====
Write-Host "📋 Copiando archivos principales..." -ForegroundColor Cyan

$mainFiles = @(
    "index.html",
    "admin-panel.html", 
    "pago-premium.html",
    "install-hosting.php",
    ".htaccess",
    "success.html",
    "failure.html", 
    "pending.html"
)

foreach ($file in $mainFiles) {
    if (Test-Path $file) {
        Copy-Item $file $outputDir
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ FALTA: $file" -ForegroundColor Red
    }
}

# ===== CARPETAS CRÍTICAS =====
Write-Host "📂 Copiando carpetas críticas..." -ForegroundColor Cyan

$folders = @(
    "config",
    "api", 
    "assets",
    "images",
    "php",
    "classes"
)

foreach ($folder in $folders) {
    if (Test-Path $folder) {
        Copy-Item $folder $outputDir -Recurse
        $fileCount = (Get-ChildItem "$outputDir\$folder" -Recurse -File).Count
        Write-Host "  ✅ $folder ($fileCount archivos)" -ForegroundColor Green
    } else {
        Write-Host "  ⚠️  $folder no encontrada" -ForegroundColor Yellow
    }
}

# ===== ELIMINAR ARCHIVOS NO DESEADOS =====
Write-Host "🗑️  Eliminando archivos no deseados..." -ForegroundColor Cyan

$unwantedPatterns = @(
    "*.md",
    "*.ps1", 
    "debug-*",
    "test-*",
    "temp-*",
    "backup-*",
    "*-test.php",
    "*-local.php"
)

foreach ($pattern in $unwantedPatterns) {
    $files = Get-ChildItem $outputDir -Recurse -File -Name $pattern -ErrorAction SilentlyContinue
    foreach ($file in $files) {
        Remove-Item "$outputDir\$file" -Force
        Write-Host "  🗑️  Eliminado: $file" -ForegroundColor Yellow
    }
}

# ===== VERIFICAR ARCHIVOS CRÍTICOS =====
Write-Host "🔍 Verificando archivos críticos..." -ForegroundColor Cyan

$criticalFiles = @(
    "config\config-global.php",
    "config\database.php",
    "config\mercadopago.php", 
    "api\productos.php",
    "api\create-preference-premium.php",
    "install-hosting.php"
)

$allPresent = $true
foreach ($file in $criticalFiles) {
    if (Test-Path "$outputDir\$file") {
        Write-Host "  ✅ $file" -ForegroundColor Green
    } else {
        Write-Host "  ❌ CRÍTICO FALTANTE: $file" -ForegroundColor Red
        $allPresent = $false
    }
}

# ===== CREAR ZIP =====
if ($allPresent) {
    Write-Host "📦 Creando archivo ZIP..." -ForegroundColor Cyan
    
    $zipName = "MUSA-HOSTING-$timestamp.zip"
    Compress-Archive -Path "$outputDir\*" -DestinationPath $zipName -Force
    
    Write-Host "✅ ZIP creado: $zipName" -ForegroundColor Green
    
    # Renombrar carpeta final
    Rename-Item $outputDir $finalDir
    
    # ===== RESUMEN FINAL =====
    Write-Host ""
    Write-Host "🎉 PREPARACIÓN COMPLETADA" -ForegroundColor Green
    Write-Host "========================" -ForegroundColor Green
    Write-Host "📁 Carpeta: $finalDir" -ForegroundColor White
    Write-Host "📦 Archivo: $zipName" -ForegroundColor White
    
    $totalFiles = (Get-ChildItem $finalDir -Recurse -File).Count
    $totalSize = [math]::Round((Get-ChildItem $finalDir -Recurse -File | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    
    Write-Host "📊 Total archivos: $totalFiles" -ForegroundColor White
    Write-Host "📏 Tamaño total: $totalSize MB" -ForegroundColor White
    Write-Host ""
    Write-Host "🚀 INSTRUCCIONES:" -ForegroundColor Yellow
    Write-Host "1. Sube el contenido de '$finalDir' a tu hosting" -ForegroundColor White
    Write-Host "2. O sube '$zipName' y extráelo en public_html/" -ForegroundColor White
    Write-Host "3. Ejecuta: https://tu-dominio.com/install-hosting.php" -ForegroundColor White
    Write-Host "4. Sigue las instrucciones del instalador" -ForegroundColor White
    Write-Host ""
    Write-Host "✅ LISTO PARA HOSTING! 🚀" -ForegroundColor Green
    
} else {
    Write-Host ""
    Write-Host "❌ ERROR: Faltan archivos críticos" -ForegroundColor Red
    Write-Host "No se puede crear el paquete para hosting" -ForegroundColor Red
    Write-Host "Revisa los archivos faltantes arriba" -ForegroundColor Red
}

Write-Host ""
Write-Host "Presiona cualquier tecla para continuar..." -ForegroundColor Gray
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")