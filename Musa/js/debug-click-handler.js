// ================================================================================================
// 🐛 DEBUG CLICK HANDLER - Ver exactamente qué pasa cuando se hace click
// ================================================================================================
console.log('🐛 Iniciando debug de click handler...');

// Función para interceptar y mostrar todos los datos que se pasan al modal
function debugProductClick() {
    // Interceptar la función populateProductModal
    const originalPopulate = window.populateProductModal;
    
    window.populateProductModal = function(data) {
        console.log('🐛 ========== DEBUG POPULATE MODAL ==========');
        console.log('📦 Datos recibidos:', data);
        console.log('🖼️ Imagen recibida:', data.image);
        console.log('📝 Título recibido:', data.title);
        console.log('💰 Precio recibido:', data.price);
        console.log('🔍 Tipo de data.image:', typeof data.image);
        console.log('✅ ¿Es video?', /\.(mp4|mov|avi|webm)$/i.test(data.image || ''));
        console.log('⚠️ ¿Es placeholder?', (data.image || '').includes('placeholder'));
        console.log('==========================================');
        
        // Llamar la función original
        return originalPopulate.call(this, data);
    };
    
    // Interceptar extractProductData del click handler
    if (window.extractProductData) {
        const originalExtract = window.extractProductData;
        
        window.extractProductData = function(container, mediaElement) {
            console.log('🐛 ========== DEBUG EXTRACT DATA ==========');
            console.log('📦 Contenedor:', container);
            console.log('🖼️ Elemento media:', mediaElement);
            console.log('🔗 SRC del elemento:', mediaElement.src);
            console.log('🏷️ Tag del elemento:', mediaElement.tagName);
            
            // Buscar videos en el contenedor
            const videos = container.querySelectorAll('video');
            console.log('🎬 Videos encontrados:', videos.length);
            videos.forEach((video, i) => {
                console.log(`   Video ${i + 1}: ${video.src}`);
            });
            
            // Buscar imágenes no placeholder
            const realImages = container.querySelectorAll('img:not([src*="placeholder"])');
            console.log('📸 Imágenes reales:', realImages.length);
            realImages.forEach((img, i) => {
                console.log(`   Imagen ${i + 1}: ${img.src}`);
            });
            
            const result = originalExtract.call(this, container, mediaElement);
            
            console.log('📤 Datos extraídos finales:', result);
            console.log('==========================================');
            
            return result;
        };
    }
    
    // Interceptar clicks en general
    document.addEventListener('click', (e) => {
        const target = e.target;
        if (target.matches('img, video')) {
            console.log('🐛 ========== DEBUG CLICK ==========');
            console.log('🖱️ Click en:', target.tagName);
            console.log('🔗 SRC:', target.src);
            console.log('📦 Contenedor padre:', target.closest('.card, .product-card'));
            console.log('================================');
        }
    }, true);
}

// Función para hacer debug de la API
async function debugAPI() {
    console.log('🐛 ========== DEBUG API ==========');
    try {
        const response = await fetch('api/productos-v2.php');
        const data = await response.json();
        
        if (data.success && data.data) {
            console.log('📊 Total productos:', data.data.length);
            
            const videosOnly = data.data.filter(p => 
                p.main_image && /\.(mp4|mov|avi|webm)$/i.test(p.main_image)
            );
            console.log('🎬 Productos con video:', videosOnly.length);
            
            videosOnly.forEach((product, i) => {
                console.log(`🎥 Video ${i + 1}:`);
                console.log(`   - ID: ${product.id}`);
                console.log(`   - Nombre: ${product.nombre || 'SIN NOMBRE'}`);
                console.log(`   - Video: ${product.main_image}`);
                console.log(`   - Precio: ${product.precio_oferta || product.precio_original}`);
            });
        }
        
        console.log('==============================');
    } catch (error) {
        console.error('❌ Error en API:', error);
    }
}

// Función para debug manual del modal actual
function debugCurrentModal() {
    console.log('🐛 ========== DEBUG MODAL ACTUAL ==========');
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.log('❌ No se encontró modal');
        return;
    }
    
    console.log('✅ Modal encontrado');
    console.log('👁️ Visible:', modal.classList.contains('show'));
    
    const carouselInner = modal.querySelector('#productViewCarouselInner');
    if (carouselInner) {
        console.log('📦 Contenido del carousel:');
        console.log(carouselInner.innerHTML);
        
        const images = carouselInner.querySelectorAll('img');
        const videos = carouselInner.querySelectorAll('video');
        
        console.log(`🖼️ Imágenes: ${images.length}`);
        images.forEach((img, i) => {
            console.log(`   Imagen ${i + 1}: ${img.src}`);
        });
        
        console.log(`🎬 Videos: ${videos.length}`);
        videos.forEach((video, i) => {
            console.log(`   Video ${i + 1}: ${video.src}`);
        });
    }
    
    const title = modal.querySelector('#productViewTitle');
    if (title) {
        console.log('📝 Título actual:', title.textContent);
    }
    
    console.log('=========================================');
}

// Función para simular click con datos correctos
async function simulateCorrectClick() {
    console.log('🧪 ========== SIMULANDO CLICK CORRECTO ==========');
    
    try {
        // Obtener datos de la API
        const response = await fetch('api/productos-v2.php');
        const data = await response.json();
        
        if (data.success && data.data) {
            // Buscar un producto con video
            const videoProduct = data.data.find(p => 
                p.main_image && /\.(mp4|mov|avi|webm)$/i.test(p.main_image)
            );
            
            if (videoProduct) {
                console.log('🎬 Producto con video encontrado:', videoProduct);
                
                // Crear datos correctos para el modal
                const correctData = {
                    image: videoProduct.main_image,
                    title: videoProduct.nombre || 'Producto con Video',
                    price: `$${videoProduct.precio_oferta || videoProduct.precio_original || '0'}`,
                    id: videoProduct.id
                };
                
                console.log('📦 Datos correctos preparados:', correctData);
                
                // Llamar directamente a populateProductModal con datos correctos
                if (window.populateProductModal) {
                    window.populateProductModal(correctData);
                    
                    // Abrir modal si no está abierto
                    const modal = document.getElementById('ProductViewModal');
                    if (modal && !modal.classList.contains('show')) {
                        const bsModal = new bootstrap.Modal(modal);
                        bsModal.show();
                    }
                    
                    console.log('✅ Modal poblado con datos correctos');
                } else {
                    console.error('❌ Función populateProductModal no disponible');
                }
            } else {
                console.error('❌ No se encontró producto con video');
            }
        }
        
        console.log('===============================================');
    } catch (error) {
        console.error('❌ Error simulando click:', error);
    }
}

// Auto-inicializar debug
debugProductClick();

// Ejecutar debug de API
debugAPI();

// Exponer funciones globalmente
window.debugCurrentModal = debugCurrentModal;
window.simulateCorrectClick = simulateCorrectClick;
window.debugAPI = debugAPI;

console.log('🐛 Debug de click handler configurado');
console.log('🧪 Funciones disponibles:');
console.log('   - window.debugCurrentModal()');
console.log('   - window.simulateCorrectClick()');
console.log('   - window.debugAPI()');
console.log('📝 Haz click en cualquier producto para ver el debug completo');