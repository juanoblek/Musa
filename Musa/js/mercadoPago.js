// Mercado Pago Integration - PRODUCCIÓN
const MP_PUBLIC_KEY = 'APP_USR-5afce1ba-5244-42d4-939e-f9979851577'; // Clave de PRODUCCIÓN
const API_BASE = window.location.hostname === 'localhost' 
    ? 'http://localhost/Musa/api' 
    : 'https://musaarion.com/api';

// Initialize Mercado Pago
const mp = new MercadoPago(MP_PUBLIC_KEY, {
    locale: 'es-CO'
});

class MercadoPagoCheckout {
    constructor() {
        this.cart = [];
        this.customer = {};
        this.shippingAddress = {};
    }

    // Add item to cart
    addToCart(item) {
        const existingItem = this.cart.find(cartItem => 
            cartItem.id === item.id && 
            cartItem.color === item.color && 
            cartItem.size === item.size
        );

        if (existingItem) {
            existingItem.quantity += item.quantity;
        } else {
            this.cart.push({
                id: item.id,
                product_id: item.product_id,
                title: item.title,
                sku: item.sku || '',
                unit_price: parseFloat(item.unit_price),
                quantity: parseInt(item.quantity),
                color: item.color || '',
                size: item.size || '',
                image: item.image || ''
            });
        }

        this.updateCartDisplay();
        this.saveCart();
    }

    // Remove item from cart
    removeFromCart(itemIndex) {
        this.cart.splice(itemIndex, 1);
        this.updateCartDisplay();
        this.saveCart();
    }

    // Procesar pago y crear pedido
    async processPayment(customerData, shippingData) {
        try {
            console.log('🚀 Procesando pago y creando pedido...');

            // Crear pedido usando el sistema del carrito principal
            if (window.carrito && typeof window.carrito.crearPedido === 'function') {
                const order = window.carrito.crearPedido(customerData, shippingData, 'mercado_pago');
                
                console.log('📦 Pedido creado:', order.id);
                
                // Simular procesamiento de pago de MercadoPago
                await this.simulatePayment(order);
                
                return order;
            } else {
                throw new Error('Sistema de carrito no disponible');
            }
        } catch (error) {
            console.error('❌ Error procesando pago:', error);
            throw error;
        }
    }

    // Simular procesamiento de pago (en producción sería real)
    async simulatePayment(order) {
        return new Promise((resolve) => {
            setTimeout(() => {
                // Actualizar estado del pedido
                const orders = JSON.parse(localStorage.getItem('orders')) || [];
                const orderIndex = orders.findIndex(o => o.id === order.id);
                
                if (orderIndex !== -1) {
                    orders[orderIndex].payment_status = 'approved';
                    orders[orderIndex].payment_id = 'MP_' + Date.now();
                    orders[orderIndex].status = 'processing';
                    orders[orderIndex].updated_at = Date.now();
                    
                    localStorage.setItem('orders', JSON.stringify(orders));
                    
                    console.log('✅ Pago simulado exitosamente');
                }
                
                resolve();
            }, 2000); // Simular 2 segundos de procesamiento
        });
    }

    // Update cart quantity
    updateCartQuantity(itemIndex, newQuantity) {
        if (newQuantity <= 0) {
            this.removeFromCart(itemIndex);
        } else {
            this.cart[itemIndex].quantity = parseInt(newQuantity);
            this.updateCartDisplay();
            this.saveCart();
        }
    }

    // Calculate totals
    calculateTotals() {
        const subtotal = this.cart.reduce((total, item) => 
            total + (item.unit_price * item.quantity), 0
        );
        
        const shipping = 0; // ENVÍO SIEMPRE GRATIS - NO SUMAR AL TOTAL
        const total = subtotal; // TOTAL = SUBTOTAL (SIN ENVÍO)

        return { subtotal, shipping, total };
    }

    // Calculate shipping cost
    calculateShipping() {
        // ENVÍO SIEMPRE GRATIS
        return 0;
    }

    // Set customer information
    setCustomer(customerData) {
        this.customer = {
            name: customerData.name,
            surname: customerData.surname || '',
            email: customerData.email,
            phone: customerData.phone || '',
            phone_area_code: '57'
        };
    }

