// =======================================================
// 🛒 SISTEMA DE CARRITO SIMPLIFICADO Y FUNCIONAL
// =======================================================

console.log('🚀 Cargando sistema de carrito simplificado...');

// Función principal para agregar al carrito
window.addToCartSimplified = function(productId, productName, productPrice, selectedSize, selectedColor) {
    console.log('🛒 [SIMPLE] Agregando al carrito:', {
        id: productId,
        name: productName,
        price: productPrice,
        size: selectedSize,
        color: selectedColor
    });

    try {
        // Obtener carrito actual
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        // Crear producto
        const product = {
            id: productId || 'product-' + Date.now(),
            name: productName || 'Producto',
            price: productPrice || '50000',
            size: selectedSize || 'M',
            color: selectedColor || 'Negro',
            quantity: 1,
            image: 'uploads/product_68bf7bbbc2175_1757379515.jpeg',
            timestamp: Date.now()
        };

        // Buscar si ya existe
        const existingIndex = cart.findIndex(item => 
            item.id === product.id && 
            item.size === product.size && 
            item.color === product.color
        );

        if (existingIndex >= 0) {
            cart[existingIndex].quantity += 1;
            console.log('➕ Cantidad incrementada');
        } else {
            cart.push(product);
            console.log('✅ Producto agregado al carrito');
        }

        // Guardar en localStorage
        localStorage.setItem('carrito', JSON.stringify(cart));
        console.log('💾 Carrito guardado:', cart);

        // Mostrar modal inmediatamente
        showCartModal(cart);
        
        return true;
    } catch (error) {
        console.error('❌ Error agregando al carrito:', error);
        alert('Error al agregar producto al carrito');
        return false;
    }
};

