<?php
/* ============================================================
   index.php
   The first page Apache serves for the project folder.
   Replaces the app.get('/') route from server.js
   ============================================================ */

require_once "config/auth.php";

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