    // Set shipping address
    setShippingAddress(addressData) {
        this.shippingAddress = {
            street: addressData.street,
            number: addressData.number || '',
            city: addressData.city,
            state: addressData.state,
            department: addressData.department,
            zip_code: addressData.zip_code
        };
    }

    // Create payment preference
    async createPayment() {
        try {
            if (this.cart.length === 0) {
                throw new Error('El carrito está vacío');
            }

            if (!this.customer.email || !this.customer.name) {
                throw new Error('Información del cliente incompleta');
            }

            const totals = this.calculateTotals();

            const paymentData = {
                items: this.cart,
                customer: this.customer,
                shipping_address: this.shippingAddress,
                shipping_cost: totals.shipping
            };

            const response = await fetch(`${API_BASE}/payments/create-preference`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(paymentData)
            });

            const result = await response.json();

            if (result.success) {
                // Redirect to Mercado Pago
                window.location.href = result.init_point;
            } else {
                throw new Error(result.error || 'Error al crear la preferencia de pago');
            }

        } catch (error) {
            console.error('Payment error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error en el pago',
                text: error.message
            });
        }
    }

    // Update cart display
    updateCartDisplay() {
        const cartContainer = document.getElementById('cart-items');
        const cartTotal = document.querySelector('[data-total]');
        const cartSubtotal = document.querySelector('[data-subtotal]');
        const cartCount = document.querySelector('.cart-count');

        if (!cartContainer) return;

        cartContainer.innerHTML = '';

        if (this.cart.length === 0) {
            cartContainer.innerHTML = '<p class="text-center text-muted">Tu carrito está vacío</p>';
            if (cartTotal) cartTotal.textContent = '$0';
            if (cartSubtotal) cartSubtotal.textContent = '$0';
            if (cartCount) cartCount.textContent = '0';
            return;
        }

        this.cart.forEach((item, index) => {
            const itemElement = document.createElement('div');
            itemElement.className = 'cart-item d-flex align-items-center mb-3 p-3 border rounded';
            itemElement.innerHTML = `
                <div class="cart-item-image me-3">
                    ${item.image ? 
                        `<img src="${item.image}" alt="${item.title}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">` :
                        '<div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-image text-muted"></i></div>'
                    }
                </div>
                <div class="cart-item-details flex-grow-1">
                    <h6 class="mb-1">${item.title}</h6>
                    ${item.color ? `<small class="text-muted">Color: ${item.color}</small><br>` : ''}
                    ${item.size ? `<small class="text-muted">Talla: ${item.size}</small><br>` : ''}
                    <small class="text-muted">Precio: $${item.unit_price.toLocaleString()}</small>
                </div>
                <div class="cart-item-quantity mx-3">
                    <div class="input-group" style="width: 120px;">
                        <button class="btn btn-outline-secondary btn-sm" onclick="mpCheckout.updateCartQuantity(${index}, ${item.quantity - 1})">-</button>
                        <input type="number" class="form-control text-center" value="${item.quantity}" onchange="mpCheckout.updateCartQuantity(${index}, this.value)">
                        <button class="btn btn-outline-secondary btn-sm" onclick="mpCheckout.updateCartQuantity(${index}, ${item.quantity + 1})">+</button>
                    </div>
                </div>
                <div class="cart-item-total mx-3">
                    <strong>$${(item.unit_price * item.quantity).toLocaleString()}</strong>
                </div>
                <div class="cart-item-remove">
                    <button class="btn btn-outline-danger btn-sm" onclick="mpCheckout.removeFromCart(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            cartContainer.appendChild(itemElement);
        });

        // Update totals
        const totals = this.calculateTotals();
        if (cartSubtotal) cartSubtotal.textContent = `$${totals.subtotal.toLocaleString()}`;
        if (cartTotal) cartTotal.textContent = `$${totals.total.toLocaleString()}`;
        if (cartCount) cartCount.textContent = this.cart.reduce((total, item) => total + item.quantity, 0);

        // Update shipping info
        const shippingInfo = document.getElementById('shipping-info');
        if (shippingInfo) {
            if (totals.shipping === 0) {
                shippingInfo.innerHTML = '<span class="text-success">¡Envío gratis!</span>';
            } else {
                shippingInfo.innerHTML = `Envío: $${totals.shipping.toLocaleString()}`;
            }
        }
    }

    // Save cart to localStorage
    saveCart() {
        localStorage.setItem('shopping_cart', JSON.stringify(this.cart));
    }

    // Load cart from localStorage
    loadCart() {
        const savedCart = localStorage.getItem('shopping_cart');
        if (savedCart) {
            this.cart = JSON.parse(savedCart);
            this.updateCartDisplay();
        }
    }

    // Clear cart
    clearCart() {
        this.cart = [];
        this.updateCartDisplay();
        this.saveCart();
    }
}

// Initialize Mercado Pago checkout
const mpCheckout = new MercadoPagoCheckout();

// Load cart on page load
document.addEventListener('DOMContentLoaded', function() {
    mpCheckout.loadCart();
});

// Function to add product to cart (call from product pages)
function addToCart(productData) {
    mpCheckout.addToCart(productData);
    
    Swal.fire({
        icon: 'success',
        title: 'Producto agregado',
        text: 'El producto se agregó al carrito correctamente',
        timer: 2000,
        showConfirmButton: false
    });
}

// Function to proceed to checkout
function proceedToCheckout() {
    if (mpCheckout.cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Carrito vacío',
            text: 'Agrega productos al carrito antes de continuar'
        });
        return;
    }

    // Show shipping modal or form
    const shippingModal = new bootstrap.Modal(document.getElementById('ShippingModal'));
    shippingModal.show();
}

// Function to complete checkout with shipping info
async function completeCheckout(shippingFormData) {
    try {
        // Mostrar loading
        Swal.fire({
            title: 'Procesando pedido...',
            html: 'Por favor espera mientras procesamos tu pedido',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Preparar datos del cliente
        const customerData = {
            nombre: shippingFormData.get('nombreCompleto'),
            email: shippingFormData.get('emailCliente'),
            telefono: shippingFormData.get('telefono'),
            documento: shippingFormData.get('documento')
        };

        // Preparar datos de envío
        const shippingData = {
            direccion: shippingFormData.get('direccion'),
            ciudad: shippingFormData.get('ciudad'),
            departamento: shippingFormData.get('departamento'),
            codigoPostal: shippingFormData.get('codigoPostal'),
            notas: shippingFormData.get('notas') || ''
        };

        // Set customer and shipping information
        mpCheckout.setCustomer(customerData);
        mpCheckout.setShippingAddress(shippingData);

        // Procesar pago y crear pedido
        const order = await mpCheckout.processPayment(customerData, shippingData);

        // Mostrar éxito
        Swal.fire({
            icon: 'success',
            title: '¡Pedido creado exitosamente!',
            html: `
                <div class="text-start">
                    <p><strong>Número de pedido:</strong> ${order.id}</p>
                    <p><strong>Total:</strong> $${order.total.toLocaleString()}</p>
                    <p class="text-muted">Recibirás un email con los detalles de tu pedido.</p>
                </div>
            `,
            confirmButtonText: 'Entendido',
            allowOutsideClick: false
        }).then(() => {
            // Cerrar modales
            const modals = ['CartModal', 'ShippingModal', 'PaymentModal'];
            modals.forEach(modalId => {
                const modalElement = document.getElementById(modalId);
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) modal.hide();
                }
            });

            // Actualizar contador del carrito
            if (window.carrito) {
                window.carrito.actualizarContadorGlobal();
            }

            // Opcional: redirigir a página de confirmación
            // window.location.href = `orden-confirmada.html?order=${order.id}`;
        });

    } catch (error) {
        console.error('❌ Error en checkout:', error);
        
        Swal.fire({
            icon: 'error',
            title: 'Error al procesar el pedido',
            text: 'Ha ocurrido un error. Por favor intenta nuevamente.',
            confirmButtonText: 'Entendido'
        });
    }
}

// Export for use in other scripts
window.mpCheckout = mpCheckout;
window.addToCart = addToCart;
window.proceedToCheckout = proceedToCheckout;
window.completeCheckout = completeCheckout;
