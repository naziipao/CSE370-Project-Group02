<?php
/* ============================================================
   config/auth.php          (replaces middleware/loginMiddleware.js)

   Handles the session for BOTH kinds of account:

     - a normal user uses  $_SESSION['user_id']
     - a recycler uses     $_SESSION['recycler_id']

   Only one of the two is ever set, because login.php decides
   which table the email was found in.
   ============================================================ */


// How long anyone may sit idle before being logged out, in seconds.
// 1800 = 30 minutes. Change this one number to adjust the timeout.
define('SESSION_TIMEOUT', 1800);


if (session_status() === PHP_SESSION_NONE) {

    // PHP deletes idle session files after 24 minutes by default,
    // which would log people out before our own timeout fires.
    ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);

    // 0 = the session cookie dies when the browser closes.
    session_set_cookie_params(0);

    session_start();
}


/* ---------- Stop the browser caching logged-in pages ----------
   Replaces the Cache-Control middleware from server.js. Without
   it, pressing Back after logging out shows the old page again
   from the browser cache, even though the session is gone.      */

header("Cache-Control: no-store, no-cache, must-revalidate, private");
header("Pragma: no-cache");
header("Expires: 0");


/* ---------- Automatic logout after inactivity ---------- */

$this_page  = basename($_SERVER['PHP_SELF']);
$logged_in  = isset($_SESSION['user_id'])
           || isset($_SESSION['recycler_id'])
           || isset($_SESSION['manager_id']);

if ($logged_in && $this_page != 'login.php') {

    // First page after logging in: start the clock instead of
    // comparing against a value that does not exist yet.
    if (!isset($_SESSION['last_active'])) {
        $_SESSION['last_active'] = time();
    }

    if (time() - $_SESSION['last_active'] > SESSION_TIMEOUT) {

        // Idle too long. Throw everything away.
        $_SESSION = [];

        // A fresh session ID, so the message below still has
        // somewhere to live.
        session_regenerate_id(true);

        $_SESSION['flash'] = "You were logged out due to inactivity.";

        header("Location: login.php");
        exit;
    }

    // Still active, so reset the countdown.
    $_SESSION['last_active'] = time();
}


/* ---------- Helper functions ---------- */


/**
 * For pages only normal users may see:
 * dashboard, profile, deposit, rewards, rankings, pickup.
 */
function require_login() {

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}


/**
 * For pages only recyclers may see: recycler_requests.php.
 * A logged-in user is NOT a recycler, so they get bounced too.
 */
function require_recycler() {

    if (!isset($_SESSION['recycler_id'])) {
        header("Location: login.php");
        exit;
    }
}


/**
 * For pages only collection center managers may see: center_manager.php.
 * Same idea as require_recycler — a normal user or recycler is bounced.
 */
function require_manager() {

    if (!isset($_SESSION['manager_id'])) {
        header("Location: login.php");
        exit;
    }
}


// Saves a message to show on the next page load.
function set_flash($message) {
    $_SESSION['flash'] = $message;
}


// Prints the saved message once, then deletes it.
function show_flash() {

    if (isset($_SESSION['flash'])) {
        echo "<p class='form-message'>"
             . htmlspecialchars($_SESSION['flash'])
             . "</p>";
        unset($_SESSION['flash']);
    }
}
