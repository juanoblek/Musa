/**
 * EMERGENCY PRODUCT MODAL FIX
 * Solución de emergencia que usa la función showProductView existente
 */

console.log('🚨 Cargando fix de emergencia para modal de producto...');

// Override del handler de clicks para usar la función existente
function emergencyImageClickHandler(e) {
    // Verificar si el click fue en una imagen de producto
    const target = e.target;
    
    if (!target.matches('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img')) {
        return; // No es una imagen de producto
    }
    
    // Prevenir comportamiento por defecto
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    console.log('🚨 === EMERGENCY CLICK HANDLER ===');
    console.log('📍 Imagen:', target.src);
    
    // Usar la función showProductView existente si está disponible
    if (typeof window.showProductView === 'function') {
        console.log('✅ Usando window.showProductView existente');
        try {
            window.showProductView(target);
            console.log('✅ Modal abierto con función existente');
        } catch (error) {
            console.error('❌ Error con showProductView:', error);
            fallbackModalOpen(target);
        }
    } else {
        console.log('⚠️ showProductView no disponible, usando fallback');
        fallbackModalOpen(target);
    }
}

// Función de fallback para abrir modal
function fallbackModalOpen(imageElement) {
    console.log('🔧 Ejecutando fallback para abrir modal...');
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.error('❌ Modal no encontrado');
        alert('Error: Modal de producto no disponible');
        return;
    }
    
    try {
        // Extraer datos básicos
        const container = imageElement.closest('.product-card, .api-product, .main-product-card, .card');
        const title = container?.querySelector('.card-title, h5, h3, .product-title, .title')?.textContent?.trim() || 'Producto';
        const price = container?.querySelector('.current-price, .price, .text-primary')?.textContent?.trim() || '$0';
        
        // Configurar modal básico
        const modalImage = modal.querySelector('#productViewImage');
        const modalTitle = modal.querySelector('#productViewTitle');
        const modalPrice = modal.querySelector('#productViewPrice, .product-view-price');
        
        if (modalImage) modalImage.src = imageElement.src;
        if (modalTitle) modalTitle.textContent = title;
        if (modalPrice) modalPrice.textContent = price;
        
        // Abrir modal usando diferentes métodos
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            console.log('✅ Modal abierto con Bootstrap');
        } else if (typeof $ !== 'undefined') {
            $(modal).modal('show');
            console.log('✅ Modal abierto con jQuery');
        } else {
            // Manual
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            console.log('✅ Modal abierto manualmente');
        }
        
    } catch (error) {
        console.error('❌ Error en fallback:', error);
        alert('Error al abrir modal: ' + error.message);
    }
}

// Reemplazar el handler global
function setupEmergencyHandler() {
    console.log('🚨 Configurando handler de emergencia...');
    
    // Remover handler anterior
    document.removeEventListener('click', globalImageClickHandler, true);
    
    // Agregar nuevo handler de emergencia
    document.addEventListener('click', emergencyImageClickHandler, true);
    
    console.log('✅ Handler de emergencia configurado');
}

// Test de emergencia
function emergencyTest() {
    console.log('🧪 Test de emergencia...');
    
    const images = document.querySelectorAll('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img');
    
    if (images.length === 0) {
        console.log('⚠️ No hay imágenes para testear');
        return false;
    }
    
    const firstImage = images[0];
    console.log('🎯 Testeando con:', firstImage.src);
    
    // Simular click
    const event = {
        target: firstImage,
        preventDefault: () => {},
        stopPropagation: () => {},
        stopImmediatePropagation: () => {}
    };
    
    emergencyImageClickHandler(event);
    
    return true;
}

// Auto-inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupEmergencyHandler);
} else {
    setupEmergencyHandler();
}

// Exportar funciones
window.emergencyTest = emergencyTest;
window.setupEmergencyHandler = setupEmergencyHandler;
window.emergencyImageClickHandler = emergencyImageClickHandler;

console.log('✅ Emergency Fix cargado');
console.log('🧪 Test de emergencia: window.emergencyTest()');