# 🚀 GUÍA COMPLETA DE MIGRACIÓN - M & A MODA

## 📝 Checklist de Migración

### ✅ PASO 1: Verificar Configuración Local
- [x] XAMPP funcionando
- [x] Base de datos local limpia
- [x] Archivos innecesarios eliminados
- [x] APIs funcionando correctamente

### 🎯 PASO 2: Preparar Hosting (cPanel)

#### 2.1 Base de Datos
1. **Crear nueva base de datos:**
   - Nombre: `[usuario]_musa_db`
   - Usuario: `[usuario]_musa_user`
   - Contraseña: `[generar_segura]`

2. **Actualizar configuración:**
   - Editar `config/production-database.php`
   - Cambiar credenciales reales

#### 2.2 Subir Archivos
```bash
# Archivos principales a subir:
- index.html
- admin-panel.html
- login.php
- logout.php
- config/
- api/
- assets/
- images/
- .htaccess
- install-database.php
```

### 🗄️ PASO 3: Configurar Base de Datos en Hosting

#### 3.1 Ejecutar Instalador
```
https://tudominio.com/install-database.php?password=musa2025
```

#### 3.2 Importar Datos (si existen)
1. Ejecutar desde XAMPP:
   ```
   http://localhost/Musa/export-local-data.php
   ```
2. Descargar archivo SQL
3. Importar en cPanel > phpMyAdmin

### ⚙️ PASO 4: Configurar Archivos

#### 4.1 Actualizar .htaccess
```apache
RewriteEngine On
RewriteBase /

# Seguridad
<Files "config/*">
    Order allow,deny
    Deny from all
</Files>

# Redireccionamientos API
RewriteRule ^api/productos$ api/productos.php [L]
RewriteRule ^api/categorias$ api/categorias.php [L]

# Cache de imágenes
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 month"
    ExpiresByType image/jpeg "access plus 1 month"
    ExpiresByType image/gif "access plus 1 month"
    ExpiresByType image/png "access plus 1 month"
    ExpiresByType image/webp "access plus 1 month"
</IfModule>
```

#### 4.2 Verificar Permisos
```bash
# En SSH o usar script fix-cpanel-permissions.php
chmod 755 api/
chmod 644 config/
chmod 755 images/
```

### 🔧 PASO 5: Testing y Validación

#### 5.1 Verificar APIs
```
https://tudominio.com/api/productos.php
https://tudominio.com/api/categorias.php
```

#### 5.2 Verificar Panel Admin
```
https://tudominio.com/admin-panel.html
Usuario: admin
Contraseña: admin123
```

#### 5.3 Verificar Frontend
```
https://tudominio.com/
```

### 🛠️ PASO 6: Resolución de Problemas

#### 6.1 Error 500
- Verificar .htaccess
- Revisar permisos de archivos
- Verificar logs de error

#### 6.2 Error de Base de Datos
- Verificar credenciales en production-database.php
- Verificar que la base de datos existe
- Verificar permisos del usuario

#### 6.3 Imágenes no cargan
- Verificar estructura de carpeta images/
- Verificar permisos 755 en carpetas
- Verificar rutas en el código

### 🔒 PASO 7: Seguridad Post-Instalación

#### 7.1 Eliminar archivos de instalación
```bash
rm install-database.php
rm export-local-data.php
rm fix-cpanel-permissions.php
rm cleanup-cpanel.sh
```

#### 7.2 Cambiar contraseñas
- Cambiar contraseña del admin
- Generar nueva contraseña de instalación
- Actualizar credenciales de base de datos

#### 7.3 Configurar backups
- Configurar backup automático en cPanel
- Documentar credenciales de acceso

### 📊 PASO 8: Monitoreo

#### 8.1 Verificaciones regulares
- Funcionamiento del panel admin
- Carga de imágenes
- Velocidad de respuesta de APIs
- Logs de errores

#### 8.2 Optimizaciones
- Comprimir imágenes
- Configurar CDN si es necesario
- Optimizar consultas de base de datos

---

## 🚨 CONTACTOS DE EMERGENCIA

### Hosting Support
- **cPanel**: Panel de control principal
- **phpMyAdmin**: Gestión de base de datos
- **File Manager**: Gestión de archivos

### Archivos Críticos
- `config/production-database.php`: Configuración DB
- `api/productos.php`: API de productos
- `api/categorias.php`: API de categorías
- `.htaccess`: Configuración del servidor

### Credenciales por Defecto
- **Admin Panel**: admin / admin123
- **DB Password**: [actualizar en production-database.php]
- **Install Password**: musa2025

---

## ✅ VERIFICACIÓN FINAL

- [ ] Sitio web carga correctamente
- [ ] Panel admin funciona
- [ ] APIs responden correctamente
- [ ] Imágenes se cargan
- [ ] Base de datos conecta
- [ ] Archivos de instalación eliminados
- [ ] Contraseñas cambiadas
- [ ] Backup configurado

**¡MIGRACIÓN COMPLETADA!** 🎉
