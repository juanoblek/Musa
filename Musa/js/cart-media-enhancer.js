/**
 * Mejoras automáticas para elementos multimedia en el carrito
 * Aplica contenedores, indicadores y optimizaciones responsive
 */

(function() {
    'use strict';
    
    console.log('🎬 Cargando mejoras multimedia para carrito...');
    
    function enhanceCartMediaElements() {
        // Buscar todos los elementos multimedia en el carrito
        const cartContainer = document.querySelector('#CartModal') || 
                             document.querySelector('.cart-container') ||
                             document.querySelector('[id*="cart"]');
        
        if (!cartContainer) {
            console.log('🔍 Contenedor del carrito no encontrado, reintentando...');
            return false;
        }
        
        const mediaElements = cartContainer.querySelectorAll('video, .media-elemento, img[src*="uploads/"]');
        let enhancedCount = 0;
        
        mediaElements.forEach(element => {
            // Saltar si ya está mejorado
            if (element.closest('.media-container')) {
                return;
            }
            
            // Crear contenedor mejorado
            const container = document.createElement('div');
            container.className = 'media-container';
            
            // Detectar si es video
            if (element.tagName === 'VIDEO' || element.classList.contains('media-elemento') && element.src && element.src.includes('.mp4')) {
                container.classList.add('is-video');
            }
            
            // Envolver el elemento
            const parent = element.parentNode;
            parent.insertBefore(container, element);
            container.appendChild(element);
            
            // Agregar clases mejoradas
            element.classList.add('enhanced-media');
            
            // Mejorar atributos de video
            if (element.tagName === 'VIDEO') {
                element.muted = true;
                element.loop = true;
                element.playsInline = true;
                
                // Agregar evento de click para reproducir/pausar
                element.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (this.paused) {
                        this.play().catch(console.warn);
                    } else {
                        this.pause();
                    }
                });
                
                // Auto-reproducir cuando sea visible
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            element.play().catch(() => {
                                // Silenciar error si no se puede reproducir
                            });
                        } else {
                            element.pause();
                        }
                    });
                }, { threshold: 0.5 });
                
                observer.observe(element);
            }
            
            enhancedCount++;
        });
        
        if (enhancedCount > 0) {
            console.log(`✅ ${enhancedCount} elementos multimedia mejorados en el carrito`);
            return true;
        }
        
        return false;
    }
    
    function optimizeCartLayout() {
        const cartItems = document.querySelectorAll('#CartModal .cart-item, .cart-item');
        
        cartItems.forEach(item => {
            const mediaElement = item.querySelector('video, .media-elemento, img[src*="uploads/"]');
            if (mediaElement) {
                // Mejorar layout del item
                const flexContainer = item.querySelector('.d-flex');
                if (flexContainer) {
                    flexContainer.style.alignItems = 'center';
                    flexContainer.style.gap = '0.75rem';
                }
                
                // Optimizar información del producto
                const productInfo = item.querySelector('.flex-grow-1');
                if (productInfo) {
                    productInfo.style.minWidth = '0';
                    productInfo.style.overflow = 'hidden';
                }
            }
        });
    }
    
    function addResponsiveControls() {
        // Detectar tamaño de pantalla y ajustar comportamientos
        const isMobile = window.innerWidth <= 768;
        const isTouch = 'ontouchstart' in window;
        
        document.documentElement.classList.toggle('is-mobile', isMobile);
        document.documentElement.classList.toggle('is-touch', isTouch);
        
        // Agregar clase para CSS condicional
        if (isMobile) {
            document.body.classList.add('cart-mobile');
        } else {
            document.body.classList.remove('cart-mobile');
        }
    }
    
    function setupMediaObserver() {
        // Observer para detectar nuevos elementos multimedia añadidos al carrito
        const observer = new MutationObserver((mutations) => {
            let shouldEnhance = false;
            
            mutations.forEach(mutation => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1) { // Element node
                            if (node.matches && (
                                node.matches('video, .media-elemento, img[src*="uploads/"]') ||
                                node.querySelector('video, .media-elemento, img[src*="uploads/"]')
                            )) {
                                shouldEnhance = true;
                            }
                        }
                    });
                }
            });
            
            if (shouldEnhance) {
                setTimeout(() => {
                    enhanceCartMediaElements();
                    optimizeCartLayout();
                }, 100);
            }
        });
        
        // Observar el carrito
        const cartContainer = document.querySelector('#CartModal') || 
                             document.querySelector('.offcanvas-body') ||
                             document.body;
        
        observer.observe(cartContainer, {
            childList: true,
            subtree: true
        });
        
        console.log('👁️ Observer configurado para nuevos elementos multimedia');
    }
    
    function initializeCartEnhancements() {
        console.log('🚀 Inicializando mejoras del carrito...');
        
        // Ejecutar mejoras iniciales
        setTimeout(() => {
            enhanceCartMediaElements();
            optimizeCartLayout();
            addResponsiveControls();
        }, 500);
        
        // Re-ejecutar cuando se abra el carrito
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-bs-toggle="offcanvas"], .cart-toggle, .btn-cart')) {
                setTimeout(() => {
                    enhanceCartMediaElements();
                    optimizeCartLayout();
                }, 300);
            }
        });
        
        // Configurar observer
        setupMediaObserver();
        
        // Re-ejecutar en cambios de tamaño de ventana
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                addResponsiveControls();
                optimizeCartLayout();
            }, 250);
        });
        
        // Funciones globales para debug
        window.debugCartMedia = function() {
            console.log('🎬 DEBUG - Elementos multimedia en carrito:');
            const elements = document.querySelectorAll('#CartModal video, #CartModal .media-elemento, #CartModal img[src*="uploads/"]');
            console.log(`Total: ${elements.length}`);
            elements.forEach((el, i) => {
                console.log(`${i+1}. ${el.tagName} - ${el.src || 'sin src'} - ${el.className}`);
            });
        };
        
        window.enhanceCartNow = function() {
            const enhanced = enhanceCartMediaElements();
            optimizeCartLayout();
            console.log(enhanced ? '✅ Carrito mejorado' : '⚠️ No se encontraron elementos para mejorar');
        };
        
        console.log('✅ Mejoras del carrito inicializadas correctamente');
    }
    
    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCartEnhancements);
    } else {
        initializeCartEnhancements();
    }
    
    // También ejecutar después de que otros scripts se hayan cargado
    setTimeout(initializeCartEnhancements, 1500);
    
})();