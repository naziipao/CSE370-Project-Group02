<?php
/* ============================================================
   dashboard.php
   Logic at the Top, HTML UI at the Bottom
   ============================================================ */

require_once "config/auth.php";
require_once "config/DBconnect.php";

// This was missing in the version pulled from the repo. Without it,
// a visitor with no session at all (or a recycler, who has
// $_SESSION['recycler_id'] but no $_SESSION['user_id']) hits the
// query below with $user_id = null. The query then finds no
// matching row, $user stays false, and the code DOES redirect to
// logout.php a few lines down - but only after PHP has already
// thrown an "Undefined array key" notice trying to read
// $_SESSION['user_id']. If display_errors is on, that notice text
// prints before header() runs, which then fails with
// "headers already sent". require_login() stops all of this by
// checking the session and redirecting BEFORE any of that happens.
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
//
// Note: this reads edu_institute_stats.CumulativeEarnedPoints
// directly, with no need to also SUM deposit points separately,
// because config/points.php's award_pickup_points() already keeps
// that column incrementally up to date on every completed pickup:
//
//   UPDATE edu_institute_stats
//   SET CumulativeEarnedPoints = CumulativeEarnedPoints + ?
//   WHERE institute_EIIN = ?
//
// So this column is always the current running total already.
$universityRank = 'N/A';
$universityName = 'Not Affiliated'; // Default fallback

try {
    $stmt_student = $pdo->prepare("SELECT institute_EIIN FROM student WHERE User_id = ?");
    $stmt_student->execute([$user_id]);
    $student = $stmt_student->fetch();

    if ($student && !empty($student['institute_EIIN'])) {
        $eiin = $student['institute_EIIN'];
        
        // Fetch Institute Name AND Calculate rank based on CumulativeEarnedPoints.
        // The subquery counts how many institutes have STRICTLY MORE points
        // than this one, +1 = this institute's rank. Two institutes tied on
        // points get the same rank (standard competition ranking).
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

// 3. Render Header
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
    <h3>University Rank</h3>
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

<?php
// 4. Render Footer
include 'footer.php';
?>