# Script de Limpieza Avanzada - Carpetas Pesadas Opcionales
# Elimina carpetas que no son esenciales para el funcionamiento de la plataforma

Write-Host "🚀 LIMPIEZA AVANZADA DE CARPETAS PESADAS" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green

$basePath = "c:\xampp\htdocs\Musa"

Write-Host "`n📊 ANÁLISIS DE ESPACIO ACTUAL:" -ForegroundColor Cyan
Get-ChildItem -Path $basePath -Directory | ForEach-Object { 
    $size = [math]::Round((Get-ChildItem $_.FullName -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    [PSCustomObject]@{ Carpeta = $_.Name; "Tamaño(MB)" = $size }
} | Sort-Object "Tamaño(MB)" -Descending | Format-Table -AutoSize

Write-Host "`n🔍 IDENTIFICANDO CARPETAS OPCIONALES..." -ForegroundColor Cyan

# Función para eliminar carpeta con confirmación
function Remove-OptionalFolder {
    param($folderPath, $description, $sizeMB, $reason)
    
    if (Test-Path $folderPath) {
        Write-Host "`n📁 $description" -ForegroundColor Yellow
        Write-Host "   Tamaño: $sizeMB MB" -ForegroundColor Gray
        Write-Host "   Razón: $reason" -ForegroundColor Gray
        
        $response = Read-Host "   ¿Eliminar esta carpeta? [S/N/Ver]"
        
        switch ($response.ToLower()) {
            's' {
                try {
                    Remove-Item $folderPath -Recurse -Force
                    Write-Host "   ✅ Carpeta eliminada - $sizeMB MB liberados" -ForegroundColor Green
                    return $sizeMB
                } catch {
                    Write-Host "   ❌ Error eliminando carpeta: $($_.Exception.Message)" -ForegroundColor Red
                    return 0
                }
            }
            'ver' {
                Write-Host "   📋 Contenido de la carpeta:" -ForegroundColor Cyan
                Get-ChildItem $folderPath -Recurse -File | Select-Object Name, @{N="Size(KB)";E={[math]::Round($_.Length/1KB,1)}} | Sort-Object "Size(KB)" -Descending | Select-Object -First 10 | Format-Table
                return 0
            }
            default {
                Write-Host "   ⚠️ Carpeta mantenida" -ForegroundColor Gray
                return 0
            }
        }
    }
    return 0
}

$totalSaved = 0

# 1. Backend Node.js (58MB) - No parece estar en uso
$backendSize = 58
$totalSaved += Remove-OptionalFolder "$basePath\backend" "Backend Node.js" $backendSize "No está corriendo, la app usa solo PHP"

# 2. Carpeta .vscode (configuración de VS Code)
if (Test-Path "$basePath\.vscode") {
    $vscodeSize = [math]::Round((Get-ChildItem "$basePath\.vscode" -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    if ($vscodeSize -gt 0.1) {
        $totalSaved += Remove-OptionalFolder "$basePath\.vscode" "Configuración VS Code" $vscodeSize "Solo configuración del editor"
    }
}

# 3. Carpeta data (si existe y contiene archivos temporales)
if (Test-Path "$basePath\data") {
    $dataSize = [math]::Round((Get-ChildItem "$basePath\data" -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    if ($dataSize -gt 0.1) {
        $totalSaved += Remove-OptionalFolder "$basePath\data" "Carpeta de datos" $dataSize "Puede contener archivos temporales"
    }
}

# 4. Carpeta database (scripts SQL antiguos)
if (Test-Path "$basePath\database") {
    $dbSize = [math]::Round((Get-ChildItem "$basePath\database" -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    if ($dbSize -gt 0.1) {
        Write-Host "`n📁 Scripts de base de datos" -ForegroundColor Yellow
        Write-Host "   Tamaño: $dbSize MB" -ForegroundColor Gray
        Write-Host "   Contenido:" -ForegroundColor Gray
        Get-ChildItem "$basePath\database" -File | Select-Object Name, @{N="Size(KB)";E={[math]::Round($_.Length/1KB,1)}} | Format-Table
        
        $response = Read-Host "   ¿Eliminar scripts SQL antiguos? [S/N]"
        if ($response.ToLower() -eq 's') {
            Remove-Item "$basePath\database" -Recurse -Force
            Write-Host "   ✅ Scripts SQL eliminados - $dbSize MB liberados" -ForegroundColor Green
            $totalSaved += $dbSize
        }
    }
}

# 5. Verificar archivos JavaScript duplicados o innecesarios
Write-Host "`n📂 ANALIZANDO CARPETA JS..." -ForegroundColor Cyan
if (Test-Path "$basePath\js") {
    $jsFiles = Get-ChildItem "$basePath\js" -File -Filter "*.js" | Where-Object { 
        $_.Name -like "*backup*" -or 
        $_.Name -like "*old*" -or 
        $_.Name -like "*copy*" -or
        $_.Name -like "*temp*" -or
        $_.Name -like "*test*"
    }
    
    if ($jsFiles.Count -gt 0) {
        Write-Host "   📋 Archivos JS potencialmente innecesarios:" -ForegroundColor Yellow
        $jsFiles | Select-Object Name, @{N="Size(KB)";E={[math]::Round($_.Length/1KB,1)}} | Format-Table
        
        $response = Read-Host "   ¿Eliminar estos archivos JS? [S/N]"
        if ($response.ToLower() -eq 's') {
            $jsSize = ($jsFiles | Measure-Object -Property Length -Sum).Sum / 1MB
            $jsFiles | Remove-Item -Force
            Write-Host "   ✅ Archivos JS eliminados - $([math]::Round($jsSize,2)) MB liberados" -ForegroundColor Green
            $totalSaved += $jsSize
        }
    }
}

# 6. Limpiar archivos de log si existen
Write-Host "`n📝 VERIFICANDO ARCHIVOS DE LOG..." -ForegroundColor Cyan
$logFiles = Get-ChildItem $basePath -File -Recurse -ErrorAction SilentlyContinue | Where-Object { 
    $_.Extension -eq ".log" -or 
    $_.Extension -eq ".txt" -and $_.Name -like "*log*" -or
    $_.Name -like "error*" -or
    $_.Name -like "access*"
} | Where-Object { $_.Length -gt 100KB }

if ($logFiles.Count -gt 0) {
    Write-Host "   📋 Archivos de log grandes encontrados:" -ForegroundColor Yellow
    $logFiles | Select-Object Name, @{N="Size(KB)";E={[math]::Round($_.Length/1KB,1)}}, Directory | Format-Table
    
    $response = Read-Host "   ¿Eliminar archivos de log? [S/N]"
    if ($response.ToLower() -eq 's') {
        $logSize = ($logFiles | Measure-Object -Property Length -Sum).Sum / 1MB
        $logFiles | Remove-Item -Force
        Write-Host "   ✅ Archivos de log eliminados - $([math]::Round($logSize,2)) MB liberados" -ForegroundColor Green
        $totalSaved += $logSize
    }
}

Write-Host "`n📊 RESUMEN DE LIMPIEZA AVANZADA" -ForegroundColor Green
Write-Host "==============================" -ForegroundColor Green
Write-Host "Espacio total liberado: $([math]::Round($totalSaved,2)) MB" -ForegroundColor White

Write-Host "`n📈 ESPACIO FINAL:" -ForegroundColor Cyan
Get-ChildItem -Path $basePath -Directory | ForEach-Object { 
    $size = [math]::Round((Get-ChildItem $_.FullName -Recurse -File -ErrorAction SilentlyContinue | Measure-Object -Property Length -Sum).Sum / 1MB, 2)
    [PSCustomObject]@{ Carpeta = $_.Name; "Tamaño(MB)" = $size }
} | Sort-Object "Tamaño(MB)" -Descending | Format-Table -AutoSize

Write-Host "`n✅ LIMPIEZA AVANZADA COMPLETADA!" -ForegroundColor Green
