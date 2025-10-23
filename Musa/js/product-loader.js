class ProductLoader {
    constructor() {
        this.productos = [];
        this.categoriaActual = 'todas';
        this.ordenActual = 'nombre-asc';
        this.terminoBusqueda = '';
        this.productsContainer = document.getElementById('dynamic-products-grid') || 
                                document.getElementById('productos-grid') || 
                                document.querySelector('.grid-productos');
        this.cartCount = 0;
        this.cartItems = [];
        
        this.loadCart();
        this.updateCartUI();
    }
    
    async init() {
        console.log('🚀 ProductLoader iniciado');
        
        // Esperar un poco para que mockApi se inicialice (reducido)
        await new Promise(resolve => setTimeout(resolve, 50));
        
        await this.cargarProductos();
        this.setupEventListeners();
        this.renderProducts();
    }
    
    async cargarProductos() {
        try {
            console.log('📡 Intentando cargar productos desde la API...');
            
            // URLs a intentar (en orden de prioridad)
            const apiUrls = [
                'get_productos_frontend.php',  // ⭐ NUEVO: Endpoint principal frontend con formato correcto
                'get_productos_admin.php',     // Endpoint administrativo
                'php/get_productos.php',       // Endpoint legacy
                'php/productos-mysql.php',     // Script PHP directo a MySQL
                'productos-mysql.php',         // Script PHP directo a MySQL (ruta alternativa)
                'data/productos.json',         // Archivo JSON con datos de la BD
                'http://localhost/get_productos_frontend.php',  // Servidor local
                'http://localhost:8000/get_productos_frontend.php',  // Servidor PHP built-in
                'http://localhost/php/get_productos.php',  // Servidor local legacy
                'http://localhost:8000/php/get_productos.php',  // Servidor PHP built-in legacy
            ];
            
            for (const apiUrl of apiUrls) {
                try {
                    console.log(`🔍 Intentando conectar con: ${apiUrl}`);
                    const response = await fetch(apiUrl);
                    if (response.ok) {
                        const data = await response.json();
                        
                        // Manejar diferentes formatos de respuesta
                        let productos = [];
                        
                        if (data.success && data.products && data.products.length > 0) {
                            // Formato frontend: {success: true, products: [...]}
                            productos = data.products;
                            console.log('📦 Formato frontend detectado');
                        } else if (data.success && data.productos && data.productos.length > 0) {
                            // Formato admin: {success: true, productos: [...]}
                            productos = data.productos;
                            console.log('📦 Formato admin detectado');
                        } else if (Array.isArray(data) && data.length > 0) {
                            // Formato: array directo de productos
                            productos = data;
                            console.log('📦 Formato array directo detectado');
                        } else if (data.length > 0) {
                            // Formato: objeto con productos como array directo
                            productos = data;
                            console.log('📦 Formato objeto directo detectado');
                        }
                        
                        if (productos.length > 0) {
                            this.productos = productos;
                            console.log('✅ Productos cargados desde API real:', this.productos.length);
                            console.log('🌐 API URL funcionando:', apiUrl);
                            console.log('📊 Primeros 2 productos:', this.productos.slice(0, 2));
                            console.log('🖼️ Imágenes de productos:', this.productos.map(p => ({
                                nombre: p.name || p.nombre,
                                imagen: p.image || p.imagen
                            })));
                            return;
                        }
                    }
                } catch (error) {
                    console.log(`❌ Fallo en ${apiUrl}:`, error.message);
                    continue; // Intentar siguiente URL
                }
            }
            
            // Si no hay API, usar datos mock
            // Intentar múltiples formas de acceder al Mock API
            console.log('🔍 Buscando Mock API...');
            
            if (typeof mockApi !== 'undefined' && mockApi && mockApi.productos) {
                this.productos = mockApi.productos;
                console.log('✅ Productos cargados desde mockApi global:', this.productos.length);
            } else if (typeof window !== 'undefined' && window.mockApi && window.mockApi.productos) {
                this.productos = window.mockApi.productos;
                console.log('✅ Productos cargados desde window.mockApi:', this.productos.length);
            } else if (typeof window !== 'undefined' && window.mockApi && typeof window.mockApi.getProductos === 'function') {
                this.productos = window.mockApi.getProductos();
                console.log('✅ Productos cargados desde mockApi.getProductos():', this.productos.length);
            } else {
                // Último intento: intentar ejecutar el script mock directamente
                try {
                    // Verificar si existe una función global que podamos llamar
                    console.log('🔄 Último intento: buscando productos en el contexto global');
                    
                    // Intentar acceder a productos definidos globalmente
                    if (typeof window !== 'undefined') {
                        // Buscar en todas las variables globales posibles
                        const posiblesAPIs = ['mockApi', 'mockAPI', 'productosData', 'productData'];
                        
                        for (const apiName of posiblesAPIs) {
                            if (window[apiName] && window[apiName].productos) {
                                this.productos = window[apiName].productos;
                                console.log(`✅ Productos encontrados en window.${apiName}:`, this.productos.length);
                                break;
                            }
                            if (window[apiName] && typeof window[apiName].getProductos === 'function') {
                                this.productos = window[apiName].getProductos();
                                console.log(`✅ Productos obtenidos via window.${apiName}.getProductos():`, this.productos.length);
                                break;
                            }
                        }
                        
                        // Si aún no encontramos productos, usar datos hardcodeados como último recurso
                        if (this.productos.length === 0) {
                            console.warn('⚠️ Usando productos hardcodeados como último recurso');
                            this.productos = [
                                {
                                    id: 1,
                                    nombre: "Chaqueta Deportiva Premium",
                                    precio: 89.99,
                                    categoria: "chaquetas",
                                    imagen: "https://images.unsplash.com/photo-1551698618-1dfe5d97d256?w=400&h=400&fit=crop",
                                    stock: 15,
                                    badges: ["nuevo", "destacado"]
                                },
                                {
                                    id: 2,
                                    nombre: "Vestido Elegante",
                                    precio: 120.00,
                                    precio_oferta: 95.00,
                                    categoria: "vestidos",
                                    imagen: "https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=400&h=400&fit=crop",
                                    stock: 8
                                },
                                {
                                    id: 3,
                                    nombre: "Pantalón Clásico",
                                    precio: 65.50,
                                    categoria: "pantalones",
                                    imagen: "https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?w=400&h=400&fit=crop",
                                    stock: 12
                                },
                                {
                                    id: 4,
                                    nombre: "Blusa Casual",
                                    precio: 45.00,
                                    categoria: "blusas",
                                    imagen: "https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400&h=400&fit=crop",
                                    stock: 20
                                },
                                {
                                    id: 5,
                                    nombre: "Falda Moderna",
                                    precio: 55.00,
                                    precio_oferta: 40.00,
                                    categoria: "faldas",
                                    imagen: "https://images.unsplash.com/photo-1583496661160-fb5886a13d14?w=400&h=400&fit=crop",
                                    stock: 6,
                                    badges: ["destacado"]
                                },
                                {
                                    id: 6,
                                    nombre: "Conjunto Deportivo",
                                    precio: 85.00,
                                    categoria: "deportivo",
                                    imagen: "https://images.unsplash.com/photo-1506629905607-ce2a6c49e8d4?w=400&h=400&fit=crop",
                                    stock: 10,
                                    badges: ["nuevo"]
                                }
                            ];
                            console.log('✅ Productos hardcodeados cargados:', this.productos.length);
                        }
                    }
                    
                    if (this.productos.length === 0) {
                        console.warn('❌ No se pudieron cargar productos de ninguna fuente');
                        console.warn('Debug: mockApi global disponible?', typeof mockApi !== 'undefined');
                        console.warn('Debug: window.mockApi disponible?', typeof window !== 'undefined' && window.mockApi);
                        console.warn('Debug: window keys:', typeof window !== 'undefined' ? Object.keys(window).filter(k => k.includes('mock') || k.includes('product')) : 'window no disponible');
                    }
                    
                } catch (error) {
                    console.error('Error accediendo al Mock API:', error);
                    this.productos = [];
                }
            }
            
        } catch (error) {
            console.error('❌ Error cargando productos:', error);
            this.productos = [];
        }
    }
    
    setupEventListeners() {
        const categoryButtons = document.querySelectorAll('[data-categoria]');
        categoryButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.categoriaActual = button.dataset.categoria;
                
                categoryButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                this.renderProducts();
            });
        });
        
        const sortSelect = document.getElementById('ordenar-productos');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.ordenActual = e.target.value;
                this.renderProducts();
            });
        }
        
        const searchInput = document.getElementById('buscar-producto');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.terminoBusqueda = e.target.value.toLowerCase();
                this.renderProducts();
            });
        }
    }
    
    getProductosFiltrados() {
        let productosFiltrados = [...this.productos];
        
        if (this.categoriaActual !== 'todas') {
            productosFiltrados = productosFiltrados.filter(producto => 
                producto.categoria && producto.categoria.toLowerCase() === this.categoriaActual.toLowerCase()
            );
        }
        
        if (this.terminoBusqueda) {
            productosFiltrados = productosFiltrados.filter(producto => 
                (producto.nombre && producto.nombre.toLowerCase().includes(this.terminoBusqueda)) ||
                (producto.descripcion && producto.descripcion.toLowerCase().includes(this.terminoBusqueda))
            );
        }
        
        productosFiltrados.sort((a, b) => {
            switch (this.ordenActual) {
                case 'precio-asc':
                    return (a.precio_oferta || a.precio) - (b.precio_oferta || b.precio);
                case 'precio-desc':
                    return (b.precio_oferta || b.precio) - (a.precio_oferta || a.precio);
                case 'nombre-desc':
                    return (b.nombre || '').localeCompare(a.nombre || '');
                default:
                    return (a.nombre || '').localeCompare(b.nombre || '');
            }
        });
        
        return productosFiltrados;
    }
    
    renderProducts() {
        if (!this.productsContainer) {
            console.error('❌ Contenedor de productos no encontrado');
            return;
        }
        
        const productosFiltrados = this.getProductosFiltrados();
        
        if (productosFiltrados.length === 0) {
            this.productsContainer.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No se encontraron productos</h4>
                        <p class="text-muted">Intenta con otros términos de búsqueda o categoría</p>
                    </div>
                </div>
            `;
            return;
        }
        
        const html = productosFiltrados.map(producto => {
            const precioFinal = producto.precio_oferta || producto.precio;
            const tieneOferta = producto.precio_oferta && producto.precio_oferta < producto.precio;
            
            return `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card h-100" data-product-id="${producto.id}">
                        <div class="product-image-container position-relative overflow-hidden">
                            ${this.renderBadges(producto)}
                            
                            <img src="${producto.imagen_principal || producto.imagen || `http://localhost/generate-placeholder-svg.php?w=300&h=300&text=${encodeURIComponent(producto.nombre || 'Producto')}`}" 
                                 class="product-image" 
                                 alt="${producto.nombre || 'Producto'}"
                                 loading="lazy"
                                 onerror="this.src='http://localhost/generate-placeholder-svg.php?w=300&h=300&text='+encodeURIComponent('${producto.nombre || 'Producto'}')">
                            
                            <div class="product-overlay">
                                <div class="product-actions">
                                    <button class="btn btn-white btn-sm rounded-circle mb-2" 
                                            onclick="productLoader.verDetalleProducto(${producto.id})"
                                            title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-primary btn-sm rounded-circle" 
                                            onclick="productLoader.agregarAlCarrito(${producto.id})"
                                            title="Agregar al carrito"
                                            ${producto.stock <= 0 ? 'disabled' : ''}>
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="product-body">
                            <div class="product-category">
                                <small class="text-muted">${producto.categoria || 'Sin categoría'}</small>
                            </div>
                            
                            <h5 class="product-title">${producto.nombre || 'Producto sin nombre'}</h5>
                            
                            ${producto.descripcion ? `
                            <p class="product-description">
                                ${producto.descripcion.length > 80 ? 
                                    producto.descripcion.substring(0, 80) + '...' : 
                                    producto.descripcion
                                }
                            </p>
                            ` : ''}
                            
                            <div class="product-pricing">
                                <span class="price-current">$${this.formatPrice(precioFinal)}</span>
                                ${tieneOferta ? `
                                    <span class="price-original">$${this.formatPrice(producto.precio)}</span>
                                ` : ''}
                            </div>
                            
                            <div class="product-stock">
                                ${this.renderStockStatus(producto.stock)}
                            </div>
                            
                            ${producto.variantes && producto.variantes.length > 0 ? `
                            <div class="product-variants">
                                <small class="text-muted">
                                    <i class="fas fa-palette me-1"></i>
                                    ${producto.variantes.length} opciones
                                </small>
                            </div>
                            ` : ''}
                            
                            <div class="product-actions-bottom">
                                <button class="btn btn-primary w-100 add-to-cart-btn" 
                                        onclick="productLoader.agregarAlCarrito(${producto.id})"
                                        ${producto.stock <= 0 ? 'disabled' : ''}>
                                    ${producto.stock <= 0 ? 
                                        '<i class="fas fa-times me-2"></i>Agotado' : 
                                        '<i class="fas fa-shopping-cart me-2"></i>Añadir al Carrito'
                                    }
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        this.productsContainer.innerHTML = html;
        
        console.log('✅ Productos renderizados correctamente:', productosFiltrados.length);
    }
    
    renderBadges(producto) {
        let badges = '';
        
        // Badge de oferta (descuento)
        if (producto.precio_oferta && producto.precio_oferta < producto.precio) {
            const descuento = Math.round(((producto.precio - producto.precio_oferta) / producto.precio) * 100);
            badges += `<span class="product-badge badge-sale">-${descuento}%</span>`;
        }
        
        // Badges de la API (estructura de array de objetos)
        if (producto.badges && Array.isArray(producto.badges)) {
            producto.badges.forEach(badge => {
                if (typeof badge === 'object' && badge.tipo) {
                    switch (badge.tipo) {
                        case 'nuevo':
                            badges += `<span class="product-badge badge-new">${badge.texto || 'Nuevo'}</span>`;
                            break;
                        case 'destacado':
                            badges += `<span class="product-badge badge-featured">${badge.texto || 'Destacado'}</span>`;
                            break;
                        case 'oferta':
                            badges += `<span class="product-badge badge-sale">${badge.texto || 'Oferta'}</span>`;
                            break;
                        case 'stock-bajo':
                            badges += `<span class="product-badge badge-low-stock">${badge.texto || 'Stock bajo'}</span>`;
                            break;
                    }
                } else if (typeof badge === 'string') {
                    // Compatibilidad con estructura antigua (array de strings)
                    switch (badge.toLowerCase()) {
                        case 'nuevo':
                            badges += `<span class="product-badge badge-new">Nuevo</span>`;
                            break;
                        case 'destacado':
                            badges += `<span class="product-badge badge-featured">Destacado</span>`;
                            break;
                    }
                }
            });
        }
        
        // Badges basados en propiedades booleanas (compatibilidad con API)
        if (producto.nuevo === true || producto.nuevo === 1) {
            badges += `<span class="product-badge badge-new">Nuevo</span>`;
        }
        
        if (producto.destacado === true || producto.destacado === 1) {
            badges += `<span class="product-badge badge-featured">Destacado</span>`;
        }
        
        if (producto.oferta === true || producto.oferta === 1) {
            badges += `<span class="product-badge badge-sale">Oferta</span>`;
        }
        
        // Badge de stock bajo (automático)
        if (producto.stock > 0 && producto.stock <= 5) {
            badges += `<span class="product-badge badge-low-stock">¡Últimas ${producto.stock}!</span>`;
        }
        
        return badges;
    }
    
    renderStockStatus(stock) {
        if (stock <= 0) {
            return '<span class="badge bg-danger">Agotado</span>';
        } else if (stock <= 5) {
            return `<span class="badge bg-warning text-dark">Stock bajo (${stock})</span>`;
        } else {
            return `<span class="badge bg-success">Disponible</span>`;
        }
    }
    
    formatPrice(precio) {
        return new Intl.NumberFormat('es-AR').format(precio);
    }
    
    agregarAlCarrito(productoId) {
        const producto = this.productos.find(p => p.id === productoId);
        if (!producto) {
            console.error('Producto no encontrado:', productoId);
            return;
        }
        
        if (producto.stock <= 0) {
            this.mostrarNotificacion('Producto agotado', 'error');
            return;
        }
        
        const itemExistente = this.cartItems.find(item => item.id === productoId);
        
        if (itemExistente) {
            itemExistente.cantidad += 1;
        } else {
            this.cartItems.push({
                id: producto.id,
                nombre: producto.nombre,
                precio: producto.precio_oferta || producto.precio,
                imagen: producto.imagen,
                cantidad: 1
            });
        }
        
        this.cartCount++;
        this.updateCartUI();
        this.saveCart();
        this.mostrarNotificacion(`${producto.nombre} agregado al carrito`, 'success');
    }
    
    updateCartUI() {
        const cartBadges = document.querySelectorAll('.cart-count');
        cartBadges.forEach(badge => {
            badge.textContent = this.cartCount;
            badge.style.display = this.cartCount > 0 ? 'inline' : 'none';
        });
        
        const total = this.cartItems.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
        const cartTotalElements = document.querySelectorAll('.cart-total');
        cartTotalElements.forEach(element => {
            element.textContent = `$${this.formatPrice(total)}`;
        });
    }
    
    saveCart() {
        localStorage.setItem('golden-cart-items', JSON.stringify(this.cartItems));
        localStorage.setItem('golden-cart-count', this.cartCount.toString());
    }
    
    loadCart() {
        try {
            const items = localStorage.getItem('golden-cart-items');
            const count = localStorage.getItem('golden-cart-count');
            
            this.cartItems = items ? JSON.parse(items) : [];
            this.cartCount = count ? parseInt(count) : 0;
        } catch (error) {
            console.error('Error cargando carrito:', error);
            this.cartItems = [];
            this.cartCount = 0;
        }
    }
    
    verDetalleProducto(productoId) {
        const producto = this.productos.find(p => p.id === productoId);
        if (!producto) return;
        
        alert(`Producto: ${producto.nombre}\nPrecio: $${this.formatPrice(producto.precio_oferta || producto.precio)}\nStock: ${producto.stock}\nDescripción: ${producto.descripcion || 'Sin descripción'}`);
    }
    
    mostrarNotificacion(mensaje, tipo = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${tipo === 'success' ? 'success' : tipo === 'error' ? 'danger' : 'info'} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-${tipo === 'success' ? 'check' : tipo === 'error' ? 'times' : 'info'}-circle me-2"></i>
            ${mensaje}
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 3000);
    }
}

