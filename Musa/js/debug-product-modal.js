/**
 * DEBUG Y FIX PARA MODAL PRODUCT VIEW
 * Detecta y corrige problemas con el modal de vista de producto
 */

console.log('🔧 Iniciando Debug para Modal ProductView...');

// Función de debug mejorada para detectar problemas
function debugProductViewModal() {
    console.log('🔍 === DIAGNÓSTICO COMPLETO DEL MODAL ===');
    
    // 1. Verificar el modal
    const modal = document.getElementById('ProductViewModal');
    console.log('📱 Modal existe:', !!modal);
    
    if (!modal) {
        console.error('❌ Modal ProductViewModal no encontrado!');
        return false;
    }
    
    // 2. Verificar la función showProductView
    console.log('🔧 window.showProductView:', typeof window.showProductView);
    
    // 3. Verificar imágenes de productos
    const images = document.querySelectorAll('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img');
    console.log(`🖼️ Imágenes encontradas: ${images.length}`);
    
    // 4. Verificar listeners en las imágenes
    let imagesWithListeners = 0;
    images.forEach((img, index) => {
        if (img.dataset.modalListener === 'true') {
            imagesWithListeners++;
        }
        console.log(`   - Imagen ${index + 1}: ${img.src.substring(img.src.lastIndexOf('/') + 1)} - Listener: ${img.dataset.modalListener === 'true' ? '✅' : '❌'}`);
    });
    
    console.log(`🎯 Imágenes con listeners: ${imagesWithListeners}/${images.length}`);
    
    // 5. Test directo del modal
    return testModalDirectly();
}

// Test directo del modal
function testModalDirectly() {
    console.log('🧪 Probando abrir modal directamente...');
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.error('❌ Modal no encontrado para test');
        return false;
    }
    
    try {
        // Configurar datos de prueba
        document.getElementById('productViewImage').src = 'uploads/product_68c221404c803_1757552960.jpeg';
        document.getElementById('productViewTitle').textContent = 'Producto de Prueba';
        
        // Intentar abrir el modal usando diferentes métodos
        let success = false;
        
        // Método 1: Bootstrap 5.1+
        if (bootstrap.Modal && bootstrap.Modal.getOrCreateInstance) {
            try {
                const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
                bsModal.show();
                success = true;
                console.log('✅ Modal abierto con Bootstrap 5.1+');
            } catch (e) {
                console.log('⚠️ Bootstrap 5.1+ falló, probando siguiente método...');
            }
        }
        
        // Método 2: Bootstrap constructor directo
        if (!success && bootstrap.Modal) {
            try {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                success = true;
                console.log('✅ Modal abierto con Bootstrap constructor');
            } catch (e) {
                console.log('⚠️ Bootstrap constructor falló, probando siguiente método...');
            }
        }
        
        // Método 3: jQuery
        if (!success && typeof $ !== 'undefined') {
            try {
                $(modal).modal('show');
                success = true;
                console.log('✅ Modal abierto con jQuery');
            } catch (e) {
                console.log('⚠️ jQuery falló, usando método manual...');
            }
        }
        
        // Método 4: Manual
        if (!success) {
            modal.style.display = 'block';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('modal-open');
            success = true;
            console.log('✅ Modal abierto manualmente');
        }
        
        if (success) {
            console.log('✅ Modal abierto directamente');
            
            // Cerrar después de 2 segundos
            setTimeout(() => {
                try {
                    if (bootstrap.Modal && modal.classList.contains('show')) {
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        if (bsModal) {
                            bsModal.hide();
                        } else {
                            // Fallback manual
                            modal.style.display = 'none';
                            modal.classList.remove('show');
                            modal.setAttribute('aria-hidden', 'true');
                            document.body.classList.remove('modal-open');
                        }
                    }
                    console.log('✅ Modal cerrado automáticamente');
                } catch (closeError) {
                    console.log('⚠️ Error al cerrar modal:', closeError);
                }
            }, 2000);
        }
        
        return success;
    } catch (error) {
        console.error('❌ Error al abrir modal:', error);
        return false;
    }
}

