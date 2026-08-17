// File: routes/rewardRoutes.js
const express = require('express');
const router = express.Router();
const { getVouchers, purchaseVoucher } = require('../controllers/rewardController');
const { requireAuth } = require('../middleware/loginMiddleware');

router.get('/', requireAuth, getVouchers);
router.post('/purchase', requireAuth, purchaseVoucher);

module.exports = router;