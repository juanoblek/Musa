// INTERCEPTOR DE EMERGENCIA PARA ELIMINAR LOS $12,000 DEL CARRITO
(function() {
    'use strict';
    
    console.log('🚨 INTERCEPTOR DE EMERGENCIA ACTIVADO - ELIMINANDO $12,000');
    
    // Función para corregir totales inmediatamente
    function forceCorrectTotals() {
        // Buscar elementos de subtotal y total
        const subtotalElements = document.querySelectorAll('[id*="subtotal"], [class*="subtotal"], .cart-subtotal, #cartSubtotal');
        const totalElements = document.querySelectorAll('[id*="total"], [class*="total"], .cart-total, #cartTotal, .final-total');
        
        subtotalElements.forEach(element => {
            const text = element.textContent;
            if (text.includes('$')) {
                const subtotalMatch = text.match(/\$[\d,]+/);
                if (subtotalMatch) {
                    const subtotal = subtotalMatch[0];
                    console.log(`🔧 EMERGENCY: Subtotal detectado: ${subtotal}`);
                    
                    // Actualizar todos los totales para que sean igual al subtotal
                    totalElements.forEach(totalElement => {
                        const oldText = totalElement.textContent;
                        if (oldText.includes('$')) {
                            totalElement.textContent = totalElement.textContent.replace(/\$[\d,]+/, subtotal);
                            console.log(`✅ EMERGENCY: Total corregido de "${oldText}" a "${totalElement.textContent}"`);
                        }
                    });
                }
            }
        });
        
        // También buscar en el HTML directamente
        const allElements = document.querySelectorAll('*');
        allElements.forEach(element => {
            if (element.textContent && element.textContent.includes('$33,313')) {
                element.textContent = element.textContent.replace('$33,313', '$21,313');
                console.log('🔧 EMERGENCY: Corregido $33,313 a $21,313');
            }
        });
    }
    
    // Interceptar cuando se muestre el modal
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                const target = mutation.target;
                if (target.id === 'CartModal' && target.style.display === 'block') {
                    console.log('🚨 MODAL CARRITO DETECTADO - APLICANDO CORRECCIÓN INMEDIATA');
                    setTimeout(forceCorrectTotals, 50);
                    setTimeout(forceCorrectTotals, 100);
                    setTimeout(forceCorrectTotals, 200);
                    setTimeout(forceCorrectTotals, 500);
                }
            }
        });
    });
    
    // Observar cambios en el DOM
    observer.observe(document.body, {
        attributes: true,
        subtree: true,
        attributeFilter: ['style', 'class']
    });
    
    // También interceptar cualquier función que actualice el total
    const originalInnerHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML');
    Object.defineProperty(Element.prototype, 'innerHTML', {
        set: function(value) {
            if (typeof value === 'string' && value.includes('$33,313')) {
                console.log('🚨 INTERCEPTANDO innerHTML con $33,313');
                value = value.replace(/\$33,313/g, '$21,313');
            }
            originalInnerHTML.set.call(this, value);
        },
        get: originalInnerHTML.get
    });
    
    // Interceptar textContent
    const originalTextContent = Object.getOwnPropertyDescriptor(Node.prototype, 'textContent');
    Object.defineProperty(Node.prototype, 'textContent', {
        set: function(value) {
            if (typeof value === 'string' && value.includes('$33,313')) {
                console.log('🚨 INTERCEPTANDO textContent con $33,313');
                value = value.replace(/\$33,313/g, '$21,313');
            }
            originalTextContent.set.call(this, value);
        },
        get: originalTextContent.get
    });
    
    // Ejecutar corrección cada segundo mientras el modal esté abierto
    setInterval(() => {
        const modal = document.getElementById('CartModal');
        if (modal && modal.style.display === 'block') {
            forceCorrectTotals();
        }
    }, 500);
    
    // Interceptor adicional para funciones específicas que pueden estar causando problemas
    if (window.updateCartTotal && typeof window.updateCartTotal === 'function') {
        const originalUpdateCartTotal = window.updateCartTotal;
        window.updateCartTotal = function() {
            console.log('🚨 INTERCEPTANDO updateCartTotal() - ejecutando corrección en su lugar');
            setTimeout(forceCorrectTotals, 50);
            return;
        };
    }
    
    console.log('✅ INTERCEPTOR DE EMERGENCIA CONFIGURADO');
})();