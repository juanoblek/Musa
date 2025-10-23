// ================================================================================================
// 🎯 INTERCEPTOR MODAL PERMANENTE - Solución automática para videos en modal
// ================================================================================================
// Este archivo se carga automáticamente y corrige el problema del placeholder permanentemente

(function() {
    console.log('🚀 Iniciando interceptor modal permanente...');

    // ============================================================================
    // 🔥 INTERCEPTOR PRINCIPAL - Corrige showProductView automáticamente
    // ============================================================================

    // Esperar a que la página se cargue completamente
    function inicializarInterceptor() {
        // Verificar que las funciones existan
        if (!window.showProductView) {
            console.log('⏳ Esperando funciones del modal...');
            setTimeout(inicializarInterceptor, 500);
            return;
        }

        console.log('✅ Funciones del modal encontradas, configurando interceptor...');

        // Guardar la función original
        const originalShowProductView = window.showProductView;

        // Nueva función que intercepta y corrige
        window.showProductView = function(element) {
            console.log('🎯 INTERCEPTOR: showProductView llamado');
            
            // Extraer URL real del video si existe
            let realVideoUrl = null;
            if (element && element.tagName === 'VIDEO' && element.src) {
                realVideoUrl = element.src;
                console.log('🎬 Video detectado:', realVideoUrl);
            }
            
            // Llamar a la función original
            const result = originalShowProductView.call(this, element);
            
            // Si detectamos un video real, corregir el modal inmediatamente
            if (realVideoUrl) {
                setTimeout(() => {
                    console.log('🔄 Aplicando corrección automática...');
                    corregirModalConVideo(realVideoUrl);
                }, 200);
            } else {
                // Si no hay video específico, intentar corrección general
                setTimeout(() => {
                    corregirModalGeneral();
                }, 500);
            }
            
            return result;
        };

        console.log('🎯 Interceptor configurado exitosamente');
    }

    // ============================================================================
    // 🎬 FUNCIÓN DE CORRECCIÓN CON VIDEO ESPECÍFICO
    // ============================================================================

    function corregirModalConVideo(videoUrl) {
        console.log('🔍 Corrigiendo modal con video específico:', videoUrl);
        
        const modal = document.getElementById('ProductViewModal');
        if (!modal) return false;
        
        const carouselInner = modal.querySelector('#productViewCarouselInner');
        if (!carouselInner) return false;
        
        // Buscar placeholders
        const placeholders = carouselInner.querySelectorAll('img[src*="placeholder.svg"]');
        
        if (placeholders.length > 0) {
            console.log('🗑️ Eliminando', placeholders.length, 'placeholders...');
            
            // Crear HTML del video real
            const videoHTML = `
                <div class="carousel-item active">
                    <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 400px;">
                        <video src="${videoUrl}" 
                               class="d-block img-fluid" 
                               alt="Video del producto"
                               style="max-height: 80vh; max-width: 100%; object-fit: contain; background: #f8f9fa; border-radius: 8px; cursor: pointer;"
                               autoplay 
                               muted 
                               loop 
                               playsinline>
                               Tu navegador no soporta videos.
                        </video>
                    </div>
                </div>
            `;
            
            // Reemplazar contenido
            carouselInner.innerHTML = videoHTML;
            console.log('✅ Video real insertado en modal');
            
            // Actualizar botón carrito
            const cartButton = modal.querySelector('#productViewAddToCart');
            if (cartButton) {
                cartButton.setAttribute('data-image', videoUrl);
                console.log('🛒 Botón carrito actualizado');
            }
            
            return true;
        }
        
        return false;
    }

    // ============================================================================
    // 🔍 FUNCIÓN DE CORRECCIÓN GENERAL (busca videos en la página)
    // ============================================================================

    async function corregirModalGeneral() {
        console.log('🔍 Corrección general iniciada...');
        
        const modal = document.getElementById('ProductViewModal');
        if (!modal || !modal.classList.contains('show')) return false;
        
        const carouselInner = modal.querySelector('#productViewCarouselInner');
        if (!carouselInner) return false;
        
        // Buscar placeholders
        const placeholders = carouselInner.querySelectorAll('img[src*="placeholder.svg"]');
        
        if (placeholders.length > 0) {
            console.log('⚠️ Placeholders detectados, buscando video de reemplazo...');
            
            try {
                // Opción 1: Buscar videos en la página
                const videosEnPagina = document.querySelectorAll('video[src*="uploads/"]');
                if (videosEnPagina.length > 0) {
                    const videoUrl = videosEnPagina[0].src;
                    console.log('🎬 Usando video de la página:', videoUrl);
                    return corregirModalConVideo(videoUrl);
                }
                
                // Opción 2: Consultar API
                const response = await fetch('api/productos-v2.php');
                const data = await response.json();
                
                if (data.success && data.data) {
                    // Buscar primer producto con video
                    const productoConVideo = data.data.find(p => 
                        p.main_image && /\.(mp4|mov|avi|webm)$/i.test(p.main_image)
                    );
                    
                    if (productoConVideo) {
                        console.log('🎬 Video de API encontrado:', productoConVideo.main_image);
                        return corregirModalConVideo(productoConVideo.main_image);
                    }
                }
                
                console.log('❌ No se encontraron videos disponibles');
                return false;
                
            } catch (error) {
                console.error('❌ Error en corrección general:', error);
                return false;
            }
        }
        
        return false;
    }

    // ============================================================================
    // 🔄 MONITOR AUTOMÁTICO - Auto-corrección continua
    // ============================================================================

    function iniciarMonitorAutomatico() {
        // Auto-corrección cada 2 segundos
        setInterval(() => {
            const modal = document.getElementById('ProductViewModal');
            if (modal && modal.classList.contains('show')) {
                const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
                if (placeholders.length > 0) {
                    console.log('🔄 Auto-corrección: placeholder detectado');
                    corregirModalGeneral();
                }
            }
        }, 2000);

        console.log('👀 Monitor automático iniciado');
    }

    // ============================================================================
    // 🎭 OBSERVER DEL MODAL - Detecta cuando se abre
    // ============================================================================

    function configurarObserverModal() {
        const modal = document.getElementById('ProductViewModal');
        if (!modal) {
            setTimeout(configurarObserverModal, 500);
            return;
        }

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const modal = mutation.target;
                    if (modal.classList.contains('show')) {
                        console.log('🎭 Modal abierto detectado por observer');
                        
                        setTimeout(() => {
                            corregirModalGeneral();
                        }, 300);
                    }
                }
            });
        });

        observer.observe(modal, { attributes: true });
        console.log('👀 Observer del modal configurado');
    }

    // ============================================================================
    // 🚀 INICIALIZACIÓN AUTOMÁTICA
    // ============================================================================

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                inicializarInterceptor();
                iniciarMonitorAutomatico();
                configurarObserverModal();
            }, 1000);
        });
    } else {
        // DOM ya está listo
        setTimeout(() => {
            inicializarInterceptor();
            iniciarMonitorAutomatico();
            configurarObserverModal();
        }, 1000);
    }

    // ============================================================================
    // 🛠️ FUNCIONES GLOBALES DE UTILIDAD
    // ============================================================================

    // Función manual de corrección
    window.corregirModalVideoManual = function() {
        console.log('🔧 Corrección manual iniciada...');
        return corregirModalGeneral();
    };

    // Función para listar videos disponibles
    window.listarVideosDisponibles = function() {
        console.log('📋 Listando videos disponibles...');
        const videos = document.querySelectorAll('video[src*="uploads/"]');
        
        if (videos.length > 0) {
            videos.forEach((video, i) => {
                console.log(`   Video ${i+1}: ${video.src}`);
            });
            return videos;
        } else {
            console.log('❌ No se encontraron videos en la página');
            return [];
        }
    };

    console.log('✅ Interceptor modal permanente cargado');
    console.log('🎬 Funciones disponibles:');
    console.log('   - window.corregirModalVideoManual()');
    console.log('   - window.listarVideosDisponibles()');

})();