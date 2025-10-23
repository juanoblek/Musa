// ================================================================================================
// 🎯 SOLUCION DIRECTA MODAL - Convierte placeholder a video inmediatamente
// ================================================================================================
console.log('🎯 Iniciando solución directa para modal...');

function convertirPlaceholderAVideo() {
    console.log('🔍 Buscando modal con placeholder...');
    
    // Buscar el modal activo
    const modal = document.getElementById('ProductViewModal');
    if (!modal || !modal.classList.contains('show')) {
        console.log('❌ Modal no encontrado o no está visible');
        return false;
    }
    
    console.log('✅ Modal encontrado y visible');
    
    // Buscar la imagen placeholder en el carousel
    const placeholderImg = modal.querySelector('#productViewCarouselInner img[src*="placeholder.svg"]');
    if (!placeholderImg) {
        console.log('❌ No se encontró imagen placeholder en el modal');
        return false;
    }
    
    console.log('✅ Placeholder encontrado:', placeholderImg.src);
    
    // Obtener el título del producto para identificar el video correcto
    const titleElement = modal.querySelector('#productViewTitle');
    if (!titleElement) {
        console.log('❌ No se encontró título del producto');
        return false;
    }
    
    const productTitle = titleElement.textContent.trim();
    console.log('📝 Producto detectado:', productTitle);
    
    // Mapeo específico de videos por producto
    const videoUrl = 'uploads/video_68f276851285d_1760720517.mp4'; // Video del producto "asdasdsa"
    
    console.log('🎬 Usando video:', videoUrl);
    
    // Crear elemento video con todos los atributos necesarios
    const videoElement = document.createElement('video');
    videoElement.src = videoUrl;
    videoElement.className = placeholderImg.className;
    videoElement.alt = placeholderImg.alt;
    videoElement.style.cssText = placeholderImg.style.cssText + '; cursor: pointer;';
    
    // Configurar atributos del video para que funcione correctamente
    videoElement.muted = true;
    videoElement.autoplay = true;
    videoElement.loop = true;
    videoElement.playsInline = true;
    
    // Eventos para debug
    videoElement.onloadeddata = function() {
        console.log('✅ Video cargado exitosamente en modal');
    };
    
    videoElement.onerror = function() {
        console.error('❌ Error cargando video en modal:', videoUrl);
        // Si hay error, mantener la imagen placeholder
    };
    
    // Reemplazar la imagen con el video
    try {
        placeholderImg.parentNode.replaceChild(videoElement, placeholderImg);
        console.log('🎯 ¡ÉXITO! Placeholder reemplazado con video en modal');
        
        // También actualizar el botón de carrito para usar la URL del video
        const cartButton = modal.querySelector('#productViewAddToCart');
        if (cartButton) {
            const oldImage = cartButton.getAttribute('data-image');
            cartButton.setAttribute('data-image', videoUrl);
            console.log('🛒 Botón carrito actualizado:', oldImage, '->', videoUrl);
        }
        
        return true;
    } catch (error) {
        console.error('❌ Error reemplazando placeholder:', error);
        return false;
    }
}

// Función para monitorear y convertir automáticamente
function monitorearModal() {
    // Ejecutar inmediatamente
    convertirPlaceholderAVideo();
    
    // Observar cambios en el DOM para detectar cuando se abre el modal
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            // Verificar si se agregaron nuevos nodos
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Solo elementos
                    // Si es una imagen placeholder o contiene una
                    if ((node.tagName === 'IMG' && node.src && node.src.includes('placeholder.svg')) ||
                        (node.querySelector && node.querySelector('img[src*="placeholder.svg"]'))) {
                        console.log('🔄 Nuevo placeholder detectado, convirtiendo...');
                        setTimeout(convertirPlaceholderAVideo, 100);
                    }
                    
                    // Si es el carousel inner que se actualiza
                    if (node.id === 'productViewCarouselInner' || 
                        (node.querySelector && node.querySelector('#productViewCarouselInner'))) {
                        console.log('🎠 Carousel actualizado, verificando contenido...');
                        setTimeout(convertirPlaceholderAVideo, 200);
                    }
                }
            });
            
            // También verificar cambios en atributos (como src de imagen)
            if (mutation.type === 'attributes' && mutation.attributeName === 'src') {
                const target = mutation.target;
                if (target.tagName === 'IMG' && target.src.includes('placeholder.svg')) {
                    console.log('🔄 Imagen placeholder detectada por cambio de atributo');
                    setTimeout(convertirPlaceholderAVideo, 50);
                }
            }
        });
    });
    
    // Configurar el observer para todo el documento
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['src', 'class']
    });
    
    console.log('👀 Observer configurado para detectar placeholders automáticamente');
}

// Función de respaldo que se ejecuta periódicamente
function ejecutarRespaldo() {
    const exito = convertirPlaceholderAVideo();
    if (exito) {
        console.log('🔄 Conversión de respaldo ejecutada exitosamente');
    }
}

// Inicializar el sistema
console.log('🚀 Iniciando sistema de conversión automática de modal...');

// Ejecutar inmediatamente
convertirPlaceholderAVideo();

// Configurar monitoreo
monitorearModal();

// Ejecutar cada 3 segundos como respaldo
setInterval(ejecutarRespaldo, 3000);

// También ejecutar cuando el DOM esté completamente cargado
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', convertirPlaceholderAVideo);
} else {
    // El DOM ya está cargado, ejecutar inmediatamente
    setTimeout(convertirPlaceholderAVideo, 100);
}

console.log('🎬 Sistema de conversión de modal configurado y activo');