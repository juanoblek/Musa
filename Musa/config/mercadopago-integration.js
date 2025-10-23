// ==========================================
// INTEGRACIÓN MERCADO PAGO - MUSA & ARION
// ==========================================

class MercadoPagoIntegration {
    constructor() {
        this.mp = null;
        this.config = null;
        this.init();
    }

    // ===== INICIALIZACIÓN =====
    async init() {
        try {
            // Verificar que la configuración esté cargada
            if (typeof MERCADOPAGO_CONFIG === 'undefined') {
                throw new Error('Configuración de Mercado Pago no encontrada');
            }

            this.config = getMercadoPagoConfig();
            
            // Inicializar SDK de Mercado Pago
            this.mp = new MercadoPago(this.config.PUBLIC_KEY, {
                locale: 'es-CO' // Español Colombia
            });

            console.log('✅ Mercado Pago inicializado correctamente');
            this.updatePaymentUI();
            
        } catch (error) {
            console.error('❌ Error inicializando Mercado Pago:', error);
            this.showError('Error de configuración. Verifica las credenciales.');
        }
    }

    // ===== CREAR PREFERENCIA DE PAGO =====
    async createPreference() {
        try {
            const cart = this.getCartData();
            const customer = this.getCustomerData();
            
            if (!cart.items || cart.items.length === 0) {
                throw new Error('El carrito está vacío');
            }

            const preference = {
                items: cart.items,
                payer: {
                    name: customer.name,
                    surname: customer.surname || '',
                    email: customer.email,
                    phone: {
                        area_code: customer.phone_area || '57',
                        number: customer.phone
                    },
                    address: {
                        street_name: customer.address,
                        zip_code: customer.postal_code
                    }
                },
                back_urls: {
                    success: MERCADOPAGO_CONFIG.URLS.SUCCESS,
                    failure: MERCADOPAGO_CONFIG.URLS.FAILURE,
                    pending: MERCADOPAGO_CONFIG.URLS.PENDING
                },
                auto_return: 'approved',
                payment_methods: MERCADOPAGO_CONFIG.PAYMENT_CONFIG.payment_methods,
                notification_url: window.location.origin + '/webhook/mercadopago',
                external_reference: this.generateOrderId(),
                expires: true,
                expiration_date_from: new Date().toISOString(),
                expiration_date_to: new Date(Date.now() + 30 * 60 * 1000).toISOString() // 30 minutos
            };

            console.log('📝 Creando preferencia:', preference);
            return await this.sendPreferenceToBackend(preference);
            
        } catch (error) {
            console.error('❌ Error creando preferencia:', error);
            throw error;
        }
    }

