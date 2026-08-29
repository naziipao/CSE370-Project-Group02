<?php
/* ============================================================
   logout.php               (replaces loginController.logoutUser)
   ============================================================ */

require_once "config/auth.php";

// Empty the session data.
$_SESSION = [];

// Delete the session cookie from the browser.
if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 3600,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session on the server.
session_destroy();

header("Location: login.php");
exit;