// Función para inicializar el ProductLoader de forma segura
function initProductLoader() {
    // Función para verificar si Mock API está disponible
    function checkMockApiReady() {
        return (typeof mockApi !== 'undefined' && mockApi && mockApi.productos) ||
               (typeof window !== 'undefined' && window.mockApi && (window.mockApi.productos || window.mockApi.getProductos)) ||
               (typeof window !== 'undefined' && Object.keys(window).some(key => 
                   key.includes('mock') && window[key] && (window[key].productos || typeof window[key].getProductos === 'function')
               ));
    }
    
    // Función para inicializar cuando esté listo
    function doInit() {
        console.log('🔄 Inicializando ProductLoader...');
        window.productLoader = new ProductLoader();
        productLoader.init();
    }
    
    // Si ya está listo, inicializar inmediatamente
    if (checkMockApiReady()) {
        console.log('✅ Mock API ya disponible, inicializando inmediatamente');
        doInit();
        return;
    }
    
    // Función para intentar inicializar con retraso
    function attemptInit(delay, reason) {
        console.log(`⏳ Esperando ${delay}ms para inicializar (${reason})...`);
        setTimeout(() => {
            if (checkMockApiReady()) {
                console.log(`✅ Mock API disponible después de ${delay}ms`);
                doInit();
            } else {
                console.warn(`⚠️ Mock API no disponible después de ${delay}ms, inicializando de todos modos`);
                doInit(); // Inicializar de todos modos, usará datos hardcodeados
            }
        }, delay);
    }
    
    // Si el DOM está cargando, esperar a que termine
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            attemptInit(500, 'DOMContentLoaded');
        });
    } else {
        // DOM ya cargado, esperar un poco más
        attemptInit(800, 'DOM ya cargado');
    }
}

