# 🚀 CONFIGURACIÓN DE MERCADOPAGO PARA PRODUCCIÓN

## ✅ CAMBIOS REALIZADOS AUTOMÁTICAMENTE

Los siguientes archivos han sido actualizados para usar el modo PRODUCCIÓN:

### 📄 Archivos de Configuración Frontend
1. **`config/mercadopago-config.js`**
   - ✅ Cambiado `CURRENT: 'PROD'`
   - ⚠️ Falta: Credenciales reales de producción

### 📄 Archivos de Backend
2. **`api/create-preference.php`**  
   - ✅ Configurado para usar token de producción
   - ⚠️ Falta: Token real de producción

## 🔑 PASOS PENDIENTES (REQUERIDOS)

### 1. Obtener Credenciales de Producción
1. Ve a: https://www.mercadopago.com.co/developers/panel
2. Crea una aplicación de producción
3. Obtén tus credenciales:
   - **Public Key de Producción**: `APP_USR-xxxxxxxxx`
   - **Access Token de Producción**: `APP_USR-xxxxxxxxx`

### 2. Actualizar Credenciales Frontend
Edita `config/mercadopago-config.js` y reemplaza:
```javascript
PROD: {
    PUBLIC_KEY: 'TU_PUBLIC_KEY_REAL_AQUI',     // ← Poner tu clave real
    ACCESS_TOKEN: 'TU_ACCESS_TOKEN_REAL_AQUI'  // ← Poner tu token real
},
```

### 3. Actualizar Credenciales Backend  
Edita `api/create-preference.php` línea 23:
```php
$MP_ACCESS_TOKEN = 'TU_ACCESS_TOKEN_REAL_AQUI'; // ← Token real
```

### 4. Verificar URLs de Respuesta
En `config/mercadopago-config.js`, actualiza las URLs:
```javascript
URLS: {
    SUCCESS: 'https://tu-dominio.com/success.html',
    FAILURE: 'https://tu-dominio.com/cancel.html', 
    PENDING: 'https://tu-dominio.com/pending.html'
}
```

## ⚠️ VALIDACIONES IMPORTANTES

### Antes de Activar Producción:
1. **✅ Verificar SSL**: Tu sitio DEBE tener HTTPS
2. **✅ Dominio Real**: No usar localhost en producción
3. **✅ Webhooks**: Configurar notificaciones de pago
4. **✅ Testing**: Probar con cuentas reales pequeñas

### URLs de Testing vs Producción:
- **Testing**: `https://api.mercadopago.com/checkout/preferences` 
- **Producción**: `https://api.mercadopago.com/checkout/preferences` (misma URL)

## 🔄 CÓMO VOLVER A TESTING (EMERGENCIA)

Si necesitas volver rápidamente al modo testing:

1. **Frontend**: En `config/mercadopago-config.js`
```javascript
CURRENT: 'TEST', // Cambiar de 'PROD' a 'TEST'
```

2. **Backend**: En `api/create-preference.php`
```php
$MP_ACCESS_TOKEN = 'TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501';
```

## 📊 MONITOREO DE PAGOS

Una vez en producción, revisa:
- Panel de MercadoPago: https://www.mercadopago.com.co/activities
- Logs de tu servidor para errores
- Webhooks funcionando correctamente

## 🆘 PROBLEMAS COMUNES

1. **Error 401 Unauthorized**: Credenciales incorrectas
2. **Error 400 Bad Request**: Datos de preferencia malformados  
3. **Pagos no aparecen**: Verificar webhooks y URLs

---

**⚠️ CRITICAL**: No olvides actualizar las credenciales reales antes de recibir pagos en vivo.
**🔐 SEGURIDAD**: Nunca expongas tus tokens de producción en código público.