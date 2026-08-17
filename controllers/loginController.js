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
    
    // 1. Add a console.log so we can see it working in the terminal
    console.log(`Login attempt for: ${email}`); 

    const query = 'SELECT * FROM `User` WHERE Email = ? AND Pin = ?';
    
    db.query(query, [email, password], (err, results) => {
        if (err) {
            console.error('Login Error:', err);
            return res.send('<h2>An error occurred during login.</h2><a href="/">Go Back</a>');
        }

        if (results.length > 0) {
            const user = results[0];
            
            // 2. Add another log to confirm the user was found
            console.log(`User found: ${user.FirstName}. Saving session...`);
            
            req.session.userId = user.User_id; 
            
            // 3. FORCE THE SESSION TO SAVE BEFORE REDIRECTING
            req.session.save((err) => {
                if (err) {
                    console.error('Session save error:', err);
                }
                console.log('Session saved successfully. Redirecting to dashboard...');
                res.redirect('/UI/HTML/dashboard.html');
            });

        } else {
            console.log('Login failed: Invalid credentials.');
            res.send('<h2>Invalid email or password.</h2><a href="/">Try Again</a>');
        }
    });
};



exports.logoutUser = (req, res) => {
    // 1. Destroy the session
    req.session.destroy((err) => {
        if (err) {
            console.error('Logout Error:', err);
            return res.status(500).send('<h2>Error logging out.</h2><a href="/UI/HTML/dashboard.html">Go Back</a>');
        }
        
        // 2. Clear the session cookie from the user's browser
        res.clearCookie('connect.sid'); 
        
        // 3. Redirect back to the login page
        console.log('User logged out successfully.');
        res.redirect('/');
    });
};