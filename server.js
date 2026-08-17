const express = require('express');
const path = require('path');
const session = require('express-session');

const authRoutes = require('./routes/loginRoutes');
const dashboardRoutes = require('./routes/dashboardRoutes');
const voucherRoutes = require('./routes/rewardRoutes');

const app = express();
const PORT = 3000;

// Database connection
require('./config/db'); 

// Cache Control Middleware
app.use((req, res, next) => {
    res.set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    next();
});

// Session Middleware
app.use(session({
    secret: 'my_super_secret_key', 
    resave: false,
    saveUninitialized: false
}));

// Body Parsers
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve static frontend files
app.use(express.static(path.join(__dirname, 'frontend')));

// Routes
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'frontend', 'UI', 'HTML', 'login.html'));
});

app.use('/', authRoutes);
app.use('/', dashboardRoutes);
app.use('/api/vouchers', voucherRoutes);

// Start Server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});