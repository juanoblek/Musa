// Sincronización del Frontend con MySQL
// ====================================

// Configuración de la API
const FRONTEND_API_CONFIG = {
    baseUrl: window.location.protocol === 'file:' ? 'http://localhost:3000' : '',
    endpoints: {
        productos: '/get_productos_frontend.php',
        categorias: '/php/get_categorias.php'
    }
};

// Variable global para productos
let frontendProducts = [];

// Función para cargar productos desde MySQL
async function loadProductsFromMySQL() {
    try {
        console.log('📦 Frontend: Cargando productos desde MySQL...');
        
        const response = await fetch(FRONTEND_API_CONFIG.baseUrl + FRONTEND_API_CONFIG.endpoints.productos, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            // El nuevo endpoint devuelve "products" en lugar de "productos"
            frontendProducts = data.products || data.productos || [];
            console.log(`✅ Frontend: ${frontendProducts.length} productos cargados desde MySQL`);
            
            // Actualizar localStorage para compatibilidad
            localStorage.setItem('products', JSON.stringify(frontendProducts));
            
            // Disparar evento de actualización
            window.dispatchEvent(new CustomEvent('productsLoadedFromMySQL', {
                detail: { products: frontendProducts }
            }));
            
            // Actualizar el DOM si hay funciones disponibles
            if (typeof updateProductDisplay === 'function') {
                updateProductDisplay();
            }
            
            if (typeof loadProducts === 'function') {
                loadProducts();
            }
            
            return frontendProducts;
        } else {
            throw new Error(data.message || 'Error al cargar productos');
        }
    } catch (error) {
        console.error('❌ Frontend: Error cargando productos desde MySQL:', error);
        console.log('⚠️ Frontend: Usando productos desde localStorage como fallback');
        
        // Fallback a localStorage
        try {
            const localProducts = localStorage.getItem('products');
            if (localProducts) {
                frontendProducts = JSON.parse(localProducts);
                console.log(`📦 Frontend: ${frontendProducts.length} productos cargados desde localStorage`);
                return frontendProducts;
            }
        } catch (localError) {
            console.error('❌ Frontend: Error también con localStorage:', localError);
        }
        
        return [];
    }
}

// Función para escuchar actualizaciones del panel administrativo
function listenForAdminUpdates() {
    // Escuchar evento personalizado del admin
    window.addEventListener('productsUpdated', async function(event) {
        console.log('🔄 Frontend: Recibida actualización del admin panel');
        await loadProductsFromMySQL();
    });
    
    // Escuchar cambios en localStorage (backup)
    window.addEventListener('storage', async function(event) {
        if (event.key === 'products') {
            console.log('🔄 Frontend: Detectado cambio en localStorage');
            await loadProductsFromMySQL();
        }
    });
}

// Función para sincronización automática cada cierto tiempo
function startAutoSync() {
    // Sincronizar cada 30 segundos
    setInterval(async () => {
        if (document.hidden) return; // No sincronizar si la pestaña no está visible
        
        console.log('🔄 Frontend: Sincronización automática...');
        await loadProductsFromMySQL();
    }, 30000);
}

// Función para obtener producto por ID desde MySQL
async function getProductByIdFromMySQL(productId) {
    const products = await loadProductsFromMySQL();
    return products.find(p => p.id == productId);
}

// Función para buscar productos
function searchProductsInMySQL(searchTerm) {
    if (!searchTerm) return frontendProducts;
    
    const term = searchTerm.toLowerCase();
    return frontendProducts.filter(product => 
        (product.nombre || '').toLowerCase().includes(term) ||
        (product.descripcion_corta || '').toLowerCase().includes(term) ||
        (product.categoria_nombre || '').toLowerCase().includes(term)
    );
}

// Función para filtrar productos por categoría
function filterProductsByCategoryMySQL(categoryId) {
    if (!categoryId) return frontendProducts;
    return frontendProducts.filter(product => product.categoria_id == categoryId);
}

// Función de inicialización del frontend MySQL
async function initFrontendMySQLSync() {
    console.log('🚀 Inicializando sincronización frontend con MySQL...');
    
    try {
        // Cargar productos iniciales
        await loadProductsFromMySQL();
        
        // Configurar listeners
        listenForAdminUpdates();
        
        // Iniciar sincronización automática
        startAutoSync();
        
        console.log('✅ Frontend: Sincronización MySQL configurada');
        
        return true;
    } catch (error) {
        console.error('❌ Frontend: Error configurando sincronización MySQL:', error);
        return false;
    }
}

// Función helper para compatibilidad con código existente
function reloadProductsFromMySQL() {
    return loadProductsFromMySQL();
}

// Función para forzar recarga desde el admin
window.reloadProducts = function() {
    console.log('🔄 Frontend: Forzando recarga de productos desde admin...');
    return loadProductsFromMySQL();
};

// Auto-inicializar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initFrontendMySQLSync, 1000);
    });
} else {
    // DOM ya está listo
    setTimeout(initFrontendMySQLSync, 1000);
}

console.log('✅ Frontend MySQL Sync loaded');
