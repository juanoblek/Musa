// ProductsLoader - Versión simplificada y directa
console.log('🚀 ProductsLoader iniciado');

async function loadProductsFromAPI() {
    console.log('📡 Cargando productos desde API...');
    
    try {
        const response = await fetch('api/productos-v2.php');
        const result = await response.json();
        
        if (!result.success || !result.data) {
            console.error('❌ Error en API:', result);
            return [];
        }
        
    console.log(`✅ ${result.data.length} productos cargados de la API`);
    
    // Debug: Mostrar las categorías de cada producto
    console.log('🔍 Debug - Categorías de productos:');
    result.data.forEach(product => {
        console.log(`- ${product.name}: category_id="${product.category_id}", gender="${product.gender}"`);
    });
    
    return result.data;    } catch (error) {
        console.error('❌ Error cargando productos:', error);
        return [];
    }
}

function createProductCard(product) {
    // Arreglar la ruta de imagen para evitar duplicación de uploads/
    let image = 'images/placeholder.svg';
    if (product.main_image) {
        // Si ya contiene uploads/, usarlo directamente, sino agregarlo
        image = product.main_image.includes('uploads/') ? 
            product.main_image : 
            `uploads/${product.main_image}`;
    }
    
    const price = parseFloat(product.sale_price || product.price) || 0;
    const originalPrice = parseFloat(product.price) || 0;
    const hasDiscount = product.sale_price && product.sale_price < product.price;
    
    const formattedPrice = price.toLocaleString('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    });
    
    return `
        <div class="col-lg-4 col-md-6 mb-4 product-card filter api-product" 
             data-gender="${product.gender || ''}" 
             data-category="${product.category_id || product.category || ''}"
             data-aos="zoom-in-up"
             data-aos-delay="100"
             style="display: block !important; opacity: 1 !important; visibility: visible !important;">
            <div class="card h-100 shadow-lg border-0" style="border-radius: 20px; overflow: hidden; transform: translateY(0); transition: all 0.3s ease;">
                ${hasDiscount ? '<div class="position-absolute top-0 start-0 m-3 z-3"><span class="badge bg-danger fs-6">🔥 OFERTA</span></div>' : ''}
                
                <div class="position-relative overflow-hidden">
                    ${window.generarMediaHTMLSincrono(image, {
                        class: 'card-img-top',
                        alt: product.name,
                        style: 'height: 250px; object-fit: cover; transition: transform 0.3s ease;',
                        placeholder: 'images/placeholder.svg'
                    })}
                </div>
                
                <div class="card-body p-4">
                    <h5 class="card-title text-primary fw-bold">${product.name}</h5>
                    <p class="card-text text-muted">${product.short_description || product.description?.substring(0, 80) + '...' || 'Producto de alta calidad'}</p>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="h4 text-success fw-bold">${formattedPrice}</span>
                            ${hasDiscount ? `<br><small class="text-muted text-decoration-line-through">$${originalPrice.toLocaleString()}</small>` : ''}
                        </div>
                        ${product.stock_quantity ? `<span class="badge bg-info">Stock: ${product.stock_quantity}</span>` : ''}
                    </div>
                    
                    <button class="btn btn-primary w-100 fw-bold" onclick="handleProductClick('${product.id}', '${product.name}')">
                        🛒 Agregar al carrito
                    </button>
                </div>
            </div>
        </div>
    `;
}

function handleProductClick(productId, productName) {
    console.log('🛒 Producto seleccionado:', productName);
    alert(`🛒 ${productName}\n\nFuncionalidad de carrito próximamente.\n\nID: ${productId}`);
}

async function displayProducts() {
    console.log('🎨 Iniciando displayProducts...');
    
    // Cargar productos
    const products = await loadProductsFromAPI();
    if (products.length === 0) {
        console.log('⚠️ No hay productos para mostrar');
        return;
    }
    
    // Buscar dónde mostrar
    const section = document.getElementById('prendas-exclusivas');
    if (!section) {
        console.error('❌ Sección prendas-exclusivas no encontrada');
        return;
    }
    
    // Eliminar contenedor previo
    const oldContainer = document.getElementById('api-products-display');
    if (oldContainer) {
        oldContainer.remove();
        console.log('🗑️ Contenedor anterior eliminado');
    }
    
    // Agregar CSS para forzar visibilidad de productos API
    const forceVisibilityCSS = document.createElement('style');
    forceVisibilityCSS.id = 'api-products-force-css';
    forceVisibilityCSS.innerHTML = `
        .api-product {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: none !important;
        }
        .api-product.filtered-visible {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        .api-product.filtered-hidden {
            display: none !important;
        }
        #api-products-display .api-product {
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
    `;
    document.head.appendChild(forceVisibilityCSS);
    
    // Crear nuevo contenedor
    const container = document.createElement('div');
    container.id = 'api-products-display';
    container.style.cssText = `
        margin: 50px 0;
        padding: 50px 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 25px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        color: white;
        position: relative;
        overflow: hidden;
    `;
    
    // Crear HTML
    const productsHTML = products.map(createProductCard).join('');
    
    container.innerHTML = `
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <h2 class="display-3 fw-bold text-white mb-3" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                        🔥 Productos del Inventario
                    </h2>
                    <p class="lead text-white-50 fs-4">Productos agregados desde el panel administrativo</p>
                    <div style="width: 100px; height: 4px; background: white; margin: 20px auto; border-radius: 2px; opacity: 0.8;"></div>
                </div>
            </div>
            <div class="row g-4">
                ${productsHTML}
            </div>
        </div>
    `;
    
    // Agregar al DOM
    section.appendChild(container);
    console.log(`✅ ${products.length} productos mostrados con éxito`);
    
    // Forzar visibilidad inmediata
    const apiProducts = document.querySelectorAll('.api-product');
    apiProducts.forEach(product => {
        product.style.display = 'block';
        product.style.opacity = '1';
        product.style.visibility = 'visible';
        product.style.transform = 'none';
    });
    console.log(`🔧 Forzada visibilidad de ${apiProducts.length} productos API`);
    
    // FORZADO FINAL INMEDIATO - SIN TIMEOUT
    console.log('🚀 FORZADO FINAL INMEDIATO - Iniciando...');
    const finalApiProducts = document.querySelectorAll('.api-product');
    
    finalApiProducts.forEach((product, index) => {
        // Forzar visibilidad extrema
        product.style.cssText = `
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: static !important;
            transform: none !important;
            left: auto !important;
            top: auto !important;
            width: auto !important;
            height: auto !important;
            margin: inherit !important;
            padding: inherit !important;
            z-index: 1 !important;
        `;
        
        // Agregar clases para identificar
        product.classList.add('force-visible', 'api-visible', 'final-forced');
        product.classList.remove('d-none', 'hidden');
        
        console.log(`✅ FINAL - Producto API ${index + 1} FORZADO: ${product.dataset.category}`);
    });
    
    console.log(`🎯 FORZADO FINAL COMPLETADO: ${finalApiProducts.length} productos API`);
    
    // ARREGLAR EL CONTENEDOR ORIGINAL
    const originalContainer = document.querySelector('.row');
    if (originalContainer) {
        // Forzar dimensiones y visibilidad del contenedor original
        originalContainer.style.cssText = `
            display: flex !important;
            flex-wrap: wrap !important;
            width: 100% !important;
            min-height: 400px !important;
            opacity: 1 !important;
            visibility: visible !important;
            overflow: visible !important;
            position: relative !important;
            margin: 0 auto !important;
            padding: 20px !important;
        `;
        originalContainer.classList.remove('d-none', 'hidden');
        console.log('✅ CONTENEDOR ORIGINAL ARREGLADO - Ahora debería ser visible');
        
        // Verificar dimensiones después del arreglo
        setTimeout(() => {
            const rect = originalContainer.getBoundingClientRect();
            console.log('📐 Dimensiones del contenedor arreglado:', {
                width: rect.width,
                height: rect.height,
                visible: rect.width > 0 && rect.height > 0
            });
        }, 500);
    }
    
    // También forzar visibilidad de productos en el contenedor original
    finalApiProducts.forEach((product, index) => {
        // Forzar visibilidad extrema en el contenedor original también
        product.style.cssText = `
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            position: relative !important;
            transform: none !important;
            left: auto !important;
            top: auto !important;
            width: auto !important;
            height: auto !important;
            margin: 15px !important;
            padding: inherit !important;
            z-index: 1 !important;
            float: left !important;
            min-width: 250px !important;
            min-height: 300px !important;
        `;
        
        // Agregar clases para identificar
        product.classList.add('force-visible', 'api-visible', 'final-forced');
        product.classList.remove('d-none', 'hidden');
        
        console.log(`✅ ORIGINAL - Producto API ${index + 1} FORZADO: ${product.dataset.category}`);
    });
    
    console.log(`🎯 CONTENEDOR ORIGINAL REPARADO: ${finalApiProducts.length} productos API`);
    
    // YA NO NECESITAMOS EL CONTENEDOR DE EMERGENCIA
    console.log('✅ Contenedor original funciona - eliminando contenedor de emergencia');
    const emergencyContainer = document.getElementById('emergency-api-container');
    if (emergencyContainer) {
        emergencyContainer.remove();
        console.log('🗑️ Contenedor de emergencia eliminado');
    }
    
    // INSPECCIÓN VISUAL FINAL
    console.log('� INSPECCIÓN FINAL - Productos en contenedor original:');
    finalApiProducts.forEach((product, index) => {
        const rect = product.getBoundingClientRect();
        const styles = window.getComputedStyle(product);
        console.log(`✅ Producto ${index + 1} (${product.dataset.category}):`, {
            visible: rect.width > 0 && rect.height > 0,
            dimensions: `${rect.width}x${rect.height}`,
            display: styles.display,
            opacity: styles.opacity
        });
    });
    
    // FORZAR TAMBIÉN EL CONTENEDOR PADRE ORIGINAL
    const parentContainer = document.querySelector('.row');
    if (parentContainer) {
        parentContainer.style.cssText = `
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
            width: 100% !important;
            height: auto !important;
            min-height: 200px !important;
            overflow: visible !important;
        `;
        parentContainer.classList.remove('d-none', 'hidden');
        console.log('🎯 CONTENEDOR PADRE TAMBIÉN FORZADO');
    }
    
    // INSPECCIÓN VISUAL EXTREMA
    console.log('🔍 INICIANDO INSPECCIÓN VISUAL...');
    finalApiProducts.forEach((product, index) => {
        const rect = product.getBoundingClientRect();
        const styles = window.getComputedStyle(product);
        console.log(`🔍 Producto ${index + 1}:`, {
            visible: rect.width > 0 && rect.height > 0,
            dimensions: `${rect.width}x${rect.height}`,
            position: `${rect.left}, ${rect.top}`,
            display: styles.display,
            opacity: styles.opacity,
            visibility: styles.visibility,
            className: product.className,
            content: product.textContent.substring(0, 50) + '...'
        });
        
        // AGREGAR BORDE ROJO PARA IDENTIFICAR VISUALMENTE
        product.style.border = '3px solid red';
        product.style.backgroundColor = 'yellow';
        product.style.minHeight = '200px';
    });
    
    // SCROLL AUTOMÁTICO REMOVIDO - Se mantiene posición actual
    
    // Animación de entrada
    container.style.opacity = '0';
    container.style.transform = 'translateY(50px)';
    
    setTimeout(() => {
        container.style.transition = 'all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0)';
        
        // SCROLL AUTOMÁTICO REMOVIDO - Solo animación visual
        console.log('✅ Productos cargados sin scroll automático');
    }, 100);
}

// Auto-ejecutar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('📚 DOM cargado - ProductsLoader esperando...');
    
    // Esperar a que otros scripts se carguen primero
    setTimeout(() => {
        console.log('⏰ Iniciando carga de productos...');
        displayProducts();
    }, 4000);
});
