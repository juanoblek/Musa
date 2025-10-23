/**
 * 🔥 JAVASCRIPT PARA INTEGRACIÓN REAL MERCADOPAGO
 * Solo guarda en BD cuando el pago es 100% exitoso
 */

class PagoRealMercadoPago {
    constructor(publicKey, debug = false) {
        this.publicKey = publicKey;
        this.debug = debug;
        this.mp = new MercadoPago(publicKey);
        this.cardForm = null;
        this.procesandoPago = false;
        
        this.log('Inicializado con Public Key:', publicKey);
    }
    
    log(...args) {
        if (this.debug) {
            console.log('🔥 PagoReal:', ...args);
        }
    }
    
    /**
     * Inicializar formulario de tarjeta
     */
    inicializarFormulario(formSelector, submitButtonSelector) {
        this.log('Inicializando formulario:', formSelector);
        
        // Crear formulario de tarjeta con MercadoPago
        this.cardForm = this.mp.cardForm({
            form: {
                id: formSelector.replace('#', ''),
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
                        this.log('Error al montar formulario:', error);
                    } else {
                        this.log('Formulario de tarjeta montado correctamente');
                    }
                },
                onSubmit: event => {
                    event.preventDefault();
                    this.procesarPago(event);
                },
                onFetching: (resource) => {
                    this.log('Obteniendo:', resource);
                    // Mostrar indicador de carga si es necesario
                    this.mostrarCargando(true);
                    
                    // Ocultar loading después de 800ms para UX fluida
                    setTimeout(() => {
                        this.mostrarCargando(false);
                    }, 800);
                }
            }
        });
        
        // Si hay botón de submit personalizado
        if (submitButtonSelector) {
            const submitBtn = document.querySelector(submitButtonSelector);
            if (submitBtn) {
                submitBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.cardForm.submit();
                });
            }
        }
        
        return this.cardForm;
    }
    
    /**
     * Procesar pago real con MercadoPago
     */
    async procesarPago(event) {
        if (this.procesandoPago) {
            this.log('Ya hay un pago en proceso...');
            return;
        }
        
        this.procesandoPago = true;
        this.mostrarCargando(true, 'Procesando pago...');
        
        try {
            // Obtener token de la tarjeta
            const { token, requireESC, error } = await this.cardForm.getCardFormData();
            
            if (error) {
                throw new Error(`Error con la tarjeta: ${error.message}`);
            }
            
            if (requireESC) {
                this.log('Se requiere código de seguridad mejorado');
                // Aquí podrías manejar ESC si es necesario
            }
            
            this.log('Token de tarjeta obtenido:', token);
            
            // Recopilar datos del formulario principal
            const datosFormulario = this.recopilarDatosFormulario();
            
            // Agregar token de MercadoPago
            const datosCompletos = {
                ...datosFormulario,
                token: token,
                paymentMethod: 'credit_card',
                mercadopago: true
            };
            
            this.log('Enviando datos completos a backend:', datosCompletos);
            
            // Enviar al backend que maneja MercadoPago real
            const respuesta = await this.enviarPagoAlBackend(datosCompletos);
            
            this.log('Respuesta del backend:', respuesta);
            
            // Manejar respuesta
            this.manejarRespuestaPago(respuesta);
            
        } catch (error) {
            this.log('Error en procesarPago:', error);
            this.mostrarError(`Error al procesar el pago: ${error.message}`);
        } finally {
            this.procesandoPago = false;
            this.mostrarCargando(false);
        }
    }
    
    /**
     * Recopilar datos del formulario principal
     */
    recopilarDatosFormulario() {
        const form = document.querySelector('#checkout-form') || document.querySelector('form');
        
        if (!form) {
            throw new Error('No se encontró el formulario principal');
        }
        
        const formData = new FormData(form);
        const datos = {};
        
        // Convertir FormData a objeto
        for (let [key, value] of formData.entries()) {
            datos[key] = value;
        }
        
        // Obtener campos específicos importantes
        const campos = {
            fullName: '#fullName, input[name="fullName"], input[name="nombre"]',
            email: '#email, input[name="email"], #form-checkout__cardholderEmail',
            phone: '#phone, input[name="phone"], input[name="telefono"]',
            documentType: '#form-checkout__identificationType, select[name="documentType"]',
            documentNumber: '#form-checkout__identificationNumber, input[name="documentNumber"]',
            address: '#address, input[name="address"], input[name="direccion"]',
            department: '#department, select[name="department"], select[name="departamento"]',
            city: '#city, input[name="city"], input[name="ciudad"]',
            postalCode: '#postalCode, input[name="postalCode"]',
            notes: '#notes, textarea[name="notes"], textarea[name="notas"]'
        };
        
        // Extraer valores usando selectores múltiples
        Object.keys(campos).forEach(campo => {
            const elemento = document.querySelector(campos[campo]);
            if (elemento && elemento.value) {
                datos[campo] = elemento.value.trim();
            }
        });
        
        // Obtener datos del carrito/productos
        datos.productos = this.obtenerProductosCarrito();
        datos.subtotal = this.calcularSubtotal();
        datos.envio = this.obtenerCostoEnvio();
        datos.total = this.calcularTotal();
        
        // Obtener cuotas seleccionadas
        const installmentsSelect = document.querySelector('#form-checkout__installments');
        if (installmentsSelect && installmentsSelect.value) {
            datos.installments = installmentsSelect.value;
        }
        
        this.log('Datos recopilados del formulario:', datos);
        return datos;
    }
    
    /**
     * Enviar pago al backend
     */
    async enviarPagoAlBackend(datos) {
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
        return resultado;
    }
    
    /**
     * Manejar respuesta del pago
     */
    manejarRespuestaPago(respuesta) {
        if (respuesta.success && respuesta.pago_exitoso) {
            // ✅ PAGO EXITOSO Y GUARDADO EN BD
            this.mostrarExito({
                titulo: '¡Pago Aprobado!',
                mensaje: respuesta.message,
                pedidoId: respuesta.pedido_id,
                mercadopagoId: respuesta.mercadopago_id,
                total: respuesta.total_pagado,
                detalles: respuesta.payment_details
            });
            
            // Opcional: Redireccionar a página de confirmación
            setTimeout(() => {
                this.redirigirConfirmacion(respuesta.pedido_id);
            }, 3000);
            
        } else if (respuesta.pago_exitoso === false) {
            // ❌ PAGO RECHAZADO O PENDIENTE
            this.mostrarEstadoPago({
                estado: respuesta.estado_pago,
                mensaje: respuesta.message,
                detalles: respuesta.status_detail,
                mercadopagoId: respuesta.mercadopago_id
            });
            
        } else if (respuesta.error_critico) {
            // ⚠️ PAGO EXITOSO PERO ERROR EN BD (MUY RARO)
            this.mostrarErrorCritico({
                mensaje: respuesta.message,
                mercadopagoId: respuesta.mercadopago_id,
                soporte: true
            });
            
        } else {
            // ❌ ERROR GENERAL
            this.mostrarError(respuesta.message || 'Error desconocido');
        }
    }
    
    /**
     * Mostrar éxito del pago
     */
    mostrarExito(datos) {
        this.log('Pago exitoso:', datos);
        
        // Crear modal/mensaje de éxito
        const modal = this.crearModal(`
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 48px; color: #4CAF50; margin-bottom: 20px;">✅</div>
                <h2 style="color: #4CAF50; margin-bottom: 10px;">${datos.titulo}</h2>
                <p style="margin-bottom: 15px;">${datos.mensaje}</p>
                
                <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <strong>Número de Pedido:</strong> ${datos.pedidoId}<br>
                    <strong>Total Pagado:</strong> $${this.formatearMonto(datos.total)}<br>
                    ${datos.detalles ? `<strong>Cuotas:</strong> ${datos.detalles.installments}x` : ''}
                    ${datos.detalles && datos.detalles.card_last_digits ? `<br><strong>Tarjeta:</strong> ****${datos.detalles.card_last_digits}` : ''}
                </div>
                
                <p style="color: #666; font-size: 14px;">
                    Tu pedido aparecerá en el panel de administración y recibirás una confirmación por email.
                </p>
                
                <button onclick="this.closest('.pago-modal').remove()" 
                        style="background: #4CAF50; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; margin-top: 15px;">
                    Continuar
                </button>
            </div>
        `);
        
        document.body.appendChild(modal);
    }
    
    /**
     * Mostrar estado del pago (pendiente, rechazado, etc.)
     */
    mostrarEstadoPago(datos) {
        this.log('Estado de pago:', datos);
        
        const colores = {
            'pending': '#FF9800',
            'in_process': '#2196F3', 
            'rejected': '#F44336',
            'cancelled': '#9E9E9E'
        };
        
        const iconos = {
            'pending': '⏳',
            'in_process': '⚙️',
            'rejected': '❌',
            'cancelled': '🚫'
        };
        
        const color = colores[datos.estado] || '#666';
        const icono = iconos[datos.estado] || '❓';
        
        const modal = this.crearModal(`
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 48px; margin-bottom: 20px;">${icono}</div>
                <h2 style="color: ${color}; margin-bottom: 10px;">Estado del Pago</h2>
                <p style="margin-bottom: 15px;">${datos.mensaje}</p>
                
                ${datos.mercadopagoId ? `
                    <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0;">
                        <strong>ID de Pago:</strong> ${datos.mercadopagoId}
                    </div>
                ` : ''}
                
                <p style="color: #666; font-size: 14px;">
                    ${datos.estado === 'rejected' ? 'Puedes intentar nuevamente con otra tarjeta o método de pago.' : 
                      datos.estado === 'pending' ? 'Te notificaremos por email cuando se confirme el pago.' : 
                      'Por favor contacta soporte si tienes dudas.'}
                </p>
                
                <button onclick="this.closest('.pago-modal').remove()" 
                        style="background: ${color}; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; margin-top: 15px;">
                    ${datos.estado === 'rejected' ? 'Intentar Nuevamente' : 'Entendido'}
                </button>
            </div>
        `);
        
        document.body.appendChild(modal);
    }
    
    /**
     * Mostrar error crítico (pago exitoso pero error en BD)
     */
    mostrarErrorCritico(datos) {
        this.log('Error crítico:', datos);
        
        const modal = this.crearModal(`
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 48px; color: #FF5722; margin-bottom: 20px;">⚠️</div>
                <h2 style="color: #FF5722; margin-bottom: 10px;">Situación Especial</h2>
                <p style="margin-bottom: 15px;">${datos.mensaje}</p>
                
                <div style="background: #fff3e0; border: 2px solid #FF9800; padding: 15px; border-radius: 8px; margin: 20px 0;">
                    <strong>Tu pago fue procesado exitosamente</strong><br>
                    ID: ${datos.mercadopagoId}<br><br>
                    Sin embargo, hubo un problema técnico al registrar tu pedido.<br>
                    <strong>Por favor contacta soporte inmediatamente.</strong>
                </div>
                
                <div style="margin-top: 20px;">
                    <a href="mailto:soporte@musamoda.com?subject=Error Crítico - Pago ${datos.mercadopagoId}" 
                       style="background: #FF5722; color: white; text-decoration: none; padding: 12px 24px; border-radius: 6px; display: inline-block; margin-right: 10px;">
                        Contactar Soporte
                    </a>
                    <button onclick="this.closest('.pago-modal').remove()" 
                            style="background: #666; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer;">
                        Cerrar
                    </button>
                </div>
            </div>
        `);
        
        document.body.appendChild(modal);
    }
    
    /**
     * Mostrar error general
     */
    mostrarError(mensaje) {
        this.log('Error:', mensaje);
        
        const modal = this.crearModal(`
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 48px; color: #F44336; margin-bottom: 20px;">❌</div>
                <h2 style="color: #F44336; margin-bottom: 10px;">Error en el Pago</h2>
                <p style="margin-bottom: 15px;">${mensaje}</p>
                
                <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
                    Verifica los datos de tu tarjeta y intenta nuevamente.<br>
                    Si el problema persiste, contacta soporte.
                </p>
                
                <button onclick="this.closest('.pago-modal').remove()" 
                        style="background: #F44336; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px;">
                    Intentar Nuevamente
                </button>
            </div>
        `);
        
        document.body.appendChild(modal);
    }
    
    /**
     * Crear modal genérico
     */
    crearModal(contenido) {
        const modal = document.createElement('div');
        modal.className = 'pago-modal';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;
        
        const contenedor = document.createElement('div');
        contenedor.style.cssText = `
            background: white;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            max-height: 80%;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        `;
        
        contenedor.innerHTML = contenido;
        modal.appendChild(contenedor);
        
        // Cerrar al hacer clic fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        return modal;
    }
    
    /**
     * Mostrar indicador de carga
     */
    mostrarCargando(mostrar, mensaje = 'Procesando...') {
        let loader = document.querySelector('.pago-loader');
        
        if (mostrar) {
            if (!loader) {
                loader = document.createElement('div');
                loader.className = 'pago-loader';
                loader.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(255, 255, 255, 0.9);
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    z-index: 9999;
                `;
                
                loader.innerHTML = `
                    <div style="border: 4px solid #f3f3f3; border-top: 4px solid #4CAF50; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite;"></div>
                    <p style="margin-top: 20px; font-size: 16px; color: #666;">${mensaje}</p>
                    <style>
                        @keyframes spin {
                            0% { transform: rotate(0deg); }
                            100% { transform: rotate(360deg); }
                        }
                    </style>
                `;
                
                document.body.appendChild(loader);
            }
        } else {
            if (loader) {
                loader.remove();
            }
        }
    }
    
    /**
     * Utilidades para datos del carrito
     */
    obtenerProductosCarrito() {
        // Intentar obtener de variables globales o localStorage
        if (typeof carrito !== 'undefined') {
            return JSON.stringify(carrito);
        }
        
        if (localStorage.getItem('carrito')) {
            return localStorage.getItem('carrito');
        }
        
        return JSON.stringify([]);
    }
    
    calcularSubtotal() {
        try {
            const productos = JSON.parse(this.obtenerProductosCarrito());
            return productos.reduce((total, item) => total + (item.precio * item.cantidad), 0);
        } catch {
            return parseFloat(document.querySelector('#subtotal')?.textContent?.replace(/[^\d.]/g, '') || '0');
        }
    }
    
    obtenerCostoEnvio() {
        return parseFloat(document.querySelector('#envio')?.textContent?.replace(/[^\d.]/g, '') || '0'); // ENVÍO SIEMPRE GRATIS
    }
    
    calcularTotal() {
        return this.calcularSubtotal() + this.obtenerCostoEnvio();
    }
    
    formatearMonto(monto) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(monto);
    }
    
    /**
     * Redireccionar a página de confirmación
     */
    redirigirConfirmacion(pedidoId) {
        // Puedes personalizar esta redirección
        const url = `/Musa/confirmacion.html?pedido=${pedidoId}`;
        window.location.href = url;
    }
}

// Uso:
// const pago = new PagoRealMercadoPago('TEST-b3d5b663-664a-4e8f-b759-de5d7c12ef8f', true);
// pago.inicializarFormulario('#checkout-form', '#submit-btn');