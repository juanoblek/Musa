/**
 * CUSTOM CLOSE BUTTON FIX - Asegura que el botón personalizado funcione
 * Corrige la funcionalidad de cerrar modal con botón personalizado
 */

console.log('🔧 Configurando funcionalidad del botón de cerrar personalizado...');

function setupCustomCloseButton() {
    const modal = document.getElementById('ProductViewModal');
    if (!modal) return;
    
    const closeButton = modal.querySelector('.custom-close-btn');
    if (!closeButton) return;
    
    console.log('✅ Botón personalizado encontrado, configurando evento...');
    
    // Remover cualquier evento existente
    closeButton.removeEventListener('click', handleCloseClick);
    
    // Añadir evento de click
    closeButton.addEventListener('click', handleCloseClick);
    
    // Asegurar que el botón tenga todos los atributos necesarios
    closeButton.setAttribute('data-bs-dismiss', 'modal');
    closeButton.setAttribute('aria-label', 'Close');
    closeButton.setAttribute('type', 'button');
    
    console.log('🎯 Botón de cerrar configurado correctamente');
}

function handleCloseClick(event) {
    console.log('🔥 Click en botón de cerrar detectado');
    
    // Prevenir comportamiento por defecto
    event.preventDefault();
    event.stopPropagation();
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) return;
    
    // Método 1: Usar Bootstrap Modal API
    try {
        const bootstrapModal = bootstrap.Modal.getInstance(modal);
        if (bootstrapModal) {
            console.log('📤 Cerrando modal usando Bootstrap API');
            bootstrapModal.hide();
            return;
        }
    } catch (error) {
        console.warn('⚠️ Bootstrap Modal API no disponible:', error);
    }
    
    // Método 2: Crear instancia y cerrar
    try {
        console.log('📤 Creando nueva instancia de Bootstrap Modal');
        const newModal = new bootstrap.Modal(modal);
        newModal.hide();
        return;
    } catch (error) {
        console.warn('⚠️ No se pudo crear instancia de Bootstrap Modal:', error);
    }
    
    // Método 3: Manipulación manual del DOM
    console.log('📤 Cerrando modal manualmente');
    modal.classList.remove('show');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modal.removeAttribute('aria-modal');
    
    // Remover backdrop si existe
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
    
    // Restaurar scroll del body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
}

// Ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', setupCustomCloseButton);

// Ejecutar cuando el modal se muestre
const modal = document.getElementById('ProductViewModal');
if (modal) {
    modal.addEventListener('shown.bs.modal', setupCustomCloseButton);
    modal.addEventListener('show.bs.modal', setupCustomCloseButton);
}

// Configurar inmediatamente si el modal ya existe
setupCustomCloseButton();

console.log('🎯 Sistema de botón de cerrar personalizado activado');