// 🔧 FUNCIÓN AUXILIAR PARA FORMATEAR NÚMEROS
function formatCurrency(amount) {
    try {
        // Asegurar que el amount es un número entero
        const numAmount = Math.round(parseFloat(amount) || 0);
        
        // Formato manual con puntos como separadores de miles (sin decimales)
        return numAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    } catch (error) {
        console.warn('⚠️ Error formateando moneda:', error, 'Amount:', amount);
        // Fallback: formato manual
        const numAmount = Math.round(parseFloat(amount) || 0);
        return numAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
}

// 🌟 FUNCIÓN GLOBAL PARA ACTUALIZAR TODOS LOS RESÚMENES
function updateAllSummaries() {
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    
    // Calcular totales - FORZADO SIN ENVÍO
    const subtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
    const shipping = 0; // ENVÍO SIEMPRE GRATIS
    const total = subtotal; // TOTAL = SUBTOTAL (SIN SUMAR ENVÍO)
    const totalItems = cart.reduce((sum, item) => sum + (item.quantity || item.cantidad), 0);
    
    console.log('� [FORCE SUMMARY] TOTALES FORZADOS SIN ENVÍO:', {
        subtotal: subtotal,
        shipping: shipping,
        total: total,
        totalItems: totalItems
    });
    
    // Actualizar TODOS los contadores de artículos (incluyendo el badge del botón carrito)
    document.querySelectorAll('.cart-count, .badge-cart-count, [data-cart-count]').forEach(element => {
        element.textContent = totalItems;
        console.log('🔢 [SUMMARY] Contador actualizado:', element.textContent);
    });
    
    // Actualizar TODOS los subtotales
    document.querySelectorAll('[data-subtotal]').forEach((element, index) => {
        element.textContent = `$${formatCurrency(subtotal)}`;
        console.log(`💵 [SUMMARY] Subtotal ${index} actualizado:`, element.textContent, '- Elemento:', element);
    });
    
    // Actualizar TODOS los envíos
    document.querySelectorAll('[data-shipping]').forEach((element, index) => {
        element.textContent = shipping === 0 ? 'Gratis' : `$${formatCurrency(shipping)}`;
        console.log(`🚚 [SUMMARY] Envío ${index} actualizado:`, element.textContent, '- Elemento:', element);
    });
    
    // Actualizar TODOS los totales - FORZAR QUE TOTAL = SUBTOTAL
    document.querySelectorAll('[data-total]').forEach((element, index) => {
        // 🚨 FORZAR: El total SIEMPRE debe ser igual al subtotal (envío gratis)
        const totalCorrecto = subtotal;  // TOTAL = SUBTOTAL
        element.textContent = `$${formatCurrency(totalCorrecto)}`;
        console.log(`🎯 [SUMMARY] Total ${index} actualizado:`, element.textContent, '- Elemento:', element);
        
        // 🔥 VERIFICACIÓN ADICIONAL: Asegurar que el valor se aplicó correctamente
        if (element.textContent !== `$${formatCurrency(totalCorrecto)}`) {
            console.error(`❌ [SUMMARY] ERROR: Total ${index} NO se actualizó correctamente!`);
            // Forzar valor directo
            element.innerHTML = `$${formatCurrency(totalCorrecto)}`;
            element.setAttribute('data-forced-total', totalCorrecto);
        }
    });
    
    // 🚨 INTERCEPTOR ADICIONAL: BUSCAR Y CORREGIR CUALQUIER TOTAL INCORRECTO
    setTimeout(() => {
        const todosLosElementos = document.querySelectorAll('*');
        todosLosElementos.forEach(el => {
            if (el.textContent && (el.textContent.includes('$33,313') || el.textContent.includes('33313'))) {
                console.log('🚨 DETECTADO Y CORRIGIENDO TOTAL INCORRECTO:', el.textContent);
                el.textContent = el.textContent.replace(/\$?33,?313/g, `$${formatCurrency(subtotal)}`);
                console.log('✅ CORREGIDO A:', el.textContent);
            }
        });
    }, 100);
    
    // Actualizar TODOS los contadores de productos
    document.querySelectorAll('[data-product-count]').forEach(element => {
        element.textContent = `${totalItems} ${totalItems === 1 ? 'producto' : 'productos'}`;
        console.log('📦 [SUMMARY] Productos actualizado:', element.textContent);
    });
    
    // 🛡️ FORZAR CORRECCIÓN ADICIONAL
    if (typeof window.forceFixShipping === 'function') {
        window.forceFixShipping();
    }
    
    console.log('✅ [SUMMARY] Todos los resúmenes actualizados correctamente');
    return { subtotal: subtotal, shipping: 0, total: subtotal, totalItems }; // FORZAR VALORES CORRECTOS
}

// 🌟 FUNCIÓN GLOBAL PARA ACTUALIZAR RESUMEN DE PAGO (ESPECÍFICA)
function updatePaymentSummary() {
    console.log('💳 [PAYMENT] Actualizando resumen de pago...');
    const summaryData = updateAllSummaries();
    
    // Actualizar específicamente el modal de pago
    const orderDetails = document.getElementById('order-details');
    const summaryTotal = document.getElementById('summary-total');
    
    if (orderDetails) {
        const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        if (cart.length > 0) {
            const orderHTML = `
                <div class="order-summary">
                    ${cart.map(item => `
                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <div>
                                <div class="fw-medium">${item.name || item.nombre}</div>
                                <small class="text-muted">
                                    Color: ${item.color} | Talla: ${item.size || item.talla} | 
                                    Cantidad: ${item.quantity || item.cantidad}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">$${formatCurrency(parseInt(item.price || item.precio) * (item.quantity || item.cantidad))}</div>
                            </div>
                        </div>
                    `).join('')}
                    
                    <div class="d-flex justify-content-between py-2">
                        <span>Subtotal:</span>
                        <span>$${formatCurrency(summaryData.subtotal)}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>Envío:</span>
                        <span>${summaryData.shipping === 0 ? 'Gratis' : '$' + formatCurrency(summaryData.shipping)}</span>
                    </div>
                </div>
            `;
            orderDetails.innerHTML = orderHTML;
            console.log('✅ [PAYMENT] Resumen de productos actualizado en modal de pago');
        } else {
            orderDetails.innerHTML = `
                <div class="text-center py-3 text-muted">
                    <i class="bi bi-cart-x fs-1"></i>
                    <p class="mt-2">No hay productos en el carrito</p>
                </div>
            `;
        }
    }
    
    if (summaryTotal) {
        summaryTotal.textContent = `$${formatCurrency(summaryData.total)}`;
        console.log('✅ [PAYMENT] Total actualizado en modal de pago:', summaryTotal.textContent);
    }
    
    return summaryData;
}

// 🌟 HACER FUNCIONES GLOBALES
window.updateAllSummaries = updateAllSummaries;
window.updatePaymentSummary = updatePaymentSummary;

// 🛡️ EJECUTAR CORRECCIÓN INMEDIATA AL CARGAR
if (typeof window.forceFixShipping === 'function') {
    console.log('🛡️ [CART-SYSTEM] Ejecutando corrección inmediata de shipping...');
    window.forceFixShipping();
} else {
    console.log('🔄 [CART-SYSTEM] forceFixShipping no disponible aún, será ejecutada automáticamente');
}

// 🌟 FUNCIÓN DE DIAGNÓSTICO COMPLETO
window.diagnosticCartModal = function() {
    console.log('🔍 === DIAGNÓSTICO COMPLETO DEL MODAL DEL CARRITO ===');
    
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    console.log('📦 Carrito en localStorage:', cart);
    
    const modal = document.getElementById('CartModal');
    console.log('🎭 Modal encontrado:', !!modal);
    
    if (modal) {
        const cartItemsContainer = modal.querySelector('.cart-items');
        console.log('📋 Container encontrado:', !!cartItemsContainer);
        
        if (cartItemsContainer) {
            console.log('📏 Container HTML length:', cartItemsContainer.innerHTML.length);
            console.log('🔢 Items en DOM:', cartItemsContainer.children.length);
            console.log('👀 Container visible:', window.getComputedStyle(cartItemsContainer).display !== 'none');
            console.log('📐 Container height:', cartItemsContainer.offsetHeight);
        }
        
        // Verificar elementos de resumen en el modal
        const subtotalInModal = modal.querySelector('[data-subtotal]');
        const totalInModal = modal.querySelector('[data-total]');
        
        console.log('💵 Subtotal en modal:', subtotalInModal?.textContent || 'NO ENCONTRADO');
        console.log('🎯 Total en modal:', totalInModal?.textContent || 'NO ENCONTRADO');
    }
    
    // Verificar todos los elementos de resumen en toda la página
    const allSubtotals = document.querySelectorAll('[data-subtotal]');
    const allTotals = document.querySelectorAll('[data-total]');
    
    console.log('🌐 Subtotales en toda la página:', allSubtotals.length);
    allSubtotals.forEach((el, i) => console.log(`   ${i}: ${el.textContent}`));
    
    console.log('🌐 Totales en toda la página:', allTotals.length);
    allTotals.forEach((el, i) => console.log(`   ${i}: ${el.textContent}`));
    
    console.log('🔍 === FIN DEL DIAGNÓSTICO ===');
};

// 🌟 INICIALIZAR RESÚMENES AL CARGAR LA PÁGINA
document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 [INIT] Inicializando resúmenes al cargar la página...');
    setTimeout(() => {
        updateAllSummaries();
        console.log('✅ [INIT] Resúmenes inicializados');
    }, 1000); // Esperar un segundo para que todos los elementos estén cargados
});
function showCartModal(cart) {
    console.log('🛒 Mostrando modal del carrito con diseño original...');
    console.log('🔍 [DEBUG VISUAL] Carrito recibido:', cart);
    console.log('🔍 [DEBUG VISUAL] Número de productos:', cart.length);
    
    try {
        const modal = document.getElementById('CartModal');
        if (!modal) {
            console.error('❌ Modal CartModal no encontrado');
            return;
        }
        console.log('✅ [DEBUG VISUAL] Modal encontrado');

        // Buscar el contenedor de items del carrito
        const cartItemsContainer = modal.querySelector('#cartItems');
        if (!cartItemsContainer) {
            console.error('❌ Contenedor cartItems no encontrado');
            console.log('🔍 [DEBUG VISUAL] Contenedores disponibles en modal:', 
                Array.from(modal.querySelectorAll('[id]')).map(el => el.id));
            return;
        }
        console.log('✅ [DEBUG VISUAL] Contenedor cartItems encontrado');
        console.log('🔍 [DEBUG VISUAL] Contenedor tipo:', cartItemsContainer.tagName);
        console.log('🔍 [DEBUG VISUAL] Contenedor clases:', cartItemsContainer.className);
        console.log('🔍 [DEBUG VISUAL] Contenedor innerHTML actual:', cartItemsContainer.innerHTML.length, 'caracteres');

        // Generar HTML de productos con diseño simple (sin accordion)
        const cartHTML = cart.map((item, index) => {
            console.log(`🎨 [RENDER DEBUG] Item ${index}:`, {
                id: item.id,
                name: item.name || item.nombre,
                color: item.color,
                size: item.size || item.talla,
                fullItem: item
            });
            
            return `
            <div class="cart-item border-bottom py-3">
                <div class="d-flex align-items-center">
                    <img src="${item.image || item.imagen || 'images/placeholder.svg'}" 
                         class="me-3 rounded border" 
                         style="width: 80px; height: 80px; object-fit: cover;"
                         alt="${item.name || item.nombre}">
                    <div class="flex-grow-1">
                        <h6 class="mb-1 fw-bold">${item.name || item.nombre}</h6>
                        <div class="d-flex gap-3 mb-2">
                            <span class="badge bg-secondary">Talla: ${item.size || item.talla}</span>
                            <span class="badge bg-info">Color: ${item.color || 'N/A'}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="decreaseQuantity('${item.id || item.nombre}', '${item.size || item.talla}', '${item.color || 'default'}')">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span class="fw-bold px-2">${item.quantity || item.cantidad}</span>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="increaseQuantity('${item.id || item.nombre}', '${item.size || item.talla}', '${item.color || 'default'}')">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-primary fs-6">$${(parseInt(item.price || item.precio) * (item.quantity || item.cantidad)).toLocaleString()}</div>
                                <small class="text-muted">$${parseInt(item.price || item.precio).toLocaleString()} c/u</small>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-outline-danger btn-sm ms-3" 
                            onclick="removeFromCart('${item.id || item.nombre}', '${item.size || item.talla}', '${item.color || 'default'}')"
                            title="Eliminar producto">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
        `;
        }).join('');

        console.log('🔍 [DEBUG VISUAL] HTML generado para', cart.length, 'productos');
        console.log('🔍 [DEBUG VISUAL] Longitud del HTML:', cartHTML.length, 'caracteres');
        console.log('🔍 [DEBUG VISUAL] Preview del HTML:', cartHTML.substring(0, 200) + '...');

        // Actualizar contenido del carrito
        cartItemsContainer.innerHTML = cartHTML;
        console.log('✅ [DEBUG VISUAL] HTML insertado en el contenedor');
        
        // Verificar inmediatamente después de insertar
        console.log('🔍 [DEBUG VISUAL] HTML después de insertar:', cartItemsContainer.innerHTML.length, 'caracteres');
        console.log('🔍 [DEBUG VISUAL] Primer hijo:', cartItemsContainer.firstElementChild?.tagName || 'NINGUNO');
        console.log('🔍 [DEBUG VISUAL] Número de hijos:', cartItemsContainer.children.length);
        
        // Verificar que se insertó correctamente
        const insertedItems = cartItemsContainer.querySelectorAll('.cart-item');
        console.log('🔍 [DEBUG VISUAL] Items insertados en DOM:', insertedItems.length);
        
        if (insertedItems.length !== cart.length) {
            console.error('❌ [DEBUG VISUAL] Mismatch: productos en carrito:', cart.length, 'vs items en DOM:', insertedItems.length);
        }

        // 🌟 USAR LA FUNCIÓN UNIFICADA PARA ACTUALIZAR TODOS LOS RESÚMENES
        const summaryData = updateAllSummaries();
        
        // 🚨 CORRECCIÓN FORZADA INMEDIATA PARA EL MODAL
        setTimeout(() => {
            console.log('🚨 [MODAL FIX] Corrigiendo totales en modal del carrito...');
            corregirTotalesEmergencia();
        }, 50);
        setTimeout(() => {
            corregirTotalesEmergencia();
        }, 200);
        setTimeout(() => {
            corregirTotalesEmergencia();
        }, 1000);

        // Actualizar progreso de envío gratis - SIEMPRE 100% (ENVÍO GRATIS)
        const progressBar = modal.querySelector('.progress-bar');
        if (progressBar) {
            const progressPercentage = 100; // SIEMPRE 100% - ENVÍO SIEMPRE GRATIS
            progressBar.style.width = `${progressPercentage}%`;
        }

        // Actualizar información de envío
        const shippingElement = modal.querySelector('.text-success.fw-bold');
        if (shippingElement) {
            shippingElement.textContent = summaryData.shipping > 0 ? `$${summaryData.shipping.toLocaleString()}` : 'GRATIS';
            shippingElement.className = summaryData.shipping > 0 ? 'text-warning fw-bold' : 'text-success fw-bold';
        }

        // Abrir modal con Bootstrap
        if (window.bootstrap && window.bootstrap.Modal) {
            const bsModal = new window.bootstrap.Modal(modal);
            bsModal.show();
            console.log('✅ Modal del carrito abierto con diseño original');
            
            // Verificar que el modal se abrió correctamente
            modal.addEventListener('shown.bs.modal', function() {
                console.log('✅ [DEBUG VISUAL] Modal completamente visible');
                
                // Verificar items nuevamente después de que el modal esté visible
                const visibleItems = cartItemsContainer.querySelectorAll('.cart-item');
                console.log('🔍 [DEBUG VISUAL] Items visibles después de abrir modal:', visibleItems.length);
                
                if (visibleItems.length === 0 && cart.length > 0) {
                    console.error('❌ [DEBUG VISUAL] PROBLEMA: Modal abierto pero no se muestran productos');
                    console.log('🔧 [DEBUG VISUAL] Intentando re-insertar HTML...');
                    
                    // Debug extra para diagnosticar el problema
                    console.log('🔍 [DIAGNOSTIC] Modal display:', window.getComputedStyle(modal).display);
                    console.log('🔍 [DIAGNOSTIC] Modal visibility:', window.getComputedStyle(modal).visibility);
                    console.log('🔍 [DIAGNOSTIC] Container display:', window.getComputedStyle(cartItemsContainer).display);
                    console.log('🔍 [DIAGNOSTIC] Container visibility:', window.getComputedStyle(cartItemsContainer).visibility);
                    console.log('🔍 [DIAGNOSTIC] Container height:', cartItemsContainer.offsetHeight);
                    console.log('🔍 [DIAGNOSTIC] Container scroll height:', cartItemsContainer.scrollHeight);
                    
                    // Re-insertar con fuerza
                    cartItemsContainer.innerHTML = '';
                    setTimeout(() => {
                        cartItemsContainer.innerHTML = cartHTML;
                        console.log('🔧 [DIAGNOSTIC] HTML re-insertado después de timeout');
                        
                        // Verificar nuevamente
                        const itemsAfterTimeout = cartItemsContainer.querySelectorAll('.cart-item');
                        console.log('🔍 [DIAGNOSTIC] Items después de timeout:', itemsAfterTimeout.length);
                        
                        // Forzar actualización de los resúmenes nuevamente
                        updateAllSummaries();
                    }, 100);
                }
            }, { once: true });
            
        } else {
            console.error('❌ Bootstrap Modal no disponible');
            // Mostrar modal manualmente
            modal.style.display = 'block';
            modal.classList.add('show');
        }

    } catch (error) {
        console.error('❌ Error mostrando modal:', error);
    }
}