// Inicializar inmediatamente
initProductLoader();

// 🔄 FUNCIÓN GLOBAL PARA RECARGAR PRODUCTOS
// Esta función puede ser llamada desde el admin panel después de crear/editar productos
window.reloadProducts = async function() {
    console.log('🔄 Recargando productos desde admin panel...');
    
    // Primero intentar recargar con ProductLoader existente
    if (window.productLoader) {
        try {
            await window.productLoader.cargarProductos();
            window.productLoader.renderProducts();
            console.log('✅ Productos recargados exitosamente via ProductLoader');
            
            // Mostrar notificación visual si está disponible
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Productos actualizados',
                    text: 'Los productos se han recargado desde la base de datos',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            return;
        } catch (error) {
            console.error('❌ Error al recargar con ProductLoader:', error);
        }
    }
    
    // Si ProductLoader no está disponible, usar carga directa
    console.log('🔄 ProductLoader no disponible, usando carga directa...');
    
    try {
        const productosGrid = document.getElementById('productos-grid');
        if (!productosGrid) {
            console.error('❌ Contenedor productos-grid no encontrado');
            return;
        }
        
        // Mostrar spinner mientras carga
        productosGrid.innerHTML = `
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Recargando productos...</span>
                </div>
                <p class="text-muted">Recargando productos desde la base de datos...</p>
            </div>
        `;
        
        // Cargar productos desde API
        const response = await fetch('php/get_productos.php');
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const productos = await response.json();
        
        if (Array.isArray(productos) && productos.length > 0) {
            // Usar la función de renderizado del index.html
            if (typeof window.renderProductsInGrid === 'function') {
                window.renderProductsInGrid(productos);
            } else {
                // Renderizado simple como fallback
                let html = '';
                productos.forEach(producto => {
                    html += `
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="card product-card h-100">
                                <img src="${producto.imagen_principal || `http://localhost/generate-placeholder-svg.php?w=300&h=300&text=${encodeURIComponent(producto.nombre || 'Producto')}`}" 
                                     class="card-img-top" style="height: 200px; object-fit: cover;"
                                     alt="${producto.nombre}"
                                     onerror="this.src='http://localhost/generate-placeholder-svg.php?w=300&h=300&text='+encodeURIComponent('${producto.nombre || 'Producto'}')">
                                <div class="card-body">
                                    <h5 class="card-title">${producto.nombre}</h5>
                                    <p class="text-primary">$${(producto.precio || 0).toLocaleString()}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
                productosGrid.innerHTML = html;
            }
            
            console.log('✅ Productos recargados exitosamente via carga directa');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Productos actualizados',
                    text: `${productos.length} productos recargados desde MySQL`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
            
        } else {
            // No hay productos
            productosGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay productos</h4>
                    <p class="text-muted">Agrega productos desde el panel de administración</p>
                </div>
            `;
        }
        
    } catch (error) {
        console.error('❌ Error al recargar productos:', error);
        
        const productosGrid = document.getElementById('productos-grid');
        if (productosGrid) {
            productosGrid.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="fas fa-exclamation-circle fa-3x text-danger mb-3"></i>
                    <h4 class="text-danger">Error al cargar productos</h4>
                    <p class="text-muted">${error.message}</p>
                    <button class="btn btn-primary" onclick="window.reloadProducts()">Intentar de nuevo</button>
                </div>
            `;
        }
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron recargar los productos: ' + error.message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000
            });
        }
    }
};

// 🔄 FUNCIÓN GLOBAL PARA FORZAR RECARGA COMPLETA
window.forceReloadProducts = function() {
    console.log('🔄 Forzando recarga completa de productos...');
    if (window.productLoader) {
        window.productLoader.init();
    }
};
