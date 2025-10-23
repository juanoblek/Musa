// Integración entre navegación inteligente y sistema de búsqueda
console.log('🔗 Integrando navegación con sistema de búsqueda...');

// Función para sincronizar filtros de navegación con búsqueda
function syncNavigationWithSearch() {
    // Escuchar eventos de filtrado de navegación
    document.addEventListener('navigationFilterApplied', function(event) {
        const { collection, category, gender } = event.detail;
        console.log(`🎯 Filtro de navegación aplicado: ${collection} > ${category} (${gender})`);
        
        // Aplicar filtro usando nuestro sistema de búsqueda mejorado
        if (window.applyFilter) {
            if (category !== 'all') {
                window.applyFilter('category', category);
            }
            
            if (gender && gender !== 'all') {
                window.applyFilter('gender', gender);
            }
        }
        
        // Limpiar búsqueda actual si existe
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.value = '';
        }
    });
    
    // Actualizar función de aplicar filtro de navegación para disparar evento
    if (window.applyNavigationFilter) {
        const originalApplyNavigationFilter = window.applyNavigationFilter;
        
        window.applyNavigationFilter = function(collection, category, gender) {
            originalApplyNavigationFilter(collection, category, gender);
            
            // Disparar evento personalizado
            document.dispatchEvent(new CustomEvent('navigationFilterApplied', {
                detail: { collection, category, gender }
            }));
        };
    }
}

// Función para mejorar la experiencia de usuario
function enhanceUserExperience() {
    // Añadir indicador visual de filtro activo
    document.addEventListener('click', function(event) {
        const filterLink = event.target.closest('.nav-filter');
        if (!filterLink) return;
        
        // Actualizar título de la página/sección
        const collection = filterLink.dataset.collection;
        const category = filterLink.dataset.category;
        
        const titleElement = document.querySelector('h1, .section-title, #productos-title');
        if (titleElement) {
            let newTitle = '';
            
            if (category === 'all') {
                newTitle = collection === 'musa' ? 'Colección Musa - Toda la línea femenina' : 'Colección Arion - Toda la línea masculina';
            } else {
                const categoryName = filterLink.textContent.trim();
                newTitle = `${categoryName} - ${collection === 'musa' ? 'Colección Musa' : 'Colección Arion'}`;
            }
            
            titleElement.textContent = newTitle;
        }
        
        // Scroll suave a la sección de productos
        const productsSection = document.querySelector('#prendas-exclusivas, #productos');
        if (productsSection) {
            setTimeout(() => {
                productsSection.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start' 
                });
            }, 300);
        }
    });
}

// Función para añadir breadcrumbs dinámicos
function addBreadcrumbs() {
    const breadcrumbContainer = document.querySelector('.breadcrumb-container');
    if (!breadcrumbContainer) {
        // Crear contenedor de breadcrumbs si no existe
        const navbar = document.querySelector('.navbar');
        if (navbar) {
            const breadcrumbDiv = document.createElement('div');
            breadcrumbDiv.className = 'breadcrumb-container bg-light py-2';
            breadcrumbDiv.innerHTML = `
                <div class="container">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" id="dynamic-breadcrumb">
                            <li class="breadcrumb-item"><a href="#" onclick="clearAllFilters()">Inicio</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Todos los productos</li>
                        </ol>
                    </nav>
                </div>
            `;
            navbar.parentNode.insertBefore(breadcrumbDiv, navbar.nextSibling);
        }
    }
    
    // Actualizar breadcrumb cuando se aplique un filtro
    document.addEventListener('navigationFilterApplied', function(event) {
        const { collection, category } = event.detail;
        const breadcrumb = document.getElementById('dynamic-breadcrumb');
        
        if (breadcrumb) {
            let breadcrumbHTML = '<li class="breadcrumb-item"><a href="#" onclick="clearAllFilters()">Inicio</a></li>';
            
            if (collection) {
                const collectionName = collection === 'musa' ? 'Colección Musa' : 'Colección Arion';
                breadcrumbHTML += `<li class="breadcrumb-item"><a href="#" onclick="filterByCollection('${collection}')">${collectionName}</a></li>`;
            }
            
            if (category && category !== 'all') {
                breadcrumbHTML += `<li class="breadcrumb-item active" aria-current="page">${category}</li>`;
            }
            
            breadcrumb.innerHTML = breadcrumbHTML;
        }
    });
}

// Función para limpiar todos los filtros
window.clearAllFilters = function() {
    console.log('🧹 Limpiando todos los filtros...');
    
    // Limpiar búsqueda
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
    }
    
    // Mostrar todos los productos
    const products = document.querySelectorAll('.product-card, .card.h-100');
    products.forEach(product => {
        product.style.display = '';
        product.closest('.col-lg-3, .col-md-4, .col-sm-6, .col')?.style.setProperty('display', '', 'important');
    });
    
    // Remover clases active
    document.querySelectorAll('.nav-filter').forEach(link => {
        link.classList.remove('active');
    });
    
    // Actualizar breadcrumb
    const breadcrumb = document.getElementById('dynamic-breadcrumb');
    if (breadcrumb) {
        breadcrumb.innerHTML = '<li class="breadcrumb-item active" aria-current="page">Todos los productos</li>';
    }
    
    console.log('✅ Todos los filtros limpiados');
};

// Función para filtrar por colección
window.filterByCollection = function(collection) {
    const collectionDropdown = document.querySelector(`#coleccion-${collection} .nav-filter[data-category="all"]`);
    if (collectionDropdown) {
        collectionDropdown.click();
    }
};

// Función para mejorar la accesibilidad
function enhanceAccessibility() {
    // Añadir aria-labels y mejores indicadores
    document.querySelectorAll('.nav-filter').forEach(link => {
        const collection = link.dataset.collection;
        const category = link.dataset.category;
        
        link.setAttribute('aria-label', `Filtrar por ${category} en ${collection}`);
        link.setAttribute('role', 'button');
    });
    
    // Manejar navegación por teclado
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            // Cerrar dropdowns abiertos
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                const dropdown = bootstrap.Dropdown.getInstance(toggle);
                if (dropdown) dropdown.hide();
            });
        }
    });
}

// Inicialización
function initializeIntegration() {
    console.log('🚀 Inicializando integración navegación-búsqueda...');
    
    syncNavigationWithSearch();
    enhanceUserExperience();
    addBreadcrumbs();
    enhanceAccessibility();
    
    console.log('✅ Integración completada');
}

// Inicializar cuando esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeIntegration);
} else {
    initializeIntegration();
}

console.log('🎯 Sistema de integración navegación-búsqueda cargado');