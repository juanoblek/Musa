/**
 * PRODUCT MODAL CLICK FIX
 * Solución robusta para clicks en imágenes de productos dinámicos
 */

console.log('🔧 Cargando fix para clicks en modal de producto...');

// Delegar eventos para imágenes dinámicas
function setupDynamicImageClicks() {
    console.log('🎯 Configurando delegación de eventos para imágenes...');
    
    // Remover listeners anteriores si existen
    document.removeEventListener('click', globalImageClickHandler);
    
    // Agregar delegación de eventos al documento
    document.addEventListener('click', globalImageClickHandler, true);
    
    console.log('✅ Delegación de eventos configurada');
}

// Handler global para clicks en imágenes Y VIDEOS
function globalImageClickHandler(e) {
    // Verificar si el click fue en una imagen O VIDEO de producto
    const target = e.target;
    
    const isProductMedia = target.matches('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img, .media-elemento') ||
                          target.matches('video') ||
                          target.matches('.product-card video, .api-product video, .main-product-card video');
    
    if (!isProductMedia) {
        return; // No es una imagen o video de producto
    }
    
    // Prevenir comportamiento por defecto y propagación
    e.preventDefault();
    e.stopPropagation();
    e.stopImmediatePropagation();
    
    console.log('🖼️ === CLICK INTERCEPTADO EN MEDIA ===');
    console.log('📍 Tipo:', target.tagName, '- SRC:', target.src);
    
    // Procesar click
    handleProductMediaClick(target);
}

// Función mejorada para manejar clicks en imágenes Y VIDEOS
function handleProductMediaClick(mediaElement) {
    console.log('🚀 Procesando click en media de producto...');
    console.log('📍 Elemento:', mediaElement.tagName, '- SRC:', mediaElement.src);
    
    try {
        // Buscar contenedor padre
        const productContainer = mediaElement.closest('.product-card, .api-product, .main-product-card, .card');
        
        if (!productContainer) {
            console.error('❌ No se encontró contenedor de producto');
            showErrorAlert('No se pudo encontrar la información del producto');
            return;
        }
        
        console.log('✅ Contenedor encontrado:', productContainer.className);
        
        // Extraer datos del producto
        const productData = extractProductData(productContainer, mediaElement);
        console.log('📦 Datos extraídos:', productData);
        
        // Abrir modal con búsqueda mejorada
        openProductViewModalEnhanced(productData);
        
    } catch (error) {
        console.error('❌ Error al procesar click:', error);
        showErrorAlert('Error al abrir la vista del producto: ' + error.message);
    }
}

// Extraer datos del producto del DOM
function extractProductData(container, mediaElement) {
    const data = {
        image: mediaElement.src,
        title: 'Producto',
        price: '$0',
        originalPrice: null,
        description: '',
        colors: [],
        sizes: []
    };
    
    // 🎬 BUSCAR ELEMENTO DE VIDEO PRIMERO (PRIORIDAD)
    const videoElement = container.querySelector('video');
    if (videoElement && videoElement.src && !videoElement.src.includes('placeholder')) {
        data.image = videoElement.src;
        console.log('🎬 URL de video detectada desde contenedor:', data.image);
    } else if (mediaElement.src.includes('placeholder')) {
        // Si el elemento clickeado es placeholder, buscar video o imagen real
        const realVideo = container.querySelector('video[src]:not([src*="placeholder"])');
        const realImage = container.querySelector('img[src]:not([src*="placeholder"]):not(.fallback-image)');
        
        if (realVideo) {
            data.image = realVideo.src;
            console.log('🎬 URL de video real encontrada:', data.image);
        } else if (realImage) {
            data.image = realImage.src;
            console.log('📸 URL de imagen real encontrada:', data.image);
        } else {
            console.log('⚠️ Solo se encontró placeholder, usando como fallback');
        }
    }
    
    // Extraer título
    const titleElement = container.querySelector('.card-title, h5, h3, .product-title, .title, .product-name');
    if (titleElement) {
        data.title = titleElement.textContent.trim();
    }
    
    // Extraer precio actual
    const priceElement = container.querySelector('.current-price, .price:not(.original-price), .text-primary, .product-price');
    if (priceElement) {
        data.price = priceElement.textContent.trim();
    }
    
    // Extraer precio original (si existe)
    const originalPriceElement = container.querySelector('.original-price, .text-muted.text-decoration-line-through, del');
    if (originalPriceElement) {
        data.originalPrice = originalPriceElement.textContent.trim();
    }
    
    // Extraer descripción
    const descElement = container.querySelector('.card-text, .product-description, .description');
    if (descElement) {
        data.description = descElement.textContent.trim();
    }
    
    return data;
}

