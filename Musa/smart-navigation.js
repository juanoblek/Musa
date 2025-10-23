// Script para generar navegación inteligente basada en la base de datos
console.log('🏗️ Iniciando sistema de navegación inteligente...');

// Configuración de colecciones
const COLLECTIONS_CONFIG = {
    musa: {
        title: 'COLECCIÓN MUSA',
        gender: 'mujer',
        description: 'Elegancia femenina redefinida'
    },
    arion: {
        title: 'COLECCIÓN ARION', 
        gender: 'hombre',
        description: 'Estilo masculino contemporáneo'
    }
};

// Función para cargar categorías desde la base de datos
async function loadCategoriesFromDB() {
    try {
        console.log('📊 Cargando categorías desde la base de datos...');
        const response = await fetch('api/navigation-categories.php');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.categories) {
            console.log('✅ Categorías cargadas:', data.categories);
            return data.categories;
        } else {
            throw new Error(data.message || 'Error al cargar categorías');
        }
    } catch (error) {
        console.error('❌ Error cargando categorías:', error);
        return getFallbackCategories();
    }
}

// Categorías de respaldo si falla la conexión a BD
function getFallbackCategories() {
    return {
        mujer: [
            { name: 'Camisas', category: 'camisas' },
            { name: 'Chaquetas', category: 'chaquetas' },
            { name: 'Tops', category: 'tops' },
            { name: 'Accesorios', category: 'accesorios' }
        ],
        hombre: [
            { name: 'Camisas Ejecutivas', category: 'Camisas Ejecutivas' },
            { name: 'Pantalones', category: 'pantalones' },
            { name: 'Chaquetas', category: 'Chaqueta' },
            { name: 'Hoddies', category: 'Hoddies' }
        ]
    };
}

// Función para generar HTML del menú desplegable
function generateDropdownHTML(collection, categories) {
    const config = COLLECTIONS_CONFIG[collection];
    if (!config) return '';
    
    let html = `
        <li>
            <a class="dropdown-item nav-filter" href="#" 
               data-collection="${collection}" 
               data-category="all" 
               data-gender="${config.gender}">
                <i class="fas fa-th-large me-2"></i>Ver Todo
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
    `;
    
    if (categories && categories.length > 0) {
        categories.forEach(cat => {
            html += `
                <li>
                    <a class="dropdown-item nav-filter" href="#" 
                       data-collection="${collection}" 
                       data-category="${cat.category}" 
                       data-gender="${config.gender}">
                        <i class="fas fa-tag me-2"></i>${cat.name}
                    </a>
                </li>
            `;
        });
    }
    
    return html;
}

// Función para actualizar el HTML de navegación
function updateNavigationHTML(categoriesData) {
    console.log('🔄 Actualizando HTML de navegación...');
    
    // Actualizar COLECCIÓN MUSA
    const musaDropdown = document.querySelector('#coleccion-musa .dropdown-menu');
    if (musaDropdown) {
        const musaCategories = categoriesData.mujer || [];
        musaDropdown.innerHTML = generateDropdownHTML('musa', musaCategories);
    }
    
    // Actualizar COLECCIÓN ARION
    const arionDropdown = document.querySelector('#coleccion-arion .dropdown-menu');
    if (arionDropdown) {
        const arionCategories = categoriesData.hombre || [];
        arionDropdown.innerHTML = generateDropdownHTML('arion', arionCategories);
    }
    
    console.log('✅ Navegación actualizada correctamente');
}

// Función para configurar eventos de navegación
function setupNavigationEvents() {
    console.log('⚡ Configurando eventos de navegación...');
    
    // Configurar eventos para filtros de navegación
    document.addEventListener('click', function(event) {
        const filterLink = event.target.closest('.nav-filter');
        if (!filterLink) return;
        
        event.preventDefault();
        
        const collection = filterLink.dataset.collection;
        const category = filterLink.dataset.category;
        const gender = filterLink.dataset.gender;
        
        console.log(`🎯 Filtro seleccionado: ${collection} > ${category}`);
        
        // Remover clase active de todos los filtros
        document.querySelectorAll('.nav-filter').forEach(link => {
            link.classList.remove('active');
        });
        
        // Añadir clase active al filtro seleccionado
        filterLink.classList.add('active');
        
        // Aplicar filtro usando el sistema existente
        applyNavigationFilter(collection, category, gender);
        
        // Cerrar el dropdown en móvil
        const dropdown = filterLink.closest('.dropdown-menu');
        if (dropdown && window.innerWidth < 992) {
            const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.previousElementSibling);
            if (bsDropdown) bsDropdown.hide();
        }
    });
    
    console.log('✅ Eventos de navegación configurados');
}

