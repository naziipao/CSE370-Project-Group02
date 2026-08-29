<?php
/* ============================================================
   center_manager.php  -  Deposit Waste  (MANAGER side)

   The collection center manager's own page, the mirror image of
   recycler_requests.php. A manager signs in with their email and
   only ever sees THEIR center:

     - a live capacity bar (same numbers as centers.php)
     - the list of Pending deposit requests dropped at their center
     - Accept  -> the deposit becomes real, fills the center, pays points
     - Reject  -> the request is turned down, nothing is added

   All database work lives in deposit_data.php.
   ============================================================ */


/* ===== 1. SETUP ===== */

require_once "config/DBconnect.php";
require_once "config/auth.php";
require_once "deposit_data.php";

require_manager();   // logged-in managers only

$manager_id   = $_SESSION['manager_id'];
$manager_name = $_SESSION['manager_name'];
$center_id    = $_SESSION['manager_center'];
$center_name  = $_SESSION['manager_center_name'];


/* ===== 2. HANDLE THE FORMS ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action     = $_POST['action'] ?? '';
    $request_id = $_POST['request_id'] ?? '';

    if ($action == 'accept') {
        [$ok, $msg] = accept_deposit_request($pdo, $request_id, $manager_id, $center_id);
        set_flash($msg);
        header("Location: center_manager.php");
        exit;
    }

    if ($action == 'reject') {
        [$ok, $msg] = reject_deposit_request($pdo, $request_id, $manager_id, $center_id);
        set_flash($msg);
        header("Location: center_manager.php");
        exit;
    }

    if ($action == 'empty') {
        [$ok, $msg] = empty_center_waste($pdo, $center_id);
        set_flash($msg);
        header("Location: center_manager.php");
        exit;
    }
}


/* ===== 3. LOAD THE DATA TO DISPLAY ===== */

$cap      = get_center_capacity($pdo, $center_id);            // live fill level
$pending  = get_pending_requests_for_center($pdo, $center_id);
$history  = get_center_recent_decisions($pdo, $center_id);

// New requests should appear on their own.
$auto_refresh = 15;

// Colour band for the capacity bar, same idea as centers.php.
if ($cap['is_full'])        { $level = 'full'; }
elseif ($cap['percent'] >= 80) { $level = 'high'; }
else                        { $level = 'ok'; }


/* ===== 4. THE PAGE ===== */

$page_title = "Deposit Requests";
$page_css   = "manager.css";
include "header.php";
?>

      <?php show_flash(); ?>

      <header class="top-header glow-card">
        <div class="welcome-text">
          <h1>Hello, <?= htmlspecialchars($manager_name) ?> &#127970;</h1>
          <p>Accept the waste people bring to your center.</p>
        </div>
        <div class="area-badge">
          <span class="badge-icon">&#128449;&#65039;</span>
          <div class="badge-info">
            <span class="badge-label">Your Center</span>
            <span class="badge-points"><?= htmlspecialchars($center_name) ?></span>
          </div>
        </div>
      </header>


      <!-- ============ LIVE CAPACITY (same maths as centers.php) ============ -->
      <section class="mgr-capacity glow-card level-<?= $level ?>">
        <div class="cap-head">
          <h2 class="card-title">Center Capacity</h2>
        </div>

        <div class="bar-track">
          <div class="bar-fill" style="width: <?= $cap['percent'] ?>%;"></div>
        </div>

        <div class="bar-legend">
          <span><?= $cap['percent'] ?>% filled</span>
          <span>
            <?= number_format($cap['used'], 0) ?> /
            <?= number_format($cap['max'], 0) ?> kg
            &nbsp;&middot;&nbsp;
            <strong><?= number_format($cap['remaining'], 0) ?> kg free</strong>
          </span>
        </div>

        <!-- Manager action: clear the center out (waste hauled away).
             Resets the stored load to 0 kg. Disabled when already empty. -->
        <div class="cap-actions">
          <form method="POST" action="center_manager.php">
            <input type="hidden" name="action" value="empty">
            <button type="submit" class="btn-empty" <?= $cap['used'] > 0 ? '' : 'disabled' ?>>
              &#129529; Empty Center Waste
            </button>
          </form>
        </div>
      </section>


      <!-- ============ PENDING DEPOSIT REQUESTS ============ -->
      <section class="mgr-section glow-card">
        <div class="card-head">
          <h2 class="card-title">Waiting for Your Decision</h2>
          <span class="count-pill"><?= count($pending) ?> pending</span>
        </div>

        <?php if (count($pending) == 0): ?>
          <p class="empty-msg">
            No one is waiting to deposit right now. New requests will show up here on their own.
          </p>
        <?php else: ?>
          <div class="req-list">
            <?php foreach ($pending as $r): ?>
            <?php $fits = ($r['weight'] <= $cap['remaining']); ?>
            <div class="req-card <?= $fits ? '' : 'over' ?>">

              <div class="req-top">
                <span class="req-id"><?= htmlspecialchars($r['request_id']) ?></span>
                <span class="req-type"><?= htmlspecialchars($r['waste_type']) ?></span>
              </div>

              <h3 class="req-user">
                <?= htmlspecialchars($r['FirstName'] . ' ' . $r['LastName']) ?>
              </h3>

              <p class="req-line">
                &#9878;&#65039; <?= number_format($r['weight'], 1) ?> kg
                &nbsp;&middot;&nbsp;
                &#127793; worth <?= points_for_weight($r['weight']) ?> pts
              </p>
              <p class="req-line">&#128336; <?= htmlspecialchars($r['request_date']) ?></p>

              <?php if (!$fits): ?>
                <p class="req-warn">
                  &#9888;&#65039; This is more than the <?= number_format($cap['remaining'], 0) ?> kg
                  of space left. Free up space or reject it.
                </p>
              <?php endif; ?>

              <div class="req-actions">
                <form method="POST" action="center_manager.php">
                  <input type="hidden" name="action" value="accept">
                  <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['request_id']) ?>">
                  <button type="submit" class="btn-accept" <?= $fits ? '' : 'disabled' ?>>
                    &#9989; Accept
                  </button>
                </form>
                <form method="POST" action="center_manager.php">
                  <input type="hidden" name="action" value="reject">
                  <input type="hidden" name="request_id" value="<?= htmlspecialchars($r['request_id']) ?>">
                  <button type="submit" class="btn-reject">&#10006; Reject</button>
                </form>
              </div>

            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>


      <!-- ============ RECENT DECISIONS ============ -->
      <?php if (count($history) > 0): ?>
      <section class="mgr-section glow-card">
        <div class="card-head">
          <h2 class="card-title">Recently Handled</h2>
        </div>
        <table class="history-table">
          <tr>
            <th>ID</th><th>User</th><th>Type</th>
            <th>Weight</th><th>When</th><th>Result</th>
          </tr>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['request_id']) ?></td>
            <td><?= htmlspecialchars($h['FirstName'] . ' ' . $h['LastName']) ?></td>
            <td><?= htmlspecialchars($h['waste_type']) ?></td>
            <td><?= number_format($h['weight'], 1) ?> kg</td>
            <td><?= htmlspecialchars($h['handled_at']) ?></td>
            <td>
              <span class="status-pill status-<?= strtolower($h['status']) ?>">
                <?= htmlspecialchars($h['status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
        </table>
      </section>
      <?php endif; ?>

<?php include "footer.php"; ?>
