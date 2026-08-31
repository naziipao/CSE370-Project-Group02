<?php
/* ============================================================
   dashboard.php
   Logic at the Top, HTML UI at the Bottom
   ============================================================ */

require_once "config/auth.php";
require_once "config/DBconnect.php";
require_once "feed_data.php";   // FEATURE 3: the activity feed

require_login();

$user_id = $_SESSION['user_id'];

// 1. Fetch User details + Wallet points & user voucher count
$stmt = $pdo->prepare("
    SELECT 
        u.User_id,
        u.FirstName,
        u.LastName,
        u.current_badge_points,
        u.total_recycled,
        COALESCE(w.current_points, 0) AS spendable_balance,
        COALESCE(w.voucher, 0) AS user_vouchers
    FROM `user` u
    LEFT JOIN wallet w ON u.User_id = w.User_id
    WHERE u.User_id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header("Location: logout.php");
    exit;
}

$firstName = $user['FirstName'] ?: 'User';
$lastName = $user['LastName'] ?: '';
$fullName = trim("$firstName $lastName");

// 2. Fetch University Rank & Name
$universityRank = 'N/A';
$universityName = 'Not Affiliated'; // Default fallback

try {
    $stmt_student = $pdo->prepare("SELECT institute_EIIN FROM student WHERE User_id = ?");
    $stmt_student->execute([$user_id]);
    $student = $stmt_student->fetch();

    if ($student && !empty($student['institute_EIIN'])) {
        $eiin = $student['institute_EIIN'];
        
        $rank_stmt = $pdo->prepare("
            SELECT e.Institute_name, (
                SELECT COUNT(*) + 1 
                FROM edu_institute_stats 
                WHERE CumulativeEarnedPoints > e.CumulativeEarnedPoints
            ) AS inst_rank
            FROM edu_institute_stats e
            WHERE e.institute_EIIN = ?
        ");
        $rank_stmt->execute([$eiin]);
        $rank_data = $rank_stmt->fetch();
        
        if ($rank_data) {
            $universityName = $rank_data['Institute_name'];
            if ($rank_data['inst_rank']) {
                $universityRank = $rank_data['inst_rank'];
            }
        }
    }
} catch (Exception $e) {
    // Keeps default fallback if something goes wrong
}

// 3. FEATURE 3: pull the most recent platform-wide activity.
// One function call - all the query/formatting logic lives in
// feed_data.php, exactly like deposit.php delegates to deposit_data.php.
$feed = get_recent_activity($pdo, 20);

// 4. Render Header
include 'header.php';
?>

<header class="top-header glow-card">
  <div class="welcome-text">
    <h1>Welcome back, <span class="user-name" id="userName"><?= htmlspecialchars($fullName) ?></span>! 👋</h1>
    <p>Track your environmental contribution and manage your points.</p>
  </div>
  <div class="wallet-badge">
    <span class="badge-icon">🌿</span>
    <div class="badge-info">
      <span class="badge-label">Eco Balance</span>
      <span class="badge-points" id="headerPoints"><?= htmlspecialchars($user['spendable_balance']) ?> Points</span>
    </div>
  </div>
</header>

<section class="dashboard-grid">
  <div class="card glow-card">
    <span class="card-icon">♻️</span>
    <h3>Total Recycled</h3>
    <p class="card-value"><span id="valRecycled"><?= htmlspecialchars($user['total_recycled'] ?? 0) ?></span> <span class="unit">kg</span></p>
    <span class="card-subtext">Lifetime contribution</span>
  </div>

  <div class="card glow-card">
    <span class="card-icon">🏆</span>
    <h3>Institute Rank</h3>
    <span style="display: block; font-size: 0.85rem; color: #94bba4; margin-bottom: 8px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($universityName) ?>">
      <?= htmlspecialchars($universityName) ?>
    </span>
    <p class="card-value">#<span id="valRank"><?= htmlspecialchars($universityRank) ?></span></p>
    <span class="card-subtext">Based on Cumulative Points</span>
  </div>

  <div class="card glow-card">
    <span class="card-icon">🛍️</span>
    <h3>Available Rewards</h3>
    <p class="card-value"><span id="valVouchers"><?= htmlspecialchars($user['user_vouchers'] ?? 0) ?></span> Vouchers</p>
    <span class="card-subtext">Partner Discounts</span>
  </div>
</section>

<section class="cta-banner glow-card">
  <div class="cta-info">
    <h2>Log Your Waste Deposit</h2>
    <p>Scanned a recycling bin? Register your plastics, paper, or electronic waste now to claim points.</p>
  </div>
  <a href="deposit.php" class="btn-primary" style="text-decoration: none; display: inline-block;">Deposit Waste Now</a>
</section>


<!-- ============ FEATURE 3(Aurpita): ACTIVITY FEED ============

     A live "what's happening" timeline, not a notification system -
     nothing pops up, nothing is marked read. Deliberately NOT wired
     to the page's own auto-refresh: a full-page reload every few
     seconds would reset the user's scroll position while they are
     reading down the list, which defeats the point of a scrollable
     feed. It simply shows the latest snapshot every time the
     dashboard loads, same as before this feature existed. -->
<section class="feed-section glow-card">

  <div class="feed-head">
    <h2 class="feed-title">🌍 Community Activity</h2>
    <span class="feed-sub">See what everyone's recycling, live</span>
  </div>

  <?php if (count($feed) === 0): ?>
    <p class="feed-empty">No activity yet — be the first to recycle something today!</p>
  <?php else: ?>
  <div class="feed-list">
    <?php foreach ($feed as $item): ?>
    <div class="feed-item" style="--accent: <?= $item['accent'] ?>;">
      <span class="feed-icon"><?= $item['icon'] ?></span>
      <div class="feed-body">
        <p class="feed-line">
          <strong><?= htmlspecialchars($item['name']) ?></strong>
          <?php if ($item['institute']): ?>
            <span class="feed-institute">· <?= htmlspecialchars($item['institute']) ?></span>
          <?php endif; ?>
          recycled <strong><?= number_format($item['weight'], 1) ?> kg</strong>
          of <?= htmlspecialchars($item['waste_type']) ?>
        </p>
        <div class="feed-meta">
          <span class="feed-badge-pill" style="border-color: <?= $item['accent'] ?>; color: <?= $item['accent'] ?>;">
            <?= htmlspecialchars($item['badge']) ?>
          </span>
          <span class="feed-points">+<?= $item['points'] ?> pts</span>
          <span class="feed-time"><?= $item['time_label'] ?></span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</section>

<?php
// 5. Render Footer
include 'footer.php';
?>
