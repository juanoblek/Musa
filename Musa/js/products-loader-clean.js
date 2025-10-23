// 🎯 PRODUCTS LOADER DEFINITIVO - VERSIÓN LIMPIA
console.log('🚀 ProductsLoader LIMPIO iniciado');

class CleanProductsLoader {
    constructor() {
        this.products = [];
        this.apiEndpoint = 'api/productos-v2.php';
        this.init();
    }

    async init() {
        try {
            console.log('📡 Cargando productos desde API...');
            const response = await fetch(this.apiEndpoint);
            const data = await response.json();
            
            if (data.success && data.data) {
                this.products = data.data;
                console.log(`✅ ${this.products.length} productos cargados de la API`);
                
                // Debug de categorías
                console.log('🔍 Debug - Categorías de productos:');
                this.products.forEach(product => {
                    console.log(`- ${product.name}: category_id="${product.category_id}", gender="${product.gender}"`);
                });
                
                this.createCleanContainer();
                this.renderProducts();
                this.setupFilters();
            } else {
                console.error('❌ Error en la respuesta de la API:', data);
            }
        } catch (error) {
            console.error('💥 Error al cargar productos:', error);
        }
    }

    createCleanContainer() {
        // ELIMINAR TODOS LOS CONTENEDORES PROBLEMÁTICOS
        const oldContainers = [
            document.getElementById('clean-api-products'),
            document.getElementById('api-products-display'),
            document.querySelector('.row'),
            document.querySelector('#products-grid'),
            document.querySelector('.products-container')
        ];
        
        oldContainers.forEach(container => {
            if (container) {
                console.log('🗑️ Eliminando contenedor problemático:', container.id || container.className);
                container.remove();
            }
        });

        // ENCONTRAR EL LUGAR EXACTO DONDE ESTÁN LOS PRODUCTOS PRINCIPALES
        const targetLocation = this.findMainProductsLocation();

        // CREAR CONTENEDOR EN EL LUGAR PRINCIPAL
        const container = document.createElement('section');
        container.id = 'main-products-container';
        container.className = 'main-products-section';
        
        // ESTILOS PERFECTOS SIN CONFLICTOS
        container.style.cssText = `
            display: block !important;
            position: relative !important;
            width: 100% !important;
            max-width: 1200px !important;
            margin: 40px auto !important;
            padding: 30px !important;
            background: white !important;
            border-radius: 15px !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1) !important;
            z-index: 1 !important;
            min-height: 500px !important;
            clear: both !important;
            overflow: visible !important;
            visibility: visible !important;
            opacity: 1 !important;
        `;
        
        // CSS INTEGRADO PARA MÁXIMA COMPATIBILIDAD
        this.injectStyles();

        // CREAR TÍTULO INTEGRADO
        const title = document.createElement('h2');
        title.className = 'main-products-title';
        title.innerHTML = '🛍️ Productos Disponibles';
        title.style.cssText = `
            text-align: center !important;
            margin: 0 0 30px 0 !important;
            color: #333 !important;
            font-size: 2.2rem !important;
            font-weight: 600 !important;
            border-bottom: 3px solid #007bff !important;
            padding-bottom: 15px !important;
        `;
        container.appendChild(title);

        // CREAR GRID PERFECTO
        const grid = document.createElement('div');
        grid.id = 'main-products-grid';
        grid.className = 'main-products-grid';
        grid.style.cssText = `
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
            gap: 25px !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        `;
        container.appendChild(grid);

        // INSERTAR EN EL LUGAR PRINCIPAL EXACTO
        targetLocation.appendChild(container);

        console.log('✅ Contenedor principal creado en la ubicación correcta');
        
        // SCROLL INMEDIATO
        setTimeout(() => {
            container.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }, 300);
    }

    findMainProductsLocation() {
        // BUSCAR LA UBICACIÓN EXACTA DONDE DEBEN IR LOS PRODUCTOS
        
        // 1. Buscar después de las categorías
        const categoriesGrid = document.querySelector('.categories-grid');
        if (categoriesGrid && categoriesGrid.parentNode) {
            console.log('📍 Ubicación encontrada: después de categories-grid');
            return categoriesGrid.parentNode;
        }

        // 2. Buscar en sección main
        const mainSection = document.querySelector('main');
        if (mainSection) {
            console.log('📍 Ubicación encontrada: main section');
            return mainSection;
        }

        // 3. Buscar contenedor principal
        const container = document.querySelector('.container');
        if (container) {
            console.log('📍 Ubicación encontrada: container principal');
            return container;
        }

        // 4. Fallback al body
        console.log('📍 Ubicación fallback: body');
        return document.body;
    }

