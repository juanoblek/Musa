/**
 * PAYMENT RESULT MODALS - Modales estéticos para resultados de pago
 * Reemplaza los mensajes del navegador con modales elegantes
 */

console.log('💳 Cargando sistema de modales de resultado de pago...');

// Crear CSS para los modales de resultado
function createPaymentResultCSS() {
    const css = `
        /* MODAL DE RESULTADO DE PAGO */
        .payment-result-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .payment-result-modal.show {
            opacity: 1;
            visibility: visible;
        }

        .payment-result-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transform: scale(0.8) translateY(30px);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
        }

        .payment-result-modal.show .payment-result-content {
            transform: scale(1) translateY(0);
        }

        .payment-result-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            position: relative;
            animation: pulseIcon 2s infinite;
        }

        .payment-result-icon.success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            box-shadow: 0 0 30px rgba(76, 175, 80, 0.4);
        }

        .payment-result-icon.error {
            background: linear-gradient(135deg, #f44336, #d32f2f);
            box-shadow: 0 0 30px rgba(244, 67, 54, 0.4);
        }

        .payment-result-icon.pending {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            box-shadow: 0 0 30px rgba(255, 152, 0, 0.4);
        }

        @keyframes pulseIcon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .payment-result-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #333;
        }

        .payment-result-title.success {
            color: #4CAF50;
        }

        .payment-result-title.error {
            color: #f44336;
        }

        .payment-result-title.pending {
            color: #FF9800;
        }

        .payment-result-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .payment-result-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .payment-result-detail {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .payment-result-detail:last-child {
            margin-bottom: 0;
            font-weight: 600;
            font-size: 16px;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
            margin-top: 10px;
        }

        .payment-result-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .payment-result-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            min-width: 120px;
        }

        .payment-result-btn.primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }

        .payment-result-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
        }

        .payment-result-btn.secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #dee2e6;
        }

        .payment-result-btn.secondary:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }

        /* Efectos de confetti para pago exitoso */
        .confetti {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
        }

        .confetti-piece {
            position: absolute;
            width: 8px;
            height: 8px;
            background: #4CAF50;
            animation: confettiFall 3s linear infinite;
        }

        @keyframes confettiFall {
            0% {
                transform: translateY(-100vh) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .payment-result-content {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .payment-result-icon {
                width: 60px;
                height: 60px;
                font-size: 30px;
            }
            
            .payment-result-title {
                font-size: 24px;
            }
            
            .payment-result-actions {
                flex-direction: column;
            }
        }
    `;

    const style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);
}

