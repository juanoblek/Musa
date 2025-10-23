// Sincronización de productos entre admin y index
// Este archivo se incluye en index.html para cargar productos desde localStorage

// Función para cargar productos desde localStorage
function loadProductsFromStorage() {
    const storedProducts = localStorage.getItem('products');
    if (storedProducts) {
        return JSON.parse(storedProducts);
    }
    return [];
}

// Función para renderizar productos dinámicamente
function renderProductsInIndex() {
    const products = loadProductsFromStorage();
    const productContainer = document.getElementById('products-container');
    const staticProducts = document.getElementById('static-products');
    
    console.log('renderProductsInIndex called');
    console.log('Products loaded:', products.length);
    console.log('Product container found:', !!productContainer);
    console.log('Static products found:', !!staticProducts);
    
    if (!productContainer) {
        console.log('Error: products-container not found');
        return;
    }
    
    // Si hay productos en localStorage, ocultar productos estáticos y mostrar dinámicos
    if (products.length > 0) {
        console.log('Rendering dynamic products');
        
        if (staticProducts) {
            staticProducts.style.display = 'none';
        }
        productContainer.style.display = 'flex';
        productContainer.style.flexWrap = 'wrap';
        
        // Limpiar contenedor
        productContainer.innerHTML = '';
        
        // Filtrar solo productos activos
        const activeProducts = products.filter(product => product.is_active);
        console.log('Active products:', activeProducts.length);
        
        activeProducts.forEach(product => {
            console.log('Creating card for product:', product.name);
            const productCard = createProductCard(product);
            productContainer.appendChild(productCard);
        });
        
        // Asegurar que el contenedor mantenga las clases Bootstrap
        productContainer.className = 'row g-3';
        productContainer.style.display = '';
    } else {
        console.log('No products found, showing static products');
        // Si no hay productos en localStorage, mostrar productos estáticos
        if (staticProducts) {
            staticProducts.style.display = '';
        }
        productContainer.style.display = 'none';
    }
    
    // Reinicializar filtros después de cargar productos
    setTimeout(() => {
        initializeProductFilters();
    }, 100);
}

