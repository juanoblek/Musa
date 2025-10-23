// Función de debug específica para el carrito
window.debugCartSystem = function() {
    console.log('🔍 DEBUG DEL SISTEMA DE CARRITO');
    console.log('=' .repeat(50));
    
    // Verificar localStorage
    const carritoData = localStorage.getItem('carrito');
    const cartData = localStorage.getItem('cart');
    
    console.log('📦 Estado del localStorage:');
    console.log(`  - carrito: ${carritoData ? 'EXISTS' : 'NULL'}`);
    console.log(`  - cart: ${cartData ? 'EXISTS (PROBLEMA!)' : 'NULL'}`);
    
    if (carritoData) {
        try {
            const carrito = JSON.parse(carritoData);
            console.log('✅ Carrito parseado exitosamente:');
            console.log('  - Productos:', carrito.length);
            
            carrito.forEach((item, index) => {
                console.log(`  - Producto ${index + 1}:`);
                console.log(`    * Nombre: ${item.nombre}`);
                console.log(`    * Precio: ${item.precio}`);
                console.log(`    * Cantidad: ${item.cantidad}`);
                console.log(`    * Talla: ${item.talla}`);
                console.log(`    * Color: ${item.color || 'N/A'}`);
                console.log(`    * Imagen: ${item.imagen || 'N/A'}`);
            });
            
            // Calcular total
            const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
            console.log(`💰 Total calculado: $${total.toLocaleString()}`);
            
        } catch (error) {
            console.error('❌ Error parseando carrito:', error);
        }
    }
    
    // Verificar funciones
    console.log('🔧 Funciones disponibles:');
    console.log(`  - addToCart: ${typeof window.addToCart}`);
    console.log(`  - showCartModal: ${typeof showCartModal}`);
    console.log(`  - increaseQuantity: ${typeof window.increaseQuantity}`);
    console.log(`  - decreaseQuantity: ${typeof window.decreaseQuantity}`);
    console.log(`  - removeFromCart: ${typeof window.removeFromCart}`);
    
    // Verificar modal
    const modal = document.getElementById('CartModal');
    console.log(`🪟 Modal CartModal: ${modal ? 'EXISTS' : 'NOT FOUND'}`);
    
    if (modal) {
        const cartItems = modal.querySelector('#cart-items');
        console.log(`📋 Container cart-items: ${cartItems ? 'EXISTS' : 'NOT FOUND'}`);
    }
    
    console.log('🎯 Debug completado');
};

// Función para simular agregar producto de prueba
window.addTestProductToCart = function() {
    console.log('🧪 Agregando producto de prueba...');
    
    const testProduct = {
        nombre: 'Producto de Prueba Debug',
        precio: 75000,
        cantidad: 1,
        talla: 'M',
        color: 'Azul',
        imagen: 'images/placeholder.svg'
    };
    
    // Usar la función addToCart global
    if (typeof window.addToCart === 'function') {
        window.addToCart(testProduct);
        console.log('✅ Producto de prueba agregado');
    } else {
        console.error('❌ Función addToCart no disponible');
    }
};

console.log('🔧 Funciones de debug del carrito cargadas:');
console.log('  - debugCartSystem()');
console.log('  - addTestProductToCart()');
