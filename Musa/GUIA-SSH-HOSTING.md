# 🔐 GUÍA SSH PARA CONFIGURAR MUSA MODA EN HOSTING

## 📋 DATOS DE CONEXIÓN SSH CONFIRMADOS

✅ **Clave SSH**: `id_rsa` (autorizada)  
✅ **Contraseña/Passphrase**: `Chiguiro553021`  
✅ **Estado**: `authorized` (funcionando)

## 🚀 PASOS PARA SUBIR Y CONFIGURAR VIA SSH

### 1. **CONECTAR AL HOSTING**
```bash
# Conectar via SSH (reemplaza con tu usuario y dominio real)
ssh usuario@musaarion.com
# O si tu hosting usa un formato diferente:
ssh usuario@servidor.hosting.com
```

### 2. **VERIFICAR DIRECTORIO**
```bash
# Ir al directorio web
cd public_html

# Verificar que estás en el lugar correcto
pwd
ls -la
```

### 3. **SUBIR ARCHIVOS** (Una de estas opciones):

#### OPCIÓN A: Via SCP (desde tu máquina local)
```bash
# Subir archivos desde tu computadora al hosting
scp -r C:\xampp\htdocs\Musa\* usuario@musaarion.com:public_html/

# Subir archivos específicos
scp C:\xampp\htdocs\Musa\index.html usuario@musaarion.com:public_html/
scp C:\xampp\htdocs\Musa\admin-panel.html usuario@musaarion.com:public_html/
scp -r C:\xampp\htdocs\Musa\api usuario@musaarion.com:public_html/
scp -r C:\xampp\htdocs\Musa\config usuario@musaarion.com:public_html/
```

#### OPCIÓN B: Via SFTP
```bash
# Conectar via SFTP
sftp usuario@musaarion.com

# Subir archivos
put C:\xampp\htdocs\Musa\index.html public_html/
put C:\xampp\htdocs\Musa\admin-panel.html public_html/
put -r C:\xampp\htdocs\Musa\api public_html/
put -r C:\xampp\htdocs\Musa\config public_html/
```

### 4. **CONFIGURAR PERMISOS AUTOMÁTICAMENTE**
```bash
# Una vez conectado via SSH al hosting, ejecutar:

# Subir y ejecutar el script de permisos
cd public_html

# Ejecutar el script automático
bash fix-hosting-permissions.sh

# O configurar manualmente:
find . -name "*.php" -exec chmod 755 {} \;
find . -name "*.html" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 777 uploads/
```

### 5. **VERIFICAR CONFIGURACIÓN**
```bash
# Verificar permisos
ls -la api/
ls -la config/

# Probar configuración de BD
php -f api/productos.php
```

## 🎯 COMANDOS ESPECÍFICOS PARA MUSA MODA

### Estructura de directorios a crear:
```bash
mkdir -p public_html/api
mkdir -p public_html/config  
mkdir -p public_html/php
mkdir -p public_html/js
mkdir -p public_html/css
mkdir -p public_html/images
mkdir -p public_html/uploads
mkdir -p public_html/sounds
```

### Archivos críticos a subir:
```bash
# Configuración principal
scp C:\xampp\htdocs\Musa\config\database.php usuario@musaarion.com:public_html/config/

# APIs principales
scp C:\xampp\htdocs\Musa\api\productos.php usuario@musaarion.com:public_html/api/
scp C:\xampp\htdocs\Musa\api\categorias.php usuario@musaarion.com:public_html/api/
scp C:\xampp\htdocs\Musa\api\obtener-pedidos.php usuario@musaarion.com:public_html/api/

# Frontend
scp C:\xampp\htdocs\Musa\index.html usuario@musaarion.com:public_html/
scp C:\xampp\htdocs\Musa\admin-panel.html usuario@musaarion.com:public_html/
```

## 🔍 VERIFICACIÓN FINAL

Una vez subido todo, probar desde el navegador:

1. **Sitio principal**: `https://musaarion.com`
2. **Panel admin**: `https://musaarion.com/admin-panel.html`  
3. **API productos**: `https://musaarion.com/api/productos.php`

## 💡 CONSEJOS IMPORTANTES

- **Usar la clave SSH**: No necesitarás escribir contraseñas constantemente
- **Permisos automáticos**: El script `fix-hosting-permissions.sh` configura todo de una vez
- **Backup**: Haz backup de archivos existentes antes de sobrescribir

---

**¿Necesitas ayuda con algún comando específico o quieres que te guíe paso a paso?**