import CONFIG from './config.js';

class BoldPayments {
    static validateOrderData(orderData) {
        if (!orderData.total || orderData.total < 1000) {
            throw new Error('El monto mínimo de compra es de $1,000 COP');
        }
        
        if (!orderData.customer || !orderData.customer.email || !orderData.customer.name) {
            throw new Error('Los datos del cliente son incompletos');
        }
    }

    static generateOrderId() {
        const timestamp = Date.now().toString(36);
        const random = Math.random().toString(36).substr(2, 5);
        return `ORD-${timestamp}-${random}`.toUpperCase();
    }

    static async getSignature(orderData) {
        try {
            const response = await fetch('/api/generate-signature', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });

            if (!response.ok) {
                throw new Error('Error al generar la firma');
            }

            return await response.json();
        } catch (error) {
            console.error('Error getting signature:', error);
            throw new Error('Error al generar la firma para el pago');
        }
    }

    static async initializeCheckout(cartData, customerData) {
        try {
            this.validateOrderData({
                total: cartData.total,
                customer: customerData
            });

            const orderData = {
                id: this.generateOrderId(),
                amount: Math.round(cartData.total * 100), // Convert to cents
                currency: 'COP',
                customer: customerData
            };

            const { hash } = await this.getSignature(orderData);

            // Initialize Bold checkout
            Bold.initialize({
                key: CONFIG.BOLD.API_KEY,
                orderId: orderData.id,
                amount: orderData.amount,
                currency: orderData.currency,
                integritySignature: hash,
                container: '#bold-checkout-container',
                redirectionUrl: `${window.location.origin}/success.html`,
                customer: {
                    email: customerData.email,
                    name: customerData.name,
                    phone: customerData.phone,
                    address: {
                        address: customerData.address,
                        city: customerData.city,
                        country: 'CO'
                    }
                },
                onSuccess: (data) => {
                    console.log('Payment successful:', data);
                    localStorage.removeItem('cart');
                    window.location.href = `${window.location.origin}/success.html?reference=${data.reference}`;
                },
                onCancel: () => {
                    console.log('Payment cancelled');
                    window.location.href = `${window.location.origin}/cancel.html`;
                },
                onError: (error) => {
                    console.error('Payment error:', error);
                    alert('Error en el pago: ' + error.message);
                }
            });

            return orderData;
        } catch (error) {
            console.error('Error initializing checkout:', error);
            throw error;
        }
    }

    static async validatePayment(paymentId) {
        try {
            const apiUrl = CONFIG.BOLD.ENVIRONMENT === 'production' 
                ? 'https://api.bold.co/v1'
                : 'https://sandbox.bold.co/v1';

            const response = await fetch(`${apiUrl}/payments/${paymentId}`, {
                headers: {
                    'Authorization': `Bearer ${CONFIG.BOLD.SECRET_KEY}`
                }
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Error al validar el pago');
            }

            // Limpiar sesión si el pago está completado
            if (data.status === 'approved') {
                localStorage.removeItem('boldPaymentSession');
            }

            return data;
        } catch (error) {
            console.error('Error in validatePayment:', error);
            throw new Error('Error al verificar el estado del pago');
        }
    }

    static async getPaymentStatus(reference) {
        try {
            const apiUrl = CONFIG.BOLD.ENVIRONMENT === 'production' 
                ? 'https://api.bold.co/v1'
                : 'https://sandbox.bold.co/v1';

            const response = await fetch(`${apiUrl}/payments/by-reference/${reference}`, {
                headers: {
                    'Authorization': `Bearer ${CONFIG.BOLD.SECRET_KEY}`
                }
            });

            if (!response.ok) {
                throw new Error('Error al consultar el estado del pago');
            }

            return await response.json();
        } catch (error) {
            console.error('Error in getPaymentStatus:', error);
            throw new Error('Error al consultar el estado del pago');
        }
    }
}

export default BoldPayments;