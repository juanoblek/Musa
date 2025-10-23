/**
 * MODAL CLEANUP - Limpia el modal al cargar la página
 * Evita que se muestre información residual
 */

console.log('🧹 Cargando limpieza automática del modal...');

function cleanupProductModal() {
    console.log('🧹 Limpiando modal de producto...');
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.log('⚠️ Modal no encontrado para limpiar');
        return;
    }
    
    try {
        // Asegurar que el modal esté cerrado
        modal.style.display = 'none';
        modal.classList.remove('show');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        
        // Limpiar backdrop si existe
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        
        // Limpiar contenido del modal
        const modalImage = modal.querySelector('#productViewImage');
        const modalTitle = modal.querySelector('#productViewTitle');
        const modalPrice = modal.querySelector('#productViewPrice, .product-view-price');
        const modalDescription = modal.querySelector('#productViewDescription, .product-description');
        
        if (modalImage) {
            modalImage.src = '';
            modalImage.alt = '';
        }
        
        if (modalTitle) {
            modalTitle.textContent = 'Vista Completa del Producto';
        }
        
        if (modalPrice) {
            modalPrice.textContent = '';
        }
        
        if (modalDescription) {
            modalDescription.textContent = '';
        }
        
        console.log('✅ Modal limpiado exitosamente');
        
    } catch (error) {
        console.error('❌ Error al limpiar modal:', error);
    }
}

// Limpiar automáticamente cuando se carga la página
function autoCleanup() {
    // Limpiar inmediatamente
    cleanupProductModal();
    
    // Limpiar después de un breve delay para asegurar que todos los scripts se hayan cargado
    setTimeout(() => {
        cleanupProductModal();
    }, 500);
    
    // Limpiar después de 2 segundos por si hay procesos tardíos
    setTimeout(() => {
        cleanupProductModal();
    }, 2000);
}

// Ejecutar limpieza
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoCleanup);
} else {
    autoCleanup();
}

// Exportar función para uso manual
window.cleanupProductModal = cleanupProductModal;

console.log('✅ Modal Cleanup cargado - Función: window.cleanupProductModal()');