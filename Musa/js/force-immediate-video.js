// ================================================================================================
// 🎬 FORCE IMMEDIATE VIDEO - Solución inmediata para modal actual
// ================================================================================================
console.log('🔥 Iniciando Force Immediate Video...');

// Función para convertir imagen a video inmediatamente
function convertPlaceholderToVideo() {
    console.log('🔍 Buscando placeholders en modal...');
    
    // Buscar el modal actual
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.log('❌ Modal no encontrado');
        return;
    }
    
    // Buscar imagen placeholder en el modal
    const placeholderImg = modal.querySelector('img[src*="placeholder.svg"]');
    if (!placeholderImg) {
        console.log('❌ No se encontró placeholder en modal');
        return;
    }
    
    console.log('✅ Placeholder encontrado:', placeholderImg.src);
    
    // Mapeo de productos con videos conocidos
    const PRODUCT_VIDEOS = {
        'asdasdsa': 'uploads/video_68f276851285d_1760720517.mp4',
        'Chaqueta Nueva Era': 'uploads/video_68f2764f21aed_1760720047.mp4',
        'Chaqueta See': 'uploads/video_68f2786e8a477_1760720494.mp4'
    };
    
    // Obtener el título del producto desde el modal
    const titleElement = modal.querySelector('#productViewTitle');
    if (!titleElement) {
        console.log('❌ No se encontró título del producto');
        return;
    }
    
    const productTitle = titleElement.textContent.trim();
    console.log('📝 Producto actual:', productTitle);
    
    // Buscar video para este producto
    const videoUrl = PRODUCT_VIDEOS[productTitle];
    if (!videoUrl) {
        console.log('❌ No hay video configurado para:', productTitle);
        return;
    }
    
    console.log('🎬 Video encontrado:', videoUrl);
    
    // Crear elemento video
    const videoElement = document.createElement('video');
    videoElement.src = videoUrl;
    videoElement.autoplay = false;
    videoElement.muted = true;
    videoElement.loop = true;
    videoElement.style.cssText = placeholderImg.style.cssText + '; cursor: pointer;';
    videoElement.className = placeholderImg.className;
    videoElement.alt = placeholderImg.alt;
    
    // Agregar eventos de error
    videoElement.onerror = function() {
        console.log('❌ Error cargando video:', videoUrl);
        // Mantener la imagen si el video no carga
    };
    
    videoElement.onloadeddata = function() {
        console.log('✅ Video cargado exitosamente:', videoUrl);
    };
    
    // Reemplazar imagen con video
    try {
        placeholderImg.parentNode.replaceChild(videoElement, placeholderImg);
        console.log('🎯 Placeholder reemplazado con video exitosamente');
        
        // También actualizar el botón de carrito para que use el video
        const cartButton = modal.querySelector('#productViewAddToCart');
        if (cartButton && cartButton.dataset.image && cartButton.dataset.image.includes('placeholder.svg')) {
            cartButton.dataset.image = videoUrl;
            console.log('🛒 URL de imagen en botón carrito actualizada');
        }
        
    } catch (error) {
        console.error('❌ Error reemplazando placeholder:', error);
    }
}

// Ejecutar inmediatamente
convertPlaceholderToVideo();

// También escuchar cambios en el DOM
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList') {
            // Verificar si se agregaron nuevos nodos con placeholder
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    const placeholder = node.querySelector ? node.querySelector('img[src*="placeholder.svg"]') : null;
                    if (placeholder || (node.tagName === 'IMG' && node.src && node.src.includes('placeholder.svg'))) {
                        console.log('🔄 Nuevo placeholder detectado, convirtiendo...');
                        setTimeout(convertPlaceholderToVideo, 100);
                    }
                }
            });
        }
    });
});

// Observar cambios en el modal
const modal = document.getElementById('ProductViewModal');
if (modal) {
    observer.observe(modal, {
        childList: true,
        subtree: true
    });
    console.log('👀 Observer configurado para modal');
}

// Ejecutar cada 2 segundos como backup
setInterval(convertPlaceholderToVideo, 2000);

console.log('🎬 Force Immediate Video configurado');