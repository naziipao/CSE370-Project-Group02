const mysql = require('mysql2');

const db = mysql.createConnection({
    host: 'localhost',
    user: 'root',      // Default XAMPP username
    password: '',      // Default XAMPP password is empty
    database: 'smart_recycling' 
});

db.connect((err) => {
    if (err) {
        console.error('Database connection failed: ' + err.stack);
        return;
    }
    console.log('Connected to XAMPP MySQL database successfully.');
});

module.exports = db;