import BoldPayments from './boldPayments.js';
import CONFIG from './config.js';

// Cart array to store items
let cart = [];

// Function to add item to cart
function addToCart(item) {
    cart.push(item);
    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

// Function to generate unique reference
function generateReference() {
    return `PEDIDO_${Date.now()}_${Math.floor(Math.random() * 1000)}`;
}

// Function to calculate cart total
function calculateTotal() {
    return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
}

// Function to checkout
async function checkout() {
    try {
        if (cart.length === 0) {
            Swal.fire('Error', 'El carrito está vacío', 'error');
            return;
        }

        // Mostrar spinner
        document.getElementById('checkoutSpinner').classList.remove('d-none');
        document.getElementById('checkout-form').classList.add('d-none');

        const orderData = {
            total: calculateTotal(),
            reference: generateReference(),
            customer: {
                email: document.getElementById('customer-email').value,
                full_name: document.getElementById('customer-name').value
            },
            items: cart
        };

        // Crear el pago en Bold
        const paymentUrl = await BoldPayments.createPayment(orderData);
        
        // Guardar datos del pedido en localStorage
        localStorage.setItem('currentOrder', JSON.stringify({
            reference: orderData.reference,
            items: cart,
            total: orderData.total,
            customer: orderData.customer
        }));

        // Redirigir a la página de pago de Bold
        window.location.href = paymentUrl;

    } catch (error) {
        console.error('Error en checkout:', error);
        Swal.fire('Error', 'Hubo un problema al procesar el pago. Por favor intente nuevamente.', 'error');
        
        // Ocultar spinner y mostrar formulario
        document.getElementById('checkoutSpinner').classList.add('d-none');
        document.getElementById('checkout-form').classList.remove('d-none');
    }
}

// Function to render cart
function renderCart() {
    const cartContainer = document.getElementById('cartItems');
    if (!cartContainer) return;

    cartContainer.innerHTML = cart.map((item, index) => `
        <div class="cart-item p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="${item.image}" alt="${item.name}" class="img-producto me-3">
                    <div>
                        <h6 class="mb-0">${item.name}</h6>
                        <small class="text-muted">
                            Talla: ${item.size} | Color: ${item.color}
                        </small>
                        <div class="quantity-selector mt-2">
                            <button class="btn btn-sm btn-outline-secondary" 
                                    onclick="updateQuantity(${index}, ${item.quantity - 1})">-</button>
                            <span class="mx-2">${item.quantity}</span>
                            <button class="btn btn-sm btn-outline-secondary" 
                                    onclick="updateQuantity(${index}, ${item.quantity + 1})">+</button>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold">$${formatPrice(item.price * item.quantity)}</div>
                    <button class="btn btn-sm btn-link text-danger" 
                            onclick="removeFromCart(${index})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');

    // updateCartTotal(); // DESHABILITADO - usando cart-system-simple.js
}

// Function to update cart total - DESHABILITADA PARA EVITAR CONFLICTOS
function updateCartTotal() {
    console.log('⚠️ [CUSTOM.JS] updateCartTotal() DESHABILITADA - usando cart-system-simple.js');
    // FUNCIÓN DESHABILITADA PARA PREVENIR CONFLICTOS CON CART-SYSTEM-SIMPLE.JS
    // El sistema principal está en cart-system-simple.js con correcciones de envío
    return;
    
    // CÓDIGO ORIGINAL COMENTADO PARA EVITAR INTERFERENCIAS
    /*
    const subtotal = calculateTotal();
    const shipping = 0; // ENVÍO SIEMPRE GRATIS - FORZADO
    const tax = 0;      // SIN IMPUESTOS - FORZADO  
    const total = subtotal; // TOTAL = SUBTOTAL (sin envío ni impuestos)

    document.querySelectorAll('[data-subtotal]').forEach(el => {
        el.textContent = `$${formatPrice(subtotal)}`;
    });
    
    document.querySelectorAll('[data-shipping]').forEach(el => {
        el.textContent = shipping === 0 ? 'Gratis' : `$${formatPrice(shipping)}`;
    });
    
    document.querySelectorAll('[data-tax]').forEach(el => {
        el.textContent = `$${formatPrice(tax)}`;
    });
    
    document.querySelectorAll('[data-total]').forEach(el => {
        el.textContent = `$${formatPrice(total)}`;
    });
    */
}

// Function to update cart count
function updateCartCount() {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    document.querySelectorAll('.cart-badge').forEach(badge => {
        badge.textContent = count;
    });
}

// Function to remove item from cart
function removeFromCart(index) {
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

// Function to update item quantity
function updateQuantity(index, newQuantity) {
    if (newQuantity < 1 || newQuantity > CONFIG.VALIDATORS.MAX_QUANTITY) return;
    
    cart[index].quantity = newQuantity;
    localStorage.setItem('cart', JSON.stringify(cart));
    renderCart();
    updateCartCount();
}

// Format price helper
function formatPrice(value) {
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value);
}

// SYSTEM DISABLED - Using cart-system-simple.js instead
// Add event listeners
/*
document.addEventListener('DOMContentLoaded', () => {
    // Recuperar carrito del localStorage si existe
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
        cart = JSON.parse(savedCart);
        renderCart();
        updateCartCount();
    }

    // Event listener para el formulario de checkout
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await checkout();
        });
    }
});
*/

// Exportar funciones necesarias
export { addToCart, removeFromCart, updateQuantity, checkout };
