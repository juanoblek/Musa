# 🚀 Guía Completa: Despliegue Automático Git → cPanel

## 📋 Índice
1. [Configuración inicial en GitHub](#1-configuración-inicial-en-github)
2. [Configuración en cPanel](#2-configuración-en-cpanel)
3. [Despliegue automático con Webhook](#3-despliegue-automático-con-webhook)
4. [Flujo de trabajo diario](#4-flujo-de-trabajo-diario)

---

## 1. Configuración inicial en GitHub

### A. Verificar que tu repo esté actualizado

```bash
# Desde tu carpeta local del proyecto
cd C:\xampp\htdocs\Musa

# Ver estado actual
git status

# Agregar todos los cambios
git add .

# Commit con mensaje descriptivo
git commit -m "feat: Sistema de pago optimizado con fallbacks API"

# Subir a GitHub
git push origin main
```

### B. Crear token de acceso personal (si es privado)

1. Ve a GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Click en "Generate new token (classic)"
3. Nombre: `cPanel Deployment - Musa`
4. Permisos necesarios:
   - ✅ `repo` (acceso completo al repositorio)
5. Copia el token generado (solo se muestra una vez)

---

## 2. Configuración en cPanel

### A. Generar clave SSH (si tu repo es privado)

```bash
# 1. Accede a cPanel → Terminal
# 2. Genera la clave SSH
ssh-keygen -t ed25519 -C "tu-email@ejemplo.com" -f ~/.ssh/github_musa

# 3. Ver la clave pública
cat ~/.ssh/github_musa.pub

# 4. Copiar todo el contenido que empieza con "ssh-ed25519..."
```

**Agregar la clave a GitHub:**
- GitHub → Settings → SSH and GPG keys → New SSH key
- Title: `cPanel - musaarion.com`
- Key: pegar el contenido de `github_musa.pub`
- Guardar

### B. Clonar el repositorio en cPanel

**Opción 1: Interfaz gráfica de cPanel**

1. Ve a: **Git™ Version Control** (o "Control de versiones Git™")
2. Click en **"Create"** (Crear)
3. Rellena el formulario:
   ```
   Clone a Repository: ✅ Activar
   Clone URL: https://github.com/juanoblek/Musa.git
   Repository Path: /home/tu-usuario/repos/musa-ecommerce
   Repository Name: Musa E-commerce
   ```
4. Click en **"Create"**

**Opción 2: Por terminal SSH** (más rápido)

```bash
# 1. Conectar por SSH a tu hosting
ssh tu-usuario@musaarion.com

# 2. Crear carpeta para repositorios
mkdir -p ~/repos
cd ~/repos

# 3. Clonar el repositorio
git clone https://github.com/juanoblek/Musa.git musa-ecommerce

# 4. Entrar al repositorio
cd musa-ecommerce

# 5. Verificar que todo está bien
git status
git log --oneline -5
```

### C. Configurar despliegue automático a public_html

**Crear script de despliegue:**

```bash
# En tu hosting, conectado por SSH
cd ~/repos/musa-ecommerce

# Crear el script de despliegue
nano deploy.sh
```

**Contenido de `deploy.sh`:**

```bash
#!/bin/bash

# 🚀 Script de despliegue automático - Musa E-commerce
# Este script se ejecuta cada vez que haces git push

echo "🔄 Iniciando despliegue..."

# Variables
REPO_DIR="/home/tu-usuario/repos/musa-ecommerce"
PUBLIC_DIR="/home/tu-usuario/public_html"
BACKUP_DIR="/home/tu-usuario/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Crear backup antes de desplegar
echo "💾 Creando backup..."
mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP_DIR/backup_$TIMESTAMP.tar.gz" -C "$PUBLIC_DIR" Musa/ 2>/dev/null || true

# Ir al repositorio
cd "$REPO_DIR"

# Actualizar desde GitHub
echo "📥 Descargando última versión..."
git fetch origin main
git reset --hard origin/main

# Copiar archivos al directorio público (excluyendo sensibles)
echo "📋 Copiando archivos..."
rsync -av --delete \
  --exclude='.git' \
  --exclude='logs/' \
  --exclude='test-*.json' \
  --exclude='.env' \
  --exclude='config/config-global.php' \
  "$REPO_DIR/Musa/" "$PUBLIC_DIR/Musa/"

# Establecer permisos correctos
echo "🔐 Configurando permisos..."
find "$PUBLIC_DIR/Musa" -type d -exec chmod 755 {} \;
find "$PUBLIC_DIR/Musa" -type f -exec chmod 644 {} \;
chmod 755 "$PUBLIC_DIR/Musa/api/"*.php 2>/dev/null || true

# Mantener solo últimos 5 backups
echo "🧹 Limpiando backups antiguos..."
cd "$BACKUP_DIR"
ls -t backup_*.tar.gz | tail -n +6 | xargs rm -f 2>/dev/null || true

echo "✅ Despliegue completado exitosamente!"
echo "📊 Archivos desplegados en: $PUBLIC_DIR/Musa/"
echo "💾 Backup guardado en: $BACKUP_DIR/backup_$TIMESTAMP.tar.gz"
```

**Hacer el script ejecutable:**

```bash
chmod +x deploy.sh

# Probar el script manualmente
./deploy.sh
```

---

## 3. Despliegue automático con Webhook

### A. Crear endpoint de webhook en tu servidor

**Archivo: `public_html/webhook-deploy.php`**

```php
<?php
/**
 * 🔗 Webhook de despliegue automático desde GitHub
 * Este archivo recibe notificaciones de GitHub y ejecuta el script de deploy
 */

// Configuración
define('SECRET_TOKEN', 'TU_TOKEN_SECRETO_AQUI'); // Cámbialo por algo único
define('DEPLOY_SCRIPT', '/home/tu-usuario/repos/musa-ecommerce/deploy.sh');
define('LOG_FILE', '/home/tu-usuario/logs/webhook-deploy.log');

// Función para logging
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(LOG_FILE, "[$timestamp] $message\n", FILE_APPEND);
}

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Verificar firma de GitHub (seguridad)
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if (!empty($signature)) {
    $expected = 'sha256=' . hash_hmac('sha256', $payload, SECRET_TOKEN);
    if (!hash_equals($expected, $signature)) {
        logMessage('⚠️ Firma inválida - Acceso denegado');
        http_response_code(403);
        die('Invalid signature');
    }
}

// Parsear el payload
$data = json_decode($payload, true);

// Verificar que sea un push al branch main
if (!isset($data['ref']) || $data['ref'] !== 'refs/heads/main') {
    logMessage('ℹ️ Evento ignorado (no es push a main)');
    die('Not a push to main branch');
}

// Log del evento
$commits = count($data['commits'] ?? []);
$pusher = $data['pusher']['name'] ?? 'unknown';
logMessage("🚀 Push recibido de $pusher con $commits commits");

// Ejecutar script de despliegue en background
$command = escapeshellcmd(DEPLOY_SCRIPT) . ' > /dev/null 2>&1 &';
exec($command, $output, $return);

if ($return === 0) {
    logMessage('✅ Script de despliegue ejecutado exitosamente');
    echo json_encode(['status' => 'success', 'message' => 'Deployment triggered']);
} else {
    logMessage('❌ Error al ejecutar script de despliegue');
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Deployment failed']);
}
?>
```

### B. Configurar webhook en GitHub

1. Ve a tu repositorio en GitHub
2. Settings → Webhooks → Add webhook
3. Configuración:
   ```
   Payload URL: https://musaarion.com/webhook-deploy.php
   Content type: application/json
   Secret: TU_TOKEN_SECRETO_AQUI (el mismo del webhook-deploy.php)
   
   Which events:
   ✅ Just the push event
   
   Active: ✅ Activado
   ```
4. Click en **"Add webhook"**
5. Verifica que aparezca un ✅ verde en "Recent Deliveries"

---

## 4. Flujo de trabajo diario

### Desarrollo local → Producción en 3 pasos

```bash
# 1️⃣ Hacer cambios en tu código local
# (Editas pago-premium.html, create-preference.php, etc.)

# 2️⃣ Commit y push
git add .
git commit -m "fix: Corregir credenciales MercadoPago"
git push origin main

# 3️⃣ ¡YA ESTÁ! 
# GitHub notifica a tu webhook → ejecuta deploy.sh → actualiza public_html
# Todo en ~15 segundos
```

### Comandos útiles

```bash
# Ver estado del repositorio en hosting (por SSH)
cd ~/repos/musa-ecommerce
git log --oneline -10
git status

# Desplegar manualmente si el webhook falla
./deploy.sh

# Ver logs del webhook
tail -f ~/logs/webhook-deploy.log

# Restaurar un backup si algo sale mal
cd ~/backups
tar -xzf backup_20250106_143022.tar.gz -C ~/public_html/

# Actualizar repo manualmente
cd ~/repos/musa-ecommerce
git pull origin main
```

---

## 🎯 Checklist de verificación

Antes de probar, asegúrate de:

- [ ] Repositorio subido a GitHub con todos los archivos
- [ ] `.gitignore` configurado (no subir config-global.php con credenciales)
- [ ] SSH key agregada a GitHub (si repo es privado)
- [ ] Repositorio clonado en `~/repos/musa-ecommerce`
- [ ] Script `deploy.sh` creado y con permisos de ejecución
- [ ] `webhook-deploy.php` creado en `public_html/`
- [ ] Webhook configurado en GitHub con el secret correcto
- [ ] Carpeta `~/logs/` creada para logs del webhook

---

## 🔥 Ventajas de este setup

✅ **Despliegue en segundos**: Push → 15 seg → Live  
✅ **Backups automáticos**: Cada deploy guarda backup  
✅ **Rollback fácil**: Si algo falla, restauras el backup  
✅ **Seguro**: Webhook con firma, SSH keys, secret tokens  
✅ **Profesional**: Control de versiones completo  
✅ **Sin errores manuales**: No más FTP/cPanel File Manager  

---

## 🆘 Troubleshooting

### Webhook no se ejecuta
```bash
# Verificar logs
tail -50 ~/logs/webhook-deploy.log

# Probar webhook manualmente desde GitHub
# Settings → Webhooks → Recent Deliveries → Redeliver
```

### Permisos de archivo
```bash
# Si aparecen errores de permisos
chmod +x ~/repos/musa-ecommerce/deploy.sh
chmod 755 ~/public_html/webhook-deploy.php
```

### Config sensible no se copia
**¡Perfecto!** `config-global.php` debe estar en `.gitignore` y tener una versión manual en el servidor con tus credenciales reales.

```bash
# En el servidor, editar config real:
nano ~/public_html/Musa/config/config-global.php
# Poner tus credenciales de producción válidas
```

---

## 📞 Siguiente paso

Dime en qué punto estás y te guío:

1. **"Necesito subir mi repo a GitHub"** → Te ayudo con los comandos git
2. **"Ya está en GitHub, quiero configurar cPanel"** → Te doy comandos SSH específicos
3. **"Quiero automatizar todo YA"** → Configuro el webhook completo
4. **"Tengo dudas sobre..."** → Me dices y lo resolvemos

¿Por dónde empezamos? 🚀
