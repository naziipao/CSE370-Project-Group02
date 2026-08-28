<?php
/* ============================================================
   deposit.php  -  Deposit Waste  (USER side)

   The user drops off waste at a collection center. Here they:
     - pick a center, a waste type and a weight, and post a request
     - watch that request until the center manager accepts it
       (the page meta-refreshes every 15s while it is Pending)
     - see how many points an accepted deposit earned

   ALL database work is in deposit_data.php. This file only handles
   the form actions and draws the page, exactly like pickup.php.
   ============================================================ */


/* ===== 1. SETUP ===== */

require_once "config/DBconnect.php";
require_once "config/auth.php";
require_once "deposit_data.php";      // create/cancel/load helpers + points

require_login();

$user_id = $_SESSION['user_id'];


/* ===== 2. HANDLE THE FORMS ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /* ---------- Post a new deposit request ---------- */
    if ($action == 'create') {

        [$ok, $msg] = create_deposit_request(
            $pdo,
            $user_id,
            $_POST['center_id']  ?? '',
            $_POST['waste_type'] ?? '',
            $_POST['weight']     ?? 0
        );

        set_flash($msg);
        header("Location: deposit.php");
        exit;
    }

    /* ---------- Withdraw a still-pending request ---------- */
    if ($action == 'cancel') {

        [$ok, $msg] = cancel_deposit_request($pdo, $_POST['request_id'] ?? '', $user_id);

        set_flash($msg);
        header("Location: deposit.php");
        exit;
    }
}


/* ===== 3. LOAD THE DATA TO DISPLAY ===== */

$active   = get_user_active_request($pdo, $user_id);   // Pending one, or false
$centers  = get_centers_with_capacity($pdo);           // for the dropdown
$history  = get_user_recent_requests($pdo, $user_id);  // past decisions

// Keep refreshing only while something is waiting to be accepted.
if ($active) {
    $auto_refresh = 15;
}


/* ===== 4. THE PAGE ===== */

$page_title = "Deposit Waste";
$page_css   = "deposit.css";
include "header.php";
?>

      <?php show_flash(); ?>

      <header class="top-header glow-card">
        <div class="welcome-text">
          <h1>&#9851;&#65039; Deposit Waste</h1>
          <p>Drop waste off at a collection center. A manager accepts it, then your points land.</p>
        </div>
        <div class="area-badge">
          <span class="badge-icon">&#127807;</span>
          <div class="badge-info">
            <span class="badge-label">Rate</span>
            <span class="badge-points"><?= POINTS_PER_KG ?> pts / kg</span>
          </div>
        </div>
      </header>


      <?php if ($active): ?>
      <!-- ============ ACTIVE (PENDING) REQUEST ============ -->
      <section class="deposit-card glow-card">

        <div class="card-head">
          <h2 class="card-title">Deposit <?= htmlspecialchars($active['request_id']) ?></h2>
          <span class="status-pill status-pending">Waiting for manager</span>
        </div>

        <div class="deposit-detail-grid">
          <div class="detail-box">
            <span class="detail-label">Collection Center</span>
            <span class="detail-value"><?= htmlspecialchars($active['Center_name']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Waste Type</span>
            <span class="detail-value"><?= htmlspecialchars($active['waste_type']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Weight</span>
            <span class="detail-value"><?= number_format($active['weight'], 1) ?> kg</span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Worth (if accepted)</span>
            <span class="detail-value"><?= points_for_weight($active['weight']) ?> pts</span>
          </div>
        </div>

        <div class="waiting-panel">
          <span class="waiting-dot"></span>
          The manager at <?= htmlspecialchars($active['Center_name']) ?> has not accepted this yet.
        </div>

        <form method="POST" action="deposit.php" class="cancel-form">
          <input type="hidden" name="action" value="cancel">
          <input type="hidden" name="request_id" value="<?= htmlspecialchars($active['request_id']) ?>">
          <button type="submit" class="btn-cancel">Cancel this deposit</button>
        </form>

        <p class="refresh-note">This page checks for updates every 15 seconds.</p>

      </section>

      <?php else: ?>
      <!-- ============ NEW DEPOSIT FORM ============ -->
      <section class="deposit-card glow-card">

        <div class="card-head">
          <h2 class="card-title">Make a Deposit</h2>
        </div>

        <div class="rule-note">
          &#127793; Drop-off is best for smaller amounts. You earn
          <strong><?= POINTS_PER_KG ?> points per kg</strong> once the center
          manager accepts your waste.
        </div>

        <form method="POST" action="deposit.php" class="deposit-form">
          <input type="hidden" name="action" value="create">

          <div class="form-group">
            <label for="centerId">Collection Center</label>
            <select id="centerId" name="center_id" required>
              <option value="">Choose a center...</option>
              <?php foreach ($centers as $c): ?>
                <option value="<?= htmlspecialchars($c['id']) ?>"
                        <?= $c['is_full'] ? 'disabled' : '' ?>>
                  <?= htmlspecialchars($c['name']) ?>
                  <?php if ($c['is_full']): ?>
                    &mdash; FULL
                  <?php else: ?>
                    (<?= number_format($c['remaining'], 0) ?> kg free)
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <span class="field-note">Full centers cannot take new deposits.</span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="wasteType">Waste Type</label>
              <select id="wasteType" name="waste_type" required>
                <option value="Plastic">Plastic</option>
                <option value="Paper">Paper</option>
                <option value="Glass">Glass</option>
                <option value="Metal">Metal</option>
                <option value="E-waste">Electronic Waste</option>
              </select>
            </div>
            <div class="form-group">
              <label for="weight">Weight (kg)</label>
              <input type="number" id="weight" name="weight"
                     min="0.5" step="0.5" placeholder="e.g. 5" required />
              <span class="field-note">Any amount greater than 0.</span>
            </div>
          </div>

          <button type="submit" class="btn-primary">Send Deposit to Center</button>
        </form>

      </section>
      <?php endif; ?>


      <!-- ============ PAST DEPOSITS ============ -->
      <?php if (count($history) > 0): ?>
      <section class="deposit-card glow-card">
        <div class="card-head">
          <h2 class="card-title">Your Recent Deposits</h2>
        </div>
        <table class="history-table">
          <tr>
            <th>ID</th><th>Center</th><th>Type</th>
            <th>Weight</th><th>Date</th><th>Result</th>
          </tr>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['request_id']) ?></td>
            <td><?= htmlspecialchars($h['Center_name']) ?></td>
            <td><?= htmlspecialchars($h['waste_type']) ?></td>
            <td><?= number_format($h['weight'], 1) ?> kg</td>
            <td><?= htmlspecialchars($h['request_date']) ?></td>
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
