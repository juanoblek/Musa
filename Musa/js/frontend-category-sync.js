/**
 * ========================================
 * SISTEMA DE SINCRONIZACIÓN DE CATEGORÍAS
 * Frontend <-> Panel Administrativo
 * ========================================
 */

class CategorySyncSystem {
    constructor() {
        this.categories = [];
        this.isInitialized = false;
        this.init();
    }

    async init() {
        console.log('🔄 Inicializando sistema de sincronización de categorías...');
        
        // Cargar categorías desde MySQL
        await this.loadCategoriesFromServer();
        
        // Renderizar filtros de categorías
        this.renderCategoryFilters();
        
        // Configurar eventos
        this.setupEventListeners();
        
        // Configurar sincronización automática
        this.setupAutoSync();
        
        this.isInitialized = true;
        console.log('✅ Sistema de sincronización de categorías iniciado');
    }

    async loadCategoriesFromServer() {
        try {
            console.log('📡 [FRONTEND] Cargando categorías desde el servidor...');
            
            // Detectar puerto automáticamente como en el panel limpio
            const currentPort = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
            const baseUrl = `${window.location.protocol}//${window.location.hostname}${currentPort !== '80' && currentPort !== '443' ? ':' + currentPort : ''}`;
            const mainUrl = `${baseUrl}/get_categorias.php`;
            
            console.log('🔗 [FRONTEND] URL automática detectada:', mainUrl);
            
            // Intentar URLs en orden de prioridad
            const urls = [
                mainUrl,
                './get_categorias.php',
                'get_categorias.php'
            ];
            
            let response = null;
            let lastError = null;
            
            for (const url of urls) {
                try {
                    console.log(`🔍 [FRONTEND] Probando URL: ${url}`);
                    response = await fetch(url);
                    if (response.ok) {
                        console.log(`✅ Conectado exitosamente a: ${url}`);
                        break;
                    }
                } catch (error) {
                    lastError = error;
                    console.log(`❌ Fallo en ${url}: ${error.message}`);
                    continue;
                }
            }
            
            if (!response || !response.ok) {
                throw lastError || new Error('No se pudo conectar a ninguna URL');
            }
            
            const data = await response.json();
            console.log('📊 [FRONTEND] Datos recibidos:', data);
            
            let categories = [];
            if (data.success && data.categories && Array.isArray(data.categories)) {
                categories = data.categories;
                console.log(`✅ [FRONTEND] ${categories.length} categorías cargadas desde MySQL (formato success)`);
            } else if (data.categorias && Array.isArray(data.categorias)) {
                categories = data.categorias;
                console.log(`✅ [FRONTEND] ${categories.length} categorías cargadas desde MySQL (formato alternativo)`);
            } else if (Array.isArray(data)) {
                categories = data;
                console.log(`✅ [FRONTEND] ${categories.length} categorías cargadas desde MySQL (formato array)`);
            } else {
                throw new Error('Formato de respuesta no válido');
            }
            
            this.categories = categories;
            
            // Guardar en localStorage como respaldo
            localStorage.setItem('frontend_categories', JSON.stringify(this.categories));
            
            return true;
        } catch (error) {
            console.warn('⚠️ Error cargando desde servidor, usando localStorage:', error);
            
            // Fallback a localStorage
            const storedCategories = localStorage.getItem('frontend_categories') || localStorage.getItem('categories');
            if (storedCategories) {
                this.categories = JSON.parse(storedCategories);
                console.log(`📦 ${this.categories.length} categorías cargadas desde localStorage`);
                return true;
            }
            
            return false;
        }
    }

    renderCategoryFilters() {
        console.log('🎨 Renderizando filtros de categorías...');
        
        const categoriaHombreContainer = document.getElementById('categoria-hombre');
        const categoriaMujerContainer = document.getElementById('categoria-mujer');
        
        if (!categoriaHombreContainer || !categoriaMujerContainer) {
            console.warn('⚠️ Contenedores de categorías no encontrados');
            return;
        }

        // Limpiar contenedores existentes (mantener solo el botón "Todas")
        this.clearCategoryContainer(categoriaHombreContainer);
        this.clearCategoryContainer(categoriaMujerContainer);

        // Filtrar y renderizar categorías por género
        const categoriasHombre = this.categories.filter(cat => 
            cat.activo && (cat.gender === 'hombre' || cat.gender === 'general' || cat.gender === 'unisex')
        );
        
        const categoriasMujer = this.categories.filter(cat => 
            cat.activo && (cat.gender === 'mujer' || cat.gender === 'general' || cat.gender === 'unisex')
        );

        // Renderizar categorías de hombre
        categoriasHombre.forEach(category => {
            this.createCategoryButton(category, categoriaHombreContainer, 'hombre');
        });

        // Renderizar categorías de mujer
        categoriasMujer.forEach(category => {
            this.createCategoryButton(category, categoriaMujerContainer, 'mujer');
        });

        console.log(`✅ Filtros renderizados: ${categoriasHombre.length} hombre, ${categoriasMujer.length} mujer`);
    }

    clearCategoryContainer(container) {
        // Mantener solo los elementos que no sean botones de categoría dinámicos
        const dynamicButtons = container.querySelectorAll('.btn-category:not([data-filter="all"])');
        dynamicButtons.forEach(btn => btn.remove());
    }

