// Script rápido para debug y verificación del carrito
console.log('🔧 Ejecutando debug rápido del carrito...');

function quickCartDebug() {
    console.log('='.repeat(50));
    console.log('🔍 DIAGNÓSTICO RÁPIDO DEL CARRITO');
    console.log('='.repeat(50));
    
    // 1. Verificar localStorage
    const carritoData = localStorage.getItem('carrito');
    console.log('1. 📦 Carrito en localStorage:', carritoData ? 'EXISTE' : 'VACÍO');
    
    if (carritoData) {
        try {
            const carrito = JSON.parse(carritoData);
            console.log('2. 📊 Productos en carrito:', carrito.length);
            
            carrito.forEach((item, i) => {
                console.log(`   Producto ${i + 1}:`);
                console.log(`   - Nombre: ${item.nombre || item.name || 'N/A'}`);
                console.log(`   - Precio: ${item.precio || item.price || 'N/A'}`);
                console.log(`   - Cantidad: ${item.cantidad || item.quantity || 'N/A'}`);
                console.log(`   - Talla: ${item.talla || item.size || 'N/A'}`);
                console.log(`   - Estructura: ${JSON.stringify(Object.keys(item))}`);
            });
            
            // 3. Calcular total
            const total1 = carrito.reduce((sum, item) => sum + ((item.precio || item.price) * (item.cantidad || item.quantity)), 0);
            console.log('3. 💰 Total calculado:', total1);
            
        } catch (error) {
            console.error('❌ Error parseando carrito:', error);
        }
    }
    
    // 4. Verificar funciones
    console.log('4. 🔧 Funciones disponibles:');
    console.log(`   - addToCart: ${typeof window.addToCart}`);
    console.log(`   - updatePaymentSummary: ${typeof window.updatePaymentSummary}`);
    
    // 5. Verificar modal
    const modal = document.getElementById('PaymentModal');
    console.log('5. 🪟 PaymentModal:', modal ? 'EXISTE' : 'NO ENCONTRADO');
    
    if (modal) {
        const container = document.getElementById('mercadopago-payment-container');
        console.log('   - Container MP:', container ? 'EXISTE' : 'NO ENCONTRADO');
        
        const form = document.getElementById('mp-form');
        console.log('   - Formulario:', form ? 'EXISTE' : 'NO ENCONTRADO');
    }
    
    console.log('='.repeat(50));
}

// Ejecutar automáticamente
setTimeout(quickCartDebug, 1000);

// Hacer disponible globalmente
window.quickCartDebug = quickCartDebug;
