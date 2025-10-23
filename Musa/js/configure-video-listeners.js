// 🎯 CONFIGURADOR DE EVENT LISTENERS PARA VIDEOS
// Se asegura de que todos los videos tengan event listeners para abrir el modal

(function() {
    'use strict';
    
    console.log('🎯 Configurador de event listeners para videos iniciado...');
    
    function configureVideoListeners() {
        console.log('🔗 Configurando event listeners para videos...');
        
        // Buscar todos los videos que podrían ser productos
        const productVideos = document.querySelectorAll('video.product-image, video.card-img-top, .product-card video, .api-product video, .main-product-card video, video[src*="uploads/"]');
        
        console.log(`🎬 Encontrados ${productVideos.length} videos de productos`);
        
        let configuredCount = 0;
        
        productVideos.forEach((video, index) => {
            try {
                // Verificar si ya tiene el listener configurado
                if (video.dataset.modalListener === 'true') {
                    console.log(`⏭️ Video ${index + 1} ya tiene listener configurado`);
                    return;
                }
                
                // Configurar event listener para abrir modal
                video.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    console.log('🎬 Click en video detectado:', this.src);
                    console.log('🔍 Verificando función showProductView...');
                    
                    if (typeof window.showProductView === 'function') {
                        console.log('✅ Llamando window.showProductView...');
                        window.showProductView(this);
                    } else {
                        console.error('❌ window.showProductView no está disponible');
                        
                        // Intentar con otras funciones posibles
                        if (typeof showProductView === 'function') {
                            console.log('✅ Usando showProductView global...');
                            showProductView(this);
                        } else if (typeof window.openProductModal === 'function') {
                            console.log('✅ Usando openProductModal...');
                            window.openProductModal(this);
                        } else {
                            console.error('❌ Ninguna función de modal disponible');
                            alert('Error: No se puede abrir el modal del producto');
                        }
                    }
                });
                
                // Marcar como configurado
                video.dataset.modalListener = 'true';
                video.style.cursor = 'pointer';
                
                // Agregar título para indicar que es clickeable
                if (!video.title) {
                    video.title = 'Click para ver detalles del producto';
                }
                
                console.log(`✅ Event listener configurado para video ${index + 1}: ${video.src}`);
                configuredCount++;
                
            } catch (error) {
                console.error(`❌ Error configurando listener para video ${index + 1}:`, error);
            }
        });
        
        console.log(`🎯 Configuración de videos completada: ${configuredCount}/${productVideos.length} videos`);
        
        return configuredCount;
    }
    
    // Función para reconfigurar todos los listeners (imágenes y videos)
    function reconfigureAllListeners() {
        console.log('🔄 Reconfigurando todos los event listeners...');
        
        // Configurar videos
        const videosConfigured = configureVideoListeners();
        
        // Configurar imágenes si la función existe
        let imagesConfigured = 0;
        if (typeof window.attachProductImageListeners === 'function') {
            try {
                const result = window.attachProductImageListeners();
                imagesConfigured = result ? 1 : 0;
                console.log('✅ Función attachProductImageListeners ejecutada');
            } catch (error) {
                console.error('❌ Error ejecutando attachProductImageListeners:', error);
            }
        } else {
            console.warn('⚠️ Función attachProductImageListeners no disponible');
        }
        
        console.log(`🎯 Reconfiguración completada: ${videosConfigured} videos, ${imagesConfigured} función imágenes`);
    }
    
    // Observador para nuevos elementos
    function setupObserver() {
        const observer = new MutationObserver(function(mutations) {
            let hasNewVideos = false;
            
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            const videos = node.querySelectorAll ? node.querySelectorAll('video') : [];
                            if (videos.length > 0 || node.tagName === 'VIDEO') {
                                hasNewVideos = true;
                            }
                        }
                    });
                }
            });
            
            if (hasNewVideos) {
                console.log('🔄 Nuevos videos detectados, configurando listeners...');
                setTimeout(configureVideoListeners, 100);
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👁️ Observador de mutaciones configurado para videos');
    }
    
    // Función global para uso externo
    window.configureVideoListeners = configureVideoListeners;
    window.reconfigureAllListeners = reconfigureAllListeners;
    
    // Inicialización
    function init() {
        configureVideoListeners();
        setupObserver();
        
        console.log('🎯 Configurador de event listeners para videos listo');
    }
    
    // Ejecutar en diferentes momentos
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Ejecutar después de tiempo para asegurar que todo esté cargado
    setTimeout(init, 1000);
    setTimeout(reconfigureAllListeners, 3000);
    setTimeout(reconfigureAllListeners, 5000);
    
    // Ejecutar cuando se haga clic en cualquier parte (lazy loading)
    document.addEventListener('click', function() {
        setTimeout(configureVideoListeners, 100);
    }, { once: false });
    
    console.log('🎯 Script configurador de event listeners cargado');
    
})();