// Abrir modal con los datos del producto
function openProductViewModal(productData) {
    console.log('🚀 Abriendo modal con datos:', productData);
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.error('❌ Modal ProductViewModal no encontrado');
        showErrorAlert('Modal de producto no disponible');
        return;
    }
    
    try {
        // 🔧 USAR LA FUNCIÓN CORREGIDA CON SOPORTE DE VIDEO
        if (typeof window.populateProductModal === 'function') {
            console.log('✅ Usando función corregida populateProductModal con soporte de video');
            window.populateProductModal(productData);
        } else {
            console.log('⚠️ Función populateProductModal no disponible, usando método básico');
            
            // Fallback: configurar carousel con soporte de video
            const carouselInner = document.getElementById('productViewCarouselInner');
            if (carouselInner && productData.image) {
                // Detectar si es video
                const isVideo = /\.(mp4|mov|avi|webm)$/i.test(productData.image);
                console.log(`📹 Archivo de click fix: ${productData.image} - Es video: ${isVideo}`);
                
                // Crear elemento multimedia apropiado
                const mediaElement = isVideo 
                    ? `<video src="${productData.image}" 
                             class="d-block w-100" 
                             alt="${productData.title}"
                             style="max-height: 400px; object-fit: contain; background: #f8f9fa;"
                             controls autoplay muted loop playsinline
                             onerror="console.error('Error cargando video:', '${productData.image}');">
                             Tu navegador no soporta la reproducción de video.
                       </video>`
                    : `<img src="${productData.image}" 
                           class="d-block w-100" 
                           alt="${productData.title}"
                           style="max-height: 400px; object-fit: contain; background: #f8f9fa;"
                           onerror="this.src='images/placeholder.svg';">`;
                
                carouselInner.innerHTML = `
                    <div class="carousel-item active">
                        ${mediaElement}
                    </div>
                `;
                
                console.log(`✅ Click fix: ${isVideo ? 'Video' : 'Imagen'} configurado en carousel`);
            }
            
            // Configurar título
            const modalTitle = modal.querySelector('#productViewTitle');
            if (modalTitle) {
                modalTitle.textContent = productData.title;
                console.log('📝 Título configurado:', productData.title);
            }
            
            // Configurar precio
            const modalPrice = modal.querySelector('#productViewPrice, .product-view-price');
            if (modalPrice) {
                modalPrice.textContent = productData.price;
                console.log('💰 Precio configurado:', productData.price);
            }
            
            // Configurar precio original si existe
            if (productData.originalPrice) {
                const modalOriginalPrice = modal.querySelector('#productViewOriginalPrice, .original-price');
                if (modalOriginalPrice) {
                    modalOriginalPrice.textContent = productData.originalPrice;
                    modalOriginalPrice.style.display = 'inline';
                }
            }
        }
        
        // Abrir modal usando Bootstrap (compatible con todas las versiones)
        let bsModal;
        try {
            // Intentar con getOrCreateInstance (Bootstrap 5.1+)
            if (bootstrap.Modal.getOrCreateInstance) {
                bsModal = bootstrap.Modal.getOrCreateInstance(modal);
            } else {
                // Fallback para versiones anteriores
                bsModal = new bootstrap.Modal(modal);
            }
            
            bsModal.show();
            console.log('✅ Modal abierto con Bootstrap');
            
        } catch (error) {
            console.log('🔄 Fallback: Intentando con jQuery...');
            try {
                // Fallback usando jQuery si está disponible
                if (typeof $ !== 'undefined') {
                    $(modal).modal('show');
                    console.log('✅ Modal abierto con jQuery');
                } else {
                    // Último fallback: mostrar manualmente
                    modal.style.display = 'block';
                    modal.classList.add('show');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                    console.log('✅ Modal abierto manualmente');
                }
            } catch (jqueryError) {
                console.error('❌ Error con jQuery, usando método manual:', jqueryError);
                // Último fallback: mostrar manualmente
                modal.style.display = 'block';
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('modal-open');
                console.log('✅ Modal abierto manualmente');
            }
        }
        
        console.log('✅ Modal abierto exitosamente');
        
        // Aplicar fixes específicos del modal
        setTimeout(() => {
            if (window.modalManager && window.modalManager.refresh) {
                window.modalManager.refresh();
            }
        }, 100);
        
    } catch (error) {
        console.error('❌ Error al abrir modal:', error);
        showErrorAlert('Error al configurar el modal: ' + error.message);
    }
}

