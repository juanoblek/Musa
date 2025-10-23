# 🔧 GUÍA PARA SOLUCIONAR PERMISOS EN EL HOSTING

## 🚨 PROBLEMA IDENTIFICADO
Los archivos se suben con permisos **555** (solo lectura) pero necesitan **755** para ejecutarse correctamente.

## 🎯 SOLUCIONES DISPONIBLES

### 📝 OPCIÓN 1: Via cPanel File Manager

1. **Entra a cPanel** → File Manager
2. **Navega a public_html**
3. **Selecciona todos los archivos PHP**
4. **Clic derecho** → "Permissions" 
5. **Cambiar a 755** para archivos PHP
6. **Cambiar a 644** para HTML, CSS, JS, imágenes

### 🖥️ OPCIÓN 2: Via SSH Terminal (MÁS RÁPIDO)

```bash
# Conectar via SSH al hosting
ssh usuario@musaarion.com

# Ir al directorio
cd public_html

# Ejecutar el script automático
bash fix-hosting-permissions.sh
```

### 📋 OPCIÓN 3: Comandos Manuales via SSH

```bash
# Archivos PHP ejecutables
find . -name "*.php" -exec chmod 755 {} \;

# Archivos estáticos legibles  
find . -name "*.html" -exec chmod 644 {} \;
find . -name "*.css" -exec chmod 644 {} \;
find . -name "*.js" -exec chmod 644 {} \;

# Directorios navegables
find . -type d -exec chmod 755 {} \;

# Uploads con escritura
chmod 777 uploads/
```

## 🎯 PERMISOS ESPECÍFICOS NECESARIOS

### Para que MUSA MODA funcione correctamente:

| Tipo de Archivo | Permiso | Razón |
|-----------------|---------|-------|
| **Archivos PHP** | `755` | Necesitan ejecutarse |
| **config/database.php** | `755` | Configuración crítica |
| **api/*.php** | `755` | APIs deben ejecutarse |
| **php/*.php** | `755` | Clases PHP ejecutables |
| **uploads/** | `777` | Subida de imágenes |
| **HTML/CSS/JS** | `644` | Solo lectura |
| **Imágenes** | `644` | Solo lectura |
| **Directorios** | `755` | Navegación |

## ⚡ SCRIPT AUTOMÁTICO INCLUIDO

He creado el archivo `fix-hosting-permissions.sh` que puedes:

1. **Subir al hosting**
2. **Ejecutar una sola vez** via SSH:
   ```bash
   bash fix-hosting-permissions.sh
   ```
3. **Configurará todos los permisos automáticamente**

## 🔍 VERIFICAR QUE FUNCIONA

Después de cambiar permisos, prueba:

1. **Panel Admin**: `https://musaarion.com/admin-panel.html`
2. **API Productos**: `https://musaarion.com/api/productos.php`
3. **API Categorías**: `https://musaarion.com/api/categorias.php`

## 💡 CONSEJO PARA EL FUTURO

**Algunos hostings** permiten configurar permisos por defecto en el cPanel. Busca:
- "Default Permissions"
- "File Upload Settings" 
- "PHP Settings"

---

**¿Tienes acceso SSH al hosting o prefieres usar cPanel File Manager?**