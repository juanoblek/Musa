// 🚀 SCRIPT FORZADO: Conversión de videos después de carga completa
// Se ejecuta después de que todos los productos hayan cargado

(function() {
    'use strict';
    
    console.log('🚀 Script forzado de conversión de videos iniciado...');
    
    function forceVideoConversion() {
        console.log('⚡ Forzando conversión de videos...');
        
        // Buscar TODAS las imágenes que apunten a videos
        const allVideoImages = document.querySelectorAll('img');
        let convertedCount = 0;
        
        allVideoImages.forEach((img, index) => {
            try {
                const src = img.src || '';
                
                // Verificar si es un archivo de video
                if (/\.(mp4|mov|avi|webm|3gp|mkv|flv|wmv)$/i.test(src)) {
                    console.log(`🎬 Encontrada imagen de video: ${src}`);
                    
                    // Verificar que el detector esté disponible
                    if (typeof generarMediaHTMLSincrono === 'function') {
                        const alt = img.alt || img.title || 'Video';
                        const classes = img.className || '';
                        const styles = img.style.cssText || '';
                        
                        // Generar HTML de video
                        const videoHTML = generarMediaHTMLSincrono(src, alt, classes, styles);
                        
                        // Crear elemento temporal
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = videoHTML;
                        const videoElement = tempDiv.firstElementChild;
                        
                        if (videoElement && videoElement.tagName === 'VIDEO') {
                            // Copiar atributos adicionales de forma segura
                            Array.from(img.attributes).forEach(attr => {
                                if (!['src', 'alt', 'class', 'style'].includes(attr.name)) {
                                    try {
                                        // Escapar caracteres especiales en el nombre del atributo
                                        const safeName = attr.name.replace(/[^\w-]/g, '');
                                        if (safeName) {
                                            videoElement.setAttribute(safeName, attr.value);
                                        }
                                    } catch (e) {
                                        // Ignorar atributos problemáticos
                                    }
                                }
                            });
                            
                            // Copiar event listeners comunes
                            ['onclick', 'onmouseover', 'onmouseout', 'onload', 'onerror'].forEach(event => {
                                if (img[event]) {
                                    videoElement[event] = img[event];
                                }
                            });
                            
                            // Copiar atributos del modal
                            if (img.dataset.modalListener) {
                                videoElement.dataset.modalListener = img.dataset.modalListener;
                            }
                            if (img.style.cursor) {
                                videoElement.style.cursor = img.style.cursor;
                            }
                            
                            // Agregar event listener para el modal si no existe
                            if (!videoElement.dataset.modalListener) {
                                videoElement.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    
                                    console.log('🎬 Click en video forzado detectado:', this.src);
                                    
                                    if (typeof window.showProductView === 'function') {
                                        console.log('✅ Llamando window.showProductView desde video forzado...');
                                        window.showProductView(this);
                                    } else {
                                        console.error('❌ window.showProductView no está disponible');
                                    }
                                });
                                
                                videoElement.dataset.modalListener = 'true';
                                videoElement.style.cursor = 'pointer';
                                console.log('✅ Event listener para modal agregado al video forzado');
                            }
                            
                            // Reemplazar imagen con video
                            if (img.parentNode) {
                                img.parentNode.replaceChild(videoElement, img);
                                convertedCount++;
                                console.log(`✅ Convertida imagen ${index + 1} a video: ${src}`);
                            }
                        }
                    } else {
                        console.error('❌ Función generarMediaHTMLSincrono no disponible');
                    }
                }
            } catch (error) {
                console.error(`❌ Error convirtiendo imagen ${index}:`, error);
            }
        });
        
        console.log(`🎯 Conversión forzada completada: ${convertedCount} videos convertidos`);
    }
    
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            z-index: 10000;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }
    
    // Ejecutar después de diferentes eventos
    
    // 1. Si ya está cargado, ejecutar inmediatamente
    if (document.readyState === 'complete') {
        setTimeout(forceVideoConversion, 500);
    }
    
    // 2. Cuando termine de cargar
    window.addEventListener('load', () => {
        setTimeout(forceVideoConversion, 1000);
    });
    
    // 3. Después de un tiempo fijo (para productos que cargan async)
    setTimeout(forceVideoConversion, 3000);
    setTimeout(forceVideoConversion, 5000);
    setTimeout(forceVideoConversion, 10000);
    
    // 4. Cuando se haga clic en cualquier lugar (por si hay carga lazy)
    document.addEventListener('click', () => {
        setTimeout(forceVideoConversion, 200);
    }, { once: false });
    
    // 5. Cuando se haga scroll (por si hay lazy loading)
    window.addEventListener('scroll', () => {
        setTimeout(forceVideoConversion, 300);
    }, { passive: true });
    
    // 6. Observador de mutaciones para detectar nuevos elementos
    const observer = new MutationObserver((mutations) => {
        let hasNewImages = false;
        
        mutations.forEach(mutation => {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1) {
                        const images = node.querySelectorAll ? node.querySelectorAll('img') : [];
                        if (images.length > 0 || node.tagName === 'IMG') {
                            hasNewImages = true;
                        }
                    }
                });
            }
        });
        
        if (hasNewImages) {
            setTimeout(forceVideoConversion, 200);
        }
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('🎯 Script forzado configurado con múltiples triggers');
})();