// Función para crear una tarjeta de producto con diseño responsivo
function createProductCard(product) {
    const col = document.createElement('div');
    col.className = 'col-lg-4 col-md-6 col-sm-6 col-12 mb-4 product-card filter';
    col.setAttribute('data-gender', product.gender);
    col.setAttribute('data-category', product.category);
    col.setAttribute('data-filter', product.category);
    col.setAttribute('data-aos', 'zoom-in-up');
    col.setAttribute('data-aos-delay', '100');
    
    const totalStock = Array.isArray(product.sizes) ? 
        product.sizes.reduce((sum, size) => sum + size.stock, 0) : 
        product.stock;
    
    const formattedPrice = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(product.price);

    // Crear imágenes por color si existen
    const colorImages = Array.isArray(product.colors) ? 
        product.colors.reduce((acc, color) => {
            acc[color.name] = product.image; // Usar la imagen principal por defecto
            return acc;
        }, {}) : {};

    // Crear data-images string
    const dataImages = Object.keys(colorImages).length > 0 ? 
        `data-images='${JSON.stringify(colorImages)}'` : '';

    // Crear botones de tallas
    const sizeButtons = Array.isArray(product.sizes) ? 
        product.sizes.map(size => `
            <button type="button" 
                    class="btn btn-sm btn-outline-dark size-btn" 
                    data-size="${size.name}"
                    ${size.stock === 0 ? 'disabled' : ''}
                    title="${size.stock > 0 ? `${size.stock} disponibles` : 'Agotado'}">${size.name}</button>
        `).join('') : 
        '<button type="button" class="btn btn-sm btn-outline-dark size-btn" data-size="Única">Única</button>';

    // Crear botones de colores
    const colorButtons = Array.isArray(product.colors) ? 
        product.colors.map((color, index) => `
            <button type="button" 
                    class="btn color-btn rounded-circle ${index === 0 ? 'active' : ''}" 
                    data-color="${color.name}" 
                    style="background: ${color.code};"
                    ${color.stock === 0 ? 'disabled' : ''}
                    title="${color.name} - ${color.stock > 0 ? `${color.stock} disponibles` : 'Agotado'}"></button>
        `).join('') : 
        '<button type="button" class="btn color-btn rounded-circle active" data-color="Único" style="background: #666;"></button>';

    col.innerHTML = `
        <div class="card h-100 product-item hover-3d">
            <div class="card-img-wrap position-relative overflow-hidden">
                <!-- Badge con efecto neón -->
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge bg-primary neon-pulse">
                        ${totalStock > 0 ? `${totalStock} disponibles` : 'AGOTADO'}
                    </span>
                </div>
                
                <!-- Contenedor de imagen con hover 3D -->
                <div class="image-hover-wrapper">
                    ${window.generarMediaHTMLSincrono(product.image, {
                        class: 'card-img-top product-image glasseffect',
                        alt: product.name,
                        style: 'object-fit: cover;',
                        placeholder: 'images/clothes-1839935_1920.jpg'
                    })}
                    <div class="hover-3d-layer"></div>
                </div>
            </div>

            <div class="card-body position-relative">
                <!-- Título con efecto gradiente -->
                <h5 class="card-title text-gradient-primary">${product.name}</h5>
                
                <!-- Rating y contador de ventas -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="rating-group">
                        <div class="stars-pulse">
                            <i class="bi-star-fill reviews-icon"></i>
                            <i class="bi-star-fill reviews-icon"></i>
                            <i class="bi-star-fill reviews-icon"></i>
                            <i class="bi-star-fill reviews-icon"></i>
                            <i class="bi-star-fill reviews-icon"></i>
                        </div>
                        <small class="text-muted">Nuevo producto</small>
                    </div>
                    <div class="sales-counter">
                        <i class="fas fa-bolt text-warning"></i>
                        <span class="badge bg-dark">Disponible</span>
                    </div>
                </div>

                <!-- Selectores mejorados -->
                <div class="size-color-selector mb-4">
                    <!-- Tallas con stock -->
                    <div class="size-options d-flex gap-2 mb-3">
                        ${sizeButtons}
                    </div>

                    <!-- Colores -->
                    <div class="color-options d-flex gap-2">
                        ${colorButtons}
                    </div>
                </div>

                <!-- Precio con efecto de ahorro -->
                <div class="price-container mb-3">
                    <div class="d-flex align-items-end gap-2">
                        <span class="h4 text-primary mb-0">${formattedPrice}</span>
                    </div>
                    <div class="text-success small">
                        <i class="fas fa-tag"></i> ${product.description}
                    </div>
                </div>

                <!-- Botón mejorado con microinteracciones -->
                <button class="btn btn-hover-glow w-100 add-to-cart-btn py-3" 
                        data-id="${product.id}"
                        data-name="${product.name}" 
                        data-price="${product.price}"
                        data-image="${product.image}"
                        ${totalStock === 0 ? 'disabled' : ''}>
                    <span class="btn-content">
                        <i class="fas fa-cart-plus me-2"></i>
                        ${totalStock > 0 ? 'Añadir al Carrito' : 'Agotado'}
                    </span>
                    <div class="btn-glow"></div>
                </button>

                <!-- Sección de garantías eliminada -->
            </div>
        </div>
    `;
    
    return col;
}

// Función para agregar al carrito
function addToCart(productId, productName, price) {
    // Obtener el carrito actual del localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Verificar si el producto ya está en el carrito
    const existingProduct = cart.find(item => item.id === productId);
    
    if (existingProduct) {
        existingProduct.quantity += 1;
    } else {
        // Obtener el producto completo desde localStorage
        const products = loadProductsFromStorage();
        const product = products.find(p => p.id === productId);
        
        if (product) {
            cart.push({
                id: productId,
                name: productName,
                price: price,
                image: product.image,
                quantity: 1
            });
        }
    }
    
    // Guardar el carrito actualizado
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Mostrar notificación
    Swal.fire({
        icon: 'success',
        title: 'Producto agregado',
        text: `${productName} ha sido agregado al carrito`,
        showConfirmButton: false,
        timer: 1500,
        position: 'top-end',
        toast: true
    });
    
    // Actualizar contador del carrito si existe
    updateCartCounter();
}

// Función para actualizar contador del carrito
function updateCartCounter() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    
    const cartCounters = document.querySelectorAll('.cart-counter, .cart-count');
    cartCounters.forEach(counter => {
        counter.textContent = totalItems;
        counter.style.display = totalItems > 0 ? 'inline' : 'none';
    });
}

// Función para inicializar filtros de productos
function initializeProductFilters() {
    const filterButtons = document.querySelectorAll(".btn-category");
    const products = document.querySelectorAll(".product-card");

    filterButtons.forEach(button => {
        button.addEventListener("click", function() {
            // Reset filtros
            filterButtons.forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");
            
            // Aplicar filtro
            const filter = this.dataset.filter;
            products.forEach(product => {
                const shouldShow = filter === "all" || product.dataset.filter === filter;
                product.style.display = shouldShow ? "block" : "none";
            });
        });
    });
}

