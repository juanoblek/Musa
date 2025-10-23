/* ====================================================
   🚀 SISTEMA ADMINISTRATIVO COMPLETO - M & A MODA ACTUAL
   ====================================================*/

// === FUNCIONES GLOBALES DE DEBUG ===
// Estas funciones están disponibles en la consola del navegador para debugging

// Función para mostrar el estado del loading
window.debugLoadingState = function() {
    const overlay = document.getElementById('loadingOverlay');
    const saveBtn = document.getElementById('saveProductBtn');
    
    console.log('🔍 [DEBUG] Estado actual del loading:');
    console.log('  - Overlay existe:', !!overlay);
    if (overlay) {
        console.log('  - Overlay classes:', overlay.className);
        console.log('  - Overlay style display:', overlay.style.display);
        console.log('  - Overlay computed display:', window.getComputedStyle(overlay).display);
    }
    console.log('  - Botón existe:', !!saveBtn);
    if (saveBtn) {
        console.log('  - Botón disabled:', saveBtn.disabled);
        console.log('  - Botón classes:', saveBtn.className);
        console.log('  - Botón innerHTML:', saveBtn.innerHTML);
    }
    
    return { overlay, saveBtn };
};

// Función de emergencia para cerrar el loading
window.forceCloseLoading = function() {
    console.log('🚨 Forzando cierre del loading desde consola...');
    if (window.adminSystem) {
        window.adminSystem.forceHideLoading();
    } else {
        // Fallback manual
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.remove('show');
            overlay.classList.add('hide');
            overlay.style.setProperty('display', 'none', 'important');
        }
        
        const saveBtn = document.getElementById('saveProductBtn');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.classList.remove('btn-loading');
            saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
        }
        
        console.log('✅ Loading cerrado manualmente');
    }
};

