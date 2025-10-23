/**
 * Musa & Arion - Backend Integration Module
 * Complete API integration and state management
 */

class MusaArionAPI {
    constructor() {
        this.baseURL = 'http://localhost:3002/api';
        this.token = localStorage.getItem('admin_token');
        this.retryCount = 3;
        this.retryDelay = 1000;
    }

    // Utility methods
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        if (this.token && !config.headers.Authorization) {
            config.headers.Authorization = `Bearer ${this.token}`;
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || data.error || `HTTP ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error(`API Error [${endpoint}]:`, error);
            throw error;
        }
    }

    async retryRequest(endpoint, options = {}, retries = this.retryCount) {
        try {
            return await this.request(endpoint, options);
        } catch (error) {
            if (retries > 0) {
                await new Promise(resolve => setTimeout(resolve, this.retryDelay));
                return this.retryRequest(endpoint, options, retries - 1);
            }
            throw error;
        }
    }

    // Authentication
    async login(credentials) {
        try {
            const response = await this.request('/admin/login', {
                method: 'POST',
                body: JSON.stringify(credentials)
            });

            if (response.success) {
                this.token = response.token;
                localStorage.setItem('admin_token', response.token);
                localStorage.setItem('admin_user', JSON.stringify(response.user));
                return response;
            }

            throw new Error(response.message || 'Login failed');
        } catch (error) {
            console.error('Login error:', error);
            throw error;
        }
    }

    logout() {
        this.token = null;
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_user');
    }

    isAuthenticated() {
        return !!this.token;
    }

    // Products API
    async getProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = `/products${queryString ? `?${queryString}` : ''}`;
        return this.request(endpoint);
    }

    async getProduct(slug) {
        return this.request(`/products/${slug}`);
    }

    async searchProducts(term, limit = 20) {
        return this.request(`/products/search/${encodeURIComponent(term)}?limit=${limit}`);
    }

    // Admin Products API
    async getAdminProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        const endpoint = `/admin/products${queryString ? `?${queryString}` : ''}`;
        return this.request(endpoint);
    }

    async createProduct(productData) {
        const formData = new FormData();
        
        // Add product fields
        Object.keys(productData).forEach(key => {
            if (key === 'images' && productData[key]) {
                // Handle file uploads
                for (let i = 0; i < productData[key].length; i++) {
                    formData.append('images', productData[key][i]);
                }
            } else if (key === 'colors' || key === 'sizes') {
                // Handle arrays
                formData.append(key, JSON.stringify(productData[key]));
            } else if (productData[key] !== null && productData[key] !== undefined) {
                formData.append(key, productData[key]);
            }
        });

        return this.request('/admin/products', {
            method: 'POST',
            body: formData,
            headers: {} // Don't set Content-Type for FormData
        });
    }

    async updateProduct(productId, productData) {
        const formData = new FormData();
        
        Object.keys(productData).forEach(key => {
            if (key === 'images' && productData[key]) {
                for (let i = 0; i < productData[key].length; i++) {
                    formData.append('images', productData[key][i]);
                }
            } else if (key === 'colors' || key === 'sizes') {
                formData.append(key, JSON.stringify(productData[key]));
            } else if (productData[key] !== null && productData[key] !== undefined) {
                formData.append(key, productData[key]);
            }
        });

        return this.request(`/admin/products/${productId}`, {
            method: 'PUT',
            body: formData,
            headers: {}
        });
    }

    async deleteProduct(productId) {
        return this.request(`/admin/products/${productId}`, {
            method: 'DELETE'
        });
    }

    // Categories API
    async getCategories() {
        return this.request('/products/categories/all');
    }

    // Dashboard API
    async getDashboardStats() {
        return this.request('/admin/dashboard/stats');
    }

    // Health Check
    async healthCheck() {
        return this.request('/health');
    }
}

// Global API instance
const api = new MusaArionAPI();

// Enhanced Product Management with Backend Integration
class ProductManager {
    constructor() {
        this.products = [];
        this.categories = [];
        this.loading = false;
        this.lastSync = null;
    }

    // Initialize
    async init() {
        try {
            await this.loadCategories();
            await this.loadProducts();
            console.log('✅ ProductManager initialized');
        } catch (error) {
            console.error('❌ ProductManager initialization failed:', error);
        }
    }

    // Load categories from backend
    async loadCategories() {
        try {
            const response = await api.getCategories();
            if (response.success) {
                this.categories = response.categories;
            }
        } catch (error) {
            console.error('Error loading categories:', error);
            // Fallback to default categories
            this.categories = [
                { id: 1, name: 'Camisas', slug: 'camisas' },
                { id: 2, name: 'Chaquetas', slug: 'chaquetas' },
                { id: 3, name: 'Pantalones', slug: 'pantalones' },
                { id: 4, name: 'Blazers', slug: 'blazers' }
            ];
        }
    }

    // Load products from backend
    async loadProducts() {
        try {
            this.loading = true;
            const response = await api.getProducts();
            if (response.success) {
                this.products = response.products;
                this.lastSync = new Date();
                this.displayProducts();
            }
        } catch (error) {
            console.error('Error loading products:', error);
            this.showError('Error al cargar productos');
        } finally {
            this.loading = false;
        }
    }

    // Create new product
    async createProduct(productData) {
        try {
            this.loading = true;
            const response = await api.createProduct(productData);
            if (response.success) {
                this.showSuccess('Producto creado exitosamente');
                await this.loadProducts(); // Refresh products
                return response;
            }
        } catch (error) {
            console.error('Error creating product:', error);
            this.showError('Error al crear producto: ' + error.message);
            throw error;
        } finally {
            this.loading = false;
        }
    }

    // Update product
    async updateProduct(productId, productData) {
        try {
            this.loading = true;
            const response = await api.updateProduct(productId, productData);
            if (response.success) {
                this.showSuccess('Producto actualizado exitosamente');
                await this.loadProducts(); // Refresh products
                return response;
            }
        } catch (error) {
            console.error('Error updating product:', error);
            this.showError('Error al actualizar producto: ' + error.message);
            throw error;
        } finally {
            this.loading = false;
        }
    }

    // Delete product
    async deleteProduct(productId) {
        try {
            this.loading = true;
            const response = await api.deleteProduct(productId);
            if (response.success) {
                this.showSuccess('Producto eliminado exitosamente');
                await this.loadProducts(); // Refresh products
                return response;
            }
        } catch (error) {
            console.error('Error deleting product:', error);
            this.showError('Error al eliminar producto: ' + error.message);
            throw error;
        } finally {
            this.loading = false;
        }
    }

    // Display products in the UI
    displayProducts() {
        const container = document.getElementById('products-container');
        if (!container) return;

        if (this.products.length === 0) {
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4>No hay productos disponibles</h4>
                        <p>Agrega algunos productos para comenzar a mostrarlos aquí.</p>
                    </div>
                </div>
            `;
            return;
        }

