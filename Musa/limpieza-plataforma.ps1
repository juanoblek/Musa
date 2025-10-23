# Script de Limpieza de la Plataforma M & A MODA
# Elimina archivos innecesarios, temporales y de debug

Write-Host "🧹 INICIANDO LIMPIEZA DE LA PLATAFORMA" -ForegroundColor Green
Write-Host "=====================================" -ForegroundColor Green

$basePath = "c:\xampp\htdocs\Musa"
$totalDeleted = 0
$totalSize = 0

# Función para eliminar archivos de forma segura
function Remove-SafeFile {
    param($filePath, $description)
    
    if (Test-Path $filePath) {
        $size = (Get-Item $filePath).Length
        try {
            Remove-Item $filePath -Force
            $script:totalDeleted++
            $script:totalSize += $size
            Write-Host "✅ Eliminado: $description" -ForegroundColor Yellow
        } catch {
            Write-Host "❌ Error eliminando: $description - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

# Función para eliminar carpetas de forma segura
function Remove-SafeFolder {
    param($folderPath, $description)
    
    if (Test-Path $folderPath) {
        try {
            $size = (Get-ChildItem $folderPath -Recurse -File | Measure-Object -Property Length -Sum).Sum
            Remove-Item $folderPath -Recurse -Force
            $script:totalSize += $size
            Write-Host "✅ Carpeta eliminada: $description" -ForegroundColor Yellow
        } catch {
            Write-Host "❌ Error eliminando carpeta: $description - $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

Write-Host "`n📁 ELIMINANDO ARCHIVOS DE DEBUG Y PRUEBAS..." -ForegroundColor Cyan

# Archivos de debug
Remove-SafeFile "$basePath\debug-api-productos.php" "API Debug de productos"
Remove-SafeFile "$basePath\debug-api-simple.php" "API Debug simple"
Remove-SafeFile "$basePath\debug-button.html" "Debug de botones"
Remove-SafeFile "$basePath\debug-container.html" "Debug de contenedores"
Remove-SafeFile "$basePath\debug-eliminacion.html" "Debug de eliminación"
Remove-SafeFile "$basePath\debug-elimination.html" "Debug elimination"
Remove-SafeFile "$basePath\debug-error-500.php" "Debug de errores 500"
Remove-SafeFile "$basePath\debug-products-fix.php" "Debug fix productos"
Remove-SafeFile "$basePath\debug-products.php" "Debug productos"
Remove-SafeFile "$basePath\debug-simple-create.php" "Debug creación simple"
Remove-SafeFile "$basePath\debug-simple.php" "Debug simple"

# Archivos de test
Remove-SafeFile "$basePath\test-add-product.html" "Test agregar producto"
Remove-SafeFile "$basePath\test-api-final.html" "Test API final"
Remove-SafeFile "$basePath\test-api-response.html" "Test respuesta API"
Remove-SafeFile "$basePath\test-api.html" "Test API"
Remove-SafeFile "$basePath\test-api.php" "Test API PHP"
Remove-SafeFile "$basePath\test-communication.html" "Test comunicación"
Remove-SafeFile "$basePath\test-connection.php" "Test conexión"
Remove-SafeFile "$basePath\test-create-product.html" "Test crear producto"
Remove-SafeFile "$basePath\test-data.json" "Test data JSON"
Remove-SafeFile "$basePath\test-database.html" "Test base de datos"
Remove-SafeFile "$basePath\test-db-connection.php" "Test conexión DB"
Remove-SafeFile "$basePath\test-deletion.html" "Test eliminación"
Remove-SafeFile "$basePath\test-frontend-update.html" "Test actualización frontend"
Remove-SafeFile "$basePath\test-image-upload.html" "Test subida imágenes"
Remove-SafeFile "$basePath\test-images.html" "Test imágenes"
Remove-SafeFile "$basePath\test-insert.php" "Test inserción"
Remove-SafeFile "$basePath\test-product-complete.html" "Test producto completo"
Remove-SafeFile "$basePath\test-redirect.html" "Test redirección"
Remove-SafeFile "$basePath\test-simple-api.html" "Test API simple"
Remove-SafeFile "$basePath\test-sistema-final.html" "Test sistema final"
Remove-SafeFile "$basePath\test-upload.php" "Test upload"
Remove-SafeFile "$basePath\test-visual.html" "Test visual"
Remove-SafeFile "$basePath\admin-test.html" "Admin test"

Write-Host "`n🗑️ ELIMINANDO SCRIPTS DE LIMPIEZA Y ELIMINACIÓN..." -ForegroundColor Cyan

# Scripts de eliminación y limpieza
Remove-SafeFile "$basePath\clean-static-products.ps1" "Script limpiar productos estáticos"
Remove-SafeFile "$basePath\deep-clean-products.ps1" "Script limpieza profunda"
Remove-SafeFile "$basePath\eliminador-definitivo.html" "Eliminador definitivo HTML"
Remove-SafeFile "$basePath\eliminador-emergencia.js" "Eliminador de emergencia"
Remove-SafeFile "$basePath\eliminador-inmediato-consola.js" "Eliminador inmediato consola"
Remove-SafeFile "$basePath\eliminador-ultra-agresivo.js" "Eliminador ultra agresivo"
Remove-SafeFile "$basePath\eliminar-definitivo.html" "Eliminar definitivo HTML"
Remove-SafeFile "$basePath\eliminar-estaticos.js" "Eliminar estáticos JS"
Remove-SafeFile "$basePath\eliminar-productos-estaticos.ps1" "Eliminar productos estáticos"
Remove-SafeFile "$basePath\limpiar-archivos.ps1" "Script limpiar archivos"
Remove-SafeFile "$basePath\limpiar-productos-consola.js" "Limpiar productos consola"
Remove-SafeFile "$basePath\limpiar-productos-db-simple.ps1" "Limpiar productos DB simple"
Remove-SafeFile "$basePath\limpiar-productos-db.ps1" "Limpiar productos DB"
Remove-SafeFile "$basePath\limpiar-referencias-estaticas.ps1" "Limpiar referencias estáticas"
Remove-SafeFile "$basePath\remove-static-products.ps1" "Remover productos estáticos"
Remove-SafeFile "$basePath\destruccion-total.js" "Script destrucción total"
Remove-SafeFile "$basePath\force-update-products.js" "Forzar actualización productos"
Remove-SafeFile "$basePath\patch-definitivo-estaticos.js" "Patch definitivo estáticos"
Remove-SafeFile "$basePath\sobrescribir-productos.js" "Sobrescribir productos"
Remove-SafeFile "$basePath\temp_clear.js" "Limpieza temporal"

Write-Host "`n📄 ELIMINANDO ARCHIVOS DE RESPALDO Y DUPLICADOS..." -ForegroundColor Cyan

# Archivos de respaldo y duplicados
Remove-SafeFile "$basePath\index-backup.html" "Respaldo del index (422KB)"
Remove-SafeFile "$basePath\index.html.backup-final" "Respaldo final del index (345KB)"
Remove-SafeFile "$basePath\login-final.html" "Login final"
Remove-SafeFile "$basePath\limpieza-final.html" "Limpieza final"

Write-Host "`n🔧 ELIMINANDO SCRIPTS DE CONFIGURACIÓN TEMPORALES..." -ForegroundColor Cyan

# Scripts de setup temporales
Remove-SafeFile "$basePath\setup-simple.php" "Setup simple"
Remove-SafeFile "$basePath\setup.bat" "Setup batch"
Remove-SafeFile "$basePath\start.bat" "Start batch"
Remove-SafeFile "$basePath\server.py" "Servidor Python"
Remove-SafeFile "$basePath\install.php" "Instalador PHP"

Write-Host "`n📋 ELIMINANDO ARCHIVOS DE VERIFICACIÓN Y DIAGNÓSTICO..." -ForegroundColor Cyan

# Archivos de verificación y diagnóstico
Remove-SafeFile "$basePath\check-db.php" "Verificar DB"
Remove-SafeFile "$basePath\check-products-table.php" "Verificar tabla productos"
Remove-SafeFile "$basePath\check-table-structure.php" "Verificar estructura tablas"
Remove-SafeFile "$basePath\diagnostico-productos.html" "Diagnóstico productos HTML"
Remove-SafeFile "$basePath\diagnostico-productos.js" "Diagnóstico productos JS"
Remove-SafeFile "$basePath\info-database.php" "Info base de datos"
Remove-SafeFile "$basePath\investigate-table.php" "Investigar tabla"
Remove-SafeFile "$basePath\monitor-frontend.html" "Monitor frontend"
Remove-SafeFile "$basePath\redirect-test.html" "Test redirección"
Remove-SafeFile "$basePath\verify-db.php" "Verificar DB"
Remove-SafeFile "$basePath\verificar-archivos-problematicos.ps1" "Verificar archivos problemáticos"

Write-Host "`n🛠️ ELIMINANDO ARCHIVOS DE REPARACIÓN YA EJECUTADOS..." -ForegroundColor Cyan

# Scripts de reparación ya ejecutados
Remove-SafeFile "$basePath\fix-categories-table.php" "Fix tabla categorías"
Remove-SafeFile "$basePath\fix-foreign-key-constraint.php" "Fix constraint foreign key"
Remove-SafeFile "$basePath\fix-image-names.php" "Fix nombres imágenes"
Remove-SafeFile "$basePath\fix-productos-estaticos.html" "Fix productos estáticos"
Remove-SafeFile "$basePath\create-categories-table.php" "Crear tabla categorías"
Remove-SafeFile "$basePath\create-categories.sql" "SQL crear categorías"
Remove-SafeFile "$basePath\sync-images.php" "Sincronizar imágenes"
Remove-SafeFile "$basePath\update-images.sql" "Actualizar imágenes SQL"

Write-Host "`n📹 VERIFICANDO CARPETA DE VIDEOS..." -ForegroundColor Cyan

# Verificar si la carpeta video tiene archivos necesarios
if (Test-Path "$basePath\video") {
    $videoFiles = Get-ChildItem "$basePath\video" -File
    if ($videoFiles.Count -eq 0) {
        Remove-SafeFolder "$basePath\video" "Carpeta video vacía"
    } else {
        Write-Host "⚠️ Carpeta video contiene archivos (148MB) - revisar manualmente" -ForegroundColor Yellow
        Get-ChildItem "$basePath\video" -File | Select-Object Name, @{N="Size(MB)";E={[math]::Round($_.Length/1MB,2)}} | Format-Table
    }
}

Write-Host "`n🗂️ LIMPIANDO CARPETA UPLOADS..." -ForegroundColor Cyan

# Limpiar uploads de archivos temporales
if (Test-Path "$basePath\uploads") {
    $uploadsPath = "$basePath\uploads"
    Get-ChildItem $uploadsPath -File | Where-Object { 
        $_.Name -like "*temp*" -or 
        $_.Name -like "*test*" -or 
        $_.LastWriteTime -lt (Get-Date).AddDays(-30)
    } | ForEach-Object {
        Remove-SafeFile $_.FullName "Upload temporal: $($_.Name)"
    }
}

Write-Host "`n📊 RESUMEN DE LIMPIEZA" -ForegroundColor Green
Write-Host "===================" -ForegroundColor Green
Write-Host "Archivos eliminados: $totalDeleted" -ForegroundColor White
Write-Host "Espacio liberado: $([math]::Round($totalSize/1MB,2)) MB" -ForegroundColor White

Write-Host "`n✅ LIMPIEZA COMPLETADA!" -ForegroundColor Green
Write-Host "La plataforma debería ser mucho más rápida ahora." -ForegroundColor Green

# Mostrar archivos restantes importantes
Write-Host "`n📋 ARCHIVOS PRINCIPALES MANTENIDOS:" -ForegroundColor Cyan
$importantFiles = @(
    "index.html",
    "admin-panel.html", 
    "admin-panel.php",
    "login.php",
    "logout.php"
)

foreach ($file in $importantFiles) {
    if (Test-Path "$basePath\$file") {
        Write-Host "✅ $file" -ForegroundColor Green
    } else {
        Write-Host "❌ $file (FALTANTE)" -ForegroundColor Red
    }
}
