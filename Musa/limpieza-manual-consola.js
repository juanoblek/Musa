/**
 * 🎯 HERRAMIENTA MANUAL DE LIMPIEZA - USAR EN CONSOLA
 * Copia y pega este código directamente en la consola del navegador
 */

// FUNCIÓN DE LIMPIEZA MANUAL COMPLETA
function limpiarProductosEstaticosManual() {
    console.log('🎯 INICIANDO LIMPIEZA MANUAL COMPLETA...');
    
    let totalEliminado = 0;
    
    // 1. Eliminar por IDs exactos
    const staticIds = ['pantalon-1', 'chaqueta-1', 'blazer-1', 'camisa-1', 'chaqueta-mujer-1'];
    staticIds.forEach(id => {
        const elementos = document.querySelectorAll(`[data-id="${id}"]`);
        elementos.forEach(elemento => {
            const contenedor = elemento.closest('.col-lg-4, .col-md-6, .col-sm-6, .col-12, .col, .product-card, .card');
            if (contenedor) {
                contenedor.remove();
                console.log(`✅ Eliminado contenedor de: ${id}`);
                totalEliminado++;
            }
        });
    });
    
    // 2. Eliminar por precios específicos + ID corto
    const preciosEstaticos = ['99999', '139999', '189999', '79999', '129999'];
    preciosEstaticos.forEach(precio => {
        const elementos = document.querySelectorAll(`[data-price="${precio}"]`);
        elementos.forEach(elemento => {
            const dataId = elemento.getAttribute('data-id');
            if (dataId && dataId.length < 15 && dataId.includes('-')) {
                const contenedor = elemento.closest('.col-lg-4, .col-md-6, .col-sm-6, .col-12, .col, .product-card, .card');
                if (contenedor) {
                    contenedor.remove();
                    console.log(`✅ Eliminado por precio ${precio}: ${dataId}`);
                    totalEliminado++;
                }
            }
        });
    });
    
    // 3. Verificar productos restantes
    const productosRestantes = document.querySelectorAll('[data-id]');
    console.log(`📊 RESULTADO:`);
    console.log(`   🗑️ Productos eliminados: ${totalEliminado}`);
    console.log(`   📦 Productos restantes: ${productosRestantes.length}`);
    
    // 4. Mostrar lista de productos restantes
    productosRestantes.forEach((producto, index) => {
        const id = producto.getAttribute('data-id');
        const name = producto.getAttribute('data-name');
        const price = producto.getAttribute('data-price');
        console.log(`   ${index + 1}. ID: ${id} | Nombre: ${name} | Precio: $${price}`);
    });
    
    return {
        eliminados: totalEliminado,
        restantes: productosRestantes.length
    };
}

// FUNCIÓN PARA ELIMINAR TODOS LOS BOTONES CON CLASES ESPECÍFICAS
function eliminarBotonesEspecificos() {
    let eliminados = 0;
    
    // Eliminar botones con clases btn-hover-glow que contengan IDs estáticos
    const botonesHover = document.querySelectorAll('.btn-hover-glow[data-id]');
    botonesHover.forEach(boton => {
        const dataId = boton.getAttribute('data-id');
        if (dataId && (dataId === 'pantalon-1' || dataId === 'chaqueta-1' || dataId === 'blazer-1' || dataId === 'camisa-1' || dataId === 'chaqueta-mujer-1')) {
            const contenedor = boton.closest('.col-lg-4, .col-md-6, .col-sm-6, .col-12, .col, .product-card, .card');
            if (contenedor) {
                contenedor.remove();
                eliminados++;
                console.log(`✅ Eliminado botón hover: ${dataId}`);
            }
        }
    });
    
    return eliminados;
}

// EJECUTAR LIMPIEZA COMPLETA
console.log('🎯 HERRAMIENTAS DE LIMPIEZA MANUAL CARGADAS');
console.log('📝 Usa las siguientes funciones:');
console.log('   limpiarProductosEstaticosManual() - Limpieza completa');
console.log('   eliminarBotonesEspecificos() - Eliminar botones específicos');

// Ejecutar automáticamente
setTimeout(() => {
    limpiarProductosEstaticosManual();
    eliminarBotonesEspecificos();
}, 1000);
