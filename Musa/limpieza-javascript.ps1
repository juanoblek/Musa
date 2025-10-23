# Limpieza Específica de Archivos JavaScript
# Elimina archivos duplicados, respaldos y de debug en la carpeta js

Write-Host "🔧 LIMPIEZA DE ARCHIVOS JAVASCRIPT" -ForegroundColor Green
Write-Host "==================================" -ForegroundColor Green

$jsPath = "c:\xampp\htdocs\Musa\js"
$totalSize = 0
$count = 0

function Remove-JSFile {
    param($fileName, $reason)
    
    $filePath = Join-Path $jsPath $fileName
    if (Test-Path $filePath) {
        $size = (Get-Item $filePath).Length
        Remove-Item $filePath -Force
        $script:totalSize += $size
        $script:count++
        Write-Host "✅ Eliminado: $fileName ($reason)" -ForegroundColor Yellow
    }
}

Write-Host "`n🗑️ Eliminando archivos de respaldo..." -ForegroundColor Cyan
Remove-JSFile "product-sync.js.backup" "Respaldo de product-sync.js"
Remove-JSFile "hybrid-products.js.backup" "Respaldo de hybrid-products.js"
Remove-JSFile "sample-products.js.backup" "Respaldo de sample-products.js"

Write-Host "`n🛠️ Eliminando archivos de debug y diagnóstico..." -ForegroundColor Cyan
Remove-JSFile "debug.js" "Archivo de debug"
Remove-JSFile "debug-productos-frontend.js" "Debug productos frontend"
Remove-JSFile "diagnostico.js" "Diagnóstico"
Remove-JSFile "diagnostico-completo.js" "Diagnóstico completo"

Write-Host "`n🚨 Eliminando archivos de emergencia y limpieza..." -ForegroundColor Cyan
Remove-JSFile "emergency-product-fix.js" "Fix de emergencia"
Remove-JSFile "eliminador-ultra-agresivo.js" "Eliminador ultra agresivo"
Remove-JSFile "limpiar-todo-estatico.js" "Limpiador estático"
Remove-JSFile "limpieza-emergencia.js" "Limpieza de emergencia"
Remove-JSFile "auto-clean-system.js" "Sistema auto-limpieza"

Write-Host "`n🔄 Eliminando archivos duplicados/redundantes..." -ForegroundColor Cyan
# Verificar si hay múltiples versiones del sistema admin
$adminFiles = @(
    "admin-system.js",
    "admin-system-integrated.js", 
    "admin-database-system.js",
    "admin-database-system-fixed.js",
    "simple-admin.js"
)

Write-Host "   📋 Archivos del sistema admin encontrados:" -ForegroundColor Yellow
foreach ($file in $adminFiles) {
    $filePath = Join-Path $jsPath $file
    if (Test-Path $filePath) {
        $size = [math]::Round((Get-Item $filePath).Length / 1KB, 1)
        Write-Host "   - $file ($size KB)" -ForegroundColor Gray
    }
}

# Eliminar versiones redundantes (manteniendo admin-panel.js como principal)
Remove-JSFile "admin-system-integrated.js" "Versión integrada redundante"
Remove-JSFile "admin-database-system-fixed.js" "Versión fixed redundante"
Remove-JSFile "simple-admin.js" "Versión simple redundante"

Write-Host "`n🔗 Eliminando archivos de sistema híbrido..." -ForegroundColor Cyan
Remove-JSFile "hybrid-system.js" "Sistema híbrido vacío"
Remove-JSFile "repair-system.js" "Sistema de reparación"

Write-Host "`n📦 Eliminando archivos de prueba y temporales..." -ForegroundColor Cyan
Remove-JSFile "modalTest.js" "Test de modales"

Write-Host "`n📊 RESUMEN DE LIMPIEZA JS" -ForegroundColor Green
Write-Host "========================" -ForegroundColor Green
Write-Host "Archivos eliminados: $count" -ForegroundColor White
Write-Host "Espacio liberado: $([math]::Round($totalSize/1KB,1)) KB" -ForegroundColor White

Write-Host "`n📋 ARCHIVOS JS PRINCIPALES MANTENIDOS:" -ForegroundColor Cyan
$keepFiles = @(
    "admin-panel.js",
    "admin-system.js",
    "admin-mysql-connection.js",
    "product-loader.js",
    "frontend-database.js",
    "jquery.min.js",
    "bootstrap.bundle.min.js"
)

foreach ($file in $keepFiles) {
    $filePath = Join-Path $jsPath $file
    if (Test-Path $filePath) {
        $size = [math]::Round((Get-Item $filePath).Length / 1KB, 1)
        Write-Host "✅ $file ($size KB)" -ForegroundColor Green
    } else {
        Write-Host "❌ $file (FALTANTE)" -ForegroundColor Red
    }
}

Write-Host "`n✅ LIMPIEZA JS COMPLETADA!" -ForegroundColor Green
