<?php
/* ============================================================
   header.php  -  shared top of every logged-in page

   A page may set these before including this file:

       $page_title = "Rankings";     (browser tab text)
       $page_css   = "rankings.css"; (optional)

   If $page_css is not set, it is worked out from the file name,
   so rankings.php loads CSS/rankings.css automatically.

   IMPORTANT: this file always loads CSS/main.css FIRST. That is
   the one place the color palette (--bg-card, --accent-green,
   --border-dark, etc.) and the sidebar/app-container/glow-card
   rules are defined. Every other stylesheet just USES those
   variables - it does not define them.

   The per-page stylesheet link is UNCONDITIONAL - it always
   prints the <link> tag, even if the file cannot be found yet.
   This is deliberate: a missing/misnamed CSS file then shows up
   as a 404 in the browser's Network tab, which is easy to spot.
   A silent is_file() check here has repeatedly caused a page to
   look completely unstyled with no visible error anywhere.

   The sidebar changes depending on who is signed in:
     - a recycler sees only their request list
     - a normal user sees the full menu
   ============================================================ */

$currentPage = basename($_SERVER['PHP_SELF']);

// dashboard.php -> dashboard.css , rankings.php -> rankings.css
if (!isset($page_css)) {
    $page_css = str_replace('.php', '.css', $currentPage);
}

if (!isset($page_title)) {
    $page_title = "Smart Circular Recycling";
}

$is_recycler = isset($_SESSION['recycler_id']);
$is_manager  = isset($_SESSION['manager_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Circular Recycling - <?= htmlspecialchars($page_title) ?></title>

  <?php if (isset($auto_refresh)): ?>
  <meta http-equiv="refresh" content="<?= (int) $auto_refresh ?>">
  <?php endif; ?>

  <!-- shared layout: colours, sidebar, cards. Always loads FIRST. -->
  <link rel="stylesheet" href="CSS/main.css">

  <!-- styles for this page only. Always printed - if this 404s in
       your browser's Network tab (F12), the file is missing or
       misnamed. That is the first thing to check if a page looks
       unstyled. -->
  <link rel="stylesheet" href="CSS/<?= htmlspecialchars($page_css) ?>">

  <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <div class="app-container">

    <!-- LEFT SIDEBAR MENU -->
    <aside class="sidebar <?= $is_recycler ? 'recycler-sidebar' : ($is_manager ? 'manager-sidebar' : '') ?>">

      <div class="brand-header">
        <a href="<?= $is_recycler ? 'recycler_requests.php' : ($is_manager ? 'center_manager.php' : 'dashboard.php') ?>"
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

      <?php elseif ($is_manager): ?>

        <!-- ---------- CENTER MANAGER MENU ---------- -->
        <nav class="nav-menu">
          <a href="center_manager.php"
             class="nav-item <?= ($currentPage == 'center_manager.php') ? 'active' : '' ?>">
            <span class="nav-icon">📥</span>
            <span class="nav-label">Deposit Requests</span>
          </a>
        </nav>

        <div class="sidebar-footer">
          <p class="who-line">
            Signed in as<br>
            <strong><?= htmlspecialchars($_SESSION['manager_name']) ?></strong><br>
            <span class="who-city">🗄️ <?= htmlspecialchars($_SESSION['manager_center_name']) ?></span>
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

          <a href="profile.php"
             class="nav-item <?= ($currentPage == 'profile.php') ? 'active' : '' ?>">
            <span class="nav-icon">👤</span>
            <span class="nav-label">User Profile</span>
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

          <a href="centers.php"
             class="nav-item <?= ($currentPage == 'centers.php') ? 'active' : '' ?>">
            <span class="nav-icon">🗄️</span>
            <span class="nav-label">Collection Centers</span>
          </a>

          <a href="reward.php"
             class="nav-item <?= ($currentPage == 'reward.php') ? 'active' : '' ?>">
            <span class="nav-icon">🛍️</span>
            <span class="nav-label">Rewards Store</span>
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