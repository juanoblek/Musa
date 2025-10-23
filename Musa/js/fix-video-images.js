// 🔧 FIX TEMPORAL: Convertir imágenes de video a elementos video
// Este script debe cargarse después del detector inteligente

(function() {
    'use strict';
    
    console.log('🔧 Iniciando fix temporal para videos...');
    
    function convertVideoImagesToVideoElements() {
        // Buscar todas las imágenes que apunten a archivos de video
        const allImages = document.querySelectorAll('img[src*=".mp4"], img[src*=".mov"], img[src*=".avi"], img[src*=".webm"]');
        
        console.log(`🎬 Encontradas ${allImages.length} imágenes que deberían ser videos`);
        
        allImages.forEach((img, index) => {
            try {
                const videoSrc = img.src;
                const alt = img.alt || 'Video';
                const classes = img.className;
                const styles = img.style.cssText;
                const title = img.title || '';
                
                console.log(`🔄 Convirtiendo imagen ${index + 1}: ${videoSrc}`);
                
                // Crear elemento video usando nuestro detector inteligente
                if (typeof generarMediaHTMLSincrono !== 'undefined') {
                    const videoHTML = generarMediaHTMLSincrono(videoSrc, alt, classes, styles);
                    
                    // Crear un contenedor temporal
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = videoHTML;
                    const newVideoElement = tempDiv.firstElementChild;
                    
                    // Copiar atributos adicionales
                    if (title) newVideoElement.title = title;
                    
                    // Copiar event listeners si existen
                    const events = ['click', 'mouseover', 'mouseout', 'load'];
                    events.forEach(eventType => {
                        const listener = img['on' + eventType];
                        if (listener) {
                            newVideoElement['on' + eventType] = listener;
                        }
                    });
                    
                    // Copiar atributos importantes para modal
                    if (img.dataset.modalListener) {
                        newVideoElement.dataset.modalListener = img.dataset.modalListener;
                    }
                    if (img.style.cursor) {
                        newVideoElement.style.cursor = img.style.cursor;
                    }
                    
                    // Agregar event listener para el modal si no existe
                    if (!newVideoElement.dataset.modalListener) {
                        newVideoElement.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            console.log('🎬 Click en video convertido detectado:', this.src);
                            
                            if (typeof window.showProductView === 'function') {
                                console.log('✅ Llamando window.showProductView desde video...');
                                window.showProductView(this);
                            } else {
                                console.error('❌ window.showProductView no está disponible');
                            }
                        });
                        
                        newVideoElement.dataset.modalListener = 'true';
                        newVideoElement.style.cursor = 'pointer';
                        console.log('✅ Event listener para modal agregado al video');
                    }
                    
                    // Reemplazar la imagen con el video
                    img.parentNode.replaceChild(newVideoElement, img);
                    
                    console.log(`✅ Imagen ${index + 1} convertida a video exitosamente`);
                } else {
                    console.error('❌ Detector inteligente no disponible');
                }
                
            } catch (error) {
                console.error(`❌ Error convirtiendo imagen ${index + 1}:`, error);
            }
        });
    }
    
    // Ejecutar inmediatamente
    convertVideoImagesToVideoElements();
    
    // Ejecutar cuando se agreguen nuevos elementos al DOM
    const observer = new MutationObserver(function(mutations) {
        let needsConversion = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        // Verificar si el nodo agregado o sus hijos contienen imágenes de video
                        const videoImages = node.querySelectorAll ? 
                            node.querySelectorAll('img[src*=".mp4"], img[src*=".mov"], img[src*=".avi"], img[src*=".webm"]') : [];
                        
                        if (videoImages.length > 0 || 
                            (node.tagName === 'IMG' && /\.(mp4|mov|avi|webm)$/i.test(node.src))) {
                            needsConversion = true;
                        }
                    }
                });
            }
        });
        
        if (needsConversion) {
            console.log('🔄 Nuevos elementos detectados, ejecutando conversión...');
            setTimeout(convertVideoImagesToVideoElements, 100);
        }
    });
    
    // Observar cambios en el DOM
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // Ejecutar también cuando se complete la carga de la página
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', convertVideoImagesToVideoElements);
    }
    
    // Ejecutar después de que se carguen los productos dinámicos
    window.addEventListener('load', function() {
        setTimeout(convertVideoImagesToVideoElements, 2000);
    });
    
    console.log('🎯 Fix temporal para videos activado');
})();