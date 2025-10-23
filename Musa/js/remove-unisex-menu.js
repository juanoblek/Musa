/**
 * Script para eliminar completamente el menú UNISEX
 * Se ejecuta después de que se cargue la página para asegurar que se elimine
 */

console.log('🗑️ [REMOVE-UNISEX] Iniciando eliminación del menú UNISEX...');

function removeUnisexMenu() {
    // Buscar y eliminar cualquier elemento con ID 'coleccion-unisex'
    const unisexById = document.getElementById('coleccion-unisex');
    if (unisexById) {
        console.log('🗑️ [REMOVE-UNISEX] Eliminando menú por ID:', unisexById);
        unisexById.closest('li')?.remove() || unisexById.remove();
    }
    
    // Buscar elementos que contengan "UNISEX" en el texto
    const allLinks = document.querySelectorAll('a');
    allLinks.forEach(link => {
        if (link.textContent.trim().toUpperCase().includes('UNISEX')) {
            console.log('🗑️ [REMOVE-UNISEX] Eliminando enlace UNISEX:', link.textContent);
            link.closest('li')?.remove() || link.remove();
        }
    });
    
    // Buscar elementos con texto "UNISEX" en cualquier parte
    const allElements = document.querySelectorAll('*');
    allElements.forEach(element => {
        if (element.children.length === 0 && element.textContent.trim().toUpperCase() === 'UNISEX') {
            console.log('🗑️ [REMOVE-UNISEX] Eliminando elemento con texto UNISEX:', element);
            element.closest('li')?.remove() || element.closest('.nav-item')?.remove() || element.remove();
        }
    });
    
    // Buscar dropdowns que puedan contener "unisex" en atributos
    const dropdowns = document.querySelectorAll('[aria-labelledby*="unisex"], [id*="unisex"]');
    dropdowns.forEach(dropdown => {
        console.log('🗑️ [REMOVE-UNISEX] Eliminando dropdown unisex:', dropdown);
        dropdown.remove();
    });
}

// Ejecutar inmediatamente
removeUnisexMenu();

// Ejecutar después de un breve delay para capturar elementos cargados dinámicamente
setTimeout(removeUnisexMenu, 500);
setTimeout(removeUnisexMenu, 1000);
setTimeout(removeUnisexMenu, 2000);

// Observer para detectar cuando se agreguen nuevos elementos al DOM
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.addedNodes) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Solo elementos
                    // Verificar si el nuevo elemento es o contiene "UNISEX"
                    if (node.textContent && node.textContent.toUpperCase().includes('UNISEX')) {
                        console.log('🗑️ [REMOVE-UNISEX] Nuevo elemento UNISEX detectado, eliminando...', node);
                        setTimeout(() => {
                            removeUnisexMenu();
                        }, 100);
                    }
                }
            });
        }
    });
});

// Observar cambios en el documento
observer.observe(document.body, {
    childList: true,
    subtree: true
});

console.log('✅ [REMOVE-UNISEX] Script de eliminación de UNISEX activado');

// Función global para eliminar manualmente si es necesario
window.forceRemoveUnisex = removeUnisexMenu;