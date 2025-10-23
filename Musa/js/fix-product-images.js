/**
 * Fix para imágenes de productos - Corrige rutas y carga correcta
 * Este script soluciona el problema de "Sin Imagen" en las tarjetas de producto
 */

console.log('🔧 [FIX-IMAGES] Iniciando corrección de imágenes...');

// Función para obtener la URL correcta de la imagen del producto
function getCorrectImageUrl(imagePath) {
    if (!imagePath) return null;
    
    // Si ya es una URL completa
    if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
        return imagePath;
    }
    
    // Si es data URL o blob
    if (imagePath.startsWith('data:') || imagePath.startsWith('blob:')) {
        return imagePath;
    }
    
    // Si ya tiene uploads/, mantenerla
    if (imagePath.startsWith('uploads/')) {
        return imagePath;
    }
    
    // Si es un archivo de producto típico, agregar uploads/
    if (imagePath.match(/^product_[a-f0-9]+_\d+\.(png|jpg|jpeg|gif|mp4|webm)$/i)) {
        return `uploads/${imagePath}`;
    }
    
    // Si está en images/, mantenerla
    if (imagePath.startsWith('images/')) {
        return imagePath;
    }
    
    // Por defecto, intentar en uploads/
    return `uploads/${imagePath}`;
}

// Función para crear un fallback de imagen mejorado
function createImageFallback(src, alt, className) {
    const img = document.createElement('img');
    img.className = className;
    img.alt = alt;
    img.style.cssText = `
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: #f8f9fa;
    `;
    
    const fallbacks = [
        src,
        `images/placeholder.svg`,
        `images/placeholder.jpg`,
        `images/clothes-1839935_1920.jpg`
    ];
    
    let currentFallback = 0;
    
    function tryNextFallback() {
        if (currentFallback < fallbacks.length) {
            img.src = fallbacks[currentFallback];
            currentFallback++;
        } else {
            // Último recurso: generar placeholder SVG
            img.src = "data:image/svg+xml;base64," + btoa(`
                <svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
                    <rect width="300" height="300" fill="#f8f9fa"/>
                    <rect x="100" y="100" width="100" height="100" fill="#dee2e6"/>
                    <text x="150" y="250" text-anchor="middle" font-family="Arial" font-size="14" fill="#6c757d">
                        ${alt}
                    </text>
                </svg>
            `);
        }
    }
    
    img.onerror = tryNextFallback;
    
    // Intentar cargar la primera imagen
    tryNextFallback();
    
    return img;
}

// Función principal para corregir imágenes de productos
function fixProductImages() {
    console.log('🔍 [FIX-IMAGES] Buscando imágenes problemáticas...');
    
    // Buscar todas las imágenes de productos
    const productImages = document.querySelectorAll('.product-image, .card-img-top, img[alt*="producto"], img[src*="product_"]');
    let fixedCount = 0;
    
    productImages.forEach((img, index) => {
        if (!img.src || img.src.includes('data:') || img.alt.toLowerCase().includes('imagen no disponible')) {
            console.log(`🚨 [FIX-IMAGES] Imagen problemática encontrada:`, img.src);
            
            // Intentar obtener la ruta desde atributos del contenedor
            const card = img.closest('.product-card, .card, .product-item');
            let newSrc = null;
            
            if (card) {
                // Buscar en data attributes
                newSrc = card.dataset.image || card.dataset.mainImage || card.dataset.src;
                
                // Buscar en texto del producto para extraer ID
                if (!newSrc) {
                    const titleElement = card.querySelector('.card-title, .product-title, h5, h3');
                    if (titleElement) {
                        const productName = titleElement.textContent.trim();
                        console.log(`📝 [FIX-IMAGES] Producto: ${productName}`);
                        
                        // Intentar construir URL basada en nombre
                        const slug = productName.toLowerCase()
                            .replace(/[^a-z0-9\s-]/g, '')
                            .replace(/\s+/g, '-')
                            .trim();
                        
                        // Buscar en el directorio de imágenes por carpetas con nombre similar
                        const possiblePaths = [
                            `images/${productName}/`,
                            `images/${slug}/`,
                            `uploads/product_${slug}.jpg`,
                            `uploads/product_${slug}.png`
                        ];
                        
                        newSrc = possiblePaths[0]; // Temporal, mejorar después
                    }
                }
            }
            
            if (newSrc) {
                const correctedSrc = getCorrectImageUrl(newSrc);
                console.log(`✅ [FIX-IMAGES] Corrigiendo: ${img.src} → ${correctedSrc}`);
                
                // Crear nueva imagen con fallbacks
                const newImg = createImageFallback(correctedSrc, img.alt, img.className);
                newImg.style.cssText = img.style.cssText;
                
                // Reemplazar la imagen
                img.parentNode.replaceChild(newImg, img);
                fixedCount++;
            }
        }
    });
    
    console.log(`✅ [FIX-IMAGES] Corregidas ${fixedCount} imágenes problemáticas`);
}

