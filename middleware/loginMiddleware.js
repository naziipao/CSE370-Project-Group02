// File: middleware/loginMiddleware.js

const requireAuth = (req, res, next) => {
    // Check if a user session exists
    if (req.session && req.session.userId) {
        // User is logged in, allow them to proceed
        return next();
    } else {
        // User is not logged in, send an unauthorized error
        return res.status(401).json({ success: false, message: 'Unauthorized. Please log in.' });
    }
};

module.exports = { requireAuth };