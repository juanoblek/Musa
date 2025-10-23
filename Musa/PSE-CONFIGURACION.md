# 🏦 Sistema PSE - Pagos Seguros en Línea

## 📋 **Configuración Actual**

### ✅ **MODO PRODUCCIÓN (ACTIVO)**
- **Archivo**: `api/create-preference-pse-production.php`
- **Credenciales**: Producción real de MercadoPago
- **Estado**: PAGOS REALES - El dinero se transfiere efectivamente
- **Access Token**: `APP_USR-3757332100534516-071917-e5e42e9dc4b69ffaaf64d59e8faf00d1-285063501`

### 🧪 **Modo Sandbox (Disponible)**
- **Archivo**: `api/create-preference-pse-sandbox.php`
- **Credenciales**: Sandbox/Testing de MercadoPago
- **Estado**: PAGOS DE PRUEBA - No se transfiere dinero real
- **Access Token**: `TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501`

## 🔧 **Cambiar Modo de PSE**

### Para usar Modo Sandbox (Pruebas):
1. En `pago-premium.html` línea ~3713:
```javascript
const endpoint = isPSE ? 'api/create-preference-pse-sandbox.php' : 'api/create-preference-premium.php';
```

### Para usar Modo Producción (Actual):
```javascript
const endpoint = isPSE ? 'api/create-preference-pse-production.php' : 'api/create-preference-premium.php';
```

## ⚠️ **IMPORTANTE**

- **PRODUCCIÓN**: Los pagos PSE son REALES, el dinero se debita de las cuentas bancarias de los clientes
- **SANDBOX**: Los pagos son simulados, usar solo para pruebas
- **Verificación**: El modo actual se muestra en la interfaz PSE con el badge "MODO PRODUCCIÓN"

## 🏗️ **Archivos del Sistema PSE**

1. **Frontend**: `pago-premium.html` (formulario PSE + lógica de pago)
2. **Backend Producción**: `api/create-preference-pse-production.php`
3. **Backend Sandbox**: `api/create-preference-pse-sandbox.php`
4. **Guardado**: `api/guardar-pedido-real.php` (mismo para ambos modos)
5. **Simulador Local**: `pse-simulator.html` (solo para desarrollo)

## 📊 **Flujo de Pago PSE**

1. Cliente llena formulario PSE
2. Se crea preferencia en MercadoPago (producción/sandbox)
3. Cliente es redirigido al banco para autenticación
4. Banco procesa el pago
5. Cliente regresa con resultado
6. Sistema guarda pedido en base de datos
7. Se envía email de confirmación

## 🔍 **Testing**

- **Producción**: Usar datos bancarios reales
- **Sandbox**: Usar datos de prueba de MercadoPago
- **Local**: Usar simulador `pse-simulator.html`

## 📱 **Bancos Soportados**

PSE soporta todos los principales bancos colombianos:
- Bancolombia
- Banco de Bogotá
- Davivienda
- BBVA
- Banco Popular
- Y más...

---
**Última actualización**: Octubre 8, 2025
**Estado actual**: ✅ PRODUCCIÓN ACTIVA