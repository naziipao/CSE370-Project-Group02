// File: controllers/rewardController.js
const db = require('../config/db');

const executeQuery = (query, params = []) => {
  return new Promise((resolve, reject) => {
    db.query(query, params, (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
};

const getVouchers = async (req, res) => {
  try {
    const userId = req.session?.userId || null;

    let userPoints = 0;
    if (userId) {
      try {
        const walletRows = await executeQuery('SELECT current_points FROM wallet WHERE user_id = ?', [userId]);
        if (walletRows.length > 0) userPoints = walletRows[0].current_points || 0;
      } catch (e) {
        console.warn('Wallet fetch warning:', e.message);
      }
    }

    const vouchersQuery = `
      SELECT 
        r.reward_id AS voucher_id,
        r.reward_name AS voucher_name,
        r.required_points,
        r.expiry_date,
        p.company_name
      FROM reward r
      LEFT JOIN partner_company p ON r.company_id = p.company_id
      ORDER BY r.reward_id ASC
    `;

    const vouchers = await executeQuery(vouchersQuery);

    return res.status(200).json({
      success: true,
      vouchers: vouchers,
      userPoints: userPoints
    });

  } catch (error) {
    console.error('DATABASE ERROR:', error);
    return res.status(500).json({ success: false, message: 'Database Error: ' + (error.sqlMessage || error.message) });
  }
};

const purchaseVoucher = async (req, res) => {
  try {
    const userId = req.session?.userId;
    const { voucherId } = req.body;

    if (!userId) {
      return res.status(401).json({ success: false, message: 'Please log in to obtain vouchers.' });
    }

    if (!voucherId) {
      return res.status(400).json({ success: false, message: 'Voucher ID is required.' });
    }

    // 1. Fetch voucher required points
    const voucherRows = await executeQuery('SELECT required_points FROM reward WHERE reward_id = ?', [voucherId]);
    if (voucherRows.length === 0) {
      return res.status(404).json({ success: false, message: 'Voucher not found.' });
    }
    const requiredPoints = voucherRows[0].required_points;

    // 2. Fetch user wallet balance
    const walletRows = await executeQuery('SELECT current_points FROM wallet WHERE user_id = ?', [userId]);
    if (walletRows.length === 0) {
      return res.status(404).json({ success: false, message: 'Wallet record not found.' });
    }
    const currentPoints = walletRows[0].current_points;

    // 3. Check for sufficient points balance
    if (currentPoints < requiredPoints) {
      return res.status(400).json({ 
        success: false, 
        message: `Insufficient points. Required: ${requiredPoints}, You have: ${currentPoints}.` 
      });
    }

    // 4. Deduct points from wallet
    await executeQuery('UPDATE wallet SET current_points = current_points - ? WHERE user_id = ?', [requiredPoints, userId]);

    const updatedPoints = currentPoints - requiredPoints;

    return res.status(200).json({
      success: true,
      message: 'Voucher obtained successfully!',
      newBalance: updatedPoints
    });

  } catch (error) {
    console.error('REDEEM ERROR:', error);
    return res.status(500).json({ success: false, message: 'Database Error: ' + (error.sqlMessage || error.message) });
  }
};

module.exports = { getVouchers, purchaseVoucher };