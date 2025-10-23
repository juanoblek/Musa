// ================================================================================================
// 🎯 SIMPLE PLACEHOLDER ELIMINATOR - Solución directa para eliminar placeholders
// ================================================================================================
console.log('🎯 Iniciando eliminador simple de placeholders...');

// Función principal para eliminar placeholders del modal
async function eliminarPlaceholdersModal() {
    console.log('🔍 Verificando modal por placeholders...');
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal || !modal.classList.contains('show')) {
        return; // Modal no visible
    }
    
    // Buscar placeholders en el modal
    const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
    if (placeholders.length === 0) {
        console.log('✅ No hay placeholders en el modal');
        return;
    }
    
    console.log(`⚠️ Encontrados ${placeholders.length} placeholders, eliminando...`);
    
    // Obtener el título del producto
    const titleElement = modal.querySelector('#productViewTitle');
    const productTitle = titleElement ? titleElement.textContent.trim() : null;
    
    if (!productTitle) {
        console.log('❌ No se pudo obtener título del producto');
        return;
    }
    
    console.log('📝 Producto:', productTitle);
    
    try {
        // Consultar API para obtener datos reales
        const response = await fetch('api/productos-v2.php');
        const data = await response.json();
        
        if (!data.success || !data.data) {
            console.error('❌ API no disponible');
            return;
        }
        
        // Buscar el producto específico
        let producto = data.data.find(p => 
            p.nombre && p.nombre.toLowerCase().trim() === productTitle.toLowerCase().trim()
        );
        
        // Si no se encuentra por nombre, buscar por cualquier coincidencia
        if (!producto) {
            producto = data.data.find(p => p.main_image && !p.main_image.includes('placeholder'));
            console.log('⚠️ Producto no encontrado por título, usando primer producto con imagen real');
        }
        
        if (!producto || !producto.main_image) {
            console.error('❌ No se encontró producto con imagen/video válido');
            return;
        }
        
        console.log('✅ Producto encontrado:', producto.main_image);
        
        // Determinar si es video o imagen
        const isVideo = /\.(mp4|mov|avi|webm)$/i.test(producto.main_image);
        console.log(`📺 Es video: ${isVideo}`);
        
        // Obtener el carousel inner
        const carouselInner = modal.querySelector('#productViewCarouselInner');
        if (!carouselInner) {
            console.error('❌ Carousel inner no encontrado');
            return;
        }
        
        // Crear nuevo contenido multimedia
        let nuevoContenido;
        if (isVideo) {
            nuevoContenido = `
                <div class="carousel-item active">
                    <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 400px;">
                        <video src="${producto.main_image}" 
                               class="d-block img-fluid" 
                               alt="${productTitle}"
                               style="max-height: 80vh; max-width: 100%; object-fit: contain; background: #f8f9fa; border-radius: 8px; cursor: pointer;"
                               autoplay 
                               muted 
                               loop 
                               playsinline>
                               Tu navegador no soporta la reproducción de video.
                        </video>
                    </div>
                </div>
            `;
        } else {
            nuevoContenido = `
                <div class="carousel-item active">
                    <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 400px;">
                        <img src="${producto.main_image}" 
                             class="d-block img-fluid" 
                             alt="${productTitle}"
                             style="max-height: 80vh; max-width: 100%; object-fit: contain; background: #f8f9fa; border-radius: 8px;">
                    </div>
                </div>
            `;
        }
        
        // Reemplazar contenido del carousel
        carouselInner.innerHTML = nuevoContenido;
        console.log('✅ Placeholder eliminado y reemplazado con contenido real');
        
        // También actualizar el botón de carrito
        const cartButton = modal.querySelector('#productViewAddToCart');
        if (cartButton) {
            cartButton.setAttribute('data-image', producto.main_image);
            console.log('🛒 Botón de carrito actualizado');
        }
        
    } catch (error) {
        console.error('❌ Error eliminando placeholders:', error);
    }
}

// Ejecutar inmediatamente
eliminarPlaceholdersModal();

// Ejecutar cada 2 segundos
setInterval(eliminarPlaceholdersModal, 2000);

// Observer para nuevos placeholders
const observer = new MutationObserver(() => {
    setTimeout(eliminarPlaceholdersModal, 500);
});

observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['src']
});

// Event listener para cuando se abre el modal
document.addEventListener('shown.bs.modal', (e) => {
    if (e.target && e.target.id === 'ProductViewModal') {
        console.log('📦 Modal abierto, eliminando placeholders...');
        setTimeout(eliminarPlaceholdersModal, 1000);
    }
});

// Función manual para debug
window.eliminarPlaceholdersModal = eliminarPlaceholdersModal;

console.log('🎯 Eliminador de placeholders configurado');
console.log('🧪 Función manual: window.eliminarPlaceholdersModal()');