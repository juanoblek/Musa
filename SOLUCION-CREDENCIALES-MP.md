# 🔧 INSTRUCCIONES: Credenciales MercadoPago Inválidas

## Problema detectado
```
Error de MercadoPago: {"code":"unauthorized","message":"invalid access token"}
```

Tu `access_token` de producción en `config/config-global.php` está siendo rechazado por MercadoPago.

## Solución paso a paso

### Opción 1: Obtener nuevas credenciales de producción (RECOMENDADO)

1. **Ingresa a tu cuenta MercadoPago**
   - Ve a: https://www.mercadopago.com.co/developers/panel
   - Inicia sesión con tu cuenta de MercadoPago

2. **Verifica que tu cuenta esté en modo PRODUCCIÓN**
   - En el panel, busca el toggle "Producción / Pruebas"
   - Asegúrate de estar en **modo Producción**

3. **Obtén tus credenciales de PRODUCCIÓN**
   - Ve a: Tus aplicaciones → (Tu app) → Credenciales de producción
   - Copia:
     - **Public Key** (empieza con `APP_USR-...`)
     - **Access Token** (empieza con `APP_USR-...`)

4. **Actualiza `Musa/config/config-global.php`**
   ```php
   // Línea 70-75 aprox.
   if (self::isProduction()) {
       // HOSTING - Credenciales de PRODUCCIÓN
       return [
           'public_key' => 'APP_USR-XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX',
           'access_token' => 'APP_USR-XXXXXXXXXXXXXXXX-XXXXXX-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-XXXXXXXXX',
           'environment' => 'production'
       ];
   }
   ```

5. **Sube el archivo al hosting**
   - Por FTP/SFTP: reemplaza `public_html/Musa/config/config-global.php`
   - Por cPanel File Manager: edita directamente

6. **Verifica con el script que creé**
   ```bash
   curl https://musaarion.com/api/verificar-config.php
   ```
   Debes ver:
   ```json
   {
     "ok": true,
     "mercadopago": {
       "token_validation": {
         "valid": true,
         "message": "✅ Token válido y funcional"
       }
     }
   }
   ```

### Opción 2: Usar modo TEST temporalmente

Si necesitas que funcione YA mientras obtienes las credenciales de producción:

1. **Edita `config-global.php` línea 13-16**:
   ```php
   public static function isProduction() {
       if (self::$isProduction === null) {
           // CAMBIO TEMPORAL: Forzar modo TEST
           self::$isProduction = false; // ← Cambiar true por false
       }
       return self::$isProduction;
   }
   ```

2. **Sube el archivo**

3. **Prueba de nuevo**
   - Ahora usará tus credenciales TEST (que ya funcionan)
   - Los pagos NO serán reales, solo simulados
   - Perfecto para probar que todo fluye correctamente

### Opción 3: Revisar permisos de la aplicación

Si las credenciales son correctas pero aún falla:

1. En el panel de MercadoPago → Tu aplicación → Configuración
2. Verifica que tenga permisos de:
   - ✅ `read` (lectura)
   - ✅ `write` (escritura)
   - ✅ Crear preferencias de pago
3. Si faltan, actívalos y regenera las credenciales

---

## Archivos creados para ayudarte

### 1. `api/verificar-config.php`
Diagnóstico completo de tu configuración:
```bash
# En navegador:
https://musaarion.com/api/verificar-config.php

# O por terminal:
curl https://musaarion.com/api/verificar-config.php
```

Te dirá:
- ✅ Si encuentra el config
- ✅ Si las credenciales están presentes
- ✅ Si el access_token es válido (hace una llamada real a MP)

### 2. `api/health-check.php`
Chequeo rápido de conectividad:
```bash
https://musaarion.com/api/health-check.php
```

---

## Siguiente paso

**Por favor hazme saber cuál opción prefieres**:

A. 🔒 **Producción real**: Dame tus nuevas credenciales (por mensaje privado si quieres) y actualizo el config
B. 🧪 **Modo TEST temporal**: Cambio el flag a `false` para que funcione con credenciales de prueba
C. 🔧 **Ya las tengo**: Confirma que subiste el config actualizado y probamos

Una vez que elijas, en menos de 2 minutos tendrás el botón funcionando.
