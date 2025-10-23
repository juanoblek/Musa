const express = require('express');
const crypto = require('crypto');
const router = express.Router();

router.post('/generate-signature', (req, res) => {
  try {
    const { orderId, amount, currency } = req.body;
    
    const concatenated = `${orderId}${amount}${currency}${process.env.BOLD_SECRET_KEY}`;
    
    const hash = crypto.createHash('sha256')
                       .update(concatenated)
                       .digest('hex');

    res.json({ success: true, hash });
    
  } catch (error) {
    console.error('Error generando firma:', error);
    res.status(500).json({ 
      success: false,
      error: 'Error interno del servidor'
    });
  }
});

module.exports = router;