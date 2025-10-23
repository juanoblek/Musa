# 🛠️ SOLUCIÓN PARA ERRORES DE CPANEL "Directory not empty"

## 📋 PASOS PARA RESOLVER EL PROBLEMA

### 🔧 Opción 1: Usar el Script PHP (Recomendado)

1. **Ejecutar desde navegador:**
   ```
   https://tudominio.com/Musa/fix-cpanel-permissions.php
   ```

2. **El script automáticamente:**
   - ✅ Arregla permisos de archivos (644) y carpetas (755)
   - 🗑️ Elimina directorios vacíos
   - 🧹 Limpia archivos temporales
   - 📊 Muestra estadísticas del sistema

### 🖥️ Opción 2: Terminal SSH (Si está disponible)

Si tienes acceso a terminal SSH en tu hosting:

```bash
# 1. Conectar por SSH
ssh usuario@tudominio.com

# 2. Ir al directorio
cd public_html/Musa

# 3. Ejecutar script de limpieza
chmod +x cleanup-cpanel.sh
./cleanup-cpanel.sh
```

### 📁 Opción 3: File Manager de cPanel

1. **Ir a cPanel → File Manager**

2. **Seleccionar archivos problemáticos uno por uno:**
   - Click derecho → Change Permissions
   - Establecer permisos: Carpetas `755`, Archivos `644`

3. **Para carpetas que no se pueden eliminar:**
   - Entrar a la carpeta
   - Eliminar todo el contenido primero
   - Luego eliminar la carpeta vacía

### 🎯 Carpetas Específicas Problemáticas

#### Backend (Node.js) - 58MB
```bash
# Si no usas Node.js, eliminar completamente
rm -rf backend/node_modules
rm -rf backend
```

#### Archivos de desarrollo
```bash
# Eliminar archivos de desarrollo
rm -rf .vscode
rm -rf .git
find . -name "*.log" -delete
find . -name "*.tmp" -delete
```

### ⚡ Comandos de Emergencia

Si nada funciona, ejecutar en orden:

```bash
# 1. Cambiar permisos de TODO
chmod -R 755 .

# 2. Cambiar propietario (si tienes permisos)
chown -R $USER:$USER .

# 3. Eliminar forzado de carpetas problemáticas
rm -rf node_modules backend/.git .vscode

# 4. Eliminar directorios vacíos
find . -type d -empty -delete
```

### 🚨 En Caso de Hosting Compartido

Si estás en hosting compartido y no tienes SSH:

1. **Contactar soporte técnico** con este mensaje:
   ```
   "Tengo problemas para eliminar carpetas en File Manager. 
   Error: 'Directory not empty'. 
   ¿Pueden ayudarme a arreglar permisos en /public_html/Musa?"
   ```

2. **Usar FTP cliente** (FileZilla):
   - Conectar por FTP
   - Cambiar permisos desde FileZilla
   - Eliminar archivos desde FTP

### 📊 Verificar Resultados

Después de aplicar cualquier solución:

1. **Verificar en File Manager:**
   - Las carpetas deberían eliminarse sin error
   - Los permisos deberían ser correctos

2. **Verificar la aplicación:**
   - Ir a: `https://tudominio.com/Musa/`
   - Verificar que todo funciona correctamente

### 💡 Prevenir Problemas Futuros

1. **Configurar .htaccess correctamente** (ya incluido)
2. **No subir carpetas node_modules**
3. **Mantener permisos correctos:**
   - Carpetas: `755`
   - Archivos PHP: `644`
   - Archivos ejecutables: `755`

---

## 🔗 Enlaces Útiles

- **Panel Admin:** `/admin-panel.html`
- **Script de Permisos:** `/fix-cpanel-permissions.php`
- **Verificar DB:** `/test-db-connection.php`

---

**⚠️ Importante:** Haz un respaldo antes de hacer cambios masivos.
