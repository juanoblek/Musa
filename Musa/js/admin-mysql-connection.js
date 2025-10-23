// Conexión del Panel Administrativo con MySQL
// ==========================================

// Variables globales
var currentProducts = [];
var currentCategories = [];

// Hacerlas accesibles globalmente
window.currentProducts = currentProducts;
window.currentCategories = currentCategories;

// Configuración de la API
const API_BASE_URL = window.location.protocol === 'file:' ? 'http://localhost' : '';
const API_ENDPOINTS = {
    productos: '/api/productos.php',
    categorias: '/api/categorias.php',
    crear_producto: '/crear_producto_con_imagen_real.php',
    actualizar_producto: '/php/actualizar_producto.php',
    eliminar_producto: '/eliminar_producto_mejorado.php',
    get_productos: '/get_productos_admin.php',
    get_categorias: '/get_categorias.php',
    save_category: '/save_category.php'
};

// Función para cargar productos desde MySQL
async function loadProductsFromDatabase() {
    try {
        console.log('📦 Cargando productos desde MySQL...');
        
        const response = await fetch(API_BASE_URL + API_ENDPOINTS.get_productos, {
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
            currentProducts = data.productos || [];
            console.log(`✅ ${currentProducts.length} productos cargados desde MySQL`);
            
            // Actualizar la tabla de productos en el admin
            loadProductsTable();
            updateDashboard();
            
            // Notificar al frontend sobre la actualización
            notifyFrontendUpdate('products');
            
            return currentProducts;
        } else {
            throw new Error(data.message || 'Error al cargar productos');
        }
    } catch (error) {
        console.error('❌ Error cargando productos desde MySQL:', error);
        showNotification('Error al cargar productos desde la base de datos', 'error');
        
        // Fallback a localStorage en caso de error
        currentProducts = getFromStorage('products', []);
        loadProductsTable();
        return currentProducts;
    }
}

// Función para guardar producto en MySQL
async function saveProductToDatabase(productData, isEdit = false) {
    try {
        console.log('💾 Guardando producto en MySQL...', productData);
        
        const endpoint = isEdit ? API_ENDPOINTS.actualizar_producto : API_ENDPOINTS.crear_producto;
        const method = 'POST';
        
        // Preparar FormData para enviar archivos
        const formData = new FormData();
        
        // Agregar imagen si existe
        const imageInput = document.getElementById('productImage');
        if (imageInput && imageInput.files && imageInput.files[0]) {
            formData.append('imagen', imageInput.files[0]);
            console.log('📸 Imagen agregada al FormData:', imageInput.files[0].name, 'Tamaño:', imageInput.files[0].size);
        } else {
            console.log('⚠️ No se encontró imagen para enviar');
        }
        
        // Agregar datos del producto (mapear nombres de campos)
        formData.append('nombre', productData.nombre || '');
        formData.append('codigo_producto', productData.codigo_producto || '');
        formData.append('descripcion_corta', productData.descripcion_corta || '');
        formData.append('descripcion_larga', productData.descripcion_larga || productData.descripcion_corta || '');
        formData.append('precio', productData.precio || 0);
        formData.append('precio_oferta', productData.precio_oferta || '');
        formData.append('stock', productData.stock || 0);
        formData.append('stock_minimo', productData.stock_minimo || 5);
        formData.append('categoria_id', productData.categoria_id || '');
        formData.append('genero', productData.genero || 'unisex');
        formData.append('activo', productData.activo ? '1' : '0');
        formData.append('destacado', productData.destacado ? '1' : '0');
        formData.append('nuevo', productData.nuevo ? '1' : '0');
        formData.append('oferta', productData.oferta ? '1' : '0');

        console.log('📦 Enviando datos del producto...');

        const response = await fetch(API_BASE_URL + endpoint, {
            method: method,
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Producto guardado en MySQL exitosamente');
            showNotification(isEdit ? 'Producto actualizado correctamente' : 'Producto creado correctamente', 'success');
            
            // Recargar productos desde la base de datos
            await loadProductsFromDatabase();
            
            // Limpiar formulario
            clearProductForm();
            
            return data;
        } else {
            throw new Error(data.message || 'Error al guardar producto');
        }
    } catch (error) {
        console.error('❌ Error guardando producto en MySQL:', error);
        showNotification('Error al guardar producto en la base de datos: ' + error.message, 'error');
        throw error;
    }
}

// Función para eliminar producto de MySQL
async function deleteProductFromDatabase(productId) {
    try {
        console.log('🗑️ Eliminando producto de MySQL...', productId);
        
        const response = await fetch(API_BASE_URL + API_ENDPOINTS.eliminar_producto, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: productId })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Producto eliminado de MySQL exitosamente');
            showNotification('Producto eliminado correctamente', 'success');
            
            // Recargar productos desde la base de datos
            await loadProductsFromDatabase();
            
            return data;
        } else {
            throw new Error(data.message || 'Error al eliminar producto');
        }
    } catch (error) {
        console.error('❌ Error eliminando producto de MySQL:', error);
        showNotification('Error al eliminar producto de la base de datos: ' + error.message, 'error');
        throw error;
    }
}