class AdminSystem {
    constructor() {
        try {
            console.log('🔧 Constructor AdminSystem iniciando...');
            
            this.products = JSON.parse(localStorage.getItem('products')) || [];
            console.log(`📦 Productos cargados: ${this.products.length}`);
            
            // Verificar si es la PRIMERA inicialización usando bandera específica
            const isFirstInit = !localStorage.getItem('admin_initialized');
            
            if (this.products.length === 0 && isFirstInit) {
                console.log('📦 PRIMERA INICIALIZACIÓN - Cargando productos del frontend...');
                this.products = this.getDefaultProducts();
                this.saveProducts();
                localStorage.setItem('admin_initialized', 'true'); // Marcar como inicializado
                console.log(`📦 Productos del frontend cargados: ${this.products.length}`);
            } else if (this.products.length === 0) {
                console.log('ℹ️ No hay productos pero el admin ya fue inicializado - mantener vacío');
            } else {
                console.log('🔄 Productos existentes encontrados, conservando TODOS los productos del usuario...');
                console.log('📋 Productos actuales:');
                this.products.forEach((product, index) => {
                    console.log(`  ${index + 1}. ${product.name} (ID: ${product.id})`);
                });
                
                // Verificar que los productos tienen las propiedades necesarias
                this.products = this.products.map(product => {
                    if (!product.id) {
                        product.id = `item_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
                    }
                    if (!product.stock_quantity) {
                        product.stock_quantity = 10;
                    }
                    if (!product.created_at) {
                        product.created_at = Date.now();
                    }
                    return product;
                });
            }
            
            this.categories = JSON.parse(localStorage.getItem('categories')) || this.getDefaultCategories();
            console.log(`📂 Categorías cargadas: ${this.categories.length}`);
            
            this.orders = JSON.parse(localStorage.getItem('orders')) || [];
            console.log(`🛒 Pedidos cargados: ${this.orders.length}`);
            
            this.navigation = JSON.parse(localStorage.getItem('navigation')) || this.getDefaultNavigation();
            console.log('🧭 Navegación cargada');
            
            this.currentSection = 'dashboard';
            
            this.init();
            
            // Verificar productos perdidos después de inicializar
            setTimeout(() => {
                this.recoverLostProducts();
            }, 1000);
            
            console.log('✅ Constructor AdminSystem completado');
        } catch (error) {
            console.error('❌ Error en constructor AdminSystem:', error);
            throw error;
        }
    }

    init() {
        console.log('🎯 Iniciando Sistema Administrativo...');
        
        // Verificar autenticación
        this.checkAuth();
        
        // Inicializar componentes
        this.setupNavigation();
        this.setupEventListeners();
        this.loadDashboard();
        this.updateDateTime();
        
        // Auto-actualizar estadísticas
        setInterval(() => this.updateStats(), 30000);
        
        console.log('✅ Sistema Administrativo iniciado correctamente');
    }

    checkAuth() {
        if (!sessionStorage.getItem('adminAuthenticated')) {
            window.location.href = 'index.html';
            return false;
        }
        return true;
    }

    // =================== UTILIDADES ===================
    formatPrice(value) {
        // Validar que el valor sea un número válido
        const numValue = parseFloat(value);
        if (isNaN(numValue) || numValue === null || numValue === undefined) {
            return '0';
        }
        return numValue.toLocaleString('es-CO');
    }

    validateAndSyncProducts() {
        try {
            const frontendProducts = this.getDefaultProducts();
            const frontendProductIds = frontendProducts.map(p => p.id);
            
            // Verificar si los productos actuales son válidos
            const hasValidProducts = this.products.some(product => 
                frontendProductIds.includes(product.id) && 
                product.price && 
                product.name && 
                product.category
            );
            
            // Si no hay productos válidos o hay datos incorrectos, reemplazar con los del frontend
            if (!hasValidProducts || this.products.length < frontendProducts.length) {
                console.log('🔄 Sincronizando con productos del frontend...');
                this.products = frontendProducts;
                this.saveProducts();
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('❌ Error validando productos:', error);
            // COMENTADO: No resetear automáticamente productos para preservar eliminaciones
            // En lugar de resetear, mantenemos los productos actuales
            console.log('⚠️ Manteniendo productos actuales para preservar cambios del usuario');
            return false; // Indicar que no se hizo reset automático
        }
    }

    // =================== NAVEGACIÓN ===================
    setupNavigation() {
        const navLinks = document.querySelectorAll('.nav-link[data-section]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.closest('[data-section]').dataset.section;
                this.showSection(section);
            });
        });
    }

    showSection(sectionName) {
        // Ocultar todas las secciones
        document.querySelectorAll('.content-section').forEach(section => {
            section.classList.remove('active');
        });

        // Mostrar sección seleccionada
        document.getElementById(sectionName).classList.add('active');

        // Actualizar navegación
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${sectionName}"]`).classList.add('active');

        this.currentSection = sectionName;

        // Cargar contenido específico
        switch(sectionName) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'products':
                this.loadProducts();
                break;
            case 'categories':
                this.loadCategories();
                break;
            case 'orders':
                this.loadOrders();
                break;
            case 'navigation':
                this.loadNavigation();
                break;
            case 'settings':
                this.loadSettings();
                break;
        }
    }

    // =================== DASHBOARD ===================
    loadDashboard() {
        this.updateStats();
        this.loadRecentActivity();
        this.loadAlerts();
    }

    updateStats() {
        // Estadísticas básicas
        document.getElementById('totalProducts').textContent = this.products.length;
        document.getElementById('totalCategories').textContent = this.categories.length;
        document.getElementById('totalOrders').textContent = this.orders.length;

        // Calcular ingresos
        const totalRevenue = this.orders.reduce((sum, order) => sum + (parseFloat(order.total) || 0), 0);
        document.getElementById('totalRevenue').textContent = `$${this.formatPrice(totalRevenue)}`;
    }

    loadRecentActivity() {
        const activityContainer = document.getElementById('recentActivity');
        const recentActivities = this.getRecentActivities();

        if (recentActivities.length === 0) {
            activityContainer.innerHTML = '<p class="text-muted">No hay actividad reciente</p>';
            return;
        }

        const activityHTML = recentActivities.map(activity => `
            <div class="d-flex align-items-center mb-3 p-2 rounded" style="background: rgba(52, 152, 219, 0.05);">
                <div class="me-3">
                    <i class="${activity.icon} text-${activity.type}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold">${activity.title}</div>
                    <small class="text-muted">${activity.time}</small>
                </div>
            </div>
        `).join('');

        activityContainer.innerHTML = activityHTML;
    }

    getRecentActivities() {
        const activities = [];
        
        // Productos recientes
        const recentProducts = this.products.slice(-3);
        recentProducts.forEach(product => {
            activities.push({
                title: `Producto agregado: ${product.name}`,
                time: this.formatDate(product.created_at || Date.now()),
                icon: 'fas fa-box',
                type: 'primary'
            });
        });

        // Pedidos recientes
        const recentOrders = this.orders.slice(-3);
        recentOrders.forEach(order => {
            activities.push({
                title: `Nuevo pedido #${order.id}`,
                time: this.formatDate(order.created_at || Date.now()),
                icon: 'fas fa-shopping-cart',
                type: 'success'
            });
        });

        return activities.sort((a, b) => new Date(b.time) - new Date(a.time)).slice(0, 5);
    }

    loadAlerts() {
        const alertsContainer = document.getElementById('alerts');
        const alerts = this.getSystemAlerts();

        const alertsHTML = alerts.map(alert => `
            <div class="alert alert-${alert.type} d-flex align-items-center">
                <i class="${alert.icon} me-2"></i>
                ${alert.message}
            </div>
        `).join('');

        alertsContainer.innerHTML = alertsHTML;
    }

    getSystemAlerts() {
        const alerts = [];

        // Productos con bajo stock
        const lowStock = this.products.filter(p => p.stock_quantity < 5);
        if (lowStock.length > 0) {
            alerts.push({
                type: 'warning',
                icon: 'fas fa-exclamation-triangle',
                message: `${lowStock.length} productos con stock bajo`
            });
        }

        // Productos sin imagen
        const noImage = this.products.filter(p => !p.image);
        if (noImage.length > 0) {
            alerts.push({
                type: 'info',
                icon: 'fas fa-image',
                message: `${noImage.length} productos sin imagen`
            });
        }

        if (alerts.length === 0) {
            alerts.push({
                type: 'success',
                icon: 'fas fa-check-circle',
                message: 'Sistema funcionando correctamente'
            });
        }

        return alerts;
    }

    // =================== PRODUCTOS ===================
    loadProducts() {
        console.log('📦 Cargando productos...', this.products);
        
        // Validar y limpiar datos de productos
        this.products.forEach((product, index) => {
            if (!product.price && !product.sale_price) {
                console.warn(`⚠️ Producto ${index} sin precio:`, product);
                product.price = 0; // Valor por defecto
            }
            if (typeof product.price === 'string') {
                product.price = parseFloat(product.price.replace(/[^0-9.]/g, '')) || 0;
            }
            if (product.sale_price && typeof product.sale_price === 'string') {
                product.sale_price = parseFloat(product.sale_price.replace(/[^0-9.]/g, '')) || null;
            }
            if (!product.stock_quantity && product.stock_quantity !== 0) {
                product.stock_quantity = 0;
            }
            
            // Usar imagen unificada para consistencia
            if (window.getUnifiedProductImage) {
                const unifiedImage = window.getUnifiedProductImage(product);
                if (unifiedImage !== 'images/placeholder.svg') {
                    product.image = unifiedImage;
                }
            } else if (product.image && !this.imageExists(product.image)) {
                console.warn(`⚠️ Imagen no encontrada: ${product.image}`);
                product.image = 'images/placeholder.svg'; // Fallback a placeholder
            }
        });
        
        const tbody = document.getElementById('productsTableBody');
        
        if (this.products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-box fa-3x mb-3"></i>
                        <p>No hay productos registrados</p>
                        <button class="btn btn-admin-primary" onclick="adminSystem.showAddProductModal()">
                            <i class="fas fa-plus me-2"></i>Agregar Primer Producto
                        </button>
                    </td>
                </tr>
            `;
            return;
        }

        const productsHTML = this.products.map(product => `
            <tr>
                <td>
                    <img src="${window.getUnifiedProductImage ? window.getUnifiedProductImage(product) : (product.image || 'images/placeholder.svg')}" 
                         alt="${product.name}" 
                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                </td>
                <td>
                    <div class="fw-bold">${product.name}</div>
                    <small class="text-muted">${product.description || 'Sin descripción'}</small>
                    ${product.badge ? `<br><span class="badge bg-info">${product.badge}</span>` : ''}
                </td>
                <td>
                    <span class="badge bg-primary">${this.getCategoryName(product.category)}</span>
                </td>
                <td>
                    <div class="fw-bold text-success">$${this.formatPrice(product.sale_price || product.price)}</div>
                    ${product.sale_price ? `<small class="text-muted text-decoration-line-through">$${this.formatPrice(product.price)}</small>` : ''}
                    ${product.discount ? `<br><span class="badge bg-danger">-${product.discount}%</span>` : ''}
                    ${product.reviews ? `<br><small class="text-info"><i class="fas fa-star"></i> ${product.reviews.toLocaleString()} reseñas</small>` : ''}
                </td>
                <td>
                    <span class="badge ${product.stock_quantity > 10 ? 'bg-success' : product.stock_quantity > 0 ? 'bg-warning' : 'bg-danger'}">
                        ${product.stock_quantity} unidades
                    </span>
                </td>
                <td>
                    <span class="badge ${product.status === 'active' ? 'bg-success' : product.status === 'inactive' ? 'bg-secondary' : 'bg-danger'}">
                        ${this.getStatusText(product.status)}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="adminSystem.editProduct('${product.id}')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="adminSystem.deleteProduct('${product.id}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.innerHTML = productsHTML;
    }

    showAddProductModal() {
        const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
        document.getElementById('addProductForm').reset();
        document.getElementById('imagePreview').innerHTML = '';
        modal.show();
    }

    async saveProduct() {
        console.log('💾 [SAVE PRODUCT] Iniciando guardado de producto...');
        
        const form = document.getElementById('addProductForm');
        if (!form) {
            console.error('❌ [SAVE PRODUCT] Formulario no encontrado');
            this.showLoading(false);
            return;
        }
        
        // Validar campos requeridos
        const requiredFields = ['productName', 'productPrice', 'productCategory', 'productGender', 'productStock'];
        let isValid = true;

        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input || !input.value.trim()) {
                if (input) input.classList.add('is-invalid');
                isValid = false;
                console.warn(`⚠️ [SAVE PRODUCT] Campo requerido vacío: ${field}`);
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
            console.error('❌ [SAVE PRODUCT] Validación fallida');
            this.showLoading(false);
            Swal.fire('Error', 'Por favor completa todos los campos requeridos', 'error');
            return;
        }

        // Mostrar loading
        console.log('⏳ [SAVE PRODUCT] Mostrando loading...');
        this.showLoading(true);

        try {
            console.log('🔄 [SAVE PRODUCT] Obteniendo datos del formulario...');
            
            // Obtener datos del formulario
            const productData = this.getProductFormData();
            console.log('📋 [SAVE PRODUCT] Datos del producto:', productData);
            
            // Procesar imagen
            const imageFile = document.getElementById('productImage')?.files[0];
            if (imageFile) {
                console.log('🖼️ [SAVE PRODUCT] Procesando imagen...');
                productData.image = await this.processImage(imageFile);
            } else {
                console.log('📷 [SAVE PRODUCT] No se seleccionó imagen, usando placeholder');
                productData.image = 'images/placeholder.svg';
            }

            // Generar ID único
            productData.id = this.generateId();
            productData.created_at = Date.now();

            console.log(`✅ [SAVE PRODUCT] Producto creado con ID: ${productData.id}`);

            // Guardar producto
            this.products.push(productData);
            this.saveProducts();
            
            console.log('📡 [SAVE PRODUCT] Notificando al frontend...');
            
            // Notificar al frontend inmediatamente
            this.notifyFrontendProductAdded(productData);
            
            // Forzar recarga inmediata en el frontend
            this.forceReloadFrontendProducts();

            // Cerrar modal y recargar
            console.log('🔄 [SAVE PRODUCT] Cerrando modal y actualizando UI...');
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            if (modal) {
                modal.hide();
            }
            
            // Limpiar formulario
            form.reset();
            
            // Recargar datos
            this.loadProducts();
            this.updateStats();

            console.log('✅ [SAVE PRODUCT] Producto guardado exitosamente');
            
            // Log detallado para debug
            console.log('🔍 [DEBUG] Estado después de guardar:');
            console.log('  - Total productos:', this.products.length);
            console.log('  - Último producto:', this.products[this.products.length - 1]);
            console.log('  - LocalStorage products:', JSON.parse(localStorage.getItem('products'))?.length);
            console.log('  - Eventos disparados: productAdded, productsUpdated, forceReloadProducts');
            console.log('  - Banderas establecidas: force_reload_products, lastProductAdded');

            // Mostrar notificación de éxito
            Swal.fire({
                title: '¡Producto Agregado!',
                html: `
                    <div class="text-start">
                        <p><strong>Producto:</strong> ${productData.name}</p>
                        <p><strong>Categoría:</strong> ${this.getCategoryName(productData.category)}</p>
                        <p><strong>Precio:</strong> $${this.formatPrice(productData.sale_price || productData.price)}</p>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle me-2"></i>
                            El producto aparecerá automáticamente en el frontend
                        </div>
                    </div>
                `,
                icon: 'success',
                timer: 4000,
                showConfirmButton: false
            });

        } catch (error) {
            console.error('❌ [SAVE PRODUCT] Error guardando producto:', error);
            Swal.fire({
                title: 'Error',
                text: 'Error al guardar el producto: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        } finally {
            // GARANTIZAR que el loading siempre se oculte
            console.log('🔄 [SAVE PRODUCT] Ocultando loading...');
            this.showLoading(false);
            
            // Doble verificación después de un delay
            setTimeout(() => {
                console.log('🔄 [SAVE PRODUCT] Verificación final - ocultando loading...');
                this.showLoading(false);
            }, 500);
        }
    }

    getProductFormData() {
        // === RECOLECTAR TALLAS ===
        const sizes = [];
        // Tallas de letra (XS, S, M, L, XL, XXL)
        document.querySelectorAll('input[type="checkbox"][id^="size"]:checked').forEach(checkbox => {
            sizes.push(checkbox.value);
        });

        // === RECOLECTAR COLORES CON HEX ===
        const colors = [];
        document.querySelectorAll('.color-input-group').forEach(group => {
            const hexInput = group.querySelector('.color-picker');
            const nameInput = group.querySelector('.color-name');
            if (hexInput && nameInput && nameInput.value.trim()) {
                colors.push({
                    hex: hexInput.value,
                    name: nameInput.value.trim()
                });
            }
        });

        // === CALCULAR PRECIOS Y DESCUENTOS ===
        const price = parseFloat(document.getElementById('productPrice').value) || 0;
        const salePrice = parseFloat(document.getElementById('productSalePrice').value) || 0;
        const hasDiscount = document.getElementById('hasDiscount').checked;
        const discountPercentage = document.getElementById('discountPercentage').value;

        // === CONSTRUIR OBJETO DEL PRODUCTO ===
        const productData = {
            name: document.getElementById('productName').value.trim(),
            description: document.getElementById('productDescription').value.trim(),
            price: price,
            sale_price: salePrice > 0 ? salePrice : null,
            category: document.getElementById('productCategory').value,
            gender: document.getElementById('productGender').value,
            stock_quantity: parseInt(document.getElementById('productStock').value) || 0,
            sizes: sizes,
            colors: colors,
            status: document.getElementById('productStatus').value,
            
            // === CAMPOS DE DESCUENTO ===
            has_discount: hasDiscount,
            discount_percentage: discountPercentage ? parseInt(discountPercentage) : null,
            
            // === METADATOS ===
            created_at: Date.now(),
            updated_at: Date.now(),
            
            // === CAMPOS PARA EL CARRITO ===
            stock: parseInt(document.getElementById('productStock').value) || 0, // Alias para compatibilidad
            available: true,
            featured: false
        };

        // === VALIDACIONES ADICIONALES ===
        if (hasDiscount && salePrice > 0) {
            // Calcular el porcentaje real de descuento
            const realDiscountPercent = Math.round(((price - salePrice) / price) * 100);
            productData.discount_percentage = realDiscountPercent;
        }

        // Asegurar que los colores tengan formato correcto para la tarjeta
        if (colors.length === 0) {
            // Si no hay colores específicos, agregar algunos por defecto basados en la categoría
            const defaultColors = this.getDefaultColorsForCategory(productData.category);
            productData.colors = defaultColors;
        }

        console.log('📦 [FORM DATA] Datos del producto recolectados:', {
            name: productData.name,
            price: productData.price,
            salePrice: productData.sale_price,
            sizes: productData.sizes.length,
            colors: productData.colors.length,
            hasDiscount: productData.has_discount,
            discountPercent: productData.discount_percentage
        });

        return productData;
    }

    // === FUNCIÓN AUXILIAR PARA COLORES POR DEFECTO ===
    getDefaultColorsForCategory(category) {
        const defaultColorsByCategory = {
            'pantalones': [
                { hex: '#000000', name: 'Negro' },
                { hex: '#4169E1', name: 'Azul' },
                { hex: '#8B4513', name: 'Café' }
            ],
            'camisas': [
                { hex: '#FFFFFF', name: 'Blanco' },
                { hex: '#87CEEB', name: 'Azul Claro' },
                { hex: '#000000', name: 'Negro' }
            ],
            'chaquetas': [
                { hex: '#000000', name: 'Negro' },
                { hex: '#708090', name: 'Gris' },
                { hex: '#800000', name: 'Vino' }
            ],
            'blazers': [
                { hex: '#000000', name: 'Negro' },
                { hex: '#2F4F4F', name: 'Gris Oscuro' },
                { hex: '#800080', name: 'Morado' }
            ]
        };
        
        return defaultColorsByCategory[category] || [
            { hex: '#000000', name: 'Negro' },
            { hex: '#FFFFFF', name: 'Blanco' }
        ];
    }

    async processImage(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                resolve(e.target.result);
            };
            reader.readAsDataURL(file);
        });
    }

    deleteProduct(productId) {
        const product = this.products.find(p => p.id === productId);
        if (!product) {
            Swal.fire('Error', 'Producto no encontrado', 'error');
            return;
        }

        Swal.fire({
            title: '¿Eliminar producto?',
            html: `
                <div class="text-start">
                    <p><strong>Producto:</strong> ${product.name}</p>
                    <p><strong>Categoría:</strong> ${this.getCategoryName(product.category)}</p>
                    <p><strong>Precio:</strong> $${this.formatPrice(product.sale_price || product.price)}</p>
                    <br>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>¡Atención!</strong> Este producto se eliminará tanto del panel administrativo como del sitio web.
                    </div>
                    <p class="text-muted small">Esta acción no se puede deshacer.</p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#95a5a6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            width: '500px'
        }).then((result) => {
            if (result.isConfirmed) {
                // Eliminar producto del array
                this.products = this.products.filter(p => p.id !== productId);
                
                // Guardar cambios (esto automáticamente sincroniza con el frontend)
                this.saveProducts();
                
                // Actualizar interfaz del panel
                this.loadProducts();
                this.updateStats();
                
                // Notificar al frontend si está abierto
                this.notifyFrontendProductDeleted(productId, product.name);
                
                // Mostrar confirmación
                Swal.fire({
                    title: '¡Producto Eliminado!',
                    text: `${product.name} ha sido eliminado exitosamente del sitio web y del panel administrativo.`,
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        });
    }

    // =================== EDITAR PRODUCTO ===================
    editProduct(productId) {
        console.log(`✏️ [EDIT PRODUCT] Iniciando edición del producto ID: ${productId}`);
        
        const product = this.products.find(p => p.id === productId);
        if (!product) {
            console.error('❌ Producto no encontrado:', productId);
            Swal.fire('Error', 'Producto no encontrado', 'error');
            return;
        }

        // Por ahora, mostrar los datos del producto para editar
        Swal.fire({
            title: `Editar: ${product.name}`,
            html: `
                <div class="text-start">
                    <p><strong>ID:</strong> ${product.id}</p>
                    <p><strong>Nombre:</strong> ${product.name}</p>
                    <p><strong>Precio:</strong> $${product.price?.toLocaleString()}</p>
                    <p><strong>Stock:</strong> ${product.stock_quantity || 0}</p>
                    <p><strong>Categoría:</strong> ${product.category || 'Sin categoría'}</p>
                    <br>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Función en desarrollo:</strong><br>
                        La edición completa de productos estará disponible próximamente.
                        Por ahora puedes eliminar y volver a crear el producto.
                    </div>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Entendido',
            width: '500px'
        });
    }

    notifyFrontendProductDeleted(productId, productName) {
        // Disparar evento personalizado para el frontend
        window.dispatchEvent(new CustomEvent('productDeleted', {
            detail: { 
                productId: productId, 
                productName: productName,
                timestamp: new Date().toISOString()
            }
        }));

        // Intentar notificar ventanas del frontend si están abiertas
        try {
            // Buscar ventanas del index.html que puedan estar abiertas
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'PRODUCT_DELETED',
                    productId: productId,
                    productName: productName
                }, '*');
            }
        } catch (error) {
            // Silenciar errores de cross-origin
            console.log('🔄 Notificación de eliminación enviada via localStorage');
        }

        console.log(`🗑️ Producto eliminado: ${productName} (ID: ${productId})`);
    }

