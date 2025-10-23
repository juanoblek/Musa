// 🎬 FIX ESPECÍFICO PARA MODAL DE PRODUCTOS CON VIDEOS
// Este script se asegura de que el modal también muestre videos correctamente

(function() {
    'use strict';
    
    console.log('🎬 Iniciando fix específico para modal de videos...');
    
    // Función para convertir imágenes a videos en el modal
    function fixModalImages() {
        const modalCarousel = document.getElementById('productViewCarouselInner');
        if (!modalCarousel) return;
        
        const modalImages = modalCarousel.querySelectorAll('img[src*=".mp4"], img[src*=".mov"], img[src*=".avi"], img[src*=".webm"]');
        
        if (modalImages.length > 0) {
            console.log(`🔄 Convirtiendo ${modalImages.length} imágenes de video en modal...`);
            
            modalImages.forEach((img, index) => {
                try {
                    const videoSrc = img.src;
                    const alt = img.alt || 'Video';
                    const classes = img.className;
                    const styles = img.style.cssText;
                    
                    // Generar video usando el detector inteligente
                    if (typeof generarMediaHTMLSincrono !== 'undefined') {
                        const videoHTML = generarMediaHTMLSincrono(videoSrc, alt, classes, styles);
                        
                        // Crear contenedor temporal
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = videoHTML;
                        const videoElement = tempDiv.firstElementChild;
                        
                        if (videoElement && videoElement.tagName === 'VIDEO') {
                            // Reemplazar imagen con video
                            img.parentNode.replaceChild(videoElement, img);
                            console.log(`✅ Modal: Imagen ${index + 1} convertida a video: ${videoSrc}`);
                        }
                    }
                } catch (error) {
                    console.error(`❌ Error convirtiendo imagen modal ${index + 1}:`, error);
                }
            });
        }
    }
    
    // Función personalizada para interceptar el modal
    function setupModalInterceptor() {
        // Interceptar cuando se abre el modal
        const modal = document.getElementById('ProductViewModal');
        if (modal) {
            modal.addEventListener('shown.bs.modal', function() {
                console.log('🎭 Modal abierto - verificando videos...');
                setTimeout(fixModalImages, 100);
            });
            
            // También interceptar cuando cambia el contenido
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1 && node.querySelector) {
                                const hasVideoImages = node.querySelector('img[src*=".mp4"], img[src*=".mov"], img[src*=".avi"], img[src*=".webm"]');
                                if (hasVideoImages) {
                                    console.log('🔄 Contenido modal cambiado - aplicando fix...');
                                    setTimeout(fixModalImages, 50);
                                }
                            }
                        });
                    }
                });
            });
            
            observer.observe(modal, {
                childList: true,
                subtree: true
            });
        }
    }
    
    // Interceptar funciones originales de población del modal
    function interceptModalFunctions() {
        // Interceptar populateProductModal si existe
        if (window.populateProductModal) {
            const originalFunction = window.populateProductModal;
            window.populateProductModal = function(...args) {
                console.log('🎯 Interceptando populateProductModal...');
                const result = originalFunction.apply(this, args);
                setTimeout(fixModalImages, 100);
                return result;
            };
        }
        
        // Interceptar showProductView si existe
        if (window.showProductView) {
            const originalShowProductView = window.showProductView;
            window.showProductView = function(...args) {
                console.log('🎯 Interceptando showProductView...');
                const result = originalShowProductView.apply(this, args);
                setTimeout(fixModalImages, 200);
                return result;
            };
        }
    }
    
    // Override función para agregar videos al modal directamente
    function createModalVideoFunction() {
        window.addVideoToModal = function(videoSrc, title = 'Video') {
            const carouselInner = document.getElementById('productViewCarouselInner');
            if (carouselInner && typeof generarMediaHTMLSincrono !== 'undefined') {
                const videoHTML = generarMediaHTMLSincrono(
                    videoSrc, 
                    title, 
                    "d-block w-100", 
                    "max-height: 400px; object-fit: contain; background: #f8f9fa;"
                );
                
                carouselInner.innerHTML = `
                    <div class="carousel-item active">
                        ${videoHTML}
                    </div>
                `;
                
                console.log('✅ Video agregado directamente al modal:', videoSrc);
                return true;
            }
            return false;
        };
    }
    
    // Inicializar
    function init() {
        setupModalInterceptor();
        interceptModalFunctions();
        createModalVideoFunction();
        
        // Fix inmediato si el modal ya está abierto
        fixModalImages();
        
        console.log('🎯 Fix específico para modal de videos configurado');
    }
    
    // Ejecutar cuando esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // También ejecutar después de un tiempo para asegurar que todo esté cargado
    setTimeout(init, 1000);
    
})();