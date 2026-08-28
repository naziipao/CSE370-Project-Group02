<?php
/* ============================================================
   config/DBconnect.php      (replaces config/db.js)

   Creates one PDO connection called $pdo.
   Every page includes this file at the top.
   ============================================================ */

// Show errors while developing.
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host   = "localhost";
$user   = "root";        // default XAMPP username
$pass   = "";            // default XAMPP password is empty
$dbname = "smart_recycling";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass
    );

    // Throw an exception when a query fails, instead of failing silently.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Return rows as simple arrays: $row['FirstName']
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die("Database connection failed: " . $e->getMessage());
}