// Función para sincronizar categorías en el filtro del index.html
function syncCategoriesInFilter() {
    const storedCategories = localStorage.getItem('categories');
    
    if (!storedCategories) return;
    
    const categories = JSON.parse(storedCategories);
    
    // Buscar los dropdowns de COLECCIÓN MUSA y COLECCIÓN ARION por ID
    const musaDropdown = document.getElementById('musa-categories');
    const arionDropdown = document.getElementById('arion-categories');
    
    if (musaDropdown && arionDropdown) {
        // Crear categorías únicas para cada colección
        const musaCategories = categories.filter(cat => 
            cat.gender === 'mujer' || cat.gender === 'unisex'
        );
        const arionCategories = categories.filter(cat => 
            cat.gender === 'hombre' || cat.gender === 'unisex'
        );
        
        // Función para actualizar un dropdown
        function updateDropdown(dropdown, categoriesArray) {
            // Obtener categorías existentes
            const existingItems = Array.from(dropdown.querySelectorAll('.filter-item'));
            const existingFilters = existingItems.map(item => item.dataset.filter);
            
            // Agregar nuevas categorías que no existen
            categoriesArray.forEach(category => {
                if (!existingFilters.includes(category.filter)) {
                    const newItem = document.createElement('li');
                    newItem.innerHTML = `<a class="dropdown-item filter-item" data-filter="${category.filter}" href="#">${category.name}</a>`;
                    dropdown.appendChild(newItem);
                    
                    // Agregar event listener a la nueva categoría
                    const newLink = newItem.querySelector('.filter-item');
                    newLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        const filter = this.dataset.filter;
                        filterProducts(filter);
                    });
                }
            });
        }
        
        // Actualizar ambos dropdowns
        updateDropdown(musaDropdown, musaCategories);
        updateDropdown(arionDropdown, arionCategories);
        
        console.log('Categorías sincronizadas en el filtro');
    }
}

// Función para sincronizar categorías en los filtros horizontales
function syncCategoriesInHorizontalFilters() {
    const storedCategories = localStorage.getItem('categories');
    
    if (!storedCategories) return;
    
    const categories = JSON.parse(storedCategories);
    
    // Buscar los contenedores de categorías por género
    const hombreContainer = document.getElementById('categoria-hombre');
    const mujerContainer = document.getElementById('categoria-mujer');
    
    if (hombreContainer && mujerContainer) {
        // Filtrar categorías por género
        const categoriesHombre = categories.filter(cat => 
            cat.gender === 'hombre' || cat.gender === 'unisex'
        );
        const categoriesMujer = categories.filter(cat => 
            cat.gender === 'mujer' || cat.gender === 'unisex'
        );
        
        // Función para actualizar un contenedor de filtros
        function updateFilterContainer(container, categoriesArray) {
            // Obtener botones existentes
            const existingButtons = Array.from(container.querySelectorAll('.btn-category'));
            const existingFilters = existingButtons.map(btn => btn.dataset.filter);
            
            // Agregar nuevas categorías que no existen
            categoriesArray.forEach(category => {
                if (!existingFilters.includes(category.filter)) {
                    const newButton = document.createElement('button');
                    newButton.type = 'button';
                    newButton.className = 'btn btn-category';
                    newButton.dataset.filter = category.filter;
                    newButton.textContent = category.name;
                    
                    // Agregar event listener
                    newButton.addEventListener('click', function() {
                        // Remover active de otros botones
                        document.querySelectorAll('.btn-category').forEach(btn => {
                            btn.classList.remove('active');
                        });
                        // Activar este botón
                        this.classList.add('active');
                        
                        // Filtrar productos
                        filterProducts(this.dataset.filter);
                    });
                    
                    container.appendChild(newButton);
                }
            });
        }
        
        // Actualizar ambos contenedores
        updateFilterContainer(hombreContainer, categoriesHombre);
        updateFilterContainer(mujerContainer, categoriesMujer);
        
        console.log('Categorías sincronizadas en filtros horizontales');
    }
}