// Mostrar alerta de error
function showErrorAlert(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Entendido'
        });
    } else {
        alert(message);
    }
}

// Función de test para verificar que todo funciona
function testProductModalClick() {
    console.log('🧪 Ejecutando test de click en modal...');
    
    const images = document.querySelectorAll('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img');
    
    if (images.length === 0) {
        console.log('⚠️ No se encontraron imágenes para testear');
        return false;
    }
    
    // Simular click en la primera imagen
    const firstImage = images[0];
    console.log('🎯 Testeando click en:', firstImage.src);
    
    handleProductImageClick(firstImage);
    
    return true;
}

// Inicializar cuando el DOM esté listo
function initProductModalFix() {
    console.log('🚀 Inicializando fix para modal de producto...');
    
    // Configurar delegación de eventos
    setupDynamicImageClicks();
    
    // Configurar observer para nuevas imágenes
    // ⚠️ MUTATIONOBSERVER DESHABILITADO PARA EVITAR LOOPS INFINITOS
    console.log('🚫 MutationObserver de product-modal-click-fix.js DESHABILITADO');
    console.log('💡 Esto previene loops infinitos y spam en la consola');
    
    // if (window.MutationObserver) {
    //     const observer = new MutationObserver((mutations) => {
    //         mutations.forEach((mutation) => {
    //             if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
    //                 const hasProductImages = Array.from(mutation.addedNodes).some(node => {
    //                     return node.nodeType === 1 && (
    //                         node.matches?.('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img') ||
    //                         node.querySelector?.('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img')
    //                     );
    //                 });
    //                 
    //                 if (hasProductImages) {
    //                     console.log('🔄 Nuevas imágenes de producto detectadas');
    //                     // La delegación de eventos ya las cubrirá automáticamente
    //                 }
    //             }
    //         });
    //     });
    //     
    //     observer.observe(document.body, {
    //         childList: true,
    //         subtree: true
    //     });
    //     
    //     console.log('👀 Observer configurado para nuevas imágenes');
    // }
    
    console.log('✅ Fix para modal de producto inicializado');
}

// 🔍 FUNCIÓN PARA BUSCAR URL REAL DESDE LA API
async function fetchRealMediaURL(productTitle) {
    try {
        console.log('🔍 Buscando URL real desde API para:', productTitle);
        
        const response = await fetch('api/productos-v2.php');
        const products = await response.json();
        
        if (products.success && products.data) {
            const product = products.data.find(p => 
                p.nombre && p.nombre.toLowerCase().trim() === productTitle.toLowerCase().trim()
            );
            
            if (product && product.main_image) {
                console.log('✅ URL real encontrada desde API:', product.main_image);
                return product.main_image;
            }
        }
        
        console.log('❌ No se encontró el producto en la API');
        return null;
    } catch (error) {
        console.error('❌ Error buscando en API:', error);
        return null;
    }
}

// 🚀 FUNCIÓN MEJORADA PARA ABRIR MODAL CON BÚSQUEDA DE API
async function openProductViewModalEnhanced(productData) {
    console.log('🚀 Abriendo modal mejorado con búsqueda de API...');
    
    // Si la imagen es placeholder, buscar la real desde la API
    if (productData.image && productData.image.includes('placeholder.svg') && productData.title) {
        console.log('⚠️ Detectado placeholder, buscando URL real...');
        const realURL = await fetchRealMediaURL(productData.title);
        if (realURL) {
            productData.image = realURL;
            console.log('✅ URL actualizada desde API:', realURL);
        }
    }
    
    // Llamar la función original
    return openProductViewModal(productData);
}

// Auto-inicializar
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductModalFix);
} else {
    initProductModalFix();
}

// Exportar funciones para uso manual
window.testProductModalClick = testProductModalClick;
window.setupDynamicImageClicks = setupDynamicImageClicks;
window.handleProductMediaClick = handleProductMediaClick;
window.openProductViewModalEnhanced = openProductViewModalEnhanced;
window.fetchRealMediaURL = fetchRealMediaURL;

console.log('✅ Product Modal Click Fix cargado');
console.log('🧪 Función de test: window.testProductModalClick()');