# 📁 ARCHIVOS PARA SUBIR AL HOSTING - M & A MODA

## ✅ ARCHIVOS PRINCIPALES (OBLIGATORIOS)

### 🏠 Archivos Raíz
- `index.html` ✅
- `admin-panel.html` ✅
- `login.php` ✅
- `logout.php` ✅
- `.htaccess` ✅
- `install-database.php` ✅ (eliminar después de usar)

### 📂 Carpeta `config/`
- `config/production-database.php` ✅ (con contraseña real)

### 🔌 Carpeta `api/`
- `api/productos.php` ✅
- `api/categorias.php` ✅

### 🎨 Carpeta `assets/`
- `assets/css/` (todos los archivos CSS)
- `assets/js/` (todos los archivos JavaScript)
- `assets/images/` (iconos, logos)

### 🖼️ Carpeta `images/`
- `images/productos/` (todas las subcarpetas)
- `images/categorias/` (todas las subcarpetas)

---

## ❌ ARCHIVOS QUE NO SUBIR

### 🗑️ Archivos de Desarrollo Local
- `config/database.php` ❌ (solo para XAMPP)
- `server.py` ❌
- `start.bat` ❌
- `setup.bat` ❌

### 🔧 Archivos de Limpieza/Debug
- `*debug*` ❌
- `*test*` ❌
- `*temp*` ❌
- `*backup*` ❌
- `limpieza-plataforma.ps1` ❌
- `*.ps1` ❌
- `*.md` ❌

### 📝 Archivos de Documentación
- `DOCUMENTACION.md` ❌
- `MEJORAS-IMPLEMENTADAS.md` ❌
- `GUIA-MIGRACION-COMPLETA.md` ❌

---

## 🚀 PROCESO DE SUBIDA

### Método 1: cPanel File Manager
1. **Acceder a:** cPanel → File Manager
2. **Ir a:** public_html/
3. **Subir archivos** uno por uno o en ZIP
4. **Mantener estructura** de carpetas

### Método 2: FTP/SFTP
```
Host: tudominio.com
Usuario: [tu usuario cPanel]
Contraseña: [tu contraseña cPanel]
Puerto: 21 (FTP) o 22 (SFTP)
```

### Método 3: Comprimir y Subir
```powershell
# Crear ZIP con archivos principales
Compress-Archive -Path "index.html", "admin-panel.html", "login.php", "logout.php", ".htaccess", "install-database.php", "config", "api", "assets", "images" -DestinationPath "musa-produccion.zip"
```

---

## 📋 CHECKLIST DE SUBIDA

### Antes de Subir
- [x] Base de datos creada en cPanel
- [x] Usuario de BD creado
- [x] Contraseña actualizada en production-database.php
- [ ] Archivos comprimidos o listos para FTP

### Durante la Subida
- [ ] Archivos raíz subidos
- [ ] Carpeta config/ subida
- [ ] Carpeta api/ subida
- [ ] Carpeta assets/ subida
- [ ] Carpeta images/ subida
- [ ] Permisos verificados

### Después de la Subida
- [ ] Ejecutar install-database.php
- [ ] Verificar APIs funcionando
- [ ] Probar panel admin
- [ ] Eliminar install-database.php
- [ ] Cambiar contraseña admin

---

## 🔗 URLS PARA TESTING

### Después de la subida, verificar:
```
https://tudominio.com/
https://tudominio.com/admin-panel.html
https://tudominio.com/api/productos.php
https://tudominio.com/api/categorias.php
https://tudominio.com/install-database.php?password=musa2025
```

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### Error 500
1. Verificar .htaccess
2. Verificar permisos de archivos
3. Revisar logs en cPanel

### Base de Datos no Conecta
1. Verificar credenciales en production-database.php
2. Verificar que la BD existe
3. Verificar permisos del usuario

### Archivos no Cargan
1. Verificar estructura de carpetas
2. Verificar permisos 755 en carpetas
3. Verificar rutas en el código

---

## 📞 DATOS DE CONEXIÓN

### Base de Datos
- **Host:** localhost
- **Nombre:** janithal_musa_moda
- **Usuario:** janithal_usuario_musaarion_db
- **Contraseña:** Chiguiro553021

### Acceso Admin (después de instalación)
- **Usuario:** admin
- **Contraseña:** admin123
- **URL:** https://tudominio.com/admin-panel.html

---

## ✅ ARCHIVOS LISTOS PARA SUBIR

Los siguientes archivos están configurados y listos:
- ✅ `config/production-database.php` (con contraseña real)
- ✅ `install-database.php` (configurado)
- ✅ APIs actualizadas
- ✅ Base de datos creada en hosting

**¡Ya puedes proceder con la subida!** 🚀