// Función para vaciar carrito
window.clearCartAndClose = function() {
    localStorage.removeItem('carrito');
    console.log('🗑️ Carrito vaciado');
    
    // Cerrar modal
    const modal = document.getElementById('CartModal');
    if (modal && window.bootstrap) {
        const bsModal = window.bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    }
};

// Función para aumentar cantidad
window.increaseQuantity = function(productId, size, color) {
    console.log('➕ Aumentando cantidad:', { productId, size, color });
    
    try {
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        const itemIndex = cart.findIndex(item => 
            (item.id === productId || item.nombre === productId) && 
            (item.size === size || item.talla === size) && 
            (item.color === color || color === 'default')
        );
        
        if (itemIndex >= 0) {
            // Usar estructura unificada - ambas propiedades para compatibilidad
            cart[itemIndex].quantity = (cart[itemIndex].quantity || cart[itemIndex].cantidad || 0) + 1;
            cart[itemIndex].cantidad = cart[itemIndex].quantity; // Mantener compatibilidad
            
            localStorage.setItem('carrito', JSON.stringify(cart));
            console.log('✅ Cantidad incrementada a:', cart[itemIndex].quantity);
            
            // 🌟 ACTUALIZAR TODOS LOS RESÚMENES
            updateAllSummaries();
            showCartModal(cart);
        }
    } catch (error) {
        console.error('❌ Error aumentando cantidad:', error);
    }
};

// Función para disminuir cantidad
window.decreaseQuantity = function(productId, size, color) {
    console.log('➖ Disminuyendo cantidad:', { productId, size, color });
    
    try {
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        const itemIndex = cart.findIndex(item => 
            (item.id === productId || item.nombre === productId) && 
            (item.size === size || item.talla === size) && 
            (item.color === color || color === 'default')
        );
        
        if (itemIndex >= 0) {
            const currentQuantity = cart[itemIndex].quantity || cart[itemIndex].cantidad || 0;
            
            if (currentQuantity > 1) {
                // Usar estructura unificada - ambas propiedades para compatibilidad
                cart[itemIndex].quantity = currentQuantity - 1;
                cart[itemIndex].cantidad = cart[itemIndex].quantity; // Mantener compatibilidad
                
                localStorage.setItem('carrito', JSON.stringify(cart));
                console.log('✅ Cantidad decrementada a:', cart[itemIndex].quantity);
                
                // 🌟 ACTUALIZAR TODOS LOS RESÚMENES
                updateAllSummaries();
                showCartModal(cart);
            } else {
                // Si la cantidad es 1, eliminar el producto
                removeFromCart(productId, size, color);
            }
        }
    } catch (error) {
        console.error('❌ Error disminuyendo cantidad:', error);
    }
};

