// Script para corregir todos los errores del buscador y filtros
console.log('🔧 Corrigiendo errores del buscador...');

// 1. Función normalizeText si no existe
if (typeof normalizeText === 'undefined') {
    window.normalizeText = function(text) {
        if (!text) return '';
        return text.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^\w\s]/gi, '');
    };
}

// 2. Función searchProducts corregida
window.searchProducts = function() {
    console.log('🔍 Ejecutando búsqueda...');
    
    const searchInput = document.getElementById('search-input');
    if (!searchInput) {
        console.error('❌ Input de búsqueda no encontrado');
        return;
    }
    
    const term = normalizeText(searchInput.value);
    if (term.length < 2) {
        // Mostrar todos los productos si el término es muy corto
        document.querySelectorAll('.product-card').forEach(product => {
            product.style.display = 'block';
        });
        return;
    }
    
    const products = document.querySelectorAll('.product-card');
    let matchCount = 0;
    
    products.forEach(product => {
        const nameElement = product.querySelector('.card-title');
        const name = nameElement ? normalizeText(nameElement.textContent) : '';
        const category = product.dataset.category || '';
        const gender = product.dataset.gender || '';
        
        const match = name.includes(term) || 
                     normalizeText(category).includes(term) || 
                     normalizeText(gender).includes(term);
        
        if (match) {
            product.style.display = 'block';
            matchCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    console.log(`✅ Búsqueda completada: ${matchCount} productos encontrados`);
};

// 3. Función applyFilter corregida
window.applyFilter = function(filter) {
    console.log('🏷️ Aplicando filtro:', filter);
    
    const products = document.querySelectorAll('.product-card');
    let matchCount = 0;
    
    products.forEach(product => {
        const category = product.dataset.category || '';
        const gender = product.dataset.gender || '';
        
        let match = false;
        
        if (filter === 'all' || filter === 'todos') {
            match = true;
        } else if (filter === 'mujer' || filter === 'hombre' || filter === 'unisex') {
            match = gender === filter;
        } else {
            match = category === filter;
        }
        
        if (match) {
            product.style.display = 'block';
            matchCount++;
        } else {
            product.style.display = 'none';
        }
    });
    
    console.log(`✅ Filtro aplicado: ${matchCount} productos mostrados`);
};

// 4. Función filterProducts si existe el input productSearch
const productSearchInput = document.getElementById('productSearch');
if (productSearchInput) {
    window.filterProducts = function() {
        const term = normalizeText(productSearchInput.value);
        const products = document.querySelectorAll('.product-card');
        
        products.forEach(product => {
            const nameElement = product.querySelector('.card-title');
            const name = nameElement ? normalizeText(nameElement.textContent) : '';
            
            if (name.includes(term) || term === '') {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    };
}

// 5. Agregar listeners para Enter en los inputs de búsqueda
const searchInputs = document.querySelectorAll('#search-input, #productSearch');
searchInputs.forEach(input => {
    if (input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.id === 'search-input') {
                    searchProducts();
                } else if (this.id === 'productSearch' && typeof filterProducts === 'function') {
                    filterProducts();
                }
            }
        });
    }
});

// 6. Configurar botones de filtro
document.addEventListener('DOMContentLoaded', function() {
    // Configurar dropdown items
    document.querySelectorAll('.dropdown-item[data-filter]').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            applyFilter(filter);
            
            // Cerrar dropdown en móvil
            if (window.innerWidth < 992) {
                const dropdownMenu = this.closest('.dropdown-menu');
                if (dropdownMenu) {
                    const dropdown = dropdownMenu.previousElementSibling;
                    if (dropdown && bootstrap && bootstrap.Dropdown) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(dropdown);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                }
            }
        });
    });
    
    // Configurar botón de búsqueda
    const searchButton = document.querySelector('.btn-search');
    if (searchButton) {
        searchButton.addEventListener('click', function(e) {
            e.preventDefault();
            searchProducts();
        });
    }
});

console.log('✅ Sistema de búsqueda y filtros corregido');