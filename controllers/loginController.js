const db = require('../config/db');

exports.signupUser = (req, res) => {
    const { role, first_name, last_name, email, phone, password } = req.body;
    const userId = Math.floor(100000000 + Math.random() * 900000000); 

    const userQuery = 'INSERT INTO `User` (User_id, FirstName, LastName, Email, Pin) VALUES (?, ?, ?, ?, ?)';
    const userValues = [userId, first_name, last_name, email, password];

    db.query(userQuery, userValues, (err) => {
        if (err) {
            if (err.code === 'ER_DUP_ENTRY') {
                return res.send('<h2>Error: This email is already registered.</h2><a href="/">Go Back</a>');
            }
            console.error('Error inserting User:', err);
            return res.send('<h2>An error occurred creating the user.</h2><a href="/">Go Back</a>');
        }

        const phoneQuery = 'INSERT INTO Phone (User_id, Phone) VALUES (?, ?)';
        
        db.query(phoneQuery, [userId, phone], (err) => {
            if (err) {
                console.error('Error inserting Phone:', err);
                return res.send('<h2>User created, but failed to save phone number.</h2><a href="/">Go Back</a>');
            }

            let roleQuery = role === 'Student' 
                ? 'INSERT INTO Student (User_id) VALUES (?)' 
                : 'INSERT INTO Non_Student (User_id) VALUES (?)';

            db.query(roleQuery, [userId], (err) => {
                if (err) {
                    console.error('Error inserting Role:', err);
                    return res.send('<h2>User created, but failed to assign role.</h2><a href="/">Go Back</a>');
                }

                res.send('<h2>Account created successfully!</h2><a href="/">Click here to Log In</a>');
            });
        });
    });
};

exports.loginUser = (req, res) => {
    const { email, password } = req.body;

    const query = 'SELECT * FROM `User` WHERE Email = ? AND Pin = ?';
    
    db.query(query, [email, password], (err, results) => {
        if (err) {
            console.error('Login Error:', err);
            return res.send('<h2>An error occurred during login.</h2><a href="/">Go Back</a>');
        }

        if (results.length > 0) {
            const user = results[0];
            res.send(`<h2>Welcome back, ${user.FirstName} ${user.LastName}!</h2><p>Login successful.</p>`);
        } else {
            res.send('<h2>Invalid email or password.</h2><a href="/">Try Again</a>');
        }
    });
};