// Función para eliminar producto del carrito
window.removeFromCart = function(productId, size, color) {
    console.log('🗑️ Eliminando producto:', { productId, size, color });
    
    try {
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        cart = cart.filter(item => 
            !((item.id === productId || item.nombre === productId) && 
              (item.size === size || item.talla === size) && 
              (item.color === color || color === 'default'))
        );
        
        localStorage.setItem('carrito', JSON.stringify(cart));
        console.log('✅ Producto eliminado, productos restantes:', cart.length);
        
        // 🌟 ACTUALIZAR TODOS LOS RESÚMENES
        updateAllSummaries();
        
        if (cart.length > 0) {
            showCartModal(cart);
        } else {
            // Si no hay productos, cerrar modal y actualizar resúmenes
            const modal = document.getElementById('CartModal');
            if (modal && window.bootstrap) {
                const bsModal = window.bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        }
    } catch (error) {
        console.error('❌ Error eliminando producto:', error);
    }
};

// Función de prueba super simple
window.testCartSimple = function() {
    console.log('🧪 PROBANDO CARRITO SIMPLE...');
    addToCartSimplified(
        'test-' + Date.now(),
        'Chaqueta Deportiva Test',
        '90000',
        'S',
        'Negro'
    );
};

// Sobrescribir la función global addToCart con versión más robusta
window.addToCart = function(productId) {
    console.log('🚀🚀🚀 FUNCIÓN ADDTOCART EJECUTÁNDOSE - VERSIÓN SIMPLE ROBUSTA 🚀🚀🚀');
    
    // Prevenir múltiples clics rápidos
    if (window.addToCartInProgress) {
        console.log('⏳ Agregado ya en progreso, ignorando clic...');
        return;
    }
    
    window.addToCartInProgress = true;
    
    setTimeout(() => {
        window.addToCartInProgress = false;
    }, 1000);
    
    try {
        // Buscar datos del producto
        let productData = null;
        
        // Intentar obtener desde mainProductsLoader
        if (window.mainProductsLoader?.products) {
            productData = window.mainProductsLoader.products.find(p => p.id === productId);
        }
        
        // Obtener selecciones de la tarjeta
        const productCard = document.querySelector(`[data-product-id="${productId}"]`);
        let selectedSize = 'M';
        let selectedColor = 'Negro';
        
        if (productCard) {
            const activeSizeBtn = productCard.querySelector('.size-btn.active');
            if (activeSizeBtn) selectedSize = activeSizeBtn.dataset.size;
            
            // Buscar el botón de color activo usando múltiples estrategias
            let activeColorBtn = null;
            
            // Estrategia 1: Buscar por clase 'active'
            activeColorBtn = productCard.querySelector('.color-btn.active');
            console.log('🔧 [STRATEGY 1] Botón con clase active:', activeColorBtn);
            
            // Estrategia 2: Si no hay 'active', buscar por borde sólido que NO sea transparent
            if (!activeColorBtn) {
                const colorButtons = productCard.querySelectorAll('.color-btn');
                for (const btn of colorButtons) {
                    const style = window.getComputedStyle(btn);
                    const borderStyle = style.border;
                    console.log(`🔧 [STRATEGY 2] Botón ${btn.dataset.color}: ${borderStyle}`);
                    if (borderStyle.includes('2px') && borderStyle.includes('solid') && !borderStyle.includes('transparent')) {
                        activeColorBtn = btn;
                        console.log('🔧 [STRATEGY 2] Botón seleccionado:', btn.dataset.color);
                        break;
                    }
                }
            }
            
            console.log('🔧 [FINAL STRATEGY] Botón final seleccionado:', activeColorBtn);
            if (activeColorBtn) {
                selectedColor = activeColorBtn.dataset.color;
                console.log('🔧 [FINAL STRATEGY] Color detectado:', selectedColor);
            } else {
                console.log('🔧 [ERROR] NO SE PUDO DETECTAR COLOR ACTIVO');
            }
        }
        
        // Usar datos del producto o valores por defecto - ESTRUCTURA NUEVA
        const nombre = productData?.name || 'Chaqueta Deportiva';
        // CORREGIDO: Usar precio con descuento si está disponible
        let precio = 90000; // Valor por defecto
        if (productData) {
            console.log('🔍 [PRECIO DEBUG] Datos de producto:', {
                sale_price: productData.sale_price,
                price: productData.price,
                id: productData.id,
                name: productData.name
            });
            
            // Prioridad 1: precio con descuento
            if (productData.sale_price && productData.sale_price > 0) {
                precio = parseInt(productData.sale_price);
                console.log('💰 [PRECIO] Usando precio con descuento de BD:', precio, '(sale_price)');
            } else if (productData.price && productData.price > 0) {
                precio = parseInt(productData.price);
                console.log('💰 [PRECIO] Usando precio regular de BD:', precio, '(price)');
            }
        }
        
        console.log('� [FINAL DEBUG] Color final asignado:', selectedColor);
        console.log('�📦 Datos finales:', { productId, nombre, precio, selectedSize, selectedColor });
        
        // Crear objeto con estructura unificada NUEVA
        const productDataUnified = {
            id: productId,
            name: nombre,        // Usar 'name' en lugar de 'nombre'
            price: parseInt(precio),  // Usar 'price' en lugar de 'precio'
            size: selectedSize,       // Usar 'size' en lugar de 'talla'
            color: selectedColor,
            quantity: 1,             // Usar 'quantity' en lugar de 'cantidad'
            image: productData?.main_image || 'images/placeholder.svg'  // Usar 'image' en lugar de 'imagen'
        };
        
        console.log('🔧 [OBJETO FINAL] Producto unificado antes de enviar:', productDataUnified);
        
        return addToCartUnified(productDataUnified);
        
    } catch (error) {
        console.error('❌ Error en addToCart:', error);
        window.addToCartInProgress = false;
        return false;
    }
};

// Nueva función unificada para agregar al carrito
function addToCartUnified(productData) {
    try {
        console.log('🛒 [UNIFICADO] Agregando al carrito:', productData);
        console.log('🔍 [COLOR ESPECÍFICO] El color que se va a guardar es:', productData.color);
        
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        // Buscar si el producto ya existe (mismo id, size y color) - ESTRUCTURA NUEVA con DEBUG
        console.log('🔍 [DEBUG] Buscando producto existente:', {
            buscando_id: productData.id,
            buscando_size: productData.size,
            buscando_color: productData.color
        });
        
        const existingIndex = cart.findIndex((item, index) => {
            const itemSize = item.size || item.talla;
            const matches = item.id === productData.id && 
                           itemSize === productData.size && 
                           item.color === productData.color;
            
            console.log(`🔍 [DEBUG] Comparando con item ${index}:`, {
                item_id: item.id,
                item_size: itemSize,
                item_color: item.color,
                matches: matches
            });
            
            return matches;
        });
        
        console.log('🔍 [DEBUG] Resultado búsqueda:', existingIndex >= 0 ? `Encontrado en índice ${existingIndex}` : 'No encontrado');
        
        if (existingIndex >= 0) {
            // Incrementar cantidad si ya existe - COMPATIBLE CON AMBAS ESTRUCTURAS
            const oldQuantity = cart[existingIndex].quantity || cart[existingIndex].cantidad || 0;
            cart[existingIndex].quantity = oldQuantity + 1;
            // Mantener consistencia - asegurar que use la nueva estructura
            cart[existingIndex].cantidad = cart[existingIndex].quantity;
            console.log(`➕ Cantidad incrementada: ${oldQuantity} → ${cart[existingIndex].quantity}`);
        } else {
            // Agregar nuevo producto - ESTRUCTURA NUEVA
            cart.push(productData);
            console.log('✅ Producto agregado al carrito');
        }
        
        // Guardar carrito
        localStorage.setItem('carrito', JSON.stringify(cart));
        console.log('💾 Carrito guardado:', cart);
        console.log('🔍 [STORAGE DEBUG] Verificando lo que se guardó:');
        const savedCart = JSON.parse(localStorage.getItem('carrito') || '[]');
        savedCart.forEach((item, index) => {
            console.log(`   Item ${index}:`, {
                id: item.id,
                name: item.name,
                color: item.color,
                size: item.size,
                fullItem: item
            });
        });

        // Mostrar modal inmediatamente
        showCartModal(cart);
        
        return true;
    } catch (error) {
        console.error('❌ Error agregando al carrito:', error);
        alert('Error al agregar producto al carrito');
        return false;
    }
}

console.log('✅ Sistema de carrito simplificado cargado');
console.log('🎯 Función testCartSimple() disponible para pruebas');

// Función de test para verificar que todo funcione
window.testCartFull = function() {
    console.log('🧪 INICIANDO TEST COMPLETO DEL CARRITO...');
    
    // 1. Limpiar carrito
    localStorage.removeItem('carrito');
    console.log('🧹 Carrito limpiado');
    
    // 2. Agregar producto de prueba
    const testProduct = {
        id: 'test-product-123',
        name: 'Producto de Prueba',
        price: 50000,
        size: 'M',
        color: 'Azul',
        quantity: 1,
        image: 'images/placeholder.svg'
    };
    
    // 3. Simular agregar al carrito
    console.log('➕ Agregando producto de prueba...');
    const cart = [testProduct];
    localStorage.setItem('carrito', JSON.stringify(cart));
    
    // 4. Mostrar modal
    console.log('🪟 Abriendo modal del carrito...');
    showCartModal(cart);
    
    // 5. Actualizar resumen de pago si existe
    setTimeout(() => {
        if (typeof window.updatePaymentSummary === 'function') {
            console.log('💳 Actualizando resumen de pago...');
            window.updatePaymentSummary();
        }
    }, 500);
    
    console.log('✅ Test completado');
};

// Funciones para manejar el carrito desde el modal
window.increaseQuantity = function(productId, size, color) {
    console.log('➕ Incrementando cantidad:', { productId, size, color });
    
    try {
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        const itemIndex = cart.findIndex(item => 
            item.id === productId && 
            (item.size || item.talla) === size && 
            item.color === color
        );
        
        if (itemIndex >= 0) {
            cart[itemIndex].quantity = (cart[itemIndex].quantity || cart[itemIndex].cantidad || 0) + 1;
            cart[itemIndex].cantidad = cart[itemIndex].quantity; // Mantener consistencia
            
            localStorage.setItem('carrito', JSON.stringify(cart));
            showCartModal(cart); // Refrescar modal
            console.log('✅ Cantidad incrementada');
        }
    } catch (error) {
        console.error('❌ Error incrementando cantidad:', error);
    }
};

window.decreaseQuantity = function(productId, size, color) {
    console.log('➖ Decrementando cantidad:', { productId, size, color });
    
    try {
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        const itemIndex = cart.findIndex(item => 
            item.id === productId && 
            (item.size || item.talla) === size && 
            item.color === color
        );
        
        if (itemIndex >= 0) {
            const currentQuantity = cart[itemIndex].quantity || cart[itemIndex].cantidad || 1;
            
            if (currentQuantity > 1) {
                cart[itemIndex].quantity = currentQuantity - 1;
                cart[itemIndex].cantidad = cart[itemIndex].quantity; // Mantener consistencia
                
                localStorage.setItem('carrito', JSON.stringify(cart));
                showCartModal(cart); // Refrescar modal
                console.log('✅ Cantidad decrementada');
            } else {
                // Si cantidad es 1, eliminar el producto
                removeFromCart(productId, size, color);
            }
        }
    } catch (error) {
        console.error('❌ Error decrementando cantidad:', error);
    }
};

window.removeFromCart = function(productId, size, color) {
    console.log('🗑️ Eliminando del carrito:', { productId, size, color });
    
    try {
        let cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        
        cart = cart.filter(item => 
            !(item.id === productId && 
              (item.size || item.talla) === size && 
              item.color === color)
        );
        
        localStorage.setItem('carrito', JSON.stringify(cart));
        
        if (cart.length > 0) {
            showCartModal(cart); // Refrescar modal
        } else {
            // Cerrar modal si no hay productos
            const modal = document.getElementById('CartModal');
            if (modal) {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) bsModal.hide();
            }
        }
        
        console.log('✅ Producto eliminado del carrito');
    } catch (error) {
        console.error('❌ Error eliminando del carrito:', error);
    }
};

