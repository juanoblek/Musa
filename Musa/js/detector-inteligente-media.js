// detector-inteligente-media.js - Sistema avanzado de detección de medios
class MediaDetectorInteligente {
    constructor() {
        this.videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv', 'flv', 'm4v', '3gp'];
        this.imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'tif', 'ico'];
        this.audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'flac', 'wma'];
        this.cache = new Map();
        this.loadingPromises = new Map();
    }

    /**
     * Detecta inteligentemente el tipo de media
     * @param {string} src - URL o ruta del archivo
     * @param {Object} options - Opciones de configuración
     * @returns {Promise<Object>} - Información del tipo de media
     */
    async detectarTipoMedia(src, options = {}) {
        if (!src) {
            return { tipo: 'ninguno', esValido: false, razon: 'URL vacía' };
        }

        // Verificar cache primero
        if (this.cache.has(src)) {
            return this.cache.get(src);
        }

        // Si ya hay una promesa en curso para esta URL, esperarla
        if (this.loadingPromises.has(src)) {
            return await this.loadingPromises.get(src);
        }

        // Crear nueva promesa de detección
        const promiseDeteccion = this._realizarDeteccion(src, options);
        this.loadingPromises.set(src, promiseDeteccion);

        try {
            const resultado = await promiseDeteccion;
            this.cache.set(src, resultado);
            return resultado;
        } finally {
            this.loadingPromises.delete(src);
        }
    }

    async _realizarDeteccion(src, options = {}) {
        const resultado = {
            src,
            tipo: 'desconocido',
            esValido: false,
            razon: '',
            metadata: {},
            timestamp: new Date().getTime()
        };

        try {
            // Paso 1: Detección por extensión (rápida)
            const deteccionExtension = this._detectarPorExtension(src);
            resultado.tipo = deteccionExtension.tipo;
            resultado.metadata.extension = deteccionExtension.extension;

            // Paso 2: Verificación de existencia del archivo
            const existeArchivo = await this._verificarExistenciaArchivo(src);
            if (!existeArchivo.existe) {
                resultado.esValido = false;
                resultado.razon = `Archivo no encontrado: ${existeArchivo.error}`;
                return resultado;
            }

            resultado.metadata.tamaño = existeArchivo.tamaño;
            resultado.metadata.tipoMime = existeArchivo.tipoMime;

            // Paso 3: Verificación avanzada por tipo MIME
            const verificacionMime = this._verificarTipoMime(existeArchivo.tipoMime);
            if (verificacionMime.tipo !== resultado.tipo && verificacionMime.confianza > 0.8) {
                resultado.tipo = verificacionMime.tipo;
                resultado.metadata.tipoCorregidoPorMime = true;
                resultado.razon = `Tipo corregido por MIME: ${existeArchivo.tipoMime}`;
            }

            // Paso 4: Validación específica por tipo
            if (resultado.tipo === 'video') {
                const validacionVideo = await this._validarVideo(src);
                resultado.esValido = validacionVideo.esValido;
                resultado.razon = validacionVideo.razon;
                resultado.metadata.video = validacionVideo.metadata;
            } else if (resultado.tipo === 'imagen') {
                const validacionImagen = await this._validarImagen(src);
                resultado.esValido = validacionImagen.esValido;
                resultado.razon = validacionImagen.razon;
                resultado.metadata.imagen = validacionImagen.metadata;
            } else {
                resultado.esValido = false;
                resultado.razon = 'Tipo de archivo no soportado';
            }

            // Paso 5: Aplicar reglas de negocio
            this._aplicarReglasNegocio(resultado, options);

        } catch (error) {
            resultado.esValido = false;
            resultado.razon = `Error de detección: ${error.message}`;
            console.error('🔍 Error en detección inteligente:', error);
        }

        return resultado;
    }

    _detectarPorExtension(src) {
        const url = new URL(src, window.location.origin);
        const pathname = url.pathname.toLowerCase();
        const extension = pathname.split('.').pop();

        if (this.videoExtensions.includes(extension)) {
            return { tipo: 'video', extension, confianza: 0.8 };
        }
        
        if (this.imageExtensions.includes(extension)) {
            return { tipo: 'imagen', extension, confianza: 0.8 };
        }

        if (this.audioExtensions.includes(extension)) {
            return { tipo: 'audio', extension, confianza: 0.8 };
        }

        return { tipo: 'desconocido', extension, confianza: 0.1 };
    }

    async _verificarExistenciaArchivo(src) {
        try {
            const response = await fetch(src, { 
                method: 'HEAD',
                cache: 'no-cache'
            });

            return {
                existe: response.ok,
                tamaño: parseInt(response.headers.get('content-length')) || 0,
                tipoMime: response.headers.get('content-type') || '',
                error: response.ok ? null : `HTTP ${response.status}`
            };
        } catch (error) {
            return {
                existe: false,
                tamaño: 0,
                tipoMime: '',
                error: error.message
            };
        }
    }

    _verificarTipoMime(tipoMime) {
        if (!tipoMime) {
            return { tipo: 'desconocido', confianza: 0 };
        }

        const mime = tipoMime.toLowerCase();

        if (mime.startsWith('video/')) {
            return { tipo: 'video', confianza: 0.95 };
        }

        if (mime.startsWith('image/')) {
            return { tipo: 'imagen', confianza: 0.95 };
        }

        if (mime.startsWith('audio/')) {
            return { tipo: 'audio', confianza: 0.95 };
        }

        // Verificaciones específicas para casos especiales
        const mimeMapping = {
            'application/ogg': 'video',
            'application/x-msvideo': 'video',
            'video/x-msvideo': 'video',
            'image/svg+xml': 'imagen'
        };

        if (mimeMapping[mime]) {
            return { tipo: mimeMapping[mime], confianza: 0.9 };
        }

        return { tipo: 'desconocido', confianza: 0.3 };
    }

    async _validarVideo(src) {
        return new Promise((resolve) => {
            const video = document.createElement('video');
            const timeoutId = setTimeout(() => {
                resolve({
                    esValido: false,
                    razon: 'Timeout: Video no cargó en tiempo esperado',
                    metadata: {}
                });
            }, 5000);

            video.onloadedmetadata = () => {
                clearTimeout(timeoutId);
                resolve({
                    esValido: true,
                    razon: 'Video válido y cargado correctamente',
                    metadata: {
                        duracion: video.duration,
                        ancho: video.videoWidth,
                        alto: video.videoHeight,
                        tieneAudio: video.mozHasAudio !== false, // Firefox specific
                        soportaPlatform: true
                    }
                });
            };

            video.onerror = (error) => {
                clearTimeout(timeoutId);
                resolve({
                    esValido: false,
                    razon: `Error de video: ${error.message || 'Formato no soportado'}`,
                    metadata: { errorCode: video.error?.code }
                });
            };

            video.src = src;
            video.preload = 'metadata';
        });
    }

    async _validarImagen(src) {
        return new Promise((resolve) => {
            const img = new Image();
            const timeoutId = setTimeout(() => {
                resolve({
                    esValido: false,
                    razon: 'Timeout: Imagen no cargó en tiempo esperado',
                    metadata: {}
                });
            }, 5000);

            img.onload = () => {
                clearTimeout(timeoutId);
                resolve({
                    esValido: true,
                    razon: 'Imagen válida y cargada correctamente',
                    metadata: {
                        ancho: img.naturalWidth,
                        alto: img.naturalHeight,
                        relacion: img.naturalWidth / img.naturalHeight,
                        esCuadrada: Math.abs(img.naturalWidth - img.naturalHeight) < 10
                    }
                });
            };

            img.onerror = (error) => {
                clearTimeout(timeoutId);
                resolve({
                    esValido: false,
                    razon: `Error de imagen: Formato no soportado o corrupto`,
                    metadata: {}
                });
            };

            img.src = src;
        });
    }

    _aplicarReglasNegocio(resultado, options) {
        // Regla 1: Tamaño mínimo para productos
        if (options.esProdcuto && resultado.metadata.tamaño < 1024) {
            resultado.esValido = false;
            resultado.razon += ' (Archivo muy pequeño para producto)';
        }

        // Regla 2: Resolución mínima para imágenes de producto
        if (options.esProducto && resultado.tipo === 'imagen' && resultado.metadata.imagen) {
            const { ancho, alto } = resultado.metadata.imagen;
            if (ancho < 200 || alto < 200) {
                resultado.esValido = false;
                resultado.razon += ' (Resolución muy baja para producto)';
            }
        }

        // Regla 3: Duración razonable para videos de producto
        if (options.esProducto && resultado.tipo === 'video' && resultado.metadata.video) {
            const duracion = resultado.metadata.video.duracion;
            if (duracion > 300) { // Más de 5 minutos
                resultado.esValido = false;
                resultado.razon += ' (Video muy largo para producto)';
            }
        }

        // Regla 4: Formatos preferidos
        const formatosPreferidos = {
            video: ['mp4', 'webm'],
            imagen: ['jpg', 'jpeg', 'png', 'webp']
        };

        if (formatosPreferidos[resultado.tipo] && !formatosPreferidos[resultado.tipo].includes(resultado.metadata.extension)) {
            resultado.metadata.formatoSuboptimo = true;
            resultado.razon += ' (Formato subóptimo)';
        }
    }

    /**
     * Genera el HTML apropiado basado en la detección
     */
    generarHTML(deteccion, atributos = {}) {
        if (!deteccion.esValido) {
            return this._generarPlaceholder(deteccion, atributos);
        }

        const atributosComunes = {
            class: atributos.class || 'media-elemento',
            alt: atributos.alt || 'Media elemento',
            style: atributos.style || 'width: 100%; height: auto; object-fit: cover;',
            loading: atributos.loading || 'lazy',
            ...atributos.extra
        };

        switch (deteccion.tipo) {
            case 'video':
                return this._generarHTMLVideo(deteccion, atributosComunes);
            case 'imagen':
                return this._generarHTMLImagen(deteccion, atributosComunes);
            default:
                return this._generarPlaceholder(deteccion, atributos);
        }
    }

    _generarHTMLVideo(deteccion, atributos) {
        const videoAtributos = {
            ...atributos,
            muted: true,
            autoplay: true,
            loop: true,
            playsinline: true,
            controls: false,
            preload: 'metadata'
        };

        const atributosStr = Object.entries(videoAtributos)
            .map(([key, value]) => {
                if (typeof value === 'boolean') {
                    return value ? key : '';
                }
                return `${key}="${value}"`;
            })
            .filter(Boolean)
            .join(' ');

        return `<video src="${deteccion.src}" ${atributosStr}></video>`;
    }

    _generarHTMLImagen(deteccion, atributos) {
        const atributosStr = Object.entries(atributos)
            .map(([key, value]) => `${key}="${value}"`)
            .join(' ');

        return `<img src="${deteccion.src}" ${atributosStr}>`;
    }

    _generarPlaceholder(deteccion, atributos) {
        const placeholder = atributos.placeholder || 'images/placeholder.svg';
        const clases = atributos.class || 'media-elemento placeholder';
        
        return `
            <div class="${clases} media-placeholder" style="${atributos.style || ''}" 
                 title="Error: ${deteccion.razon}">
                <img src="${placeholder}" alt="Placeholder" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.5;">
                <div class="error-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #666;">
                    <i class="fas fa-exclamation-triangle"></i><br>
                    <small>Media no disponible</small>
                </div>
            </div>
        `;
    }

    /**
     * Limpia el cache de detecciones
     */
    limpiarCache() {
        this.cache.clear();
        this.loadingPromises.clear();
    }

    /**
     * Obtiene estadísticas del detector
     */
    obtenerEstadisticas() {
        const cachePorTipo = {};
        for (const [url, deteccion] of this.cache.entries()) {
            cachePorTipo[deteccion.tipo] = (cachePorTipo[deteccion.tipo] || 0) + 1;
        }

        return {
            itemsEnCache: this.cache.size,
            deteccionesEnCurso: this.loadingPromises.size,
            cachePorTipo,
            formatosSoportados: {
                video: this.videoExtensions,
                imagen: this.imageExtensions,
                audio: this.audioExtensions
            }
        };
    }
}

