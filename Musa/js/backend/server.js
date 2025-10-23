const express = require('express');
const bodyParser = require('body-parser');
const CryptoJS = require('crypto-js');

const app = express();
app.use(bodyParser.json());

app.post('/api/bold/generate-signature', (req, res) => {
    const { orderId, amount, currency } = req.body;
    const message = `${orderId}${amount}${currency}`;
    const signature = CryptoJS.HmacSHA256(message, 'TU_API_SECRET').toString();
    
    res.json({ success: true, hash: signature });
});

app.listen(3001, () => console.log('Backend running on port 3001'));