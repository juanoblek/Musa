// Funciones para mejorar el formulario de pago
document.addEventListener('DOMContentLoaded', function() {
    
    // Formatear número de tarjeta automáticamente
    function formatCardNumber(input) {
        let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formattedValue = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += ' ';
            }
            formattedValue += value[i];
        }
        
        input.value = formattedValue;
        
        // Detectar tipo de tarjeta
        detectCardType(value);
    }
    
    // Formatear fecha de vencimiento
    function formatExpiryDate(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        input.value = value;
    }
    
    // Detectar tipo de tarjeta
    function detectCardType(number) {
        const cardTypeIcon = document.querySelector('.card-brand-icon');
        if (!cardTypeIcon) return;
        
        const patterns = {
            visa: /^4/,
            mastercard: /^5[1-5]|^2[2-7]/,
            amex: /^3[47]/,
            discover: /^6(?:011|5)/
        };
        
        let detectedType = '';
        let icon = '';
        
        for (const [type, pattern] of Object.entries(patterns)) {
            if (pattern.test(number)) {
                detectedType = type;
                break;
            }
        }
        
        switch (detectedType) {
            case 'visa':
                icon = '💳';
                break;
            case 'mastercard':
                icon = '💳';
                break;
            case 'amex':
                icon = '💳';
                break;
            default:
                icon = '💳';
        }
        
        cardTypeIcon.textContent = icon;
    }
    
    // Agregar event listeners cuando el contenedor de pago se actualice
    function attachFormListeners() {
        const container = document.getElementById('mercadopago-payment-container');
        if (!container) return;
        
        // Observer para detectar cambios en el contenedor
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    setupFormInputs();
                }
            });
        });
        
        observer.observe(container, { childList: true, subtree: true });
    }
    
    function setupFormInputs() {
        // Número de tarjeta
        const cardNumberInput = document.querySelector('input[placeholder*="1234"]');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', function() {
                formatCardNumber(this);
            });
            
            // Agregar icono de tipo de tarjeta
            if (!cardNumberInput.parentElement.querySelector('.card-brand-icon')) {
                const icon = document.createElement('span');
                icon.className = 'card-brand-icon';
                icon.textContent = '💳';
                cardNumberInput.parentElement.style.position = 'relative';
                cardNumberInput.parentElement.appendChild(icon);
            }
        }
        
        // Fecha de vencimiento
        const expiryInput = document.querySelector('input[placeholder*="MM/AA"]');
        if (expiryInput) {
            expiryInput.addEventListener('input', function() {
                formatExpiryDate(this);
            });
        }
        
        // CVV
        const cvvInput = document.querySelector('input[placeholder*="123"]');
        if (cvvInput) {
            cvvInput.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '').substring(0, 4);
            });
        }
        
        // Validación en tiempo real
        addRealTimeValidation();
    }
    
    function addRealTimeValidation() {
        const inputs = document.querySelectorAll('#mercadopago-payment-container input');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                // Remover clases de error al escribir
                this.classList.remove('is-invalid');
                const feedback = this.parentElement.querySelector('.invalid-feedback');
                if (feedback) feedback.remove();
            });
        });
    }
    
    function validateField(input) {
        let isValid = true;
        let message = '';
        
        if (input.placeholder.includes('1234')) {
            // Validar número de tarjeta
            const value = input.value.replace(/\s/g, '');
            if (value.length < 13 || value.length > 19) {
                isValid = false;
                message = 'Número de tarjeta inválido';
            }
        } else if (input.placeholder.includes('MM/AA')) {
            // Validar fecha
            const value = input.value;
            if (!/^\d{2}\/\d{2}$/.test(value)) {
                isValid = false;
                message = 'Formato de fecha inválido';
            }
        } else if (input.placeholder.includes('123')) {
            // Validar CVV
            const value = input.value;
            if (value.length < 3 || value.length > 4) {
                isValid = false;
                message = 'CVV inválido';
            }
        } else if (input.type === 'email') {
            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                isValid = false;
                message = 'Email inválido';
            }
        }
        
        // Aplicar estilos de validación
        if (!isValid && input.value.trim() !== '') {
            input.classList.add('is-invalid');
            
            let feedback = input.parentElement.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                input.parentElement.appendChild(feedback);
            }
            feedback.textContent = message;
        } else {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            const feedback = input.parentElement.querySelector('.invalid-feedback');
            if (feedback) feedback.remove();
        }
    }
    
    // Inicializar
    attachFormListeners();
    
    // También intentar configurar inmediatamente por si ya existe el formulario
    setTimeout(setupFormInputs, 1000);
});

// Función global para mejorar el botón de pago
window.enhancePayButton = function() {
    const payButton = document.querySelector('button[onclick*="processPayment"]');
    if (payButton) {
        payButton.classList.add('btn-pay-highlight');
        
        // Agregar efecto de loading al hacer clic
        payButton.addEventListener('click', function() {
            const originalText = this.innerHTML;
            this.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Procesando...';
            this.disabled = true;
            
            // Restaurar después de 3 segundos (o cuando el pago se complete)
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 3000);
        });
    }
};

console.log('🎨 Mejoras del formulario de pago cargadas');
