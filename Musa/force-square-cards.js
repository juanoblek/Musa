console.log('🔧 APLICANDO ESTILOS CUADRADOS MANUALMENTE...');

// Función para forzar tarjetas cuadradas
function forceSquareCards() {
    // Aplicar estilos al grid
    const grid = document.getElementById('main-products-grid') || document.querySelector('.main-products-grid');
    if (grid) {
        grid.style.cssText = `
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(280px, 320px)) !important;
            gap: 25px !important;
            justify-content: center !important;
            padding: 0 20px !important;
            margin: 0 auto !important;
            max-width: 1200px !important;
        `;
        console.log('✅ Grid estilos aplicados');
    }
    
    // Aplicar estilos a las tarjetas
    const cards = document.querySelectorAll('.product-card, .main-product-card');
    console.log(`🔧 Encontradas ${cards.length} tarjetas para ajustar`);
    
    cards.forEach((card, index) => {
        // Contenedor principal
        card.style.cssText = `
            max-width: 320px !important;
            min-width: 280px !important;
            width: 100% !important;
            flex: 0 0 auto !important;
            margin: 0 auto !important;
        `;
        
        // Tarjeta interna
        const innerCard = card.querySelector('.product-item, .card');
        if (innerCard) {
            innerCard.style.cssText = `
                width: 100% !important;
                aspect-ratio: 3/4 !important;
                max-width: 320px !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
                border-radius: 20px !important;
            `;
        }
        
        // Imagen
        const imageWrap = card.querySelector('.card-img-wrap, .main-product-image');
        if (imageWrap) {
            imageWrap.style.cssText = `
                height: 200px !important;
                flex-shrink: 0 !important;
                border-radius: 20px 20px 0 0 !important;
            `;
        }
        
        // Contenido
        const content = card.querySelector('.card-body');
        if (content) {
            content.style.cssText = `
                flex: 1 !important;
                padding: 15px !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
            `;
        }
        
        console.log(`✅ Tarjeta ${index + 1} ajustada`);
    });
    
    console.log('🎯 ESTILOS CUADRADOS APLICADOS EXITOSAMENTE');
}

// Ejecutar inmediatamente
forceSquareCards();

// También ejecutar después de un delay para asegurar que las tarjetas estén cargadas
setTimeout(() => {
    console.log('🔄 Re-aplicando estilos después de delay...');
    forceSquareCards();
}, 1000);

console.log('📋 Para aplicar manualmente en la consola del navegador, copia y pega: forceSquareCards()');
