# 🚀 GUÍA ALTERNATIVA - SUBIR MUSA MODA VIA cPANEL FILE MANAGER

## 🔍 PROBLEMA DETECTADO
SSH no está disponible o usa un puerto diferente. Usaremos **cPanel File Manager** que es más confiable para hosting compartido.

## 📁 ARCHIVOS LISTOS PARA SUBIR

### ✅ ARCHIVOS PRINCIPALES CONFIRMADOS:
- `index.html` (1,019 KB) - Página principal ✅
- `admin-panel.html` (81 KB) - Panel administrativo ✅ 
- `admin-login.html` - Login admin ✅
- `.htaccess` - Configuración Apache ✅

### ✅ CONFIGURACIÓN:
- `config/database.php` (3.2 KB) - Configuración BD ✅

### ✅ APIs DISPONIBLES:
- `api/categorias.php` (8.2 KB) ✅
- `api/obtener-pedidos.php` (6.6 KB) ✅
- `api/crear-preferencia.php` (6.3 KB) ✅
- `api/guardar-pedido.php` (4.9 KB) ✅
- `api/productos-emergency.php` (3.4 KB) - **USAR COMO productos.php** ✅

### ✅ BACKEND:
- `php/database.php` - Clase de conexión ✅
- `js/frontend-database.js` - Frontend ✅

## 🎯 PASOS VIA cPANEL FILE MANAGER

### 1. **ACCEDER A cPANEL**
- Ir a: `https://musaarion.com:2083` o `https://musaarion.com/cpanel`
- Usuario: `janithal`
- Contraseña: `Chiguiro553021`

### 2. **ABRIR FILE MANAGER**
- Buscar "File Manager" en cPanel
- Clic en "File Manager"
- Ir a `public_html/`

### 3. **CREAR DIRECTORIOS**
Crear estas carpetas en `public_html/`:
- `api/`
- `config/`
- `php/`
- `js/`
- `css/`
- `images/`
- `uploads/` (importante para imágenes)

### 4. **SUBIR ARCHIVOS PRINCIPALES**
En `public_html/`:
- Subir `index.html`
- Subir `admin-panel.html`
- Subir `admin-login.html`
- Subir `.htaccess`

### 5. **SUBIR CONFIGURACIÓN**
En `public_html/config/`:
- Subir `config/database.php`

### 6. **SUBIR APIs**
En `public_html/api/`:
- Subir `api/categorias.php`
- Subir `api/obtener-pedidos.php`
- Subir `api/crear-preferencia.php`
- Subir `api/guardar-pedido.php`
- Subir `api/productos-emergency.php` → **RENOMBRAR a `productos.php`**

### 7. **SUBIR BACKEND**
En `public_html/php/`:
- Subir `php/database.php`

En `public_html/js/`:
- Subir `js/frontend-database.js`

### 8. **CONFIGURAR PERMISOS**
Para cada archivo `.php`:
- Clic derecho → "Permissions"
- Cambiar a `755`

Para `uploads/`:
- Clic derecho → "Permissions" 
- Cambiar a `777`

## 🧪 PROBAR QUE FUNCIONA

1. **Sitio principal**: `https://musaarion.com`
2. **Panel admin**: `https://musaarion.com/admin-panel.html`
3. **API productos**: `https://musaarion.com/api/productos.php`

## 📋 LISTA DE VERIFICACIÓN

- [ ] Crear directorios en public_html
- [ ] Subir index.html
- [ ] Subir admin-panel.html  
- [ ] Subir config/database.php
- [ ] Subir todas las APIs
- [ ] Subir php/database.php
- [ ] Renombrar productos-emergency.php → productos.php
- [ ] Configurar permisos 755 para PHP
- [ ] Configurar permisos 777 para uploads
- [ ] Probar en navegador

## ✅ RESULTADO ESPERADO

Una vez completado:
- ❌ Errores HTTP 500 desaparecerán
- ✅ Panel administrativo funcionará
- ✅ APIs responderán correctamente
- ✅ Base de datos se conectará

---

**¿Tienes acceso a cPanel? Te guío paso a paso si necesitas ayuda.**