    notifyFrontendProductAdded(product) {
        console.log(`➕ [ADMIN] Notificando producto agregado: ${product.name}`);
        
        // 1. Disparar evento personalizado para el frontend
        const productAddedEvent = new CustomEvent('productAdded', {
            detail: { 
                product: product,
                productName: product.name,
                category: product.category,
                timestamp: new Date().toISOString()
            }
        });
        
        window.dispatchEvent(productAddedEvent);
        console.log(`🔔 [ADMIN] Evento 'productAdded' disparado para: ${product.name}`);

        // 2. También disparar evento de productos actualizados
        const productsUpdatedEvent = new CustomEvent('productsUpdated', {
            detail: { products: this.products }
        });
        
        window.dispatchEvent(productsUpdatedEvent);
        console.log(`🔄 [ADMIN] Evento 'productsUpdated' disparado`);

        // 3. Intentar notificar ventanas del frontend si están abiertas
        try {
            const message = {
                type: 'PRODUCT_ADDED',
                product: product,
                productName: product.name,
                timestamp: new Date().toISOString()
            };

            // Buscar ventanas del index.html que puedan estar abiertas
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(message, '*');
                console.log(`📤 [ADMIN] Mensaje enviado al opener: ${product.name}`);
            }

            // También intentar con parent si está en iframe
            if (window.parent && window.parent !== window) {
                window.parent.postMessage(message, '*');
                console.log(`📤 [ADMIN] Mensaje enviado al parent: ${product.name}`);
            }

            // Broadcast para cualquier ventana que esté escuchando
            localStorage.setItem('lastProductAdded', JSON.stringify({
                product: product,
                timestamp: Date.now()
            }));
            console.log(`� [ADMIN] Producto guardado en localStorage para sync: ${product.name}`);

        } catch (error) {
            console.warn('⚠️ [ADMIN] Error enviando notificación entre ventanas:', error);
        }

