/**
 * Musa & Arion - Sistema Híbrido de Productos
 * Mantiene productos existentes y agrega integración backend opcional
 */

class HybridProductSystem {
    constructor() {
        this.useBackend = false;
        this.products = [];
        this.staticProducts = [];
        this.initialized = false;
        this.backendAPI = null;
        
        this.init();
    }

    async init() {
        console.log('🔄 Inicializando sistema híbrido de productos...');
        
        // Cargar productos estáticos (los que ya existen)
        this.loadStaticProducts();
        
        // Intentar conectar con backend
        await this.tryBackendConnection();
        
        // Mostrar productos
        this.displayProducts();
        
        this.initialized = true;
        console.log('✅ Sistema híbrido inicializado');
    }

    loadStaticProducts() {
        // Estos son los productos que ya existían en el sistema
        this.staticProducts = [
            {
                id: 'static-1',
                name: 'Camisa Seda Egipcia',
                price: 89900,
                sale_price: 44950,
                image: 'images/Camisas Seda Egipcia/Camisa 1.jpg',
                category: 'camisas',
                gender: 'hombre',
                description: 'Camisa de seda egipcia de alta calidad',
                colors: ['Gris', 'Beige', 'Negro', 'Cafe'],
                sizes: ['S', 'M', 'L', 'XL'],
                featured: true,
                stock: 25,
                rating: 5,
                sales: 12
            },
            {
                id: 'static-2',
                name: 'Camisa Blanco Purista',
                price: 89900,
                sale_price: 79900,
                image: 'images/Camisa Blanco Purista/Camisa Blanco Purista.jpeg',
                category: 'camisas',
                gender: 'unisex',
                description: 'Camisa elegante de color blanco, perfecta para ocasiones formales',
                colors: ['Blanco'],
                sizes: ['S', 'M', 'L', 'XL'],
                featured: true,
                stock: 18,
                rating: 5,
                sales: 8
            },
            {
                id: 'static-3',
                name: 'Camisa Elegancia Escarlata',
                price: 92900,
                sale_price: 82900,
                image: 'images/Camisa Elegancia Escarlata/Camisa Elegancia Escarlata.jpeg',
                category: 'camisas',
                gender: 'unisex',
                description: 'Camisa de color escarlata con estilo refinado',
                colors: ['Escarlata'],
                sizes: ['S', 'M', 'L', 'XL'],
                featured: true,
                stock: 15,
                rating: 5,
                sales: 6
            },
            {
                id: 'static-4',
                name: 'Camisa Gris Vanguardista',
                price: 94900,
                sale_price: 84900,
                image: 'images/Camisa Gris Vanguardista/Camisa Gris Vanguardista.jpeg',
                category: 'camisas',
                gender: 'unisex',
                description: 'Camisa gris con diseño vanguardista y moderno',
                colors: ['Gris'],
                sizes: ['S', 'M', 'L', 'XL'],
                featured: true,
                stock: 20,
                rating: 5,
                sales: 10
            },
            {
                id: 'static-5',
                name: 'Camisa Noir Refinado Negra',
                price: 96900,
                sale_price: 86900,
                image: 'images/Camisa Noir Refinado Negra/Camisa Noir Refinado Negra.jpeg',
                category: 'camisas',
                gender: 'unisex',
                description: 'Camisa negra de estilo refinado y elegante',
                colors: ['Negro'],
                sizes: ['S', 'M', 'L', 'XL'],
                featured: true,
                stock: 22,
                rating: 5,
                sales: 14
            },
            {
                id: 'static-6',
                name: 'Chaqueta Beige Botones Dorados',
                price: 189900,
                sale_price: 169900,
                image: 'images/Chaqueta Beige Botones Dorados/1.jpeg',
                category: 'chaquetas',
                gender: 'mujer',
                description: 'Chaqueta beige con botones dorados de lujo',
                colors: ['Beige'],
                sizes: ['S', 'M', 'L'],
                featured: true,
                stock: 8,
                rating: 5,
                sales: 5
            },
            {
                id: 'static-7',
                name: 'Chaqueta Deportiva Blue Ox',
                price: 149900,
                sale_price: 129900,
                image: 'images/Chaqueta Deportiva Blue Ox/1.jpeg',
                category: 'chaquetas',
                gender: 'hombre',
                description: 'Chaqueta deportiva azul de alta calidad',
                colors: ['Azul'],
                sizes: ['M', 'L', 'XL'],
                featured: true,
                stock: 12,
                rating: 5,
                sales: 7
            },
            {
                id: 'static-8',
                name: 'Blazer Cuadrado',
                price: 199900,
                sale_price: 179900,
                image: 'images/BLAZER CUADRADO.png',
                category: 'blazers',
                gender: 'unisex',
                description: 'Blazer con patrón cuadrado elegante',
                colors: ['Gris'],
                sizes: ['M', 'L', 'XL'],
                featured: true,
                stock: 6,
                rating: 5,
                sales: 3
            },
            {
                id: 'static-9',
                name: 'Blazer Morado',
                price: 199900,
                sale_price: 179900,
                image: 'images/BLAZER MORADO.png',
                category: 'blazers',
                gender: 'unisex',
                description: 'Blazer de color morado vibrante',
                colors: ['Morado'],
                sizes: ['M', 'L', 'XL'],
                featured: true,
                stock: 4,
                rating: 5,
                sales: 2
            },
            {
                id: 'static-10',
                name: 'Pantalón Drill Liso',
                price: 79900,
                sale_price: 69900,
                image: 'images/Pantalon Drill Liso/1.jpeg',
                category: 'pantalones',
                gender: 'unisex',
                description: 'Pantalón de drill liso, cómodo y versátil',
                colors: ['Khaki'],
                sizes: ['30', '32', '34', '36'],
                featured: false,
                stock: 30,
                rating: 4,
                sales: 15
            }
        ];

        console.log(`📦 Productos estáticos cargados: ${this.staticProducts.length}`);
    }

