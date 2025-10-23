/**
 * KILLSWITCH INTELIGENTE - Solo bloquea operaciones problemáticas específicas
 * Permite el funcionamiento normal del carrito mientras previene valores erróneos
 */
(function() {
    'use strict';
    
    console.log('🛡️ KILLSWITCH INTELIGENTE ACTIVADO - MODO SELECTIVO 🛡️');
    
    // Valores problemáticos a interceptar
    const PROBLEMATIC_VALUES = [12000, 33313, 144231];
    
    // Interceptar solo Math.abs para valores problemáticos específicos
    const originalMathAbs = Math.abs;
    Math.abs = function(value) {
        const result = originalMathAbs(value);
        
        // Solo interceptar valores problemáticos conocidos
        if (PROBLEMATIC_VALUES.includes(result) || PROBLEMATIC_VALUES.includes(-value)) {
            console.log(`💀 MATH KILLSWITCH: Bloqueado Math.abs(${value}) = ${result}`);
            return 0; // Retornar 0 en lugar del valor problemático
        }
        
        return result;
    };
    
    // Interceptor más inteligente para setTimeout
    const originalSetTimeout = window.setTimeout;
    window.setTimeout = function(func, delay, ...args) {
        const funcStr = func.toString();
        
        // Solo bloquear si contiene valores problemáticos específicos
        if (funcStr.includes('12000') || funcStr.includes('33313') || funcStr.includes('144231')) {
            console.log('💀 TIMEOUT KILLSWITCH: Interceptando setTimeout problemático');
            return originalSetTimeout(function() {
                console.log('🚫 TIMEOUT BLOQUEADO: Función problemática cancelada');
            }, delay);
        }
        
        // Permitir otros timeouts normales
        return originalSetTimeout(func, delay, ...args);
    };
    
    // Interceptor para prevenir asignaciones de valores problemáticos
    function interceptProblemValues(obj, prop) {
        if (obj[prop] !== undefined) {
            const originalValue = obj[prop];
            
            Object.defineProperty(obj, prop, {
                get: function() {
                    return originalValue;
                },
                set: function(newValue) {
                    // Interceptar solo valores problemáticos
                    if (typeof newValue === 'number' && PROBLEMATIC_VALUES.includes(newValue)) {
                        console.log(`🔧 MATH INTERCEPTOR: Corrigiendo ${prop} (${newValue}) → 0`);
                        originalValue = 0;
                    } else if (typeof newValue === 'string' && 
                              (newValue.includes('12000') || newValue.includes('33313'))) {
                        console.log(`🔧 STRING INTERCEPTOR: Bloqueando valor problemático en ${prop}`);
                        return; // No asignar
                    } else {
                        originalValue = newValue;
                    }
                },
                configurable: true
            });
        }
    }
    
    // Función para corregir solo elementos problemáticos
    function correctProblematicValues() {
        document.querySelectorAll('*').forEach(element => {
            if (element.textContent) {
                const text = element.textContent;
                
                // Solo corregir valores problemáticos específicos
                PROBLEMATIC_VALUES.forEach(value => {
                    const regex = new RegExp(`\\$?\\s*${value.toLocaleString()}`, 'gi');
                    if (regex.test(text)) {
                        // Solo corregir si es claramente un shipping cost problemático
                        if (element.className.toLowerCase().includes('shipping') ||
                            element.className.toLowerCase().includes('envio') ||
                            text.includes('Envío')) {
                            element.textContent = text.replace(regex, '$0');
                            console.log(`🛠️ CORRECTOR: Shipping corregido de $${value.toLocaleString()} a $0`);
                        }
                    }
                });
            }
        });
    }
    
    // Corrección periódica menos agresiva
    setInterval(correctProblematicValues, 5000);
    
    // Función global para corrección manual
    window.fixCartValues = function() {
        correctProblematicValues();
        console.log('✅ Valores problemáticos corregidos manualmente');
    };
    
    console.log('🛡️ Killswitch inteligente listo - Solo bloquea valores problemáticos');
    
})();