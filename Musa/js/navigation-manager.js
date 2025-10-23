// js/navigation-manager.js - Gestión dinámica de navegación y categorías
class NavigationManager {
    constructor() {
        this.categories = {};
        this.currentFilter = null;
        this.init();
    }

    async init() {
        try {
            await this.loadCategories();
            this.setupNavigation();
            this.setupEventListeners();
            console.log('✅ Sistema de navegación inicializado correctamente');
        } catch (error) {
            console.error('❌ Error inicializando navegación:', error);
            this.showError('Error cargando categorías');
        }
    }

    async loadCategories() {
        try {
            const response = await fetch('api/navigation-categories.php');
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Error cargando categorías');
            }
            
            this.categories = data.categories;
            console.log('📂 Categorías cargadas:', this.categories);
            
        } catch (error) {
            console.error('Error cargando categorías:', error);
            throw error;
        }
    }

    setupNavigation() {
        // Actualizar dropdown MUSA (Mujer)
        const musaDropdown = document.querySelector('#coleccion-musa .dropdown-menu');
        if (musaDropdown && this.categories.mujer) {
            musaDropdown.innerHTML = this.generateDropdownItems(this.categories.mujer, 'musa');
        }

        // Actualizar dropdown ARION (Hombre)
        const arionDropdown = document.querySelector('#coleccion-arion .dropdown-menu');
        if (arionDropdown && this.categories.hombre) {
            arionDropdown.innerHTML = this.generateDropdownItems(this.categories.hombre, 'arion');
        }


    }

    generateDropdownItems(categories, collection) {
        let html = `
            <li>
                <a class="dropdown-item nav-filter" 
                   href="#" 
                   data-collection="${collection}" 
                   data-category="all">
                    Ver Todo
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
        `;

        categories.forEach(category => {
            html += `
                <li>
                    <a class="dropdown-item nav-filter" 
                       href="#" 
                       data-collection="${collection}"
                       data-category="${category.slug}" 
                       data-gender="${category.gender}">
                        ${category.name}
                    </a>
                </li>
            `;
        });

        return html;
    }



    setupEventListeners() {
        // Event delegation para los enlaces de navegación
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('nav-filter')) {
                e.preventDefault();
                this.handleCategoryClick(e.target);
            }
        });

        // Listener para el input de búsqueda
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearch(e.target.value);
            });
        }

        // Listeners para filtros adicionales
        document.addEventListener('change', (e) => {
            if (e.target.matches('input[name="precio"]') || 
                e.target.matches('select[name="ordenar"]')) {
                this.applyFilters();
            }
        });
    }

    handleCategoryClick(element) {
        const collection = element.dataset.collection;
        const category = element.dataset.category;
        const gender = element.dataset.gender;

        console.log(`🔍 Filtro seleccionado: ${collection} > ${category}`);

        // Actualizar estado actual
        this.currentFilter = {
            collection,
            category,
            gender: gender || this.getGenderFromCollection(collection)
        };

        // Aplicar filtros y scroll
        this.applyFilters();
        this.scrollToProducts();

        // Actualizar UI
        this.updateActiveStates(element);
        this.updatePageTitle(collection, category);
    }

    getGenderFromCollection(collection) {
        const genderMap = {
            'musa': 'mujer',
            'arion': 'hombre',
            'unisex': 'unisex'
        };
        return genderMap[collection] || 'unisex';
    }

    async applyFilters() {
        try {
            this.showLoadingState();
            
            const params = new URLSearchParams();
            
            if (this.currentFilter) {
                if (this.currentFilter.gender) {
                    params.append('gender', this.currentFilter.gender);
                }
                if (this.currentFilter.category && this.currentFilter.category !== 'all') {
                    params.append('category', this.currentFilter.category);
                }
            }

            // Agregar otros filtros activos
            const searchInput = document.getElementById('search-input');
            if (searchInput && searchInput.value.trim()) {
                params.append('search', searchInput.value.trim());
            }

            const response = await fetch(`api/productos-api-v2.php?${params.toString()}`);
            const data = await response.json();

            if (data.success) {
                // Don't render products here - let the main system handle it
                // this.renderProducts(data.productos);
                this.updateResultsInfo(data.productos ? data.productos.length : 0, this.currentFilter);
                
                // Instead, trigger the main products system to update
                if (window.productsManager && typeof window.productsManager.renderProducts === 'function') {
                    window.productsManager.renderProducts();
                }
            } else {
                throw new Error(data.message || 'Error cargando productos');
            }

        } catch (error) {
            console.error('Error aplicando filtros:', error);
            this.showError('Error cargando productos');
        } finally {
            this.hideLoadingState();
        }
    }

    renderProducts(productos) {
        const container = document.getElementById('productos-container');
        if (!container) return;

        if (productos.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron productos</h4>
                    <p class="text-muted">Intenta con otros filtros o términos de búsqueda</p>
                </div>
            `;
            return;
        }

        container.innerHTML = productos.map(producto => `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4" data-category="${producto.categoria || ''}" data-gender="${producto.gender || ''}">
                <div class="card product-card h-100 shadow-sm">
                    <div class="position-relative overflow-hidden">
                        <img src="${this.getProductImage(producto)}" 
                             class="card-img-top product-img" 
                             alt="${producto.name}"
                             loading="lazy"
                             style="height: 250px; object-fit: cover;">
                        ${producto.descuento ? `
                            <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                                -${producto.descuento}%
                            </span>
                        ` : ''}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title text-truncate">${producto.name}</h5>
                        <p class="card-text text-muted small flex-grow-1">${producto.description || ''}</p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div class="price-section">
                                    ${producto.precio_oferta ? `
                                        <span class="text-decoration-line-through text-muted small">$${parseFloat(producto.precio).toLocaleString()}</span>
                                        <span class="text-danger fw-bold">$${parseFloat(producto.precio_oferta).toLocaleString()}</span>
                                    ` : `
                                        <span class="text-dark fw-bold">$${parseFloat(producto.precio).toLocaleString()}</span>
                                    `}
                                </div>
                                ${producto.rating ? `
                                    <div class="stars-rating">
                                        ${this.generateStars(producto.rating)}
                                    </div>
                                ` : ''}
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-dark btn-sm" onclick="openProductModal('${producto.id}')">
                                    <i class="fas fa-eye me-1"></i> Ver Detalles
                                </button>
                                <button class="btn btn-outline-dark btn-sm" onclick="addToCart('${producto.id}')">
                                    <i class="fas fa-shopping-cart me-1"></i> Agregar al Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    getProductImage(producto) {
        if (producto.images && producto.images.length > 0) {
            return producto.images[0];
        }
        return producto.image || 'assets/placeholder-product.jpg';
    }

    generateStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        let stars = '';

        for (let i = 0; i < fullStars; i++) {
            stars += '<i class="fas fa-star text-warning"></i>';
        }

        if (hasHalfStar) {
            stars += '<i class="fas fa-star-half-alt text-warning"></i>';
        }

        const emptyStars = 5 - Math.ceil(rating);
        for (let i = 0; i < emptyStars; i++) {
            stars += '<i class="far fa-star text-warning"></i>';
        }

        return stars;
    }

    updateActiveStates(activeElement) {
        // Remover estados activos previos
        document.querySelectorAll('.nav-filter').forEach(el => {
            el.classList.remove('active');
        });

        // Agregar estado activo al elemento seleccionado
        activeElement.classList.add('active');
    }

    updatePageTitle(collection, category) {
        const titles = {
            'musa': 'MUSA - Colección Mujer',
            'arion': 'ARION - Colección Hombre',
            'unisex': 'Colección Unisex'
        };

        let title = titles[collection] || 'MUSA - Tienda de Moda';
        
        if (category && category !== 'all') {
            const categoryName = this.getCategoryName(category);
            title = `${categoryName} - ${titles[collection] || 'MUSA'}`;
        }

        document.title = title;
        
        // Actualizar meta description
        const metaDescription = document.querySelector('meta[name="description"]');
        if (metaDescription) {
            metaDescription.content = `Explora nuestra ${title.toLowerCase()} con las mejores tendencias de moda en Madrid, Cundinamarca.`;
        }
    }

    getCategoryName(categorySlug) {
        for (const gender in this.categories) {
            const category = this.categories[gender].find(cat => cat.slug === categorySlug);
            if (category) return category.name;
        }
        return categorySlug;
    }

    scrollToProducts() {
        const productsSection = document.getElementById('productos');
        if (productsSection) {
            productsSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    updateResultsInfo(count, filter) {
        const resultsInfo = document.getElementById('results-info');
        if (!resultsInfo) return;

        let message = `Mostrando ${count} producto${count !== 1 ? 's' : ''}`;
        
        if (filter && filter.category && filter.category !== 'all') {
            const categoryName = this.getCategoryName(filter.category);
            message += ` en ${categoryName}`;
        }
        
        if (filter && filter.collection) {
            const collectionNames = {
                'musa': 'MUSA',
                'arion': 'ARION',
                'unisex': 'Unisex'
            };
            message += ` - ${collectionNames[filter.collection]}`;
        }

        resultsInfo.textContent = message;
    }

    handleSearch(searchTerm) {
        // Limpiar filtro de categoría cuando se busca
        if (searchTerm.trim()) {
            this.currentFilter = null;
            this.updateActiveStates(document.createElement('a')); // Limpiar estados activos
        }
        
        // Aplicar filtros con búsqueda
        this.applyFilters();
    }

    showLoadingState() {
        const container = document.getElementById('productos-container');
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando productos...</p>
                </div>
            `;
        }
    }

    hideLoadingState() {
        // El estado de carga se oculta automáticamente al renderizar productos
    }

    showError(message) {
        const container = document.getElementById('productos-container');
        if (container) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h4 class="text-muted">Error</h4>
                    <p class="text-muted">${message}</p>
                    <button class="btn btn-outline-dark" onclick="navigationManager.applyFilters()">
                        <i class="fas fa-redo me-1"></i> Reintentar
                    </button>
                </div>
            `;
        }
    }

    // Método público para recargar categorías
    async reloadCategories() {
        await this.loadCategories();
        this.setupNavigation();
        console.log('🔄 Categorías recargadas');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.navigationManager = new NavigationManager();
});

// Función global para compatibilidad
function filterProducts(category) {
    if (window.navigationManager) {
        window.navigationManager.handleCategoryClick({
            dataset: {
                collection: 'all',
                category: category,
                gender: 'unisex'
            }
        });
    }
}