    injectStyles() {
        // ELIMINAR ESTILOS ANTERIORES
        const oldStyle = document.getElementById('main-products-styles');
        if (oldStyle) oldStyle.remove();
        
        const style = document.createElement('style');
        style.id = 'main-products-styles';
        style.textContent = `
            .main-products-section {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
            }
            
            .main-products-grid {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)) !important;
                gap: 25px !important;
            }
            
            .main-product-card {
                display: block !important;
                background: white !important;
                border: 1px solid #ddd !important;
                border-radius: 12px !important;
                padding: 20px !important;
                text-align: center !important;
                box-shadow: 0 3px 10px rgba(0,0,0,0.1) !important;
                transition: all 0.3s ease !important;
                position: relative !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                min-height: 400px !important;
                overflow: hidden !important;
            }
            
            .main-product-card:hover {
                transform: translateY(-5px) !important;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
                border-color: #007bff !important;
            }
            
            .main-product-image {
                width: 100% !important;
                height: 200px !important;
                margin-bottom: 15px !important;
                overflow: hidden !important;
                border-radius: 8px !important;
                background: #f8f9fa !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                border: 1px solid #eee !important;
            }
            
            .main-product-image img {
                max-width: 100% !important;
                max-height: 100% !important;
                object-fit: cover !important;
            }
            
            .main-product-name {
                font-size: 1.2rem !important;
                margin: 15px 0 !important;
                color: #333 !important;
                font-weight: 600 !important;
                line-height: 1.4 !important;
                min-height: 50px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            .main-product-price {
                font-size: 1.5rem !important;
                color: #dc3545 !important;
                font-weight: 700 !important;
                margin: 10px 0 !important;
            }
            
            .main-product-category {
                background: #007bff !important;
                color: white !important;
                padding: 5px 12px !important;
                border-radius: 20px !important;
                font-size: 0.85rem !important;
                text-transform: uppercase !important;
                margin: 10px 0 !important;
                display: inline-block !important;
                font-weight: 500 !important;
            }
            
            .main-product-button {
                background: #28a745 !important;
                color: white !important;
                border: none !important;
                padding: 12px 24px !important;
                border-radius: 8px !important;
                cursor: pointer !important;
                font-size: 1rem !important;
                margin-top: 15px !important;
                width: 100% !important;
                font-weight: 600 !important;
                transition: background 0.3s ease !important;
            }
            
            .main-product-button:hover {
                background: #218838 !important;
            }
            
            .main-product-card.hidden {
                display: none !important;
            }
        `;
        document.head.appendChild(style);
    }

    renderProducts() {
        const grid = document.getElementById('main-products-grid');
        if (!grid) {
            console.error('❌ Grid principal no encontrado');
            return;
        }

        grid.innerHTML = ''; // Limpiar

        this.products.forEach((product, index) => {
            const productCard = this.createPerfectProductCard(product, index);
            grid.appendChild(productCard);
        });

        // FORZAR VISIBILIDAD
        this.forceVisibility();

        console.log(`✅ ${this.products.length} productos renderizados en contenedor principal`);
    }

    createPerfectProductCard(product, index) {
        const card = document.createElement('div');
        card.className = 'main-product-card api-product';
        card.dataset.category = product.category_id || 'general';
        card.dataset.gender = product.gender || 'unisex';
        card.dataset.productId = product.id;

        // FORZAR VISIBILIDAD MÁXIMA
        card.style.cssText = `
            display: block !important;
            opacity: 1 !important;
            visibility: visible !important;
        `;

        // Imagen
        const imgContainer = document.createElement('div');
        imgContainer.className = 'main-product-image';

        if (product.image_url && product.image_url !== 'NULL') {
            const img = document.createElement('img');
            img.src = product.image_url;
            img.alt = product.name;
            img.onerror = () => {
                imgContainer.innerHTML = '<span style="color: #999; font-size: 14px;">Sin Imagen<br>Imagen no disponible</span>';
            };
            imgContainer.appendChild(img);
        } else {
            imgContainer.innerHTML = '<span style="color: #999; font-size: 14px;">Sin Imagen<br>Imagen no disponible</span>';
        }

        // Nombre
        const name = document.createElement('h3');
        name.className = 'main-product-name';
        name.textContent = product.name;

        // Precio
        const price = document.createElement('div');
        price.className = 'main-product-price';
        price.textContent = product.price ? `$${parseFloat(product.price).toFixed(2)}` : 'Precio no disponible';

        // Categoría
        const category = document.createElement('span');
        category.className = 'main-product-category';
        category.textContent = this.getCategoryName(product.category_id);

        // Botón
        const button = document.createElement('button');
        button.className = 'main-product-button';
        button.textContent = '🛒 Agregar al Carrito';
        button.onclick = () => {
            console.log('🛒 Producto agregado:', product.name);
            alert(`✅ ${product.name} agregado al carrito`);
        };

        card.appendChild(imgContainer);
        card.appendChild(name);
        card.appendChild(price);
        card.appendChild(category);
        card.appendChild(button);

        console.log(`✅ Producto ${index + 1} creado: ${product.name}`);

        return card;
    }