// Función para filtrar productos
function filterProducts(filter) {
    const products = document.querySelectorAll('.product-card');
    
    products.forEach(product => {
        const productFilter = product.dataset.filter || product.dataset.category;
        
        if (filter === 'all' || productFilter === filter) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
    
    // Actualizar botones activos
    document.querySelectorAll('.filter-item').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Marcar el filtro actual como activo
    document.querySelectorAll(`[data-filter="${filter}"]`).forEach(btn => {
        btn.classList.add('active');
    });
}

// Escuchar cambios en localStorage
window.addEventListener('storage', function(e) {
    console.log('Storage event received:', e.key);
    if (e.key === 'products') {
        console.log('Products updated via storage event');
        renderProductsInIndex();
    }
});

// Escuchar eventos personalizados del admin
window.addEventListener('productsUpdated', function(e) {
    console.log('Products updated via custom event');
    renderProductsInIndex();
});

// Escuchar cambios en categorías desde el admin
window.addEventListener('categoriesUpdated', function(e) {
    console.log('Categories updated via custom event');
    syncCategoriesInFilter();
    syncCategoriesInHorizontalFilters();
});

// Escuchar cambios en localStorage para categorías
window.addEventListener('storage', function(e) {
    if (e.key === 'categories') {
        console.log('Categories updated via storage event');
        syncCategoriesInFilter();
        syncCategoriesInHorizontalFilters();
    }
});

// Cargar productos cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    // Esperar un poco para que el DOM esté completamente cargado
    setTimeout(() => {
        renderProductsInIndex();
        updateCartCounter();
        addProductEvents(); // Agregar eventos del carrito
        syncCategoriesInFilter(); // Sincronizar categorías en el filtro
        syncCategoriesInHorizontalFilters(); // Sincronizar categorías en filtros horizontales
    }, 500);
});

// Función para agregar eventos del carrito a los productos dinámicos
function addProductEvents() {
    const container = document.getElementById('products-container');
    
    if (!container) return;
    
    // Agregar eventos de delegación para los productos dinámicos
    container.addEventListener('click', function(event) {
        const target = event.target;
        
        // Manejar clicks en botones de colores
        if (target.classList.contains('color-btn')) {
            event.preventDefault();
            const card = target.closest('.product-card');
            const mainImage = card.querySelector('.product-image');
            const color = target.dataset.color;
            
            // Cambiar color activo
            card.querySelectorAll('.color-btn').forEach(btn => btn.classList.remove('active'));
            target.classList.add('active');
            
            // Cambiar imagen si existe data-images
            if (mainImage.dataset.images) {
                try {
                    const images = JSON.parse(mainImage.dataset.images);
                    if (images[color]) {
                        mainImage.src = images[color];
                    }
                } catch (e) {
                    console.warn('Error al parsear imágenes:', e);
                }
            }
        }
        
        // Manejar clicks en botones de tallas
        if (target.classList.contains('size-btn')) {
            event.preventDefault();
            const card = target.closest('.product-card');
            
            // Cambiar talla activa
            card.querySelectorAll('.size-btn').forEach(btn => btn.classList.remove('active'));
            target.classList.add('active');
        }
        
        // Manejar clicks en botones de agregar al carrito
        if (target.classList.contains('add-to-cart-btn') || target.closest('.add-to-cart-btn')) {
            event.preventDefault();
            const button = target.classList.contains('add-to-cart-btn') ? 
                target : target.closest('.add-to-cart-btn');
            
            const card = button.closest('.product-card');
            const selectedSize = card.querySelector('.size-btn.active');
            const selectedColor = card.querySelector('.color-btn.active');
            const productImage = card.querySelector('.product-image');
            
            if (!selectedSize || !selectedColor) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Selección requerida',
                        text: 'Debes seleccionar talla y color',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert('Por favor selecciona talla y color');
                }
                return;
            }
            
            // Usar el carrito global si existe
            if (typeof window.carrito !== 'undefined') {
                const productData = {
                    id: button.dataset.id,
                    nombre: button.dataset.name,
                    precio: parseFloat(button.dataset.price),
                    imagen: productImage.src,
                    talla: selectedSize.dataset.size,
                    color: selectedColor.dataset.color,
                    cantidad: 1
                };
                
                window.carrito.agregarProducto(productData);
            } else {
                // Fallback si no hay carrito
                addToCart(button.dataset.id, button.dataset.name, parseFloat(button.dataset.price));
            }
        }
    });
}

// Exportar funciones para uso global
window.renderProductsInIndex = renderProductsInIndex;
window.loadProductsFromStorage = loadProductsFromStorage;
window.addToCart = addToCart;
window.updateCartCounter = updateCartCounter;
window.addProductEvents = addProductEvents;
window.syncCategoriesInFilter = syncCategoriesInFilter;
window.syncCategoriesInHorizontalFilters = syncCategoriesInHorizontalFilters;
window.filterProducts = filterProducts;
