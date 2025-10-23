// ================================================================================================
// 🎬 CONSOLE COMMAND - Ejecutar directamente en consola del navegador
// ================================================================================================

// Copiar y pegar este código en la consola del navegador para convertir inmediatamente:

(function() {
    console.log('🔥 Iniciando conversión inmediata desde consola...');
    
    // Mapeo de videos
    const PRODUCT_VIDEOS = {
        'asdasdsa': 'uploads/video_68f276851285d_1760720517.mp4',
        'Chaqueta Nueva Era': 'uploads/video_68f2764f21aed_1760720047.mp4',
        'Chaqueta See': 'uploads/video_68f2786e8a477_1760720494.mp4'
    };
    
    // Buscar modal
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.error('❌ Modal no encontrado');
        return;
    }
    console.log('✅ Modal encontrado');
    
    // Buscar placeholder
    const placeholder = modal.querySelector('img[src*="placeholder.svg"]');
    if (!placeholder) {
        console.error('❌ Placeholder no encontrado');
        return;
    }
    console.log('✅ Placeholder encontrado:', placeholder.src);
    
    // Obtener título
    const titleElement = modal.querySelector('#productViewTitle');
    if (!titleElement) {
        console.error('❌ Título no encontrado');
        return;
    }
    
    const productTitle = titleElement.textContent.trim();
    console.log('📝 Producto:', productTitle);
    
    // Buscar video
    const videoUrl = PRODUCT_VIDEOS[productTitle];
    if (!videoUrl) {
        console.error('❌ No hay video para:', productTitle);
        return;
    }
    console.log('🎬 Video:', videoUrl);
    
    // Crear video
    const video = document.createElement('video');
    video.src = videoUrl;
    video.controls = true;
    video.autoplay = false;
    video.muted = true;
    video.loop = true;
    video.style.cssText = placeholder.style.cssText;
    video.className = placeholder.className;
    video.alt = placeholder.alt;
    
    // Reemplazar
    placeholder.parentNode.replaceChild(video, placeholder);
    console.log('🎯 ¡CONVERTIDO! Placeholder reemplazado con video');
    
    // Actualizar carrito
    const cartButton = modal.querySelector('#productViewAddToCart');
    if (cartButton && cartButton.dataset.image && cartButton.dataset.image.includes('placeholder.svg')) {
        cartButton.dataset.image = videoUrl;
        console.log('🛒 Carrito actualizado');
    }
    
    console.log('🎉 ¡CONVERSIÓN COMPLETADA!');
})();