// Crear instancia global
window.MediaDetector = new MediaDetectorInteligente();

// Función para normalizar rutas de imágenes
window.normalizarRutaImagen = function(src) {
    if (!src) return null;
    
    // Si ya es una URL completa, devolverla tal como está
    if (src.startsWith('http://') || src.startsWith('https://') || src.startsWith('//')) {
        return src;
    }
    
    // Si es una ruta de placeholder, devolverla
    if (src.includes('placeholder') || src.startsWith('data:') || src.startsWith('blob:')) {
        return src;
    }
    
    // Si la ruta ya tiene uploads/, verificar que no esté duplicada
    if (src.startsWith('uploads/')) {
        return src;
    }
    
    // Si es solo el nombre del archivo y está en uploads (producto_xxx.ext), agregar uploads/
    if (src.match(/^product_[a-f0-9]+_\d+\.(png|jpg|jpeg|gif|mp4|webm)$/i)) {
        return `uploads/${src}`;
    }
    
    // Si empieza con images/, mantenerla así
    if (src.startsWith('images/')) {
        return src;
    }
    
    // Para cualquier otra ruta relativa, intentar encontrarla en uploads/ primero
    return src;
};

// Función helper para generar HTML de media de forma síncrona (para templates)
window.generarMediaHTMLSincrono = function(src, atributos = {}) {
    if (!src) {
        return window.generarPlaceholderHTML(atributos);
    }
    
    // Normalizar la ruta de la imagen
    src = window.normalizarRutaImagen(src);

    const extension = src.split('.').pop().toLowerCase();
    const videoExtensions = ['mp4', 'webm', 'ogg', 'mov', 'avi', 'wmv', 'flv', 'm4v', '3gp'];
    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'tiff', 'tif', 'ico'];
    
    const clases = atributos.class || 'media-elemento';
    const alt = atributos.alt || 'Media elemento';
    const estilo = atributos.style || 'width: 100%; height: auto; object-fit: cover;';
    const placeholder = atributos.placeholder || 'images/placeholder.svg';

    if (videoExtensions.includes(extension)) {
        return `<video src="${src}" 
                      class="${clases}" 
                      alt="${alt}" 
                      style="${estilo}"
                      muted autoplay loop playsinline 
                      onloadstart="this.nextElementSibling.style.display='none';"
                      onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                </video>
                <img src="${placeholder}" 
                     class="${clases} fallback-image" 
                     alt="Error cargando video"
                     style="display: none !important; visibility: hidden !important; opacity: 0 !important; ${estilo}">`;
    } else if (imageExtensions.includes(extension)) {
        return `<img src="${src}" 
                     class="${clases}" 
                     alt="${alt}" 
                     style="${estilo}"
                     onerror="this.src='${placeholder}'">`;
    } else {
        // Tipo desconocido - intentar como imagen con fallback rápido
        return `<img src="${src}" 
                     class="${clases}" 
                     alt="${alt}" 
                     style="${estilo}"
                     onerror="this.src='${placeholder}'">`;
    }
};

