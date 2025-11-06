# 🎬 CORRECCIÓN: Productos Tipo Video No Se Muestran en Index

## 🔍 Problema Identificado
Los productos con video (tipo `.mp4`, `.mov`, etc.) no se estaban mostrando en el index porque:

1. **Error en atributos HTML**: El nombre del producto contenía comillas (`Chaqueta "Nocturna Rebelde"`) que rompían los atributos HTML
2. **Error en conversión de video**: El script `force-video-conversion.js` intentaba copiar atributos con nombres inválidos

## ❌ Errores en Consola
```
InvalidCharacterError: Failed to execute 'setAttribute' on 'Element': 'rebelde""' is not a valid attribute name.
```

## ✅ Solución Implementada

### 1. Archivo: `Musa/js/force-video-conversion.js`
**Cambio**: Validación de nombres de atributos antes de copiarlos

**Líneas modificadas**: 38-49

```javascript
// ANTES:
Array.from(img.attributes).forEach(attr => {
    if (!['src', 'alt', 'class', 'style'].includes(attr.name)) {
        videoElement.setAttribute(attr.name, attr.value);
    }
});

// DESPUÉS:
Array.from(img.attributes).forEach(attr => {
    if (!['src', 'alt', 'class', 'style'].includes(attr.name)) {
        try {
            // Escapar caracteres especiales en el nombre del atributo
            const safeName = attr.name.replace(/[^\w-]/g, '');
            if (safeName) {
                videoElement.setAttribute(safeName, attr.value);
            }
        } catch (e) {
            // Ignorar atributos problemáticos
        }
    }
});
```

### 2. Archivo: `Musa/index.php`
**Cambio**: Escapar comillas en el nombre del producto antes de pasarlo a funciones HTML

**Líneas modificadas**: 25973-25975

```javascript
// ANTES:
const mediaElement = generarMediaHTMLSincrono(
    img, 
    product.name,  // ← PROBLEMA: "Chaqueta "Nocturna Rebelde""
    "d-block w-100 card-img-top product-image glasseffect", 
    "object-fit: cover; width: 100%; height: 100%;"
);

// DESPUÉS:
// Escapar el nombre del producto para evitar problemas con comillas
const safeName = product.name.replace(/["']/g, '');

const mediaElement = generarMediaHTMLSincrono(
    img, 
    safeName,  // ← CORREGIDO: "Chaqueta Nocturna Rebelde"
    "d-block w-100 card-img-top product-image glasseffect", 
    "object-fit: cover; width: 100%; height: 100%;"
);
```

## 📤 Archivos Para Subir al Hosting

### Via FTP (ftp.musaarion.com:21)
**Usuario**: usuario_musaarion_db@musaarion.com

1. **`Musa/js/force-video-conversion.js`** 
   - Destino: `/public_html/js/force-video-conversion.js`
   - Peso: ~5 KB

2. **`Musa/index.php`**
   - Destino: `/public_html/index.php`
   - Peso: ~1.5 MB

## 🧪 Verificación Post-Subida

### 1. Limpiar Caché del Navegador
```
Ctrl + Shift + R (Windows/Linux)
Cmd + Shift + R (Mac)
```

### 2. Verificar en Consola del Navegador
Abrir https://musaarion.com y verificar:

✅ **Debe aparecer**:
```
✅ Listener recreado para imagen 1
✅ Convertida imagen 1 a video: ...
🎯 Conversión forzada completada: X videos convertidos
```

❌ **No debe aparecer**:
```
❌ Error convirtiendo imagen: InvalidCharacterError
```

### 3. Verificar Visualmente
- Ir a https://musaarion.com
- Scroll hasta la sección "Productos Recientes del Inventario"
- El producto "Chaqueta Nocturna Rebelde" debe aparecer con su video
- Al hacer clic debe abrir el modal correctamente

## 📋 Checklist de Subida

- [ ] Conectar via FTP a ftp.musaarion.com
- [ ] Subir `/public_html/js/force-video-conversion.js`
- [ ] Subir `/public_html/index.php` (¡HACER BACKUP PRIMERO!)
- [ ] Limpiar caché del navegador
- [ ] Verificar https://musaarion.com
- [ ] Abrir consola del navegador (F12)
- [ ] Verificar que no hay errores `InvalidCharacterError`
- [ ] Verificar que el producto con video se muestra
- [ ] Hacer clic en el producto y verificar modal

## ⚠️ Importante: Backup
Antes de subir `index.php`, hacer backup del archivo actual en el hosting:

```bash
# Via SSH o File Manager en cPanel
cp /home4/janithal/public_html/index.php /home4/janithal/public_html/index.php.backup-$(date +%Y%m%d-%H%M%S)
```

O desde File Manager de cPanel:
1. Ir a `/public_html/`
2. Click derecho en `index.php`
3. "Copy"
4. Renombrar a `index.php.backup-2025-11-05`

## 🎯 Resultado Esperado
- ✅ Productos tipo video se muestran correctamente en el index
- ✅ No hay errores en la consola
- ✅ Videos se pueden reproducir en las tarjetas de productos
- ✅ Modal de producto funciona correctamente con videos