        console.log(`✅ [ADMIN] Notificación completa para producto: ${product.name} (ID: ${product.id})`);
    }

    // =================== FORZAR RECARGA FRONTEND ===================
    forceReloadFrontendProducts() {
        console.log('🚀 [FORCE RELOAD] Forzando recarga inmediata de productos en frontend...');
        
        try {
            // 1. Disparar evento específico de recarga forzada
            const forceReloadEvent = new CustomEvent('forceReloadProducts', {
                detail: { 
                    products: this.products,
                    timestamp: Date.now(),
                    source: 'admin'
                }
            });
            window.dispatchEvent(forceReloadEvent);
            
            // 2. Establecer bandera en localStorage
            localStorage.setItem('force_reload_products', 'true');
            localStorage.setItem('force_reload_timestamp', Date.now().toString());
            
            // 3. Intentar llamar función directamente si está disponible
            if (window.opener && typeof window.opener.loadDynamicProducts === 'function') {
                window.opener.loadDynamicProducts();
                console.log('📤 [FORCE RELOAD] Función llamada directamente en opener');
            }
            
            // 4. Intentar en parent si está disponible
            if (window.parent && window.parent !== window && typeof window.parent.loadDynamicProducts === 'function') {
                window.parent.loadDynamicProducts();
                console.log('📤 [FORCE RELOAD] Función llamada directamente en parent');
            }
            
            // 5. Intentar en todas las ventanas abiertas
            try {
                // Buscar todas las ventanas que puedan tener la función
                if (typeof window.loadDynamicProducts === 'function') {
                    window.loadDynamicProducts();
                    console.log('📤 [FORCE RELOAD] Función llamada en ventana actual');
                }
            } catch (e) {
                console.log('⚠️ No se pudo llamar loadDynamicProducts localmente');
            }
            
        console.log('✅ [FORCE RELOAD] Recarga forzada ejecutada');
        
    } catch (error) {
        console.error('❌ [FORCE RELOAD] Error forzando recarga:', error);
    }
}

