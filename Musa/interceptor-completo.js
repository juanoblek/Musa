// ================================================================================================
// 🎯 INTERCEPTOR COMPLETO - Corrige el problema del placeholder en el modal
// ================================================================================================
// Este script debe ejecutarse INMEDIATAMENTE en la consola del navegador

console.log('🚀 INICIANDO INTERCEPTOR COMPLETO...');

// ============================================================================
// 🔥 INTERCEPTOR PRINCIPAL - Corrige showProductView para usar videos reales
// ============================================================================

// Guardar la función original
const originalShowProductView = window.showProductView;

// Nueva función que intercepta y corrige
window.showProductView = function(element) {
    console.log('🎯 INTERCEPTOR: showProductView llamado con:', element);
    
    // Si el elemento es un video, extraer la URL real
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
            console.log('🔄 Corrigiendo modal con video real...');
            corregirModalConVideo(realVideoUrl);
        }, 100);
    }
    
    return result;
};

// ============================================================================
// 🎬 FUNCIÓN DE CORRECCIÓN - Reemplaza placeholder con video real
// ============================================================================

function corregirModalConVideo(videoUrl) {
    console.log('🔍 Buscando modal para corregir con:', videoUrl);
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.error('❌ Modal no encontrado');
        return;
    }
    
    // Buscar el carousel inner
    const carouselInner = modal.querySelector('#productViewCarouselInner');
    if (!carouselInner) {
        console.error('❌ Carousel inner no encontrado');
        return;
    }
    
    // Verificar si ya hay placeholder
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
                           style="max-height: 80vh; max-width: 100%; object-fit: contain; background: #f8f9fa; border-radius: 8px;"
                           controls 
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
        
        // Actualizar el botón carrito para usar video
        const cartButton = modal.querySelector('#productViewAddToCart');
        if (cartButton) {
            cartButton.setAttribute('data-image', videoUrl);
            console.log('🛒 Botón carrito actualizado con video');
        }
        
        return true;
    } else {
        console.log('ℹ️ No se encontraron placeholders en el modal');
        return false;
    }
}

// ============================================================================
// 🔍 MONITOR AUTOMÁTICO - Detecta cuando se abre el modal y lo corrige
// ============================================================================

// Observer para detectar cuando se abre el modal
const modalObserver = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            const modal = mutation.target;
            if (modal.id === 'ProductViewModal' && modal.classList.contains('show')) {
                console.log('🎭 Modal abierto detectado - verificando contenido...');
                
                setTimeout(() => {
                    // Buscar si hay placeholders y videos disponibles
                    const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
                    
                    if (placeholders.length > 0) {
                        console.log('⚠️ Placeholders detectados en modal, buscando video real...');
                        
                        // Buscar el último video clickeado en la página
                        const videos = document.querySelectorAll('video[src*="uploads/"]');
                        if (videos.length > 0) {
                            // Usar el primer video encontrado como fallback
                            const videoUrl = videos[0].src;
                            console.log('🎬 Usando video encontrado:', videoUrl);
                            corregirModalConVideo(videoUrl);
                        } else {
                            console.log('❌ No se encontraron videos en la página');
                        }
                    }
                }, 500);
            }
        }
    });
});

// Observar el modal
const modalElement = document.getElementById('ProductViewModal');
if (modalElement) {
    modalObserver.observe(modalElement, { attributes: true });
    console.log('👀 Observer configurado en el modal');
} else {
    console.error('❌ Modal no encontrado para observar');
}

// ============================================================================
// 🛠️ FUNCIONES DE UTILIDAD MANUAL
// ============================================================================

// Función para corregir manualmente
window.corregirModalManual = function() {
    console.log('🔧 Corrección manual iniciada...');
    
    // Buscar videos en la página
    const videos = document.querySelectorAll('video[src*="uploads/"]');
    if (videos.length > 0) {
        const videoUrl = videos[0].src;
        console.log('🎬 Usando primer video encontrado:', videoUrl);
        return corregirModalConVideo(videoUrl);
    } else {
        console.error('❌ No se encontraron videos en la página');
        return false;
    }
};

// Función para listar todos los videos disponibles
window.listarVideos = function() {
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

// ============================================================================
// 🎯 AUTO-CORRECCIÓN CADA 2 SEGUNDOS
// ============================================================================

setInterval(() => {
    const modal = document.getElementById('ProductViewModal');
    if (modal && modal.classList.contains('show')) {
        const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
        if (placeholders.length > 0) {
            console.log('🔄 Auto-corrección: placeholder detectado');
            window.corregirModalManual();
        }
    }
}, 2000);

// ============================================================================
// 🚀 CONFIRMACIÓN
// ============================================================================

console.log('✅ INTERCEPTOR COMPLETO CONFIGURADO');
console.log('🎬 Funciones disponibles:');
console.log('   - window.corregirModalManual() - Corrige manualmente');
console.log('   - window.listarVideos() - Lista videos disponibles');
console.log('🔄 Auto-corrección activa cada 2 segundos');
console.log('👀 Observer del modal activo');

// Hacer una corrección inicial si el modal ya está abierto
const modal = document.getElementById('ProductViewModal');
if (modal && modal.classList.contains('show')) {
    console.log('🎯 Modal ya abierto - aplicando corrección inicial...');
    setTimeout(() => {
        window.corregirModalManual();
    }, 1000);
}