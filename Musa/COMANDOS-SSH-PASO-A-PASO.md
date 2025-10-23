# 🚀 COMANDOS PASO A PASO PARA SUBIR MUSA MODA AL HOSTING

## 📋 ANTES DE EMPEZAR

Necesitas saber tu **usuario SSH del hosting**. Normalmente es:
- El nombre de usuario de tu cPanel
- O algo como: `musaarion` o `tu_usuario`

## 🔧 PASO 1: PREPARAR ARCHIVOS LOCALMENTE

```powershell
# En PowerShell, ir al directorio de Musa
cd C:\xampp\htdocs\Musa

# Verificar que tienes todos los archivos
dir

# Crear archivo de permisos si no existe
echo 'Script de permisos creado'
```

## 🌐 PASO 2: CONECTAR AL HOSTING

```bash
# REEMPLAZA "tu_usuario" con tu usuario real de SSH
ssh tu_usuario@musaarion.com

# Si tienes un puerto diferente:
# ssh -p 2222 tu_usuario@musaarion.com

# Te pedirá la contraseña SSH: Chiguiro553021
```

## 📁 PASO 3: PREPARAR DIRECTORIOS EN EL HOSTING

```bash
# Una vez conectado al hosting via SSH:

# Ir al directorio web
cd public_html

# Verificar contenido actual
ls -la

# Crear directorios necesarios
mkdir -p api
mkdir -p config
mkdir -p php
mkdir -p js
mkdir -p css
mkdir -p images
mkdir -p uploads
mkdir -p sounds

# Verificar directorios creados
ls -la
```

## 📤 PASO 4: SUBIR ARCHIVOS (Nueva terminal en tu PC)

```powershell
# Abrir nueva terminal PowerShell en tu PC
# No cerrar la conexión SSH anterior

# Ir al directorio de Musa
cd C:\xampp\htdocs\Musa

# Subir archivos principales
scp index.html tu_usuario@musaarion.com:public_html/
scp admin-panel.html tu_usuario@musaarion.com:public_html/
scp admin-login.html tu_usuario@musaarion.com:public_html/
scp .htaccess tu_usuario@musaarion.com:public_html/

# Subir configuración
scp config\database.php tu_usuario@musaarion.com:public_html/config/

# Subir APIs
scp api\productos.php tu_usuario@musaarion.com:public_html/api/
scp api\categorias.php tu_usuario@musaarion.com:public_html/api/
scp api\obtener-pedidos.php tu_usuario@musaarion.com:public_html/api/
scp api\crear-preferencia.php tu_usuario@musaarion.com:public_html/api/
scp api\guardar-pedido.php tu_usuario@musaarion.com:public_html/api/
scp api\webhook-mercadopago.php tu_usuario@musaarion.com:public_html/api/

# Subir PHP
scp php\database.php tu_usuario@musaarion.com:public_html/php/

# Subir JavaScript
scp js\frontend-database.js tu_usuario@musaarion.com:public_html/js/
scp js\admin-database-system.js tu_usuario@musaarion.com:public_html/js/

# Subir script de permisos
scp fix-hosting-permissions.sh tu_usuario@musaarion.com:public_html/
```

## 🔧 PASO 5: CONFIGURAR PERMISOS (Volver a SSH)

```bash
# En la terminal SSH del hosting:

# Verificar archivos subidos
ls -la
ls -la api/
ls -la config/

# Ejecutar script de permisos
chmod +x fix-hosting-permissions.sh
bash fix-hosting-permissions.sh

# O configurar manualmente:
chmod 755 api/*.php
chmod 755 config/*.php
chmod 755 php/*.php
chmod 644 *.html
chmod 644 js/*.js
chmod 644 css/*.css
chmod 777 uploads/
```

## 🧪 PASO 6: PROBAR QUE FUNCIONA

```bash
# En SSH, probar APIs:
php -f api/productos.php
php -f api/categorias.php

# Si no hay errores, probar en navegador:
# https://musaarion.com
# https://musaarion.com/admin-panel.html
# https://musaarion.com/api/productos.php
```

## 📋 COMANDOS DE VERIFICACIÓN

```bash
# Verificar permisos
ls -la api/
ls -la config/

# Verificar configuración de BD
cat config/database.php

# Probar conexión a BD
php -r "
try {
    \$pdo = new PDO('mysql:host=localhost;dbname=janithal_musa_moda', 'janithal_usuario_musaarion_db', 'Chiguiro553021');
    echo 'Conexión exitosa!';
} catch(Exception \$e) {
    echo 'Error: ' . \$e->getMessage();
}
"
```

## 🚨 SI HAY PROBLEMAS

```bash
# Ver logs de errores
tail -f /home/tu_usuario/logs/error_log

# Verificar sintaxis PHP
php -l api/productos.php
php -l config/database.php

# Reinstalar permisos
find . -name "*.php" -exec chmod 755 {} \;
```

---

## 🎯 REEMPLAZAR EN TODOS LOS COMANDOS:

- **`tu_usuario`** → Tu usuario real de SSH
- **`musaarion.com`** → Tu dominio real

**¿Cuál es tu usuario SSH para empezar?**