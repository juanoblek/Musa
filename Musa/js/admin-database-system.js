/**
 * ====================================================
 * 🎨 ADMIN PANEL CON BASE DE DATOS - M & A MODA ACTUAL
 * ====================================================
 */

console.log('🔧 Cargando admin-database-system.js versión 20250828_02 - CON CORRECCIÓN DE IMÁGENES');

// Configuración de la API
const API_CONFIG = {
    baseURL: window.location.hostname === 'localhost' 
        ? 'http://localhost/Musa/api/' 
        : 'https://musaarion.com/api/',
    timeout: 10000,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

class DatabaseAdminSystem {
    constructor() {
        console.log('🔧 Iniciando DatabaseAdminSystem...');
        this.init();
    }

    async init() {
        try {
            await this.loadProducts();
            await this.loadCategories();
            this.setupEventListeners();
            console.log('✅ DatabaseAdminSystem iniciado correctamente');
        } catch (error) {
            console.error('❌ Error inicializando DatabaseAdminSystem:', error);
            this.showError('Error inicializando el sistema administrativo');
        }
    }

    /**
     * =================== PRODUCTOS ===================
     */
    
    async loadProducts() {
        console.log('📦 Cargando productos desde base de datos...');
        
        try {
            const response = await this.apiRequest('GET', 'productos.php');
            
            console.log('🔍 DEBUG - Respuesta completa de la API:', response);
            console.log('🔍 DEBUG - response.success:', response.success);
            console.log('🔍 DEBUG - response.products:', response.products);
            console.log('🔍 DEBUG - response.data:', response.data);
            
            // CORREGIR: El API devuelve directamente response.products, no response.success
            if (response.products && Array.isArray(response.products)) {
                this.products = response.products;
                console.log('🔍 DEBUG - Productos asignados:', this.products);
                this.renderProductsList();
                console.log(`✅ ${this.products.length} productos cargados desde base de datos`);
            } else if (response.success && response.data?.products) {
                // Fallback para otros formatos de respuesta
                this.products = response.data.products;
                this.renderProductsList();
                console.log(`✅ ${this.products.length} productos cargados desde base de datos`);
            } else {
                console.error('❌ API Response error:', response.error);
                throw new Error(response.error || 'Error obteniendo productos');
            }
        } catch (error) {
            console.error('❌ Error cargando productos:', error);
            this.showError('Error cargando productos: ' + error.message);
        }
    }

    /**
     * Subir imagen al servidor
     */
    async uploadImage(file) {
        console.log('📤 Subiendo imagen:', file.name);
        
        const formData = new FormData();
        formData.append('image', file);
        
        try {
            const response = await fetch(`${API_CONFIG.baseURL}images.php`, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            
            if (result.success) {
                console.log('✅ Imagen subida correctamente:', result.data);
                return result.data.image_url;
            } else {
                throw new Error(result.error || 'Error subiendo imagen');
            }
            
        } catch (error) {
            console.error('❌ Error subiendo imagen:', error);
            throw error;
        }
    }

    renderProductsList() {
        const container = document.getElementById('productsTableBody');
        if (!container) {
            console.warn('⚠️ Container productsTableBody no encontrado');
            return;
        }

        if (!this.products || this.products.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="alert alert-info">
                            <i class="fas fa-box-open fa-2x mb-3"></i><br>
                            <h6>No hay productos</h6>
                            <p class="mb-0">Agrega tu primer producto usando el botón "Nuevo Producto"</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        container.innerHTML = this.products.map(product => {
            // Normalizar ruta de imagen
            const normalizeImagePath = (imagePath) => {
                if (!imagePath) return './images/placeholder.svg';
                
                // Si ya es una URL completa, devolverla
                if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
                    return imagePath;
                }
                
                // Si es una ruta de uploads, usar la ruta correcta
                if (imagePath.startsWith('uploads/')) {
                    return `./${imagePath}`;
                }
                
                // Si ya comienza con images/, solo agregar el prefijo
                if (imagePath.startsWith('images/')) {
                    return `./${imagePath}`;
                }
                
                // Si no tiene prefijo, asumir que es images/
                return `./images/${imagePath}`;
            };

            const imageSrc = normalizeImagePath(product.main_image);
            
            return `
            <tr>
                <td>
                    <img src="${imageSrc}" 
                         alt="${product.name}" 
                         class="img-thumbnail" 
                         style="width: 50px; height: 50px; object-fit: cover;"
                         onerror="this.onerror=null; this.src='./images/placeholder.svg'">
                </td>
                <td>
                    <strong>${product.name}</strong>
                    ${product.short_description ? `<br><small class="text-muted">${product.short_description}</small>` : ''}
                </td>
                <td>
                    <span class="badge bg-primary">${product.category_name || 'Sin categoría'}</span>
                </td>
                <td>
                    <div class="price-display">
                        ${product.sale_price ? 
                            `<span class="text-danger fw-bold">$${this.formatPrice(product.sale_price)}</span><br>
                             <small class="text-decoration-line-through text-muted">$${this.formatPrice(product.price)}</small>` :
                            `<span class="fw-bold">$${this.formatPrice(product.price)}</span>`
                        }
                    </div>
                </td>
                <td>
                    <span class="badge ${product.stock_quantity > 10 ? 'bg-success' : product.stock_quantity > 0 ? 'bg-warning' : 'bg-danger'}">
                        ${product.stock_quantity}
                    </span>
                </td>
                <td>
                    <span class="badge ${this.getStatusBadgeClass(product.status)}">
                        ${this.getStatusText(product.status)}
                    </span>
                    ${product.gender ? `<br><small class="text-muted">${product.gender}</small>` : ''}
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="window.databaseAdmin.editProduct('${product.id}')" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct('${product.id}')" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        // Actualizar estadísticas
        this.updateDashboardStats();
    }

    updateDashboardStats() {
        // Actualizar contador de productos
        const totalProductsEl = document.getElementById('totalProducts');
        if (totalProductsEl && this.products) {
            totalProductsEl.textContent = this.products.length;
        }

        // Actualizar contador de categorías
        const totalCategoriesEl = document.getElementById('totalCategories');
        if (totalCategoriesEl && this.categories) {
            totalCategoriesEl.textContent = this.categories.length;
        }
    }

    async showAddProductModal() {
        console.log('🔧 Mostrando modal de agregar producto...');
        
        const modal = document.getElementById('addProductModal');
        if (modal) {
            const bsModal = new bootstrap.Modal(modal);
            
            // Limpiar formulario
            const form = document.getElementById('addProductForm');
            if (form) {
                form.reset();
                
                // Cargar categorías en el select
                const categorySelect = document.getElementById('productCategory');
                if (categorySelect && this.categories) {
                    categorySelect.innerHTML = '<option value="">Seleccionar categoría...</option>' +
                        this.categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
                }

                // Limpiar preview de imagen
                const imagePreview = document.getElementById('imagePreview');
                if (imagePreview) {
                    imagePreview.innerHTML = '';
                }
            }
            
            bsModal.show();
        }
    }

    async saveProduct() {
        console.log('💾 Guardando producto con imagen en base de datos...');
        
        try {
            this.showLoading(true);
            
            // Obtener datos del formulario
            const productData = this.getProductFormData();
            if (!productData) {
                throw new Error('Datos del formulario inválidos');
            }

            // Verificar si hay imagen para subir
            const imageFile = document.getElementById('productImage')?.files[0];
            
            let response;
            
            if (imageFile) {
                console.log('� Subiendo imagen:', imageFile.name);
                
                // Crear FormData para incluir la imagen
                const formData = new FormData();
                
                // Agregar todos los datos del producto
                Object.keys(productData).forEach(key => {
                    if (Array.isArray(productData[key])) {
                        formData.append(key, JSON.stringify(productData[key]));
                    } else {
                        formData.append(key, productData[key]);
                    }
                });
                
                // Agregar la imagen
                formData.append('main_image', imageFile);
                
                // Enviar con FormData (no JSON)
                response = await fetch(`${API_CONFIG.baseURL}productos.php`, {
                    method: 'POST',
                    body: formData // No establecer Content-Type, el navegador lo hace automáticamente
                });
                
            } else {
                console.log('📋 Guardando producto sin imagen');
                response = await this.apiRequest('POST', 'productos.php', productData);
            }

            // Procesar respuesta
            let result;
            
            if (imageFile) {
                // Para FormData, manejar respuesta directamente
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Error response:', errorText);
                    throw new Error('Error del servidor: ' + response.status);
                }
                result = await response.json();
            } else {
                // Para JSON normal, usar el resultado de apiRequest
                result = response;
            }
            
            if (result.success) {
                await this.loadProducts(); // Recargar productos
                await this.notifyFrontend(); // Notificar al frontend
                
                this.hideModal('addProductModal');
                this.showSuccess('Producto creado exitosamente con imagen');
                
                console.log('✅ Producto guardado exitosamente:', result.product || result);
            } else {
                throw new Error(result.error || result.message || 'Error guardando producto');
            }
            
        } catch (error) {
            console.error('❌ Error guardando producto:', error);
            this.showError('Error guardando producto: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    getProductFormData() {
        // Obtener datos directamente de los inputs por ID
        const data = {};
        
        // Campos básicos
        data.name = document.getElementById('productName')?.value?.trim();
        data.description = document.getElementById('productDescription')?.value?.trim() || '';
        data.price = parseFloat(document.getElementById('productPrice')?.value) || 0;
        data.sale_price = document.getElementById('productSalePrice')?.value ? 
                         parseFloat(document.getElementById('productSalePrice').value) : null;
        data.stock_quantity = parseInt(document.getElementById('productStock')?.value) || 0;
        // CORREGIR: El API espera 'category_id', no 'category'
        data.category_id = document.getElementById('productCategory')?.value || '';
        data.gender = document.getElementById('productGender')?.value || 'unisex';
        data.status = document.getElementById('productStatus')?.value || 'active';
        // CORREGIR: El API espera 'is_featured', no 'featured'
        data.is_featured = document.getElementById('hasDiscount')?.checked || false;

        // Validar campos requeridos
        if (!data.name || !data.price || !data.category_id) {
            this.showError('Por favor completa todos los campos requeridos: Nombre, Precio y Categoría');
            return null;
        }

        // Procesar tallas seleccionadas
        const sizes = [];
        document.querySelectorAll('input[type="checkbox"][id^="size"]:checked').forEach(checkbox => {
            sizes.push(checkbox.value);
        });
        data.sizes = sizes;

        // Procesar colores con códigos hexadecimales
        const colors = [];
        document.querySelectorAll('.color-input-group').forEach(group => {
            const hexInput = group.querySelector('.color-picker');
            const nameInput = group.querySelector('.color-name');
            if (hexInput && nameInput && nameInput.value.trim()) {
                colors.push({
                    nombre: nameInput.value.trim(),
                    codigo_hex: hexInput.value,
                    hex: hexInput.value // Para compatibilidad con frontend
                });
            }
        });
        data.colors = colors;

        console.log('🎨 Colores procesados:', colors);
        console.log('📏 Tallas procesadas:', sizes);

        // CORREGIR: El API espera 'images' como array, no 'main_image'
        data.images = ['images/placeholder.svg'];

        console.log('📦 Datos del formulario recolectados:', data);
        return data;
    }

    async deleteProduct(productId) {
        console.log('🗑️ Eliminando producto:', productId);
        
        const product = this.products.find(p => p.id === productId);
        if (!product) {
            this.showError('Producto no encontrado');
            return;
        }

        const result = await Swal.fire({
            title: '¿Eliminar producto?',
            text: `Se eliminará "${product.name}" permanentemente`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                this.showLoading(true);
                
                const response = await this.apiRequest('DELETE', `productos.php/${productId}`);
                
                if (response.success) {
                    await this.loadProducts(); // Recargar productos
                    await this.notifyFrontend(); // Notificar al frontend
                    
                    this.showSuccess('Producto eliminado exitosamente');
                    console.log('✅ Producto eliminado exitosamente');
                } else {
                    throw new Error(response.error || 'Error eliminando producto');
                }
                
            } catch (error) {
                console.error('❌ Error eliminando producto:', error);
                this.showError('Error eliminando producto: ' + error.message);
            } finally {
                this.showLoading(false);
            }
        }
    }

    /**
     * =================== CATEGORÍAS ===================
     */
    
    async loadCategories() {
        console.log('📂 Cargando categorías...');
        
        try {
            // Por ahora usar categorías hardcodeadas, después implementar API
            this.categories = [
                { id: 'hombre', name: 'Ropa para Hombre' },
                { id: 'mujer', name: 'Ropa para Mujer' },
                { id: 'camisas', name: 'Camisas' },
                { id: 'pantalones', name: 'Pantalones' },
                { id: 'chaquetas', name: 'Chaquetas' },
                { id: 'blazers', name: 'Blazers' },
                { id: 'accesorios', name: 'Accesorios' }
            ];
            
            console.log(`✅ ${this.categories.length} categorías cargadas`);
        } catch (error) {
            console.error('❌ Error cargando categorías:', error);
            this.categories = [];
        }
    }

    /**
     * =================== API REQUESTS ===================
     */
    
    async apiRequest(method, endpoint, data = null) {
        const url = `${API_CONFIG.baseURL}${endpoint}`;
        
        const config = {
            method: method,
            headers: API_CONFIG.headers,
            timeout: API_CONFIG.timeout
        };

        if (data && (method === 'POST' || method === 'PUT')) {
            config.body = JSON.stringify(data);
        }

        console.log(`🌐 API Request: ${method} ${url}`, data);

        try {
            const response = await fetch(url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const result = await response.json();
            console.log('📡 API Response:', result);
            
            return result;
        } catch (error) {
            console.error('❌ API Request failed:', error);
            throw error;
        }
    }

    /**
     * =================== FRONTEND SYNC ===================
     */
    
    async notifyFrontend() {
        console.log('📡 Notificando cambios al frontend...');
        
        try {
            // Disparar eventos personalizados para el frontend
            const events = ['productsUpdated', 'forceReloadProducts', 'databaseSync'];
            
            events.forEach(eventName => {
                const event = new CustomEvent(eventName, {
                    detail: {
                        source: 'database-admin',
                        timestamp: Date.now(),
                        products: this.products
                    }
                });
                window.dispatchEvent(event);
                console.log(`✅ Evento ${eventName} disparado`);
            });
            
            // También enviar mensaje a otras ventanas
            if (window.BroadcastChannel) {
                const channel = new BroadcastChannel('musa-products');
                channel.postMessage({
                    type: 'products-updated',
                    products: this.products,
                    timestamp: Date.now()
                });
                channel.close();
            }
            
        } catch (error) {
            console.error('❌ Error notificando frontend:', error);
        }
    }

    /**
     * =================== UTILIDADES ===================
     */
    
    formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price);
    }

    getStatusBadgeClass(status) {
        const classes = {
            'active': 'bg-success',
            'inactive': 'bg-secondary',
            'draft': 'bg-warning'
        };
        return classes[status] || 'bg-secondary';
    }

    getStatusText(status) {
        const texts = {
            'active': 'Activo',
            'inactive': 'Inactivo',
            'draft': 'Borrador'
        };
        return texts[status] || status;
    }

    showLoading(show) {
        const overlay = document.getElementById('loading-overlay');
        const saveBtn = document.getElementById('save-product-btn');
        
        if (overlay) {
            if (show) {
                overlay.classList.remove('d-none');
                overlay.style.display = 'flex';
            } else {
                overlay.classList.add('d-none');
                overlay.style.display = 'none';
            }
        }
        
        if (saveBtn) {
            saveBtn.disabled = show;
            if (show) {
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
            } else {
                saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Guardar Producto';
            }
        }
    }

    hideModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const bsModal = bootstrap.Modal.getInstance(modal);
            if (bsModal) {
                bsModal.hide();
            }
        }
    }

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }

    setupEventListeners() {
        // Event listeners para navegación
        const navLinks = document.querySelectorAll('[data-section]');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const section = e.target.closest('[data-section]').getAttribute('data-section');
                this.showSection(section);
                
                // Actualizar clases activas
                navLinks.forEach(nl => nl.classList.remove('active'));
                e.target.closest('[data-section]').classList.add('active');
            });
        });

        // Event listeners para el formulario
        const form = document.getElementById('product-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveProduct();
            });
        }

        // Event listener para el botón de guardar
        const saveBtn = document.getElementById('save-product-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveProduct();
            });
        }

        console.log('✅ Event listeners configurados');
    }

    /**
     * Mostrar sección específica del admin panel
     */
    showSection(sectionName) {
        console.log(`📱 Mostrando sección: ${sectionName}`);
        
        // Detener auto-refresh anterior si existe
        if (window.stopOrdersAutoRefresh) {
            window.stopOrdersAutoRefresh();
        }
        
        // Ocultar todas las secciones
        const sections = document.querySelectorAll('.content-section');
        sections.forEach(section => {
            section.classList.remove('active');
            section.style.display = 'none';
        });

        // Mostrar la sección solicitada
        const targetSection = document.getElementById(sectionName);
        if (targetSection) {
            targetSection.style.display = 'block';
            targetSection.classList.add('active');
            
            // Si es la sección de pedidos, iniciar auto-refresh
            if (sectionName === 'orders' && window.startOrdersAutoRefresh) {
                console.log('🔄 Iniciando auto-refresh de pedidos...');
                window.startOrdersAutoRefresh();
            }
        } else {
            // Si no existe la sección, mostrar dashboard por defecto
            const dashboardSection = document.getElementById('dashboard');
            if (dashboardSection) {
                dashboardSection.style.display = 'block';
                dashboardSection.classList.add('active');
            }
            console.warn(`⚠️ Sección '${sectionName}' no encontrada, mostrando dashboard`);
        }

        // Actualizar título si existe
        const pageTitle = document.querySelector('.page-title');
        if (pageTitle) {
            const sectionTitles = {
                'dashboard': 'Dashboard',
                'products': 'Gestión de Productos',
                'categories': 'Categorías',
                'orders': 'Pedidos',
                'navigation': 'Navegación',
                'settings': 'Configuración'
            };
            pageTitle.textContent = sectionTitles[sectionName] || 'Dashboard';
        }
    }
}

/**
 * =================== FUNCIONES GLOBALES ===================
 */

// Función global para mostrar modal de agregar producto
window.showAddProductModal = function() {
    if (window.databaseAdmin) {
        window.databaseAdmin.showAddProductModal();
    } else {
        console.error('❌ DatabaseAdminSystem no está disponible');
    }
};

// Función global para guardar producto
window.saveProduct = function() {
    if (window.databaseAdmin) {
        window.databaseAdmin.saveProduct();
    } else {
        console.error('❌ DatabaseAdminSystem no está disponible');
    }
};

// Función global para previsualizar imagen
window.previewImage = function(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validar tipo de archivo
        if (!file.type.startsWith('image/')) {
            preview.innerHTML = '<div class="alert alert-danger">Por favor selecciona un archivo de imagen válido.</div>';
            input.value = '';
            return;
        }
        
        // Validar tamaño (máximo 5MB)
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<div class="alert alert-danger">La imagen es demasiado grande. Máximo 5MB.</div>';
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="image-preview-container mt-3">
                    <img src="${e.target.result}" 
                         class="img-thumbnail" 
                         style="max-width: 200px; max-height: 200px; object-fit: cover;">
                    <div class="mt-2">
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> 
                            ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                        </small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" 
                            onclick="clearImagePreview('${input.id}', '${previewId}')">
                        <i class="fas fa-times"></i> Quitar imagen
                    </button>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
};

// Función global para limpiar vista previa de imagen
window.clearImagePreview = function(inputId, previewId) {
    document.getElementById(inputId).value = '';
    document.getElementById(previewId).innerHTML = '';
};

// Función global para eliminar producto
window.deleteProduct = function(productId) {
    if (window.databaseAdmin) {
        window.databaseAdmin.deleteProduct(productId);
    } else {
        console.error('❌ DatabaseAdminSystem no está disponible');
    }
};

/**
 * =================== INICIALIZACIÓN ===================
 */

document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('🚀 Iniciando DatabaseAdminSystem PURO (sin localStorage)...');
        window.databaseAdmin = new DatabaseAdminSystem();
        
        // También crear alias para compatibilidad
        window.adminSystem = window.databaseAdmin;
        
        console.log('✅ DatabaseAdminSystem iniciado correctamente (SOLO BASE DE DATOS)');
    } catch (error) {
        console.error('❌ Error inicializando DatabaseAdminSystem:', error);
    }
});
