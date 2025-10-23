// ================================================================================================
// 🎯 OVERRIDE POPULATE MODAL - Reemplaza función para evitar placeholders permanentemente
// ================================================================================================

(function() {
    console.log('🚀 Configurando override de populateProductModal...');

    function inicializarOverride() {
        // Esperar a que la función original exista
        if (!window.populateProductModal) {
            console.log('⏳ Esperando populateProductModal...');
            setTimeout(inicializarOverride, 500);
            return;
        }

        console.log('✅ populateProductModal encontrada, aplicando override...');

        // Guardar función original
        const originalPopulateProductModal = window.populateProductModal;

        // Nueva función que nunca usa placeholders con videos
        window.populateProductModal = function(data) {
            console.log('🎯 OVERRIDE populateProductModal llamada con:', data);

            // Si viene un placeholder pero tenemos videos disponibles, usar video real
            if (data && data.image && data.image.includes('placeholder.svg')) {
                console.log('⚠️ Placeholder detectado, buscando video real...');

                // Buscar videos en la página
                const videosEnPagina = document.querySelectorAll('video[src*="uploads/"]');
                if (videosEnPagina.length > 0) {
                    const videoReal = videosEnPagina[0].src;
                    console.log('🎬 Reemplazando placeholder con video:', videoReal);
                    
                    // Crear nueva data con video real
                    const newData = { ...data, image: videoReal };
                    return originalPopulateProductModal.call(this, newData);
                }

                // Si no hay videos en página, consultar API
                buscarVideoEnAPI()
                    .then(videoUrl => {
                        if (videoUrl) {
                            console.log('🎬 Video de API encontrado:', videoUrl);
                            const newData = { ...data, image: videoUrl };
                            originalPopulateProductModal.call(this, newData);
                        } else {
                            // Si no hay videos disponibles, usar función original
                            originalPopulateProductModal.call(this, data);
                        }
                    })
                    .catch(error => {
                        console.error('❌ Error buscando video en API:', error);
                        originalPopulateProductModal.call(this, data);
                    });
                
                // Retornar temprano para evitar doble ejecución
                return;
            }

            // Si no es placeholder, usar función original
            return originalPopulateProductModal.call(this, data);
        };

        console.log('✅ Override de populateProductModal configurado');
    }

    // Función auxiliar para buscar video en API
    async function buscarVideoEnAPI() {
        try {
            const response = await fetch('api/productos-v2.php');
            const data = await response.json();
            
            if (data.success && data.data) {
                const productoConVideo = data.data.find(p => 
                    p.main_image && /\.(mp4|mov|avi|webm)$/i.test(p.main_image)
                );
                
                return productoConVideo ? productoConVideo.main_image : null;
            }
            return null;
        } catch (error) {
            console.error('Error consultando API:', error);
            return null;
        }
    }

    // Inicializar cuando DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(inicializarOverride, 1500);
        });
    } else {
        setTimeout(inicializarOverride, 1500);
    }

    console.log('✅ Override populateProductModal cargado');

})();