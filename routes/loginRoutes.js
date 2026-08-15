const express = require('express');
const router = express.Router();
const loginController = require('../controllers/loginController');

// 1. Handle Signup Form Submission
router.post('/signup', loginController.signupUser);

// 2. Handle Login Form Submission
router.post('/login', loginController.loginUser);

module.exports = router;