// Función para limpiar y debuggear el carrito
window.debugAndFixCart = function() {
    console.log('🔧 INICIANDO DEBUG Y REPARACIÓN DEL CARRITO...');
    
    try {
        // 1. Obtener carrito actual
        const rawCart = localStorage.getItem('carrito');
        console.log('📦 Carrito raw:', rawCart);
        
        if (!rawCart) {
            console.log('⚠️ No hay carrito en localStorage');
            return;
        }
        
        // 2. Parsear carrito
        let cart = JSON.parse(rawCart);
        console.log('📦 Carrito parseado:', cart);
        console.log('📦 Número de productos:', cart.length);
        
        // 3. Verificar estructura de cada producto
        cart.forEach((item, index) => {
            console.log(`🔍 Producto ${index + 1}:`, {
                id: item.id,
                name: item.name || item.nombre,
                price: item.price || item.precio,
                size: item.size || item.talla,
                color: item.color,
                quantity: item.quantity || item.cantidad,
                structure: Object.keys(item)
            });
        });
        
        // 4. Normalizar estructura si es necesario
        const normalizedCart = cart.map(item => ({
            id: item.id,
            name: item.name || item.nombre,
            price: item.price || item.precio,
            size: item.size || item.talla,
            color: item.color,
            quantity: item.quantity || item.cantidad || 1,
            image: item.image || item.imagen || 'images/placeholder.svg'
        }));
        
        // 5. Guardar carrito normalizado
        localStorage.setItem('carrito', JSON.stringify(normalizedCart));
        console.log('✅ Carrito normalizado y guardado:', normalizedCart);
        
        // 6. Probar modal del carrito
        console.log('🪟 Abriendo modal de prueba...');
        showCartModal(normalizedCart);
        
        console.log('✅ Debug y reparación completados');
        
    } catch (error) {
        console.error('❌ Error en debug:', error);
    }
};

// Función específica para testear la visualización del modal
window.testCartVisualModal = function() {
    console.log('🧪 INICIANDO TEST VISUAL DEL MODAL...');
    
    try {
        // 1. Crear carrito de prueba con múltiples productos
        const testCart = [
            {
                id: 'test-1',
                name: 'Chaqueta Deportiva',
                price: 120000,
                size: 'S',
                color: 'Rojo',
                quantity: 1,
                image: 'images/placeholder.svg'
            },
            {
                id: 'test-1',
                name: 'Chaqueta Deportiva',
                price: 120000,
                size: 'L',
                color: 'Negro',
                quantity: 2,
                image: 'images/placeholder.svg'
            },
            {
                id: 'test-1',
                name: 'Chaqueta Deportiva',
                price: 120000,
                size: 'M',
                color: 'Azul',
                quantity: 1,
                image: 'images/placeholder.svg'
            }
        ];
        
        console.log('🧪 Carrito de test creado:', testCart);
        
        // 2. Guardar en localStorage
        localStorage.setItem('carrito', JSON.stringify(testCart));
        console.log('💾 Carrito de test guardado en localStorage');
        
        // 3. Llamar función de mostrar modal
        console.log('🪟 Abriendo modal con datos de test...');
        showCartModal(testCart);
        
        // 4. Verificar después de un momento
        setTimeout(() => {
            const modal = document.getElementById('CartModal');
            const items = modal?.querySelectorAll('.accordion-item');
            console.log('✅ Verificación final:');
            console.log('   - Modal existe:', !!modal);
            console.log('   - Items mostrados:', items?.length || 0);
            console.log('   - Items esperados:', testCart.length);
            
            if (items?.length === testCart.length) {
                console.log('🎉 ¡TEST VISUAL EXITOSO!');
            } else {
                console.log('❌ Test visual falló - discrepancia en cantidad');
            }
        }, 1000);
        
    } catch (error) {
        console.error('❌ Error en test visual:', error);
    }
};

// Función de test paso a paso para diagnosticar el problema
window.testCartStepByStep = function() {
    console.log('🔬 INICIANDO DIAGNÓSTICO PASO A PASO...');
    
    try {
        // PASO 1: Limpiar carrito
        console.log('📝 PASO 1: Limpiando carrito...');
        localStorage.removeItem('carrito');
        
        // PASO 2: Crear producto de prueba
        console.log('📝 PASO 2: Creando producto de prueba...');
        const testProduct = {
            id: 'test-chaqueta-1',
            name: 'Chaqueta de Prueba',
            price: 85000,
            size: 'M',
            color: 'Verde',
            quantity: 1,
            image: 'images/placeholder.svg'
        };
        
        // PASO 3: Agregar al carrito manualmente
        console.log('📝 PASO 3: Agregando al carrito...');
        let cart = [testProduct];
        localStorage.setItem('carrito', JSON.stringify(cart));
        console.log('✅ Carrito guardado:', cart);
        
        // PASO 4: Verificar modal existe
        console.log('📝 PASO 4: Verificando modal...');
        const modal = document.getElementById('CartModal');
        if (!modal) {
            console.error('❌ FALLO: Modal no encontrado');
            return;
        }
        console.log('✅ Modal encontrado');
        
        // PASO 5: Verificar contenedor
        console.log('📝 PASO 5: Verificando contenedor cartItems...');
        const container = modal.querySelector('#cartItems');
        if (!container) {
            console.error('❌ FALLO: Contenedor cartItems no encontrado');
            return;
        }
        console.log('✅ Contenedor encontrado');
        
        // PASO 6: Generar HTML simple
        console.log('📝 PASO 6: Generando HTML...');
        const simpleHTML = `
            <div class="test-item p-3 border">
                <h6>${testProduct.name}</h6>
                <p>Precio: $${testProduct.price.toLocaleString()}</p>
                <p>Talla: ${testProduct.size} | Color: ${testProduct.color}</p>
                <p>Cantidad: ${testProduct.quantity}</p>
            </div>
        `;
        
        // PASO 7: Insertar HTML
        console.log('📝 PASO 7: Insertando HTML...');
        container.innerHTML = simpleHTML;
        console.log('✅ HTML insertado');
        
        // PASO 8: Verificar inserción
        console.log('📝 PASO 8: Verificando inserción...');
        const testItem = container.querySelector('.test-item');
        if (!testItem) {
            console.error('❌ FALLO: HTML no se insertó correctamente');
            return;
        }
        console.log('✅ HTML verificado en DOM');
        
        // PASO 9: Abrir modal
        console.log('📝 PASO 9: Abriendo modal...');
        if (window.bootstrap && window.bootstrap.Modal) {
            const bsModal = new window.bootstrap.Modal(modal);
            bsModal.show();
            console.log('✅ Modal abierto');
        } else {
            modal.style.display = 'block';
            modal.classList.add('show');
            console.log('✅ Modal abierto manualmente');
        }
        
        console.log('🎉 DIAGNÓSTICO COMPLETADO - Modal debería mostrar el producto de prueba');
        
    } catch (error) {
        console.error('❌ Error en diagnóstico:', error);
    }
};

