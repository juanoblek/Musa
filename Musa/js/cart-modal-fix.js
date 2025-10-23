/**
 * CORRECCIÓN INMEDIATA DEL MODAL DEL CARRITO
 * Se ejecuta tan pronto como se detecta el modal abierto
 */

console.log('🚀 [CART-MODAL-FIX] Iniciando corrección inmediata del modal...');

// Función para corregir el modal inmediatamente
function fixCartModalImmediately() {
    console.log('🔧 [CART-MODAL-FIX] Ejecutando corrección del modal...');
    
    // Buscar el modal del carrito
    const cartModal = document.getElementById('CartModal');
    if (!cartModal || !cartModal.classList.contains('show')) {
        return false; // Modal no está abierto
    }
    
    console.log('🎯 [CART-MODAL-FIX] Modal del carrito detectado');
    
    // Recalcular totales desde localStorage
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    const subtotal = cart.reduce((sum, item) => {
        const precio = parseFloat(item.precio || item.price) || 0;
        const cantidad = parseInt(item.cantidad || item.quantity) || 1;
        return sum + (precio * cantidad);
    }, 0);
    
    const shipping = 0; // SIEMPRE GRATIS
    const total = subtotal; // TOTAL = SUBTOTAL
    
    console.log('📊 [CART-MODAL-FIX] Totales calculados:', {
        subtotal: subtotal,
        shipping: shipping,
        total: total,
        formatted: {
            subtotal: `$${subtotal.toLocaleString()}`,
            total: `$${total.toLocaleString()}`
        }
    });
    
    // Corregir elementos específicos del modal
    const modalSubtotalElements = cartModal.querySelectorAll('[data-subtotal]');
    const modalShippingElements = cartModal.querySelectorAll('[data-envio], [data-shipping]');
    const modalTotalElements = cartModal.querySelectorAll('[data-total]');
    
    // Actualizar subtotales
    modalSubtotalElements.forEach((el, index) => {
        const newValue = `$${subtotal.toLocaleString()}`;
        if (el.textContent !== newValue) {
            console.log(`🔄 [CART-MODAL-FIX] Corrigiendo subtotal ${index}: ${el.textContent} → ${newValue}`);
            el.textContent = newValue;
        }
    });
    
    // Actualizar shipping
    modalShippingElements.forEach((el, index) => {
        const newValue = 'GRATIS';
        if (el.textContent !== newValue) {
            console.log(`🔄 [CART-MODAL-FIX] Corrigiendo envío ${index}: ${el.textContent} → ${newValue}`);
            el.textContent = newValue;
        }
    });
    
    // Actualizar totales (CRÍTICO)
    modalTotalElements.forEach((el, index) => {
        const newValue = `$${total.toLocaleString()}`;
        if (el.textContent !== newValue) {
            console.log(`🛠️ [CART-MODAL-FIX] CORRIGIENDO TOTAL ${index}: ${el.textContent} → ${newValue}`);
            el.textContent = newValue;
            el.style.color = '#0d6efd'; // Azul para confirmar que se actualizó
        }
    });
    
    console.log('✅ [CART-MODAL-FIX] Corrección del modal completada');
    return true;
}

// Observer para detectar cuando se abre el modal
function observeCartModal() {
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const cartModal = document.getElementById('CartModal');
                if (cartModal && cartModal.classList.contains('show')) {
                    console.log('👁️ [CART-MODAL-FIX] Modal del carrito abierto detectado');
                    setTimeout(() => {
                        fixCartModalImmediately();
                    }, 100); // Pequeño delay para que el contenido se renderice
                }
            }
            
            // También observar cambios en el contenido del modal
            if (mutation.target.classList && mutation.target.classList.contains('modal-content')) {
                setTimeout(() => {
                    fixCartModalImmediately();
                }, 50);
            }
        });
    });
    
    // Observar el modal del carrito si existe
    const cartModal = document.getElementById('CartModal');
    if (cartModal) {
        observer.observe(cartModal, {
            attributes: true,
            attributeFilter: ['class', 'style'],
            childList: true,
            subtree: true
        });
        console.log('👁️ [CART-MODAL-FIX] Observer del modal activado');
        
        // Si el modal ya está abierto, corregirlo inmediatamente
        if (cartModal.classList.contains('show')) {
            console.log('👁️ [CART-MODAL-FIX] Modal ya abierto, corrigiendo inmediatamente...');
            setTimeout(() => {
                fixCartModalImmediately();
            }, 100);
        }
    }
}

// Interceptar las funciones que abren el modal
function interceptModalFunctions() {
    // Interceptar funciones de cart-system-simple.js
    const originalUpdateAllSummaries = window.updateAllSummaries;
    if (typeof originalUpdateAllSummaries === 'function') {
        window.updateAllSummaries = function(...args) {
            console.log('🔄 [CART-MODAL-FIX] updateAllSummaries interceptado');
            const result = originalUpdateAllSummaries.apply(this, args);
            
            // Forzar corrección del modal después de actualizar
            setTimeout(() => {
                fixCartModalImmediately();
            }, 50);
            
            return result;
        };
        console.log('🔄 [CART-MODAL-FIX] updateAllSummaries interceptado');
    }
}

// Ejecutar corrección periódica mientras el modal esté abierto
function startPeriodicCorrection() {
    setInterval(() => {
        const cartModal = document.getElementById('CartModal');
        if (cartModal && cartModal.classList.contains('show')) {
            fixCartModalImmediately();
        }
    }, 1000); // Cada segundo mientras el modal esté abierto
}

// Función global para corrección manual
window.fixCartModal = fixCartModalImmediately;

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 [CART-MODAL-FIX] DOM cargado, inicializando...');
    
    setTimeout(() => {
        observeCartModal();
        interceptModalFunctions();
        startPeriodicCorrection();
        
        // Corrección inicial si el modal ya existe
        fixCartModalImmediately();
        
        console.log('✅ [CART-MODAL-FIX] Sistema de corrección del modal activado');
    }, 500);
});

// También ejecutar cuando se carga el script
setTimeout(() => {
    observeCartModal();
    fixCartModalImmediately();
}, 100);

console.log('✅ [CART-MODAL-FIX] Script cargado correctamente');