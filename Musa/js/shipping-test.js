/**
 * SCRIPT DE TESTING PARA VERIFICAR QUE EL SHIPPING ESTÁ CORREGIDO
 * Ejecuta: testShippingFix() en la consola del navegador
 */

window.testShippingFix = function() {
    console.log('🧪 [SHIPPING-TEST] Iniciando pruebas de corrección de shipping...');
    console.log('='.repeat(60));
    
    let testResults = {
        passed: 0,
        failed: 0,
        warnings: 0
    };
    
    // TEST 1: Verificar localStorage del carrito
    console.log('📦 TEST 1: Verificando carrito en localStorage...');
    try {
        const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        const subtotal = cart.reduce((sum, item) => {
            const precio = parseFloat(item.precio || item.price) || 0;
            const cantidad = parseInt(item.cantidad || item.quantity) || 1;
            return sum + (precio * cantidad);
        }, 0);
        
        console.log(`   ✅ Carrito tiene ${cart.length} productos`);
        console.log(`   ✅ Subtotal calculado: $${subtotal.toLocaleString()}`);
        testResults.passed++;
    } catch (error) {
        console.error('   ❌ Error leyendo carrito:', error);
        testResults.failed++;
    }
    
    // TEST 2: Verificar elementos DOM de subtotal
    console.log('💰 TEST 2: Verificando elementos [data-subtotal]...');
    const subtotalElements = document.querySelectorAll('[data-subtotal]');
    console.log(`   📍 Encontrados ${subtotalElements.length} elementos de subtotal`);
    
    subtotalElements.forEach((el, index) => {
        console.log(`   - Subtotal ${index}: ${el.textContent}`);
    });
    
    if (subtotalElements.length > 0) {
        testResults.passed++;
    } else {
        console.warn('   ⚠️ No se encontraron elementos de subtotal');
        testResults.warnings++;
    }
    
    // TEST 3: Verificar elementos DOM de envío
    console.log('🚚 TEST 3: Verificando elementos de envío...');
    const shippingElements = document.querySelectorAll('[data-envio], [data-shipping]');
    console.log(`   📍 Encontrados ${shippingElements.length} elementos de envío`);
    
    let shippingCorrect = true;
    shippingElements.forEach((el, index) => {
        const text = el.textContent || '';
        console.log(`   - Envío ${index}: "${text}"`);
        
        if (!text.toLowerCase().includes('gratis') && text !== '$0' && text !== '0') {
            console.error(`   ❌ Envío ${index} INCORRECTO: debería ser "GRATIS"`);
            shippingCorrect = false;
        }
    });
    
    if (shippingCorrect) {
        console.log('   ✅ Todos los envíos están marcados como GRATIS');
        testResults.passed++;
    } else {
        console.error('   ❌ Hay elementos de envío incorrectos');
        testResults.failed++;
    }
    
    // TEST 4: Verificar elementos DOM de total (CRÍTICO)
    console.log('🎯 TEST 4: Verificando elementos [data-total]...');
    const totalElements = document.querySelectorAll('[data-total]');
    console.log(`   📍 Encontrados ${totalElements.length} elementos de total`);
    
    let totalsCorrect = true;
    const expectedSubtotal = subtotalElements.length > 0 ? 
        parseInt((subtotalElements[0].textContent || '0').replace(/[^\d]/g, '')) : 0;
    
    totalElements.forEach((el, index) => {
        const text = el.textContent || '';
        const number = parseInt(text.replace(/[^\d]/g, '')) || 0;
        console.log(`   - Total ${index}: "${text}" (${number})`);
        
        if (expectedSubtotal > 0 && number !== expectedSubtotal) {
            console.error(`   ❌ Total ${index} INCORRECTO: es ${number}, debería ser ${expectedSubtotal}`);
            console.error(`   ❌ Diferencia: +${number - expectedSubtotal} (probablemente shipping)`);
            totalsCorrect = false;
        }
    });
    
    if (totalsCorrect && totalElements.length > 0) {
        console.log('   ✅ Todos los totales son correctos (total = subtotal)');
        testResults.passed++;
    } else if (totalElements.length === 0) {
        console.warn('   ⚠️ No se encontraron elementos de total');
        testResults.warnings++;
    } else {
        console.error('   ❌ Hay totales incorrectos');
        testResults.failed++;
    }
    
    // TEST 5: Verificar window.carritoTotales
    console.log('🌐 TEST 5: Verificando window.carritoTotales...');
    if (window.carritoTotales) {
        console.log('   📊 window.carritoTotales encontrado:', window.carritoTotales);
        
        if (window.carritoTotales.shipping === 0 && 
            window.carritoTotales.total === window.carritoTotales.subtotal) {
            console.log('   ✅ window.carritoTotales es correcto');
            testResults.passed++;
        } else {
            console.error('   ❌ window.carritoTotales tiene valores incorrectos');
            console.error('   ❌ shipping:', window.carritoTotales.shipping, '(debería ser 0)');
            console.error('   ❌ total:', window.carritoTotales.total, 'vs subtotal:', window.carritoTotales.subtotal);
            testResults.failed++;
        }
    } else {
        console.warn('   ⚠️ window.carritoTotales no está definido');
        testResults.warnings++;
    }
    
    // TEST 6: Verificar funciones de corrección
    console.log('🛠️ TEST 6: Verificando funciones de corrección...');
    const functions = [
        'forceFixShipping',
        'fixCartModal',
        'correctTotals',
        'verifyShipping'
    ];
    
    functions.forEach(funcName => {
        if (typeof window[funcName] === 'function') {
            console.log(`   ✅ ${funcName}() está disponible`);
            testResults.passed++;
        } else {
            console.error(`   ❌ ${funcName}() NO está disponible`);
            testResults.failed++;
        }
    });
    
    // RESUMEN DE RESULTADOS
    console.log('='.repeat(60));
    console.log('📋 RESUMEN DE PRUEBAS:');
    console.log(`   ✅ Pruebas exitosas: ${testResults.passed}`);
    console.log(`   ❌ Pruebas fallidas: ${testResults.failed}`);
    console.log(`   ⚠️ Advertencias: ${testResults.warnings}`);
    
    if (testResults.failed === 0) {
        console.log('🎉 ¡TODAS LAS PRUEBAS PASARON! El shipping está funcionando correctamente.');
        return true;
    } else {
        console.error('🚨 HAY PROBLEMAS QUE NECESITAN CORRECCIÓN');
        
        // Ejecutar correcciones automáticas
        console.log('🛠️ Intentando corrección automática...');
        if (typeof window.forceFixShipping === 'function') window.forceFixShipping();
        if (typeof window.fixCartModal === 'function') window.fixCartModal();
        if (typeof window.correctTotals === 'function') window.correctTotals();
        
        console.log('✅ Correcciones ejecutadas. Ejecuta testShippingFix() nuevamente para verificar.');
        return false;
    }
};

