# 📋 Comandos PowerShell para Git Deploy

## Si Git no está en PATH, usa Git Bash o estos comandos:

### Opción A: Usar Git Bash (recomendado)
```bash
# 1. Abrir Git Bash (desde menú inicio o click derecho en carpeta)
cd /c/xampp/htdocs/Musa

# 2. Ver estado
git status

# 3. Agregar archivos nuevos
git add GUIA-DESPLIEGUE-GIT-CPANEL.md
git add SETUP-RAPIDO-GIT-DEPLOY.md
git add SOLUCION-CREDENCIALES-MP.md
git add deploy.sh
git add webhook-deploy.php
git add Musa/api/verificar-config.php
git add Musa/api/health-check.php
git add Musa/api/create-preference.php
git add Musa/config/config-global.php

# O agregar todo:
git add .

# 4. Commit
git commit -m "feat: Sistema completo de despliegue Git automático + fix credenciales MP"

# 5. Push a GitHub
git push origin main
```

### Opción B: Usar PowerShell con ruta completa
```powershell
# Cambiar directorio
Set-Location "C:\xampp\htdocs\Musa"

# Usar ruta completa de git.exe (ajustar según tu instalación)
& "C:\Program Files\Git\bin\git.exe" status
& "C:\Program Files\Git\bin\git.exe" add .
& "C:\Program Files\Git\bin\git.exe" commit -m "feat: Sistema de despliegue automático"
& "C:\Program Files\Git\bin\git.exe" push origin main
```

### Opción C: Agregar Git al PATH de Windows

```powershell
# Ver dónde está Git instalado
Get-ChildItem -Path "C:\Program Files\Git\bin\git.exe" -ErrorAction SilentlyContinue
Get-ChildItem -Path "C:\Program Files (x86)\Git\bin\git.exe" -ErrorAction SilentlyContinue

# Agregar al PATH de la sesión actual (temporal)
$env:Path += ";C:\Program Files\Git\bin"

# Ahora git debería funcionar
git --version
git status
```

---

## 📦 Resumen de archivos creados

### Para despliegue automático:
- ✅ `GUIA-DESPLIEGUE-GIT-CPANEL.md` - Guía completa paso a paso
- ✅ `SETUP-RAPIDO-GIT-DEPLOY.md` - Setup en 10 minutos
- ✅ `deploy.sh` - Script que ejecuta el despliegue en hosting
- ✅ `webhook-deploy.php` - Endpoint que recibe notificaciones de GitHub

### Para diagnóstico MercadoPago:
- ✅ `SOLUCION-CREDENCIALES-MP.md` - Cómo obtener credenciales válidas
- ✅ `Musa/api/verificar-config.php` - Verifica config y prueba token MP
- ✅ `Musa/api/health-check.php` - Chequeo rápido del sistema

### Actualizados:
- ✅ `Musa/api/create-preference.php` - Corregido para no dar 500 silencioso
- ✅ `Musa/config/config-global.php` - Modo TEST temporal hasta obtener credenciales válidas

---

## 🎯 Siguiente acción inmediata

### 1. Subir cambios a GitHub

```bash
# Abrir Git Bash en C:\xampp\htdocs\Musa
cd /c/xampp/htdocs/Musa

# Verificar qué cambió
git status

# Agregar todo
git add .

# Commit
git commit -m "feat: Sistema despliegue automático + fix credenciales MP en modo TEST"

# Push
git push origin main
```

### 2. Configurar en hosting (SSH)

```bash
# Conectar por SSH
ssh tu-usuario@musaarion.com

# Clonar repo (solo primera vez)
mkdir -p ~/repos
cd ~/repos
git clone https://github.com/juanoblek/Musa.git musa-ecommerce

# Configurar deploy script
cd musa-ecommerce
chmod +x deploy.sh

# Crear logs
mkdir -p ~/logs

# Copiar webhook a public_html
cp webhook-deploy.php ~/public_html/

# IMPORTANTE: Editar webhook con tus rutas
nano ~/public_html/webhook-deploy.php
# Cambiar TU_USUARIO por tu usuario real de hosting
# Guardar: Ctrl+O, Enter, Ctrl+X

# Probar deploy manual
./deploy.sh
```

### 3. Configurar webhook en GitHub

1. Generar token secreto:
   ```bash
   openssl rand -hex 32
   ```

2. GitHub → tu repo → Settings → Webhooks → Add webhook
   ```
   URL: https://musaarion.com/webhook-deploy.php
   Content type: application/json
   Secret: (el token generado)
   Events: Just the push event
   Active: ✅
   ```

### 4. ¡Probar!

```bash
# En tu PC:
cd C:\xampp\htdocs\Musa
echo "# Test deploy" >> TEST.md
git add TEST.md
git commit -m "test: Probar deploy automático"
git push origin main

# Verificar (15 segundos después):
# - GitHub → Webhooks → Recent Deliveries → ✅
# - SSH: tail ~/logs/webhook-deploy.log
# - Navegador: https://musaarion.com/Musa/TEST.md
```

---

## 💡 Próximos pasos después del deploy

1. **Credenciales MP de producción**:
   - Obtener de https://www.mercadopago.com.co/developers/panel
   - Actualizar `config/config-global.php` en hosting (manual, no en repo)
   - Cambiar `isProduction()` a `return true;`
   - Probar con: https://musaarion.com/api/verificar-config.php

2. **Opcional: Automatizar más**:
   - Crear script de rollback automático
   - Notificaciones por email/Slack en cada deploy
   - Tests automáticos antes de desplegar

---

¿En qué paso necesitas ayuda? 🚀
