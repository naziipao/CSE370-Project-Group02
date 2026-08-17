const express = require('express');
const router = express.Router();
const dashboardController = require('../controllers/dashboardController');
const { requireAuth } = require('../middleware/loginMiddleware');

// Define the API route for the frontend to call
router.get('/api/dashboard', requireAuth, dashboardController.getDashboardData);

module.exports = router;