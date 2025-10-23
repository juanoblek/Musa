/**
 * 🔥 INTEGRACIÓN COMPLETA PARA PAGO-PREMIUM.HTML
 * Actualiza el formulario existente para usar MercadoPago real + BD
 */

// Actualizar el archivo pago-premium.html en la línea donde se inicializa MercadoPago
// Reemplazar la clase PremiumPaymentSystem con esta versión:

class PremiumPaymentSystemReal {
    constructor() {
        this.mp = null;
        this.cart = [];
        this.subtotal = 0;
        this.shippingCost = 0;  // ENVÍO SIEMPRE GRATIS
        this.total = 0;
        this.procesandoPago = false;
        
        console.log('🔥 Sistema de Pago Real MUSA Fashion inicializando...');
        this.init();
    }
    
    async init() {
        try {
            // Cargar carrito
            this.loadCart();
            
            // Inicializar MercadoPago
            await this.initMercadoPago();
            
            // Renderizar orden
            this.renderOrderSummary();
            
            // Configurar eventos
            this.setupEventListeners();
            
            console.log('✅ Sistema inicializado correctamente');
            
        } catch (error) {
            console.error('❌ Error inicializando sistema:', error);
            this.showAlert('Error al inicializar el sistema de pago', 'error');
        }
    }
    
    async initMercadoPago() {
        try {
            // 🔑 CREDENCIALES MERCADOPAGO - PRODUCCIÓN
            const publicKey = 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577'; // PRODUCCIÓN
            
            this.mp = new MercadoPago(publicKey, { locale: 'es-CO' });
            
            console.log('🔥 MercadoPago Real inicializado en PRODUCCIÓN');
            
            // Inicializar formulario de tarjeta si no existe checkout redirect
            if (document.getElementById('card-form-container')) {
                await this.initCardForm();
            }
            
        } catch (error) {
            console.error('❌ Error inicializando MercadoPago:', error);
            throw error;
        }
    }
    
    async initCardForm() {
        try {
            // Crear formulario de tarjeta
            this.cardForm = this.mp.cardForm({
                form: {
                    id: "payment-form",
                    cardNumber: {
                        id: "form-checkout__cardNumber",
                        placeholder: "Número de tarjeta",
                    },
                    expirationDate: {
                        id: "form-checkout__expirationDate", 
                        placeholder: "MM/YY",
                    },
                    securityCode: {
                        id: "form-checkout__securityCode",
                        placeholder: "Código de seguridad",
                    },
                    cardholderName: {
                        id: "form-checkout__cardholderName",
                        placeholder: "Titular de la tarjeta",
                    },
                    issuer: {
                        id: "form-checkout__issuer",
                        placeholder: "Banco emisor",
                    },
                    installments: {
                        id: "form-checkout__installments",
                        placeholder: "Cuotas",
                    },
                    identificationType: {
                        id: "form-checkout__identificationType",
                    },
                    identificationNumber: {
                        id: "form-checkout__identificationNumber",
                        placeholder: "Número de documento",
                    },
                    cardholderEmail: {
                        id: "form-checkout__cardholderEmail",
                        placeholder: "E-mail",
                    }
                },
                callbacks: {
                    onFormMounted: error => {
                        if (error) {
                            console.error('Error al montar formulario:', error);
                        } else {
                            console.log('✅ Formulario de tarjeta montado');
                            this.syncEmailFields();
                        }
                    },
                    onSubmit: event => {
                        event.preventDefault();
                        this.procesarPagoReal(event);
                    },
                    onFetching: (resource) => {
                        console.log('🔄 Obteniendo:', resource);
                        this.toggleLoading(true);
                        setTimeout(() => this.toggleLoading(false), 800);
                    }
                }
            });
            
        } catch (error) {
            console.error('❌ Error creando card form:', error);
        }
    }
    
    syncEmailFields() {
        // Sincronizar email principal con email de tarjeta
        const emailPrincipal = document.getElementById('email');
        const emailTarjeta = document.getElementById('form-checkout__cardholderEmail');
        
        if (emailPrincipal && emailTarjeta) {
            emailPrincipal.addEventListener('input', () => {
                emailTarjeta.value = emailPrincipal.value;
            });
            
            // Sincronizar valor inicial
            if (emailPrincipal.value) {
                emailTarjeta.value = emailPrincipal.value;
            }
        }
    }
    