// Función para mejorar imágenes existentes que funcionan mal
function enhanceExistingImages() {
    console.log('🔧 [FIX-IMAGES] Mejorando imágenes existentes...');
    
    const allImages = document.querySelectorAll('.product-image, .card-img-top');
    let enhancedCount = 0;
    
    allImages.forEach(img => {
        if (img.src && !img.dataset.enhanced) {
            // Marcar como procesada
            img.dataset.enhanced = 'true';
            
            // Mejorar el onerror
            img.addEventListener('error', function() {
                console.log(`🚨 [FIX-IMAGES] Error cargando: ${this.src}`);
                
                // Intentar rutas alternativas
                if (this.src.includes('images/') && !this.dataset.triedUploads) {
                    const filename = this.src.split('/').pop();
                    this.src = `uploads/${filename}`;
                    this.dataset.triedUploads = 'true';
                } else if (this.src.includes('uploads/') && !this.dataset.triedImages) {
                    const filename = this.src.split('/').pop();
                    this.src = `images/${filename}`;
                    this.dataset.triedImages = 'true';
                } else if (!this.dataset.triedPlaceholder) {
                    this.src = 'images/placeholder.svg';
                    this.dataset.triedPlaceholder = 'true';
                }
            });
            
            enhancedCount++;
        }
    });
    
    console.log(`✅ [FIX-IMAGES] Mejoradas ${enhancedCount} imágenes existentes`);
}

// Función para corregir específicamente las tarjetas que muestran "Sin Imagen"
function fixSinImagenCards() {
    console.log('🎯 [FIX-IMAGES] Buscando tarjetas con "Sin Imagen"...');
    
    // Buscar elementos que contengan "Sin Imagen" o similar
    const badCards = Array.from(document.querySelectorAll('.card, .product-card')).filter(card => {
        const text = card.textContent.toLowerCase();
        return text.includes('sin imagen') || text.includes('imagen no disponible') || text.includes('imagen no');
    });
    
    badCards.forEach(card => {
        console.log('🚨 [FIX-IMAGES] Tarjeta problemática encontrada:', card);
        
        // Buscar la imagen dentro de la tarjeta
        const img = card.querySelector('img');
        const title = card.querySelector('.card-title, h5, h3')?.textContent?.trim();
        
        if (img && title) {
            console.log(`🔧 [FIX-IMAGES] Arreglando producto: ${title}`);
            
            // Intentar diferentes rutas basadas en el título
            const productSlug = title.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '-');
            
            const possibleImages = [
                `images/${title}/imagen1.jpg`,
                `images/${title}/imagen1.png`,
                `images/${productSlug}.jpg`,
                `images/${productSlug}.png`,
                `uploads/${productSlug}.jpg`,
                `uploads/${productSlug}.png`,
                'images/clothes-1839935_1920.jpg'
            ];
            
            // Crear nueva imagen con múltiples fallbacks
            const newImg = createImageFallback(possibleImages[0], title, img.className);
            newImg.style.cssText = img.style.cssText;
            
            // Configurar fallbacks adicionales
            let fallbackIndex = 1;
            newImg.addEventListener('error', function() {
                if (fallbackIndex < possibleImages.length) {
                    this.src = possibleImages[fallbackIndex];
                    fallbackIndex++;
                }
            });
            
            img.parentNode.replaceChild(newImg, img);
        }
    });
    
    console.log(`✅ [FIX-IMAGES] Procesadas ${badCards.length} tarjetas problemáticas`);
}

// Ejecutar las correcciones
function executeImageFixes() {
    try {
        fixProductImages();
        enhanceExistingImages(); 
        fixSinImagenCards();
        
        // Observer para detectar nuevos productos cargados
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(mutation => {
                if (mutation.addedNodes) {
                    mutation.addedNodes.forEach(node => {
                        if (node.nodeType === 1 && (
                            node.classList.contains('product-card') ||
                            node.classList.contains('card') ||
                            node.querySelector('.product-image, .card-img-top')
                        )) {
                            console.log('🔄 [FIX-IMAGES] Nuevo producto detectado, aplicando correcciones...');
                            setTimeout(() => {
                                fixProductImages();
                                enhanceExistingImages();
                                fixSinImagenCards();
                            }, 100);
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('👀 [FIX-IMAGES] Observer activado para nuevos productos');
        
    } catch (error) {
        console.error('❌ [FIX-IMAGES] Error ejecutando correcciones:', error);
    }
}

// Ejecutar cuando el DOM esté listo
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', executeImageFixes);
} else {
    executeImageFixes();
}

// También ejecutar después de que otros scripts hayan cargado
setTimeout(executeImageFixes, 2000);
setTimeout(executeImageFixes, 5000);

console.log('✅ [FIX-IMAGES] Script de corrección de imágenes cargado');