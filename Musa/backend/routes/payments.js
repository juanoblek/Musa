const express = require('express');
const { MercadoPagoConfig, Preference, Payment } = require('mercadopago');
const db = require('../config/database');
const { v4: uuidv4 } = require('uuid');
const router = express.Router();

// Initialize Mercado Pago
const client = new MercadoPagoConfig({
    accessToken: process.env.MP_ACCESS_TOKEN,
});

const preference = new Preference(client);
const payment = new Payment(client);

// Create payment preference
router.post('/create-preference', async (req, res) => {
    try {
        const { 
            items, 
            customer, 
            shipping_address,
            shipping_cost = 0
        } = req.body;

        if (!items || !Array.isArray(items) || items.length === 0) {
            return res.status(400).json({ error: 'Items are required' });
        }

        if (!customer || !customer.email || !customer.name) {
            return res.status(400).json({ error: 'Customer information is required' });
        }

        // Generate order number
        const orderNumber = `ORD-${Date.now()}-${Math.random().toString(36).substr(2, 5).toUpperCase()}`;

        // Calculate totals
        let subtotal = 0;
        const processedItems = [];

        for (const item of items) {
            const itemTotal = parseFloat(item.unit_price) * parseInt(item.quantity);
            subtotal += itemTotal;

            processedItems.push({
                id: item.id.toString(),
                title: item.title,
                quantity: parseInt(item.quantity),
                unit_price: parseFloat(item.unit_price),
                currency_id: 'COP'
            });
        }

        const totalAmount = subtotal; // NO SUMAR ENVÍO - SIEMPRE GRATIS

        // Add shipping as an item if there's shipping cost
        if (shipping_cost > 0) {
            processedItems.push({
                id: 'shipping',
                title: 'Envío',
                quantity: 1,
                unit_price: parseFloat(shipping_cost),
                currency_id: 'COP'
            });
        }

        // Create preference
        const preferenceData = {
            items: processedItems,
            payer: {
                name: customer.name,
                surname: customer.surname || '',
                email: customer.email,
                phone: {
                    area_code: customer.phone_area_code || '57',
                    number: customer.phone || ''
                },
                address: shipping_address ? {
                    street_name: shipping_address.street || '',
                    street_number: shipping_address.number || '',
                    zip_code: shipping_address.zip_code || ''
                } : undefined
            },
            external_reference: orderNumber,
            notification_url: `${process.env.BACKEND_URL || 'http://localhost:3001'}/api/payments/webhook`,
            back_urls: {
                success: `${process.env.FRONTEND_URL || 'http://localhost:3000'}/success`,
                failure: `${process.env.FRONTEND_URL || 'http://localhost:3000'}/failure`,
                pending: `${process.env.FRONTEND_URL || 'http://localhost:3000'}/pending`
            },
            auto_return: 'approved',
            payment_methods: {
                excluded_payment_methods: [],
                excluded_payment_types: [],
                installments: 12
            },
            shipments: {
                mode: 'not_specified'
            }
        };

        const response = await preference.create({ body: preferenceData });

        // Save order to database with pending status
        const orderResult = await db.query(`
            INSERT INTO orders (
                order_number, customer_email, customer_name, customer_phone,
                shipping_address, city, state, zip_code,
                subtotal, shipping_cost, total_amount,
                payment_method, payment_status, order_status
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)
            RETURNING id
        `, [
            orderNumber,
            customer.email,
            customer.name,
            customer.phone || '',
            shipping_address ? `${shipping_address.street} ${shipping_address.number}` : '',
            shipping_address?.city || '',
            shipping_address?.state || '',
            shipping_address?.zip_code || '',
            subtotal,
            shipping_cost,
            totalAmount,
            'mercadopago',
            'pending',
            'pending'
        ]);

        const orderId = orderResult.rows[0].id;

        // Save order items
        for (const item of items) {
            await db.query(`
                INSERT INTO order_items (
                    order_id, product_id, product_name, product_sku,
                    color_name, size_name, quantity, unit_price, total_price
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
            `, [
                orderId,
                item.product_id || null,
                item.title,
                item.sku || '',
                item.color || '',
                item.size || '',
                item.quantity,
                item.unit_price,
                parseFloat(item.unit_price) * parseInt(item.quantity)
            ]);
        }

        res.json({
            success: true,
            preference_id: response.id,
            init_point: response.init_point,
            sandbox_init_point: response.sandbox_init_point,
            order_number: orderNumber,
            total_amount: totalAmount
        });

    } catch (error) {
        console.error('Create preference error:', error);
        res.status(500).json({ 
            error: 'Error creating payment preference',
            details: error.message
        });
    }
});

// Webhook for payment notifications
router.post('/webhook', async (req, res) => {
    try {
        const { type, data } = req.body;

        if (type === 'payment') {
            const paymentId = data.id;
            
            // Get payment details from Mercado Pago
            const paymentInfo = await payment.get({ id: paymentId });
            
            const externalReference = paymentInfo.external_reference;
            const paymentStatus = paymentInfo.status;
            const paymentStatusDetail = paymentInfo.status_detail;
            
            // Update order in database
            let orderStatus = 'pending';
            
            switch (paymentStatus) {
                case 'approved':
                    orderStatus = 'confirmed';
                    break;
                case 'rejected':
                    orderStatus = 'cancelled';
                    break;
                case 'cancelled':
                    orderStatus = 'cancelled';
                    break;
                case 'in_process':
                    orderStatus = 'pending';
                    break;
            }

            await db.query(`
                UPDATE orders 
                SET payment_status = $1, order_status = $2, payment_id = $3, updated_at = CURRENT_TIMESTAMP
                WHERE order_number = $4
            `, [paymentStatus, orderStatus, paymentId, externalReference]);

            console.log(`Order ${externalReference} updated: payment_status=${paymentStatus}, order_status=${orderStatus}`);
        }

        res.status(200).send('OK');

    } catch (error) {
        console.error('Webhook error:', error);
        res.status(500).send('Error');
    }
});

// Get payment status
router.get('/status/:orderNumber', async (req, res) => {
    try {
        const { orderNumber } = req.params;

        const result = await db.query(`
            SELECT order_number, payment_status, order_status, total_amount, created_at
            FROM orders 
            WHERE order_number = $1
        `, [orderNumber]);

        if (result.rows.length === 0) {
            return res.status(404).json({ error: 'Order not found' });
        }

        res.json({
            success: true,
            order: result.rows[0]
        });

    } catch (error) {
        console.error('Get payment status error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router;