window.generarPlaceholderHTML = function(atributos = {}) {
    const clases = atributos.class || 'media-elemento placeholder';
    const estilo = atributos.style || 'width: 100%; height: auto; object-fit: cover;';
    const alt = atributos.alt || 'Imagen del producto';
    
    // Mejorar el placeholder con múltiples fallbacks
    const placeholders = [
        'images/placeholder.svg',
        'images/placeholder.jpg', 
        'images/clothes-1839935_1920.jpg',
        'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik0xNTAgNzVMMjI1IDE1MEwxNTAgMjI1TDc1IDE1MEwxNTAgNzVaIiBmaWxsPSIjREREREREIi8+Cjx0ZXh0IHg9IjE1MCIgeT0iMjcwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5OTk5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiPkltYWdlbiBubyBkaXNwb25pYmxlPC90ZXh0Pgo8L3N2Zz4K'
    ];
    
    return `<img src="${placeholders[0]}" 
                 class="${clases}" 
                 alt="${alt}" 
                 style="${estilo}"
                 onerror="
                    if (this.src !== '${placeholders[1]}') this.src = '${placeholders[1]}';
                    else if (this.src !== '${placeholders[2]}') this.src = '${placeholders[2]}';
                    else if (this.src !== '${placeholders[3]}') { 
                        this.src = '${placeholders[3]}';
                        this.style.background = '#f8f9fa';
                        this.style.border = '2px dashed #dee2e6';
                    }
                 ">`;
};

// Función helper para usar en templates
window.generarMediaHTML = async function(src, atributos = {}) {
    const deteccion = await window.MediaDetector.detectarTipoMedia(src, { esProducto: true });
    return window.MediaDetector.generarHTML(deteccion, atributos);
};

// Función helper para detección rápida (solo extensión)
window.esVideo = function(src) {
    return /\.(mp4|webm|ogg|mov|avi|wmv|flv|m4v|3gp)$/i.test(src);
};

window.esImagen = function(src) {
    return /\.(jpg|jpeg|png|gif|webp|bmp|svg|tiff|tif|ico)$/i.test(src);
};

console.log('🔍 Detector Inteligente de Media cargado');
console.log('📊 Formatos soportados:', window.MediaDetector.obtenerEstadisticas().formatosSoportados);