// Función para aplicar filtros de navegación
function applyNavigationFilter(collection, category, gender) {
    console.log(`🔍 Aplicando filtro: ${collection} > ${category} (${gender})`);
    
    // Si tenemos el sistema de navegación existente, usarlo
    if (window.navigationManager && typeof window.navigationManager.applyFilters === 'function') {
        window.navigationManager.currentFilter = {
            collection: collection,
            category: category,
            gender: gender
        };
        window.navigationManager.applyFilters();
        return;
    }
    
    // Fallback: aplicar filtro directamente a los productos
    applyFilterDirectly(category, gender);
}

// Función de filtrado directo como fallback
function applyFilterDirectly(category, gender) {
    const products = document.querySelectorAll('.product-card, .card.h-100');
    let visibleCount = 0;
    
    products.forEach(product => {
        let shouldShow = true;
        
        // Filtrar por categoría
        if (category !== 'all') {
            const productCategory = product.dataset.category || '';
            shouldShow = shouldShow && productCategory.toLowerCase().includes(category.toLowerCase());
        }
        
        // Filtrar por género
        if (gender && gender !== 'all') {
            const productGender = product.dataset.gender || '';
            shouldShow = shouldShow && (
                productGender.toLowerCase().includes(gender.toLowerCase()) ||
                productGender.toLowerCase() === 'unisex'
            );
        }
        
        // Mostrar/ocultar producto
        if (shouldShow) {
            product.style.display = '';
            product.closest('.col-lg-3, .col-md-4, .col-sm-6, .col')?.style.setProperty('display', '', 'important');
            visibleCount++;
        } else {
            product.style.display = 'none';
            product.closest('.col-lg-3, .col-md-4, .col-sm-6, .col')?.style.setProperty('display', 'none', 'important');
        }
    });
    
    console.log(`✅ Filtro aplicado: ${visibleCount} productos visibles`);
}

// Función para limpiar y organizar los botones de categorías existentes
function cleanupCategoryButtons() {
    const categoriesContainer = document.querySelector('.categories-container');
    if (categoriesContainer) {
        console.log('🧹 Limpiando botones de categorías antiguos...');
        categoriesContainer.style.display = 'none'; // Ocultar pero no eliminar por compatibilidad
    }
}

// Función de inicialización principal
async function initializeSmartNavigation() {
    console.log('🚀 Inicializando navegación inteligente...');
    
    try {
        // 1. Cargar categorías desde la base de datos
        const categoriesData = await loadCategoriesFromDB();
        
        // 2. Actualizar HTML de navegación
        updateNavigationHTML(categoriesData);
        
        // 3. Configurar eventos
        setupNavigationEvents();
        
        // 4. Limpiar elementos antiguos
        cleanupCategoryButtons();
        
        console.log('✅ Navegación inteligente inicializada correctamente');
        
    } catch (error) {
        console.error('❌ Error inicializando navegación:', error);
        
        // Usar categorías de respaldo
        const fallbackCategories = getFallbackCategories();
        updateNavigationHTML(fallbackCategories);
        setupNavigationEvents();
        cleanupCategoryButtons();
        
        console.log('⚠️ Navegación inicializada con categorías de respaldo');
    }
}

// Función para reinicializar cuando se detecten cambios
function reinitializeIfNeeded() {
    // Verificar si los dropdowns existen
    const musaDropdown = document.querySelector('#coleccion-musa .dropdown-menu');
    const arionDropdown = document.querySelector('#coleccion-arion .dropdown-menu');
    
    if (musaDropdown && arionDropdown) {
        // Verificar si aún contienen "Cargando..."
        const musaContent = musaDropdown.innerHTML;
        const arionContent = arionDropdown.innerHTML;
        
        if (musaContent.includes('Cargando...') || arionContent.includes('Cargando...')) {
            console.log('🔄 Reinicializando navegación...');
            initializeSmartNavigation();
        }
    }
}

// Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSmartNavigation);
} else {
    initializeSmartNavigation();
}

// Observer para reinicializar si es necesario
const navObserver = new MutationObserver(() => {
    setTimeout(reinitializeIfNeeded, 1000);
});

navObserver.observe(document.body, {
    childList: true,
    subtree: true
});

console.log('🎯 Sistema de navegación inteligente cargado');