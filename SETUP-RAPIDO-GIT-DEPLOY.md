# 🚀 SETUP RÁPIDO: Git Deploy en 10 minutos

## ✅ Paso 1: Verificar Git localmente (2 min)

```bash
# Abrir PowerShell en C:\xampp\htdocs\Musa
cd C:\xampp\htdocs\Musa

# Ver status
git status

# Si hay cambios pendientes, commitearlos
git add .
git commit -m "feat: Sistema completo con despliegue automático"

# Verificar que esté conectado a GitHub
git remote -v
# Debe mostrar: origin https://github.com/juanoblek/Musa.git

# Subir a GitHub
git push origin main
```

---

## ✅ Paso 2: Conectar por SSH a tu hosting (1 min)

```bash
# Desde PowerShell o PuTTY
ssh tu-usuario@musaarion.com
# Ingresa tu contraseña de cPanel
```

---

## ✅ Paso 3: Clonar repositorio en hosting (2 min)

```bash
# Una vez conectado por SSH:

# Crear carpeta para repos
mkdir -p ~/repos
cd ~/repos

# Clonar tu repositorio
git clone https://github.com/juanoblek/Musa.git musa-ecommerce

# Verificar
cd musa-ecommerce
ls -la
```

---

## ✅ Paso 4: Configurar script de deploy (2 min)

```bash
# Copiar el script desde el repo al directorio correcto
cd ~/repos/musa-ecommerce
cp deploy.sh .
chmod +x deploy.sh

# Editar variables si es necesario (opcional)
nano deploy.sh
# Presiona Ctrl+O para guardar, Ctrl+X para salir

# Probar el script manualmente
./deploy.sh
```

**Debe mostrar:**
```
🔄 Iniciando despliegue...
💾 Creando backup...
✅ Backup creado: backup_20250106_151234.tar.gz
📥 Descargando última versión desde GitHub...
✅ Código actualizado a último commit
📋 Copiando archivos a public_html...
✅ Archivos copiados
🔐 Configurando permisos...
✅ Permisos configurados
✅ ¡Despliegue completado exitosamente!
```

---

## ✅ Paso 5: Configurar webhook (3 min)

### A. Crear carpeta de logs
```bash
mkdir -p ~/logs
```

### B. Subir webhook-deploy.php

**Opción A: Por cPanel File Manager**
1. Entra a cPanel → Administrador de archivos
2. Ve a `public_html/`
3. Sube el archivo `webhook-deploy.php`
4. Edita el archivo y cambia:
   ```php
   define('SECRET_TOKEN', 'tu_token_super_secreto_aqui');
   define('DEPLOY_SCRIPT', '/home/TU_USUARIO/repos/musa-ecommerce/deploy.sh');
   define('LOG_FILE', '/home/TU_USUARIO/logs/webhook-deploy.log');
   ```

**Opción B: Por SSH**
```bash
# Subir desde tu repo local
cd ~/public_html
nano webhook-deploy.php
# Pegar el contenido, editar las 3 líneas de arriba
# Ctrl+O, Ctrl+X

# Dar permisos
chmod 644 webhook-deploy.php
```

### C. Generar token secreto

```bash
# En tu hosting o local:
openssl rand -hex 32
# Copiar el resultado, ej: a4f8e...
```

### D. Configurar webhook en GitHub

1. Ve a: https://github.com/juanoblek/Musa/settings/hooks
2. Click en **"Add webhook"**
3. Configurar:
   ```
   Payload URL: https://musaarion.com/webhook-deploy.php
   Content type: application/json
   Secret: (pegar el token generado con openssl)
   
   Which events would you like to trigger this webhook?
   ○ Just the push event
   
   ✅ Active
   ```
4. Click en **"Add webhook"**
5. Debe aparecer un ✅ verde

---

## 🎯 ¡LISTO! Probar el sistema

```bash
# En tu PC local:
cd C:\xampp\htdocs\Musa

# Hacer un cambio de prueba
echo "# Test deploy" >> TEST.md

# Commit y push
git add TEST.md
git commit -m "test: Probar despliegue automático"
git push origin main

# En 15 segundos, verifica en tu hosting:
# 1. GitHub → Settings → Webhooks → Recent Deliveries
#    Debe mostrar: ✅ 200 OK
# 
# 2. Por SSH:
tail -f ~/logs/webhook-deploy.log
#    Debe mostrar: "Push recibido..." "Despliegue completado..."
#
# 3. En navegador:
https://musaarion.com/Musa/TEST.md
#    Debe aparecer el archivo
```

---

## 🔥 Flujo diario (10 segundos)

```bash
# 1. Editas código localmente
# 2. Commit y push
git add .
git commit -m "fix: Ajustar botón de pago"
git push origin main

# 3. ¡YA ESTÁ EN PRODUCCIÓN! 🚀
```

---

## 🆘 Si algo falla

### Webhook no se ejecuta
```bash
# Ver logs
ssh tu-usuario@musaarion.com
tail -50 ~/logs/webhook-deploy.log
```

### Permisos
```bash
chmod +x ~/repos/musa-ecommerce/deploy.sh
chmod 644 ~/public_html/webhook-deploy.php
```

### Restaurar backup
```bash
cd ~/backups
ls -lt  # Ver backups disponibles
tar -xzf backup_FECHA_HORA.tar.gz -C ~/public_html/
```

---

## 📞 ¿Dónde estás?

Dime en qué paso necesitas ayuda:
- **Paso 1-2**: Configuración local/SSH
- **Paso 3-4**: Clonar repo y deploy script
- **Paso 5**: Webhook de GitHub
- **Prueba final**: Verificar que todo funcione

¡Vamos! 🚀