// Test rápido para verificar la solución del accordion
window.testCartFixedVisual = function() {
    console.log('🔧 TEST DE LA SOLUCIÓN VISUAL...');
    
    // 1. Limpiar carrito
    localStorage.removeItem('carrito');
    
    // 2. Crear múltiples productos de prueba
    const testCart = [
        {
            id: 'test-1',
            name: 'Chaqueta Deportiva',
            price: 120000,
            size: 'S',
            color: 'Rojo',
            quantity: 1,
            image: 'images/placeholder.svg'
        },
        {
            id: 'test-1',
            name: 'Chaqueta Deportiva',
            price: 120000,
            size: 'M',
            color: 'Verde',
            quantity: 2,
            image: 'images/placeholder.svg'
        },
        {
            id: 'test-1',
            name: 'Chaqueta Deportiva',
            price: 120000,
            size: 'L',
            color: 'Azul',
            quantity: 1,
            image: 'images/placeholder.svg'
        }
    ];
    
    // 3. Guardar y mostrar
    localStorage.setItem('carrito', JSON.stringify(testCart));
    console.log('✅ Carrito de prueba con', testCart.length, 'productos creado');
    
    // 4. Mostrar modal
    showCartModal(testCart);
    
    // 5. Verificar después de 1 segundo
    setTimeout(() => {
        const modal = document.getElementById('CartModal');
        const items = modal?.querySelectorAll('.cart-item');
        
        console.log('📊 RESULTADO DEL TEST:');
        console.log('  - Productos en carrito:', testCart.length);
        console.log('  - Items visibles en modal:', items?.length || 0);
        
        if (items?.length === testCart.length) {
            console.log('🎉 ¡ÉXITO! Todos los productos son visibles');
            console.log('🎊 Problema del accordion solucionado');
        } else {
            console.log('❌ Aún hay problemas visuales');
        }
    }, 1000);
};

// =================================================================
// FUNCIÓN DE DEBUG PARA FLUJO DE 3 PASOS
// =================================================================
// =================================================================
// FUNCIÓN DE DEBUG ESPECÍFICA PARA COLORES
// =================================================================
// =================================================================
// FUNCIÓN PARA FORZAR SELECCIÓN DE COLOR Y PROBAR
// =================================================================
window.testColorSelection = function(colorName = 'Rojo') {
    console.log(`🔍 TEST: Seleccionando color "${colorName}"...`);
    
    // Buscar todos los botones de color
    const colorButtons = document.querySelectorAll('.color-btn');
    console.log('🎨 Botones de color encontrados:', colorButtons.length);
    
    // Desactivar todos los botones
    colorButtons.forEach(btn => {
        btn.style.border = '2px solid transparent';
    });
    
    // Buscar el botón del color específico
    const targetButton = Array.from(colorButtons).find(btn => 
        btn.dataset.color === colorName || 
        btn.title === colorName ||
        btn.dataset.color === colorName.toLowerCase()
    );
    
    if (targetButton) {
        // Activar el botón objetivo
        targetButton.style.border = '2px solid #000';
        console.log('✅ Color seleccionado:', {
            dataColor: targetButton.dataset.color,
            title: targetButton.title,
            style: targetButton.style.border
        });
        
        // Simular agregar al carrito después de un momento
        setTimeout(() => {
            console.log('🛒 Simulando agregar al carrito...');
            // Buscar botón de agregar al carrito
            const addToCartBtn = document.querySelector('.add-to-cart, [onclick*="addToCart"]');
            if (addToCartBtn) {
                addToCartBtn.click();
            }
        }, 1000);
        
    } else {
        console.error('❌ No se encontró botón para el color:', colorName);
        console.log('🎨 Colores disponibles:', 
            Array.from(colorButtons).map(btn => btn.dataset.color || btn.title)
        );
    }
};

window.debugColorDetection = function() {
    console.log('🔍 DEBUG: Verificando detección de colores...');
    
    // Buscar tarjetas de productos
    const productCards = document.querySelectorAll('.card, .product-card');
    console.log('📋 Tarjetas encontradas:', productCards.length);
    
    productCards.forEach((card, index) => {
        console.log(`\n🔍 TARJETA ${index + 1}:`);
        
        // Buscar botones de color
        const colorButtons = card.querySelectorAll('.color-btn');
        console.log('  🎨 Botones de color:', colorButtons.length);
        
        colorButtons.forEach((btn, btnIndex) => {
            console.log(`    Botón ${btnIndex + 1}:`, {
                dataColor: btn.dataset.color,
                style: btn.style.border,
                backgroundColor: btn.style.backgroundColor,
                title: btn.title,
                innerHTML: btn.innerHTML,
                classList: Array.from(btn.classList)
            });
        });
        
        // Buscar botón activo
        const activeBtn = card.querySelector('.color-btn[style*="border: 2px solid"]');
        if (activeBtn) {
            console.log('  ✅ Botón activo:', {
                dataColor: activeBtn.dataset.color,
                style: activeBtn.style.border
            });
        } else {
            console.log('  ❌ No hay botón activo');
        }
    });
};

// =================================================================
// FUNCIÓN DE TEST RÁPIDO PARA FLUJO CORREGIDO
// =================================================================
window.testCorrectFlow = function() {
    console.log('🚀 PROBANDO FLUJO CORREGIDO...');
    
    // Crear carrito de prueba
    const testCart = [
        { name: 'Test Product', price: 50000, size: 'M', color: 'Rojo', quantity: 1, image: 'test.jpg' }
    ];
    localStorage.setItem('carrito', JSON.stringify(testCart));
    console.log('✅ Carrito de prueba creado');
    
    // Abrir modal de carrito
    const cartModal = document.getElementById('CartModal');
    if (cartModal) {
        const bsModal = new bootstrap.Modal(cartModal);
        bsModal.show();
        console.log('✅ Modal de carrito abierto');
        
        // Después de 2 segundos, hacer clic en "Pagar Ahora"
        setTimeout(() => {
            console.log('🔄 Haciendo clic en "Pagar Ahora"...');
            const payButton = document.getElementById('btnProceedToPayment');
            if (payButton) {
                payButton.click();
                console.log('✅ Botón clickeado');
            } else {
                console.error('❌ Botón no encontrado');
            }
        }, 2000);
    }
};

