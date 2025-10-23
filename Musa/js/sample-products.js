/**
 * Configuración inicial de productos de ejemplo
 * Asegura que haya productos para mostrar en la página principal
 */

// Productos de ejemplo para el admin
const sampleProducts = [
    {
        id: 'admin-camisa-elegante-1',
        name: 'Camisa Elegante Premium',
        price: 89999,
        image: 'images/Camisa Blanco Purista/Camisa Blanco Purista.jpeg',
        gender: 'hombre',
        category: 'camisas-hombre',
        isAdmin: true
    },
    {
        id: 'admin-chaqueta-urbana-1',
        name: 'Chaqueta Urbana Moderna',
        price: 159999,
        image: 'images/Chaqueta Deportiva Blue Ox/Chaqueta Deportiva Blue Ox.jpeg',
        gender: 'hombre',
        category: 'chaquetas-hombre',
        isAdmin: true
    },
    {
        id: 'admin-blazer-mujer-1',
        name: 'Blazer Ejecutivo Mujer',
        price: 199999,
        image: 'images/BLAZER MORADO.png',
        gender: 'mujer',
        category: 'blazers-mujer',
        isAdmin: true
    },
    {
        id: 'admin-camisa-seda-1',
        name: 'Camisa Seda Exclusiva',
        price: 129999,
        image: 'images/Camisas Seda Egipcia/Camisa 1.jpg',
        gender: 'hombre',
        category: 'camisas-hombre',
        isAdmin: true
    },
    {
        id: 'admin-chaqueta-rosada-1',
        name: 'Chaqueta Pink Serenity',
        price: 179999,
        image: 'images/Chaqueta Pink Serenity Super Nylon/Chaqueta Pink Serenity Super Nylon.jpeg',
        gender: 'mujer',
        category: 'chaquetas-mujer',
        isAdmin: true
    }
];

// Función para inicializar productos de ejemplo
function initializeSampleProducts() {
    console.log('🔧 Inicializando productos de ejemplo...');
    
    // Verificar si ya hay productos en localStorage
    const existingProducts = localStorage.getItem('adminProducts');
    
    if (!existingProducts || JSON.parse(existingProducts).length === 0) {
        console.log('📦 No hay productos existentes, agregando productos de ejemplo...');
        
        // Agregar productos de ejemplo
        localStorage.setItem('adminProducts', JSON.stringify(sampleProducts));
        
        console.log(`✅ ${sampleProducts.length} productos de ejemplo agregados`);
        
        // Mostrar notificación
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Productos Inicializados',
                text: `Se han agregado ${sampleProducts.length} productos de ejemplo`,
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
        }
    } else {
        const products = JSON.parse(existingProducts);
        console.log(`📦 Ya existen ${products.length} productos en el admin`);
    }
}

// Función para agregar más productos si es necesario
function addMoreSampleProducts() {
    const moreProducts = [
        {
            id: 'admin-pantalon-drill-1',
            name: 'Pantalón Drill Ejecutivo',
            price: 99999,
            image: 'images/Pantalon Drill Liso/Pantalon Drill Liso.jpeg',
            gender: 'hombre',
            category: 'pantalones-hombre',
            isAdmin: true
        },
        {
            id: 'admin-chaqueta-lavanda-1',
            name: 'Chaqueta Plush Lavender',
            price: 189999,
            image: 'images/Chaqueta Plush Lavender/Chaqueta Plush Lavender.jpeg',
            gender: 'mujer',
            category: 'chaquetas-mujer',
            isAdmin: true
        },
        {
            id: 'admin-blazer-cuadrado-1',
            name: 'Blazer Cuadrado Elegante',
            price: 169999,
            image: 'images/BLAZER CUADRADO.png',
            gender: 'hombre',
            category: 'blazers-hombre',
            isAdmin: true
        }
    ];
    
    const existingProducts = JSON.parse(localStorage.getItem('adminProducts') || '[]');
    const allProducts = [...existingProducts, ...moreProducts];
    
    localStorage.setItem('adminProducts', JSON.stringify(allProducts));
    console.log(`✅ ${moreProducts.length} productos adicionales agregados`);
}

// Función para verificar y reparar productos
function repairProductDisplay() {
    console.log('🔧 Reparando visualización de productos...');
    
    // Esperar a que el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(repairProductDisplay, 1000);
        });
        return;
    }
    
    // Verificar si AdminSystem está disponible
    if (typeof AdminSystem === 'undefined') {
        console.log('⚠️ AdminSystem no está disponible, reintentando en 1 segundo...');
        setTimeout(repairProductDisplay, 1000);
        return;
    }
    
    // Inicializar AdminSystem
    AdminSystem.init();
    
    // Cargar productos del admin
    setTimeout(function() {
        AdminSystem.loadAndDisplayAdminProducts();
        console.log('✅ Productos del admin cargados');
    }, 500);
    
    // Forzar visualización de todos los productos
    setTimeout(function() {
        const products = document.querySelectorAll('.product-card');
        products.forEach(function(product) {
            product.style.display = 'block';
            product.style.visibility = 'visible';
            product.style.opacity = '1';
            product.classList.remove('d-none', 'hidden');
        });
        
        console.log(`✅ ${products.length} productos forzados a visible`);
    }, 1000);
}

// Ejecutar inicialización
if (typeof window !== 'undefined') {
    // Ejecutar inmediatamente
    initializeSampleProducts();
    
    // Ejecutar reparación cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(repairProductDisplay, 2000);
        });
    } else {
        setTimeout(repairProductDisplay, 2000);
    }
    
    // Hacer funciones disponibles globalmente
    window.initializeSampleProducts = initializeSampleProducts;
    window.addMoreSampleProducts = addMoreSampleProducts;
    window.repairProductDisplay = repairProductDisplay;
}

console.log('✅ Configuración inicial de productos cargada');
