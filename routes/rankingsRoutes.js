// File: routes/rankingsRoutes.js
const express = require('express');
const router = express.Router();
const { getRankings } = require('../controllers/rankingsController');
const { requireAuth } = require('../middleware/loginMiddleware');

// Mounted in server.js as: app.use('/api/rankings', rankingsRoutes);
router.get('/', requireAuth, getRankings);

module.exports = router;
