// === SISTEMA DE SINCRONIZACIÓN PRODUCTOS OPTIMIZADO ===
// Este archivo contiene el código JavaScript optimizado para la sincronización
// entre el panel administrativo y el frontend (index.html)

console.log('🚀 Iniciando sistema de sincronización optimizado...');

// Variables globales
let products = [];
let categories = [];
let lastProductCount = 0;
let syncInitialized = false;

// === FUNCIONES DE CARGA Y SINCRONIZACIÓN ===

function loadDynamicProducts() {
    console.log('🔄 Cargando productos dinámicos...');
    
    try {
        // Cargar datos desde localStorage
        products = JSON.parse(localStorage.getItem('products')) || [];
        categories = JSON.parse(localStorage.getItem('categories')) || [];
        
        console.log(`📦 Productos cargados: ${products.length}`);
        console.log(`🏷️ Categorías cargadas: ${categories.length}`);
        
        // Buscar o crear contenedor de productos
        let container = document.getElementById('products-container');
        if (!container) {
            console.log('📦 Creando contenedor de productos...');
            container = createProductsContainer();
        }
        
        // Solo limpiar y re-renderizar si los productos han cambiado
        const currentRendered = container.querySelectorAll('.product-card').length;
        if (products.length === currentRendered && products.length > 0) {
            console.log('⏸️ No hay cambios en productos, no se re-renderiza.');
            return;
        }
        container.innerHTML = '';
        if (products.length > 0) {
            console.log('✅ Renderizando productos...');
            products.forEach((product, index) => {
                try {
                    const productHTML = createProductCard(product);
                    if (productHTML) {
                        container.innerHTML += productHTML;
                        console.log(`✅ Producto ${index + 1} renderizado: ${product.name}`);
                    }
                } catch (error) {
                    console.error(`❌ Error renderizando producto ${index}:`, error);
                }
            });
            container.style.display = 'flex';
            container.style.flexWrap = 'wrap';
            container.style.gap = '20px';
            // Ocultar productos estáticos
            const staticProducts = document.getElementById('static-products');
            if (staticProducts) {
                staticProducts.style.display = 'none';
            }
            console.log(`✅ ${products.length} productos renderizados exitosamente`);
        } else {
            console.log('ℹ️ No hay productos dinámicos');
            container.innerHTML = createEmptyState();
            // Mostrar productos estáticos como fallback
            const staticProducts = document.getElementById('static-products');
            if (staticProducts) {
                staticProducts.style.display = 'flex';
            }
        }
        
        // Reinicializar filtros y AOS
        setTimeout(() => {
            if (typeof initializeFilters === 'function') {
                initializeFilters();
            }
            if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        }, 200);
        
    } catch (error) {
        console.error('❌ Error en loadDynamicProducts:', error);
        showErrorNotification('Error cargando productos', error.message);
    }
}

function createProductsContainer() {
    console.log('🏗️ Creando contenedor de productos...');
    
    // Buscar una sección apropiada para insertar productos
    const targetSelectors = [
        '.container .row',
        '.container',
        '#products-section',
        'main .container',
        'body .container'
    ];
    
    let parentContainer = null;
    for (let selector of targetSelectors) {
        parentContainer = document.querySelector(selector);
        if (parentContainer) {
            console.log(`✅ Contenedor padre encontrado: ${selector}`);
            break;
        }
    }
    
    if (!parentContainer) {
        console.warn('⚠️ No se encontró contenedor padre, usando body');
        parentContainer = document.body;
    }
    
    // Crear contenedor
    const container = document.createElement('div');
    container.id = 'products-container';
    container.className = 'row g-4 mt-4';
    container.style.minHeight = '200px';
    
    // Agregar título
    const title = document.createElement('div');
    title.className = 'col-12 mb-4';
    title.innerHTML = `
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-center mb-0">
                <i class="fas fa-store me-2"></i>Nuestros Productos
            </h2>
            <button class="btn btn-outline-primary btn-sm" onclick="refreshProductsManually()">
                <i class="fas fa-sync-alt me-1"></i>Refrescar
            </button>
        </div>
        <hr class="mt-3">
    `;
    
    parentContainer.appendChild(title);
    parentContainer.appendChild(container);
    
    console.log('✅ Contenedor de productos creado y agregado');
    return container;
}