    async tryBackendConnection() {
        try {
            // Intentar conectar con el backend
            const response = await fetch('http://localhost:3002/api/health', {
                method: 'GET',
                timeout: 2000
            });
            
            if (response.ok) {
                this.useBackend = true;
                // Importar la integración backend
                if (typeof api !== 'undefined') {
                    this.backendAPI = api;
                    console.log('✅ Backend conectado, usando productos híbridos');
                } else {
                    console.log('⚠️ Backend disponible pero API no cargada');
                }
            }
        } catch (error) {
            console.log('📱 Backend no disponible, usando productos estáticos');
            this.useBackend = false;
        }
    }

    async loadBackendProducts() {
        if (!this.useBackend || !this.backendAPI) return [];

        try {
            const response = await this.backendAPI.getProducts();
            if (response.success) {
                return response.products.map(product => ({
                    id: `backend-${product.id}`,
                    name: product.name,
                    price: product.price,
                    sale_price: product.sale_price,
                    image: product.images?.[0]?.image_url || 'images/placeholder.jpg',
                    category: product.category_name?.toLowerCase() || 'general',
                    gender: product.gender,
                    description: product.description,
                    colors: product.colors?.map(c => c.color_name) || [],
                    sizes: product.sizes?.map(s => s.size_name) || [],
                    featured: product.featured,
                    stock: product.stock_quantity,
                    rating: 5,
                    sales: Math.floor(Math.random() * 20)
                }));
            }
        } catch (error) {
            console.error('Error loading backend products:', error);
        }
        
        return [];
    }

    async displayProducts() {
        // Solo cargar productos del backend si está disponible
        // NO tocar los productos estáticos que ya están en el HTML
        
        if (this.useBackend) {
            const backendProducts = await this.loadBackendProducts();
            if (backendProducts.length > 0) {
                this.addBackendProductsToDOM(backendProducts);
            }
        }

        // Aplicar filtros existentes
        this.setupFilters();
        
        // Asegurar que todos los productos sean visibles
        this.ensureProductVisibility();
    }

    addBackendProductsToDOM(backendProducts) {
        // Encontrar el contenedor de productos existente
        const container = document.querySelector('.container .row');
        if (!container) return;

        // Agregar productos del backend al final
        backendProducts.forEach(product => {
            const productHTML = this.createProductHTML(product);
            container.insertAdjacentHTML('beforeend', productHTML);
        });
    }

