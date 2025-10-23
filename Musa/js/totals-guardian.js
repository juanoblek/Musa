/**
 * GUARDIAN DE TOTALES - CORRECCIÓN INMEDIATA
 * Este script se ejecuta constantemente para corregir cualquier total incorrecto
 */

console.log('🛡️ [TOTALS-GUARDIAN] Iniciando guardián de totales...');

let isGuardianActive = false;

// Función para corregir todos los totales incorrectos
function correctAllTotals() {
    if (isGuardianActive) return; // Evitar recursión
    isGuardianActive = true;
    
    try {
        // Calcular el subtotal correcto desde localStorage
        const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        const correctSubtotal = cart.reduce((sum, item) => {
            const precio = parseFloat(item.precio || item.price) || 0;
            const cantidad = parseInt(item.cantidad || item.quantity) || 1;
            return sum + (precio * cantidad);
        }, 0);
        
        const correctTotal = correctSubtotal; // TOTAL = SUBTOTAL (sin envío)
        const correctTotalFormatted = `$${correctSubtotal.toLocaleString()}`;
        
        // Buscar TODOS los elementos de subtotal para verificar consistencia
        const subtotalElements = document.querySelectorAll('[data-subtotal]');
        let detectedSubtotal = correctSubtotal;
        
        if (subtotalElements.length > 0) {
            // Extraer el valor numérico del primer subtotal encontrado
            const subtotalText = subtotalElements[0].textContent || '';
            const subtotalMatch = subtotalText.replace(/[^\d]/g, '');
            if (subtotalMatch) {
                detectedSubtotal = parseInt(subtotalMatch);
            }
        }
        
        // El total SIEMPRE debe ser igual al subtotal
        const finalCorrectTotal = detectedSubtotal;
        const finalCorrectTotalFormatted = `$${finalCorrectTotal.toLocaleString()}`;
        
        // Corregir TODOS los elementos de total
        const totalElements = document.querySelectorAll('[data-total]');
        let correctionsMade = 0;
        
        totalElements.forEach((element, index) => {
            const currentValue = element.textContent || '';
            const currentNumber = parseInt(currentValue.replace(/[^\d]/g, '')) || 0;
            
            // Si el total NO es igual al subtotal, corregirlo
            if (currentNumber !== finalCorrectTotal && finalCorrectTotal > 0) {
                console.warn(`🔥 [TOTALS-GUARDIAN] TOTAL INCORRECTO DETECTADO ${index}:`, {
                    current: currentValue,
                    currentNumber: currentNumber,
                    expected: finalCorrectTotalFormatted,
                    expectedNumber: finalCorrectTotal,
                    difference: currentNumber - finalCorrectTotal
                });
                
                // Corregir inmediatamente
                element.textContent = finalCorrectTotalFormatted;
                element.style.backgroundColor = '#d4edda'; // Verde claro para indicar corrección
                element.style.color = '#155724'; // Verde oscuro
                element.style.transition = 'all 0.3s ease';
                
                // Remover el resaltado después de un tiempo
                setTimeout(() => {
                    element.style.backgroundColor = '';
                    element.style.color = '';
                }, 2000);
                
                correctionsMade++;
            }
        });
        
        // Verificar elementos de envío
        const shippingElements = document.querySelectorAll('[data-envio], [data-shipping]');
        shippingElements.forEach((element, index) => {
            const currentValue = element.textContent || '';
            if (!currentValue.toLowerCase().includes('gratis') && !currentValue.includes('$0')) {
                console.warn(`🔥 [TOTALS-GUARDIAN] ENVÍO INCORRECTO DETECTADO ${index}:`, currentValue);
                element.textContent = 'GRATIS';
                correctionsMade++;
            }
        });
        
        if (correctionsMade > 0) {
            console.warn(`🛠️ [TOTALS-GUARDIAN] Se realizaron ${correctionsMade} correcciones`);
            
            // Actualizar window.carritoTotales si existe
            if (window.carritoTotales) {
                window.carritoTotales = {
                    ...window.carritoTotales,
                    subtotal: finalCorrectTotal,
                    shipping: 0,
                    envio: 0,
                    total: finalCorrectTotal
                };
            }
        }
        
    } catch (error) {
        console.error('❌ [TOTALS-GUARDIAN] Error en corrección:', error);
    } finally {
        isGuardianActive = false;
    }
}

// Interceptar modificaciones DOM
function startDOMInterception() {
    const observer = new MutationObserver((mutations) => {
        let shouldCheck = false;
        
        mutations.forEach((mutation) => {
            // Verificar si se modificó algún elemento relacionado con totales
            if (mutation.type === 'childList' || mutation.type === 'characterData') {
                const target = mutation.target;
                
                // Verificar si el cambio afecta elementos de total, subtotal o shipping
                if (target.hasAttribute && (
                    target.hasAttribute('data-total') ||
                    target.hasAttribute('data-subtotal') ||
                    target.hasAttribute('data-envio') ||
                    target.hasAttribute('data-shipping')
                )) {
                    shouldCheck = true;
                }
                
                // También verificar elementos hijos
                if (target.querySelector) {
                    const hasRelevantElements = target.querySelector('[data-total], [data-subtotal], [data-envio], [data-shipping]');
                    if (hasRelevantElements) {
                        shouldCheck = true;
                    }
                }
            }
        });
        
        if (shouldCheck) {
            // Pequeño delay para permitir que otros scripts terminen
            setTimeout(correctAllTotals, 50);
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true,
        attributes: false
    });
    
    console.log('👁️ [TOTALS-GUARDIAN] Observer DOM activado');
}

// Corrección periódica agresiva
function startPeriodicCorrection() {
    setInterval(correctAllTotals, 500); // Cada 500ms
    console.log('⏰ [TOTALS-GUARDIAN] Corrección periódica activada (cada 500ms)');
}

// Función global para corrección manual
window.correctTotals = correctAllTotals;

// Inicialización inmediata
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        startDOMInterception();
        startPeriodicCorrection();
        correctAllTotals(); // Corrección inicial
        
        console.log('✅ [TOTALS-GUARDIAN] Guardián completamente activado');
    }, 100);
});

// También ejecutar inmediatamente si el DOM ya está listo
if (document.readyState === 'loading') {
    // DOM aún se está cargando
} else {
    // DOM ya está listo
    setTimeout(() => {
        startDOMInterception();
        startPeriodicCorrection();
        correctAllTotals();
    }, 100);
}

console.log('✅ [TOTALS-GUARDIAN] Script cargado');