// También crear una función para testing manual rápido
window.quickTest = function() {
    console.log('⚡ QUICK TEST - Estado actual:');
    
    const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
    const subtotal = cart.reduce((sum, item) => sum + (parseFloat(item.precio || item.price) * parseInt(item.cantidad || item.quantity)), 0);
    
    console.log('📊 Estado:');
    console.log(`   - Productos en carrito: ${cart.length}`);
    console.log(`   - Subtotal: $${subtotal.toLocaleString()}`);
    
    const totalElements = document.querySelectorAll('[data-total]');
    console.log(`   - Elementos de total encontrados: ${totalElements.length}`);
    
    totalElements.forEach((el, i) => {
        const value = parseInt(el.textContent.replace(/[^\d]/g, '')) || 0;
        const isCorrect = value === subtotal;
        console.log(`   - Total ${i}: $${value.toLocaleString()} ${isCorrect ? '✅' : '❌'}`);
    });
    
    const shippingElements = document.querySelectorAll('[data-envio], [data-shipping]');
    console.log(`   - Elementos de envío: ${shippingElements.length}`);
    shippingElements.forEach((el, i) => {
        const text = el.textContent || '';
        const isCorrect = text.toLowerCase().includes('gratis') || text === '$0';
        console.log(`   - Envío ${i}: "${text}" ${isCorrect ? '✅' : '❌'}`);
    });
};

console.log('🧪 [SHIPPING-TEST] Funciones de testing cargadas:');
console.log('   - testShippingFix() - Prueba completa');
console.log('   - quickTest() - Prueba rápida');
console.log('Ejecuta cualquiera de estas funciones en la consola para verificar el estado.');