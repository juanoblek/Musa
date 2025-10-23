// Script para corregir los errores del buscador y hacer que muestre los resultados
console.log('🔧 Aplicando fix completo del buscador...');

// 1. Fix para el error de navigation-manager.js
if (window.NavigationManager) {
    const originalApplyFilters = window.NavigationManager.prototype.applyFilters;
    
    window.NavigationManager.prototype.applyFilters = async function() {
        try {
            // Aplicar filtros de manera segura
            console.log('🔍 Aplicando filtros de manera segura...');
            
            // Obtener productos filtrados
            const response = await fetch(`api/get-products.php?${new URLSearchParams(this.getFilterParams())}`);
            const data = await response.json();
            
            if (data.success && data.productos) {
                console.log(`✅ Filtros aplicados: ${data.productos.length} productos encontrados`);
                
                // Actualizar información de resultados
                this.updateResultsInfo(data.productos.length, this.currentFilter);
                
                // Disparar evento para que el sistema principal actualice
                document.dispatchEvent(new CustomEvent('productsFiltered', {
                    detail: { productos: data.productos }
                }));
                
                return data.productos;
            } else {
                throw new Error(data.message || 'Error cargando productos');
            }
        } catch (error) {
            console.error('❌ Error en applyFilters:', error);
            return [];
        }
    };
}

// 2. Fix para la función de búsqueda para que funcione con el sistema actual
window.searchProductsFixed = function() {
    console.log('🔍 Ejecutando búsqueda corregida...');
    
    const searchInput = document.getElementById('search-input');
    if (!searchInput) {
        console.error('❌ Input de búsqueda no encontrado');
        return;
    }
    
    const searchTerm = searchInput.value.toLowerCase().trim();
    console.log(`📝 Término de búsqueda: "${searchTerm}"`);
    
    // Buscar todas las tarjetas de productos en cualquier contenedor
    const productSelectors = [
        '.product-card',
        '.card.product-card', 
        '[data-product-name]',
        '.card.h-100'
    ];
    
    let allProducts = [];
    productSelectors.forEach(selector => {
        const products = document.querySelectorAll(selector);
        products.forEach(product => {
            if (!allProducts.includes(product)) {
                allProducts.push(product);
            }
        });
    });
    
    console.log(`🎯 Productos encontrados en DOM: ${allProducts.length}`);
    
    if (allProducts.length === 0) {
        console.log('⚠️ No hay productos en el DOM, esperando...');
        setTimeout(() => window.searchProductsFixed(), 500);
        return;
    }
    
    let visibleCount = 0;
    
    allProducts.forEach((product, index) => {
        // Buscar texto del producto en diferentes lugares
        const titleElement = product.querySelector('.card-title, h5, .product-name');
        const title = titleElement ? titleElement.textContent.toLowerCase() : '';
        
        const descElement = product.querySelector('.card-text, .product-description');
        const description = descElement ? descElement.textContent.toLowerCase() : '';
        
        const productName = product.dataset.productName ? product.dataset.productName.toLowerCase() : '';
        
        // Combinar todos los textos
        const searchableText = `${title} ${description} ${productName}`.toLowerCase();
        
        // Determinar si coincide
        const matches = searchTerm === '' || searchableText.includes(searchTerm);
        
        // Mostrar/ocultar producto
        if (matches) {
            product.style.display = '';
            product.closest('.col-lg-3, .col-md-4, .col-sm-6, .col')?.style.setProperty('display', '', 'important');
            visibleCount++;
        } else {
            product.style.display = 'none';
            product.closest('.col-lg-3, .col-md-4, .col-sm-6, .col')?.style.setProperty('display', 'none', 'important');
        }
    });
    
    console.log(`✅ Búsqueda completada: ${visibleCount} productos visibles de ${allProducts.length} totales`);
    
    // Mostrar mensaje si no hay resultados
    if (visibleCount === 0 && searchTerm !== '') {
        showNoResultsMessage();
    } else {
        hideNoResultsMessage();
    }
    
    return visibleCount;
};

// 3. Función para mostrar mensaje de "sin resultados"
function showNoResultsMessage() {
    let noResultsDiv = document.getElementById('no-results-message');
    
    if (!noResultsDiv) {
        // Buscar el contenedor principal de productos
        const containers = [
            document.getElementById('products-container-dynamic'),
            document.getElementById('productos-container'),
            document.querySelector('#prendas-exclusivas .row'),
            document.querySelector('.row.g-4')
        ];
        
        const container = containers.find(c => c !== null);
        
        if (container) {
            noResultsDiv = document.createElement('div');
            noResultsDiv.id = 'no-results-message';
            noResultsDiv.className = 'col-12 text-center py-5';
            noResultsDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <h4>No se encontraron productos</h4>
                    <p>No hay productos que coincidan con tu búsqueda.</p>
                </div>
            `;
            container.appendChild(noResultsDiv);
        }
    }
    
    if (noResultsDiv) {
        noResultsDiv.style.display = 'block';
    }
}

// 4. Función para ocultar mensaje de "sin resultados"
function hideNoResultsMessage() {
    const noResultsDiv = document.getElementById('no-results-message');
    if (noResultsDiv) {
        noResultsDiv.style.display = 'none';
    }
}

// 5. Reemplazar la función searchProducts existente
window.searchProducts = window.searchProductsFixed;

// 6. Función applyFilter corregida
window.applyFilter = function(filterType, filterValue) {
    console.log(`🎯 Aplicando filtro: ${filterType} = ${filterValue}`);
    
    // Buscar todas las tarjetas de productos
    const allProducts = document.querySelectorAll('.product-card, .card.h-100');
    let visibleCount = 0;
    
    allProducts.forEach(product => {
        let shouldShow = true;
        
        if (filterType === 'category' && filterValue !== 'all') {
            const productCategory = product.dataset.category || '';
            shouldShow = productCategory.toLowerCase().includes(filterValue.toLowerCase());
        } else if (filterType === 'gender' && filterValue !== 'all') {
            const productGender = product.dataset.gender || '';
            shouldShow = productGender.toLowerCase().includes(filterValue.toLowerCase());
        }
        
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
    
    // Después de filtrar, aplicar también la búsqueda actual si existe
    const searchInput = document.getElementById('search-input');
    if (searchInput && searchInput.value.trim()) {
        setTimeout(() => window.searchProducts(), 100);
    }
    
    return visibleCount;
};

// 7. Configurar eventos de búsqueda
function setupSearchEvents() {
    const searchInput = document.getElementById('search-input');
    
    if (searchInput) {
        // Remover eventos anteriores
        searchInput.removeEventListener('input', window.searchProducts);
        searchInput.removeEventListener('keyup', window.searchProducts);
        
        // Añadir eventos optimizados
        let searchTimeout;
        
        const doSearch = () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.searchProducts();
            }, 300);
        };
        
        searchInput.addEventListener('input', doSearch);
        searchInput.addEventListener('keyup', doSearch);
        
        console.log('✅ Eventos de búsqueda configurados correctamente');
    } else {
        console.log('⚠️ Input de búsqueda no encontrado, reintentando...');
        setTimeout(setupSearchEvents, 1000);
    }
}

// 8. Inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupSearchEvents);
} else {
    setupSearchEvents();
}

// 9. También configurar cuando se detecten nuevos productos
const observer = new MutationObserver(() => {
    setupSearchEvents();
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

console.log('✅ Fix completo del buscador aplicado');