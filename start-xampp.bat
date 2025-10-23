@echo off
echo Iniciando XAMPP en puerto 8080...

REM Matar procesos previos
taskkill /f /im httpd.exe >nul 2>&1
taskkill /f /im mysqld.exe >nul 2>&1

REM Cambiar al directorio de XAMPP
cd /d C:\xampp

REM Iniciar MySQL
echo Iniciando MySQL...
start "MySQL" /min mysql\bin\mysqld.exe --defaults-file=mysql\bin\my.ini --standalone

REM Esperar un poco para que MySQL inicie
timeout /t 3 /nobreak >nul

REM Iniciar Apache en puerto 8080
echo Iniciando Apache en puerto 8080...
start "Apache" /min apache\bin\httpd.exe -D FOREGROUND -c "Listen 8080" -c "ServerName localhost:8080"

echo.
echo XAMPP iniciado:
echo - MySQL: Puerto 3306
echo - Apache: Puerto 8080
echo - Tu aplicacion: http://localhost:8080
echo.
echo Presiona cualquier tecla para cerrar...
pause >nul