/**
 * VERIFICADOR FINAL DE ENVÍO GRATIS
 * Se ejecuta al final para garantizar que todo esté correcto
 */

console.log('🔍 [SHIPPING-VERIFIER] Iniciando verificación final...');

function verifyFreeShipping() {
    console.log('🔍 [SHIPPING-VERIFIER] Ejecutando verificación completa...');
    
    // Verificar localStorage
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    const subtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
    
    console.log('📊 [SHIPPING-VERIFIER] Estado del carrito:', {
        items: cart.length,
        subtotal: subtotal
    });
    
    // Verificar window.carritoTotales
    if (window.carritoTotales) {
        if (window.carritoTotales.shipping !== 0 || window.carritoTotales.total !== window.carritoTotales.subtotal) {
            console.error('🚨 [SHIPPING-VERIFIER] ERROR: window.carritoTotales tiene shipping != 0');
            console.error('🚨 Valores detectados:', window.carritoTotales);
            
            // Forzar corrección
            window.carritoTotales = {
                ...window.carritoTotales,
                shipping: 0,
                envio: 0,
                total: window.carritoTotales.subtotal
            };
            console.log('✅ [SHIPPING-VERIFIER] window.carritoTotales corregido');
        } else {
            console.log('✅ [SHIPPING-VERIFIER] window.carritoTotales correcto');
        }
    }
    
    // Verificar elementos DOM
    let errorsFound = 0;
    
    // Verificar shipping elements
    document.querySelectorAll('[data-shipping]').forEach((el, index) => {
        if (el.textContent && !el.textContent.toLowerCase().includes('gratis') && el.textContent !== '$0') {
            console.error(`🚨 [SHIPPING-VERIFIER] Elemento shipping ${index} incorrecto:`, el.textContent);
            el.textContent = 'Gratis';
            errorsFound++;
        }
    });
    
    // Verificar que totales = subtotales
    const subtotalElements = document.querySelectorAll('[data-subtotal]');
    const totalElements = document.querySelectorAll('[data-total]');
    
    if (subtotalElements.length > 0 && totalElements.length > 0) {
        const subtotalValue = subtotalElements[0].textContent;
        totalElements.forEach((totalEl, index) => {
            if (totalEl.textContent !== subtotalValue) {
                console.error(`🚨 [SHIPPING-VERIFIER] Total ${index} no coincide con subtotal:`, {
                    subtotal: subtotalValue,
                    total: totalEl.textContent
                });
                totalEl.textContent = subtotalValue;
                errorsFound++;
            }
        });
    }
    
    if (errorsFound > 0) {
        console.warn(`🛠️ [SHIPPING-VERIFIER] Se corrigieron ${errorsFound} errores`);
    } else {
        console.log('✅ [SHIPPING-VERIFIER] Todos los elementos están correctos');
    }
    
    // Verificar configuraciones
    if (window.CONFIG && window.CONFIG.SHIPPING) {
        if (window.CONFIG.SHIPPING.STANDARD_SHIPPING !== 0) {
            console.error('🚨 [SHIPPING-VERIFIER] CONFIG.SHIPPING.STANDARD_SHIPPING != 0');
        } else {
            console.log('✅ [SHIPPING-VERIFIER] CONFIG.SHIPPING correcto');
        }
    }
    
    console.log('🔍 [SHIPPING-VERIFIER] Verificación completada');
}

// Ejecutar verificación cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        verifyFreeShipping();
        
        // Configurar verificaciones periódicas
        setInterval(verifyFreeShipping, 10000); // Cada 10 segundos
        
        console.log('✅ [SHIPPING-VERIFIER] Verificador activado - ejecutándose cada 10 segundos');
    }, 2000); // Esperar 2 segundos después de DOMContentLoaded
});

// Función global para verificación manual
window.verifyShipping = verifyFreeShipping;