// Función para cargar categorías desde MySQL
async function loadCategoriesFromDatabase() {
    try {
        console.log('🏷️ Cargando categorías desde MySQL...');
        
        // Detectar puerto automáticamente basado en la URL actual
        const currentPort = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
        const baseUrl = `${window.location.protocol}//${window.location.hostname}${currentPort !== '80' && currentPort !== '443' ? ':' + currentPort : ''}`;
        
        // URLs para probar (usando la lógica que funciona)
        const urls = [
            `${baseUrl}/get_categorias.php`,  // URL completa detectada automáticamente
            '/get_categorias.php',            // Ruta absoluta
            'get_categorias.php',             // Ruta relativa
            './get_categorias.php',           // Ruta relativa explícita
            '../get_categorias.php'           // Una carpeta arriba
        ];
        
        console.log('🔗 URL base detectada:', baseUrl);
        
        let response = null;
        let workingUrl = null;
        
        for (const url of urls) {
            try {
                console.log('🔗 Probando URL de categorías:', url);
                response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                
                if (response.ok) {
                    workingUrl = url;
                    console.log('✅ URL funcional encontrada:', url);
                    break;
                }
            } catch (urlError) {
                console.log('❌ URL no funcional:', url, urlError.message);
                continue;
            }
        }
        
        if (!response || !response.ok) {
            throw new Error(`No se pudo conectar a ninguna URL de categorías`);
        }

        console.log('📡 Respuesta categorías:', response.status, response.statusText);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('📊 Datos categorías recibidos:', data);
        
        let categories = [];
        
        // Manejar múltiples formatos de respuesta (como en el panel limpio)
        if (data.success && data.categories && Array.isArray(data.categories)) {
            categories = data.categories;
            console.log(`✅ ${categories.length} categorías cargadas desde MySQL (formato success.categories)`);
        } else if (data.success && data.categorias && Array.isArray(data.categorias)) {
            categories = data.categorias;
            console.log(`✅ ${categories.length} categorías cargadas desde MySQL (formato success.categorias)`);
        } else if (data.categorias && Array.isArray(data.categorias)) {
            categories = data.categorias;
            console.log(`✅ ${categories.length} categorías cargadas desde MySQL (formato categorias)`);
        } else if (Array.isArray(data)) {
            categories = data;
            console.log(`✅ ${categories.length} categorías cargadas desde MySQL (formato array)`);
        } else {
            throw new Error('Formato de respuesta no válido: ' + JSON.stringify(data));
        }
        
        currentCategories = categories;
        window.currentCategories = categories; // Asegurar que esté disponible globalmente
        console.log('🔄 Variable global currentCategories actualizada:', currentCategories.length);
        
        // Actualizar selects de categorías
        updateCategorySelects();
        
        // También actualizar la tabla de categorías si existe la función
        if (typeof loadCategoriesTable === 'function') {
            console.log('🔄 Actualizando tabla de categorías...');
            loadCategoriesTable();
        }
        
        // Notificar al frontend sobre la actualización de categorías
        notifyFrontendUpdate('categories');
        
        return currentCategories;
    } catch (error) {
        console.error('❌ Error cargando categorías desde MySQL:', error);
        showNotification('Error al cargar categorías desde la base de datos', 'warning');
        
        // Fallback a categorías por defecto
        currentCategories = [
            { id: 1, nombre: 'Ropa Femenina', descripcion: 'Prendas de vestir para mujeres' },
            { id: 2, nombre: 'Ropa Masculina', descripcion: 'Prendas de vestir para hombres' },
            { id: 3, nombre: 'Accesorios', descripcion: 'Complementos' },
            { id: 4, nombre: 'Calzado', descripcion: 'Zapatos y calzado' },
            { id: 5, nombre: 'Deportivo', descripcion: 'Ropa deportiva' }
        ];
        console.log('⚠️ Usando categorías por defecto:', currentCategories);
        
        updateCategorySelects();
        
        if (typeof loadCategoriesTable === 'function') {
            loadCategoriesTable();
        }
        
        return currentCategories;
    }
}