// =================== SISTEMA DE RECUPERACIÓN ===================
recoverLostProducts() {
    console.log('🔍 [RECOVERY] Verificando productos perdidos...');
    
    try {
        const currentProducts = JSON.parse(localStorage.getItem('products')) || [];
        const backup = JSON.parse(localStorage.getItem('products_backup')) || null;
        const savedIds = JSON.parse(localStorage.getItem('product_ids')) || [];
        
        console.log('📊 [RECOVERY] Estado actual:');
        console.log('  - Productos actuales:', currentProducts.length);
        console.log('  - Productos en respaldo:', backup?.count || 0);
        console.log('  - IDs guardados:', savedIds.length);
        
        // Verificar si hay productos perdidos
        if (backup && backup.count > currentProducts.length) {
            console.log('🚨 [RECOVERY] ¡PRODUCTOS PERDIDOS DETECTADOS!');
            console.log(`  - Se perdieron ${backup.count - currentProducts.length} productos`);
            
            // Preguntar si recuperar
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '🔄 Productos Perdidos Detectados',
                    html: `
                        <div class="text-start">
                            <p><strong>Productos actuales:</strong> ${currentProducts.length}</p>
                            <p><strong>Productos en respaldo:</strong> ${backup.count}</p>
                            <p><strong>Productos perdidos:</strong> ${backup.count - currentProducts.length}</p>
                            <hr>
                            <p class="text-danger">¿Deseas recuperar los productos del respaldo?</p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Recuperar',
                    cancelButtonText: 'No, Mantener Actual'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.executeRecovery(backup.products);
                    }
                });
            } else {
                // Sin SweetAlert, recuperar automáticamente
                this.executeRecovery(backup.products);
            }
        } else {
            console.log('✅ [RECOVERY] No hay productos perdidos');
        }
        
    } catch (error) {
        console.error('❌ [RECOVERY] Error en recuperación:', error);
    }
}

executeRecovery(backupProducts) {
    console.log('🔄 [RECOVERY] Ejecutando recuperación...');
    
    this.products = backupProducts;
    this.saveProducts();
    this.loadProducts();
    this.updateStats();
    
    console.log(`✅ [RECOVERY] ${backupProducts.length} productos recuperados exitosamente`);
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '✅ Recuperación Exitosa',
            text: `Se recuperaron ${backupProducts.length} productos del respaldo`,
            icon: 'success',
            timer: 3000
        });
    }
}    // =================== CATEGORÍAS ===================
    loadCategories() {
        this.renderCategoriesList();
        this.renderNavigationFilters();
    }

    renderCategoriesList() {
        const container = document.getElementById('categoriesList');
        
        const categoriesHTML = this.categories.map(category => `
            <div class="d-flex justify-content-between align-items-center p-3 mb-2 border rounded">
                <div>
                    <div class="fw-bold">${category.name}</div>
                    <small class="text-muted">${category.slug}</small>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="adminSystem.editCategory('${category.id}')" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-outline-danger" onclick="adminSystem.deleteCategory('${category.id}')" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        container.innerHTML = categoriesHTML;
    }

    renderNavigationFilters() {
        const container = document.getElementById('navigationFilters');
        
        const filtersHTML = `
            <div class="mb-3">
                <h6 class="fw-bold">Filtros Activos</h6>
                <div class="d-flex flex-wrap gap-2">
                    ${this.categories.map(cat => `
                        <span class="badge bg-secondary">${cat.name}</span>
                    `).join('')}
                </div>
            </div>
            <button class="btn btn-admin-primary btn-sm" onclick="adminSystem.updateNavigationFilters()">
                <i class="fas fa-sync me-2"></i>Actualizar Filtros
            </button>
        `;

        container.innerHTML = filtersHTML;
    }

    showAddCategoryModal() {
        Swal.fire({
            title: 'Agregar Nueva Categoría',
            html: `
                <form id="addCategoryForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="categoryName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Género</label>
                        <select class="form-select" id="categoryGender" required>
                            <option value="">Seleccionar</option>
                            <option value="hombre">Hombre</option>
                            <option value="mujer">Mujer</option>
                            <option value="unisex">Unisex</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug (URL amigable)</label>
                        <input type="text" class="form-control" id="categorySlug" placeholder="Se genera automáticamente">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Agregar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const name = document.getElementById('categoryName').value;
                const gender = document.getElementById('categoryGender').value;
                let slug = document.getElementById('categorySlug').value;

                if (!name || !gender) {
                    Swal.showValidationMessage('Por favor completa todos los campos requeridos');
                    return false;
                }

                // Generar slug si no se proporcionó
                if (!slug) {
                    slug = name.toLowerCase()
                        .replace(/[áäàâã]/g, 'a')
                        .replace(/[éëèê]/g, 'e')
                        .replace(/[íïìî]/g, 'i')
                        .replace(/[óöòôõ]/g, 'o')
                        .replace(/[úüùû]/g, 'u')
                        .replace(/[ñ]/g, 'n')
                        .replace(/[^a-z0-9]/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '') + '-' + gender;
                }

                return { name, gender, slug };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.addCategory(result.value);
            }
        });

        // Auto-generar slug al escribir el nombre
        document.addEventListener('input', function(e) {
            if (e.target.id === 'categoryName') {
                const gender = document.getElementById('categoryGender').value;
                if (gender) {
                    const slug = e.target.value.toLowerCase()
                        .replace(/[áäàâã]/g, 'a')
                        .replace(/[éëèê]/g, 'e')
                        .replace(/[íïìî]/g, 'i')
                        .replace(/[óöòôõ]/g, 'o')
                        .replace(/[úüùû]/g, 'u')
                        .replace(/[ñ]/g, 'n')
                        .replace(/[^a-z0-9]/g, '-')
                        .replace(/-+/g, '-')
                        .replace(/^-|-$/g, '') + '-' + gender;
                    document.getElementById('categorySlug').value = slug;
                }
            }
        });
    }

    addCategory(categoryData) {
        const newCategory = {
            id: this.generateId('cat'),
            name: categoryData.name,
            slug: categoryData.slug,
            gender: categoryData.gender,
            created_at: Date.now()
        };

        this.categories.push(newCategory);
        this.saveCategories();
        this.loadCategories();
        this.updateStats();

        Swal.fire('¡Éxito!', 'Categoría agregada correctamente', 'success');
    }

    editCategory(categoryId) {
        const category = this.categories.find(c => c.id === categoryId);
        if (!category) return;

        Swal.fire({
            title: 'Editar Categoría',
            html: `
                <form id="editCategoryForm">
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" class="form-control" id="editCategoryName" value="${category.name}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Género</label>
                        <select class="form-select" id="editCategoryGender" required>
                            <option value="hombre" ${category.gender === 'hombre' ? 'selected' : ''}>Hombre</option>
                            <option value="mujer" ${category.gender === 'mujer' ? 'selected' : ''}>Mujer</option>
                            <option value="unisex" ${category.gender === 'unisex' ? 'selected' : ''}>Unisex</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug (URL amigable)</label>
                        <input type="text" class="form-control" id="editCategorySlug" value="${category.slug}">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const name = document.getElementById('editCategoryName').value;
                const gender = document.getElementById('editCategoryGender').value;
                const slug = document.getElementById('editCategorySlug').value;

                if (!name || !gender || !slug) {
                    Swal.showValidationMessage('Por favor completa todos los campos');
                    return false;
                }

                return { name, gender, slug };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                this.updateCategory(categoryId, result.value);
            }
        });
    }

    updateCategory(categoryId, categoryData) {
        const categoryIndex = this.categories.findIndex(c => c.id === categoryId);
        if (categoryIndex === -1) return;

        this.categories[categoryIndex] = {
            ...this.categories[categoryIndex],
            ...categoryData,
            updated_at: Date.now()
        };

        this.saveCategories();
        this.loadCategories();

        Swal.fire('¡Éxito!', 'Categoría actualizada correctamente', 'success');
    }

    deleteCategory(categoryId) {
        const category = this.categories.find(c => c.id === categoryId);
        if (!category) return;

        // Verificar si hay productos usando esta categoría
        const productsUsingCategory = this.products.filter(p => p.category === category.slug);

        if (productsUsingCategory.length > 0) {
            Swal.fire({
                title: 'No se puede eliminar',
                html: `Esta categoría está siendo utilizada por ${productsUsingCategory.length} producto(s). 
                       <br><br>¿Deseas eliminar la categoría y actualizar los productos afectados?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, eliminar y actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Actualizar productos afectados
                    this.products.forEach(product => {
                        if (product.category === category.slug) {
                            product.category = 'sin-categoria';
                        }
                    });
                    this.saveProducts();

                    // Eliminar categoría
                    this.performDeleteCategory(categoryId);
                }
            });
        } else {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.performDeleteCategory(categoryId);
                }
            });
        }
    }

    performDeleteCategory(categoryId) {
        this.categories = this.categories.filter(c => c.id !== categoryId);
        this.saveCategories();
        this.loadCategories();
        this.updateStats();

        Swal.fire('¡Eliminada!', 'La categoría ha sido eliminada', 'success');
    }

    // =================== PEDIDOS ===================
    loadOrders() {
        const tbody = document.getElementById('ordersTableBody');
        
        if (this.orders.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                        <p>No hay pedidos registrados</p>
                    </td>
                </tr>
            `;
            return;
        }

        const ordersHTML = this.orders.map(order => `
            <tr>
                <td>#${order.id}</td>
                <td>
                    <div class="fw-bold">${order.customer.name}</div>
                    <small class="text-muted">${order.customer.email}</small>
                </td>
                <td>
                    <small>${order.items.length} productos</small>
                </td>
                <td class="fw-bold text-success">$${this.formatPrice(order.total)}</td>
                <td>${this.formatDate(order.created_at)}</td>
                <td>
                    <span class="badge ${this.getOrderStatusColor(order.status)}">
                        ${this.getOrderStatusText(order.status)}
                    </span>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="adminSystem.viewOrder('${order.id}')" title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="adminSystem.updateOrderStatus('${order.id}')" title="Actualizar estado">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        tbody.innerHTML = ordersHTML;
    }

    viewOrder(orderId) {
        const order = this.orders.find(o => o.id === orderId);
        if (!order) return;

        const orderDetails = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Información del Cliente</h6>
                    <p><strong>Nombre:</strong> ${order.customer.name}</p>
                    <p><strong>Email:</strong> ${order.customer.email}</p>
                    <p><strong>Teléfono:</strong> ${order.customer.phone}</p>
                    <p><strong>Dirección:</strong> ${order.customer.address}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="fw-bold">Información del Pedido</h6>
                    <p><strong>Fecha:</strong> ${this.formatDate(order.created_at)}</p>
                    <p><strong>Estado:</strong> ${this.getOrderStatusText(order.status)}</p>
                    <p><strong>Total:</strong> $${this.formatPrice(order.total)}</p>
                </div>
            </div>
            <hr>
            <h6 class="fw-bold">Productos</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${order.items.map(item => `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.quantity}</td>
                                <td>$${this.formatPrice(item.price)}</td>
                                <td>$${this.formatPrice(item.price * item.quantity)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        Swal.fire({
            title: `Pedido #${order.id}`,
            html: orderDetails,
            width: 800,
            showCloseButton: true,
            showConfirmButton: false
        });
    }

    // =================== UTILIDADES ===================
    setupEventListeners() {
        // Drag & Drop para imágenes
        const uploadContainer = document.querySelector('.image-upload-container');
        if (uploadContainer) {
            uploadContainer.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadContainer.classList.add('dragover');
            });

            uploadContainer.addEventListener('dragleave', () => {
                uploadContainer.classList.remove('dragover');
            });

            uploadContainer.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadContainer.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const fileInput = document.getElementById('productImage');
                    fileInput.files = files;
                    previewImage(fileInput, 'imagePreview');
                }
            });
        }
    }

    updateDateTime() {
        const now = new Date();
        const dateStr = now.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('currentDate').textContent = dateStr;
    }

    formatDate(timestamp) {
        return new Date(timestamp).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    generateId(prefix = 'item') {
        return prefix + '_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    imageExists(imagePath) {
        try {
            // Crear una imagen temporal para verificar si existe
            const img = new Image();
            img.src = imagePath;
            
            // Para verificación síncrona, simplemente retornamos true por defecto
            // La validación real se hace cuando la imagen se carga
            return true;
        } catch (error) {
            console.warn(`❌ Error validando imagen: ${imagePath}`, error);
            return false;
        }
    }

    getCategoryName(slug) {
        const category = this.categories.find(c => c.slug === slug);
        return category ? category.name : slug;
    }

    getStatusText(status) {
        const statusMap = {
            'active': 'Activo',
            'inactive': 'Inactivo',
            'out_of_stock': 'Agotado'
        };
        return statusMap[status] || status;
    }

    getOrderStatusColor(status) {
        const colorMap = {
            'pending': 'bg-warning',
            'processing': 'bg-info',
            'completed': 'bg-success',
            'cancelled': 'bg-danger'
        };
        return colorMap[status] || 'bg-secondary';
    }

    getOrderStatusText(status) {
        const textMap = {
            'pending': 'Pendiente',
            'processing': 'En proceso',
            'completed': 'Completado',
            'cancelled': 'Cancelado'
        };
        return textMap[status] || status;
    }

    showLoading(show) {
        console.log(`🔄 [SHOW LOADING] ${show ? 'Mostrando' : 'Ocultando'} loading...`);
        
        try {
            if (show) {
                // Iniciar timeout de seguridad
                this.startLoadingTimeout();
            } else {
                // Limpiar timeout si se está ocultando el loading
                this.clearLoadingTimeout();
            }

            // Manejar overlay general usando clases CSS
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                if (show) {
                    overlay.classList.remove('hide');
                    overlay.classList.add('show');
                } else {
                    overlay.classList.remove('show');
                    overlay.classList.add('hide');
                }
                console.log(`📱 [SHOW LOADING] Overlay ${show ? 'mostrado' : 'ocultado'} con clases`);
            } else {
                console.warn('⚠️ [SHOW LOADING] Overlay loadingOverlay no encontrado');
            }

            // Manejar botón específico de guardar producto
            const saveBtn = document.getElementById('saveProductBtn');
            if (saveBtn) {
                if (show) {
                    saveBtn.disabled = true;
                    saveBtn.classList.add('btn-loading');
                    // Guardar texto original si no se ha guardado
                    if (!saveBtn.getAttribute('data-original-text')) {
                        saveBtn.setAttribute('data-original-text', saveBtn.innerHTML);
                    }
                    saveBtn.innerHTML = '<span class="visually-hidden">Procesando...</span>';
                    console.log('🔘 [SHOW LOADING] Botón en estado de carga');
                } else {
                    saveBtn.disabled = false;
                    saveBtn.classList.remove('btn-loading');
                    saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
                    console.log('✅ [SHOW LOADING] Botón restaurado');
                }
            } else {
                console.warn('⚠️ [SHOW LOADING] Botón saveProductBtn no encontrado');
            }

            // Prevenir que se cierre el modal mientras se está procesando
            const modal = document.getElementById('addProductModal');
            if (modal) {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    if (show) {
                        modal.setAttribute('data-bs-backdrop', 'static');
                        modal.setAttribute('data-bs-keyboard', 'false');
                    } else {
                        modal.setAttribute('data-bs-backdrop', 'true');
                        modal.setAttribute('data-bs-keyboard', 'true');
                    }
                }
            }

            // Logging adicional para debug
            if (show) {
                console.log('🔍 [DEBUG] Elementos después de mostrar loading:');
                console.log('  - Overlay classes:', overlay?.className);
                console.log('  - Botón disabled:', saveBtn?.disabled);
                console.log('  - Botón classes:', saveBtn?.className);
            } else {
                console.log('🔍 [DEBUG] Elementos después de ocultar loading:');
                console.log('  - Overlay classes:', overlay?.className);
                console.log('  - Botón disabled:', saveBtn?.disabled);
                console.log('  - Botón innerHTML:', saveBtn?.innerHTML);
            }

        } catch (error) {
            console.error('❌ [SHOW LOADING] Error en showLoading:', error);
            // En caso de error, forzar que se oculte el loading
            if (!show) {
                this.forceHideLoading();
            }
        }
        
        console.log(`✅ [SHOW LOADING] Estado de loading ${show ? 'activado' : 'desactivado'} correctamente`);
    }

    // === FUNCIÓN DE EMERGENCIA PARA FORZAR CIERRE DE LOADING ===
    forceHideLoading() {
        console.log('🚨 [FORCE HIDE] Forzando cierre de loading...');
        
        try {
            // Ocultar overlay usando clases CSS
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.remove('show');
                overlay.classList.add('hide');
                // Fallback usando style como backup
                overlay.style.setProperty('display', 'none', 'important');
            }

            // Restaurar todos los botones
            const allButtons = document.querySelectorAll('button');
            allButtons.forEach(btn => {
                if (btn.classList.contains('btn-loading')) {
                    btn.disabled = false;
                    btn.classList.remove('btn-loading');
                    
                    // Restaurar texto original
                    const originalText = btn.getAttribute('data-original-text');
                    if (originalText) {
                        btn.innerHTML = originalText;
                    } else if (btn.id === 'saveProductBtn') {
                        btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
                    }
                }
                
                // Remover atributos de loading de todos los botones del formulario
                if (btn.closest('#addProductForm')) {
                    btn.disabled = false;
                    if (btn.innerHTML.includes('Procesando')) {
                        if (btn.id === 'saveProductBtn') {
                            btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
                        } else {
                            const originalText = btn.getAttribute('data-original-text');
                            if (originalText) {
                                btn.innerHTML = originalText;
                            }
                        }
                    }
                }
            });

            // Restaurar modal
            const modal = document.getElementById('addProductModal');
            if (modal) {
                modal.setAttribute('data-bs-backdrop', 'true');
                modal.setAttribute('data-bs-keyboard', 'true');
            }

            // Limpiar timeout si existe
            this.clearLoadingTimeout();

            console.log('✅ [FORCE HIDE] Loading forzadamente cerrado con clases CSS');
        } catch (error) {
            console.error('❌ [FORCE HIDE] Error forzando cierre:', error);
        }
    }

    // === SISTEMA DE TIMEOUT PARA LOADING ===
    startLoadingTimeout() {
        // Limpiar timeout previo si existe
        if (this.loadingTimeout) {
            clearTimeout(this.loadingTimeout);
        }

        // Crear nuevo timeout de 10 segundos
        this.loadingTimeout = setTimeout(() => {
            console.warn('⚠️ [TIMEOUT] Loading ha estado activo por más de 10 segundos, forzando cierre...');
            this.forceHideLoading();
            
            // Mostrar notificación de timeout
            Swal.fire({
                title: 'Tiempo agotado',
                text: 'La operación tardó más de lo esperado. Por favor intenta nuevamente.',
                icon: 'warning',
                confirmButtonText: 'Entendido'
            });
        }, 10000); // 10 segundos
    }

    clearLoadingTimeout() {
        if (this.loadingTimeout) {
            clearTimeout(this.loadingTimeout);
            this.loadingTimeout = null;
        }
    }

    // =================== DATOS ===================
    saveProducts() {
        // Guardar en localStorage principal
        localStorage.setItem('products', JSON.stringify(this.products));
        
        // Crear respaldo con timestamp
        const backup = {
            products: this.products,
            timestamp: Date.now(),
            count: this.products.length
        };
        localStorage.setItem('products_backup', JSON.stringify(backup));
        
        // Guardar lista de IDs para verificar integridad
        const productIds = this.products.map(p => p.id);
        localStorage.setItem('product_ids', JSON.stringify(productIds));
        
        // Log para debugging
        console.log(`💾 Guardando ${this.products.length} productos en localStorage`);
        console.log(`💾 Respaldo creado con timestamp: ${backup.timestamp}`);
        this.products.forEach(product => {
            console.log(`  - ${product.name} (ID: ${product.id})`);
        });
        
        // Sincronizar con el frontend
        this.syncWithFrontend();
        
        // Disparar evento para sincronización
        window.dispatchEvent(new CustomEvent('productsUpdated', {
            detail: { products: this.products }
        }));
    }

    syncWithFrontend() {
        // Esta función sincroniza los productos del panel con el frontend
        console.log('🔄 Sincronizando productos con frontend...');
        
        // Los productos están ahora disponibles en localStorage para el index.html
        // El sistema de productos dinámicos del index.html los cargará automáticamente
        
        // Trigger para forzar actualización en el index si está abierto
        try {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({
                    type: 'PRODUCTS_UPDATED',
                    products: this.products
                }, '*');
            }
        } catch (error) {
            // Silenciar errores de cross-origin
        }
    }

    saveCategories() {
        localStorage.setItem('categories', JSON.stringify(this.categories));
    }

    saveOrders() {
        localStorage.setItem('orders', JSON.stringify(this.orders));
    }

    getDefaultCategories() {
        return [
            // CATEGORÍAS HOMBRE
            { id: 'cat_1', name: 'Pantalones Hombre', slug: 'pantalones-hombre', gender: 'hombre', description: 'Pantalones elegantes y casuales para hombre' },
            { id: 'cat_2', name: 'Chaquetas Hombre', slug: 'chaquetas-hombre', gender: 'hombre', description: 'Chaquetas deportivas y elegantes para hombre' },
            { id: 'cat_3', name: 'Blazers Hombre', slug: 'blazers-hombre', gender: 'hombre', description: 'Blazers ejecutivos y formales para hombre' },
            { id: 'cat_4', name: 'Camisas Hombre', slug: 'camisas-hombre', gender: 'hombre', description: 'Camisas premium y formales para hombre' },
            
            // CATEGORÍAS MUJER
            { id: 'cat_5', name: 'Chaquetas Mujer', slug: 'chaqueta-mujer', gender: 'mujer', description: 'Chaquetas elegantes y modernas para mujer' },
            { id: 'cat_6', name: 'Tejidos Mujer', slug: 'tejidos-mujer', gender: 'mujer', description: 'Prendas tejidas de alta calidad para mujer' },
            { id: 'cat_7', name: 'Blusas Mujer', slug: 'blusas-mujer', gender: 'mujer', description: 'Blusas elegantes y casuales para mujer' }
        ];
    }

    getDefaultNavigation() {
        return {
            men: ['pantalones-hombre', 'chaquetas-hombre', 'blazers-hombre', 'camisas-hombre'],
            women: ['chaqueta-mujer', 'tejidos-mujer', 'blusas-mujer'],
            featured: [],
            footerLinks: [
                { name: 'Política de Privacidad', url: '#privacy' },
                { name: 'Términos y Condiciones', url: '#terms' },
                { name: 'Contacto', url: '#contact' }
            ]
        };
    }

    getDefaultProducts() {
        return [
            // PANTALONES HOMBRE (Solo productos únicos, sin duplicados)
            {
                id: 'pantalon-tela-galleta-verde',
                name: 'Pantalón Tela Galleta Slim Fit',
                description: 'Pantalón elegante de tela galleta, corte slim fit perfecto para uso formal y casual. Color Verde.',
                category: 'pantalones-hombre',
                price: 149999,
                sale_price: 99999,
                stock_quantity: 25,
                image: 'images/Pantalon Tela Galleta/Pantalón Tela Galleta Verde.jpeg',
                colors: ['Verde'],
                sizes: ['28', '30', '32', '34', '36'],
                status: 'active',
                discount: 50,
                reviews: 2158,
                sales_today: 12,
                created_at: new Date().toISOString()
            },
            {
                id: 'pantalon-drill-liso',
                name: 'Pantalón Drill Liso Slim Fit',
                description: 'Pantalón drill liso de alta calidad, perfecto para uso diario y profesional',
                category: 'pantalones-hombre',
                price: 149999,
                sale_price: 99999,
                stock_quantity: 18,
                image: 'images/Pantalon Drill Liso/Pantalon Drill Liso Gris.jpeg',
                colors: ['Azul', 'Negro', 'Beige', 'Gris'],
                sizes: ['28', '30', '32', '34', '36'],
                status: 'active',
                discount: 50,
                reviews: 2158,
                sales_today: 6,
                created_at: new Date().toISOString()
            },
            
            // CHAQUETAS HOMBRE
            {
                id: 'chaqueta-deportiva-blue-ox',
                name: 'Chaqueta Deportiva Blue Ox',
                description: 'Chaqueta deportiva premium con diseño moderno y materiales de alta calidad',
                category: 'chaquetas-hombre',
                price: 199999,
                sale_price: 139999,
                stock_quantity: 12,
                image: 'images/Chaqueta Deportiva Blue Ox/Chaqueta Deportiva Blue Ox Amarilla.jpeg',
                colors: ['Azul', 'Negro', 'Gris'],
                sizes: ['S', 'M', 'L', 'XL'],
                status: 'active',
                discount: 30,
                reviews: 1842,
                sales_today: 8,
                created_at: new Date().toISOString()
            },
            
            // BLAZERS HOMBRE
            {
                id: 'blazer-elegante-premium',
                name: 'Blazer Elegante Premium',
                description: 'Blazer ejecutivo de corte moderno, perfecto para presentaciones y eventos formales',
                category: 'blazers-hombre',
                price: 249999,
                sale_price: 189999,
                stock_quantity: 8,
                image: 'images/BLAZER.png',
                colors: ['Negro', 'Azul Marino', 'Gris'],
                sizes: ['S', 'M', 'L', 'XL'],
                status: 'active',
                discount: 25,
                reviews: 986,
                sales_today: 0,
                badge: 'Premium',
                created_at: new Date().toISOString()
            },
            
            // CAMISAS HOMBRE
            {
                id: 'camisa-premium-collection',
                name: 'Camisa Premium Collection',
                description: 'Camisa de alta calidad con acabados premium, perfecta para uso formal',
                category: 'camisas-hombre',
                price: 99999,
                sale_price: 79999,
                stock_quantity: 20,
                image: 'images/Camisa Blanco Purista/Camisa Blanco Purista.jpeg',
                colors: ['Blanco', 'Azul', 'Negro', 'Gris'],
                sizes: ['S', 'M', 'L', 'XL'],
                status: 'active',
                discount: 20,
                reviews: 1567,
                sales_today: 0,
                badge: 'Bestseller',
                created_at: new Date().toISOString()
            },
            
            // CHAQUETAS MUJER
            {
                id: 'chaqueta-moonlit-collection',
                name: 'Chaqueta Moonlit Collection',
                description: 'Chaqueta femenina de diseño exclusivo, perfecta para cualquier ocasión',
                category: 'chaqueta-mujer',
                price: 199999,
                sale_price: 129999,
                stock_quantity: 15,
                image: 'images/Chaqueta Moonlit Mauve/Chaqueta Moonlit Mauve.jpeg',
                colors: ['Mauve', 'Negro', 'Beige'],
                sizes: ['XS', 'S', 'M', 'L'],
                status: 'active',
                discount: 35,
                reviews: 2345,
                sales_today: 0,
                badge: 'Favorita',
                created_at: new Date().toISOString()
            }
        ];
    }

    // =================== NAVEGACIÓN ===================
    loadNavigation() {
        this.renderNavigationForm();
    }

    renderNavigationForm() {
        const menContainer = document.getElementById('menCategoriesForm');
        const womenContainer = document.getElementById('womenCategoriesForm');

        const menCategories = this.categories.filter(c => c.gender === 'hombre');
        const womenCategories = this.categories.filter(c => c.gender === 'mujer');

        menContainer.innerHTML = menCategories.map(category => `
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" value="${category.slug}" 
                       id="men_${category.id}" ${this.navigation.men.includes(category.slug) ? 'checked' : ''}>
                <label class="form-check-label" for="men_${category.id}">
                    ${category.name}
                </label>
            </div>
        `).join('');

        womenContainer.innerHTML = womenCategories.map(category => `
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" value="${category.slug}" 
                       id="women_${category.id}" ${this.navigation.women.includes(category.slug) ? 'checked' : ''}>
                <label class="form-check-label" for="women_${category.id}">
                    ${category.name}
                </label>
            </div>
        `).join('');
    }

    saveNavigation() {
        const menCheckboxes = document.querySelectorAll('#menCategoriesForm input[type="checkbox"]:checked');
        const womenCheckboxes = document.querySelectorAll('#womenCategoriesForm input[type="checkbox"]:checked');

        this.navigation = {
            men: Array.from(menCheckboxes).map(cb => cb.value),
            women: Array.from(womenCheckboxes).map(cb => cb.value)
        };

        localStorage.setItem('navigation', JSON.stringify(this.navigation));
        
        // Actualizar filtros en el sitio principal
        this.updateMainSiteFilters();

        Swal.fire('¡Éxito!', 'Configuración de navegación guardada', 'success');
    }

    updateMainSiteFilters() {
        // Disparar evento para que el sitio principal actualice sus filtros
        window.dispatchEvent(new CustomEvent('navigationUpdated', { 
            detail: this.navigation 
        }));
    }

    // =================== CONFIGURACIÓN ===================
    loadSettings() {
        this.loadStoreSettings();
    }

    loadStoreSettings() {
        const storeData = JSON.parse(localStorage.getItem('storeSettings')) || {};
        
        document.getElementById('storeName').value = storeData.name || 'M & A MODA ACTUAL';
        document.getElementById('storeSlogan').value = storeData.slogan || 'Estilo y Elegancia';
        document.getElementById('storePhone').value = storeData.phone || '573232212316';
        document.getElementById('storeInstagram').value = storeData.instagram || 'musa.arion';
    }

    saveStoreSettings() {
        const storeSettings = {
            name: document.getElementById('storeName').value,
            slogan: document.getElementById('storeSlogan').value,
            phone: document.getElementById('storePhone').value,
            instagram: document.getElementById('storeInstagram').value,
            updated_at: Date.now()
        };

        localStorage.setItem('storeSettings', JSON.stringify(storeSettings));

        // Actualizar el sitio principal si está abierto
        window.dispatchEvent(new CustomEvent('storeSettingsUpdated', { 
            detail: storeSettings 
        }));

        Swal.fire('¡Éxito!', 'Configuración de la tienda guardada', 'success');
    }

    // =================== HERRAMIENTAS DEL SISTEMA ===================
    exportData() {
        const data = {
            products: this.products,
            categories: this.categories,
            orders: this.orders,
            navigation: this.navigation,
            storeSettings: JSON.parse(localStorage.getItem('storeSettings')) || {},
            exportDate: new Date().toISOString()
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `musa-backup-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        Swal.fire('¡Éxito!', 'Datos exportados correctamente', 'success');
    }

    importData() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                try {
                    const data = JSON.parse(event.target.result);
                    
                    Swal.fire({
                        title: '¿Importar datos?',
                        text: 'Esto sobrescribirá todos los datos actuales',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, importar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.performImport(data);
                        }
                    });
                } catch (error) {
                    Swal.fire('Error', 'Archivo de respaldo inválido', 'error');
                }
            };
            reader.readAsText(file);
        };
        input.click();
    }

    performImport(data) {
        try {
            if (data.products) {
                this.products = data.products;
                this.saveProducts();
            }
            
            if (data.categories) {
                this.categories = data.categories;
                this.saveCategories();
            }
            
            if (data.orders) {
                this.orders = data.orders;
                this.saveOrders();
            }
            
            if (data.navigation) {
                this.navigation = data.navigation;
                localStorage.setItem('navigation', JSON.stringify(this.navigation));
            }
            
            if (data.storeSettings) {
                localStorage.setItem('storeSettings', JSON.stringify(data.storeSettings));
            }

            // Recargar la interfaz
            this.loadDashboard();
            this.updateStats();

            Swal.fire('¡Éxito!', 'Datos importados correctamente', 'success');
        } catch (error) {
            console.error('Error importing data:', error);
            Swal.fire('Error', 'Error al importar los datos', 'error');
        }
    }

    backupData() {
        this.exportData();
        
        // Simular respaldo en la nube (en producción sería real)
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Backup creado',
                text: 'El respaldo ha sido guardado localmente',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1000);
    }

    clearCache() {
        Swal.fire({
            title: '¿Limpiar caché?',
            text: 'Esto eliminará datos temporales y puede mejorar el rendimiento',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Limpiar caché (preservando datos importantes)
                const keysToPreserve = ['products', 'categories', 'orders', 'navigation', 'storeSettings', 'adminAuthenticated'];
                const allKeys = Object.keys(localStorage);
                
                allKeys.forEach(key => {
                    if (!keysToPreserve.includes(key)) {
                        localStorage.removeItem(key);
                    }
                });

                Swal.fire('¡Listo!', 'Caché limpiado correctamente', 'success');
            }
        });
    }
}

// =================== FUNCIONES GLOBALES ===================
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="preview-image" alt="Preview">`;
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function logout() {
    Swal.fire({
        title: '¿Cerrar sesión?',
        text: 'Se perderán los cambios no guardados',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar sesión',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            sessionStorage.removeItem('adminAuthenticated');
            sessionStorage.removeItem('adminLoginTime');
            window.location.href = 'index.html';
        }
    });
}

// Simulador de pedidos para demostración
function generateSampleOrder() {
    const sampleOrder = {
        id: 'ORD_' + Date.now(),
        customer: {
            name: 'Juan Pérez',
            email: 'juan@example.com',
            phone: '+57 300 123 4567',
            address: 'Calle 123 #45-67, Bogotá'
        },
        items: [
            {
                id: 'prod_1',
                name: 'Pantalón Elegante',
                price: 89000,
                quantity: 1
            }
        ],
        total: 89000,
        status: 'pending',
        created_at: Date.now()
    };

    adminSystem.orders.push(sampleOrder);
    adminSystem.saveOrders();
    adminSystem.updateStats();
    
    if (adminSystem.currentSection === 'orders') {
        adminSystem.loadOrders();
    }
}

// =================== INICIALIZACIÓN ===================
let adminSystem;

// =================== FUNCIONES ADICIONALES DEL SISTEMA ===================

// Función global para mostrar modal de agregar categoría
window.showAddCategoryModal = function() {
    if (window.adminSystem) {
        window.adminSystem.showAddCategoryModal();
    }
};

// === FUNCIONES GLOBALES DE EMERGENCIA ===
// Función para forzar cierre de loading en casos extremos
window.forceCloseLoading = function() {
    console.log('🚨 [EMERGENCY] Función de emergencia activada por el usuario');
    
    if (window.adminSystem) {
        window.adminSystem.forceHideLoading();
        console.log('✅ [EMERGENCY] Loading cerrado por función de emergencia');
    } else {
        // Método alternativo si adminSystem no está disponible
        console.log('⚠️ [EMERGENCY] AdminSystem no disponible, usando método alternativo');
        
        // Ocultar overlays
        const overlays = document.querySelectorAll('#loadingOverlay, .loading-overlay');
        overlays.forEach(overlay => overlay.style.display = 'none');
        
        // Restaurar botones
        const buttons = document.querySelectorAll('button.btn-loading, button[disabled]');
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('btn-loading');
            if (btn.id === 'saveProductBtn') {
                btn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
            }
        });
        
        console.log('✅ [EMERGENCY] Loading cerrado por método alternativo');
    }
    
    // Mostrar notificación
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Loading Cerrado',
            text: 'El estado de carga ha sido restablecido manualmente.',
            icon: 'info',
            timer: 2000,
            showConfirmButton: false
        });
    }
};

