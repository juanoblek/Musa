// ================================================================================================
// 🎯 SOLUCION CONSOLA - Código para ejecutar directamente en la consola del navegador
// ================================================================================================

// ============================================================================
// 🔥 PASO 1: Copia y pega este código en la consola del navegador (F12)
// ============================================================================

(function() {
    console.log('🔥 Iniciando solución directa para modal...');
    
    // Función para eliminar placeholders y poner videos reales
    async function solucionModalVideo() {
        console.log('🔍 Verificando modal...');
        
        const modal = document.getElementById('ProductViewModal');
        if (!modal) {
            console.error('❌ Modal no encontrado');
            return false;
        }
        
        if (!modal.classList.contains('show')) {
            console.log('⚠️ Modal no está visible, abriendo modal de prueba...');
            
            // Crear datos de prueba con un video real
            const datosTest = {
                image: 'uploads/video_68f66642a5b9b_1760978498.mp4',
                title: 'Chancleta Peluche 2313',
                price: '$2'
            };
            
            // Buscar función populateProductModal
            if (window.populateProductModal) {
                console.log('✅ Función populateProductModal encontrada, llenando modal...');
                window.populateProductModal(datosTest);
                
                // Abrir modal
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                console.log('📦 Modal abierto con datos de video');
            } else {
                console.error('❌ Función populateProductModal no disponible');
                return false;
            }
        }
        
        // Esperar un momento para que se cargue el modal
        setTimeout(async () => {
            console.log('🔍 Buscando placeholders en el modal...');
            
            const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
            console.log(`⚠️ Placeholders encontrados: ${placeholders.length}`);
            
            if (placeholders.length > 0) {
                console.log('🗑️ Eliminando placeholders y poniendo video real...');
                
                try {
                    // Consultar API para obtener datos reales
                    const response = await fetch('api/productos-v2.php');
                    const data = await response.json();
                    
                    if (data.success && data.data) {
                        // Buscar primer producto con video
                        const productoConVideo = data.data.find(p => 
                            p.main_image && /\.(mp4|mov|avi|webm)$/i.test(p.main_image)
                        );
                        
                        if (productoConVideo) {
                            console.log('🎬 Video encontrado:', productoConVideo.main_image);
                            
                            // Obtener el carousel inner
                            const carouselInner = modal.querySelector('#productViewCarouselInner');
                            if (carouselInner) {
                                // Crear HTML del video
                                const videoHTML = `
                                    <div class="carousel-item active">
                                        <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 400px;">
                                            <video src="${productoConVideo.main_image}" 
                                                   class="d-block img-fluid" 
                                                   alt="${productoConVideo.name || 'Producto'}"
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
                                console.log('✅ Placeholder eliminado, video insertado');
                                
                                // Actualizar título si está disponible
                                const titleElement = modal.querySelector('#productViewTitle');
                                if (titleElement && productoConVideo.name) {
                                    titleElement.textContent = productoConVideo.name;
                                    console.log('📝 Título actualizado');
                                }
                                
                                // Actualizar botón carrito
                                const cartButton = modal.querySelector('#productViewAddToCart');
                                if (cartButton) {
                                    cartButton.setAttribute('data-image', productoConVideo.main_image);
                                    console.log('🛒 Botón carrito actualizado');
                                }
                                
                                return true;
                            }
                        } else {
                            console.error('❌ No se encontró producto con video en API');
                        }
                    } else {
                        console.error('❌ Error en respuesta de API');
                    }
                } catch (error) {
                    console.error('❌ Error consultando API:', error);
                }
            } else {
                console.log('✅ No hay placeholders, verificando si hay video...');
                const videos = modal.querySelectorAll('video');
                const images = modal.querySelectorAll('img');
                
                console.log(`📊 Estado actual: ${videos.length} videos, ${images.length} imágenes`);
                
                if (videos.length > 0) {
                    console.log('🎬 ¡PERFECTO! Ya hay video en el modal');
                    videos.forEach((video, i) => {
                        console.log(`   Video ${i+1}: ${video.src}`);
                    });
                    return true;
                }
            }
            
            return false;
        }, 1000);
    }
    
    // Ejecutar la solución
    solucionModalVideo();
    
    // Exportar función para uso manual
    window.solucionModalVideo = solucionModalVideo;
    
    console.log('🎯 Solución configurada');
    console.log('🔄 Para ejecutar manualmente: window.solucionModalVideo()');
    
})();

// ============================================================================
// 🛠️ PASO 2: Si quieres automatizar, copia este código adicional:
// ============================================================================

// Ejecutar cada 3 segundos automáticamente
setInterval(() => {
    const modal = document.getElementById('ProductViewModal');
    if (modal && modal.classList.contains('show')) {
        const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
        if (placeholders.length > 0) {
            console.log('🔄 Auto-corrección: placeholders detectados');
            window.solucionModalVideo();
        }
    }
}, 3000);

// ============================================================================
// 🎯 PASO 3: Para probar específicamente con "asdasdsa":
// ============================================================================

function probarProductoAsdasdsa() {
    console.log('🧪 Probando producto "asdasdsa"...');
    
    const datosAsdasdsa = {
        image: 'uploads/video_68f276851285d_1760720517.mp4',
        title: 'asdasdsa',
        price: '$212'
    };
    
    if (window.populateProductModal) {
        window.populateProductModal(datosAsdasdsa);
        
        const modal = document.getElementById('ProductViewModal');
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        setTimeout(() => {
            window.solucionModalVideo();
        }, 1000);
        
        console.log('✅ Modal de "asdasdsa" abierto');
    } else {
        console.error('❌ populateProductModal no disponible');
    }
}

// Exportar función de prueba
window.probarProductoAsdasdsa = probarProductoAsdasdsa;

console.log('🎬 Funciones disponibles:');
console.log('   - window.solucionModalVideo() - Solución general');
console.log('   - window.probarProductoAsdasdsa() - Test específico');