/**
 * FORCE VIDEO RESTORE - Restaurar videos reemplazados por placeholder
 * Este script fuerza la restauración de videos que han sido reemplazados por placeholders
 */

console.log('🔧 Cargando Force Video Restore...');

// Mapeo de productos con videos conocidos
const PRODUCT_VIDEOS = {
    'prod-68f2768519a0f': 'uploads/video_68f276851285d_1760720517.mp4',  // asdasdsa
    'prod-68f11d67de99c': 'uploads/video_68f11d67ce55c_1760632167.mp4',  // Chaqueta Nueva Era
    'prod-68f025498d96c': 'uploads/video_68f0254985a91_1760568649.mp4'   // Chaqueta See
};

// Función para restaurar video en elemento
function restoreVideoInElement(element, videoUrl) {
    const media = element.querySelector('img, video');
    if (!media) return false;
    
    // Si ya es un video con la URL correcta, no hacer nada
    if (media.tagName.toLowerCase() === 'video' && media.src.includes(videoUrl.split('/').pop())) {
        return false;
    }
    
    // Si es imagen placeholder o video incorrecto, reemplazar
    if (media.src.includes('placeholder.svg') || 
        (media.tagName.toLowerCase() === 'img' && videoUrl.includes('.mp4'))) {
        
        console.log(`🎬 Restaurando video: ${videoUrl} en elemento`);
        
        // Crear nuevo elemento video
        const videoElement = document.createElement('video');
        videoElement.src = videoUrl;
        videoElement.className = media.className;
        videoElement.alt = media.alt;
        videoElement.autoplay = true;
        videoElement.muted = true;
        videoElement.loop = true;
        videoElement.playsinline = true;
        videoElement.style.cursor = 'pointer';
        videoElement.style.cssText = media.style.cssText;
        
        // Reemplazar elemento
        media.parentNode.replaceChild(videoElement, media);
        return true;
    }
    
    return false;
}

// Función para restaurar videos en productos
function restoreProductVideos() {
    console.log('🔍 Buscando productos con videos para restaurar...');
    
    let restored = 0;
    
    Object.entries(PRODUCT_VIDEOS).forEach(([productId, videoUrl]) => {
        // Buscar elementos del producto
        const elements = document.querySelectorAll(`[data-id="${productId}"]`);
        
        elements.forEach(element => {
            // Corregir data-image si es necesario
            if (element.dataset.image && element.dataset.image.includes('placeholder')) {
                element.dataset.image = videoUrl;
                console.log(`📝 Corrigiendo data-image para ${productId}`);
            }
            
            // Restaurar video en elemento
            if (restoreVideoInElement(element, videoUrl)) {
                restored++;
            }
        });
    });
    
    return restored;
}

// Función para restaurar video en modal
function restoreModalVideo() {
    const carousel = document.getElementById('productViewCarouselInner');
    if (!carousel) return false;
    
    // Verificar si el modal contiene placeholder
    if (carousel.innerHTML.includes('placeholder.svg')) {
        // Buscar qué producto está abierto
        const modalTitle = document.getElementById('productViewTitle');
        const titleText = modalTitle ? modalTitle.textContent.trim() : '';
        
        // Determinar video correcto basado en el título
        let videoUrl = null;
        if (titleText === 'asdasdsa') {
            videoUrl = PRODUCT_VIDEOS['prod-68f2768519a0f'];
        } else if (titleText.includes('Chaqueta Nueva Era')) {
            videoUrl = PRODUCT_VIDEOS['prod-68f11d67de99c'];
        } else if (titleText.includes('Chaqueta See')) {
            videoUrl = PRODUCT_VIDEOS['prod-68f025498d96c'];
        }
        
        if (videoUrl) {
            console.log(`🎬 Restaurando video en modal: ${videoUrl}`);
            
            carousel.innerHTML = `
                <div class="carousel-item active">
                    <video src="${videoUrl}" 
                           class="d-block w-100" 
                           alt="${titleText}"
                           style="max-height: 400px; object-fit: contain; background: #f8f9fa; cursor: pointer;"
                           autoplay muted loop playsinline>
                           Tu navegador no soporta la reproducción de video.
                    </video>
                </div>
            `;
            
            return true;
        }
    }
    
    return false;
}

// Función principal de restauración
function forceRestoreVideos() {
    console.log('🚀 Iniciando restauración forzada de videos...');
    
    const productsRestored = restoreProductVideos();
    const modalRestored = restoreModalVideo();
    
    console.log(`✅ Restauración completada: ${productsRestored} productos, modal: ${modalRestored ? 'SÍ' : 'NO'}`);
    
    return { products: productsRestored, modal: modalRestored };
}

// Ejecutar restauración inicial
setTimeout(() => {
    forceRestoreVideos();
}, 1000);

// Observar cambios en el DOM para restaurar automáticamente
const observer = new MutationObserver((mutations) => {
    let needsRestore = false;
    
    mutations.forEach((mutation) => {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE) {
                    // Si se agregó un elemento con placeholder
                    if (node.querySelector && node.querySelector('img[src*="placeholder.svg"]')) {
                        needsRestore = true;
                    }
                    
                    // Si se abrió el modal
                    if (node.id === 'productViewCarouselInner' || node.closest('#ProductViewModal')) {
                        needsRestore = true;
                    }
                }
            });
        }
        
        if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
            // Si se cambió src a placeholder
            if (mutation.target.src && mutation.target.src.includes('placeholder.svg')) {
                needsRestore = true;
            }
        }
    });
    
    if (needsRestore) {
        setTimeout(() => {
            forceRestoreVideos();
        }, 100);
    }
});

// Observar todo el documento
observer.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['src', 'data-image']
});

// Función global para uso manual
window.forceRestoreVideos = forceRestoreVideos;

// Restauración cada 5 segundos como backup
setInterval(() => {
    const result = forceRestoreVideos();
    if (result.products > 0 || result.modal) {
        console.log('🔄 Restauración automática ejecutada');
    }
}, 5000);

console.log('✅ Force Video Restore cargado y activo');