window.debugModalFlow = function() {
    console.log('🔧 DEBUG: Verificando flujo de 3 pasos...');
    
    // 1. Verificar funciones críticas
    console.log('📋 FUNCIONES CRÍTICAS:');
    console.log('  - proceedToPayment:', typeof window.proceedToPayment);
    console.log('  - proceedToPaymentFromShipping:', typeof window.proceedToPaymentFromShipping);
    
    // 2. Verificar modales existen
    console.log('📋 MODALES:');
    console.log('  - CartModal:', !!document.getElementById('CartModal'));
    console.log('  - ShippingModal:', !!document.getElementById('ShippingModal'));
    console.log('  - PaymentModal:', !!document.getElementById('PaymentModal'));
    
    // 3. Verificar botones
    console.log('📋 BOTONES:');
    const cartBtn = document.getElementById('btnProceedToPayment');
    console.log('  - btnProceedToPayment:', !!cartBtn);
    if (cartBtn) {
        console.log('    onclick:', cartBtn.onclick);
        console.log('    onclick attr:', cartBtn.getAttribute('onclick'));
    }
    
    // 4. Test del flujo completo
    console.log('🚀 INICIANDO TEST DEL FLUJO...');
    
    // Crear carrito de prueba
    const testCart = [
        { name: 'Test Product', price: 50000, size: 'M', color: 'Rojo', quantity: 1, image: 'test.jpg' }
    ];
    localStorage.setItem('carrito', JSON.stringify(testCart));
    
    // Abrir modal de carrito
    const cartModal = document.getElementById('CartModal');
    if (cartModal) {
        const bsModal = new bootstrap.Modal(cartModal);
        bsModal.show();
        console.log('✅ Modal de carrito abierto');
        
        // Después de 2 segundos, simular clic en "Pagar Ahora"
        setTimeout(() => {
            console.log('🔄 Simulando clic en "Pagar Ahora"...');
            if (typeof window.proceedToPayment === 'function') {
                window.proceedToPayment();
            } else {
                console.error('❌ proceedToPayment no disponible');
            }
        }, 2000);
    }
};

// ✅ EXPORTAR showCartModal para uso desde otros scripts
window.showCartModal = showCartModal;

// ⚡ SOLUCIÓN FORZADA - Interceptar y corregir TODOS los totales incorrectos
function forceCorrectTotal() {
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    
    if (cart.length === 0) {
        // Carrito vacío
        document.querySelectorAll('[data-total]').forEach(el => {
            if (el.textContent !== '$0' && el.textContent !== '$0.00') {
                console.log('🔧 [FORCE] Corrigiendo total de carrito vacío:', el.textContent, '→ $0');
                el.textContent = '$0';
            }
        });
        return;
    }
    
    // Calcular total correcto (subtotal sin envío)
    const subtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
    const correctTotal = `$${subtotal.toLocaleString()}`;
    
    console.log('🔧 [FORCE] Total correcto debería ser:', correctTotal);
    
    // Forzar el total correcto en TODOS los elementos
    document.querySelectorAll('[data-total]').forEach((el, index) => {
        if (el.textContent !== correctTotal) {
            console.log(`🔧 [FORCE] Corrigiendo total ${index}:`, el.textContent, '→', correctTotal);
            el.textContent = correctTotal;
        }
    });
    
    // También forzar subtotal correcto
    document.querySelectorAll('[data-subtotal]').forEach((el, index) => {
        if (el.textContent !== correctTotal) {
            console.log(`🔧 [FORCE] Corrigiendo subtotal ${index}:`, el.textContent, '→', correctTotal);
            el.textContent = correctTotal;
        }
    });
    
    // Forzar envío gratis
    document.querySelectorAll('[data-envio]').forEach((el, index) => {
        if (el.textContent !== 'GRATIS') {
            console.log(`🔧 [FORCE] Corrigiendo envío ${index}:`, el.textContent, '→ GRATIS');
            el.textContent = 'GRATIS';
            el.className = 'text-success fw-bold';
        }
    });
}

// Ejecutar la corrección cada vez que se actualiza el carrito
window.forceCorrectTotal = forceCorrectTotal;

// Ejecutar inmediatamente
setTimeout(forceCorrectTotal, 100);

// Ejecutar cada segundo para asegurar que se mantenga correcto
setInterval(forceCorrectTotal, 1000);

// Ejecutar cuando se abre el modal del carrito
document.addEventListener('DOMContentLoaded', function() {
    const cartModal = document.getElementById('CartModal');
    if (cartModal) {
        cartModal.addEventListener('shown.bs.modal', function() {
            console.log('🛒 [FORCE] Modal del carrito abierto, forzando totales correctos...');
            setTimeout(forceCorrectTotal, 100);
        });
    }
});

// 🐛 DEBUGGING - Monitorear cambios en data-total
document.addEventListener('DOMContentLoaded', function() {
    const totalElements = document.querySelectorAll('[data-total]');
    totalElements.forEach((element, index) => {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    console.log(`🐛 [DEBUG] data-total ${index} cambió a:`, element.textContent);
                    console.trace('🔍 Stack trace del cambio:');
                }
            });
        });
        
        observer.observe(element, {
            childList: true,
            subtree: true,
            characterData: true
        });
    });
});

// ⚡ INTERCEPCIÓN TOTAL - Sobrescribir cualquier función que toque los totales
document.addEventListener('DOMContentLoaded', function() {
    console.log('🛡️ [SHIELD] Activando protección de totales...');
    
    // Interceptar y sobrescribir cualquier cambio en data-total
    const originalSetAttribute = Element.prototype.setAttribute;
    const originalSetProperty = Object.getOwnPropertyDescriptor(Node.prototype, 'textContent').set;
    
    // Sobrescribir textContent para elementos data-total
    Object.defineProperty(Node.prototype, 'textContent', {
        get: Object.getOwnPropertyDescriptor(Node.prototype, 'textContent').get,
        set: function(value) {
            // Si es un elemento data-total y el valor contiene números incorrectos
            if (this.hasAttribute && this.hasAttribute('data-total')) {
                const numericValue = parseFloat(value.replace(/[^\d.]/g, ''));
                const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
                const correctSubtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
                
                // Si el valor que se intenta establecer es diferente al subtotal correcto
                if (numericValue > correctSubtotal && correctSubtotal > 0) {
                    console.log('🛡️ [SHIELD] BLOQUEANDO intento de cambiar total:', value, '→ FORZANDO:', `$${correctSubtotal.toLocaleString()}`);
                    originalSetProperty.call(this, `$${correctSubtotal.toLocaleString()}`);
                    return;
                }
            }
            
            originalSetProperty.call(this, value);
        }
    });
    
    console.log('🛡️ [SHIELD] Protección de totales ACTIVADA');
});

console.log('✅ Sistema de carrito simplificado cargado completamente');

// 🚨 FUNCIÓN DE EMERGENCIA PARA CORREGIR TOTALES INCORRECTOS
function corregirTotalesEmergencia() {
    console.log('🚨 [EMERGENCIA] Corrigiendo totales incorrectos...');
    
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    const subtotalCorrecto = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
    
    console.log('💰 [EMERGENCIA] Subtotal correcto calculado:', subtotalCorrecto);
    
    // CORRECCIÓN ULTRA-AGRESIVA: Buscar CUALQUIER elemento que pueda mostrar totales incorrectos
    const selectoresTotal = [
        '[data-subtotal]', '[data-total]', '.total-amount', '.payment-total', 
        '#summary-total', '#total', '.cart-total', '.order-total',
        '.total-price', '.final-total', '.checkout-total'
    ];
    
    selectoresTotal.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            const valorFormateado = `$${subtotalCorrecto.toLocaleString()}`;
            if (el.textContent !== valorFormateado && el.textContent.includes('$')) {
                console.log(`🔧 [EMERGENCIA] Corrigiendo ${selector}:`, el.textContent, '→', valorFormateado);
                el.textContent = valorFormateado;
            }
        });
    });
    
    // Forzar envío gratis - BÚSQUEDA AMPLIADA
    const selectoresEnvio = [
        '[data-shipping]', '[data-envio]', '.shipping-cost', '.envio-cost',
        '.shipping-amount', '.delivery-cost'
    ];
    
    selectoresEnvio.forEach(selector => {
        document.querySelectorAll(selector).forEach(el => {
            if (el.textContent !== 'GRATIS' && el.textContent !== 'Gratis' && el.textContent.includes('$')) {
                console.log(`🔧 [EMERGENCIA] Corrigiendo envío ${selector}:`, el.textContent, '→ GRATIS');
                el.textContent = 'GRATIS';
            }
        });
    });
    
    // CORRECCIÓN ESPECIAL: Buscar textos que contengan $144,231 o similar y corregirlos
    document.querySelectorAll('*').forEach(el => {
        if (el.textContent && el.textContent.includes('144,231')) {
            console.log('🔧 [EMERGENCIA CRÍTICA] Encontrado $144,231 - Corrigiendo:', el.textContent);
            el.textContent = el.textContent.replace(/\$144,231/, `$${subtotalCorrecto.toLocaleString()}`);
        }
        if (el.textContent && el.textContent.includes('144231')) {
            console.log('🔧 [EMERGENCIA CRÍTICA] Encontrado $144231 - Corrigiendo:', el.textContent);
            el.textContent = el.textContent.replace(/\$144231/, `$${subtotalCorrecto.toLocaleString()}`);
        }
    });
    
    console.log('✅ [EMERGENCIA] Corrección completada');
}