    /**
     * 🔥 PROCESAR PAGO REAL CON MERCADOPAGO + BD
     */
    async procesarPagoReal(event) {
        if (this.procesandoPago) return;
        
        this.procesandoPago = true;
        this.toggleLoading(true, 'Procesando pago seguro...');
        
        try {
            // Validar formulario
            if (!this.validateForm()) {
                throw new Error('Por favor completa todos los campos obligatorios');
            }
            
            let pagoExitoso = false;
            
            // Verificar si hay formulario de tarjeta (pago directo)
            if (this.cardForm) {
                pagoExitoso = await this.procesarPagoDirecto();
            } else {
                // Usar checkout redirect (Checkout Pro)
                pagoExitoso = await this.procesarCheckoutRedirect();
            }
            
            if (pagoExitoso) {
                this.showAlert('¡Pago procesado exitosamente!', 'success');
            }
            
        } catch (error) {
            console.error('❌ Error en pago:', error);
            this.showAlert(error.message || 'Error al procesar el pago', 'error');
        } finally {
            this.procesandoPago = false;
            this.toggleLoading(false);
        }
    }
    
    /**
     * Procesar pago directo con tarjeta
     */
    async procesarPagoDirecto() {
        try {
            // Obtener token de tarjeta
            const { token, requireESC, error } = await this.cardForm.getCardFormData();
            
            if (error) {
                throw new Error(`Error con la tarjeta: ${error.message}`);
            }
            
            console.log('🔑 Token obtenido:', token);
            
            // Preparar datos completos
            const datosCompletos = this.prepareCompletePaymentData(token);
            
            // Enviar al backend real
            const respuesta = await this.enviarPagoAlBackend(datosCompletos);
            
            // Manejar respuesta
            return this.manejarRespuestaPago(respuesta);
            
        } catch (error) {
            console.error('❌ Error en pago directo:', error);
            throw error;
        }
    }
    