        container.innerHTML = this.products.map(product => `
            <div class="col-lg-4 col-md-6 col-12 mb-4">
                <div class="card h-100 product-card" data-product-id="${product.id}">
                    <div class="card-img-top-container">
                        <img src="${product.images?.[0]?.image_url || 'images/placeholder.jpg'}" 
                             class="card-img-top product-image" 
                             alt="${product.name}"
                             onerror="this.src='images/placeholder.jpg'">
                        ${product.sale_price ? '<span class="badge bg-danger position-absolute top-0 start-0 m-2">Oferta</span>' : ''}
                        ${product.featured ? '<span class="badge bg-warning position-absolute top-0 end-0 m-2">Destacado</span>' : ''}
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text text-muted small">${product.description || ''}</p>
                        <div class="mt-auto">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    ${product.sale_price ? `
                                        <span class="text-decoration-line-through text-muted">$${this.formatPrice(product.price)}</span>
                                        <span class="fw-bold text-danger">$${this.formatPrice(product.sale_price)}</span>
                                    ` : `
                                        <span class="fw-bold">$${this.formatPrice(product.price)}</span>
                                    `}
                                </div>
                                <div>
                                    <span class="badge bg-secondary">${product.category_name || 'Sin categoría'}</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button class="btn btn-primary btn-sm flex-fill" onclick="addToCart(${product.id})">
                                    <i class="bi bi-cart-plus"></i> Agregar
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="viewProduct(${product.id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        // Apply CSS fixes for visibility
        this.applyProductVisibilityFixes();
    }

    // Apply CSS fixes to ensure products are visible
    applyProductVisibilityFixes() {
        const style = document.createElement('style');
        style.textContent = `
            .product-card {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                position: relative !important;
            }
            .card-img-top-container {
                position: relative;
                height: 250px;
                overflow: hidden;
            }
            .product-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block !important;
            }
            #products-container {
                display: block !important;
                visibility: visible !important;
            }
        `;
        document.head.appendChild(style);
    }

    // Format price
    formatPrice(price) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(price).replace('COP', '').trim();
    }

    // Show success message
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

    // Show error message
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

    // Get product by ID
    getProductById(id) {
        return this.products.find(p => p.id === parseInt(id));
    }

    // Search products
    searchProducts(term) {
        return this.products.filter(product =>
            product.name.toLowerCase().includes(term.toLowerCase()) ||
            product.description?.toLowerCase().includes(term.toLowerCase())
        );
    }

    // Filter products
    filterProducts(filters) {
        return this.products.filter(product => {
            if (filters.category && product.category_id !== filters.category) return false;
            if (filters.gender && product.gender !== filters.gender) return false;
            if (filters.featured && !product.featured) return false;
            if (filters.minPrice && product.price < filters.minPrice) return false;
            if (filters.maxPrice && product.price > filters.maxPrice) return false;
            return true;
        });
    }
}

// Global product manager instance
const productManager = new ProductManager();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    productManager.init();
});

// Export for global use
window.api = api;
window.productManager = productManager;
window.MusaArionAPI = MusaArionAPI;
window.ProductManager = ProductManager;

// Backward compatibility functions
window.loadAllProducts = () => productManager.loadProducts();
window.addToCart = (productId) => {
    console.log('Adding product to cart:', productId);
    // Implement cart functionality
};
window.viewProduct = (productId) => {
    const product = productManager.getProductById(productId);
    if (product) {
        console.log('Viewing product:', product);
        // Implement product detail view
    }
};

console.log('✅ Musa & Arion Backend Integration loaded');
