/**
 * Script para limpiar los logs acumulados de la consola
 */

// Función para limpiar la consola
function clearConsoleSpam() {
    // Limpiar consola
    if (typeof console.clear === 'function') {
        console.clear();
    }
    
    // Sobrescribir console.log temporalmente para filtrar spam
    const originalLog = console.log;
    const originalError = console.error;
    
    console.log = function(...args) {
        const message = args.join(' ');
        // Filtrar mensajes de spam conocidos
        if (message.includes('🔄 Nuevas imágenes de producto detectadas') ||
            message.includes('🔄 Nuevas imágenes detectadas:') ||
            message.includes('⚠️ Máximo de intentos alcanzado') ||
            message.includes('🔄 Intento') && message.includes('de configuración')) {
            return; // No mostrar estos mensajes
        }
        originalLog.apply(console, args);
    };
    
    console.error = function(...args) {
        const message = args.join(' ');
        // Filtrar errores de imágenes faltantes si no son críticos
        if (message.includes('Failed to load resource') && message.includes('404')) {
            return; // No mostrar estos errores 404 de imágenes
        }
        originalError.apply(console, args);
    };
    
    console.log('🧹 Consola limpiada y filtros anti-spam activados');
}

// Ejecutar limpieza cuando se carga el script
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', clearConsoleSpam);
} else {
    clearConsoleSpam();
}

// Función global para limpiar manualmente
window.clearSpam = clearConsoleSpam;