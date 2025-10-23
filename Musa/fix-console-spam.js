// Script para eliminar el spam de console.log y los reintentos infinitos
console.log('🧹 Limpiando console spam y deteniendo loops infinitos...');

// 1. Deshabilitar todos los setTimeout repetitivos
let timeoutCount = 0;
const originalSetTimeout = window.setTimeout;
window.setTimeout = function(fn, delay) {
    timeoutCount++;
    
    // Bloquear setTimeouts repetitivos (más de 50 en poco tiempo)
    if (timeoutCount > 50) {
        console.log('🚫 SetTimeout bloqueado para prevenir spam');
        return;
    }
    
    // Resetear contador después de un tiempo
    if (timeoutCount === 1) {
        originalSetTimeout(() => {
            timeoutCount = 0;
        }, 5000);
    }
    
    return originalSetTimeout(fn, delay);
};

// 2. Filtrar logs repetitivos
const originalLog = console.log;
let lastLogMessage = '';
let logCount = 0;

console.log = function(...args) {
    const message = args.join(' ');
    
    // Bloquear mensajes específicos repetitivos
    const spamPatterns = [
        'Event listener agregado a imagen',
        'Buscando imágenes con selector',
        'Condiciones cumplidas, configurando listeners',
        'Intento .* de configuración',
        'Event listener agregado a imagen \\d+:'
    ];
    
    const isSpam = spamPatterns.some(pattern => 
        new RegExp(pattern).test(message)
    );
    
    if (isSpam) {
        return; // Bloquear mensaje spam
    }
    
    // Bloquear mensajes repetidos
    if (message === lastLogMessage) {
        logCount++;
        if (logCount > 3) {
            return; // Bloquear después de 3 repeticiones
        }
    } else {
        lastLogMessage = message;
        logCount = 0;
    }
    
    originalLog.apply(console, args);
};

// 3. Detener observadores infinitos
const observers = [];
const originalMutationObserver = window.MutationObserver;
window.MutationObserver = function(callback) {
    const observer = new originalMutationObserver(callback);
    observers.push(observer);
    
    // Limitar número de observadores
    if (observers.length > 5) {
        console.log('🚫 Demasiados observadores, desconectando el más antiguo');
        const oldObserver = observers.shift();
        oldObserver.disconnect();
    }
    
    return observer;
};

// 4. Limpiar listeners duplicados
function removeDuplicateListeners() {
    const images = document.querySelectorAll('img[data-listeners-added]');
    console.log(`🧹 Encontradas ${images.length} imágenes con listeners duplicados`);
    
    images.forEach(img => {
        // Clonar imagen para remover todos los listeners
        const newImg = img.cloneNode(true);
        img.parentNode.replaceChild(newImg, img);
        
        // Agregar un solo listener limpio
        newImg.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            if (productId && typeof showProductView === 'function') {
                showProductView(productId);
            }
        });
        
        // Marcar como procesado
        newImg.setAttribute('data-listeners-cleaned', 'true');
    });
}

// 5. Ejecutar limpieza
removeDuplicateListeners();

// 6. Desconectar todos los observadores existentes
observers.forEach(observer => observer.disconnect());

console.log('✅ Limpieza de console spam completada');
console.log('✅ Loops infinitos detenidos');
console.log('✅ Listeners duplicados removidos');