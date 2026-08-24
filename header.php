<?php
/* ============================================================
   header.php  -  shared top of every logged-in page

   A page may set these before including this file:

       $page_title   = "Home Pickup";     (browser tab text)
       $page_css     = "pickup.css";      (optional)
       $auto_refresh = 15;                (optional, seconds)

   If $page_css is not set it is worked out from the file name,
   so dashboard.php loads CSS/dashboard.css automatically.

   The sidebar changes depending on who is signed in:
     - a recycler sees only their request list
     - a normal user sees the full menu
   ============================================================ */

$currentPage = basename($_SERVER['PHP_SELF']);

// dashboard.php -> dashboard.css , pickup.php -> pickup.css
if (!isset($page_css)) {
    $page_css = str_replace('.php', '.css', $currentPage);
}

if (!isset($page_title)) {
    $page_title = "Smart Circular Recycling";
}

// Which kind of account is this?
$is_recycler = isset($_SESSION['recycler_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Circular Recycling - <?= htmlspecialchars($page_title) ?></title>

  <?php if (isset($auto_refresh)): ?>
  <!-- Reloads the page by itself. This is how a pickup page
       notices a status change without any JavaScript. -->
  <meta http-equiv="refresh" content="<?= (int) $auto_refresh ?>">
  <?php endif; ?>

  <!-- shared layout: colours, sidebar, cards -->
  <link rel="stylesheet" href="CSS/main.css">

  <!-- styles for this page only -->
  <link rel="stylesheet" href="CSS/<?= htmlspecialchars($page_css) ?>">

  <!-- FontAwesome Icons -->
  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <div class="app-container">

    <!-- LEFT SIDEBAR MENU -->
    <aside class="sidebar <?= $is_recycler ? 'recycler-sidebar' : '' ?>">

      <div class="brand-header">
        <a href="<?= $is_recycler ? 'recycler_requests.php' : 'dashboard.php' ?>"
           class="brand-link">
          <span class="brand-title">Smart Circular Recycling</span>
        </a>
      </div>

      <?php if ($is_recycler): ?>

        <!-- ---------- RECYCLER MENU ---------- -->
        <nav class="nav-menu">

          <a href="recycler_requests.php"
             class="nav-item <?= ($currentPage == 'recycler_requests.php') ? 'active' : '' ?>">
            <span class="nav-icon">📋</span>
            <span class="nav-label">User Requests</span>
          </a>

        </nav>

        <div class="sidebar-footer">
          <p class="who-line">
            Signed in as<br>
            <strong><?= htmlspecialchars($_SESSION['recycler_name']) ?></strong><br>
            <span class="who-city">📍 <?= htmlspecialchars($_SESSION['recycler_city']) ?></span>
          </p>
          <a href="logout.php" class="nav-item logout">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span class="nav-label">Log Out</span>
          </a>
        </div>

      <?php else: ?>

        <!-- ---------- USER MENU ---------- -->
        <nav class="nav-menu">

          <a href="dashboard.php"
             class="nav-item <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">
            <span class="nav-icon">🏠</span>
            <span class="nav-label">Dashboard</span>
          </a>

          <a href="pickup.php"
             class="nav-item <?= ($currentPage == 'pickup.php') ? 'active' : '' ?>">
            <span class="nav-icon">🚚</span>
            <span class="nav-label">Home Pickup Request</span>
          </a>

          <a href="deposit.php"
             class="nav-item <?= ($currentPage == 'deposit.php') ? 'active' : '' ?>">
            <span class="nav-icon">♻️</span>
            <span class="nav-label">Deposit Waste</span>
          </a>

          <a href="rankings.php"
             class="nav-item <?= ($currentPage == 'rankings.php') ? 'active' : '' ?>">
            <span class="nav-icon">🏆</span>
            <span class="nav-label">Rankings</span>
          </a>

          <a href="reward.php"
             class="nav-item <?= ($currentPage == 'reward.php') ? 'active' : '' ?>">
            <span class="nav-icon">🛍️</span>
            <span class="nav-label">Rewards Store</span>
          </a>

          <!-- NEW: User Profile Tab -->
          <a href="profile.php"
             class="nav-item <?= ($currentPage == 'profile.php') ? 'active' : '' ?>">
            <span class="nav-icon">👤</span>
            <span class="nav-label">User Profile</span>
          </a>

        </nav>

        <div class="sidebar-footer">
          <a href="logout.php" class="nav-item logout">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span class="nav-label">Log Out</span>
          </a>
        </div>

      <?php endif; ?>

    </aside>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content">
