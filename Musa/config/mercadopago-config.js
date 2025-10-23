// ==========================================
// CONFIGURACIÓN MERCADO PAGO - MUSA & ARION
// ==========================================

const MERCADOPAGO_CONFIG = {
    // ===== CREDENCIALES DE PRUEBA =====
    TEST: {
        PUBLIC_KEY: 'TEST-70da85e6-8bcb-4f2c-9c62-d8532ae88a4a',
        ACCESS_TOKEN: 'TEST-3757332100534516-071917-bd86e8dc74bdc5dbc732e7d3ceef16ea-285063501'
    },
    
    // ===== CREDENCIALES DE PRODUCCIÓN =====
    // ✅ CREDENCIALES REALES CONFIGURADAS  
    PROD: {
        PUBLIC_KEY: 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577',
        ACCESS_TOKEN: 'APP_USR-8879308926901796-091612-6d947ae0a8df1bbbee8c6cf8ad1bf1be-295005340'
    },
    
    // ===== CONFIGURACIÓN ACTUAL =====
    // ✅ CAMBIADO A PRODUCCIÓN PARA PAGOS REALES
    CURRENT: 'PROD',
    
    // ===== INFORMACIÓN DE LA APLICACIÓN =====
    APP_INFO: {
        USER_ID: '285063501',
        APP_ID: '3757332100534516',
        INTEGRATION_TYPE: 'CheckoutPro', // Checkout Pro para redirección
        VERSION: '1.0.0'
    },
    
    // ===== URLs DE RESPUESTA =====
    URLS: {
        SUCCESS: window.location.origin + '/success.html',
        FAILURE: window.location.origin + '/cancel.html', 
        PENDING: window.location.origin + '/pending.html'
    },
    
    // ===== CONFIGURACIÓN DE PAGOS =====
    PAYMENT_CONFIG: {
        currency: 'COP', // Peso colombiano
        country: 'CO',   // Colombia
        
        // Métodos de pago habilitados - EXPLÍCITAMENTE CONFIGURADO PARA TARJETAS
        payment_methods: {
            excluded_payment_methods: [], // Vacío = todos habilitados
            excluded_payment_types: [],   // Vacío = todos habilitados
            installments: 12,             // Hasta 12 cuotas
            default_payment_method_id: null,
            default_installments: null
        },
        
        // Configuración de envío
        shipping_cost: 0, // ENVÍO SIEMPRE GRATIS
        free_shipping_from: 0, // Envío siempre gratis
        
        // Impuestos (si aplica)
        taxes: {
            iva: 0.19 // 19% IVA
        }
    },
    
    // ===== CONFIGURACIÓN DE LA TIENDA =====
    STORE_INFO: {
        name: 'Musa & Arion',
        email: 'contacto@musayarion.com',
        phone: '+57 300 123 4567',
        address: 'Bogotá, Colombia'
    }
};

// ===== FUNCIONES AUXILIARES =====

// Obtener configuración actual
function getMercadoPagoConfig() {
    const config = MERCADOPAGO_CONFIG[MERCADOPAGO_CONFIG.CURRENT];
    
    // Validar que las credenciales estén configuradas
    if (!config.PUBLIC_KEY || !config.ACCESS_TOKEN) {
        throw new Error('Credenciales de Mercado Pago no configuradas');
    }
    
    // Validar formato de credenciales de prueba
    if (MERCADOPAGO_CONFIG.CURRENT === 'TEST') {
        if (!config.PUBLIC_KEY.startsWith('TEST-') || !config.ACCESS_TOKEN.startsWith('TEST-')) {
            throw new Error('Credenciales de prueba inválidas');
        }
    }
    
    return {
        PUBLIC_KEY: config.PUBLIC_KEY,
        ACCESS_TOKEN: config.ACCESS_TOKEN,
        ...MERCADOPAGO_CONFIG
    };
}

// Verificar si está en modo de prueba
function isTestMode() {
    return MERCADOPAGO_CONFIG.CURRENT === 'TEST';
}

// ===== FUNCIÓN PARA VALIDAR CREDENCIALES =====
async function validateMercadoPagoCredentials() {
    try {
        const config = getMercadoPagoConfig();
        
        console.log('🔍 Validando credenciales de Mercado Pago...');
        console.log('Public Key:', config.PUBLIC_KEY.substring(0, 20) + '...');
        console.log('Access Token:', config.ACCESS_TOKEN.substring(0, 20) + '...');
        
        // Hacer una petición de prueba para validar el access token
        const response = await fetch('https://api.mercadopago.com/v1/account/settings', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${config.ACCESS_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });
        
        if (response.ok) {
            console.log('✅ Credenciales válidas');
            return true;
        } else {
            const errorData = await response.text();
            console.error('❌ Credenciales inválidas:', response.status, errorData);
            return false;
        }
        
    } catch (error) {
        console.error('❌ Error validando credenciales:', error);
        return false;
    }
}

// Formatear precio para Mercado Pago
function formatPrice(price) {
    return parseFloat(price.toString().replace(/[^\d.-]/g, ''));
}

// Logging de configuración
console.log('🔧 Mercado Pago configurado:', {
    mode: MERCADOPAGO_CONFIG.CURRENT,
    store: MERCADOPAGO_CONFIG.STORE_INFO.name,
    currency: MERCADOPAGO_CONFIG.PAYMENT_CONFIG.currency
});

// ===== VALIDACIÓN DE CONFIGURACIÓN =====
function validateMercadoPagoConfig() {
    const config = getMercadoPagoConfig();
    const errors = [];
    
    if (!config.PUBLIC_KEY || config.PUBLIC_KEY.includes('tu-public-key')) {
        errors.push('❌ PUBLIC_KEY no configurado correctamente');
    }
    
    if (!config.ACCESS_TOKEN || config.ACCESS_TOKEN.includes('tu-access-token')) {
        errors.push('❌ ACCESS_TOKEN no configurado correctamente');
    }
    
    if (errors.length > 0) {
        console.warn('⚠️ Configuración de Mercado Pago incompleta:', errors);
        return false;
    }
    
    console.log('✅ Configuración de Mercado Pago validada correctamente');
    return true;
}

// Validar configuración al cargar
document.addEventListener('DOMContentLoaded', function() {
    validateMercadoPagoConfig();
});

/* 
==========================================
INSTRUCCIONES DE CONFIGURACIÓN:
==========================================

1. OBTENER CREDENCIALES:
   - Ve a: https://developers.mercadopago.com
   - Crea una aplicación
   - Copia tu Public Key y Access Token

2. CONFIGURAR CREDENCIALES:
   - Reemplaza los valores de TEST con tus credenciales reales
   - Para producción, configura también PROD

3. CAMBIAR A PRODUCCIÓN:
   - Cambia CURRENT: 'TEST' por CURRENT: 'PROD'
   - Asegúrate de tener credenciales de producción

4. PERSONALIZAR:
   - Actualiza STORE_INFO con tu información
   - Ajusta precios de envío en PAYMENT_CONFIG
   - Modifica URLs de respuesta según tu estructura

==========================================
*/
