const { verifyBoldSignature, handleWebhookEvent } = require('../js/webhookHandler');

exports.handler = async function(event, context) {
    // Only allow POST requests
    if (event.httpMethod !== 'POST') {
        return {
            statusCode: 405,
            body: JSON.stringify({ error: 'Method not allowed' })
        };
    }

    try {
        // Verify Bold signature
        const signature = event.headers['bold-signature'];
        if (!signature || !verifyBoldSignature(signature, event.body)) {
            return {
                statusCode: 401,
                body: JSON.stringify({ error: 'Invalid signature' })
            };
        }

        // Parse webhook data
        const webhookData = JSON.parse(event.body);
        
        // Handle the webhook event
        await handleWebhookEvent(webhookData);

        return {
            statusCode: 200,
            body: JSON.stringify({ received: true })
        };
    } catch (error) {
        console.error('Webhook error:', error);
        return {
            statusCode: 400,
            body: JSON.stringify({ error: 'Webhook error' })
        };
    }
};