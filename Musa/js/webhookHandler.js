import CONFIG from './config.js';

// Webhook signature verification
function verifyBoldSignature(signature, bodyRaw) {
    const crypto = require('crypto');
    const hmac = crypto.createHmac('sha256', CONFIG.BOLD.WEBHOOK_SECRET);
    hmac.update(bodyRaw);
    const calculatedSignature = hmac.digest('hex');
    return signature === calculatedSignature;
}

// Handle different webhook event types
async function handleWebhookEvent(event) {
    switch (event.type) {
        case 'payment.success':
            await handlePaymentSuccess(event.data);
            break;
        case 'payment.failed':
            await handlePaymentFailure(event.data);
            break;
        case 'payment.refunded':
            await handlePaymentRefund(event.data);
            break;
        default:
            console.log('Unhandled event type:', event.type);
    }
}

// Handle successful payment
async function handlePaymentSuccess(paymentData) {
    try {
        // Update order status in your database
        await updateOrderStatus(paymentData.reference, 'paid');
        
        // Send confirmation email to customer
        await sendPaymentConfirmation(paymentData);
        
        console.log('Payment success handled:', paymentData.reference);
    } catch (error) {
        console.error('Error handling payment success:', error);
        throw error;
    }
}

// Handle failed payment
async function handlePaymentFailure(paymentData) {
    try {
        // Update order status
        await updateOrderStatus(paymentData.reference, 'failed');
        
        // Notify customer of failure
        await sendPaymentFailureNotification(paymentData);
        
        console.log('Payment failure handled:', paymentData.reference);
    } catch (error) {
        console.error('Error handling payment failure:', error);
        throw error;
    }
}

// Handle refunded payment
async function handlePaymentRefund(paymentData) {
    try {
        // Update order status
        await updateOrderStatus(paymentData.reference, 'refunded');
        
        // Send refund confirmation to customer
        await sendRefundConfirmation(paymentData);
        
        console.log('Payment refund handled:', paymentData.reference);
    } catch (error) {
        console.error('Error handling payment refund:', error);
        throw error;
    }
}

// Utility functions (implement these based on your backend setup)
async function updateOrderStatus(reference, status) {
    // Implement order status update in your database
    console.log(`Order ${reference} status updated to ${status}`);
}

async function sendPaymentConfirmation(paymentData) {
    // Implement email sending logic
    console.log(`Payment confirmation email sent for order ${paymentData.reference}`);
}

async function sendPaymentFailureNotification(paymentData) {
    // Implement notification logic
    console.log(`Payment failure notification sent for order ${paymentData.reference}`);
}

async function sendRefundConfirmation(paymentData) {
    // Implement refund notification logic
    console.log(`Refund confirmation sent for order ${paymentData.reference}`);
}

export { verifyBoldSignature, handleWebhookEvent };