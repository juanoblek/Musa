# 📦 ARCHIVOS PARA SUBIR AL HOSTING - MUSA MODA
# =====================================================
# Lista DEFINITIVA de archivos que debes subir a tu hosting

## ✅ ARCHIVOS PRINCIPALES (OBLIGATORIOS)

### 🏠 Archivos Raíz
- index.html                    # Página principal de la tienda
- admin-panel.html              # Panel de administración
- pago-premium.html             # Página de pagos con Mercado Pago
- install-hosting.php           # Instalador automático (ejecutar UNA VEZ)
- .htaccess                     # Configuración del servidor
- success.html                  # Página de pago exitoso
- failure.html                  # Página de pago fallido
- pending.html                  # Página de pago pendiente

### 📂 Carpeta config/ (CRÍTICA)
- config/config-global.php      # Configuración automática de entorno
- config/database.php           # Configuración de base de datos
- config/mercadopago.php        # Configuración de Mercado Pago

### 🔌 Carpeta api/ (OBLIGATORIA)
- api/productos.php             # API para obtener productos
- api/categorias.php            # API para obtener categorías
- api/create-preference.php     # Crear preferencias de pago
- api/create-preference-premium.php  # Crear preferencias premium
- api/crear-preferencia.php     # Crear preferencias alternativa
- api/webhook-mercadopago.php   # Webhook para notificaciones MP
- api/process-payment.php       # Procesamiento de pagos
- api/pago-real-mercadopago.php # Pagos reales
- api/guardar-pedido.php        # Guardar pedidos en BD
- api/obtener-pedidos.php       # Obtener lista de pedidos

### 🎨 Carpeta assets/
- assets/css/                   # Todos los archivos CSS
- assets/js/                    # Todos los archivos JavaScript
- assets/images/                # Iconos, logos, imágenes del sitio

### 🖼️ Carpeta images/
- images/productos/             # Todas las subcarpetas de productos
- images/categorias/            # Todas las subcarpetas de categorías

### 📱 Carpetas adicionales
- php/                          # Scripts PHP auxiliares
- classes/                      # Clases PHP
- functions/                    # Funciones auxiliares

## ❌ ARCHIVOS QUE NO SUBIR

### 🗑️ Archivos de Desarrollo
- *.md                          # Archivos de documentación
- *.ps1                         # Scripts de PowerShell
- debug-*                       # Archivos de debug
- test-*                        # Archivos de prueba
- temp-*                        # Archivos temporales
- backup-*                      # Archivos de respaldo
- servidor.py                   # Servidor de desarrollo
- start.bat                     # Archivos batch

### 🔧 Archivos de Configuración Local
- config/database-local.php     # Solo para localhost
- config/mercadopago-test.php   # Solo para pruebas

---

## 🚀 PROCESO DE SUBIDA PASO A PASO

### PASO 1: Preparar archivos
1. Crear carpeta temporal con los archivos permitidos
2. Verificar que config/config-global.php esté incluido
3. NO incluir archivos de la lista "NO SUBIR"

### PASO 2: Subir al hosting
**Opción A: cPanel File Manager**
1. Acceder a cPanel → File Manager
2. Ir a public_html/
3. Subir archivos manteniendo estructura de carpetas

**Opción B: FTP/SFTP**
```
Host: tu-dominio.com
Usuario: [usuario cPanel]
Puerto: 21 (FTP) o 22 (SFTP)
```

**Opción C: Comprimir y subir**
1. Crear ZIP con archivos permitidos
2. Subir ZIP a public_html/
3. Extraer desde cPanel

### PASO 3: Configurar base de datos
1. Crear base de datos en cPanel: `janithal_musa_moda`
2. Crear usuario: `janithal_usuario_musaarion_db`
3. Contraseña: `Chiguiro553021`
4. Asignar todos los permisos al usuario

### PASO 4: Ejecutar instalador
1. Visitar: `https://tu-dominio.com/install-hosting.php`
2. Seguir las instrucciones en pantalla
3. Verificar que todo esté en verde
4. El script se auto-bloquea después del primer uso

### PASO 5: Verificar funcionamiento
1. Probar tienda: `https://tu-dominio.com/`
2. Probar admin: `https://tu-dominio.com/admin-panel.html`
3. Probar pagos: `https://tu-dominio.com/pago-premium.html`

---

## 🔐 CONFIGURACIÓN AUTOMÁTICA

### ✅ Auto-detección de entorno
- En LOCALHOST: Usa credenciales de test y BD local
- En HOSTING: Usa credenciales de producción y BD de hosting
- Cambio automático sin modificar código

### ✅ URLs dinámicas
- En LOCALHOST: `http://localhost/Musa/success.html`
- En HOSTING: `https://tu-dominio.com/success.html`
- Configuración automática según el dominio

### ✅ Base de datos automática
- En LOCALHOST: `musa_moda` con usuario `root`
- En HOSTING: `janithal_musa_moda` con usuario específico
- Cambio automático de credenciales

---

## 🎯 CHECKLIST FINAL

### Antes de subir
- [ ] Archivos seleccionados según esta lista
- [ ] No incluir archivos de la lista "NO SUBIR"
- [ ] Verificar que config/config-global.php esté incluido

### Durante la subida
- [ ] Mantener estructura de carpetas
- [ ] Subir todas las carpetas: api/, config/, assets/, images/
- [ ] Verificar permisos de archivos

### Después de la subida
- [ ] Ejecutar install-hosting.php
- [ ] Verificar que todo esté en ✅ verde
- [ ] Probar funcionalidad básica
- [ ] Hacer prueba de pago pequeña

---

## 📞 SOPORTE

Si tienes problemas:
1. Revisar logs del instalador
2. Verificar configuración de base de datos
3. Confirmar que el hosting tenga PHP 7.4+
4. Verificar que cURL esté habilitado

---

**¡LISTA PARA PRODUCCIÓN! 🚀**