// Función para debug - mostrar estado del sistema
window.debugLoadingState = function() {
    console.log('🔍 [DEBUG] Estado actual del sistema:');
    
    const overlay = document.getElementById('loadingOverlay');
    const saveBtn = document.getElementById('saveProductBtn');
    
    console.log('📱 Overlay:', overlay ? overlay.style.display : 'No encontrado');
    console.log('🔘 Botón guardar:', {
        existe: !!saveBtn,
        deshabilitado: saveBtn?.disabled,
        tieneClaseLoading: saveBtn?.classList.contains('btn-loading'),
        texto: saveBtn?.textContent
    });
    
    const loadingButtons = document.querySelectorAll('.btn-loading');
    console.log(`🔘 Botones en loading: ${loadingButtons.length}`);
    
    if (window.adminSystem && window.adminSystem.loadingTimeout) {
        console.log('⏰ Timeout activo:', !!window.adminSystem.loadingTimeout);
    }
};

// Función global para guardar navegación
window.saveNavigation = function() {
    if (window.adminSystem) {
        window.adminSystem.saveNavigation();
    }
};

// Función global para guardar configuración de tienda
window.saveStoreSettings = function() {
    if (window.adminSystem) {
        window.adminSystem.saveStoreSettings();
    }
};

// Función global para exportar datos
window.exportData = function() {
    if (window.adminSystem) {
        window.adminSystem.exportData();
    }
};