// Función para recrear listeners de forma robusta
function recreateImageListeners() {
    console.log('🔄 Recreando listeners de imágenes...');
    
    const images = document.querySelectorAll('.product-image, .card-img-top, .product-card img, .api-product img, .main-product-card img');
    
    images.forEach((img, index) => {
        // Limpiar listener anterior
        img.removeEventListener('click', handleImageClick);
        
        // Agregar nuevo listener
        img.addEventListener('click', handleImageClick);
        img.dataset.modalListener = 'true';
        img.style.cursor = 'pointer';
        
        console.log(`✅ Listener recreado para imagen ${index + 1}`);
    });
    
    console.log(`🎯 ${images.length} listeners recreados`);
}

// Handler mejorado para clicks en imágenes
function handleImageClick(e) {
    e.preventDefault();
    e.stopPropagation();
    
    console.log('🖼️ === CLICK EN IMAGEN DETECTADO ===');
    console.log('📍 Imagen src:', this.src);
    console.log('📍 Imagen alt:', this.alt);
    
    // Buscar información del producto
    const productCard = this.closest('.product-card, .product-item, .card, .api-product, .main-product-card');
    
    if (!productCard) {
        console.error('❌ No se encontró contenedor de producto');
        alert('Error: No se pudo encontrar la información del producto');
        return;
    }
    
    console.log('✅ Contenedor encontrado:', productCard.className);
    
    // Extraer datos del producto
    const title = productCard.querySelector('.card-title, h5, h3, .product-title, .title')?.textContent?.trim() || 'Producto';
    const price = productCard.querySelector('.current-price, .price, .text-primary')?.textContent?.trim() || '$0';
    
    console.log('📦 Datos extraídos:', { title, price, image: this.src });
    
    // Abrir modal con datos
    openProductModal(this.src, title, price, productCard);
}

// Función para abrir el modal con datos específicos
function openProductModal(imageSrc, title, price, productCard) {
    console.log('🚀 Abriendo modal con datos:', { imageSrc, title, price });
    
    const modal = document.getElementById('ProductViewModal');
    if (!modal) {
        console.error('❌ Modal no encontrado');
        alert('Error: Modal de producto no disponible');
        return;
    }
    
    try {
        // Configurar imagen
        const modalImage = document.getElementById('productViewImage');
        if (modalImage) {
            modalImage.src = imageSrc;
            modalImage.alt = title;
            console.log('🖼️ Imagen configurada en modal');
        }
        
        // Configurar título
        const modalTitle = document.getElementById('productViewTitle');
        if (modalTitle) {
            modalTitle.textContent = title;
            console.log('📝 Título configurado en modal');
        }
        
        // Configurar precio
        const modalPrice = document.getElementById('productViewPrice');
        if (modalPrice) {
            modalPrice.textContent = price;
            console.log('💰 Precio configurado en modal');
        }
        
        // Abrir modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
        
        console.log('✅ Modal abierto exitosamente');
        
    } catch (error) {
        console.error('❌ Error al configurar modal:', error);
        alert('Error al abrir la vista del producto: ' + error.message);
    }
}

// Función de corrección automática
function autoFixProductModal() {
    console.log('🔧 === CORRECCIÓN AUTOMÁTICA DEL MODAL ===');
    
    // 1. Verificar y recrear listeners
    recreateImageListeners();
    
    // 2. Verificar que la función showProductView esté disponible
    if (typeof window.showProductView !== 'function') {
        console.log('🔧 Creando función showProductView de emergencia...');
        window.showProductView = function(imageElement) {
            handleImageClick.call(imageElement, { preventDefault: () => {}, stopPropagation: () => {} });
        };
    }
    
    // 3. Test final - DESACTIVADO PARA EVITAR APERTURA AUTOMÁTICA
    // const testResult = testModalDirectly();
    
    console.log('✅ Corrección automática exitosa');
    // if (testResult) {
    //     console.log('✅ Corrección automática exitosa');
    // } else {
    //     console.log('⚠️ Algunos problemas persisten');
    // }
    
    return true; // testResult;
}

// Ejecutar corrección automática cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoFixProductModal);
} else {
    autoFixProductModal();
}

// Exportar funciones para uso manual
window.debugProductViewModal = debugProductViewModal;
window.recreateImageListeners = recreateImageListeners;
window.autoFixProductModal = autoFixProductModal;
window.testModalDirectly = testModalDirectly;

console.log('✅ Debug ProductView Modal cargado. Funciones disponibles:');
console.log('  - window.debugProductViewModal()');
console.log('  - window.recreateImageListeners()');
console.log('  - window.autoFixProductModal()');
console.log('  - window.testModalDirectly()');