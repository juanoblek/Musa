/**
 * FORCE CLOSE BUTTON POSITION - Script JS para forzar posición
 * Asegura que el botón de cerrar esté siempre en la esquina
 */

console.log('🎯 Forzando posición del botón de cerrar...');

function forceCloseButtonPosition() {
    const modal = document.getElementById('ProductViewModal');
    if (!modal) return;
    
    // Buscar tanto el botón antiguo como el nuevo
    const closeButton = modal.querySelector('.modal-header .btn-close') || modal.querySelector('.modal-header .custom-close-btn');
    if (!closeButton) return;
    
    console.log('🎯 Aplicando posición forzada al botón de cerrar...');
    
    // Aplicar estilos directamente al elemento
    closeButton.style.position = 'absolute';
    closeButton.style.top = '15px';
    closeButton.style.right = '15px';
    closeButton.style.zIndex = '999999';
    closeButton.style.margin = '0';
    closeButton.style.padding = '0';
    closeButton.style.width = '32px';
    closeButton.style.height = '32px';
    closeButton.style.border = 'none';
    closeButton.style.background = 'rgba(0, 0, 0, 0.1)';
    closeButton.style.borderRadius = '6px';
    closeButton.style.backdropFilter = 'blur(10px)';
    closeButton.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.1)';
    closeButton.style.transition = 'all 0.3s ease';
    closeButton.style.display = 'flex';
    closeButton.style.alignItems = 'center';
    closeButton.style.justifyContent = 'center';
    closeButton.style.cursor = 'pointer';
    closeButton.style.fontSize = '18px';
    closeButton.style.fontWeight = 'bold';
    closeButton.style.color = '#333';
    closeButton.style.lineHeight = '1';
    closeButton.style.textAlign = 'center';
    
    // Asegurar que tenga el contenido X si es el botón personalizado
    if (closeButton.classList.contains('custom-close-btn') && closeButton.textContent !== '×') {
        closeButton.textContent = '×';
    }
    
    // Responsive para móvil
    if (window.innerWidth <= 768) {
        closeButton.style.top = '8px';
        closeButton.style.right = '8px';
        closeButton.style.width = '28px';
        closeButton.style.height = '28px';
        closeButton.style.padding = '5px';
    }
    
    console.log('✅ Botón de cerrar reposicionado por JS');
}

// Aplicar cuando el modal se muestre
function setupCloseButtonForce() {
    const modal = document.getElementById('ProductViewModal');
    if (!modal) return;
    
    // Al mostrar el modal
    modal.addEventListener('shown.bs.modal', forceCloseButtonPosition);
    
    // Al cambiar tamaño de ventana
    window.addEventListener('resize', () => {
        if (modal.classList.contains('show')) {
            forceCloseButtonPosition();
        }
    });
    
    // Observer para detectar cambios en el DOM del modal
    const observer = new MutationObserver(() => {
        if (modal.classList.contains('show')) {
            forceCloseButtonPosition();
        }
    });
    
    observer.observe(modal, {
        attributes: true,
        attributeFilter: ['class'],
        childList: true,
        subtree: true
    });
    
    // Forzar posición inicial si el modal ya está visible
    if (modal.classList.contains('show')) {
        forceCloseButtonPosition();
    }
    
    console.log('✅ Sistema de forzado de posición configurado');
}

// Inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupCloseButtonForce);
} else {
    setupCloseButtonForce();
}

// Función global para uso manual
window.forceCloseButtonPosition = forceCloseButtonPosition;

console.log('✅ Force Close Button Position cargado');