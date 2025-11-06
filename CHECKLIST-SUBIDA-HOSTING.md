🚀 CHECKLIST DE SUBIDA AL HOSTING
====================================

## ✅ Archivos Modificados (SUBIR AL HOSTING)

### 📁 Carpeta: api/
- [ ] api/productos-v2.php
- [ ] api/categorias.php  
- [ ] api/navigation-categories.php

### 📁 Carpeta raíz:
- [ ] test-api-connection.php

### 📁 Carpeta: config/
- [ ] config/config-global.php (verificar que ya esté en el hosting)

---

## 🔍 PASOS PARA SUBIR

### 1. Conectar por FTP/SFTP o usar File Manager de cPanel
```
Host: musaarion.com
Usuario: [tu usuario de cPanel]
Puerto: 21 (FTP) o 22 (SFTP)
```

### 2. Subir archivos
```
Local → Remoto
----------------------------------------------
c:\xampp\htdocs\Musa\Musa\api\productos-v2.php 
  → public_html/api/productos-v2.php

c:\xampp\htdocs\Musa\Musa\api\categorias.php
  → public_html/api/categorias.php

c:\xampp\htdocs\Musa\Musa\api\navigation-categories.php
  → public_html/api/navigation-categories.php

c:\xampp\htdocs\Musa\Musa\test-api-connection.php
  → public_html/test-api-connection.php
```

### 3. Verificar permisos (IMPORTANTE)
```
Todos los archivos .php deben tener: 644
Carpetas deben tener: 755
```

En FileZilla o File Manager:
- Click derecho → Permisos → 644 para archivos

---

## 🧪 VERIFICACIÓN DESPUÉS DE SUBIR

### Paso 1: Test de Conexión
Abre en tu navegador:
```
https://musaarion.com/test-api-connection.php
```

✅ Deberías ver:
- Conexión exitosa a la base de datos
- Lista de productos activos
- Lista de categorías activas
- Botones para probar cada API

❌ Si ves errores:
- Verifica que las credenciales en config-global.php sean correctas
- Verifica que los archivos se hayan subido correctamente
- Revisa los logs de error en cPanel

---

### Paso 2: Test de API de Productos
Abre en tu navegador:
```
https://musaarion.com/api/productos-v2.php
```

✅ Deberías ver:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Producto X",
      "price": 50000,
      ...
    }
  ]
}
```

---

### Paso 3: Test de API de Categorías
Abre en tu navegador:
```
https://musaarion.com/api/categorias.php
```

✅ Deberías ver:
```json
{
  "success": true,
  "data": [
    {
      "id": "1",
      "name": "Camisas",
      "slug": "camisas",
      ...
    }
  ]
}
```

---

### Paso 4: Verifica el Index
Abre en tu navegador:
```
https://musaarion.com/index.php
```

✅ Deberías ver:
- Productos cargándose en el grid principal
- Categorías en la navegación
- No más errores de "0 productos"
- No más "❌ Error cargando categorías"

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Problema: "Error connecting to database"
**Solución:**
1. Verifica las credenciales en `config/config-global.php`:
   ```php
   'host' => 'localhost',
   'dbname' => 'janithal_musa_moda',
   'username' => 'janithal_usuario_musaarion_db',
   'password' => 'Chiguiro553021'
   ```
2. Verifica en phpMyAdmin que la base de datos exista
3. Verifica que el usuario tenga permisos

---

### Problema: "No se encuentran productos"
**Solución:**
1. Abre phpMyAdmin
2. Ejecuta:
   ```sql
   SELECT COUNT(*) FROM products WHERE status = 'active';
   ```
3. Si retorna 0, debes insertar productos

---

### Problema: "No se encuentran categorías"
**Solución:**
1. Abre phpMyAdmin
2. Ejecuta:
   ```sql
   SELECT COUNT(*) FROM categories WHERE status = 'active';
   ```
3. Si retorna 0, debes insertar categorías

---

### Problema: "500 Internal Server Error"
**Solución:**
1. Verifica permisos de archivos (deben ser 644)
2. Revisa error_log en cPanel
3. Verifica que los archivos no tengan caracteres BOM
4. Verifica que las rutas de require_once sean correctas

---

## 📞 COMANDOS ÚTILES (Si tienes acceso SSH)

```bash
# Ver últimos errores
tail -f ~/logs/error_log

# Verificar permisos
ls -la public_html/api/

# Cambiar permisos
chmod 644 public_html/api/*.php
chmod 755 public_html/api/

# Probar API desde servidor
curl https://musaarion.com/api/productos-v2.php
```

---

## ✨ RESULTADO ESPERADO

Después de completar todos los pasos:

✅ Index carga productos correctamente
✅ Navegación muestra categorías
✅ Filtros por categoría funcionan
✅ No más errores en la consola sobre productos/categorías
✅ Las APIs responden con JSON válido

---

## 📌 NOTAS IMPORTANTES

1. **NO subas** archivos de configuración local (con credenciales de localhost)
2. **SIEMPRE** verifica que `config-global.php` tenga las credenciales del hosting
3. **Haz backup** antes de reemplazar archivos en producción
4. **Limpia caché** del navegador después de subir archivos
5. **Los problemas de imágenes** (caracteres raros) los dejamos para después

---

**Última actualización:** 5 de Noviembre, 2025
**Estado:** ✅ Listo para subir al hosting