// Crear modal de resultado
function createPaymentResultModal() {
    const modal = document.createElement('div');
    modal.id = 'paymentResultModal';
    modal.className = 'payment-result-modal';
    
    modal.innerHTML = `
        <div class="payment-result-content">
            <div class="confetti" id="confettiContainer"></div>
            <div class="payment-result-icon" id="resultIcon">
                <i class="fas fa-check" id="resultIconSymbol"></i>
            </div>
            <h2 class="payment-result-title" id="resultTitle">¡Pago Exitoso!</h2>
            <p class="payment-result-message" id="resultMessage">
                Tu pago ha sido procesado correctamente.
            </p>
            <div class="payment-result-details" id="resultDetails">
                <div class="payment-result-detail">
                    <span>Tarjeta:</span>
                    <span id="cardDetails">•••• •••• •••• 3704</span>
                </div>
                <div class="payment-result-detail">
                    <span>Fecha:</span>
                    <span id="paymentDate"></span>
                </div>
                <div class="payment-result-detail">
                    <span>Estado:</span>
                    <span id="paymentStatus">Aprobado</span>
                </div>
                <div class="payment-result-detail">
                    <span>Total:</span>
                    <span id="paymentTotal">$121.999</span>
                </div>
            </div>
            <div class="payment-result-actions">
                <button class="payment-result-btn secondary" onclick="closePaymentResult()">
                    Cerrar
                </button>
                <button class="payment-result-btn primary" onclick="goToHome()">
                    Continuar Comprando
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    return modal;
}

// Mostrar resultado de pago aprobado
function showPaymentSuccess(paymentData = {}) {
    console.log('✅ Mostrando modal de pago exitoso:', paymentData);
    
    const modal = document.getElementById('paymentResultModal') || createPaymentResultModal();
    
    // Configurar contenido para éxito
    const icon = modal.querySelector('#resultIcon');
    const iconSymbol = modal.querySelector('#resultIconSymbol');
    const title = modal.querySelector('#resultTitle');
    const message = modal.querySelector('#resultMessage');
    const cardDetails = modal.querySelector('#cardDetails');
    const paymentDate = modal.querySelector('#paymentDate');
    const paymentStatus = modal.querySelector('#paymentStatus');
    const paymentTotal = modal.querySelector('#paymentTotal');
    
    // Configurar íconos y clases
    icon.className = 'payment-result-icon success';
    iconSymbol.className = 'fas fa-check';
    title.className = 'payment-result-title success';
    title.textContent = '¡Pago Procesado Exitosamente!';
    
    message.textContent = 'Tu pedido ha sido confirmado y será procesado en breve. Recibirás un email con los detalles.';
    
    // Configurar detalles
    cardDetails.textContent = paymentData.cardNumber || '•••• •••• •••• 3704';
    paymentDate.textContent = new Date().toLocaleString('es-ES');
    paymentStatus.textContent = 'Aprobado ✅';
    paymentTotal.textContent = paymentData.total || '$121.999';
    
    // Mostrar confetti
    createConfetti();
    
    // Mostrar modal
    modal.classList.add('show');
    
    // Reproducir sonido de éxito si está disponible
    playSuccessSound();
}

// Mostrar resultado de pago rechazado
function showPaymentError(errorData = {}) {
    console.log('❌ Mostrando modal de pago rechazado:', errorData);
    
    const modal = document.getElementById('paymentResultModal') || createPaymentResultModal();
    
    // Configurar contenido para error
    const icon = modal.querySelector('#resultIcon');
    const iconSymbol = modal.querySelector('#resultIconSymbol');
    const title = modal.querySelector('#resultTitle');
    const message = modal.querySelector('#resultMessage');
    const paymentStatus = modal.querySelector('#paymentStatus');
    
    // Configurar íconos y clases
    icon.className = 'payment-result-icon error';
    iconSymbol.className = 'fas fa-times';
    title.className = 'payment-result-title error';
    title.textContent = 'Pago Rechazado';
    
    message.textContent = errorData.message || 'Tu pago no pudo ser procesado. Por favor, verifica los datos de tu tarjeta e intenta nuevamente.';
    paymentStatus.textContent = 'Rechazado ❌';
    
    // Ocultar confetti
    const confetti = modal.querySelector('#confettiContainer');
    confetti.innerHTML = '';
    
    // Mostrar modal
    modal.classList.add('show');
    
    // Reproducir sonido de error si está disponible
    playErrorSound();
}

// Mostrar resultado de pago pendiente
function showPaymentPending(pendingData = {}) {
    console.log('⏳ Mostrando modal de pago pendiente:', pendingData);
    
    const modal = document.getElementById('paymentResultModal') || createPaymentResultModal();
    
    // Configurar contenido para pendiente
    const icon = modal.querySelector('#resultIcon');
    const iconSymbol = modal.querySelector('#resultIconSymbol');
    const title = modal.querySelector('#resultTitle');
    const message = modal.querySelector('#resultMessage');
    const paymentStatus = modal.querySelector('#paymentStatus');
    
    // Configurar íconos y clases
    icon.className = 'payment-result-icon pending';
    iconSymbol.className = 'fas fa-clock';
    title.className = 'payment-result-title pending';
    title.textContent = 'Pago Pendiente';
    
    message.textContent = 'Tu pago está siendo procesado. Recibirás una notificación cuando se complete la transacción.';
    paymentStatus.textContent = 'Pendiente ⏳';
    
    // Mostrar modal
    modal.classList.add('show');
}

// Crear efecto de confetti
function createConfetti() {
    const container = document.querySelector('#confettiContainer');
    if (!container) return;
    
    container.innerHTML = '';
    
    const colors = ['#4CAF50', '#2196F3', '#FF9800', '#9C27B0', '#F44336'];
    
    for (let i = 0; i < 50; i++) {
        const confetti = document.createElement('div');
        confetti.className = 'confetti-piece';
        confetti.style.left = Math.random() * 100 + '%';
        confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
        confetti.style.animationDelay = Math.random() * 3 + 's';
        confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
        container.appendChild(confetti);
    }
}

// Cerrar modal de resultado
function closePaymentResult() {
    const modal = document.getElementById('paymentResultModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            const confetti = modal.querySelector('#confettiContainer');
            if (confetti) confetti.innerHTML = '';
        }, 300);
    }
}

// Ir a inicio
function goToHome() {
    closePaymentResult();
    // Cerrar todos los modales abiertos
    document.querySelectorAll('.modal.show').forEach(modal => {
        const bsModal = bootstrap.Modal.getInstance(modal);
        if (bsModal) bsModal.hide();
    });
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Reproducir sonidos
function playSuccessSound() {
    try {
        const audio = new Audio('sounds/success.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {});
    } catch (error) {}
}

function playErrorSound() {
    try {
        const audio = new Audio('sounds/error.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {});
    } catch (error) {}
}

// Funciones globales
window.showPaymentSuccess = showPaymentSuccess;
window.showPaymentError = showPaymentError;
window.showPaymentPending = showPaymentPending;
window.closePaymentResult = closePaymentResult;
window.goToHome = goToHome;

// Inicializar CSS y modal
createPaymentResultCSS();

console.log('✅ Sistema de modales de resultado de pago cargado');
console.log('🎯 Funciones disponibles:');
console.log('   - window.showPaymentSuccess(data)');
console.log('   - window.showPaymentError(data)');
console.log('   - window.showPaymentPending(data)');