// Función para actualizar selects de categorías en formularios
function updateCategorySelects() {
    const categorySelects = document.querySelectorAll('select[name="categoria"], #editCategoria, #productCategoria');
    
    categorySelects.forEach(select => {
        if (select) {
            // Limpiar opciones existentes excepto la primera
            select.innerHTML = '<option value="">Seleccionar categoría</option>';
            
            // Agregar categorías
            currentCategories.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id || categoria.nombre;
                option.textContent = categoria.nombre;
                select.appendChild(option);
            });
        }
    });
}

// Función para sincronizar con el frontend
function syncWithFrontend() {
    console.log('🔄 Sincronizando productos con frontend...');
    
    // Disparar evento personalizado para que index.html se actualice
    window.dispatchEvent(new CustomEvent('productsUpdated', {
        detail: { products: currentProducts, source: 'admin-mysql' }
    }));
    
    // También actualizar localStorage como backup para compatibilidad
    localStorage.setItem('products', JSON.stringify(currentProducts));
}

// Función para inicializar la conexión con MySQL
async function initializeMySQLConnection() {
    console.log('🚀 Inicializando conexión MySQL para panel administrativo...');
    
    try {
        // Cargar datos iniciales
        await loadProductsFromDatabase();
        await loadCategoriesFromDatabase();
        
        console.log('✅ Conexión MySQL inicializada correctamente');
        showNotification('Panel administrativo conectado a MySQL', 'success');
        
        return true;
    } catch (error) {
        console.error('❌ Error inicializando conexión MySQL:', error);
        showNotification('Error de conexión con MySQL - usando modo offline', 'warning');
        return false;
    }
}

