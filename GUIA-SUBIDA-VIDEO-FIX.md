 # 🚀 GUÍA RÁPIDA: Subir Corrección de Videos al Hosting

## 📁 Archivos Preparados
Los archivos corregidos están en: `c:\xampp\htdocs\Musa\ARCHIVOS-SUBIR-VIDEO-FIX\`

```
📦 ARCHIVOS-SUBIR-VIDEO-FIX/
├── 📄 force-video-conversion.js  (8.32 KB)
└── 📄 index.php                  (2.26 MB)
```

## 🔐 Credenciales FTP
- **Servidor**: ftp.musaarion.com
- **Puerto**: 21
- **Usuario**: usuario_musaarion_db@musaarion.com
- **Contraseña**: (la que configuraste)
- **Directorio**: /public_html

---

## 📤 OPCIÓN 1: FileZilla (Recomendado)

### Paso 1: Conectar
1. Abrir FileZilla
2. Host: `ftp.musaarion.com`
3. Usuario: `usuario_musaarion_db@musaarion.com`
4. Contraseña: Tu contraseña
5. Puerto: `21`
6. Click en "Conexión rápida"

### Paso 2: Hacer Backup (¡IMPORTANTE!)
1. En el panel derecho (servidor), navegar a `/public_html/`
2. Click derecho en `index.php` → **Descargar** (guardar como backup)
3. Click derecho en `index.php` → **Renombrar** → `index.php.backup-2025-11-05`

### Paso 3: Subir Archivos
1. En panel izquierdo, ir a: `c:\xampp\htdocs\Musa\ARCHIVOS-SUBIR-VIDEO-FIX\`
2. En panel derecho, ir a: `/public_html/`

**Subir force-video-conversion.js:**
- Arrastrar `force-video-conversion.js` desde el panel izquierdo
- Soltarlo en `/public_html/js/` en el panel derecho
- Confirmar sobrescritura

**Subir index.php:**
- Arrastrar `index.php` desde el panel izquierdo
- Soltarlo en `/public_html/` en el panel derecho
- Confirmar sobrescritura

---

## 📤 OPCIÓN 2: File Manager de cPanel

### Paso 1: Acceder a File Manager
1. Ir a tu panel de cPanel
2. Buscar "File Manager"
3. Click en File Manager

### Paso 2: Hacer Backup
1. Navegar a `/public_html/`
2. Buscar `index.php`
3. Click derecho → **Copy**
4. Cambiar nombre a: `index.php.backup-2025-11-05`
5. Click "Copy File(s)"

### Paso 3: Subir Archivos
1. Ir a `/public_html/js/`
2. Click en **Upload** (arriba)
3. Seleccionar: `c:\xampp\htdocs\Musa\ARCHIVOS-SUBIR-VIDEO-FIX\force-video-conversion.js`
4. Esperar que termine
5. Volver a `/public_html/`
6. Click en **Upload**
7. Seleccionar: `c:\xampp\htdocs\Musa\ARCHIVOS-SUBIR-VIDEO-FIX\index.php`
8. Esperar que termine (puede tardar por el tamaño)

---

## ✅ Verificación Post-Subida

### 1. Limpiar Caché del Navegador
```
Windows: Ctrl + Shift + R
Mac: Cmd + Shift + R
```

### 2. Abrir el Sitio
Ir a: https://musaarion.com

### 3. Verificar en Consola (F12)
Presionar `F12` → Pestaña "Console"

**Debe mostrar**:
```
✅ Listener recreado para imagen 1
✅ Convertida imagen 1 a video: ...
🎯 Conversión forzada completada: 1 videos convertidos
```

**NO debe mostrar**:
```
❌ Error convirtiendo imagen: InvalidCharacterError  ← Este error debe desaparecer
```

### 4. Verificar Producto con Video
1. Scroll hasta "Productos Recientes del Inventario"
2. Buscar: **Chaqueta Nocturna Rebelde**
3. Debe mostrar el video en la tarjeta
4. Click en el video → debe abrir modal
5. Video debe reproducirse correctamente

---

## ⚠️ Solución de Problemas

### Problema: "El producto aún no aparece"
**Solución**:
1. Limpiar caché del navegador más agresivamente:
   - Chrome: Presionar F12 → Click derecho en botón Refresh → "Empty Cache and Hard Reload"
   - Edge: Configuración → Privacidad → Borrar datos de navegación
2. Probar en navegador privado/incógnito
3. Verificar que ambos archivos se subieron correctamente

### Problema: "Error 500 después de subir index.php"
**Solución**:
1. Restaurar backup:
   - Via FTP: Descargar `index.php.backup-2025-11-05` y renombrar a `index.php`
   - Via cPanel: Copy `index.php.backup-2025-11-05` → Renombrar a `index.php`
2. Revisar logs de error en cPanel → Error Log
3. Contactarme con el error específico

### Problema: "FileZilla no conecta"
**Solución**:
1. Verificar credenciales (usuario/contraseña)
2. Cambiar a modo de transferencia "Pasivo":
   - Edit → Settings → Connection → FTP → Transfer mode: Passive
3. Desactivar temporalmente Firewall/Antivirus
4. Probar con File Manager de cPanel en su lugar

---

## 🎯 Checklist Final

- [ ] Backup de `index.php` realizado
- [ ] `force-video-conversion.js` subido a `/public_html/js/`
- [ ] `index.php` subido a `/public_html/`
- [ ] Caché del navegador limpiado
- [ ] Sitio cargado: https://musaarion.com
- [ ] Consola del navegador verificada (F12)
- [ ] No hay errores `InvalidCharacterError`
- [ ] Producto con video visible
- [ ] Video se reproduce correctamente
- [ ] Modal funciona al hacer click

---

## 💡 Nota Final

Si todo funciona correctamente, deberías ver:
- ✅ El producto "Chaqueta Nocturna Rebelde" con su video
- ✅ Sin errores en la consola
- ✅ Video reproducible en la tarjeta
- ✅ Modal funcionando al hacer click

**¡Ya está listo!** Los productos tipo video ahora se mostrarán correctamente en el index. 🎉