function createProductCard(product) {
    // Validación del producto
    if (!product || !product.name) {
        console.warn('⚠️ Producto inválido:', product);
        return '';
    }
    
    // Obtener información de categoría
    const category = categories.find(c => c.id === product.category_id);
    const categoryName = category ? category.name : 'Sin categoría';
    const categoryFilter = category ? category.filter : product.category_id;
    const productGender = product.gender || (category ? category.gender : 'unisex');
    
    // Calcular precios y descuentos
    const price = product.price || 0;
    const salePrice = product.sale_price || price;
    const hasDiscount = product.sale_price && product.sale_price < product.price;
    const discountPercentage = hasDiscount ? Math.round((1 - product.sale_price / product.price) * 100) : 0;
    
    // Generar HTML de la tarjeta
    return `
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="card product-card h-100 shadow-sm border-0" 
                 data-product-id="${product.id}"
                 data-category="${categoryFilter || ''}"
                 data-gender="${productGender}"
                 data-name="${product.name}"
                 data-aos="fade-up"
                 data-aos-delay="100">
                
                ${hasDiscount ? `
                    <div class="position-absolute top-0 end-0 bg-danger text-white px-2 py-1 m-2 rounded-pill small fw-bold" style="z-index: 10;">
                        -${discountPercentage}%
                    </div>
                ` : ''}
                
                <div class="card-img-top position-relative overflow-hidden" style="height: 250px;">
                    ${(() => {
                        const imageSrc = product.image || 'images/placeholder.jpg';
                        return generarMediaHTMLSincrono(imageSrc, product.name, "w-100 h-100 object-fit-cover", "");
                    })()}
                    
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-0 d-flex align-items-center justify-content-center opacity-0 product-overlay transition-all">
                        <button class="btn btn-light btn-sm me-2" onclick="viewProduct('${product.id}')">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="addToCart('${product.id}')">
                            <i class="fas fa-shopping-cart"></i> Agregar
                        </button>
                    </div>
                </div>
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0 fw-bold text-truncate">${product.name}</h5>
                        <small class="text-muted badge bg-light text-dark">${categoryName}</small>
                    </div>
                    
                    <p class="card-text text-muted small mb-3 flex-grow-1">${product.description || 'Sin descripción disponible'}</p>
                    
                    <div class="mt-auto">
                        ${hasDiscount ? `
                            <div class="d-flex align-items-center mb-2">
                                <span class="h5 text-danger fw-bold mb-0 me-2">$${salePrice.toLocaleString()}</span>
                                <small class="text-muted text-decoration-line-through">$${price.toLocaleString()}</small>
                            </div>
                        ` : `
                            <div class="mb-2">
                                <span class="h5 text-primary fw-bold mb-0">$${price.toLocaleString()}</span>
                            </div>
                        `}
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="fas fa-box me-1"></i>Stock: ${product.stock_quantity || 0}
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-venus-mars me-1"></i>${productGender}
                            </small>
                        </div>
                        
                        ${product.colors && product.colors.length > 0 ? `
                            <div class="mt-2">
                                <small class="text-muted">Colores: ${product.colors.slice(0, 3).join(', ')}${product.colors.length > 3 ? '...' : ''}</small>
                            </div>
                        ` : ''}
                        
                        ${product.sizes && product.sizes.length > 0 ? `
                            <div class="mt-1">
                                <small class="text-muted">Tallas: ${product.sizes.slice(0, 4).join(', ')}${product.sizes.length > 4 ? '...' : ''}</small>
                            </div>
                        ` : ''}
                        
                        <!-- Botón de añadir al carrito -->
                        <button class="btn btn-hover-glow w-100 add-to-cart-btn py-2 mt-3" 
                                data-id="${product.id}"
                                data-name="${product.name}" 
                                data-price="${salePrice}"
                                data-image="${product.image}"
                                data-category="${categoryFilter}"
                                data-gender="${productGender}">
                            <span class="btn-content">
                                <i class="fas fa-cart-plus me-2"></i>Añadir al Carrito
                            </span>
                            <span class="btn-glow"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createEmptyState() {
    return `
        <div class="col-12 text-center my-5">
            <div class="alert alert-info border-0 shadow-sm">
                <i class="fas fa-info-circle fa-3x mb-3 text-info"></i>
                <h4 class="fw-bold">No hay productos disponibles</h4>
                <p class="mb-4">Agrega productos desde el panel administrativo para verlos aquí.</p>
                <div class="d-flex justify-content-center gap-3">
                    <button class="btn btn-primary" onclick="refreshProductsManually()">
                        <i class="fas fa-sync-alt me-2"></i>Refrescar Productos
                    </button>
                    <a href="admin.html" class="btn btn-outline-secondary" target="_blank">
                        <i class="fas fa-user-shield me-2"></i>Panel Admin
                    </a>
                </div>
            </div>
        </div>
    `;
}

// === FUNCIONES DE SINCRONIZACIÓN ===

function refreshProductsManually() {
    console.log('🔄 Refrescando productos manualmente...');
    
    showNotification('Actualizando productos...', 'info');
    
    // Recargar desde localStorage
    loadDynamicProducts();
    
    // Disparar eventos de sincronización
    try {
        window.dispatchEvent(new CustomEvent('productsUpdated', {
            detail: { products: products, categories: categories }
        }));
        
        window.dispatchEvent(new StorageEvent('storage', {
            key: 'products',
            newValue: localStorage.getItem('products'),
            url: window.location.href
        }));
        
        showNotification('Productos actualizados correctamente', 'success');
        console.log('✅ Productos refrescados manualmente');
        
    } catch (error) {
        console.error('❌ Error en refresco manual:', error);
        showNotification('Error al actualizar productos', 'error');
    }
}

function setupSyncListeners() {
    if (syncInitialized) {
        console.log('⚠️ Listeners de sincronización ya inicializados');
        return;
    }
    
    console.log('📡 Configurando listeners de sincronización...');
    
    // Listener para eventos personalizados
    window.addEventListener('productsUpdated', function(event) {
        console.log('🔔 Evento productsUpdated recibido:', event.detail);
        setTimeout(() => {
            loadDynamicProducts();
        }, 100);
    });
    
    window.addEventListener('categoriesUpdated', function(event) {
        console.log('🔔 Evento categoriesUpdated recibido:', event.detail);
        setTimeout(() => {
            loadDynamicProducts();
        }, 100);
    });
    
    // Listener para cambios en localStorage
    window.addEventListener('storage', function(event) {
        if (event.key === 'products' || event.key === 'categories') {
            console.log('🔔 Cambio en localStorage detectado:', event.key);
            setTimeout(() => {
                loadDynamicProducts();
            }, 100);
        }
    });
    
    // Polling para detectar cambios (fallback)
    let lastCheckTime = Date.now();
    setInterval(() => {
        try {
            const currentProducts = JSON.parse(localStorage.getItem('products')) || [];
            const currentCategories = JSON.parse(localStorage.getItem('categories')) || [];
            
            if (currentProducts.length !== lastProductCount) {
                console.log('🔄 Cambio detectado via polling');
                lastProductCount = currentProducts.length;
                loadDynamicProducts();
            }
        } catch (error) {
            console.error('❌ Error en polling:', error);
        }
    }, 3000);
    
    syncInitialized = true;
    console.log('✅ Listeners de sincronización configurados');
}

// === FUNCIONES DE UTILIDAD ===

function showNotification(message, type = 'info') {
    console.log(`📢 ${type.toUpperCase()}: ${message}`);
    
    // Usar SweetAlert si está disponible
    if (typeof Swal !== 'undefined') {
        const icon = type === 'error' ? 'error' : 
                    type === 'success' ? 'success' : 
                    type === 'warning' ? 'warning' : 'info';
        
        Swal.fire({
            title: message,
            icon: icon,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    } else {
        // Fallback a alert nativo
        alert(`${type.toUpperCase()}: ${message}`);
    }
}

function showErrorNotification(title, message) {
    console.error(`❌ ${title}: ${message}`);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: message,
            icon: 'error',
            confirmButtonText: 'Entendido'
        });
    }
}

// Funciones placeholder para funcionalidades del carrito
function viewProduct(productId) {
    console.log('👀 Ver producto:', productId);
    showNotification('Funcionalidad de vista de producto en desarrollo', 'info');
}

function addToCart(productId) {
    console.log('🛒 Agregar al carrito:', productId);
    showNotification('Producto agregado al carrito', 'success');
}

// === INICIALIZACIÓN ===

function initializeProductSync() {
    console.log('🚀 Inicializando sistema de sincronización de productos...');
    
    try {
        // Configurar listeners
        setupSyncListeners();
        
        // Cargar productos iniciales
        loadDynamicProducts();
        
        // Actualizar contador
        const currentProducts = JSON.parse(localStorage.getItem('products')) || [];
        lastProductCount = currentProducts.length;
        
        console.log('✅ Sistema de sincronización inicializado correctamente');
        console.log(`📊 Estado inicial: ${currentProducts.length} productos`);
        
    } catch (error) {
        console.error('❌ Error inicializando sincronización:', error);
        showErrorNotification('Error de Inicialización', 'No se pudo inicializar el sistema de productos');
    }
}

// Auto-inicialización cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeProductSync);
} else {
    initializeProductSync();
}

// Exportar funciones globales para debugging
window.debugProductSync = {
    loadProducts: loadDynamicProducts,
    refreshProducts: refreshProductsManually,
    getProducts: () => products,
    getCategories: () => categories,
    reinitialize: initializeProductSync
};

console.log('📦 Sistema de sincronización de productos cargado');
console.log('🔧 Debug disponible en: window.debugProductSync');
