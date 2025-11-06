# 📦 ARCHIVOS PARA SUBIR AL HOSTING

## 🚀 LISTA COMPLETA DE ARCHIVOS Y CARPETAS

### ✅ ARCHIVOS PRINCIPALES
```
📁 Musa/ (Raíz del proyecto)
├── 📄 .htaccess (NUEVO - Configuración de Apache)
├── 📄 index.html
├── 📄 admin-panel.html
├── 📄 admin-login.html
├── 📄 success.html
├── 📄 pending.html
├── 📄 failure.html
├── 📄 success-premium.html
├── 📄 pending-premium.html
├── 📄 failure-premium.html
├── 📄 pago-premium.html
├── 📄 install-hosting-setup.php (NUEVO - Verificación)
├── 📄 login.php
├── 📄 logout.php
└── 📄 admin-panel.php
```

### 🗂️ CARPETAS PRINCIPALES
```
📁 config/ (NUEVA - Configuraciones)
├── 📄 database.php (NUEVO - Config BD)
├── 📄 config.php (NUEVO - Config principal)
├── 📄 update-hosting-settings.sql (NUEVO - Script SQL)
└── 📄 production.php (NUEVO - Config producción)

📁 api/ (Endpoints de la API)
├── 📄 productos.php
├── 📄 productos-v2.php
├── 📄 categorias.php
├── 📄 guardar-pedido.php
├── 📄 obtener-pedidos.php
└── 📄 [otros archivos API]

📁 js/ (JavaScript actualizado)
├── 📄 frontend-database.js (ACTUALIZADO)
├── 📄 admin-database-system.js (ACTUALIZADO)
├── 📄 mercadoPago.js (ACTUALIZADO)
├── 📄 product-loader.js
├── 📄 admin-system.js
└── 📄 [otros archivos JS]

📁 css/ (Estilos)
├── 📄 styles.css
├── 📄 admin-styles.css
└── 📄 [otros archivos CSS]

📁 php/ (Scripts PHP backend)
├── 📄 database.php
├── 📄 crear_producto.php
├── 📄 actualizar_producto.php
├── 📄 eliminar_producto.php
└── 📄 [otros archivos PHP]

📁 uploads/ (Imágenes de productos)
├── 📄 [archivos de imágenes existentes]
└── 📄 [nuevas imágenes]

📁 images/ (Recursos gráficos)
├── 📄 logo.svg
├── 📄 placeholder.svg
└── 📄 [otros recursos]

📁 logs/ (NUEVA - Para logs del sistema)
└── 📄 .gitkeep

📁 cache/ (NUEVA - Para cache)
└── 📄 .gitkeep
```

### ❌ ARCHIVOS QUE NO SUBIR
```
❌ config/update-localhost-references.php (Solo desarrollo)
❌ Archivos .git/ (Control de versiones)
❌ Archivos de prueba locales específicos
❌ node_modules/ (Si existe)
❌ .env (Variables de entorno locales)
❌ *.log (Logs locales)
❌ Archivos temporales .tmp
```

### 📋 PROCESO DE SUBIDA RECOMENDADO

#### 1️⃣ VIA FTP/SFTP
```bash
# Estructura recomendada en el hosting:
/public_html/
├── .htaccess
├── index.html
├── [todos los archivos del proyecto]
└── [todas las carpetas del proyecto]
```

#### 2️⃣ VIA CPANEL FILE MANAGER
1. **Comprimir el proyecto localmente** (ZIP)
2. **Subir el archivo ZIP** a `/public_html/`
3. **Extraer en el servidor**
4. **Verificar estructura de archivos**

#### 3️⃣ PERMISOS REQUERIDOS
```bash
# Archivos: 644
# Directorios: 755
# Especialmente importantes:
chmod 755 uploads/
chmod 755 logs/
chmod 755 cache/
chmod 644 config/*.php
chmod 644 .htaccess
```

### 🔧 CONFIGURACIONES POST-SUBIDA

#### ✅ INMEDIATAMENTE DESPUÉS DE SUBIR:
1. **Verificar estructura:** Todos los archivos en su lugar
2. **Configurar contraseña BD:** Editar `config/database.php`
3. **Ejecutar SQL:** Importar `config/update-hosting-settings.sql`
4. **Verificar permisos:** Directorios escribibles
5. **Probar instalación:** Acceder a `install-hosting-setup.php`

#### ⚙️ CONFIGURACIONES OPCIONALES:
- **Credenciales MercadoPago** (producción)
- **Configuración SMTP** (emails)
- **SSL/HTTPS** (automático)
- **Cache y optimizaciones** (configurado)

### 📊 TAMAÑOS APROXIMADOS
```
📦 Total del proyecto: ~50-100 MB
├── 🖼️ uploads/ (~30-60 MB - imágenes)
├── 📄 Archivos PHP/JS/CSS (~5-10 MB)
├── 📄 Archivos HTML (~5-10 MB)
└── 🗂️ Otros recursos (~5-15 MB)
```

### 🚀 ORDEN DE SUBIDA RECOMENDADO
1. **Configuraciones** → `config/`
2. **Archivos principales** → `.htaccess`, `index.html`
3. **Backend** → `api/`, `php/`
4. **Frontend** → `js/`, `css/`
5. **Recursos** → `images/`, `uploads/`
6. **Instalación** → `install-hosting-setup.php`

### ✅ VERIFICACIÓN FINAL
```bash
# Después de subir, verificar que existen:
✓ /.htaccess
✓ /config/database.php
✓ /config/config.php
✓ /api/productos.php
✓ /api/create-preference.php
✓ /api/guardar-pedido-real.php
✓ /api/webhook-mercadopago.php
✓ /js/frontend-database.js
✓ /uploads/ (directorio)
✓ /install-hosting-setup.php
```

### ℹ️ Notas sobre rutas de API en producción
- El frontend intenta primero `https://TU-DOMINIO/Musa/api/...` y luego rutas relativas (`api/...`) y archivos en la raíz de `Musa` como último recurso (por ejemplo `create-preference.php`).
- Recomendado: subir siempre la carpeta `api/` completa dentro de `public_html/Musa/api/` para evitar redirecciones innecesarias y 404.
- Asegura también los webhooks en `Musa/api/webhook-mercadopago.php` y `Musa/api/webhook.php`.

---

*🎯 Una vez subidos todos los archivos, acceder a: `https://musaarion.com/install-hosting-setup.php` para verificar la instalación.*