// Función global para importar datos
window.importData = function() {
    if (window.adminSystem) {
        window.adminSystem.importData();
    }
};

// Función global para crear backup
window.backupData = function() {
    if (window.adminSystem) {
        window.adminSystem.backupData();
    }
};

// Función global para limpiar caché
window.clearCache = function() {
    if (window.adminSystem) {
        window.adminSystem.clearCache();
    }
};

// Función global para filtrar pedidos
window.filterOrders = function() {
    if (window.adminSystem) {
        window.adminSystem.filterOrders();
    }
};

// Función global para actualizar producto
window.updateProduct = function(productId) {
    if (window.adminSystem) {
        window.adminSystem.updateProduct(productId);
    }
};

// Función global para guardar producto
window.saveProduct = function() {
    if (window.adminSystem) {
        window.adminSystem.saveProduct();
    }
};

// =================== INICIALIZACIÓN GLOBAL ===================

// Función global para reset completo
window.resetCompleto = function() {
    Swal.fire({
        title: '⚠️ Reset Completo',
        text: 'Esto eliminará TODOS los datos y cargará los productos exactos del frontend. Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, resetear todo',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545'
    }).then((result) => {
        if (result.isConfirmed) {
            // Limpiar completamente localStorage
            localStorage.clear();
            
            Swal.fire({
                icon: 'success',
                title: '✅ Reset Completo',
                text: 'Datos limpiados. La página se recargará para aplicar los productos correctos del frontend.',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                // Recargar página para inicializar con datos limpios
                window.location.reload();
            });
        }
    });
};

