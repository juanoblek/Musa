// ================================================================================================
// 🔄 AUTO FALLBACK SYSTEM - Sistema de fallback automático para modal
// ================================================================================================
console.log('🔄 Iniciando sistema de fallback automático...');

// Función para obtener URL real desde la API
async function getRealMediaURLFromAPI(productTitle) {
    try {
        console.log('🔍 Consultando API para:', productTitle);
        
        const response = await fetch('api/productos-v2.php');
        if (!response.ok) throw new Error('API no disponible');
        
        const data = await response.json();
        if (data.success && data.data) {
            const product = data.data.find(p => 
                p.nombre && p.nombre.toLowerCase().trim() === productTitle.toLowerCase().trim()
            );
            
            if (product && product.main_image && !product.main_image.includes('placeholder')) {
                console.log('✅ URL real encontrada:', product.main_image);
                return product.main_image;
            }
        }
        
        console.log('❌ No se encontró producto en API');
        return null;
    } catch (error) {
        console.error('❌ Error consultando API:', error);
        return null;
    }
}

// Función principal de fallback automático
async function executeAutoFallback() {
    console.log('🔄 Ejecutando fallback automático...');
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal || !modal.classList.contains('show')) {
        return; // Modal no visible
    }
    
    // Buscar placeholders en el modal
    const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
    if (placeholders.length === 0) {
        return; // No hay placeholders
    }
    
    console.log(`⚠️ Encontrados ${placeholders.length} placeholders en modal`);
    
    // Obtener título del producto
    const titleElement = modal.querySelector('#productViewTitle');
    if (!titleElement) {
        console.log('❌ No se encontró título del producto');
        return;
    }
    
    const productTitle = titleElement.textContent.trim();
    console.log('📝 Procesando producto:', productTitle);
    
    // Obtener URL real desde la API
    const realURL = await getRealMediaURLFromAPI(productTitle);
    if (!realURL) {
        console.log('❌ No se pudo obtener URL real');
        return;
    }
    
    // Reemplazar todos los placeholders
    placeholders.forEach((placeholder, index) => {
        console.log(`🔄 Reemplazando placeholder ${index + 1}...`);
        
        // Detectar si es video
        const isVideo = /\.(mp4|mov|avi|webm)$/i.test(realURL);
        
        if (isVideo) {
            // Crear elemento video
            const video = document.createElement('video');
            video.src = realURL;
            video.muted = true;
            video.autoplay = true;
            video.loop = true;
            video.playsInline = true;
            video.className = placeholder.className;
            video.style.cssText = placeholder.style.cssText + '; cursor: pointer;';
            video.alt = placeholder.alt;
            
            // Eventos
            video.onloadeddata = () => {
                console.log('✅ Video de fallback cargado:', realURL);
            };
            
            video.onerror = () => {
                console.error('❌ Error en video de fallback:', realURL);
            };
            
            // Reemplazar
            placeholder.parentNode.replaceChild(video, placeholder);
            console.log('🎬 Placeholder reemplazado con video');
            
        } else {
            // Es una imagen, solo actualizar la URL
            placeholder.src = realURL;
            placeholder.onerror = null; // Remover el onerror que pone placeholder
            console.log('📸 Placeholder reemplazado con imagen');
        }
    });
    
    // También actualizar el botón de carrito
    const cartButton = modal.querySelector('#productViewAddToCart');
    if (cartButton && cartButton.dataset.image && cartButton.dataset.image.includes('placeholder')) {
        cartButton.dataset.image = realURL;
        console.log('🛒 URL de carrito actualizada');
    }
    
    console.log('✅ Fallback automático completado');
}

// Función para monitorear el modal
function monitorModalForPlaceholders() {
    // Observer para detectar cambios en el modal
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        // Verificar si es un placeholder o contiene placeholders
                        const isPlaceholder = node.tagName === 'IMG' && node.src && node.src.includes('placeholder.svg');
                        const hasPlaceholders = node.querySelector && node.querySelector('img[src*="placeholder.svg"]');
                        
                        if (isPlaceholder || hasPlaceholders) {
                            console.log('🔍 Nuevo placeholder detectado, ejecutando fallback...');
                            setTimeout(executeAutoFallback, 500);
                        }
                    }
                });
            }
        });
    });
    
    // Observar todo el documento
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    console.log('👀 Monitor de placeholders activado');
}

// Sistema de verificación periódica
function startPeriodicCheck() {
    setInterval(() => {
        const modal = document.getElementById('ProductViewModal');
        if (modal && modal.classList.contains('show')) {
            const placeholders = modal.querySelectorAll('img[src*="placeholder.svg"]');
            if (placeholders.length > 0) {
                console.log('🔄 Verificación periódica: placeholders encontrados, ejecutando fallback');
                executeAutoFallback();
            }
        }
    }, 3000); // Cada 3 segundos
}

// Event listener para cuando se abre el modal
document.addEventListener('shown.bs.modal', (e) => {
    if (e.target && e.target.id === 'ProductViewModal') {
        console.log('📦 Modal de producto abierto, verificando contenido...');
        setTimeout(executeAutoFallback, 1000);
    }
});

// Inicializar sistema
console.log('🚀 Configurando sistema de fallback automático...');

// Ejecutar inmediatamente si hay modal abierto
setTimeout(executeAutoFallback, 1000);

// Configurar monitoreo
monitorModalForPlaceholders();

// Iniciar verificaciones periódicas
startPeriodicCheck();

// Función manual para debug
window.executeAutoFallback = executeAutoFallback;
window.getRealMediaURLFromAPI = getRealMediaURLFromAPI;

console.log('✅ Sistema de fallback automático configurado');
console.log('🧪 Función manual: window.executeAutoFallback()');