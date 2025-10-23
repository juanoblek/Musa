/**
 * Script de Reparación - Conexión Admin-Frontend
 * Soluciona el problema de productos no visibles en la página principal
 */

// Ejecutar cuando el DOM esté cargado
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Iniciando reparación de conexión Admin-Frontend...');
    
    // Esperar a que se carguen todos los scripts
    setTimeout(function() {
        initializeProductDisplay();
    }, 1000);
});

function initializeProductDisplay() {
    console.log('🔧 Configurando visualización de productos...');
    
    // 1. Forzar inicialización del sistema de administración
    if (typeof AdminSystem !== 'undefined') {
        console.log('✅ Sistema de administración encontrado, inicializando...');
        AdminSystem.init();
        
        // 2. Cargar productos del admin después de inicializar
        setTimeout(function() {
            AdminSystem.loadAndDisplayAdminProducts();
        }, 500);
    } else {
        console.warn('⚠️ Sistema de administración no encontrado');
    }
    
    // 3. Forzar visualización de productos existentes
    forceShowAllProducts();
    
    // 4. Configurar observador para cambios dinámicos
    setupProductObserver();
    
    // 5. Configurar actualizador automático
    setupAutoRefresh();
}

function forceShowAllProducts() {
    console.log('👁️ Forzando visualización de todos los productos...');
    
    // Seleccionar todos los productos
    const products = document.querySelectorAll('.product-card');
    console.log(`📦 Productos encontrados: ${products.length}`);
    
    products.forEach(function(product, index) {
        // Forzar visibilidad
        product.style.display = 'block';
        product.style.visibility = 'visible';
        product.style.opacity = '1';
        product.style.transform = 'none';
        
        // Remover clases que puedan ocultar
        product.classList.remove('d-none', 'hidden', 'fade-out');
        
        // Agregar clase para identificar como procesado
        product.classList.add('product-visible');
        
        console.log(`✅ Producto ${index + 1} forzado a visible`);
    });
    
    // Forzar visualización de contenedores
    const containers = [
        '.productos-filtrados',
        '#prendas-exclusivas',
        '.filter-container',
        '.container'
    ];
    
    containers.forEach(function(selector) {
        const element = document.querySelector(selector);
        if (element) {
            element.style.display = 'block';
            element.style.visibility = 'visible';
            element.style.opacity = '1';
            console.log(`✅ Contenedor ${selector} mostrado`);
        }
    });
}

function setupProductObserver() {
    console.log('👀 Configurando observador de productos...');
    
    const targetNode = document.getElementById('prendas-exclusivas');
    if (!targetNode) {
        console.warn('⚠️ No se encontró el contenedor de productos');
        return;
    }
    
    // Configurar observador de mutaciones
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList') {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.classList.contains('product-card')) {
                        console.log('🆕 Nuevo producto detectado, forzando visualización...');
                        
                        // Forzar visualización del nuevo producto
                        node.style.display = 'block';
                        node.style.visibility = 'visible';
                        node.style.opacity = '1';
                        node.classList.remove('d-none', 'hidden');
                        node.classList.add('product-visible');
                        
                        // Aplicar animación de entrada
                        node.style.transform = 'scale(0.8)';
                        node.style.transition = 'transform 0.3s ease';
                        
                        setTimeout(function() {
                            node.style.transform = 'scale(1)';
                        }, 100);
                    }
                });
            }
        });
    });
    
    observer.observe(targetNode, {
        childList: true,
        subtree: true
    });
    
    console.log('✅ Observador de productos configurado');
}

function setupAutoRefresh() {
    console.log('🔄 Configurando actualización automática...');
    
    // Verificar y mostrar productos cada 5 segundos
    setInterval(function() {
        const hiddenProducts = document.querySelectorAll('.product-card:not(.product-visible)');
        
        if (hiddenProducts.length > 0) {
            console.log(`🔄 Encontrados ${hiddenProducts.length} productos ocultos, mostrando...`);
            
            hiddenProducts.forEach(function(product) {
                product.style.display = 'block';
                product.style.visibility = 'visible';
                product.style.opacity = '1';
                product.classList.remove('d-none', 'hidden');
                product.classList.add('product-visible');
            });
        }
        
        // Verificar si el sistema de admin necesita cargar productos
        if (typeof AdminSystem !== 'undefined') {
            const adminProducts = localStorage.getItem('adminProducts');
            if (adminProducts) {
                const products = JSON.parse(adminProducts);
                const adminProductsInDOM = document.querySelectorAll('.admin-product');
                
                if (products.length > adminProductsInDOM.length) {
                    console.log('🔄 Productos del admin faltantes, cargando...');
                    AdminSystem.loadAndDisplayAdminProducts();
                }
            }
        }
    }, 5000);
    
    console.log('✅ Actualización automática configurada');
}

// Función de utilidad para depuración
function debugProductDisplay() {
    console.log('🔍 === DIAGNÓSTICO DE PRODUCTOS ===');
    
    const products = document.querySelectorAll('.product-card');
    const visibleProducts = document.querySelectorAll('.product-card.product-visible');
    const adminProducts = document.querySelectorAll('.admin-product');
    const originalProducts = document.querySelectorAll('.product-card:not(.admin-product)');
    
    console.log(`📦 Total productos: ${products.length}`);
    console.log(`👁️ Productos visibles: ${visibleProducts.length}`);
    console.log(`👨‍💼 Productos admin: ${adminProducts.length}`);
    console.log(`📋 Productos originales: ${originalProducts.length}`);
    
    // Verificar productos ocultos
    products.forEach(function(product, index) {
        const computedStyle = window.getComputedStyle(product);
        const isHidden = computedStyle.display === 'none' || 
                        computedStyle.visibility === 'hidden' || 
                        computedStyle.opacity === '0';
        
        if (isHidden) {
            console.log(`❌ Producto ${index + 1} está oculto:`, {
                display: computedStyle.display,
                visibility: computedStyle.visibility,
                opacity: computedStyle.opacity
            });
        }
    });
    
    // Verificar localStorage
    const adminProductsData = localStorage.getItem('adminProducts');
    if (adminProductsData) {
        const adminProductsArray = JSON.parse(adminProductsData);
        console.log(`💾 Productos en localStorage: ${adminProductsArray.length}`);
    } else {
        console.log('💾 No hay productos en localStorage');
    }
    
    console.log('🔍 === FIN DIAGNÓSTICO ===');
}

// Hacer funciones disponibles globalmente
window.forceShowAllProducts = forceShowAllProducts;
window.debugProductDisplay = debugProductDisplay;
window.initializeProductDisplay = initializeProductDisplay;

// Ejecutar reparación automática después de 2 segundos
setTimeout(function() {
    console.log('🚀 Ejecutando reparación automática...');
    initializeProductDisplay();
}, 2000);

// Ejecutar nuevamente después de 5 segundos para asegurar
setTimeout(function() {
    console.log('🔄 Segunda pasada de reparación...');
    forceShowAllProducts();
}, 5000);

console.log('✅ Script de reparación Admin-Frontend cargado');