    setupFilters() {
        // Sistema de filtros para el contenedor principal
        console.log('🔗 Configurando sistema de filtros principal');
        
        // Crear función de filtro para productos principales
        window.filterMainProducts = (gender, category) => {
            console.log(`🎯 Filtrando productos principales: gender=${gender}, category=${category}`);
            
            const cards = document.querySelectorAll('.main-product-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const cardGender = card.dataset.gender;
                const cardCategory = card.dataset.category;
                
                let show = true;
                
                // Filtro por género
                if (gender !== 'all' && cardGender !== gender && cardGender !== 'unisex') {
                    show = false;
                }
                
                // Filtro por categoría
                if (category !== 'all' && cardCategory !== category) {
                    show = false;
                }
                
                if (show) {
                    card.classList.remove('hidden');
                    card.style.cssText = `
                        display: block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    `;
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none !important';
                }
            });
            
            console.log(`✅ Filtro aplicado: ${visibleCount} productos visibles`);
            
            // Scroll al contenedor después de filtrar
            setTimeout(() => {
                const container = document.getElementById('main-products-container');
                if (container && visibleCount > 0) {
                    container.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                }
            }, 100);
        };
        
        // Integrar con filtros existentes
        window.filterCleanProducts = window.filterMainProducts;
    }
                }
            });
            
            // Actualizar indicador
            const indicator = document.querySelector('.filter-indicator');
            if (indicator) {
                if (category === 'all' && gender === 'all') {
                    indicator.innerHTML = `✅ ${visibleCount} PRODUCTOS VISIBLES`;
                } else {
                    indicator.innerHTML = `🔍 ${visibleCount} PRODUCTOS FILTRADOS`;
                }
            }
            
            // Scroll al contenedor después del filtro
            const container = document.getElementById('clean-api-products');
            if (container && visibleCount > 0) {
                setTimeout(() => {
                    container.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                }, 200);
            }
            
            console.log(`✅ ${visibleCount} productos visibles después del filtro`);
        };
        
        // Mostrar todos los productos inicialmente
        window.filterCleanProducts('all', 'all');
        
        // CREAR BOTÓN DE EMERGENCIA PARA MOSTRAR CONTENEDOR
        const emergencyButton = document.createElement('button');
        emergencyButton.innerHTML = '🚨 VER PRODUCTOS API';
        emergencyButton.style.cssText = `
            position: fixed !important;
            bottom: 20px !important;
            right: 20px !important;
            background: #dc3545 !important;
            color: white !important;
            border: none !important;
            padding: 15px 25px !important;
            border-radius: 50px !important;
            font-size: 1.2rem !important;
            font-weight: bold !important;
            cursor: pointer !important;
            z-index: 10001 !important;
            box-shadow: 0 5px 20px rgba(0,0,0,0.4) !important;
            animation: pulse 2s infinite !important;
        `;
        
        emergencyButton.onclick = () => {
            const container = document.getElementById('clean-api-products');
            if (container) {
                container.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                container.style.background = '#fff3cd';
                setTimeout(() => {
                    container.style.background = '#f8f9fa';
                }, 2000);
            }
        };
        
        document.body.appendChild(emergencyButton);
        
        // Agregar animación de pulse
        const pulseStyle = document.createElement('style');
        pulseStyle.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.1); }
                100% { transform: scale(1); }
            }
        `;
        document.head.appendChild(pulseStyle);
    }
}

// Función global para integrar con el sistema existente
window.initCleanProducts = () => {
    console.log('🎯 Inicializando sistema de productos limpio...');
    window.cleanProductsLoader = new CleanProductsLoader();
};

// Inicializar automáticamente
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            window.initCleanProducts();
        }, 1000);
    });
} else {
    setTimeout(() => {
        window.initCleanProducts();
    }, 1000);
}
