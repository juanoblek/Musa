// SCRIPT PARA FORZAR EL SIMULADOR DE TESTING
// Copia y pega esto en la consola del navegador (F12 > Console)

console.log('🧪 Forzando simulador de testing...');

// Simular preferencia de prueba
const mockPreference = {
    id: 'TEST-PREF-' + Date.now(),
    init_point: 'https://sandbox.mercadopago.com.co/checkout/v1/redirect?pref_id=TEST-PREF-' + Date.now(),
    testing_mode: true
};

// Activar simulador
if (window.paymentSystem && window.paymentSystem.showTestingSimulation) {
    window.paymentSystem.showTestingSimulation(mockPreference);
    console.log('✅ Simulador activado manualmente');
} else {
    console.log('❌ Sistema de pago no encontrado');
}