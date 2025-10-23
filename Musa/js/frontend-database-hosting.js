/**
 * ====================================================
 * 🎨 FRONTEND CON BASE DE DATOS - MUSA MODA HOSTING
 * ====================================================
 * Versión optimizada para musaarion.com
 */

console.log('🔧 [FRONTEND-DATABASE] Cargando archivo frontend-database.js para hosting...');

// Configuración optimizada para hosting
const FRONTEND_API_CONFIG = {
    baseURL: (() => {
        const hostname = window.location.hostname;
        const protocol = window.location.protocol;
        
        if (hostname === 'localhost' || hostname === '127.0.0.1') {
            return 'http://localhost/Musa/api/';
        } else if (hostname === 'musaarion.com' || hostname.includes('musaarion.com')) {
            return 'https://musaarion.com/api/';
        } else {
            return `${protocol}//${hostname}/api/`;
        }
    })(),
    timeout: 20000, // Aumentado para hosting compartido
    retries: 3
};

class DatabaseProductLoader {
    constructor() {
        console.log('🔧 Iniciando DatabaseProductLoader para hosting...');
        this.products = [];
        this.categories = [];
        this.currentFilter = {
            category: 'all',
            gender: 'all',
            search: ''
        };
        this.retryCount = 0;
        this.maxRetries = 3;
        this.isLoading = false;
        
        this.init();
    }

    /**
     * Normalizar ruta de imagen para hosting
     */
    normalizeImagePath(imagePath) {
        if (!imagePath || imagePath === 'null' || imagePath === 'undefined') {
            return 'images/placeholder.svg';
        }
        
        const hostname = window.location.hostname;
        const protocol = window.location.protocol;
        let baseURL;
        
        if (hostname === 'localhost' || hostname === '127.0.0.1') {
            baseURL = 'http://localhost/Musa';
        } else if (hostname === 'musaarion.com' || hostname.includes('musaarion.com')) {
            baseURL = 'https://musaarion.com';
        } else {
            baseURL = `${protocol}//${hostname}`;
        }
        
        if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
            return imagePath;
        }
        
        let cleanPath = imagePath.replace(/\/+/g, '/');
        
        if (cleanPath.startsWith('/uploads/')) {
            return `${baseURL}${cleanPath}`;
        }
        
        if (cleanPath.startsWith('uploads/')) {
            return `${baseURL}/${cleanPath}`;
        }
        
        if (cleanPath.startsWith('/images/')) {
            return `${baseURL}${cleanPath}`;
        }
        
        if (cleanPath.startsWith('images/')) {
            return `${baseURL}/${cleanPath}`;
        }
        
