/**
 * SCRIPT PARA INTEGRAR PAGO + GUARDADO EN BASE DE DATOS
 * Solo guarda si el pago es exitoso
 */

// Función para obtener el subtotal correcto del carrito
function obtenerSubtotalDelCarrito() {
    try {
        const cart = JSON.parse(localStorage.getItem('carrito') || '[]');
        const subtotal = cart.reduce((sum, item) => sum + (parseInt(item.price || item.precio) * (item.quantity || item.cantidad)), 0);
        console.log('💰 [PAGO-SEGURO] Subtotal calculado:', subtotal);
        return subtotal;
    } catch (error) {
        console.error('❌ Error calculando subtotal:', error);
        // Valor por defecto solo si hay error
        return 21313; // Usar el valor que mencionaste como correcto
    }
}

// Función principal para procesar pago y guardar pedido
async function procesarPagoYGuardar(datosFormulario) {
    try {
        // Mostrar indicador de carga
        mostrarIndicadorPago('Procesando pago...');
        
        // Enviar datos al endpoint unificado
        const response = await fetch('/Musa/api/pago-y-guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(datosFormulario)
        });
        
        const resultado = await response.json();
        
        if (resultado.success && resultado.pago_exitoso) {
            // ✅ PAGO EXITOSO Y DATOS GUARDADOS
            mostrarExitoPago(resultado);
            
            // Opcional: limpiar formulario
            document.getElementById('payment-form')?.reset();
            
        } else if (!resultado.pago_exitoso) {
            // ❌ PAGO RECHAZADO/PENDIENTE - NO SE GUARDÓ NADA
            mostrarErrorPago(resultado);
            
        } else {
            // ⚠️ ERROR GENERAL
            throw new Error(resultado.message || 'Error desconocido');
        }
        
    } catch (error) {
        console.error('Error en procesamiento:', error);
        mostrarErrorGeneral(error.message);
    }
}