    /**
     * Procesar con Checkout Pro (redirección)
     */
    async procesarCheckoutRedirect() {
        try {
            const datosCompletos = this.prepareCompletePaymentData();
            
            // Para checkout redirect, crear preferencia
            const preferencia = await this.crearPreferencia(datosCompletos);
            
            if (preferencia.init_point) {
                // Guardar datos temporalmente para después del pago
                sessionStorage.setItem('pending_order_data', JSON.stringify(datosCompletos));
                
                // Mostrar modal antes de redireccionar
                const result = await Swal.fire({
                    title: '🚀 Redirigiendo a MercadoPago',
                    html: `
                        <div class="text-start">
                            <p><strong>Total a pagar:</strong> $${this.formatCurrency(this.total)}</p>
                            <p>Serás redirigido a MercadoPago para completar el pago de forma segura.</p>
                            <div class="alert alert-info mt-3">
                                <strong>💡 Importante:</strong> Tu pedido se guardará automáticamente solo cuando el pago sea exitoso.
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#00b4d8'
                });
                
                if (result.isConfirmed) {
                    window.location.href = preferencia.init_point;
                    return true;
                }
            }
            
            return false;
            
        } catch (error) {
            console.error('❌ Error en checkout redirect:', error);
            throw error;
        }
    }
    
    /**
     * Preparar datos completos del pago
     */
    prepareCompletePaymentData(token = null) {
        const datos = {
            // Datos del cliente
            fullName: document.getElementById('fullName')?.value?.trim() || '',
            email: document.getElementById('email')?.value?.trim() || '',
            phone: document.getElementById('phone')?.value?.trim() || '',
            documentType: document.getElementById('form-checkout__identificationType')?.value || 'CC',
            documentNumber: document.getElementById('form-checkout__identificationNumber')?.value?.trim() || '',
            
            // Datos de envío
            address: document.getElementById('address')?.value?.trim() || '',
            department: document.getElementById('department')?.value || '',
            city: document.getElementById('city')?.value?.trim() || '',
            postalCode: document.getElementById('postalCode')?.value?.trim() || '',
            notes: document.getElementById('deliveryNotes')?.value?.trim() || '',
            
            // Datos del pedido
            productos: JSON.stringify(this.cart),
            subtotal: this.subtotal,
            envio: this.shippingCost,
            total: this.total,
            
            // Datos de pago
            paymentMethod: token ? 'credit_card' : 'redirect',
            mercadopago: true
        };
        
        // Agregar token si está disponible
        if (token) {
            datos.token = token;
            
            // Agregar cuotas si están seleccionadas
            const installments = document.getElementById('form-checkout__installments')?.value;
            if (installments) {
                datos.installments = parseInt(installments);
            }
        }
        
        return datos;
    }
    
    /**
     * Enviar pago al backend real
     */
    async enviarPagoAlBackend(datos) {
        try {
            const response = await fetch('/Musa/api/pago-real-mercadopago.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(datos)
            });
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const resultado = await response.json();
            console.log('📥 Respuesta backend:', resultado);
            
            return resultado;
            
        } catch (error) {
            console.error('❌ Error enviando al backend:', error);
            throw new Error('Error de conexión con el servidor');
        }
    }
    
    /**
     * Crear preferencia para Checkout Pro
     */
    async crearPreferencia(datos) {
        try {
            const items = this.cart.map(item => ({
                id: item.id?.toString() || '1',
                title: item.nombre || item.name || 'Producto MUSA',
                currency_id: 'COP',
                picture_url: item.imagen || '',
                description: `${item.talla || 'Talla única'} - ${item.color || 'Color único'}`,
                category_id: 'fashion',
                quantity: item.cantidad || 1,
                unit_price: item.precio || 0
            }));
            
            // Agregar envío como item separado
            items.push({
                id: 'envio',
                title: 'Envío Nacional',
                currency_id: 'COP',
                description: 'Envío a nivel nacional',
                category_id: 'shipping',
                quantity: 1,
                unit_price: this.shippingCost
            });
            
            const preferenceData = {
                items: items,
                back_urls: {
                    success: `${window.location.origin}/Musa/success-premium.html`,
                    failure: `${window.location.origin}/Musa/failure-premium.html`,
                    pending: `${window.location.origin}/Musa/pending-premium.html`
                },
                auto_return: 'approved',
                notification_url: `${window.location.origin}/Musa/api/webhook-mercadopago.php`,
                external_reference: 'MUSA-' + Date.now(),
                metadata: {
                    cliente_nombre: datos.fullName,
                    cliente_email: datos.email,
                    datos_envio: JSON.stringify({
                        direccion: datos.address,
                        ciudad: datos.city,
                        departamento: datos.department
                    })
                },
                payer: {
                    name: datos.fullName,
                    email: datos.email,
                    phone: {
                        area_code: '57',
                        number: datos.phone
                    },
                    identification: {
                        type: datos.documentType,
                        number: datos.documentNumber
                    },
                    address: {
                        street_name: datos.address,
                        street_number: '1',
                        zip_code: datos.postalCode || '110111'
                    }
                },
                payment_methods: {
                    excluded_payment_methods: [],
                    excluded_payment_types: [],
                    installments: 12
                },
                shipments: {
                    cost: this.shippingCost,
                    mode: 'not_specified'
                }
            };
            
            // Crear preferencia en MercadoPago
            const response = await fetch('/Musa/api/crear-preferencia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ 
                    preference: preferenceData,
                    order_data: datos 
                })
            });
            
            if (!response.ok) {
                throw new Error('Error creando preferencia');
            }
            
            const resultado = await response.json();
            return resultado;
            
        } catch (error) {
            console.error('❌ Error creando preferencia:', error);
            throw error;
        }
    }
    
    /**
     * Manejar respuesta del pago
     */
    manejarRespuestaPago(respuesta) {
        if (respuesta.success && respuesta.pago_exitoso) {
            // ✅ PAGO EXITOSO Y GUARDADO EN BD
            this.mostrarExitoPago(respuesta);
            return true;
            
        } else if (respuesta.pago_exitoso === false) {
            // ❌ PAGO RECHAZADO O PENDIENTE
            this.mostrarEstadoPago(respuesta);
            return false;
            
        } else if (respuesta.error_critico) {
            // ⚠️ PAGO EXITOSO PERO ERROR EN BD
            this.mostrarErrorCritico(respuesta);
            return false;
            
        } else {
            // ❌ ERROR GENERAL
            this.showAlert(respuesta.message || 'Error desconocido', 'error');
            return false;
        }
    }
    
    /**
     * Mostrar éxito del pago
     */
    mostrarExitoPago(datos) {
        Swal.fire({
            title: '¡Pago Exitoso!',
            html: `
                <div class="text-center">
                    <div style="font-size: 64px; color: #4CAF50; margin: 20px 0;">✅</div>
                    <h4 style="color: #4CAF50; margin-bottom: 20px;">¡Tu pago fue procesado exitosamente!</h4>
                    
                    <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: left; margin: 20px 0;">
                        <strong>📋 Detalles del Pedido:</strong><br>
                        <strong>Número:</strong> ${datos.pedido_id}<br>
                        <strong>Total Pagado:</strong> $${this.formatCurrency(datos.total_pagado)}<br>
                        <strong>MercadoPago ID:</strong> ${datos.mercadopago_id}<br>
                        ${datos.payment_details ? `<strong>Cuotas:</strong> ${datos.payment_details.installments}x<br>` : ''}
                        ${datos.payment_details?.card_last_digits ? `<strong>Tarjeta:</strong> ****${datos.payment_details.card_last_digits}<br>` : ''}
                    </div>
                    
                    <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        ✅ <strong>Tu pedido aparece en el panel de administración</strong><br>
                        📧 Recibirás confirmación por email<br>
                        🚚 Te contactaremos para coordinar el envío
                    </div>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'Continuar Comprando',
            confirmButtonColor: '#4CAF50',
            allowOutsideClick: false
        }).then(() => {
            // Limpiar carrito y redireccionar
            this.clearCart();
            window.location.href = '/Musa/index.html';
        });
    }
    
    /**
     * Mostrar estado del pago (pendiente, rechazado, etc.)
     */
    mostrarEstadoPago(datos) {
        const iconos = {
            'pending': '⏳',
            'in_process': '⚙️',
            'rejected': '❌',
            'cancelled': '🚫'
        };
        
        const colores = {
            'pending': '#FF9800',
            'in_process': '#2196F3',
            'rejected': '#F44336',
            'cancelled': '#9E9E9E'
        };
        
        const mensajes = {
            'pending': 'Tu pago está siendo procesado. Te notificaremos cuando sea confirmado.',
            'in_process': 'Estamos procesando tu pago. En breve te confirmaremos.',
            'rejected': 'Tu pago fue rechazado. Verifica los datos de tu tarjeta e intenta nuevamente.',
            'cancelled': 'El pago fue cancelado.'
        };
        
        const estado = datos.estado_pago || 'unknown';
        const icono = iconos[estado] || '❓';
        const color = colores[estado] || '#666';
        const mensaje = mensajes[estado] || datos.message;
        
        Swal.fire({
            title: 'Estado del Pago',
            html: `
                <div class="text-center">
                    <div style="font-size: 64px; margin: 20px 0;">${icono}</div>
                    <h4 style="color: ${color}; margin-bottom: 20px;">Estado: ${estado.toUpperCase()}</h4>
                    
                    <p style="margin-bottom: 20px;">${mensaje}</p>
                    
                    ${datos.mercadopago_id ? `
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;">
                            <strong>ID de Pago:</strong> ${datos.mercadopago_id}
                        </div>
                    ` : ''}
                    
                    <div style="background: #fff3e0; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        ${estado === 'rejected' ? 
                            '💡 <strong>Tip:</strong> Puedes intentar nuevamente con otra tarjeta o método de pago.' :
                            estado === 'pending' ? 
                            '📧 <strong>Te notificaremos por email</strong> cuando se confirme el pago.' :
                            '📞 Contacta soporte si tienes dudas.'}
                    </div>
                </div>
            `,
            icon: estado === 'rejected' ? 'error' : 'warning',
            confirmButtonText: estado === 'rejected' ? 'Intentar Nuevamente' : 'Entendido',
            confirmButtonColor: color
        });
    }
    
    /**
     * Mostrar error crítico
     */
    mostrarErrorCritico(datos) {
        Swal.fire({
            title: '⚠️ Situación Especial',
            html: `
                <div class="text-center">
                    <div style="font-size: 64px; color: #FF5722; margin: 20px 0;">⚠️</div>
                    
                    <div style="background: #fff3e0; border: 2px solid #FF9800; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <h5 style="color: #E65100; margin-bottom: 15px;">Tu pago fue procesado exitosamente</h5>
                        <p><strong>MercadoPago ID:</strong> ${datos.mercadopago_id}</p>
                        <hr style="margin: 15px 0;">
                        <p style="color: #D84315;"><strong>Sin embargo, hubo un problema técnico al registrar tu pedido.</strong></p>
                        <p style="color: #D84315;"><strong>Por favor contacta soporte INMEDIATAMENTE.</strong></p>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <p style="font-size: 14px; color: #666;">
                            Cita este ID cuando contactes soporte: <strong>${datos.mercadopago_id}</strong>
                        </p>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Contactar Soporte',
            cancelButtonText: 'Cerrar',
            confirmButtonColor: '#FF5722'
        }).then((result) => {
            if (result.isConfirmed) {
                window.open(`mailto:soporte@musamoda.com?subject=Error Crítico - Pago ${datos.mercadopago_id}&body=Hola, mi pago fue procesado pero hubo un error al registrar mi pedido. ID de pago: ${datos.mercadopago_id}`);
            }
        });
    }
    
    // Métodos auxiliares del sistema original
    loadCart() {
        const savedCart = localStorage.getItem('cart');
        if (savedCart) {
            this.cart = JSON.parse(savedCart);
        } else {
            // Carrito de prueba si no hay datos
            this.cart = [
                {
                    id: 1,
                    nombre: "Blusa Elegante",
                    precio: 89900,
                    cantidad: 1,
                    talla: "M",
                    color: "Rosa",
                    imagen: "img/productos/blusa1.jpg"
                }
            ];
        }
        
        this.calculateTotals();
    }
    
    calculateTotals() {
        this.subtotal = this.cart.reduce((total, item) => total + (item.precio * item.cantidad), 0);
        this.total = this.subtotal; // NO SUMAR ENVÍO - SIEMPRE GRATIS
    }
    
    renderOrderSummary() {
        const container = document.getElementById('order-summary');
        if (!container) return;
        
        let html = '<div class="order-items">';
        
        this.cart.forEach(item => {
            html += `
                <div class="order-item">
                    <img src="${item.imagen || 'img/default-product.jpg'}" alt="${item.nombre}" class="item-image">
                    <div class="item-details">
                        <h6>${item.nombre}</h6>
                        <small>Talla: ${item.talla} | Color: ${item.color}</small>
                        <div class="item-price">$${this.formatCurrency(item.precio)} x ${item.cantidad}</div>
                    </div>
                </div>
            `;
        });
        
        html += `
            </div>
            <div class="order-totals">
                <div class="total-line">
                    <span>Subtotal:</span>
                    <span>$${this.formatCurrency(this.subtotal)}</span>
                </div>
                <div class="total-line">
                    <span>Envío:</span>
                    <span>$${this.formatCurrency(this.shippingCost)}</span>
                </div>
                <div class="total-line total-final">
                    <span><strong>Total:</strong></span>
                    <span><strong>$${this.formatCurrency(this.total)}</strong></span>
                </div>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    setupEventListeners() {
        // Botón de pago principal
        const payButton = document.getElementById('pay-button');
        if (payButton) {
            payButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.procesarPagoReal(e);
            });
        }
        
        // Sincronizar emails
        this.syncEmailFields();
    }
    
    validateForm() {
        const required = ['fullName', 'email', 'address', 'city'];
        
        for (const field of required) {
            const element = document.getElementById(field);
            if (!element || !element.value.trim()) {
                element?.focus();
                return false;
            }
        }
        
        // Validar email
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            document.getElementById('email').focus();
            return false;
        }
        
        return true;
    }
    
    toggleLoading(loading, mensaje = 'Procesando...') {
        const button = document.getElementById('pay-button');
        if (!button) return;
        
        if (loading) {
            button.disabled = true;
            button.innerHTML = `
                <div class="d-flex align-items-center justify-content-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    ${mensaje}
                </div>
            `;
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-lock"></i> Pagar Ahora';
        }
    }
    
    showAlert(message, type = 'info') {
        const colors = {
            'success': '#4CAF50',
            'error': '#F44336',
            'warning': '#FF9800',
            'info': '#2196F3'
        };
        
        const icons = {
            'success': '✅',
            'error': '❌', 
            'warning': '⚠️',
            'info': 'ℹ️'
        };
        
        // Usar SweetAlert2 para mensajes más elegantes
        Swal.fire({
            title: icons[type] + ' ' + (type === 'success' ? 'Éxito' : type === 'error' ? 'Error' : type === 'warning' ? 'Atención' : 'Información'),
            text: message,
            icon: type,
            confirmButtonColor: colors[type],
            timer: type === 'success' ? 3000 : undefined,
            timerProgressBar: type === 'success'
        });
    }
    
    formatCurrency(amount) {
        return new Intl.NumberFormat('es-CO').format(amount);
    }
    
    clearCart() {
        this.cart = [];
        localStorage.removeItem('cart');
    }
}

// Para usar en pago-premium.html, reemplazar la línea de inicialización:
// window.paymentSystem = new PremiumPaymentSystemReal();