        return `${baseURL}/images/${cleanPath}`;
    }

    async init() {
        try {
            console.log('🌐 Configuración API:', FRONTEND_API_CONFIG);
            await this.loadProducts();
            this.setupEventListeners();
            console.log('✅ DatabaseProductLoader iniciado correctamente');
        } catch (error) {
            console.error('❌ Error inicializando DatabaseProductLoader:', error);
            this.handleInitError(error);
        }
    }

    async loadProducts(filters = {}) {
        if (this.isLoading) {
            console.log('⏳ Ya se está cargando productos...');
            return;
        }
        
        this.isLoading = true;
        console.log('📦 Cargando productos desde base de datos...');
        
        try {
            const params = new URLSearchParams({
                status: 'active',
                limit: 50,
                _t: Date.now(),
                ...filters
            });

            const apiUrl = `${FRONTEND_API_CONFIG.baseURL}productos.php?${params.toString()}`;
            console.log('🔗 API URL:', apiUrl);
            
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), FRONTEND_API_CONFIG.timeout);
            
            const response = await fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('📊 Respuesta del API:', data);
            
            if (data.products && Array.isArray(data.products)) {
                this.products = data.products;
                console.log(`✅ ${this.products.length} productos cargados`);
                
                this.renderProducts();
                this.updateProductCount();
                this.retryCount = 0;
            } else if (data.success && data.data?.products) {
                this.products = data.data.products;
                console.log(`✅ ${this.products.length} productos cargados (formato alternativo)`);
                
                this.renderProducts();
                this.updateProductCount();
                this.retryCount = 0;
            } else {
                throw new Error(data.error || 'Formato de respuesta inesperado');
            }
            
        } catch (error) {
            console.error('❌ Error cargando productos:', error);
            
            if (this.retryCount < this.maxRetries) {
                this.retryCount++;
                console.log(`🔄 Reintentando... (${this.retryCount}/${this.maxRetries})`);
                
                const delay = Math.pow(2, this.retryCount) * 1000;
                setTimeout(() => {
                    this.isLoading = false;
                    this.loadProducts(filters);
                }, delay);
                
                return;
            }
            
            this.showErrorMessage('Error cargando productos: ' + error.message);
        } finally {
            this.isLoading = false;
        }
    }

    renderProducts() {
        console.log('🎨 Renderizando productos...');
        
        let container = document.getElementById('products-container');
        
        if (!container) {
            container = this.createProductsContainer();
        }
        
        if (!container) {
            console.error('❌ No se pudo crear contenedor');
            return;
        }

        container.innerHTML = '';

        if (!this.products || this.products.length === 0) {
            this.showEmptyMessage(container);
            return;
        }

        this.hideStaticProducts();

        const fragment = document.createDocumentFragment();
        
        this.products.forEach((product, index) => {
            const productElement = this.createProductCard(product, index);
            if (productElement) {
                fragment.appendChild(productElement);
            }
        });
        
        container.appendChild(fragment);
        console.log(`✅ ${this.products.length} productos renderizados`);
    }

    createProductsContainer() {
        const possibleLocations = [
            document.querySelector('#prendas-exclusivas .container .row'),
            document.querySelector('#prendas-exclusivas .container'),
            document.querySelector('.container .row'),
            document.querySelector('.container'),
            document.querySelector('main'),
            document.querySelector('body')
        ];

        const targetLocation = possibleLocations.find(el => el !== null);
        
        if (targetLocation) {
            const container = document.createElement('div');
            container.id = 'products-container';
            container.className = 'row g-4';
            
            targetLocation.appendChild(container);
            console.log('✅ Contenedor de productos creado');
            return container;
        }
        
        console.error('❌ No se encontró ubicación para insertar productos');
        return null;
    }

    createProductCard(product, index) {
        try {
            const col = document.createElement('div');
            col.className = 'col-lg-4 col-md-6 mb-4 product-card';
            col.setAttribute('data-aos', 'zoom-in-up');
            col.setAttribute('data-aos-delay', (index * 100).toString());

            const card = document.createElement('div');
            card.className = 'card h-100 product-item';
            card.setAttribute('data-product-id', product.id);

            const salePrice = product.sale_price || product.price || 0;
            const hasDiscount = product.sale_price && product.sale_price < product.price;
            const discountPercentage = hasDiscount ? Math.round((1 - product.sale_price / product.price) * 100) : 0;
            const imageUrl = this.normalizeImagePath(product.main_image);

            card.innerHTML = `
                <div class="card-img-wrap position-relative">
                    ${hasDiscount ? `
                    <div class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 m-2 rounded">
                        -${discountPercentage}%
                    </div>
                    ` : ''}
                    
                    ${(() => {
                        const isVideo = /\.(mp4|mov|avi|webm)$/i.test(imageUrl);
                        return isVideo 
                            ? `<video src="${imageUrl}" 
                                     class="card-img-top" 
                                     alt="${product.name}"
                                     muted autoplay loop playsinline
                                     style="height: 300px; object-fit: cover;">
                               </video>`
                            : `<img src="${imageUrl}" 
                                   class="card-img-top" 
                                   alt="${product.name}"
                                   onerror="this.src='./images/placeholder.svg'"
                                   loading="lazy"
                                   style="height: 300px; object-fit: cover;">`;
                    })()}
                </div>

                <div class="card-body">
                    <h5 class="card-title">${product.name}</h5>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="h5 text-primary">$${salePrice.toLocaleString()}</span>
                            ${hasDiscount ? `<del class="text-muted ms-2">$${product.price.toLocaleString()}</del>` : ''}
                        </div>
                    </div>

                    <button class="btn btn-primary w-100 add-to-cart-btn" 
                            data-id="${product.id}"
                            data-name="${product.name}" 
                            data-price="${salePrice}"
                            data-image="${imageUrl}">
                        <i class="fas fa-cart-plus me-2"></i>Añadir al Carrito
                    </button>
                </div>
            `;

            col.appendChild(card);
            return col;

        } catch (error) {
            console.error(`❌ Error creando producto ${product.id}:`, error);
            return null;
        }
    }

    hideStaticProducts() {
        const staticProducts = document.querySelectorAll('.product-item:not([data-dynamic="true"])');
        staticProducts.forEach(product => {
            product.style.display = 'none';
        });
    }

    showEmptyMessage(container) {
        container.innerHTML = `
            <div class="col-12 text-center my-5">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h4>No hay productos disponibles</h4>
                    <p>No se encontraron productos que coincidan con los filtros.</p>
                </div>
            </div>
        `;
    }

    showErrorMessage(message) {
        const container = this.createProductsContainer();
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center my-5">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h4>Error de conexión</h4>
                        <p>${message}</p>
                        <button class="btn btn-primary mt-3" onclick="location.reload()">
                            <i class="fas fa-refresh me-2"></i>Recargar página
                        </button>
                    </div>
                </div>
            `;
        }
    }

    handleInitError(error) {
        console.error('❌ Error crítico:', error);
        this.showErrorMessage('No se pudo conectar con la base de datos: ' + error.message);
    }

    updateProductCount() {
        const countElements = document.querySelectorAll('.product-count');
        countElements.forEach(el => {
            el.textContent = this.products.length;
        });
    }

    setupEventListeners() {
        // Event listeners para filtros y búsquedas
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart-btn')) {
                this.handleAddToCart(e.target);
            }
        });

        // Listener para recargar productos
        window.addEventListener('reloadProducts', () => {
            this.loadProducts();
        });
    }

    handleAddToCart(button) {
        const productData = {
            id: button.dataset.id,
            name: button.dataset.name,
            price: parseFloat(button.dataset.price),
            image: button.dataset.image,
            quantity: 1
        };

        console.log('🛒 Agregando al carrito:', productData);
        
        // Agregar al carrito (implementar según tu sistema)
        if (window.cartSystem) {
            window.cartSystem.addItem(productData);
        }

        // Feedback visual
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check me-2"></i>¡Agregado!';
        button.classList.add('btn-success');
        button.classList.remove('btn-primary');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
        }, 1500);
    }
}

// Funciones globales para debug
window.reloadProducts = function() {
    if (window.productLoader) {
        window.productLoader.loadProducts();
    }
};

window.applyFilter = function(filters) {
    if (window.productLoader) {
        window.productLoader.loadProducts(filters);
    }
};

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    try {
        window.productLoader = new DatabaseProductLoader();
        console.log('✅ Sistema de productos inicializado');
    } catch (error) {
        console.error('❌ Error inicializando sistema:', error);
    }
});

console.log('🎉 Frontend database cargado correctamente para hosting');