// Función helper para mostrar notificaciones
function showNotification(message, type = 'info') {
    // Buscar contenedor de notificaciones existente o crear uno
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 300px;
        `;
        document.body.appendChild(notificationContainer);
    }
    
    // Crear notificación
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    notification.style.cssText = `
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    notificationContainer.appendChild(notification);
    
    // Auto-remove después de 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Función para limpiar el formulario de productos
function clearProductForm() {
    const form = document.getElementById('productForm');
    if (form) {
        form.reset();
    }
    
    // Limpiar preview de imágenes
    const imagePreview = document.getElementById('imagePreview');
    if (imagePreview) {
        imagePreview.innerHTML = '';
    }
    
    // Resetear variables de edición
    window.isEditingProduct = false;
    window.editingProductId = null;
}

console.log('✅ Admin MySQL Connection loaded');

// Función global para debugging
window.debugCategories = async function() {
    console.log('🔍 Debug: Forzando carga de categorías...');
    console.log('🔢 currentCategories antes:', currentCategories);
    
    await loadCategoriesFromDatabase();
    
    console.log('🔢 currentCategories después:', currentCategories);
    console.log('📊 Total categorías:', currentCategories.length);
    
    // Mostrar en el DOM
    if (typeof loadCategoriesTable === 'function') {
        loadCategoriesTable();
        console.log('✅ Tabla de categorías actualizada');
    }
    
    return currentCategories;
};

// =============================================================================
// SISTEMA DE NOTIFICACIÓN AL FRONTEND
// =============================================================================

/**
 * Notifica al frontend cuando hay actualizaciones en productos o categorías
 */
function notifyFrontendUpdate(type) {
    console.log(`🔔 Notificando actualización de ${type} al frontend...`);
    
    try {
        // 1. Disparar eventos personalizados
        if (type === 'categories') {
            window.dispatchEvent(new CustomEvent('categoriesUpdated', {
                detail: { categories: currentCategories }
            }));
            
            // Guardar en localStorage para sincronización
            localStorage.setItem('frontend_categories', JSON.stringify(currentCategories));
            localStorage.setItem('categories', JSON.stringify(currentCategories));
        } else if (type === 'products') {
            window.dispatchEvent(new CustomEvent('productsUpdated', {
                detail: { products: currentProducts }
            }));
            
            // Guardar en localStorage para sincronización
            localStorage.setItem('products', JSON.stringify(currentProducts));
        }
        
        // 2. Establecer flags de sincronización
        localStorage.setItem(`sync_${type}_${Date.now()}`, 'updated');
        
        // 3. Disparar evento global de sincronización
        window.dispatchEvent(new CustomEvent('adminDataUpdated', {
            detail: { 
                type: type,
                timestamp: Date.now(),
                categories: currentCategories || [],
                products: currentProducts || []
            }
        }));
        
        console.log(`✅ Notificación de ${type} enviada al frontend`);
        
    } catch (error) {
        console.error(`❌ Error notificando actualización de ${type}:`, error);
    }
}

/**
 * Función para forzar sincronización con el frontend
 */
window.forceFrontendSync = function() {
    console.log('🔄 Forzando sincronización completa con el frontend...');
    
    notifyFrontendUpdate('categories');
    notifyFrontendUpdate('products');
    
    // Disparar evento especial de sincronización forzada
    window.dispatchEvent(new CustomEvent('forcedSync', {
        detail: {
            categories: currentCategories,
            products: currentProducts,
            timestamp: Date.now()
        }
    }));
    
    console.log('✅ Sincronización forzada completada');
};

/**
 * Función para guardar categoría en la base de datos MySQL
 */
async function saveCategoryToDatabase(categoryData, isEdit = false) {
    console.log(`💾 ${isEdit ? 'Actualizando' : 'Guardando'} categoría en MySQL:`, categoryData);
    
    // Detectar puerto automáticamente basado en la URL actual
    const currentPort = window.location.port || (window.location.protocol === 'https:' ? '443' : '80');
    const baseUrl = `${window.location.protocol}//${window.location.hostname}${currentPort !== '80' && currentPort !== '443' ? ':' + currentPort : ''}`;
    
    // Probar URLs (usando la lógica que funciona)
    const endpoints = [
        `${baseUrl}/save_category.php`, // URL completa detectada automáticamente
        '/save_category.php',           // Ruta absoluta
        'save_category.php',            // Ruta relativa
        './save_category.php',          // Ruta relativa explícita
        '../save_category.php'          // Una carpeta arriba
    ];
    
    console.log('🔗 URL base para guardar:', baseUrl);
    
    let response = null;
    
    for (const endpoint of endpoints) {
        try {
            console.log('🔗 Probando endpoint:', endpoint);
            response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: isEdit ? 'update' : 'create',
                    category: {
                        id: categoryData.id,
                        nombre: categoryData.name,
                        descripcion: categoryData.description || '',
                        genero: categoryData.gender || '',
                        activo: categoryData.status === 'active' ? 1 : 0
                    }
                })
            });
            
            if (response.ok) {
                console.log('✅ Endpoint funcional:', endpoint);
                break;
            }
        } catch (endpointError) {
            console.log('❌ Endpoint no funcional:', endpoint);
            continue;
        }
    }
    
    if (!response || !response.ok) {
        throw new Error(`No se pudo conectar a save_category.php - Status: ${response?.status || 'No response'}`);
    }
    
    try {
        
        const result = await response.json();
        
        if (result.success) {
            console.log(`✅ Categoría ${isEdit ? 'actualizada' : 'guardada'} exitosamente en MySQL`);
            
            // Actualizar arrays globales
            if (isEdit) {
                const index = currentCategories.findIndex(c => c.id == categoryData.id);
                if (index !== -1) {
                    currentCategories[index] = {
                        id: categoryData.id,
                        name: categoryData.name,
                        description: categoryData.description,
                        gender: categoryData.gender,
                        status: categoryData.status
                    };
                }
            } else {
                currentCategories.push({
                    id: result.category_id || categoryData.id,
                    name: categoryData.name,
                    description: categoryData.description,
                    gender: categoryData.gender,
                    status: categoryData.status
                });
            }
            
            // Notificar al frontend
            notifyFrontendUpdate('categories');
            
            return result;
        } else {
            throw new Error(result.message || 'Error desconocido al guardar categoría');
        }
        
    } catch (error) {
        console.error(`❌ Error ${isEdit ? 'actualizando' : 'guardando'} categoría:`, error);
        throw error;
    }
}

// Hacer la función disponible globalmente
window.saveCategoryToDatabase = saveCategoryToDatabase;