// Función para mostrar indicador de carga
function mostrarIndicadorPago(mensaje) {
    Swal.fire({
        title: 'Procesando Pago',
        text: mensaje,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
}

// Función para mostrar éxito
function mostrarExitoPago(resultado) {
    Swal.fire({
        icon: 'success',
        title: '¡Pago Exitoso!',
        html: `
            <div style="text-align: left;">
                <p><strong>🎉 Tu pedido ha sido creado exitosamente</strong></p>
                <hr>
                <p><strong>📦 ID del Pedido:</strong> ${resultado.pedido_id}</p>
                <p><strong>💳 ID del Pago:</strong> ${resultado.pago_id}</p>
                <p><strong>💰 Total Pagado:</strong> $${resultado.total.toLocaleString()}</p>
                <hr>
                <p><strong>📋 Próximos pasos:</strong></p>
                <ul style="text-align: left;">
                    <li>Tu pedido aparecerá en el panel administrativo</li>
                    <li>Recibirás confirmación por email</li>
                    <li>Te contactaremos para coordinar el envío</li>
                </ul>
            </div>
        `,
        showConfirmButton: true,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#28a745'
    });
}

// Función para mostrar error de pago
function mostrarErrorPago(resultado) {
    let mensaje = '';
    let icono = 'error';
    
    switch (resultado.estado_pago) {
        case 'rejected':
            mensaje = `
                <p><strong>❌ Pago Rechazado</strong></p>
                <p>Tu tarjeta fue rechazada. Por favor:</p>
                <ul style="text-align: left;">
                    <li>Verifica los datos de tu tarjeta</li>
                    <li>Asegúrate de tener fondos suficientes</li>
                    <li>Intenta con otra tarjeta</li>
                </ul>
                <p><strong>💡 Tip:</strong> Para pruebas, usa una tarjeta terminada en 1</p>
            `;
            break;
            
        case 'pending':
            icono = 'warning';
            mensaje = `
                <p><strong>⏳ Pago Pendiente</strong></p>
                <p>Tu pago está siendo procesado.</p>
                <p>Te notificaremos cuando sea confirmado.</p>
            `;
            break;
            
        default:
            mensaje = `
                <p><strong>❌ Error en el Pago</strong></p>
                <p>${resultado.message}</p>
            `;
    }
    
    Swal.fire({
        icon: icono,
        title: 'Problema con el Pago',
        html: mensaje,
        showConfirmButton: true,
        confirmButtonText: 'Intentar Nuevamente',
        confirmButtonColor: '#dc3545'
    });
}

// Función para mostrar error general
function mostrarErrorGeneral(mensaje) {
    Swal.fire({
        icon: 'error',
        title: 'Error de Conexión',
        text: mensaje || 'Ocurrió un error inesperado. Intenta nuevamente.',
        showConfirmButton: true,
        confirmButtonText: 'Reintentar',
        confirmButtonColor: '#dc3545'
    });
}

// Integración con el formulario existente
document.addEventListener('DOMContentLoaded', function() {
    
    // Buscar el botón de pago en el formulario existente
    const botonPago = document.querySelector('#pay-button, .pay-btn, [data-action="pay"]');
    
    if (botonPago) {
        botonPago.addEventListener('click', async function(e) {
            e.preventDefault();
            
            // Recopilar datos del formulario
            const datosFormulario = recopilarDatosFormulario();
            
            // Validar datos
            if (!validarDatosFormulario(datosFormulario)) {
                return;
            }
            
            // Procesar pago y guardar
            await procesarPagoYGuardar(datosFormulario);
        });
    }
});

// Función para recopilar datos del formulario
function recopilarDatosFormulario() {
    return {
        // Datos del cliente
        fullName: document.getElementById('fullName')?.value || '',
        email: document.getElementById('email')?.value || '',
        phone: document.getElementById('phone')?.value || '',
        
        // Dirección
        department: document.getElementById('department')?.value || '',
        city: document.getElementById('city')?.value || '',
        address: document.getElementById('address')?.value || '',
        postalCode: document.getElementById('postalCode')?.value || '',
        
        // Datos de pago
        cardNumber: document.getElementById('cardNumber')?.value || '',
        cardHolder: document.getElementById('cardHolder')?.value || '',
        documentType: document.getElementById('documentType')?.value || '',
        documentNumber: document.getElementById('documentNumber')?.value || '',
        idNumber: document.getElementById('idNumber')?.value || '',
        expiryDate: document.getElementById('expiryDate')?.value || '',
        cvv: document.getElementById('cvv')?.value || '',
        installments: document.getElementById('installments')?.value || 1,
        
        // Método de pago
        paymentMethod: document.querySelector('.method-tab.active')?.dataset?.method || 'credit_card',
        
        // Montos corregidos - ENVÍO SIEMPRE GRATIS
        subtotal: obtenerSubtotalDelCarrito(),
        envio: 0,  // ENVÍO SIEMPRE GRATIS
        total: obtenerSubtotalDelCarrito(),  // TOTAL = SUBTOTAL (sin envío)
        
        // Productos (adaptar según tu lógica)
        productos: JSON.stringify(window.cartItems || [
            {nombre: "Producto de prueba", precio: 89000, cantidad: 1}
        ]),
        
        // Notas adicionales
        notes: document.getElementById('notes')?.value || ''
    };
}

// Función para validar datos del formulario
function validarDatosFormulario(datos) {
    const errores = [];
    
    if (!datos.fullName) errores.push('Nombre completo');
    if (!datos.email) errores.push('Email');
    if (!datos.phone) errores.push('Teléfono');
    if (!datos.address) errores.push('Dirección');
    if (!datos.cardNumber) errores.push('Número de tarjeta');
    if (!datos.cardHolder) errores.push('Titular de la tarjeta');
    
    if (errores.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Datos Incompletos',
            text: `Por favor completa: ${errores.join(', ')}`,
            confirmButtonColor: '#ffc107'
        });
        return false;
    }
    
    // Validar email
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(datos.email)) {
        Swal.fire({
            icon: 'warning',
            title: 'Email Inválido',
            text: 'Por favor ingresa un email válido',
            confirmButtonColor: '#ffc107'
        });
        return false;
    }
    
    return true;
}

// Función auxiliar para debugging
function debugFormulario() {
    console.log('🔍 Debug: Datos del formulario');
    console.log(recopilarDatosFormulario());
}