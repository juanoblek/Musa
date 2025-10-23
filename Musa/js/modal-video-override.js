/**
 * MODAL VIDEO FIX - OVERRIDE GLOBAL
 * Intercepta todas las modificaciones al carousel del modal para soportar videos
 */

console.log('🎬 Cargando fix global para modal de video...');

// Función para detectar si un archivo es video
function isVideoFile(url) {
    return /\.(mp4|mov|avi|webm)$/i.test(url);
}

// Función para crear elemento multimedia apropiado
function createMediaElement(url, alt = '', additionalClasses = '', additionalStyles = '') {
    const isVideo = isVideoFile(url);
    console.log(`📹 Creando elemento: ${url} - Es video: ${isVideo}`);
    
    if (isVideo) {
        return `<video src="${url}" 
                       class="d-block w-100 ${additionalClasses}" 
                       alt="${alt}"
                       style="max-height: 400px; object-fit: contain; background: #f8f9fa; cursor: pointer; ${additionalStyles}"
                       autoplay muted loop playsinline
                       onerror="console.error('Error cargando video:', '${url}');">
                       Tu navegador no soporta la reproducción de video.
               </video>`;
    } else {
        return `<img src="${url}" 
                     class="d-block w-100 ${additionalClasses}" 
                     alt="${alt}"
                     style="max-height: 400px; object-fit: contain; background: #f8f9fa; ${additionalStyles}"
                     onerror="this.src='images/placeholder.svg';">`;
    }
}

// Override de la función de población del modal
function overrideModalFunctions() {
    console.log('🔧 Aplicando overrides globales para modal de video...');
    
    // Override populateProductModal si existe
    if (window.populateProductModal) {
        const originalFunction = window.populateProductModal;
        window.populateProductModal = function(data) {
            console.log('🎬 Override: populateProductModal llamada con soporte de video');
            
            // Llamar función original
            try {
                originalFunction.call(this, data);
            } catch (error) {
                console.error('Error en función original:', error);
                
                // Fallback manual con soporte de video
                const carouselInner = document.getElementById('productViewCarouselInner');
                if (carouselInner && data.image) {
                    const mediaElement = createMediaElement(data.image, data.title || 'Producto');
                    carouselInner.innerHTML = `
                        <div class="carousel-item active">
                            ${mediaElement}
                        </div>
                    `;
                }
            }
        };
    }
    
    // Interceptar todas las modificaciones directas al innerHTML del carousel
    const carouselInner = document.getElementById('productViewCarouselInner');
    if (carouselInner) {
        // Crear una función observadora
        const originalSetInnerHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML').set;
        
        Object.defineProperty(carouselInner, 'innerHTML', {
            set: function(value) {
                console.log('🎬 Interceptando modificación de innerHTML del carousel');
                
                // Si el valor contiene una imagen que es video, convertirla
                if (typeof value === 'string' && value.includes('<img')) {
                    const imgRegex = /<img[^>]+src="([^"]+)"[^>]*>/g;
                    let match;
                    
                    while ((match = imgRegex.exec(value)) !== null) {
                        const imgSrc = match[1];
                        if (isVideoFile(imgSrc)) {
                            console.log(`🎬 Convirtiendo img a video: ${imgSrc}`);
                            
                            // Extraer atributos de la imagen
                            const altMatch = value.match(/alt="([^"]*)"/);
                            const alt = altMatch ? altMatch[1] : 'Video';
                            
                            // Crear elemento video
                            const videoElement = createMediaElement(imgSrc, alt);
                            
                            // Reemplazar la img con video
                            value = value.replace(match[0], videoElement);
                        }
                    }
                }
                
                // Aplicar el valor procesado
                originalSetInnerHTML.call(this, value);
            },
            get: function() {
                return originalSetInnerHTML.call(this);
            },
            configurable: true
        });
        
        console.log('✅ Override de innerHTML aplicado al carousel');
    }
}

// Aplicar overrides cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', overrideModalFunctions);
} else {
    overrideModalFunctions();
}

// También aplicar después de un pequeño delay para asegurar que todo esté cargado
setTimeout(overrideModalFunctions, 1000);

console.log('✅ Fix global para modal de video cargado');