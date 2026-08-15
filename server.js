const express = require('express');
const path = require('path');
const authRoutes = require('./routes/loginRoutes');

// Initializes the database connection
require('./config/db'); 

const app = express();
const PORT = 3000;

// Middleware
app.use(express.urlencoded({ extended: true }));

// Serve the 'frontend' folder statically 
// (This allows HTML to automatically find /UI/style.css and /Js/login.js)
app.use(express.static(path.join(__dirname, 'frontend')));

// Routes
// 1. Serve the login page on the root URL
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'frontend', 'UI', 'HTML', 'login.html'));
});

// 2. Use the auth routes for /login and /signup endpoints
app.use('/', authRoutes);

// Start Server
app.listen(PORT, () => {
    console.log(`Server is running on http://localhost:${PORT}`);
});