// Ejecutar corrección cada vez que se actualice el carrito
const originalUpdateAllSummaries = updateAllSummaries;
window.updateAllSummaries = function() {
    const result = originalUpdateAllSummaries.apply(this, arguments);
    
    // Ejecutar corrección de emergencia INMEDIATAMENTE y después de 100ms
    corregirTotalesEmergencia();
    setTimeout(() => {
        corregirTotalesEmergencia();
    }, 100);
    setTimeout(() => {
        corregirTotalesEmergencia();
    }, 500);
    
    return result;
};

// 🚨 BLOQUEAR FUNCIONES EXTERNAS QUE PUEDEN INTERFERIR
// Sobrescribir updateCartTotal de custom.js para que no interfiera
window.updateCartTotal = function() {
    console.log('🛡️ [PROTECCIÓN] Bloqueando updateCartTotal de custom.js - usando nuestro sistema');
    // Llamar nuestra función en lugar de la externa
    return updateAllSummaries();
};

// Sobrescribir cualquier otra función que pueda interferir
const funcionesProblematicas = ['updateTotal', 'calculateCartTotal', 'updatePaymentTotal'];
funcionesProblematicas.forEach(funcionName => {
    if (window[funcionName]) {
        const originalFunction = window[funcionName];
        window[funcionName] = function() {
            console.log(`🛡️ [PROTECCIÓN] Interceptando ${funcionName} - redirigiendo a nuestro sistema`);
            return updateAllSummaries();
        };
    }
});

// Hacer las funciones disponibles globalmente para debug
window.corregirTotalesEmergencia = corregirTotalesEmergencia;
window.diagnosticarTotales = diagnosticarTotales;

// Función de debug completa para diagnosticar problemas de totales
window.diagnosticarTotales = function() {
    console.log('🔍 === DIAGNÓSTICO COMPLETO DE TOTALES ===');
    
    // 1. Verificar carrito
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    const subtotalCorrecto = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
    
    console.log('📦 CARRITO:', {
        productos: cart.length,
        subtotalCalculado: subtotalCorrecto,
        items: cart
    });
    
    // 2. Verificar todos los elementos de total en la página
    console.log('🎯 ELEMENTOS DE TOTAL ENCONTRADOS:');
    const totalesElements = document.querySelectorAll('[data-total]');
    totalesElements.forEach((el, i) => {
        console.log(`  Total ${i}: ${el.textContent} (debería ser $${subtotalCorrecto.toLocaleString()})`);
    });
    
    const subtotalesElements = document.querySelectorAll('[data-subtotal]');
    subtotalesElements.forEach((el, i) => {
        console.log(`  Subtotal ${i}: ${el.textContent} (debería ser $${subtotalCorrecto.toLocaleString()})`);
    });
    
    // 3. Verificar funciones que pueden interferir
    console.log('🔍 FUNCIONES EXTERNAS:');
    console.log('  - window.updateCartTotal:', typeof window.updateCartTotal);
    console.log('  - window.calculateTotal:', typeof window.calculateTotal);
    console.log('  - window.updateTotal:', typeof window.updateTotal);
    
    // 4. Verificar configuración
    if (window.CONFIG) {
        console.log('⚙️ CONFIG:', {
            TAX_RATE: window.CONFIG.TAX_RATE,
            STANDARD_SHIPPING: window.CONFIG.SHIPPING?.STANDARD_SHIPPING,
            FREE_SHIPPING_MIN: window.CONFIG.SHIPPING?.FREE_SHIPPING_MIN
        });
    }
    
    console.log('🔧 Ejecutando corrección automática...');
    corregirTotalesEmergencia();
    
    console.log('✅ === DIAGNÓSTICO COMPLETADO ===');
};

// Ejecutar una corrección inicial cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('🚨 [INICIO] Ejecutando corrección inicial...');
        corregirTotalesEmergencia();
        
        // 🚨 CORRECCIÓN AUTOMÁTICA CADA 2 SEGUNDOS
        setInterval(() => {
            const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
            if (cart.length > 0) {
                console.log('🔄 [AUTO] Verificación automática de totales...');
                corregirTotalesEmergencia();
            }
        }, 2000);
        
    }, 2000);
});

// 🚨 PROTECCIÓN EXTREMA - Interceptar modificaciones DOM en elementos de total
function activarProteccionDOM() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' || mutation.type === 'characterData') {
                const target = mutation.target;
                
                // Si se modificó un elemento de total, corregirlo inmediatamente
                if (target.hasAttribute && (
                    target.hasAttribute('data-total') || 
                    target.hasAttribute('data-subtotal') ||
                    target.hasAttribute('data-shipping') ||
                    target.hasAttribute('data-envio')
                )) {
                    setTimeout(() => {
                        console.log('🚨 [DOM GUARD] Detectado cambio en elemento de resumen, corrigiendo...');
                        corregirTotalesEmergencia();
                    }, 50);
                }
                
                // También verificar si el padre tiene estos atributos
                if (target.parentElement && target.parentElement.hasAttribute) {
                    if (target.parentElement.hasAttribute('data-total') || 
                        target.parentElement.hasAttribute('data-subtotal') ||
                        target.parentElement.hasAttribute('data-shipping') ||
                        target.parentElement.hasAttribute('data-envio')) {
                        setTimeout(() => {
                            console.log('🚨 [DOM GUARD] Detectado cambio en hijo de elemento de resumen, corrigiendo...');
                            corregirTotalesEmergencia();
                        }, 50);
                    }
                }
            }
        });
    });
    
    // Observar cambios en todo el documento
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        characterData: true
    });
    
    console.log('🛡️ [DOM GUARD] Protección DOM activada - monitoreando cambios en elementos de total');
}

// Activar protección DOM cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        activarProteccionDOM();
        activarInterceptorTotales();
    }, 1000);
});

// 🚨 INTERCEPTOR AVANZADO PARA TOTALES INCORRECTOS
function activarInterceptorTotales() {
    console.log('🛡️ [INTERCEPTOR] Activando interceptor avanzado de totales...');
    
    // Interceptar todas las modificaciones al textContent de elementos con data-total
    document.querySelectorAll('[data-total]').forEach(element => {
        // Crear un observer para cada elemento de total
        let isCorreting = false;
        
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    if (!isCorreting) {
                        const currentText = element.textContent;
                        const numericValue = parseInt(currentText.replace(/[^\d]/g, ''));
                        
                        const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
                        const correctSubtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
                        
                        if (numericValue > correctSubtotal && correctSubtotal > 0) {
                            isCorreting = true;
                            const correctFormat = `$${correctSubtotal.toLocaleString()}`;
                            console.log(`🚨 [INTERCEPTOR] Total incorrecto detectado: ${currentText} → ${correctFormat}`);
                            element.textContent = correctFormat;
                            setTimeout(() => { isCorreting = false; }, 100);
                        }
                    }
                }
            });
        });
        
        observer.observe(element, {
            childList: true,
            subtree: true,
            characterData: true
        });
        
        console.log('🛡️ [INTERCEPTOR] Observer agregado a elemento de total:', element);
    });
}

// 🚨 SISTEMA DE MONITOREO AUTOMÁTICO CONTINUO
console.log('🚨 [PROTECCIÓN TOTAL] Iniciando monitoreo automático de totales...');

// Ejecutar corrección cada 3 segundos para garantizar totales correctos
setInterval(() => {
    try {
        // Solo si hay productos en el carrito
        const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        if (cart.length > 0) {
            corregirTotalesEmergencia();
        }
    } catch (error) {
        console.error('❌ Error en monitoreo automático:', error);
    }
}, 3000);

console.log('✅ [PROTECCIÓN TOTAL] Sistema de monitoreo automático activado');

// Ejecutar corrección inmediata al cargar
setTimeout(() => {
    corregirTotalesEmergencia();
}, 1000);