    // ===== ENVIAR PREFERENCIA AL BACKEND =====
    async sendPreferenceToBackend(preference) {
        try {
            // Si no hay backend, usar método directo (menos seguro)
            if (!this.hasBackend()) {
                return await this.createDirectPreference(preference);
            }

            const response = await fetch('/api/mercadopago/create-preference', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.config.ACCESS_TOKEN}`
                },
                body: JSON.stringify(preference)
            });

            if (!response.ok) {
                throw new Error(`Error del servidor: ${response.status}`);
            }

            const data = await response.json();
            return data.preference_id;
            
        } catch (error) {
            console.error('❌ Error en backend:', error);
            // Fallback a método directo
            return await this.createDirectPreference(preference);
        }
    }

    // ===== CREAR PREFERENCIA DIRECTA (FALLBACK) =====
    async createDirectPreference(preference) {
        try {
            console.log('🔄 Creando preferencia con Mercado Pago API...');
            
            // Validar configuración antes de hacer la petición
            const config = getMercadoPagoConfig();
            if (!config.ACCESS_TOKEN) {
                throw new Error('Access Token no configurado');
            }
            
            console.log('🔑 Usando Access Token:', config.ACCESS_TOKEN.substring(0, 20) + '...');
            
            const response = await fetch('https://api.mercadopago.com/checkout/preferences', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${config.ACCESS_TOKEN}`,
                    'X-Idempotency-Key': `${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
                },
                body: JSON.stringify(preference)
            });

            if (!response.ok) {
                const errorData = await response.text();
                console.error('❌ Error de API:', response.status, errorData);
                
                // Detalles específicos del error 401
                if (response.status === 401) {
                    console.error('🔐 Error de autenticación - verificar credenciales');
                    console.error('🔍 Access Token usado:', config.ACCESS_TOKEN.substring(0, 20) + '...');
                    throw new Error('Error de autenticación: Verifica tus credenciales de Mercado Pago');
                }
                
                throw new Error(`Error de Mercado Pago: ${response.status} - ${errorData}`);
            }

            const data = await response.json();
            console.log('✅ Preferencia creada exitosamente:', data);
            return data.id;
            
        } catch (error) {
            console.error('❌ Error creando preferencia:', error);
            throw error;
        }
    }

    // ===== PROCESAR PAGO =====
    async processPayment() {
        try {
            this.showLoading('Procesando pago...');
            
            // Validar datos del cliente
            if (!this.validateCustomerData()) {
                this.hideLoading();
                return;
            }

            // Obtener datos del carrito y cliente
            const cart = this.getCartData();
            const customer = this.getCustomerData();
            
            if (!cart.items || cart.items.length === 0) {
                throw new Error('El carrito está vacío');
            }

            // Crear preferencia simplificada para mejor compatibilidad
            const preference = {
                items: cart.items,
                payer: {
                    name: customer.name,
                    surname: customer.surname || '',
                    email: customer.email,
                    phone: {
                        area_code: '57',
                        number: customer.phone
                    },
                    address: {
                        street_name: customer.address,
                        zip_code: customer.postal_code
                    }
                },
                back_urls: {
                    success: MERCADOPAGO_CONFIG.URLS.SUCCESS,
                    failure: MERCADOPAGO_CONFIG.URLS.FAILURE,
                    pending: MERCADOPAGO_CONFIG.URLS.PENDING
                },
                auto_return: 'approved',
                // Configuración mínima y segura de métodos de pago
                payment_methods: {
                    excluded_payment_methods: [],
                    excluded_payment_types: [],
                    installments: 12
                },
                external_reference: this.generateOrderId(),
                statement_descriptor: 'MUSA&ARION'
            };

            console.log('📝 Creando preferencia de pago:', preference);
            
            // Crear preferencia usando método directo
            const preferenceId = await this.createDirectPreference(preference);
            
            if (!preferenceId) {
                throw new Error('No se pudo crear la preferencia de pago');
            }

            console.log('✅ Preferencia creada:', preferenceId);
            
            // Inicializar checkout con configuración explícita
            const checkout = this.mp.checkout({
                preference: {
                    id: preferenceId
                },
                autoOpen: true, // Abrir automáticamente
                theme: {
                    elementsColor: '#667eea',
                    headerColor: '#667eea'
                }
            });

            this.hideLoading();
            console.log('🚀 Checkout de Mercado Pago iniciado');
            
        } catch (error) {
            this.hideLoading();
            console.error('❌ Error procesando pago:', error);
            this.showError(`Error procesando el pago: ${error.message}`);
        }
    }

    // ===== PROCESAR PAGO CON MÉTODO ESPECÍFICO =====
    async processPaymentWithMethod(paymentMethod = 'all') {
        try {
            this.showLoading('Iniciando pago...');
            
            // Validar datos del cliente
            if (!this.validateCustomerData()) {
                this.hideLoading();
                return;
            }

            // Ocultar modal actual y mostrar checkout integrado
            const modal = bootstrap.Modal.getInstance(document.getElementById('PaymentModal'));
            if (modal) {
                modal.hide();
            }

            // Mostrar el checkout en modo integrado
            await this.showIntegratedCheckout(paymentMethod);
            
        } catch (error) {
            this.hideLoading();
            console.error('❌ Error procesando pago:', error);
            this.showError(`Error procesando el pago: ${error.message}`);
        }
    }

    // ===== CHECKOUT INTEGRADO =====
    async showIntegratedCheckout(paymentMethod = 'all') {
        try {
            const cart = this.getCartData();
            const customer = this.getCustomerData();
            
            // Crear preferencia específica para checkout integrado
            const preference = {
                items: cart.items,
                payer: {
                    name: customer.name,
                    surname: customer.surname || '',
                    email: customer.email,
                    phone: {
                        area_code: '57',
                        number: customer.phone
                    },
                    address: {
                        street_name: customer.address,
                        zip_code: customer.postal_code
                    }
                },
                back_urls: {
                    success: MERCADOPAGO_CONFIG.URLS.SUCCESS,
                    failure: MERCADOPAGO_CONFIG.URLS.FAILURE,
                    pending: MERCADOPAGO_CONFIG.URLS.PENDING
                },
                auto_return: 'approved',
                payment_methods: this.getPaymentMethodsConfig(paymentMethod),
                external_reference: this.generateOrderId(),
                statement_descriptor: 'MUSA&ARION'
            };

            console.log('📝 Creando preferencia para checkout integrado:', preference);
            
            const preferenceId = await this.createDirectPreference(preference);
            
            if (!preferenceId) {
                throw new Error('No se pudo crear la preferencia de pago');
            }

            // Crear modal personalizado para checkout integrado
            this.createIntegratedCheckoutModal(preferenceId, paymentMethod);
            
        } catch (error) {
            console.error('❌ Error en checkout integrado:', error);
            this.showError(`Error en el checkout: ${error.message}`);
        }
    }

    // ===== CONFIGURACIÓN DE MÉTODOS DE PAGO =====
    getPaymentMethodsConfig(method) {
        // Configuraciones válidas según documentación de Mercado Pago
        const configs = {
            'credit_card': {
                excluded_payment_methods: [],
                excluded_payment_types: [],
                default_payment_method_id: null,
                installments: 12
            },
            'debit_card': {
                excluded_payment_methods: [],
                excluded_payment_types: [],
                default_payment_method_id: null,
                installments: 1
            },
            'all': {
                excluded_payment_methods: [],
                excluded_payment_types: [],
                default_payment_method_id: null,
                installments: 12
            }
        };
        
        return configs[method] || configs['all'];
    }

    // ===== CREAR MODAL DE CHECKOUT INTEGRADO =====
    createIntegratedCheckoutModal(preferenceId, paymentMethod) {
        // Remover modal existente si existe
        const existingModal = document.getElementById('IntegratedCheckoutModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Crear nuevo modal
        const modalHTML = `
            <div class="modal fade" id="IntegratedCheckoutModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-credit-card me-2"></i>
                                ${this.getPaymentMethodTitle(paymentMethod)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="mp-checkout-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Mostrar modal
        const modal = new bootstrap.Modal(document.getElementById('IntegratedCheckoutModal'));
        modal.show();

        // Inicializar checkout integrado dentro del modal
        setTimeout(() => {
            this.initIntegratedCheckout(preferenceId);
        }, 500);
    }

    // ===== INICIALIZAR CHECKOUT INTEGRADO =====
    async initIntegratedCheckout(preferenceId) {
        try {
            console.log('🚀 Inicializando checkout integrado...');
            
            const checkout = this.mp.checkout({
                preference: {
                    id: preferenceId
                },
                render: {
                    container: '#mp-checkout-container',
                    label: 'Pagar con Mercado Pago'
                },
                theme: {
                    elementsColor: '#667eea',
                    headerColor: '#667eea'
                }
            });

            this.hideLoading();
            console.log('✅ Checkout integrado inicializado');
            
        } catch (error) {
            this.hideLoading();
            console.error('❌ Error inicializando checkout integrado:', error);
            this.showError('Error cargando el formulario de pago');
        }
    }

    // ===== OBTENER TÍTULO DEL MÉTODO DE PAGO =====
    getPaymentMethodTitle(method) {
        const titles = {
            'credit_card': 'Pago con Tarjeta de Crédito',
            'debit_card': 'Pago con Tarjeta de Débito',
            'all': 'Seleccionar Método de Pago'
        };
        
        return titles[method] || titles['all'];
    }

    // ===== OBTENER DATOS DEL CARRITO =====
    getCartData() {
        // Obtener carrito desde localStorage con la clave correcta 'carrito'
        const cartItems = JSON.parse(localStorage.getItem('carrito')) || [];
        
        // Si hay datos globales del carrito disponibles, usarlos PERO FORZAR SHIPPING = 0
        const carritoTotales = window.carritoTotales;
        
        // 🛡️ FORZAR SHIPPING = 0 SIEMPRE - NO IMPORTA LA CONFIGURACIÓN
        const shipping = 0; // MERCADOPAGO_CONFIG.PAYMENT_CONFIG.shipping_cost; // COMENTADO - SIEMPRE 0
        console.log('🛡️ [MERCADOPAGO-INTEGRATION] Shipping forzado a 0, configuración ignorada');
        
        const items = cartItems.map(item => ({
            id: item.id,
            title: item.nombre, // 'nombre' en lugar de 'name'
            description: `${item.nombre} - ${item.color} - Talla ${item.talla}`, // 'talla' en lugar de 'size'
            picture_url: item.imagen || '', // 'imagen' en lugar de 'image'
            category_id: 'fashion',
            quantity: parseInt(item.cantidad) || 1, // 'cantidad' en lugar de 'quantity'
            unit_price: formatPrice(item.precio), // 'precio' en lugar de 'price'
            currency_id: MERCADOPAGO_CONFIG.PAYMENT_CONFIG.currency
        }));

        // Calcular subtotal
        const subtotal = items.reduce((sum, item) => sum + (item.unit_price * item.quantity), 0);
        
        // Usar totales calculados globalmente si están disponibles PERO CORREGIR SHIPPING
        let rawSubtotal = carritoTotales ? carritoTotales.subtotal : subtotal;
        
        // 🛡️ VALIDAR QUE EL SUBTOTAL SEA CORRECTO
        if (!rawSubtotal || rawSubtotal === 0) {
            rawSubtotal = subtotal; // Usar el calculado localmente
        }
        
        const finalSubtotal = rawSubtotal;
        const finalShipping = 0; // ENVÍO SIEMPRE GRATIS - FORZADO ABSOLUTO
        const finalTotal = finalSubtotal; // TOTAL = SUBTOTAL (SIN ENVÍO NUNCA)
        
        console.log('🛡️ [MERCADOPAGO-INTEGRATION] Totales finales forzados:', {
            originalCarritoTotales: carritoTotales,
            finalSubtotal: finalSubtotal,
            finalShipping: finalShipping,
            finalTotal: finalTotal
        });

        // Agregar envío como item si no es gratis
        if (finalShipping > 0) {
            items.push({
                id: 'shipping',
                title: 'Envío',
                description: 'Costo de envío a domicilio',
                category_id: 'shipping',
                quantity: 1,
                unit_price: finalShipping,
                currency_id: MERCADOPAGO_CONFIG.PAYMENT_CONFIG.currency
            });
        }

        return { 
            items, 
            subtotal: finalSubtotal, 
            total: finalTotal,
            shipping: finalShipping
        };
    }

    // ===== OBTENER DATOS DEL CLIENTE =====
    getCustomerData() {
        const fullName = document.getElementById('nombreCompleto')?.value || '';
        const nameParts = fullName.split(' ');
        
        return {
            name: nameParts[0] || '',
            surname: nameParts.slice(1).join(' ') || '',
            email: document.getElementById('email')?.value || 'cliente@email.com',
            phone: document.getElementById('telefono')?.value || '',
            phone_area: '57', // Colombia
            address: document.getElementById('direccion')?.value || '',
            postal_code: document.getElementById('codigoPostal')?.value || '',
            city: document.getElementById('ciudad')?.value || '',
            state: document.getElementById('departamento')?.value || ''
        };
    }

    // ===== VALIDACIONES =====
    validateCustomerData() {
        const requiredFields = [
            { id: 'nombreCompleto', name: 'Nombre completo' },
            { id: 'telefono', name: 'Teléfono' },
            { id: 'direccion', name: 'Dirección' },
            { id: 'codigoPostal', name: 'Código postal' }
        ];

        const missing = requiredFields.filter(field => {
            const element = document.getElementById(field.id);
            return !element || !element.value.trim();
        });

        if (missing.length > 0) {
            const missingNames = missing.map(field => field.name).join(', ');
            this.showError(`Por favor completa los siguientes campos: ${missingNames}`);
            return false;
        }

        return true;
    }

    // ===== UTILIDADES =====
    generateOrderId() {
        return `ORDER-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
    }

    hasBackend() {
        // Verificar si hay un backend disponible
        return window.location.protocol === 'http:' || window.location.protocol === 'https:';
    }

    // ===== UI HELPERS =====
    updatePaymentUI() {
        // Actualizar el botón de pago en el modal
        const payButton = document.querySelector('[data-mp-payment-button]');
        if (payButton) {
            payButton.textContent = isTestMode() ? 
                '🧪 Pagar (Modo Prueba)' : 
                '💳 Pagar con Mercado Pago';
            payButton.onclick = () => this.processPayment();
        }
    }

    showLoading(message = 'Cargando...') {
        // Mostrar loading en el modal
        const modal = document.querySelector('#PaymentModal .modal-body');
        if (modal) {
            modal.insertAdjacentHTML('afterbegin', `
                <div id="mp-loading" class="text-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2">${message}</p>
                </div>
            `);
        }
    }

    hideLoading() {
        const loading = document.getElementById('mp-loading');
        if (loading) {
            loading.remove();
        }
    }

    showError(message) {
        // Mostrar error usando SweetAlert2 si está disponible
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonColor: '#dc3545'
            });
        } else {
            alert(message);
        }
    }

    showSuccess(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: message,
                confirmButtonColor: '#28a745'
            });
        } else {
            alert(message);
        }
    }
}

// ===== INICIALIZACIÓN GLOBAL =====
let mercadoPago = null;

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Verificar que el SDK esté cargado
    if (typeof MercadoPago !== 'undefined') {
        mercadoPago = new MercadoPagoIntegration();
    } else {
        console.error('❌ SDK de Mercado Pago no cargado');
    }
});

// ===== FUNCIONES PÚBLICAS =====
window.initMercadoPagoPayment = function() {
    if (mercadoPago) {
        mercadoPago.processPayment();
    } else {
        console.error('❌ Mercado Pago no inicializado');
    }
};

window.initMercadoPagoPaymentMethod = function(method) {
    if (mercadoPago) {
        mercadoPago.processPaymentWithMethod(method);
    } else {
        console.error('❌ Mercado Pago no inicializado');
    }
};

window.getMercadoPagoInstance = function() {
    return mercadoPago;
};

console.log('🔧 Integración de Mercado Pago cargada');
