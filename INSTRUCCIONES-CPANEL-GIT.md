# 🎯 CONFIGURACIÓN RÁPIDA: cPanel Git

## ✅ Ya hicimos: Código subido a GitHub
```
✅ Commit creado: ebd6e25
✅ Push exitoso a: https://github.com/juanoblek/Musa.git
```

---

## 📋 Ahora en cPanel: Rellena el formulario así

### 1️⃣ Clonar un repositorio
```
✅ ACTIVAR esta opción
```

### 2️⃣ Clonar URL
```
https://github.com/juanoblek/Musa.git
```

### 3️⃣ Ruta del repositorio
```
repos/musa-ecommerce
```
(El sistema agregará automáticamente `/home4/janithal/` al inicio)

### 4️⃣ Nombre del repositorio
```
Musa Ecommerce Deploy
```

### 5️⃣ Click en "Crear"

---

## ⚡ Después de crear el repositorio

### Paso A: Activar "Pull on Deploy"

1. En la lista de repositorios, click en **"Manage"** (Administrar) en tu repo
2. Busca la sección **"Pull or Deploy"**
3. Click en **"Update from Remote"** para traer la última versión

### Paso B: Configurar despliegue automático

1. En la misma página de administración del repo
2. Busca **"Deploy HEAD Commit"** o **"Desplegar HEAD Commit"**
3. En **"Deployment Path"** (Ruta de despliegue), pon:
   ```
   /home4/janithal/public_html
   ```
4. Click en **"Save"** o **"Guardar"**

Esto copiará automáticamente tu carpeta `Musa/` del repo a `public_html/Musa/`

---

## 🚀 Despliegue manual rápido (mientras configuras webhook)

### Opción 1: Por interfaz de cPanel
1. Ve a la lista de repositorios
2. Click en **"Manage"** en tu repo
3. Click en **"Pull or Deploy"**
4. Click en **"Deploy HEAD Commit"**

✅ ¡Listo! Tus cambios están en producción

### Opción 2: Por SSH (más rápido)
```bash
ssh janithal@musaarion.com

# Ir al repo
cd ~/repos/musa-ecommerce

# Actualizar código
git pull origin main

# Copiar a public_html (manual)
rsync -av --delete \
  --exclude='.git' \
  --exclude='logs/' \
  --exclude='test-*.json' \
  Musa/ ~/public_html/Musa/

# Permisos
find ~/public_html/Musa -type d -exec chmod 755 {} \;
find ~/public_html/Musa -type f -exec chmod 644 {} \;
```

---

## 🎯 Próxima vez (después de configurar)

```bash
# En tu PC:
git add .
git commit -m "fix: Ajuste importante"
git push origin main

# En cPanel:
# - Click en "Pull or Deploy" 
# - Click en "Deploy HEAD Commit"
# ¡O configura webhook para que sea automático!
```

---

## ⚠️ IMPORTANTE: Config sensible

**NO copies `config/config-global.php` con tus credenciales reales al repo público**

En el servidor, edita manualmente:
```bash
ssh janithal@musaarion.com
nano ~/public_html/Musa/config/config-global.php

# Poner:
# - isProduction() = true
# - Credenciales de PRODUCCIÓN de MercadoPago válidas
```

---

## 📞 Estado actual

✅ Código en GitHub: https://github.com/juanoblek/Musa/tree/main  
⏳ Pendiente: Clonar repo en cPanel  
⏳ Pendiente: Desplegar a public_html  

**¿Ya creaste el repositorio en cPanel? Dime cuando esté listo para continuar** 🚀
