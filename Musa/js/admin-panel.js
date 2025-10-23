/**
 * Enhanced Admin Panel with Full Backend Integration
 * Complete product management system
 */

class AdminPanel {
    constructor() {
        this.currentUser = null;
        this.products = [];
        this.categories = [];
        this.isLoggedIn = false;
        this.currentPage = 1;
        this.pageSize = 10;
        this.totalProducts = 0;
        
        this.init();
    }

    init() {
        this.checkAuthentication();
        this.setupEventListeners();
        this.loadCategories();
        console.log('🔐 Admin Panel initialized');
    }

    checkAuthentication() {
        const token = localStorage.getItem('admin_token');
        const user = localStorage.getItem('admin_user');
        
        if (token && user) {
            this.isLoggedIn = true;
            this.currentUser = JSON.parse(user);
            api.token = token;
        }
    }

    setupEventListeners() {
        // Login form
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'adminLoginForm') {
                e.preventDefault();
                this.handleLogin(e.target);
            }
        });

        // Product form
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'productForm') {
                e.preventDefault();
                this.handleProductSubmit(e.target);
            }
        });

        // Search products
        document.addEventListener('input', (e) => {
            if (e.target.id === 'productSearch') {
                this.debounce(this.searchProducts.bind(this), 300)(e.target.value);
            }
        });
    }

    async handleLogin(form) {
        const formData = new FormData(form);
        const credentials = {
            username: formData.get('username'),
            password: formData.get('password'),
            rememberMe: formData.get('rememberMe') === 'on'
        };

        try {
            const response = await api.login(credentials);
            if (response.success) {
                this.isLoggedIn = true;
                this.currentUser = response.user;
                this.closeLoginModal();
                this.showAdminDashboard();
                this.showSuccess('Login exitoso');
            }
        } catch (error) {
            this.showError('Error de login: ' + error.message);
        }
    }

    async handleProductSubmit(form) {
        const formData = new FormData(form);
        const productData = this.extractProductData(formData);

        try {
            const productId = formData.get('productId');
            let response;

            if (productId) {
                // Update existing product
                response = await api.updateProduct(productId, productData);
            } else {
                // Create new product
                response = await api.createProduct(productData);
            }

            if (response.success) {
                this.closeProductModal();
                this.loadProducts();
                this.showSuccess(productId ? 'Producto actualizado' : 'Producto creado');
            }
        } catch (error) {
            this.showError('Error al guardar producto: ' + error.message);
        }
    }

    extractProductData(formData) {
        const data = {};
        
        // Basic fields
        data.name = formData.get('name');
        data.description = formData.get('description');
        data.price = parseFloat(formData.get('price'));
        data.sale_price = formData.get('sale_price') ? parseFloat(formData.get('sale_price')) : null;
        data.sku = formData.get('sku');
        data.category_id = parseInt(formData.get('category_id'));
        data.gender = formData.get('gender');
        data.stock_quantity = parseInt(formData.get('stock_quantity')) || 0;
        data.featured = formData.get('featured') === 'on';
        data.meta_title = formData.get('meta_title');
        data.meta_description = formData.get('meta_description');

        // Images
        const images = formData.getAll('images');
        if (images.length > 0 && images[0].size > 0) {
            data.images = images;
        }

        // Colors
        const colors = this.extractColors(formData);
        if (colors.length > 0) {
            data.colors = colors;
        }

        // Sizes
        const sizes = this.extractSizes(formData);
        if (sizes.length > 0) {
            data.sizes = sizes;
        }

        return data;
    }

    extractColors(formData) {
        const colors = [];
        let i = 0;
        
        while (formData.get(`color_name_${i}`)) {
            colors.push({
                name: formData.get(`color_name_${i}`),
                code: formData.get(`color_code_${i}`),
                stock: parseInt(formData.get(`color_stock_${i}`)) || 0
            });
            i++;
        }
        
        return colors;
    }

    extractSizes(formData) {
        const sizes = [];
        let i = 0;
        
        while (formData.get(`size_name_${i}`)) {
            sizes.push({
                name: formData.get(`size_name_${i}`),
                stock: parseInt(formData.get(`size_stock_${i}`)) || 0
            });
            i++;
        }
        
        return sizes;
    }

    async loadCategories() {
        try {
            const response = await api.getCategories();
            if (response.success) {
                this.categories = response.categories;
                this.populateCategorySelect();
            }
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    async loadProducts(page = 1) {
        try {
            const params = {
                page: page,
                limit: this.pageSize
            };

            const response = await api.getAdminProducts(params);
            if (response.success) {
                this.products = response.products;
                this.totalProducts = response.pagination.total;
                this.currentPage = page;
                this.displayAdminProducts();
                this.updatePagination();
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showError('Error al cargar productos');
        }
    }

    async searchProducts(term) {
        if (!term.trim()) {
            this.loadProducts();
            return;
        }

        try {
            const params = {
                search: term,
                page: 1,
                limit: this.pageSize
            };

            const response = await api.getAdminProducts(params);
            if (response.success) {
                this.products = response.products;
                this.displayAdminProducts();
            }
        } catch (error) {
            console.error('Error searching products:', error);
        }
    }

    displayAdminProducts() {
        const container = document.getElementById('products-tbody');
        if (!container) return;

        if (this.products.length === 0) {
            container.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-box-seam display-4 text-muted"></i>
                        <h4 class="mt-3">No hay productos</h4>
                        <p class="text-muted">Agrega tu primer producto para comenzar</p>
                        <button class="btn btn-primary" onclick="adminPanel.showAddProductForm()">
                            <i class="bi bi-plus-circle"></i> Agregar Producto
                        </button>
                    </td>
                </tr>
            `;
            return;
        }

        container.innerHTML = this.products.map(product => {
            console.log('Renderizando producto:', product.name);
            console.log('Gender:', product.gender);
            console.log('Colors:', product.colors);
            console.log('Sizes:', product.sizes);
            
            return `
            <tr>
                <td><img src="${product.main_image || 'images/placeholder.jpg'}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover;"></td>
                <td>
                    <strong>${product.name}</strong><br>
                    <small>${product.description ? product.description.substring(0, 50) + '...' : 'Sin descripción'}</small>
                </td>
                <td>
                    ${product.sale_price ? 
                        `<span style="text-decoration: line-through;">$${this.formatPrice(product.price)}</span><br><span style="color: red;">$${this.formatPrice(product.sale_price)}</span>` : 
                        `$${this.formatPrice(product.price)}`
                    }
                </td>
                <td><span class="badge bg-primary">${product.category_name || 'Sin categoría'}</span></td>
                <td><span class="badge bg-info">${product.gender || 'Sin género'}</span></td>
                <td>
                    ${product.colors && Array.isArray(product.colors) && product.colors.length > 0 ? 
                        product.colors.map(color => `<span class="badge bg-secondary me-1">${color}</span>`).join('') : 
                        '<span class="text-muted">Sin colores</span>'
                    }
                </td>
                <td>
                    ${product.sizes && Array.isArray(product.sizes) && product.sizes.length > 0 ? 
                        product.sizes.map(size => `<span class="badge bg-dark me-1">${size}</span>`).join('') : 
                        '<span class="text-muted">Sin tallas</span>'
                    }
                </td>
                <td><span class="badge ${product.stock_quantity > 0 ? 'bg-success' : 'bg-danger'}">${product.stock_quantity}</span></td>
                <td>
                    <span class="badge ${product.status === 'active' ? 'bg-success' : 'bg-secondary'}">${product.status === 'active' ? 'Activo' : 'Inactivo'}</span>
                    ${product.is_featured ? '<br><span class="badge bg-warning">Destacado</span>' : ''}
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-primary" onclick="adminPanel.editProduct('${product.id}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="adminPanel.deleteProduct('${product.id}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    }

    updatePagination() {
        const paginationContainer = document.getElementById('adminPagination');
        if (!paginationContainer) return;

        const totalPages = Math.ceil(this.totalProducts / this.pageSize);
        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = '<nav><ul class="pagination justify-content-center">';
        
        // Previous button
        paginationHTML += `
            <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="adminPanel.loadProducts(${this.currentPage - 1})">Anterior</a>
            </li>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === this.currentPage || i === 1 || i === totalPages || (i >= this.currentPage - 1 && i <= this.currentPage + 1)) {
                paginationHTML += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="adminPanel.loadProducts(${i})">${i}</a>
                    </li>
                `;
            } else if (i === this.currentPage - 2 || i === this.currentPage + 2) {
                paginationHTML += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="adminPanel.loadProducts(${this.currentPage + 1})">Siguiente</a>
            </li>
        `;

        paginationHTML += '</ul></nav>';
        paginationContainer.innerHTML = paginationHTML;
    }

    populateCategorySelect() {
        const selects = document.querySelectorAll('select[name="category_id"]');
        selects.forEach(select => {
            select.innerHTML = '<option value="">Seleccionar categoría</option>';
            this.categories.forEach(category => {
                select.innerHTML += `<option value="${category.id}">${category.name}</option>`;
            });
        });
    }

    showAddProductForm() {
        this.clearProductForm();
        this.showProductModal('Agregar Producto');
    }

    async editProduct(productId) {
        const product = this.products.find(p => p.id === productId);
        if (!product) return;

        this.populateProductForm(product);
        this.showProductModal('Editar Producto');
    }

    async viewProduct(productId) {
        const product = this.products.find(p => p.id === productId);
        if (!product) return;

        // Show product details in a modal
        const modal = document.getElementById('productDetailModal');
        if (modal) {
            // Populate modal with product details
            document.getElementById('productDetailContent').innerHTML = this.generateProductDetailHTML(product);
            new bootstrap.Modal(modal).show();
        }
    }

    async deleteProduct(productId) {
        const product = this.products.find(p => p.id === productId);
        if (!product) return;

        const confirmed = await this.confirmDelete(product.name);
        if (!confirmed) return;

        try {
            const response = await api.deleteProduct(productId);
            if (response.success) {
                this.loadProducts();
                this.showSuccess('Producto eliminado exitosamente');
            }
        } catch (error) {
            this.showError('Error al eliminar producto: ' + error.message);
        }
    }

    generateProductDetailHTML(product) {
        return `
            <div class="row">
                <div class="col-md-6">
                    <div class="product-images">
                        ${product.images?.map(image => `
                            <img src="${window.getUnifiedProductImage ? window.getUnifiedProductImage(product) : image.image_url}" alt="${product.name}" 
                                 class="img-fluid mb-2 ${image.is_primary ? 'border border-primary' : ''}">
                        `).join('') || `<img src="${window.getUnifiedProductImage ? window.getUnifiedProductImage(product) : 'images/placeholder.jpg'}" alt="${product.name}" class="img-fluid mb-2">`}
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>${product.name}</h4>
                    <p class="text-muted">${product.description || 'Sin descripción'}</p>
                    <div class="mb-3">
                        <strong>Precio:</strong> 
                        ${product.sale_price ? `
                            <span class="text-decoration-line-through">$${this.formatPrice(product.price)}</span>
                            <span class="text-danger fw-bold">$${this.formatPrice(product.sale_price)}</span>
                        ` : `
                            $${this.formatPrice(product.price)}
                        `}
                    </div>
                    <div class="mb-3">
                        <strong>SKU:</strong> ${product.sku || 'N/A'}
                    </div>
                    <div class="mb-3">
                        <strong>Categoría:</strong> ${product.category_name || 'Sin categoría'}
                    </div>
                    <div class="mb-3">
                        <strong>Stock:</strong> ${product.stock_quantity}
                    </div>
                    <div class="mb-3">
                        <strong>Colores:</strong>
                        ${product.colors?.map(color => `
                            <span class="badge bg-secondary me-1">${color.color_name}</span>
                        `).join('') || 'No especificado'}
                    </div>
                    <div class="mb-3">
                        <strong>Tallas:</strong>
                        ${product.sizes?.map(size => `
                            <span class="badge bg-secondary me-1">${size.size_name}</span>
                        `).join('') || 'No especificado'}
                    </div>
                </div>
            </div>
        `;
    }

    clearProductForm() {
        const form = document.getElementById('productForm');
        if (form) {
            form.reset();
            document.getElementById('productId').value = '';
            this.clearColorsSizes();
        }
    }

    populateProductForm(product) {
        const form = document.getElementById('productForm');
        if (!form) return;

        // Basic fields
        form.name.value = product.name || '';
        form.description.value = product.description || '';
        form.price.value = product.price || '';
        form.sale_price.value = product.sale_price || '';
        form.sku.value = product.sku || '';
        form.category_id.value = product.category_id || '';
        form.gender.value = product.gender || '';
        form.stock_quantity.value = product.stock_quantity || '';
        form.featured.checked = product.featured || false;
        form.meta_title.value = product.meta_title || '';
        form.meta_description.value = product.meta_description || '';
        
        document.getElementById('productId').value = product.id;

        // Populate colors and sizes
        this.populateColorsAndSizes(product);
    }

    populateColorsAndSizes(product) {
        // Clear existing
        this.clearColorsSizes();

        // Add colors
        if (product.colors && product.colors.length > 0) {
            product.colors.forEach((color, index) => {
                this.addColorField(color.color_name, color.color_code, color.stock_quantity);
            });
        }

        // Add sizes
        if (product.sizes && product.sizes.length > 0) {
            product.sizes.forEach((size, index) => {
                this.addSizeField(size.size_name, size.stock_quantity);
            });
        }
    }

    addColorField(name = '', code = '', stock = '') {
        const container = document.getElementById('colorsContainer');
        if (!container) return;

        const index = container.children.length;
        const colorHTML = `
            <div class="color-item row mb-2">
                <div class="col-4">
                    <input type="text" class="form-control" name="color_name_${index}" 
                           placeholder="Nombre del color" value="${name}">
                </div>
                <div class="col-3">
                    <input type="color" class="form-control form-control-color" 
                           name="color_code_${index}" value="${code || '#000000'}">
                </div>
                <div class="col-3">
                    <input type="number" class="form-control" name="color_stock_${index}" 
                           placeholder="Stock" value="${stock}" min="0">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger" onclick="this.closest('.color-item').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', colorHTML);
    }

    addSizeField(name = '', stock = '') {
        const container = document.getElementById('sizesContainer');
        if (!container) return;

        const index = container.children.length;
        const sizeHTML = `
            <div class="size-item row mb-2">
                <div class="col-5">
                    <input type="text" class="form-control" name="size_name_${index}" 
                           placeholder="Talla" value="${name}">
                </div>
                <div class="col-5">
                    <input type="number" class="form-control" name="size_stock_${index}" 
                           placeholder="Stock" value="${stock}" min="0">
                </div>
                <div class="col-2">
                    <button type="button" class="btn btn-outline-danger" onclick="this.closest('.size-item').remove()">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', sizeHTML);
    }

    clearColorsSizes() {
        document.getElementById('colorsContainer').innerHTML = '';
        document.getElementById('sizesContainer').innerHTML = '';
    }

    showProductModal(title) {
        const modal = document.getElementById('productModal');
        if (modal) {
            document.querySelector('#productModal .modal-title').textContent = title;
            new bootstrap.Modal(modal).show();
        }
    }

    closeProductModal() {
        const modal = document.getElementById('productModal');
        if (modal) {
            bootstrap.Modal.getInstance(modal)?.hide();
        }
    }

    showAdminDashboard() {
        console.log('Showing admin dashboard');
        // Load products when dashboard is shown
        this.loadProducts();
    }

    closeLoginModal() {
        const modal = document.getElementById('adminLoginModal');
        if (modal) {
            bootstrap.Modal.getInstance(modal)?.hide();
        }
    }

    async confirmDelete(productName) {
        if (typeof Swal !== 'undefined') {
            const result = await Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el producto "${productName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            return result.isConfirmed;
        } else {
            return confirm(`¿Deseas eliminar el producto "${productName}"?`);
        }
    }

    formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price).replace('COP', '').trim();
    }

    showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }

    showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        } else {
            alert(message);
        }
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Initialize admin panel
const adminPanel = new AdminPanel();

// Export for global use
window.adminPanel = adminPanel;
window.AdminPanel = AdminPanel;

// Backward compatibility functions
window.showAdminLogin = () => {
    const modal = document.getElementById('adminLoginModal');
    if (modal) {
        new bootstrap.Modal(modal).show();
    }
};

window.showAddProductForm = () => adminPanel.showAddProductForm();
window.editProductAdmin = (id) => adminPanel.editProduct(id);
window.deleteProductAdmin = (id) => adminPanel.deleteProduct(id);
window.viewProductDetail = (id) => adminPanel.viewProduct(id);
window.addColorField = () => adminPanel.addColorField();
window.addSizeField = () => adminPanel.addSizeField();

console.log('✅ Enhanced Admin Panel loaded');
