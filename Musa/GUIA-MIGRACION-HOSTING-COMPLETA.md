# 🚀 GUÍA COMPLETA DE MIGRACIÓN A HOSTING
## MUSA MODA - musaarion.com

### 📋 INFORMACIÓN DEL HOSTING
- **Usuario:** janithal
- **Dominio:** musaarion.com  
- **IP Compartida:** 162.241.60.182
- **Directorio Principal:** /home4/janithal
- **Base de Datos:** janithal_musa_moda
- **Usuario BD:** janithal_usuario_musaarion_db

---

## 🗂️ ARCHIVOS CREADOS PARA LA MIGRACIÓN

### ✅ Archivos de Configuración
1. **`config/database.php`** - Configuración de base de datos con detección automática de entorno
2. **`config/config.php`** - Configuración principal del sistema
3. **`config/update-hosting-settings.sql`** - Script SQL para actualizar configuraciones
4. **`config/update-localhost-references.php`** - Script para actualizar referencias de URL
5. **`.htaccess`** - Configuración de Apache optimizada para hosting compartido

### ✅ Archivos de Instalación
- **`install-hosting-setup.php`** - Script de verificación e instalación en hosting

### ✅ Archivos Actualizados
- **`js/frontend-database.js`** - URLs dinámicas según entorno
- **`js/admin-database-system.js`** - API endpoints adaptativos
- **`js/mercadoPago.js`** - Configuración de MercadoPago con detección de entorno

---

## 📝 PASOS DE MIGRACIÓN

### 1️⃣ PREPARACIÓN DE ARCHIVOS LOCALES

```bash
# Ejecutar script de actualización de referencias
php config/update-localhost-references.php
```

### 2️⃣ SUBIDA AL HOSTING

**Archivos a subir al directorio raíz (/public_html):**
```
- Todos los archivos del proyecto EXCEPTO:
  ❌ config/update-localhost-references.php (solo para desarrollo)
  ❌ Archivos de prueba locales
  ❌ Carpeta .git (si existe)
```

### 3️⃣ CONFIGURACIÓN DE BASE DE DATOS

**A. Importar estructura de base de datos:**
- Acceder a phpMyAdmin en el hosting
- Seleccionar base de datos: `janithal_musa_moda`
- Importar el archivo SQL proporcionado

**B. Ejecutar script de configuración:**
```sql
-- En phpMyAdmin, ejecutar:
SOURCE config/update-hosting-settings.sql;
```

### 4️⃣ CONFIGURACIÓN DE CREDENCIALES

**A. Contraseña de Base de Datos:**
Editar `config/database.php` línea 30:
```php
define('DB_PASS', 'TU_CONTRASEÑA_REAL_AQUI');
```

**B. Credenciales de MercadoPago:**
Editar `config/config.php` líneas 42-44:
```php
define('MP_PUBLIC_KEY', 'APP_USR-TU-CLAVE-PUBLICA');
define('MP_ACCESS_TOKEN', 'APP_USR-TU-TOKEN-DE-ACCESO');
```

### 5️⃣ VERIFICACIÓN DE PERMISOS

**Directorios que necesitan permisos 755:**
```bash
chmod 755 uploads/
chmod 755 logs/
chmod 755 cache/
```

### 6️⃣ VERIFICACIÓN DE INSTALACIÓN

1. **Acceder a:** `https://musaarion.com/install-hosting-setup.php`
2. **Verificar que todos los checks aparezcan en verde ✅**
3. **Corregir cualquier error reportado**

---

## 🔧 CONFIGURACIONES ESPECÍFICAS

### 🎯 URLs de MercadoPago (Automáticas)
- **Éxito:** https://musaarion.com/success.html
- **Pendiente:** https://musaarion.com/pending.html
- **Fallo:** https://musaarion.com/failure.html
- **Premium Éxito:** https://musaarion.com/success-premium.html
- **Premium Pendiente:** https://musaarion.com/pending-premium.html
- **Premium Fallo:** https://musaarion.com/failure-premium.html

### 📧 Configuración de Email
Editar `config/config.php` líneas 95-99:
```php
define('SMTP_HOST', 'mail.musaarion.com');
define('SMTP_USERNAME', 'noreply@musaarion.com');
define('SMTP_PASSWORD', 'tu-contraseña-email');
```

### 🔐 Configuraciones de Seguridad
- **SSL:** Automático con .htaccess
- **Headers de Seguridad:** Configurados automáticamente
- **Protección de Archivos:** Incluida en .htaccess

---

## 🧪 PRUEBAS POST-MIGRACIÓN

### ✅ Lista de Verificación
- [ ] **Página principal:** https://musaarion.com
- [ ] **API de productos:** https://musaarion.com/api/productos.php
- [ ] **Panel admin:** https://musaarion.com/admin-panel.html
- [ ] **Crear producto desde admin**
- [ ] **Proceso de compra completo**
- [ ] **Notificaciones de MercadoPago**
- [ ] **Subida de imágenes**

### 🔍 URLs de Prueba
```
GET https://musaarion.com/api/productos.php
GET https://musaarion.com/api/categorias.php
POST https://musaarion.com/api/guardar-pedido.php
```

---

## 🆘 SOLUCIÓN DE PROBLEMAS COMUNES

### ❌ Error 500 - Internal Server Error
1. Verificar permisos de archivos (644) y directorios (755)
2. Revisar logs de PHP en: `logs/php_errors.log`
3. Verificar sintaxis de .htaccess

### 🔌 Error de Conexión a Base de Datos
1. Verificar credenciales en `config/database.php`
2. Confirmar que la base de datos existe
3. Verificar que el usuario tiene permisos

### 🖼️ Imágenes No Se Muestran
1. Verificar permisos del directorio `uploads/`
2. Comprobar URLs en la base de datos
3. Verificar configuración de .htaccess

### 💳 MercadoPago No Funciona
1. Verificar credenciales de producción
2. Confirmar URLs de respuesta
3. Revisar webhooks configurados

---

## 📊 MONITOREO Y MANTENIMIENTO

### 📈 Logs a Revisar
- **PHP Errors:** `logs/php_errors.log`
- **Aplicación:** `logs/application.log`
- **Servidor:** Panel de control del hosting

### 🔄 Tareas de Mantenimiento
- **Limpieza de uploads:** Automática (30 días)
- **Cache:** Limpieza automática
- **Logs:** Rotar mensualmente
- **Backups:** Configurar en el hosting

---

## 🎯 INFORMACIÓN DE CONTACTO TÉCNICO

**Configuración Completada el:** <?= date('Y-m-d H:i:s') ?>
**Versión del Sistema:** MUSA MODA v1.0
**Entorno:** Producción
**Dominio:** musaarion.com

---

## ⚡ ACCESOS RÁPIDOS

- 🏠 **Sitio Web:** https://musaarion.com
- 👨‍💼 **Admin Panel:** https://musaarion.com/admin-panel.html
- 🔧 **Verificación:** https://musaarion.com/install-hosting-setup.php
- 📊 **API Status:** https://musaarion.com/api/health.php (crear si necesario)

---

*¡Migración completada exitosamente! 🎉*