/**
 * PRODUCT MODAL CLICK FIX - VERSIÓN OPTIMIZADA SIN SPAM
 * Solución robusta para clicks en imágenes de productos dinámicos
 * SIN OBSERVERS INNECESARIOS
 */

console.log('🔧 Cargando fix OPTIMIZADO para clicks en modal de producto...');

// Variable para evitar múltiples inicializaciones
if (window.productModalFixLoaded) {
    console.log('⚠️ Product modal fix ya cargado, evitando duplicación');
} else {
    window.productModalFixLoaded = true;
    
    // Delegar eventos para imágenes dinámicas
    function setupDynamicImageClicks() {
        console.log('🎯 Configurando delegación de eventos para imágenes...');
        
        // Remover listeners anteriores si existen
        if (window.globalImageClickHandler) {
            document.removeEventListener('click', window.globalImageClickHandler);
        }
        
        // Agregar delegación de eventos al documento
        document.addEventListener('click', globalImageClickHandler, true);
        window.globalImageClickHandler = globalImageClickHandler;
        
        console.log('✅ Delegación de eventos configurada');
    }

    // Handler global para clicks en imágenes
    function globalImageClickHandler(e) {
        // Verificar si el click fue en una imagen de producto
        const target = e.target;
        
        if (!target.matches('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img')) {
            return; // No es una imagen de producto
        }
        
        // Prevenir comportamiento por defecto y propagación
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('🖼️ Click en imagen de producto');
        
        // Procesar click
        handleProductImageClick(target);
    }

    // Función mejorada para manejar clicks en imágenes
    function handleProductImageClick(imageElement) {
        console.log('🚀 Procesando click en imagen...');
        
        try {
            // Buscar contenedor padre
            const productContainer = imageElement.closest('.product-card, .api-product, .main-product-card, .card');
            
            if (!productContainer) {
                console.error('❌ No se encontró contenedor de producto');
                return;
            }
            
            // Extraer datos del producto
            const productData = extractProductData(productContainer, imageElement);
            
            // Abrir modal
            openProductViewModal(productData);
            
        } catch (error) {
            console.error('❌ Error al procesar click:', error);
        }
    }

    // Extraer datos del producto del DOM
    function extractProductData(container, imageElement) {
        const data = {
            image: imageElement.src,
            title: 'Producto',
            price: '$0',
            originalPrice: null,
            description: '',
            colors: [],
            sizes: []
        };
        
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
        
        return data;
    }

    // Función para abrir el modal
    function openProductViewModal(productData) {
        // Verificar si showProductView existe
        if (typeof showProductView === 'function') {
            showProductView(productData);
        } else if (typeof window.showProductView === 'function') {
            window.showProductView(productData);
        } else {
            console.error('❌ Función showProductView no encontrada');
        }
    }

    // ⚠️ SIN OBSERVERS - CONFIGURACIÓN MANUAL ÚNICAMENTE
    console.log('📝 Configuración sin observers automáticos');
    console.log('💡 Las imágenes se configurarán solo en la carga inicial');

    // Configuración inicial sin observers
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupDynamicImageClicks);
    } else {
        setupDynamicImageClicks();
    }
}

// NO HAY MUTATIONOBSERVER NI EVENTOS REPETITIVOS
console.log('✅ Product modal fix optimizado cargado correctamente');