// Función global para sincronizar con frontend
window.syncWithFrontend = function() {
    if (window.adminSystem) {
        Swal.fire({
            title: '🔄 Sincronizar con Frontend',
            text: 'Esto actualizará los productos del panel con los del sitio web. ¿Continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                // Recargar productos del frontend
                window.adminSystem.products = window.adminSystem.getDefaultProducts();
                window.adminSystem.categories = window.adminSystem.getDefaultCategories();
                
                // Guardar cambios
                window.adminSystem.saveProducts();
                window.adminSystem.saveCategories();
                
                // Recargar interfaz
                window.adminSystem.loadProducts();
                window.adminSystem.loadCategories();
                window.adminSystem.updateStats();
                
                Swal.fire({
                    icon: 'success',
                    title: '✅ Sincronización Completa',
                    text: 'Los productos y categorías se han sincronizado correctamente con el frontend',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    }
};

// Función para limpiar datos corruptos
function clearCorruptedData() {
    try {
        const products = localStorage.getItem('products');
        const categories = localStorage.getItem('categories');
        
        // Verificar si los datos están corruptos o no coinciden con el frontend
        if (products) {
            const parsed = JSON.parse(products);
            const expectedProductNames = [
                'Pantalón Tela Galleta Slim Fit',
                'Pantalón Drill Liso Slim Fit', 
                'Chaqueta Deportiva Blue Ox',
                'Blazer Elegante Premium',
                'Camisa Premium Collection',
                'Chaqueta Moonlit Collection'
            ];
            
            // Verificar si tiene los productos correctos del frontend
            const hasCorrectProducts = expectedProductNames.every(name => 
                parsed.some(p => p.name === name)
            );
            
            if (!Array.isArray(parsed) || !hasCorrectProducts || parsed.length !== 6) {
                console.log('🧹 Limpiando productos incorrectos, cargando productos del frontend...');
                localStorage.removeItem('products');
            }
        }
        
        if (categories) {
            const parsed = JSON.parse(categories);
            if (!Array.isArray(parsed) || parsed.some(c => !c.name || !c.slug)) {
                console.log('🧹 Limpiando categorías corruptas...');
                localStorage.removeItem('categories');
            }
        }
    } catch (error) {
        console.log('🧹 Limpiando localStorage corrupto...');
        localStorage.removeItem('products');
        localStorage.removeItem('categories');
        localStorage.removeItem('navigation');
    }
}

// Función global para mostrar modal de agregar producto
window.showAddProductModal = function() {
    console.log('🔧 showAddProductModal() llamada');
    if (window.adminSystem) {
        console.log('✅ adminSystem encontrado, llamando showAddProductModal()');
        window.adminSystem.showAddProductModal();
    } else {
        console.error('❌ window.adminSystem no está disponible');
        // Fallback: intentar crear el modal directamente
        try {
            const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
            document.getElementById('addProductForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
            modal.show();
            console.log('✅ Modal abierto como fallback');
        } catch (error) {
            console.error('❌ Error abriendo modal como fallback:', error);
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('🚀 Iniciando AdminSystem...');
        
        // Limpiar datos corruptos si es necesario
        clearCorruptedData();
        
        window.adminSystem = new AdminSystem();
        console.log('✅ AdminSystem iniciado correctamente');
        
        // Escuchar eventos del sitio principal
        window.addEventListener('newOrder', function(e) {
            if (window.adminSystem) {
                window.adminSystem.orders = JSON.parse(localStorage.getItem('orders')) || [];
                window.adminSystem.updateStats();
                
                if (window.adminSystem.currentSection === 'orders') {
                    window.adminSystem.loadOrders();
                }
                
                // Mostrar notificación de nuevo pedido
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: '¡Nuevo pedido!',
                        text: `Pedido #${e.detail.id} recibido`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            }
        });
        
        // Agregar botón de prueba (solo en desarrollo)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            document.body.insertAdjacentHTML('beforeend', `
                <button onclick="generateSampleOrder()" 
                        style="position: fixed; bottom: 20px; left: 20px; z-index: 1000;"
                        class="btn btn-sm btn-secondary">
                    Generar Pedido de Prueba
                </button>
            `);
        }
        
    } catch (error) {
        console.error('❌ Error al inicializar AdminSystem:', error);
        
        // Mostrar error amigable al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de Inicialización',
                text: 'Hubo un problema al cargar el panel administrativo. Por favor, recarga la página.',
                confirmButtonText: 'Recargar',
                allowOutsideClick: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            alert('Error al cargar el panel. Por favor, recarga la página.');
            window.location.reload();
        }
    }
});