    createCategoryButton(category, container, gender) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-category';
        button.dataset.filter = this.generateCategoryFilter(category, gender);
        button.dataset.categoryId = category.id;
        button.dataset.categoryName = category.nombre || category.name;
        button.textContent = category.nombre || category.name;

        // Añadir evento de clic
        button.addEventListener('click', (e) => {
            this.handleCategoryClick(e, category);
        });

        container.appendChild(button);
    }

    generateCategoryFilter(category, gender) {
        const name = (category.nombre || category.name || '').toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[áàäâ]/g, 'a')
            .replace(/[éèëê]/g, 'e')
            .replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o')
            .replace(/[úùüû]/g, 'u')
            .replace(/[ñ]/g, 'n')
            .replace(/[^a-z0-9-]/g, '');
        
        return `${name}-${gender}`;
    }

    handleCategoryClick(event, category) {
        // Actualizar UI
        document.querySelectorAll('.btn-category').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');

        // Obtener género activo
        const activeGender = document.querySelector('.btn-gender.active')?.dataset.gender || 'all';
        
        // Filtrar productos
        this.filterProductsByCategory(category, activeGender);
        
        console.log(`🎯 Categoría seleccionada: ${category.nombre || category.name} (${activeGender})`);
    }

    filterProductsByCategory(category, gender) {
        const products = document.querySelectorAll('.product-card');
        
        products.forEach(product => {
            const productGender = product.dataset.gender;
            const productCategoryId = product.dataset.categoryId;
            const productCategory = product.dataset.category;
            
            // Verificar si el producto debe mostrarse
            const matchesGender = gender === 'all' || productGender === gender;
            const matchesCategory = 
                productCategoryId === category.id.toString() ||
                productCategory === (category.nombre || category.name).toLowerCase() ||
                productCategory === this.generateCategoryFilter(category, gender);
            
            if (matchesGender && matchesCategory) {
                this.showProduct(product);
            } else {
                this.hideProduct(product);
            }
        });
    }

    showProduct(product) {
        product.style.display = 'block';
        product.classList.remove('filtering-out');
        product.classList.add('filtering-in');
    }

    hideProduct(product) {
        product.classList.remove('filtering-in');
        product.classList.add('filtering-out');
        setTimeout(() => {
            product.style.display = 'none';
        }, 300);
    }

    setupEventListeners() {
        // Escuchar eventos de sincronización del panel administrativo
        window.addEventListener('categoriesUpdated', () => {
            console.log('🔄 Categorías actualizadas desde panel administrativo');
            this.loadCategoriesFromServer().then(() => {
                this.renderCategoryFilters();
            });
        });

        // Escuchar cambios en localStorage
        window.addEventListener('storage', (event) => {
            if (event.key === 'categories' || event.key === 'frontend_categories') {
                console.log('🔄 Categorías actualizadas en localStorage');
                this.loadCategoriesFromServer().then(() => {
                    this.renderCategoryFilters();
                });
            }
        });

        // Mantener funcionalidad existente del botón "Todas"
        const todasButton = document.querySelector('[data-filter="all"]');
        if (todasButton && !todasButton.hasCustomListener) {
            todasButton.addEventListener('click', () => {
                document.querySelectorAll('.product-card').forEach(product => {
                    this.showProduct(product);
                });
            });
            todasButton.hasCustomListener = true;
        }
    }

    setupAutoSync() {
        // Sincronizar cada 30 segundos
        setInterval(async () => {
            const wasUpdated = await this.loadCategoriesFromServer();
            if (wasUpdated && this.hasChanges()) {
                console.log('🔄 Categorías sincronizadas automáticamente');
                this.renderCategoryFilters();
            }
        }, 30000);

        // Verificar cambios cuando la ventana recupera el foco
        window.addEventListener('focus', async () => {
            const wasUpdated = await this.loadCategoriesFromServer();
            if (wasUpdated && this.hasChanges()) {
                console.log('🔄 Categorías sincronizadas al recuperar foco');
                this.renderCategoryFilters();
            }
        });
    }

    hasChanges() {
        const stored = localStorage.getItem('frontend_categories');
        if (!stored) return true;
        
        const storedCategories = JSON.parse(stored);
        return JSON.stringify(storedCategories) !== JSON.stringify(this.categories);
    }

    // Método público para forzar sincronización
    async forcSync() {
        console.log('🔄 Forzando sincronización de categorías...');
        await this.loadCategoriesFromServer();
        this.renderCategoryFilters();
        console.log('✅ Sincronización forzada completada');
    }

    // Método público para obtener categorías
    getCategories() {
        return this.categories;
    }

    // Método público para obtener categoría por ID
    getCategoryById(id) {
        return this.categories.find(cat => cat.id.toString() === id.toString());
    }
}

// Inicializar sistema cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    // Esperar un poco para que otros scripts se carguen
    setTimeout(() => {
        window.categorySyncSystem = new CategorySyncSystem();
    }, 1000);
});

// Hacer disponible globalmente para debugging
window.CategorySyncSystem = CategorySyncSystem;

console.log('📦 Sistema de sincronización de categorías cargado');
