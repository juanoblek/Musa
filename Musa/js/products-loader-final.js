class MainProductsLoader {
    constructor() {
        this.products = [];
        this.categories = {};
    }

    // Método para formatear precios
    formatPrice(price) {
        if (!price || isNaN(price)) return '0';
        return Number(price).toLocaleString('es-CO');
    }

    async init() {
        console.log('🚀 Iniciando MainProductsLoader');
        
        // ELIMINAR TODOS LOS CONTENEDORES PROBLEMÁTICOS
        this.clearOldContainers();
        
        // CREAR CONTENEDOR PRINCIPAL PERFECTO
        this.createMainContainer();
        
        try {
            // Cargar categorías
            await this.loadCategories();
            
            // Cargar productos
            await this.loadProducts();
            
            // Renderizar productos
            this.renderProducts();
            
            // Configurar filtros
            this.setupFilters();
            
            console.log('✅ MainProductsLoader iniciado correctamente');
        } catch (error) {
            console.error('❌ Error al cargar productos:', error);
        }
    }

    clearOldContainers() {
        // ELIMINAR TODOS LOS CONTENEDORES PROBLEMÁTICOS
        const selectors = [
            '#clean-api-products',
            '#api-products-display', 
            '.clean-products-section',
            '.products-container',
            '#products-grid',
            '#main-products-container' // Eliminar el contenedor actual si existe
        ];
        
        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                console.log('🗑️ Eliminando contenedor:', selector);
                el.remove();
            });
        });
        
        // Limpiar específicamente la sección prendas-exclusivas de contenido problemático
        const prendasSection = document.getElementById('prendas-exclusivas');
        if (prendasSection) {
            // Solo limpiar si tiene contenido JavaScript problemático
            const hasProblematicContent = prendasSection.innerHTML.includes('// Debug específico') ||
                                        prendasSection.innerHTML.includes('console.log');
            if (hasProblematicContent) {
                console.log('🧹 Limpiando contenido problemático de prendas-exclusivas');
                prendasSection.innerHTML = '<!-- Productos se cargarán aquí -->';
            }
        }
        
        // Eliminar estilos anteriores
        const oldStyles = document.querySelectorAll('#clean-products-styles, #main-products-styles');
        oldStyles.forEach(style => style.remove());
    }

    createMainContainer() {
        // ENCONTRAR LA UBICACIÓN EXACTA DESPUÉS DEL VIDEO
        const targetLocation = this.findTargetLocation();

        // CREAR CONTENEDOR PRINCIPAL DESPUÉS DEL VIDEO
        const container = document.createElement('section');
        container.id = 'main-products-container';
        container.className = 'main-products-section';
        
        // ESTILOS ULTRA VISIBLES PARA GARANTIZAR QUE SE VEA
        container.style.cssText = `
            display: block !important;
            position: relative !important;
            width: 100% !important;
            max-width: 1200px !important;
            margin: 80px auto !important;
            padding: 50px !important;
            background: white !important;
            border: 3px solid #007bff !important;
            border-radius: 20px !important;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2) !important;
            z-index: 100 !important;
            min-height: 700px !important;
            clear: both !important;
            overflow: visible !important;
            visibility: visible !important;
            opacity: 1 !important;
        `;
        
        // SISTEMA DE CATEGORÍAS
        const categoriesContainer = document.createElement('div');
        categoriesContainer.className = 'categories-container';
        categoriesContainer.style.cssText = `
            margin-bottom: 30px !important;
            text-align: center !important;
        `;
        
        // Botón "Todos"
        const allButton = document.createElement('button');
        allButton.innerText = 'Todos los Productos';
        allButton.className = 'category-btn active';
        allButton.dataset.category = 'all';
        
        categoriesContainer.appendChild(allButton);
        
        // Agregar botones de categorías (se cargarán dinámicamente)
        this.categoriesContainer = categoriesContainer;
        container.appendChild(categoriesContainer);

        // GRID DE PRODUCTOS MEJORADO
        const grid = document.createElement('div');
        grid.id = 'main-products-grid';
        grid.className = 'main-products-grid';
        grid.style.cssText = `
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(280px, 320px)) !important;
            gap: 25px !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            justify-content: center !important;
        `;
        container.appendChild(grid);
        
        console.log('🔧 Grid creado con ID:', grid.id);
        console.log('🔧 Grid appendChild exitoso');

        // INSERTAR INMEDIATAMENTE DESPUÉS DE LA SECCIÓN DEL VIDEO
        
        // Buscar la sección que contiene el video
        const videoWrap = document.querySelector('.video-wrap');
        let videoSection = null;
        
        if (videoWrap) {
            videoSection = videoWrap.parentNode;
            while (videoSection && videoSection.tagName !== 'SECTION') {
                videoSection = videoSection.parentNode;
            }
        }
        
        if (videoSection && videoSection.parentNode) {
            // Insertar inmediatamente después de la sección del video
            videoSection.parentNode.insertBefore(container, videoSection.nextSibling);
            console.log('✅ Contenedor insertado INMEDIATAMENTE DESPUÉS de la sección del video');
        } else {
            // Fallback: buscar overlay y insertar después de su sección
            const overlay = document.querySelector('.overlay');
            if (overlay) {
                let overlaySection = overlay.parentNode;
                while (overlaySection && overlaySection.tagName !== 'SECTION') {
                    overlaySection = overlaySection.parentNode;
                }
                
                if (overlaySection && overlaySection.parentNode) {
                    overlaySection.parentNode.insertBefore(container, overlaySection.nextSibling);
                    console.log('✅ Contenedor insertado después de la sección con overlay');
                } else {
                    // Último fallback: al final del body
                    document.body.appendChild(container);
                    console.log('✅ Contenedor insertado en body (fallback)');
                }
            } else {
                document.body.appendChild(container);
                console.log('✅ Contenedor insertado en body (fallback)');
            }
        }

        // INYECTAR ESTILOS
        this.injectStyles();

        console.log('✅ Contenedor principal creado DESPUÉS DEL VIDEO');
        
        // VERIFICACIÓN POST-CREACIÓN
        setTimeout(() => {
            const verifyGrid = document.getElementById('main-products-grid');
            const verifyContainer = document.getElementById('main-products-container');
            console.log('🔧 Verificación post-creación:');
            console.log('  - Container existe:', !!verifyContainer);
            console.log('  - Grid existe:', !!verifyGrid);
            if (verifyGrid) {
                console.log('  - Grid parent:', verifyGrid.parentElement?.id);
                console.log('  - Grid en DOM:', document.body.contains(verifyGrid));
            }
        }, 100);
    }

    findTargetLocation() {
        // BUSCAR EXACTAMENTE DESPUÉS DE LA SECCIÓN DEL VIDEO
        
        // 1. Buscar la sección que contiene el video-wrap
        const videoWrap = document.querySelector('.video-wrap');
        if (videoWrap) {
            // Subir hasta encontrar la sección padre
            let videoSection = videoWrap.parentNode;
            while (videoSection && videoSection.tagName !== 'SECTION') {
                videoSection = videoSection.parentNode;
            }
            
            if (videoSection && videoSection.parentNode) {
                console.log('📍 Ubicación: después de la sección del video');
                return videoSection.parentNode;
            }
        }
        
        // 2. Buscar el overlay del video
        const overlay = document.querySelector('.overlay');
        if (overlay) {
            let overlaySection = overlay.parentNode;
            while (overlaySection && overlaySection.tagName !== 'SECTION') {
                overlaySection = overlaySection.parentNode;
            }
            
            if (overlaySection && overlaySection.parentNode) {
                console.log('📍 Ubicación: después de la sección con overlay');
                return overlaySection.parentNode;
            }
        }

        // 3. Fallback al body
        console.log('📍 Ubicación: body (fallback)');
        return document.body;
    }

    injectStyles() {
        const style = document.createElement('style');
        style.id = 'main-products-styles';
        style.textContent = `
            /* === DISEÑO PROFESIONAL DE TARJETAS DE PRODUCTO === */
            .main-products-section {
                display: block !important;
                visibility: visible !important;
                opacity: 1 !important;
                padding: 60px 20px !important;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            }
            
            .main-products-grid {
                display: grid !important;
                grid-template-columns: repeat(auto-fit, minmax(280px, 320px)) !important;
                gap: 25px !important;
                max-width: 1200px !important;
                margin: 0 auto !important;
                padding: 0 20px !important;
                justify-content: center !important;
            }
            
            .main-product-card {
                display: block !important;
                background: #ffffff !important;
                border: none !important;
                border-radius: 20px !important;
                padding: 0 !important;
                text-align: center !important;
                box-shadow: 0 8px 32px rgba(0,0,0,0.1) !important;
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                position: relative !important;
                width: 100% !important;
                max-width: 320px !important;
                min-width: 280px !important;
                min-height: 480px !important;
                height: auto !important;
                overflow: visible !important;
                opacity: 1 !important;
                visibility: visible !important;
                transform: none !important;
                border: 1px solid rgba(0,0,0,0.05) !important;
            }
            
            .main-product-card:hover {
                transform: translateY(-12px) scale(1.02) !important;
                box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
                border-color: #007bff !important;
            }
            
            .main-product-image {
                width: 100% !important;
                min-height: 200px !important;
                max-height: 300px !important;
                height: auto !important;
                margin: 0 !important;
                overflow: hidden !important;
                border-radius: 20px 20px 0 0 !important;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                position: relative !important;
                flex-shrink: 0 !important;
            }
            
            .main-product-image img {
                max-width: 100% !important;
                max-height: 100% !important;
                width: auto !important;
                height: auto !important;
                object-fit: contain !important;
                border-radius: 0 !important;
                transition: transform 0.4s ease !important;
            }
            
            .main-product-card:hover .main-product-image img {
                transform: scale(1.1) !important;
            }
            
            .main-product-content {
                padding: 12px !important;
                background: white !important;
                min-height: auto !important;
                height: auto !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                overflow: visible !important;
            }
            
            .main-product-name {
                font-size: 0.95rem !important;
                margin: 0 0 6px 0 !important;
                color: #2c3e50 !important;
                font-weight: 700 !important;
                line-height: 1.2 !important;
                min-height: auto !important;
                font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif !important;
                letter-spacing: -0.02em !important;
                text-overflow: ellipsis !important;
                overflow: hidden !important;
                white-space: nowrap !important;
            }
            
            .main-product-price {
                margin: 6px 0 !important;
                text-align: center !important;
                padding: 6px 0 !important;
                border-top: 1px solid #f1f3f4 !important;
                border-bottom: 1px solid #f1f3f4 !important;
            }
            
            .original-price {
                font-size: 0.9rem !important;
                color: #6c757d !important;
                text-decoration: line-through !important;
                margin-right: 8px !important;
                font-weight: 500 !important;
                display: block !important;
                margin-bottom: 2px !important;
            }
            
            .sale-price {
                font-size: 1.2rem !important;
                color: #e74c3c !important;
                font-weight: 800 !important;
                display: block !important;
            }
            
            .current-price {
                font-size: 1.2rem !important;
                color: #2c3e50 !important;
                font-weight: 800 !important;
                display: block !important;
            }
            
            .main-product-category {
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
                color: white !important;
                padding: 6px 12px !important;
                border-radius: 20px !important;
                font-size: 0.7rem !important;
                text-transform: uppercase !important;
                margin: 8px 0 !important;
                display: inline-block !important;
                font-weight: 600 !important;
                letter-spacing: 1px !important;
                box-shadow: 0 4px 15px rgba(0,123,255,0.3) !important;
            }
            
            /* === ESTILOS PROFESIONALES PARA COLORES Y TALLAS === */
            .main-product-colors, .main-product-sizes {
                margin: 8px 0 !important;
                text-align: center !important;
                padding: 6px 0 !important;
            }
            
            .colors-label, .sizes-label {
                font-size: 0.8rem !important;
                color: #495057 !important;
                font-weight: 600 !important;
                margin-right: 8px !important;
                text-transform: uppercase !important;
                letter-spacing: 0.5px !important;
                display: inline-block !important;
            }
            
            .color-badge {
                display: inline-block !important;
                background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
                color: white !important;
                padding: 4px 8px !important;
                border-radius: 12px !important;
                font-size: 0.7rem !important;
                margin: 2px 3px !important;
                font-weight: 600 !important;
                text-transform: capitalize !important;
                box-shadow: 0 2px 8px rgba(23,162,184,0.3) !important;
                transition: all 0.3s ease !important;
                border: 2px solid transparent !important;
            }
            
            .color-badge:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 15px rgba(23,162,184,0.4) !important;
                border-color: #17a2b8 !important;
            }
            
            .size-badge {
                display: inline-block !important;
                background: linear-gradient(135deg, #343a40 0%, #212529 100%) !important;
                color: white !important;
                padding: 6px 12px !important;
                border-radius: 15px !important;
                font-size: 0.75rem !important;
                margin: 2px 4px !important;
                font-weight: 600 !important;
                text-transform: uppercase !important;
                box-shadow: 0 2px 8px rgba(52,58,64,0.3) !important;
                transition: all 0.3s ease !important;
                border: 2px solid transparent !important;
                min-width: 35px !important;
                text-align: center !important;
                cursor: pointer !important;
                white-space: nowrap !important;
            }
            
            .size-badge:hover {
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 15px rgba(52,58,64,0.4) !important;
                border-color: #343a40 !important;
                background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            }

            /* Estilos específicos para contenedor de tallas */
            .size-options {
                display: flex !important;
                align-items: center !important;
                gap: 8px !important;
                flex-wrap: wrap !important;
            }

            .size-options .text-muted {
                margin: 0 !important;
                min-width: auto !important;
                flex-shrink: 0 !important;
            }
            
            .main-product-button {
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
                color: white !important;
                border: none !important;
                padding: 10px 20px !important;
                border-radius: 25px !important;
                cursor: pointer !important;
                font-size: 0.9rem !important;
                margin-top: 10px !important;
                width: 100% !important;
                font-weight: 700 !important;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
                box-shadow: 0 4px 15px rgba(40,167,69,0.3) !important;
                position: relative !important;
                overflow: hidden !important;
            }
            
            .main-product-button:hover {
                background: linear-gradient(135deg, #218838 0%, #1e7e34 100%) !important;
                transform: translateY(-3px) !important;
                box-shadow: 0 10px 30px rgba(40,167,69,0.4) !important;
            }
            
            .main-product-button:active {
                transform: translateY(-1px) !important;
            }
            
            .main-product-card.hidden {
                display: none !important;
            }
            
            /* === RESPONSIVE DESIGN === */
            @media (max-width: 768px) {
                .main-products-grid {
                    grid-template-columns: repeat(auto-fit, minmax(260px, 300px)) !important;
                    gap: 20px !important;
                    padding: 0 15px !important;
                    justify-content: center !important;
                }
                
                .main-product-card {
                    margin: 0 auto !important;
                    width: 100% !important;
                    max-width: 300px !important;
                    min-width: 260px !important;
                    min-height: 420px !important;
                    height: auto !important;
                }
                
                .main-product-image {
                    min-height: 180px !important;
                    max-height: 250px !important;
                    height: auto !important;
                }
                
                .main-product-content {
                    padding: 10px !important;
                    height: auto !important;
                    min-height: auto !important;
                }
                
                .main-product-name {
                    font-size: 1rem !important;
                }
                
                .sale-price, .current-price {
                    font-size: 1.2rem !important;
                }
            }
            
            /* === ANIMACIONES SUAVES === */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .main-product-card {
                animation: fadeInUp 0.6s ease-out !important;
            }
            
            .main-product-card:nth-child(2) {
                animation-delay: 0.1s !important;
            }
            
            .main-product-card:nth-child(3) {
                animation-delay: 0.2s !important;
            }
            
            .main-product-card:nth-child(4) {
                animation-delay: 0.3s !important;
            }
            }

            .size-badge {
                display: inline-block !important;
                background: #343a40 !important;
                color: white !important;
                padding: 4px 8px !important;
                border-radius: 12px !important;
                font-size: 0.75rem !important;
                margin: 2px 4px !important;
                font-weight: 500 !important;
                text-transform: uppercase !important;
            }
            
            .main-product-card.hidden {
                display: none !important;
            }
            
            /* Estilos para categorías */
            .categories-container {
                margin-bottom: 30px !important;
                text-align: center !important;
                padding: 20px 0 !important;
            }
            
            .category-btn {
                display: inline-block !important;
                margin: 5px 10px !important;
                padding: 12px 24px !important;
                border: 2px solid #007bff !important;
                background: white !important;
                color: #007bff !important;
                border-radius: 25px !important;
                cursor: pointer !important;
                transition: all 0.3s ease !important;
                font-weight: 600 !important;
                font-size: 14px !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
            }
            
            .category-btn:hover {
                background: #007bff !important;
                color: white !important;
                transform: translateY(-2px) !important;
                box-shadow: 0 4px 15px rgba(0,123,255,0.3) !important;
            }
            
            .category-btn.active {
                background: #007bff !important;
                color: white !important;
                box-shadow: 0 4px 15px rgba(0,123,255,0.4) !important;
            }
            
            /* Clase para elementos filtrados y ocultos */
            .filtered-hidden {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                position: absolute !important;
                left: -9999px !important;
                height: 0 !important;
                width: 0 !important;
                overflow: hidden !important;
                z-index: -1 !important;
            }

            /* === ESTILOS ULTRA LLAMATIVOS PARA BOTÓN DEL CARRITO === */
            .add-to-cart-btn {
                position: relative !important;
                background: linear-gradient(45deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3) !important;
                background-size: 400% 400% !important;
                border: none !important;
                border-radius: 25px !important;
                color: white !important;
                font-weight: 800 !important;
                font-size: 1.1rem !important;
                text-transform: uppercase !important;
                letter-spacing: 1px !important;
                overflow: hidden !important;
                box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4) !important;
                transform: translateY(0) !important;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) !important;
                animation: gradientShift 3s ease infinite !important;
                cursor: pointer !important;
                margin-top: 15px !important;
                padding: 15px 20px !important;
                min-height: 55px !important;
            }

            .add-to-cart-btn::before {
                content: '' !important;
                position: absolute !important;
                top: 0 !important;
                left: -100% !important;
                width: 100% !important;
                height: 100% !important;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent) !important;
                transition: left 0.5s !important;
            }

            .add-to-cart-btn:hover {
                transform: translateY(-3px) scale(1.05) !important;
                box-shadow: 0 15px 35px rgba(255, 107, 107, 0.6) !important;
                animation-duration: 1s !important;
            }

            .add-to-cart-btn:hover::before {
                left: 100% !important;
            }

            .add-to-cart-btn:active {
                transform: translateY(-1px) scale(1.02) !important;
                animation: pulse 0.3s ease !important;
            }

            .add-to-cart-btn .btn-content {
                position: relative !important;
                z-index: 2 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: all 0.3s ease !important;
            }

            .add-to-cart-btn:hover .btn-content {
                transform: scale(1.1) !important;
            }

            .add-to-cart-btn .fas {
                margin-right: 8px !important;
                font-size: 1.2rem !important;
                animation: bounce 2s infinite !important;
            }

            .add-to-cart-btn:hover .fas {
                animation: spin 0.5s ease !important;
            }

            .btn-glow {
                position: absolute !important;
                top: -2px !important;
                left: -2px !important;
                right: -2px !important;
                bottom: -2px !important;
                background: linear-gradient(45deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3) !important;
                border-radius: 25px !important;
                opacity: 0 !important;
                z-index: -1 !important;
                filter: blur(10px) !important;
                transition: opacity 0.3s ease !important;
            }

            .add-to-cart-btn:hover .btn-glow {
                opacity: 0.8 !important;
                animation: glowPulse 1.5s ease infinite !important;
            }

            .btn-particles {
                position: absolute !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -50%) !important;
                pointer-events: none !important;
            }

            .btn-particles .particle {
                position: absolute !important;
                width: 4px !important;
                height: 4px !important;
                background: #fff !important;
                border-radius: 50% !important;
                opacity: 0 !important;
            }

            .add-to-cart-btn:hover .particle:nth-child(1) {
                animation: particle1 1s ease !important;
            }

            .add-to-cart-btn:hover .particle:nth-child(2) {
                animation: particle2 1s ease 0.1s !important;
            }

            .add-to-cart-btn:hover .particle:nth-child(3) {
                animation: particle3 1s ease 0.2s !important;
            }

            /* === ANIMACIONES ESPECTACULARES === */
            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            @keyframes glowPulse {
                0%, 100% { 
                    transform: scale(1); 
                    opacity: 0.8; 
                }
                50% { 
                    transform: scale(1.1); 
                    opacity: 1; 
                }
            }

            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-5px); }
                60% { transform: translateY(-3px); }
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            @keyframes pulse {
                0% { transform: translateY(-1px) scale(1.02); }
                50% { transform: translateY(-1px) scale(1.08); }
                100% { transform: translateY(-1px) scale(1.02); }
            }

            @keyframes particle1 {
                0% { 
                    opacity: 0; 
                    transform: translate(0, 0) scale(0); 
                }
                50% { 
                    opacity: 1; 
                    transform: translate(-20px, -20px) scale(1); 
                }
                100% { 
                    opacity: 0; 
                    transform: translate(-40px, -40px) scale(0); 
                }
            }

            @keyframes particle2 {
                0% { 
                    opacity: 0; 
                    transform: translate(0, 0) scale(0); 
                }
                50% { 
                    opacity: 1; 
                    transform: translate(20px, -20px) scale(1); 
                }
                100% { 
                    opacity: 0; 
                    transform: translate(40px, -40px) scale(0); 
                }
            }

            @keyframes particle3 {
                0% { 
                    opacity: 0; 
                    transform: translate(0, 0) scale(0); 
                }
                50% { 
                    opacity: 1; 
                    transform: translate(0, -25px) scale(1); 
                }
                100% { 
                    opacity: 0; 
                    transform: translate(0, -50px) scale(0); 
                }
            }

            /* Animación de éxito al hacer clic */
            .add-to-cart-btn.success {
                background: linear-gradient(45deg, #2ecc71, #27ae60) !important;
                animation: successPulse 0.6s ease !important;
            }

            @keyframes successPulse {
                0% { 
                    transform: translateY(-3px) scale(1.05); 
                }
                50% { 
                    transform: translateY(-3px) scale(1.15); 
                    box-shadow: 0 20px 40px rgba(46, 204, 113, 0.6) !important;
                }
                100% { 
                    transform: translateY(-3px) scale(1.05); 
                }
            }

            /* Responsivo para móviles */
            @media (max-width: 768px) {
                .add-to-cart-btn {
                    font-size: 1rem !important;
                    padding: 12px 16px !important;
                    min-height: 50px !important;
                }
            }
        `;
        document.head.appendChild(style);
    }

    async loadCategories() {
        try {
            const response = await fetch('api/categorias.php');
            const result = await response.json();
            
            if (result.success && result.data) {
                // Guardar categorías
                result.data.forEach(cat => {
                    this.categories[cat.id] = cat.name;
                });
                
                // Crear botones de categorías
                this.createCategoryButtons(result.data);
                
                console.log('✅ Categorías cargadas:', this.categories);
            }
        } catch (error) {
            console.error('❌ Error cargando categorías:', error);
        }
    }

    createCategoryButtons(categories) {
        if (!this.categoriesContainer) return;
        
        // Agregar botones para cada categoría
        categories.forEach(category => {
            const button = document.createElement('button');
            button.innerText = category.name;
            button.className = 'category-btn';
            button.dataset.category = category.id;
            
            // Event listener para filtrar productos
            button.addEventListener('click', () => {
                this.filterByCategory(category.id);
                
                // Actualizar botón activo
                this.categoriesContainer.querySelectorAll('.category-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
            });
            
            this.categoriesContainer.appendChild(button);
        });
        
        // Event listener para el botón "Todos"
        const allButton = this.categoriesContainer.querySelector('[data-category="all"]');
        if (allButton) {
            allButton.addEventListener('click', () => {
                this.filterByCategory('all');
                
                // Actualizar botón activo
                this.categoriesContainer.querySelectorAll('.category-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                allButton.classList.add('active');
            });
        }
    }

    filterByCategory(categoryId) {
        // Marcar que el filtrado de categorías está activo
        window.categoryFilterActive = true;
        
        const grid = document.getElementById('main-products-grid');
        if (!grid) {
            console.error('❌ Grid no encontrado para filtrado');
            return;
        }
        
        const productCards = grid.querySelectorAll('.main-product-card');
        console.log(`🔍 Encontradas ${productCards.length} tarjetas de productos`);
        
        let visibleCount = 0;
        
        productCards.forEach((card, index) => {
            const productCategory = card.dataset.category;
            console.log(`📦 Producto ${index}: categoria="${productCategory}"`);
            
            if (categoryId === 'all') {
                // MOSTRAR: CSS agresivo para garantizar visibilidad
                card.style.cssText = `
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    position: relative !important;
                    height: auto !important;
                    overflow: visible !important;
                `;
                card.classList.remove('filtered-hidden');
                visibleCount++;
            } else {
                if (productCategory === categoryId) {
                    // MOSTRAR: CSS agresivo para garantizar visibilidad
                    card.style.cssText = `
                        display: block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        position: relative !important;
                        height: auto !important;
                        overflow: visible !important;
                    `;
                    card.classList.remove('filtered-hidden');
                    visibleCount++;
                    console.log(`✅ Mostrando producto ${index} (categoría coincide)`);
                } else {
                    // OCULTAR: CSS agresivo para garantizar ocultamiento
                    card.style.cssText = `
                        display: none !important;
                        visibility: hidden !important;
                        opacity: 0 !important;
                        position: absolute !important;
                        left: -9999px !important;
                        height: 0 !important;
                        overflow: hidden !important;
                    `;
                    card.classList.add('filtered-hidden');
                    console.log(`❌ Ocultando producto ${index} (categoría no coincide)`);
                }
            }
        });
        
        console.log(`🎯 Filtrado por categoría: ${categoryId} - ${visibleCount} productos visibles`);
        
        // FORZAR ACTUALIZACIÓN DEL LAYOUT
        grid.style.display = 'none';
        grid.offsetHeight; // Trigger reflow
        grid.style.display = 'grid';
    }

    async loadProducts() {
        try {
            const response = await fetch('api/productos-v2.php');
            const result = await response.json();
            
            console.log('🔍 DEBUG - Respuesta de API:', result);
            
            if (result.success && result.data) {
                // Filtrar productos válidos para evitar undefined
                this.products = result.data.filter(product => product && product.name);
                console.log('✅ Productos cargados:', this.products.length);
                
                // DEBUG: Verificar qué campos de imagen tienen los productos
                this.products.forEach(product => {
                    console.log('🔍 Producto:', product.name);
                    console.log('  - main_image:', product.main_image);
                    console.log('  - image:', product.image);
                    console.log('  - images:', product.images);
                    console.log('  - image_url:', product.image_url);
                });
            } else {
                console.error('❌ Error en respuesta de productos:', result);
                this.products = []; // Asegurar que products sea un array vacío
            }
        } catch (error) {
            console.error('❌ Error cargando productos:', error);
            this.products = []; // Asegurar que products sea un array vacío
        }
    }

    renderProducts() {
        console.log('🔧 DEBUG renderProducts() iniciado');
        
        const grid = document.getElementById('main-products-grid');
        console.log('🔧 Grid encontrado:', !!grid);
        
        if (!grid) {
            console.error('❌ Grid no encontrado');
            
            // INTENTAR RECUPERACIÓN
            console.log('🔧 Intentando encontrar grid alternativo...');
            const alternativeGrid = document.querySelector('.main-products-grid');
            console.log('🔧 Grid alternativo encontrado:', !!alternativeGrid);
            
            if (!alternativeGrid) {
                console.error('❌ No se puede encontrar ningún grid para productos');
                return;
            }
        }

        const targetGrid = grid || document.querySelector('.main-products-grid');
        targetGrid.innerHTML = '';

        // Verificación adicional: asegurar que products sea un array válido
        if (!Array.isArray(this.products)) {
            console.error('❌ this.products no es un array:', this.products);
            return;
        }

        console.log(`🔧 Renderizando ${this.products.length} productos...`);

        this.products.forEach((product, index) => {
            if (product && product.name) {
                console.log(`🔧 Renderizando producto ${index + 1}: ${product.name}`);
                const card = this.createProductCard(product, index);
                targetGrid.appendChild(card);
            } else {
                console.warn('⚠️ Producto inválido omitido:', product, 'en índice', index);
            }
        });

        console.log(`✅ ${this.products.length} productos renderizados`);
        
        // Forzar estilos cuadrados después del renderizado
        this.forceSquareCards();
        
        // Configurar event listeners para botones del carrito
        this.setupCartButtonListeners();
        
        // Scroll al contenedor
        setTimeout(() => {
            const container = document.getElementById('main-products-container');
            if (container) {
                container.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
            }
        }, 300);
    }

    // Configurar event listeners para botones del carrito con animaciones
    setupCartButtonListeners() {
        const cartButtons = document.querySelectorAll('.add-to-cart-btn');
        
        cartButtons.forEach(button => {
            // Efecto de sonido (opcional - se puede comentar si no se quiere)
            const playSound = () => {
                try {
                    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvWAcBjiS1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvWAcBjiS1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvWAcBjiS1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvWAcBjiS1/LNeSsFJHfH8N2QQAoUXrTp66hVFApGn+DyvWA=');
                    audio.volume = 0.1;
                    audio.play();
                } catch(e) {
                    // Silenciar errores de audio
                }
            };

            // Evento principal de clic
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Obtener datos del producto
                const productId = button.dataset.id;
                const productName = button.dataset.name;
                const productPrice = button.dataset.price;
                const productImage = button.dataset.image;
                
                // Animación de éxito
                this.animateCartSuccess(button);
                
                // Reproducir sonido
                playSound();
                
                // Agregar al carrito (aquí puedes integrar tu lógica existente)
                this.addToCart({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage
                });
                
                // Mostrar notificación
                this.showCartNotification(productName);
            });

            // Efecto hover adicional con vibración
            button.addEventListener('mouseenter', () => {
                if (navigator.vibrate) {
                    navigator.vibrate(50);
                }
            });

            // Efecto de partículas al hacer hover
            button.addEventListener('mouseenter', () => {
                this.createHoverParticles(button);
            });
        });
    }

    // Animación de éxito al agregar al carrito
    animateCartSuccess(button) {
        const originalText = button.innerHTML;
        
        // Cambiar a estado de éxito
        button.classList.add('success');
        button.innerHTML = '<span class="btn-content"><i class="fas fa-check me-2"></i>¡Agregado!</span>';
        
        // Crear efecto de explosión de partículas
        this.createSuccessParticles(button);
        
        // Restaurar después de 2 segundos
        setTimeout(() => {
            button.classList.remove('success');
            button.innerHTML = originalText;
        }, 2000);
    }

    // Crear partículas de éxito
    createSuccessParticles(button) {
        const rect = button.getBoundingClientRect();
        const particleCount = 8;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.style.cssText = `
                position: fixed;
                width: 6px;
                height: 6px;
                background: #2ecc71;
                border-radius: 50%;
                pointer-events: none;
                z-index: 10000;
                left: ${rect.left + rect.width / 2}px;
                top: ${rect.top + rect.height / 2}px;
            `;
            
            document.body.appendChild(particle);
            
            // Animación de la partícula
            const angle = (i / particleCount) * Math.PI * 2;
            const distance = 100 + Math.random() * 50;
            const duration = 800 + Math.random() * 400;
            
            particle.animate([
                { 
                    transform: 'translate(0, 0) scale(1)',
                    opacity: 1 
                },
                { 
                    transform: `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0)`,
                    opacity: 0 
                }
            ], {
                duration: duration,
                easing: 'cubic-bezier(0.25, 0.46, 0.45, 0.94)'
            }).onfinish = () => {
                particle.remove();
            };
        }
    }

    // Crear partículas de hover
    createHoverParticles(button) {
        const rect = button.getBoundingClientRect();
        const particle = document.createElement('div');
        
        particle.style.cssText = `
            position: fixed;
            width: 4px;
            height: 4px;
            background: white;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            left: ${rect.left + Math.random() * rect.width}px;
            top: ${rect.top + Math.random() * rect.height}px;
            opacity: 0.8;
        `;
        
        document.body.appendChild(particle);
        
        particle.animate([
            { transform: 'translateY(0px)', opacity: 0.8 },
            { transform: 'translateY(-20px)', opacity: 0 }
        ], {
            duration: 1000,
            easing: 'ease-out'
        }).onfinish = () => {
            particle.remove();
        };
    }

    // Mostrar notificación del carrito
    showCartNotification(productName) {
        // Crear notificación
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(46, 204, 113, 0.3);
            z-index: 10000;
            font-weight: 600;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        notification.innerHTML = `
            <i class="fas fa-check-circle me-2"></i>
            "${productName}" agregado al carrito
        `;
        
        document.body.appendChild(notification);
        
        // Animación de entrada
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animación de salida
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // Agregar al carrito (integrar con sistema existente)
    addToCart(product) {
        console.log('🛒 Agregando al carrito:', product);
        
        try {
            // 1. Priorizar la función global addToCart (más simple y segura)
            if (window.addToCart && typeof window.addToCart === 'function') {
                console.log('🔧 Usando función global addToCart');
                window.addToCart(product.id);
                return; // Salir aquí para evitar duplicados
            }
            
            // 2. Fallback: Sistema propio si no existe la función global
            console.log('🔧 Usando sistema de carrito propio (fallback)');
            
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            // Verificar si el producto ya existe en el carrito
            const existingItemIndex = cart.findIndex(item => item.id === product.id);
            
            if (existingItemIndex > -1) {
                // Incrementar cantidad si ya existe
                cart[existingItemIndex].quantity = (cart[existingItemIndex].quantity || 1) + 1;
            } else {
                // Agregar nuevo producto
                cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    image: product.image,
                    quantity: 1,
                    timestamp: Date.now()
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            console.log('💾 Carrito guardado en localStorage:', cart);
            
            // Actualizar contador del carrito
            this.updateCartCounter(cart);
            
            // Abrir modal del carrito si existe la función global
            if (window.openCartModal && typeof window.openCartModal === 'function') {
                window.openCartModal();
            }
            
        } catch (error) {
            console.error('❌ Error agregando al carrito:', error);
            
            // Último fallback: notificación simple
            if (window.showNotification && typeof window.showNotification === 'function') {
                window.showNotification('❌ Error al agregar producto al carrito', 'error');
            }
        }
    }

    // Actualizar contador del carrito (simplificado)
    updateCartCounter(cart) {
        if (!cart) {
            cart = JSON.parse(localStorage.getItem('cart') || '[]');
        }
        
        const totalItems = cart.reduce((total, item) => total + (item.quantity || 1), 0);
        
        // Usar la función global si existe
        if (window.updateCartCounter && typeof window.updateCartCounter === 'function') {
            window.updateCartCounter();
            return;
        }
        
        // Fallback: actualizar manualmente
        const counterElements = document.querySelectorAll('.cart-count, .cart-counter, .badge, [data-cart-count]');
        
        counterElements.forEach(element => {
            element.textContent = totalItems;
            element.style.display = totalItems > 0 ? 'inline' : 'none';
            
            // Animación simple
            element.style.transform = 'scale(1.2)';
            setTimeout(() => {
                element.style.transform = 'scale(1)';
            }, 200);
        });
        
        console.log('🔄 Contador del carrito actualizado:', totalItems);
    }

    // Abrir modal del carrito
    openCartModal() {
        try {
            // Buscar el modal del carrito
            const cartModal = document.getElementById('CartModal');
            
            if (cartModal) {
                // Usar Bootstrap para abrir el modal
                if (window.bootstrap && window.bootstrap.Modal) {
                    const modal = new window.bootstrap.Modal(cartModal);
                    modal.show();
                    console.log('🛒 Modal del carrito abierto con Bootstrap');
                } else {
                    // Fallback: abrir manualmente
                    cartModal.style.display = 'block';
                    cartModal.classList.add('show');
                    cartModal.setAttribute('aria-hidden', 'false');
                    
                    // Agregar backdrop
                    const backdrop = document.createElement('div');
                    backdrop.className = 'modal-backdrop fade show';
                    document.body.appendChild(backdrop);
                    
                    console.log('🛒 Modal del carrito abierto manualmente');
                }
                
                // Actualizar contenido del carrito
                this.updateCartModalContent();
                
            } else {
                console.warn('⚠️ Modal del carrito no encontrado');
                
                // Fallback: mostrar notificación
                this.showAlternativeCartView();
            }
            
        } catch (error) {
            console.error('❌ Error abriendo modal del carrito:', error);
            this.showAlternativeCartView();
        }
    }

    // Actualizar contenido del modal del carrito
    updateCartModalContent() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const cartModalBody = document.querySelector('#CartModal .modal-body');
        
        if (cartModalBody && cart.length > 0) {
            // Crear HTML del carrito
            const cartHTML = cart.map(item => `
                <div class="cart-item border-bottom py-2">
                    <div class="row align-items-center">
                        <div class="col-3">
                            ${(() => {
                                const imageSrc = item.image || 'images/placeholder.jpg';
                                return generarMediaHTMLSincrono(imageSrc, item.name, "img-fluid rounded", "max-height: 60px; object-fit: cover;");
                            })()}
                        </div>
                        <div class="col-6">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">Cantidad: ${item.quantity || 1}</small>
                        </div>
                        <div class="col-3 text-end">
                            <strong>$${this.formatPrice(item.price * (item.quantity || 1))}</strong>
                        </div>
                    </div>
                </div>
            `).join('');
            
            const total = cart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
            
            cartModalBody.innerHTML = `
                <div class="cart-items">
                    ${cartHTML}
                </div>
                <div class="cart-total mt-3 pt-3 border-top">
                    <div class="row">
                        <div class="col-6">
                            <h5>Total:</h5>
                        </div>
                        <div class="col-6 text-end">
                            <h5 class="text-primary">$${this.formatPrice(total)}</h5>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    // Vista alternativa del carrito (si no hay modal)
    showAlternativeCartView() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const totalItems = cart.reduce((total, item) => total + (item.quantity || 1), 0);
        const totalPrice = cart.reduce((sum, item) => sum + (item.price * (item.quantity || 1)), 0);
        
        // Crear notificación detallada
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid #007bff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 10000;
            max-width: 400px;
            width: 90%;
        `;
        
        notification.innerHTML = `
            <div class="text-center">
                <h5 class="mb-3">🛒 Carrito de Compras</h5>
                <p><strong>${totalItems}</strong> productos en el carrito</p>
                <p>Total: <strong>$${this.formatPrice(totalPrice)}</strong></p>
                <button class="btn btn-primary" onclick="this.parentElement.parentElement.remove()">
                    Cerrar
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Función para forzar tarjetas cuadradas
    forceSquareCards() {
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            // Estilos para el contenedor principal
            card.style.cssText = `
                max-width: 320px !important;
                min-width: 280px !important;
                width: 100% !important;
                flex: 0 0 auto !important;
                margin: 0 auto !important;
            `;
            
            // Estilos para la tarjeta interna
            const innerCard = card.querySelector('.product-item, .card');
            if (innerCard) {
                innerCard.style.cssText = `
                    width: 100% !important;
                    min-height: 480px !important;
                    height: auto !important;
                    max-width: 320px !important;
                    display: flex !important;
                    flex-direction: column !important;
                    overflow: visible !important;
                `;
            }
            
            // Estilos para la imagen
            const imageWrap = card.querySelector('.card-img-wrap, .main-product-image');
            if (imageWrap) {
                imageWrap.style.cssText = `
                    min-height: 200px !important;
                    max-height: 300px !important;
                    height: auto !important;
                    flex-shrink: 0 !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                `;
                
                // Actualizar también la imagen dentro
                const img = imageWrap.querySelector('img');
                if (img) {
                    img.style.cssText = `
                        max-width: 100% !important;
                        max-height: 100% !important;
                        width: auto !important;
                        height: auto !important;
                        object-fit: contain !important;
                    `;
                }
            }
            
            // Estilos para el contenido
            const cardBody = card.querySelector('.card-body');
            if (cardBody) {
                cardBody.style.cssText = `
                    flex: 1 !important;
                    padding: 12px !important;
                    height: auto !important;
                    overflow: visible !important;
                    display: flex !important;
                    flex-direction: column !important;
                    justify-content: space-between !important;
                `;
            }
        });
        
        console.log('🔧 Estilos cuadrados aplicados a las tarjetas');
    }

    // FUNCIÓN UNIFICADA PARA OBTENER RUTA DE IMAGEN DE PRODUCTO
    getProductImagePath(product) {
        // USAR LA FUNCIÓN GLOBAL UNIFICADA PRIMERO
        const unifiedImage = window.getUnifiedProductImage ? window.getUnifiedProductImage(product) : null;
        if (unifiedImage) {
            console.log(`🎯 Usando función global para "${product.name}": ${unifiedImage}`);
            return [unifiedImage];
        }
        
        console.log(`⚠️ Función global no disponible para "${product.name}", usando fallbacks`);
        
        // FALLBACK: Mapeo específico para casos sin main_image
        const productImageMap = {
            'Chaqueta Over': 'images/Chaqueta Beige Botones Dorados/chaqueta_b1.jpeg',
            'Chaqueta de Cuero Premium': 'images/Chaqueta Beige Botones Dorados/Chaqueta B1.jpeg',
            'Camisa Elegante Clásica': 'images/Camisa Blanco Purista/camisa_blanco_purista.jpeg',
            'Pantalón Formal Premium': 'images/BLAZER.png',
            'Blazer Ejecutivo': 'images/BLAZER.png',
            'Vestido Elegante de Noche': 'images/Chaqueta Beige Botones Dorados/chaqueta_b2.jpeg'
        };
        
        // SISTEMA DE RUTAS MÚLTIPLES SOLO COMO FALLBACK
        const possibleImagePaths = [
            // 1. Mapeo específico del producto
            productImageMap[product.name],
            
            // 2. Imágenes por categoría como fallback
            product.category_id === 'chaquetas' ? 'images/Chaqueta Beige Botones Dorados/chaqueta_b1.jpeg' : null,
            product.category_id === 'camisas' ? 'images/Camisa Blanco Purista/camisa_blanco_purista.jpeg' : null,
            product.category_id === 'pantalones' ? 'images/BLAZER.png' : null,
            product.category_id === 'blazers' ? 'images/BLAZER.png' : null,
            
            // 3. Imagen placeholder final
            'images/placeholder.svg',
            'images/clothes-1839935_1920.jpg'
        ].filter(path => path !== null);

        return possibleImagePaths;
    }

    createProductCard(product, index) {
        // Verificación de seguridad
        if (!product) {
            console.error('❌ Error: producto undefined en createProductCard', { product, index });
            return document.createElement('div'); // Retornar elemento vacío
        }
        
        if (!product.name) {
            console.error('❌ Error: producto sin nombre', product);
            return document.createElement('div'); // Retornar elemento vacío
        }
        
        // Calcular precios y descuentos
        const price = product.sale_price || product.price || 0;
        const originalPrice = product.price || 0;
        const hasDiscount = product.sale_price && product.sale_price < product.price;
        const discountPercent = hasDiscount ? Math.round((1 - product.sale_price / product.price) * 100) : 0;
        
        // Generar colores y tallas (compatible con arrays de strings y objetos)
        let colors = product.colors || [];
        let sizes = product.sizes || [];
        
        // Si colors es string (JSON), parsearlo
        if (typeof colors === 'string') {
            try {
                colors = JSON.parse(colors);
            } catch (e) {
                colors = [];
            }
        }
        
        // Si sizes es string (JSON), parsearlo
        if (typeof sizes === 'string') {
            try {
                sizes = JSON.parse(sizes);
            } catch (e) {
                sizes = [];
            }
        }
        
        console.log('🎨 Colores para', product.name, ':', colors);
        console.log('📏 Tallas para', product.name, ':', sizes);
        
        // Crear opciones de colores con códigos hex (maneja objetos y strings)
        const colorOptions = colors.map((color, idx) => {
            let colorCode, colorName;
            
            if (typeof color === 'object') {
                // Nuevo formato: objeto con hex y nombre
                colorCode = color.codigo_hex || color.hex || '#6c757d';
                colorName = color.nombre || color.name || 'Color';
            } else {
                // Formato anterior: string
                colorName = color;
                colorCode = this.getColorCode(color);
            }
            
            console.log(`🔍 [COLOR SETUP DEBUG] Color ${idx}:`, {
                originalColor: color,
                colorName: colorName,
                colorCode: colorCode,
                type: typeof color
            });
            
            // 🔧 Determinar si este botón debe estar activo por defecto (primer color)
            const isFirstColor = idx === 0;
            const activeClass = isFirstColor ? ' active' : '';
            const borderStyle = isFirstColor ? '2px solid #000' : '2px solid transparent';
            
            return `
                <button type="button" 
                        class="btn color-btn rounded-circle${activeClass}" 
                        data-color="${colorName}" 
                        style="background: ${colorCode}; border: ${borderStyle};"
                        title="${colorName}">
                </button>
            `;
        }).join('');
        
        // Crear opciones de tallas (maneja strings y objetos)
        const sizeOptions = sizes.map((size, idx) => {
            const sizeValue = typeof size === 'object' ? size.name || size.valor : size;
            return `
                <button type="button" 
                        class="btn btn-sm btn-outline-dark size-btn" 
                        data-size="${sizeValue}">
                    ${sizeValue}
                </button>
            `;
        }).join('');

        const card = document.createElement('div');
        card.className = 'col-lg-4 col-md-6 mb-4 product-card main-product-card filter';
        card.dataset.category = product.category_id || 'general';
        card.dataset.gender = product.gender || 'unisex';
        card.dataset.productId = product.id;

        card.innerHTML = `
            <div class="card h-100 product-item hover-3d">
                <div class="card-img-wrap position-relative overflow-hidden">
                    ${hasDiscount ? `
                        <div class="position-absolute top-0 start-0 m-2">
                            <span class="badge bg-danger neon-pulse">
                                -${discountPercent}% <span class="d-block fs-8">OFERTA</span>
                            </span>
                        </div>
                    ` : ''}
                    
                    <div class="image-hover-wrapper">
                        ${(() => {
                            const imageSrc = window.getUnifiedProductImage ? window.getUnifiedProductImage(product) : (product.main_image || product.images?.[0]?.image_url || 'images/placeholder.jpg');
                            return generarMediaHTMLSincrono(imageSrc, product.name, "card-img-top product-image glasseffect", "height: 280px; object-fit: cover;");
                        })()}
                        <div class="hover-3d-layer"></div>
                    </div>
                </div>

                <div class="card-body position-relative">
                    <h5 class="card-title text-gradient-primary">${product.name}</h5>
                    
                    <!-- Rating y contador de ventas -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="rating-group">
                            <div class="stars-pulse">
                                <i class="bi-star-fill reviews-icon"></i>
                                <i class="bi-star-fill reviews-icon"></i>
                                <i class="bi-star-fill reviews-icon"></i>
                                <i class="bi-star-fill reviews-icon"></i>
                                <i class="bi-star-fill reviews-icon"></i>
                            </div>
                            <small class="text-muted">${Math.floor(Math.random() * 2000) + 100} Opiniones</small>
                        </div>
                        <div class="sales-counter">
                            <i class="fas fa-bolt text-warning"></i>
                            <span class="badge bg-dark">${Math.floor(Math.random() * 15) + 1} comprados hoy</span>
                        </div>
                    </div>

                    <!-- Selectores de talla y color -->
                    <div class="size-color-selector mb-4">
                        ${sizes.length > 0 ? `
                            <div class="size-options d-flex align-items-center gap-2 mb-3">
                                <small class="text-muted"><strong>Tallas:</strong></small>
                                <div class="d-flex gap-2 flex-wrap">
                                    ${sizeOptions}
                                </div>
                            </div>
                        ` : ''}

                        ${colors.length > 0 ? `
                            <div class="color-options">
                                <small class="text-muted d-block mb-2"><strong>Colores:</strong></small>
                                <div class="d-flex gap-2 flex-wrap">
                                    ${colorOptions}
                                </div>
                            </div>
                        ` : ''}
                    </div>

                    <!-- Precio con efecto de ahorro -->
                    <div class="price-container mb-3">
                        <div class="d-flex align-items-end gap-2">
                            ${hasDiscount ? `
                                <span class="h4 text-primary mb-0">$${this.formatPrice(price)}</span>
                                <del class="text-muted small">$${this.formatPrice(originalPrice)}</del>
                            ` : `
                                <span class="h4 text-primary mb-0">$${this.formatPrice(price)}</span>
                            `}
                        </div>
                        ${hasDiscount ? `
                            <div class="text-success small">
                                <i class="fas fa-wallet"></i> Ahorras $${this.formatPrice(originalPrice - price)} (${discountPercent}%)
                            </div>
                        ` : ''}
                    </div>

                    <!-- Botón mejorado con microinteracciones -->
                    <button class="btn btn-hover-glow w-100 add-to-cart-btn py-3" 
                            data-id="${product.id}"
                            data-name="${product.name}" 
                            data-price="${price}"
                            data-image="${window.getUnifiedProductImage ? window.getUnifiedProductImage(product) : (product.main_image || 'images/placeholder.jpg')}">
                        <span class="btn-content">
                            <i class="fas fa-cart-plus me-2"></i>Añadir al Carrito
                        </span>
                        <span class="btn-glow"></span>
                        <span class="btn-particles">
                            <span class="particle"></span>
                            <span class="particle"></span>
                            <span class="particle"></span>
                        </span>
                    </button>

                    <!-- Sección de garantías eliminada -->
                </div>
            </div>
        `;

        // Forzar estilos para tarjetas cuadradas
        card.style.cssText = `
            max-width: 320px !important;
            min-width: 280px !important;
            width: 100% !important;
            flex: 0 0 auto !important;
            margin: 0 auto !important;
        `;
        
        // Aplicar estilos al contenedor interno de la tarjeta
        const innerCard = card.querySelector('.product-item');
        if (innerCard) {
            innerCard.style.cssText = `
                width: 100% !important;
                aspect-ratio: 3/4 !important;
                max-width: 320px !important;
                display: flex !important;
                flex-direction: column !important;
                overflow: hidden !important;
            `;
        }
        
        // Aplicar estilos a la imagen
        const imageWrap = card.querySelector('.card-img-wrap');
        if (imageWrap) {
            imageWrap.style.cssText = `
                height: 200px !important;
                flex-shrink: 0 !important;
            `;
        }

        // Agregar event listeners para selectores de talla y color
        this.addSelectorListeners(card);

        return card;
    }

    // Método para agregar event listeners a los selectores de talla y color
    addSelectorListeners(card) {
        // Event listeners para botones de talla
        const sizeButtons = card.querySelectorAll('.size-btn');
        sizeButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remover clase active de todos los botones de talla en esta tarjeta
                sizeButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.backgroundColor = '';
                    btn.style.color = '';
                });
                
                // Agregar clase active al botón clickeado
                button.classList.add('active');
                button.style.backgroundColor = '#212529';
                button.style.color = 'white';
                
                console.log('👕 Talla seleccionada:', button.dataset.size);
            });
        });

        // Event listeners para botones de color
        const colorButtons = card.querySelectorAll('.color-btn');
        colorButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remover selección de todos los botones de color en esta tarjeta
                colorButtons.forEach(btn => {
                    btn.style.border = '2px solid transparent';
                    btn.classList.remove('active'); // 🔧 AGREGAR ESTO
                });
                
                // Agregar selección al botón clickeado
                button.style.border = '2px solid #000';
                button.classList.add('active'); // 🔧 AGREGAR ESTO
                
                console.log('🎨 Color seleccionado:', button.dataset.color);
            });
        });
    }

    // Función para obtener códigos de color
    getColorCode(colorName) {
        const colorMap = {
            'negro': '#000000',
            'negra': '#000000',
            'black': '#000000',
            'blanco': '#ffffff',
            'blanca': '#ffffff',
            'white': '#ffffff',
            'rojo': '#dc3545',
            'roja': '#dc3545',
            'red': '#dc3545',
            'azul': '#0d6efd',
            'blue': '#0d6efd',
            'verde': '#198754',
            'green': '#198754',
            'amarillo': '#ffc107',
            'yellow': '#ffc107',
            'rosa': '#e91e63',
            'pink': '#e91e63',
            'gris': '#6c757d',
            'gray': '#6c757d',
            'grey': '#6c757d',
            'marrón': '#8b4513',
            'brown': '#8b4513',
            'café': '#8b4513',
            'violeta': '#6f42c1',
            'purple': '#6f42c1',
            'morado': '#6f42c1',
            'naranja': '#fd7e14',
            'orange': '#fd7e14',
            'azul marino': '#0f3460',
            'navy': '#0f3460',
            'beige': '#f5f5dc',
            'dorado': '#ffd700',
            'gold': '#ffd700',
            'plateado': '#c0c0c0',
            'silver': '#c0c0c0'
        };
        
        // Si el color ya es un código hex, lo devolvemos
        if (colorName.startsWith('#')) {
            return colorName;
        }
        
        return colorMap[colorName.toLowerCase()] || '#6c757d';
    }

    getCategoryName(categoryId) {
        return this.categories[categoryId] || 'General';
    }

    setupFilters() {
        // Sistema de filtros principal
        window.filterMainProducts = (gender, category) => {
            // No interferir si hay un filtrado de categorías activo
            if (window.categoryFilterActive) {
                console.log('🚫 Filtrado de categorías activo, evitando interferencia');
                return;
            }
            
            console.log(`🎯 Filtrando: gender=${gender}, category=${category}`);
            
            const cards = document.querySelectorAll('.main-product-card');
            let visibleCount = 0;
            
            cards.forEach(card => {
                const cardGender = card.dataset.gender;
                const cardCategory = card.dataset.category;
                
                let show = true;
                
                if (gender !== 'all' && cardGender !== gender && cardGender !== 'unisex') {
                    show = false;
                }
                
                if (category !== 'all' && cardCategory !== category) {
                    show = false;
                }
                
                if (show) {
                    card.classList.remove('hidden');
                    card.style.cssText = `
                        display: block !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    `;
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none !important';
                }
            });
            
            console.log(`✅ Filtrado: ${visibleCount} productos visibles`);
        };
        
        // Alias para compatibilidad
        window.filterCleanProducts = window.filterMainProducts;
        
        // NO ejecutar filtrado automático para evitar conflictos con categorías
        // window.filterMainProducts('all', 'all');
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('📄 DOM cargado, iniciando MainProductsLoader');
    
    setTimeout(() => {
        const loader = new MainProductsLoader();
        window.mainProductsLoader = loader; // Exponer globalmente
        loader.init();
    }, 1000);
});

// FUNCIÓN GLOBAL PARA SINCRONIZACIÓN DE IMÁGENES ENTRE ADMIN E INDEX
window.getUnifiedProductImage = function(product) {
    console.log('🔍 getUnifiedProductImage called with:', product.name);
    console.log('  - main_image:', product.main_image);
    
    // Función helper para normalizar rutas
    function normalizeImagePath(path) {
        if (!path || path === 'NULL') return null;
        
        // Si ya es una URL completa, devolverla
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        
        // Si ya tiene uploads/ al inicio, verificar que no esté duplicada
        if (path.startsWith('uploads/uploads/')) {
            return path.replace('uploads/uploads/', 'uploads/');
        }
        
        // Si no tiene uploads/ pero parece ser un archivo de producto, agregarlo
        if (path.match(/^product_[a-f0-9]+_\d+\.(png|jpg|jpeg|gif|mp4|webm)$/i)) {
            return `uploads/${path}`;
        }
        
        return path;
    }
    
    // PRIORIDAD ABSOLUTA: Campo main_image de la API (lo que usa el admin)
    if (product.main_image && product.main_image !== 'NULL' && product.main_image !== '') {
        const normalizedPath = normalizeImagePath(product.main_image);
        console.log('✅ Usando main_image normalizada:', normalizedPath);
        return normalizedPath;
    }
    
    console.log('⚠️ main_image no disponible, usando fallbacks');
    
    // Si hay una imagen cacheada que funcionó, usarla
    if (window.productImageCache) {
        const cachedImage = window.productImageCache[product.name] || window.productImageCache[product.id];
        if (cachedImage) {
            console.log('✅ Usando imagen cacheada:', cachedImage);
            return cachedImage;
        }
    }
    
    // SISTEMA DE PRIORIDADES PARA ENCONTRAR LA IMAGEN CORRECTA
    const candidates = [
        // 1. Campo images del array (primer elemento)
        product.images && product.images.length > 0 ? normalizeImagePath(product.images[0].image_url || product.images[0]) : null,
        
        // 2. Campo image del producto
        normalizeImagePath(product.image),
        
        // 3. Campo image_url del producto
        normalizeImagePath(product.image_url),
        
        // 4. Buscar por nombre del producto en carpetas de images/
        product.name ? `images/${product.name}/imagen1.jpg` : null,
        product.name ? `images/${product.name}/imagen1.png` : null,
        
        // 5. Fallback por categoría con imágenes específicas existentes
        product.category_id === 'chaquetas' ? 'images/Chaqueta Beige Botones Dorados/chaqueta_b1.jpeg' : null,
        product.category_id === 'camisas' ? 'images/Camisa Blanco Purista/camisa_blanco_purista.jpeg' : null,
        product.category_id === 'pantalones' ? 'images/Pantalon Tela Galleta/pantalon1.jpg' : null,
        product.category_id === 'blazers' ? 'images/BLAZER.png' : null,
        
        // 6. Imagen genérica de ropa
        'images/clothes-1839935_1920.jpg',
        
        // 7. Placeholder final
        'images/placeholder.svg'
    ].filter(img => img && img !== 'NULL' && img !== '');
    
    const selectedImage = candidates[0] || 'images/placeholder.svg';
    console.log('📸 Imagen seleccionada:', selectedImage);
    return selectedImage;
};
