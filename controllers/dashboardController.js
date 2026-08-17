// File: controllers/dashboardController.js
const db = require('../config/db');

const executeQuery = (query, params = []) => {
  return new Promise((resolve, reject) => {
    db.query(query, params, (err, results) => {
      if (err) return reject(err);
      resolve(results);
    });
  });
};

const getDashboardData = async (req, res) => {
  try {
    const userId = req.session?.userId;

    if (!userId) {
      return res.status(401).json({ success: false, message: 'User not logged in.' });
    }

    // 1. Fetch User details + Wallet points & specific user voucher count
    const userQuery = `
      SELECT 
        u.User_id,
        u.FirstName,
        u.LastName,
        u.current_badge_points,
        u.total_recycled,
        COALESCE(w.current_points, 0) AS spendable_balance,
        COALESCE(w.voucher, 0) AS user_vouchers
      FROM User u
      LEFT JOIN wallet w ON u.User_id = w.User_id
      WHERE u.User_id = ?
    `;

    const userRows = await executeQuery(userQuery, [userId]);

    if (userRows.length === 0) {
      return res.status(404).json({ success: false, message: 'User not found.' });
    }

    const row = userRows[0];
    const firstName = row.FirstName || 'User';
    const lastName = row.LastName || '';
    const fullName = `${firstName} ${lastName}`.trim();

    // 2. Calculate University Rank
    let universityRank = 'N/A';
    try {
      const studentRows = await executeQuery('SELECT institute_EIIN FROM student WHERE User_id = ? OR user_id = ?', [userId, userId]);
      
      if (studentRows.length > 0 && studentRows[0].institute_EIIN) {
        const eiin = studentRows[0].institute_EIIN;
        
        const instituteRows = await executeQuery('SELECT cumulative_points FROM edu_institute_stats WHERE institute_EIIN = ?', [eiin]);
        
        if (instituteRows.length > 0) {
          const myInstitutePoints = instituteRows[0].cumulative_points || 0;
          
          const rankRows = await executeQuery('SELECT COUNT(*) AS higher_count FROM edu_institute_stats WHERE cumulative_points > ?', [myInstitutePoints]);
          
          if (rankRows.length > 0) {
            universityRank = rankRows[0].higher_count + 1;
          }
        }
      }
    } catch (e) {
      console.warn('University rank calculation warning:', e.message);
    }

    // 3. Send response
    return res.status(200).json({
      success: true,
      userData: {
        id: row.User_id,
        name: fullName,
        firstName: firstName,
        ecoBalance: row.spendable_balance,
        badgePoints: row.current_badge_points || 0,
        totalRecycledKg: row.total_recycled || 0, 
        availableVouchers: row.user_vouchers || 0, // Now fetches directly from wallet.voucher!
        rank: universityRank 
      }
    });

  } catch (error) {
    console.error('DASHBOARD ERROR:', error);
    return res.status(500).json({ success: false, message: 'Database Error: ' + (error.sqlMessage || error.message) });
  }
};

module.exports = { getDashboardData };