    ensureProductVisibility() {
        // Asegurar que todos los productos (existentes y nuevos) sean visibles
        const allProductCards = document.querySelectorAll('.product-card');
        allProductCards.forEach(card => {
            card.style.display = 'block';
            card.style.visibility = 'visible';
            card.style.opacity = '1';
        });
        
        // Aplicar estilos generales
        this.applyVisibilityStyles();
    }

    renderProductsInContainer(container) {
        // Limpiar solo los productos dinámicos, mantener los estáticos
        const existingProducts = container.querySelectorAll('.product-card[data-dynamic="true"]');
        existingProducts.forEach(product => product.remove());

        // Agregar productos dinámicos
        this.products.forEach(product => {
            const productHTML = this.createProductHTML(product);
            container.insertAdjacentHTML('beforeend', productHTML);
        });

        // Aplicar estilos para asegurar visibilidad
        this.applyVisibilityStyles();
    }

    createProductHTML(product) {
        const discount = product.sale_price ? 
            Math.round((1 - product.sale_price / product.price) * 100) : 0;

        return `
            <div class="col-lg-4 col-md-6 mb-4 product-card filter" 
                 data-gender="${product.gender}" 
                 data-category="${product.category}"
                 data-dynamic="true"
                 data-aos="zoom-in-up">
                <div class="card h-100 product-item hover-3d">
                    <div class="card-img-wrap position-relative overflow-hidden">
                        ${discount > 0 ? `
                            <div class="position-absolute top-0 start-0 m-2">
                                <span class="badge bg-danger neon-pulse">
                                    -${discount}% <span class="d-block fs-8">OFERTA</span>
                                </span>
                            </div>
                        ` : ''}
                        
                        <div class="image-hover-wrapper">
                            ${generarMediaHTMLSincrono(product.image, product.name, "card-img-top product-image glasseffect", "object-fit: cover;")}
                            <div class="hover-3d-layer"></div>
                        </div>
                    </div>

                    <div class="card-body position-relative">
                        <h5 class="card-title text-gradient-primary">${product.name}</h5>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="rating-group">
                                <div class="stars-pulse">
                                    ${Array(product.rating).fill('<i class="bi-star-fill reviews-icon"></i>').join('')}
                                </div>
                            </div>
                            <div class="sales-counter">
                                <i class="fas fa-bolt text-warning"></i>
                                <span class="badge bg-dark">${product.sales} comprados hoy</span>
                            </div>
                        </div>

                        <p class="card-text text-muted small">${product.description}</p>

                        <div class="color-options mb-3">
                            ${product.colors.map(color => `
                                <span class="color-option" 
                                      style="background-color: ${this.getColorCode(color)}"
                                      title="${color}"></span>
                            `).join('')}
                        </div>

                        <div class="size-options mb-3">
                            ${product.sizes.map(size => `
                                <span class="size-option">${size}</span>
                            `).join('')}
                        </div>

                        <div class="pricing-section mb-3">
                            ${product.sale_price ? `
                                <div class="d-flex align-items-center gap-2">
                                    <span class="price-original text-muted text-decoration-line-through">
                                        $${this.formatPrice(product.price)}
                                    </span>
                                    <span class="price-sale text-danger fw-bold fs-5">
                                        $${this.formatPrice(product.sale_price)}
                                    </span>
                                </div>
                            ` : `
                                <span class="price-regular fw-bold fs-5">
                                    $${this.formatPrice(product.price)}
                                </span>
                            `}
                        </div>

                        <div class="stock-indicator mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Stock disponible:</span>
                                <span class="badge ${product.stock > 10 ? 'bg-success' : product.stock > 5 ? 'bg-warning' : 'bg-danger'}">
                                    ${product.stock} unidades
                                </span>
                            </div>
                        </div>

                        <div class="card-actions">
                            <button class="btn btn-primary w-100 mb-2 add-to-cart-btn" 
                                    onclick="addToCart('${product.id}')">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Agregar al Carrito
                            </button>
                            <button class="btn btn-outline-secondary w-100" 
                                    onclick="viewProduct('${product.id}')">
                                <i class="fas fa-eye me-2"></i>
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    applyVisibilityStyles() {
        const style = document.createElement('style');
        style.id = 'hybrid-product-styles';
        style.textContent = `
            .product-card {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .product-image {
                display: block !important;
                visibility: visible !important;
                width: 100% !important;
                height: 250px !important;
                object-fit: cover !important;
            }
            
            .color-option {
                display: inline-block;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                margin-right: 5px;
                border: 2px solid #fff;
                box-shadow: 0 0 3px rgba(0,0,0,0.3);
                cursor: pointer;
            }
            
            .size-option {
                display: inline-block;
                padding: 2px 8px;
                margin-right: 5px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
            }
            
            .size-option:hover {
                background-color: #f8f9fa;
            }
            
            .pricing-section {
                min-height: 40px;
            }
            
            .stock-indicator {
                font-size: 14px;
            }
        `;
        
        // Remover estilo anterior si existe
        const existingStyle = document.getElementById('hybrid-product-styles');
        if (existingStyle) {
            existingStyle.remove();
        }
        
        document.head.appendChild(style);
    }

    setupFilters() {
        // Configurar filtros existentes para trabajar con productos híbridos
        const filterButtons = document.querySelectorAll('.btn-filter, .nav-filter');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const gender = button.dataset.gender;
                const category = button.dataset.category;
                this.filterProducts(gender, category);
            });
        });
    }

    filterProducts(gender = 'all', category = 'all') {
        const productCards = document.querySelectorAll('.product-card');
        
        productCards.forEach(card => {
            const cardGender = card.dataset.gender;
            const cardCategory = card.dataset.category;
            
            let showCard = true;
            
            if (gender !== 'all' && cardGender !== gender) {
                showCard = false;
            }
            
            if (category !== 'all' && cardCategory !== category) {
                showCard = false;
            }
            
            if (showCard) {
                card.style.display = 'block';
                card.classList.add('filter-show');
            } else {
                card.style.display = 'none';
                card.classList.remove('filter-show');
            }
        });
    }

    getColorCode(colorName) {
        const colorMap = {
            'Gris': '#808080',
            'Beige': '#F5F5DC',
            'Negro': '#000000',
            'Cafe': '#8B4513',
            'Blanco': '#FFFFFF',
            'Escarlata': '#DC143C',
            'Azul': '#0000FF',
            'Morado': '#800080',
            'Khaki': '#F0E68C'
        };
        return colorMap[colorName] || '#cccccc';
    }

    formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price).replace('COP', '').trim();
    }

    // Método para agregar productos desde el admin
    addProduct(product) {
        if (this.useBackend && this.backendAPI) {
            // Si hay backend, usar la API
            return this.backendAPI.createProduct(product);
        } else {
            // Si no hay backend, agregar localmente
            const newProduct = {
                id: `static-${Date.now()}`,
                ...product,
                rating: 5,
                sales: 0
            };
            this.staticProducts.push(newProduct);
            this.displayProducts();
            return Promise.resolve({ success: true, product: newProduct });
        }
    }

    // Método para obtener producto por ID
    getProductById(id) {
        return this.products.find(p => p.id === id);
    }

    // Método para buscar productos
    searchProducts(term) {
        return this.products.filter(product =>
            product.name.toLowerCase().includes(term.toLowerCase()) ||
            product.description.toLowerCase().includes(term.toLowerCase())
        );
    }
}

// Inicializar sistema híbrido
const hybridSystem = new HybridProductSystem();

// Funciones globales para compatibilidad
window.addToCart = function(productId) {
    const product = hybridSystem.getProductById(productId);
    if (product) {
        console.log('Agregando al carrito:', product.name);
        // Aquí se integraría con el sistema de carrito existente
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Agregado al carrito',
                text: `${product.name} ha sido agregado al carrito`,
                timer: 2000,
                showConfirmButton: false
            });
        }
    }
};

window.viewProduct = function(productId) {
    const product = hybridSystem.getProductById(productId);
    if (product) {
        console.log('Viendo producto:', product.name);
        // Aquí se abriría el modal o página de detalles
    }
};

// Función para filtrar productos (compatible con navegación existente)
window.filterProducts = function(gender, category) {
    hybridSystem.filterProducts(gender, category);
};

// Exportar para uso global
window.hybridSystem = hybridSystem;
window.HybridProductSystem = HybridProductSystem;

console.log('✅ Sistema híbrido de productos cargado');
