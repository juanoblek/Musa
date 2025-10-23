/**
 * Fix para imágenes placeholder que interfieren con videos
 * Elimina imágenes placeholder que se muestran junto con videos funcionando
 */

console.log('🎥 [FIX-PLACEHOLDER] Iniciando corrección de placeholders MEJORADA...');

function fixVideoPlaceholderIssue() {
    // Solo log si hay debug activo
    if (window.debugPlaceholders) {
        console.log('🔧 [FIX-PLACEHOLDER] Buscando videos con placeholders problemáticos...');
    }
    
    // Buscar todos los contenedores de imágenes con videos
    const imageContainers = document.querySelectorAll('.image-hover-wrapper, .card-img-wrap, .main-product-image');
    let fixedCount = 0;
    
    imageContainers.forEach(container => {
        const video = container.querySelector('video');
        const fallbackImg = container.querySelector('img.fallback-image, img[src*="placeholder"]');
        
        if (video && fallbackImg) {
            // Verificar si el video está funcionando
            if (video.readyState >= 2 || !video.error) {
                // Video está funcionando, ocultar completamente la imagen placeholder
                if (window.debugPlaceholders) {
                    console.log('🎥 [FIX-PLACEHOLDER] Ocultando placeholder para video funcionando:', video.src);
                }
                
                fallbackImg.style.cssText += `
                    display: none !important;
                    visibility: hidden !important;
                    opacity: 0 !important;
                    position: absolute !important;
                    top: -9999px !important;
                    left: -9999px !important;
                    width: 0 !important;
                    height: 0 !important;
                `;
                
                fixedCount++;
            }
        }
    });
    
    // Buscar imágenes placeholder que están visibles junto con videos
    const visiblePlaceholders = document.querySelectorAll('img[src*="placeholder"]:not([style*="display: none"])');
    
    visiblePlaceholders.forEach(img => {
        const container = img.closest('.image-hover-wrapper, .card-img-wrap, .main-product-image');
        if (container) {
            const video = container.querySelector('video');
            
            if (video && (video.readyState >= 2 || !video.error)) {
                if (window.debugPlaceholders) {
                    console.log('🎥 [FIX-PLACEHOLDER] Eliminando placeholder visible innecesario');
                }
                
                img.style.cssText += `
                    display: none !important;
                    visibility: hidden !important;
                    opacity: 0 !important;
                `;
                
                fixedCount++;
            }
        }
    });
    
    // Buscar elementos que tengan tanto video como imagen visible al mismo tiempo
    const allMediaContainers = document.querySelectorAll('[class*="image"], [class*="media"]');
    
    allMediaContainers.forEach(container => {
        const video = container.querySelector('video');
        const images = container.querySelectorAll('img:not([style*="display: none"])');
        
        if (video && images.length > 0 && !video.error && video.readyState >= 2) {
            images.forEach(img => {
                if (img.src.includes('placeholder') || img.classList.contains('fallback-image')) {
                    console.log('🎥 [FIX-PLACEHOLDER] Ocultando imagen placeholder duplicada');
                    
                    img.style.cssText += `
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                    `;
                    
                    fixedCount++;
                }
            });
        }
    });
    
    console.log(`✅ [FIX-PLACEHOLDER] Corregidos ${fixedCount} placeholders problemáticos`);
    return fixedCount;
}

// Función para mejorar la lógica de error de videos
function improveVideoErrorHandling() {
    const videos = document.querySelectorAll('video');
    
    videos.forEach(video => {
        // Remover handlers existentes
        video.removeEventListener('error', video._errorHandler);
        video.removeEventListener('loadstart', video._loadstartHandler);
        
        // Agregar handlers mejorados con try-catch
        video._loadstartHandler = function() {
            try {
                const container = this.closest('.image-hover-wrapper, .card-img-wrap, .main-product-image') || this.parentElement;
                if (!container) return;
                
                const fallbackImg = container.querySelector('.fallback-image, img[src*="placeholder"]');
                
                if (fallbackImg) {
                    fallbackImg.style.cssText += `
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                    `;
                }
            } catch (error) {
                console.warn('🔧 [FIX-PLACEHOLDER] Warning en loadstart handler:', error.message);
            }
        };
        
        video._errorHandler = function() {
            try {
                console.log('❌ [FIX-PLACEHOLDER] Error en video, mostrando fallback:', this.src);
                
                const container = this.closest('.image-hover-wrapper, .card-img-wrap, .main-product-image') || this.parentElement;
                if (!container) return;
                
                const fallbackImg = container.querySelector('.fallback-image, img[src*="placeholder"]');
                
                this.style.display = 'none';
            
                if (fallbackImg) {
                    fallbackImg.style.cssText = fallbackImg.style.cssText.replace(/display:\s*none\s*!important/g, 'display: block !important');
                    fallbackImg.style.display = 'block';
                    fallbackImg.style.visibility = 'visible';
                    fallbackImg.style.opacity = '1';
                }
            } catch (error) {
                console.warn('🔧 [FIX-PLACEHOLDER] Warning en error handler:', error.message);
            }
        };
        
        video.addEventListener('loadstart', video._loadstartHandler);
        video.addEventListener('error', video._errorHandler);
        video.addEventListener('canplay', video._loadstartHandler); // También cuando puede reproducir
    });
}

// Función principal
function executeVideoPlaceholderFix() {
    try {
        fixVideoPlaceholderIssue();
        improveVideoErrorHandling();
        
        // Observer para nuevos elementos
        const observer = new MutationObserver(function(mutations) {
            let needsFix = false;
            
            mutations.forEach(mutation => {
                if (mutation.addedNodes) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) {
                            if (node.tagName === 'VIDEO' || node.querySelector('video') || 
                                (node.tagName === 'IMG' && node.src && node.src.includes('placeholder'))) {
                                needsFix = true;
                            }
                        }
                    });
                }
            });
            
            if (needsFix) {
                console.log('🔄 [FIX-PLACEHOLDER] Nuevos elementos multimedia detectados, aplicando correcciones...');
                setTimeout(() => {
                    fixVideoPlaceholderIssue();
                    improveVideoErrorHandling();
                }, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👀 [FIX-PLACEHOLDER] Observer activado para nuevos elementos multimedia');
        
    } catch (error) {
        console.error('❌ [FIX-PLACEHOLDER] Error ejecutando correcciones:', error);
    }
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', executeVideoPlaceholderFix);
} else {
    executeVideoPlaceholderFix();
}

// También ejecutar después de que otros scripts hayan cargado (solo una vez)
setTimeout(executeVideoPlaceholderFix, 2000);

// Función global para corrección manual
window.fixVideoPlaceholders = fixVideoPlaceholderIssue;

console.log('✅ [FIX-PLACEHOLDER] Script de corrección de placeholders cargado');