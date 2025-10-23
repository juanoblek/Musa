const express = require('express');
const crypto = require('crypto');
const router = express.Router();
const config = require('../config');

router.post('/generate-signature', (req, res) => {
    try {
        const { id, amount, currency } = req.body;
        
        if (!id || !amount || !currency) {
            return res.status(400).json({
                error: 'Missing required fields'
            });
        }

        // Concatenate the values with a dot
        const data = `${id}.${amount}.${currency}`;
        
        // Create HMAC using SHA256 and the API Secret
        const hmac = crypto.createHmac('sha256', config.BOLD.SECRET_KEY);
        hmac.update(data);
        
        // Get the signature in hex format
        const hash = hmac.digest('hex');

        res.json({ hash });
    } catch (error) {
        console.error('Error generating signature:', error);
        res.status(500).json({
            error: 'Error generating signature'
        });
    }
});

module.exports = router;
