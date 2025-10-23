/**
 * INTERCEPTOR GLOBAL PARA FORZAR ENVÍO GRATIS
 * Este archivo garantiza que TODOS los cálculos de carrito tengan shipping = 0
 */

console.log('🚀 [FORCE-FREE-SHIPPING] Cargando interceptor global...');

// ===== INTERCEPTOR PARA VARIABLES GLOBALES =====
let originalCarritoTotales = null;

// Interceptar definición de window.carritoTotales
Object.defineProperty(window, 'carritoTotales', {
    get: function() {
        return originalCarritoTotales;
    },
    set: function(value) {
        if (value && typeof value === 'object') {
            // Forzar shipping = 0 y total = subtotal
            const corregido = {
                ...value,
                shipping: 0,
                envio: 0,
                shippingCost: 0,
                total: value.subtotal || 0
            };
            console.log('🛡️ [FORCE-SHIPPING] carritoTotales corregido:', {
                original: value,
                corregido: corregido
            });
            originalCarritoTotales = corregido;
        } else {
            originalCarritoTotales = value;
        }
    }
});

// ===== INTERCEPTOR PARA CONFIGURACIONES =====
// Interceptar CONFIG.SHIPPING si se intenta cambiar
if (window.CONFIG && window.CONFIG.SHIPPING) {
    const originalConfig = window.CONFIG.SHIPPING;
    
    Object.defineProperty(window.CONFIG, 'SHIPPING', {
        get: function() {
            return {
                ...originalConfig,
                STANDARD_SHIPPING: 0,
                FREE_SHIPPING_MIN: 0,
                costoEnvio: {
                    bogota: 0,
                    cundinamarca: 0,
                    valle: 0,
                    antioquia: 0,
                    atlantico: 0,
                    default: 0
                }
            };
        },
        set: function(value) {
            console.log('🛡️ [FORCE-SHIPPING] Intento de cambiar CONFIG.SHIPPING bloqueado');
        }
    });
}

// ===== INTERCEPTOR PARA FUNCIONES DE CÁLCULO =====
// Lista de funciones conocidas que calculan totales
const functionNames = [
    'calculateTotals',
    'calculateCartTotal', 
    'getCartTotal',
    'updateAllSummaries',
    'updatePaymentSummary',
    'computeTotal',
    'getTotalAmount'
];

// Interceptar funciones en window
functionNames.forEach(funcName => {
    const originalFunc = window[funcName];
    if (typeof originalFunc === 'function') {
        window[funcName] = function(...args) {
            const result = originalFunc.apply(this, args);
            
            // Si el resultado es un objeto con totales, corregirlo
            if (result && typeof result === 'object') {
                if (result.hasOwnProperty('shipping') || result.hasOwnProperty('total')) {
                    const corrected = {
                        ...result,
                        shipping: 0,
                        envio: 0,
                        shippingCost: 0,
                        total: result.subtotal || result.total || 0
                    };
                    console.log(`🛡️ [FORCE-SHIPPING] ${funcName} corregido:`, corrected);
                    return corrected;
                }
            }
            
            return result;
        };
        console.log(`🛡️ [FORCE-SHIPPING] ${funcName} interceptado`);
    }
});

// ===== INTERCEPTOR PARA CONSOLE.LOG =====
// Interceptar console.log para detectar logs con shipping != 0
const originalConsoleLog = console.log;
console.log = function(...args) {
    // Detectar logs que contengan información de shipping
    const logString = args.join(' ');
    
    if (logString.includes('shipping') && (logString.includes('12000') || logString.includes('12,000'))) {
        console.warn('🚨 [FORCE-SHIPPING] Log detectado con shipping != 0:', args);
        
        // Intentar corregir el objeto si es posible
        args.forEach((arg, index) => {
            if (typeof arg === 'object' && arg !== null) {
                if (arg.hasOwnProperty('shipping') && arg.shipping !== 0) {
                    arg.shipping = 0;
                    arg.total = arg.subtotal || 0;
                    console.warn('🛡️ [FORCE-SHIPPING] Objeto corregido en log:', arg);
                }
            }
        });
    }
    
    return originalConsoleLog.apply(console, args);
};

// ===== INTERCEPTOR DOM =====
// Interceptar cambios en elementos con data-total, data-shipping, etc.
function interceptDOMUpdates() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList' || mutation.type === 'characterData') {
                // Verificar elementos con data-shipping
                document.querySelectorAll('[data-shipping]').forEach(el => {
                    if (el.textContent && !el.textContent.includes('Gratis') && !el.textContent.includes('0')) {
                        el.textContent = 'Gratis';
                        console.log('🛡️ [FORCE-SHIPPING] DOM shipping corregido:', el);
                    }
                });
                
                // Verificar que los totales sean iguales a los subtotales
                const subtotalElements = document.querySelectorAll('[data-subtotal]');
                const totalElements = document.querySelectorAll('[data-total]');
                
                if (subtotalElements.length > 0 && totalElements.length > 0) {
                    const subtotalText = subtotalElements[0].textContent;
                    totalElements.forEach(totalEl => {
                        if (totalEl.textContent !== subtotalText) {
                            totalEl.textContent = subtotalText;
                            console.log('🛡️ [FORCE-SHIPPING] DOM total sincronizado con subtotal:', totalEl);
                        }
                    });
                }
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true
    });
    
    console.log('🛡️ [FORCE-SHIPPING] Observer DOM activado');
}

// ===== FUNCIÓN DE CORRECCIÓN GLOBAL =====
window.forceFixShipping = function() {
    console.log('🛡️ [FORCE-SHIPPING] Ejecutando corrección manual global...');
    
    // Corregir localStorage
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    const subtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
    
    // Forzar window.carritoTotales
    window.carritoTotales = {
        subtotal: subtotal,
        shipping: 0,
        envio: 0,
        total: subtotal,
        totalItems: cart.length
    };
    
    // Corregir DOM
    document.querySelectorAll('[data-shipping]').forEach(el => {
        el.textContent = 'Gratis';
    });
    
    document.querySelectorAll('[data-total]').forEach(el => {
        el.textContent = `$${subtotal.toLocaleString()}`;
    });
    
    console.log('✅ [FORCE-SHIPPING] Corrección manual completada');
};

// ===== INICIALIZACIÓN =====
document.addEventListener('DOMContentLoaded', function() {
    interceptDOMUpdates();
    
    // Ejecutar corrección cada 5 segundos como medida preventiva
    setInterval(() => {
        window.forceFixShipping();
    }, 5000);
    
    console.log('✅ [FORCE-SHIPPING] Interceptor completamente activado');
});

console.log('✅ [FORCE-SHIPPING] Interceptor global cargado exitosamente');