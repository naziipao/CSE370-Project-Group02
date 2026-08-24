<?php
/* ============================================================
   recycler_requests.php  -  the recycler's own page

   Updated for:
     - the drop-off-center dropdown is gone; pickup is always at
       the user's home address
     - the recycler ALSO confirms the handover (feature 4), so
       both sides must agree before points are awarded
     - the user's phone number is shown so the recycler can dial
       it themselves (no call button)
   ============================================================ */


/* ===== 1. SETUP ===== */

require_once "config/DBconnect.php";
require_once "config/auth.php";
require_once "config/points.php";

require_recycler();

$recycler_id   = $_SESSION['recycler_id'];
$recycler_name = $_SESSION['recycler_name'];
$recycler_city = $_SESSION['recycler_city'];


/* ===== 2. HANDLE THE FORMS ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];


    /* ---------- Accept a request ---------- */

    if ($action == 'accept') {

        $pickup_id = $_POST['pickup_id'];

        // One job at a time.
        $stmt = $pdo->prepare("SELECT Pickup_ID FROM pickup_request
                               WHERE Recycler_ID = ?
                                 AND status IN ('Assigned','On the way','Arrived')");
        $stmt->execute([$recycler_id]);

        if ($stmt->fetch()) {
            set_flash("Finish your current pickup before accepting another.");
            header("Location: recycler_requests.php");
            exit;
        }

        $pdo->beginTransaction();

        // The status check inside the UPDATE stops two recyclers
        // grabbing the same request. Whoever runs first wins; the
        // second changes 0 rows.
        $stmt = $pdo->prepare("UPDATE pickup_request
                               SET Recycler_ID = ?, status = 'Assigned'
                               WHERE Pickup_ID = ?
                                 AND status = 'Pending'
                                 AND Recycler_ID IS NULL");
        $stmt->execute([$recycler_id, $pickup_id]);

        if ($stmt->rowCount() == 0) {
            $pdo->rollBack();
            set_flash("Sorry, another recycler just took that request.");
            header("Location: recycler_requests.php");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE recycler SET is_available = 0 WHERE Recycler_ID = ?");
        $stmt->execute([$recycler_id]);

        $pdo->commit();

        set_flash("You accepted request " . $pickup_id . ".");
        header("Location: recycler_requests.php");
        exit;
    }


    /* ---------- Move the job along ---------- */

    if ($action == 'status') {

        $pickup_id  = $_POST['pickup_id'];
        $new_status = $_POST['new_status'];

        $allowed = ['On the way', 'Arrived'];
        if (!in_array($new_status, $allowed)) {
            set_flash("Unknown status.");
            header("Location: recycler_requests.php");
            exit;
        }

        if ($new_status == 'Arrived') {
            $stmt = $pdo->prepare("UPDATE pickup_request
                                   SET status = 'Arrived', arrived_at = NOW()
                                   WHERE Pickup_ID = ? AND Recycler_ID = ?");
            $stmt->execute([$pickup_id, $recycler_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE pickup_request
                                   SET status = ?
                                   WHERE Pickup_ID = ? AND Recycler_ID = ?");
            $stmt->execute([$new_status, $pickup_id, $recycler_id]);
        }

        set_flash("Status updated to: " . $new_status);
        header("Location: recycler_requests.php");
        exit;
    }


    /* ---------- Confirm the handover (RECYCLER side) ----------

       The mirror image of the user's confirm. Points are only
       awarded once BOTH sides have confirmed, so whoever confirms
       second triggers the payout. */

    if ($action == 'confirm') {

        $pickup_id = $_POST['pickup_id'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE pickup_request
                               SET recycler_confirmed = 1
                               WHERE Pickup_ID = ?
                                 AND Recycler_ID = ?
                                 AND status = 'Arrived'");
        $stmt->execute([$pickup_id, $recycler_id]);

        $stmt = $pdo->prepare("SELECT User_id, Recycler_ID, weight_kg, Pickup_type,
                                      user_confirmed, recycler_confirmed
                               FROM pickup_request
                               WHERE Pickup_ID = ?");
        $stmt->execute([$pickup_id]);
        $req = $stmt->fetch();

        if ($req && $req['user_confirmed'] && $req['recycler_confirmed']) {

            $stmt = $pdo->prepare("UPDATE pickup_request SET status = 'Collected'
                                   WHERE Pickup_ID = ?");
            $stmt->execute([$pickup_id]);

            award_pickup_points($pdo, $req['User_id'], $req['weight_kg'],
                                $req['Pickup_type'], $pickup_id, $req['Recycler_ID']);

            $stmt = $pdo->prepare("UPDATE recycler SET is_available = 1 WHERE Recycler_ID = ?");
            $stmt->execute([$recycler_id]);

            $pdo->commit();
            set_flash("Pickup " . $pickup_id . " complete. The customer's points were added.");

        } else {
            $pdo->commit();
            set_flash("Confirmed. Waiting for the customer to confirm as well.");
        }

        header("Location: recycler_requests.php");
        exit;
    }


    /* ---------- Drop a job you cannot do ---------- */

    if ($action == 'release') {

        $pickup_id = $_POST['pickup_id'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE pickup_request
                               SET Recycler_ID = NULL, status = 'Pending'
                               WHERE Pickup_ID = ?
                                 AND Recycler_ID = ?
                                 AND status IN ('Assigned','On the way')");
        $stmt->execute([$pickup_id, $recycler_id]);

        $stmt = $pdo->prepare("UPDATE recycler SET is_available = 1 WHERE Recycler_ID = ?");
        $stmt->execute([$recycler_id]);

        $pdo->commit();

        set_flash("Request released. It is open to other recyclers again.");
        header("Location: recycler_requests.php");
        exit;
    }
}


/* ===== 3. LOAD THE DATA TO DISPLAY ===== */

// --- the job this recycler is on ---
$stmt = $pdo->prepare("SELECT p.*,
                              u.FirstName, u.LastName,
                              ph.Phone AS user_phone
                       FROM pickup_request p
                       JOIN `user` u ON p.User_id = u.User_id
                       LEFT JOIN phone ph ON ph.User_id = u.User_id
                       WHERE p.Recycler_ID = ?
                         AND p.status IN ('Assigned','On the way','Arrived')
                       LIMIT 1");
$stmt->execute([$recycler_id]);
$my_job = $stmt->fetch();

// --- open requests in this recycler's city ---
$stmt = $pdo->prepare("SELECT p.*, u.FirstName, u.LastName
                       FROM pickup_request p
                       JOIN `user` u ON p.User_id = u.User_id
                       WHERE p.status = 'Pending'
                         AND p.Recycler_ID IS NULL
                         AND p.city = ?
                       ORDER BY p.request_date ASC");
$stmt->execute([$recycler_city]);
$open_requests = $stmt->fetchAll();

// --- finished jobs ---
$stmt = $pdo->prepare("SELECT p.Pickup_ID, p.Pickup_type, p.weight_kg,
                              p.status, p.request_date,
                              u.FirstName, u.LastName
                       FROM pickup_request p
                       JOIN `user` u ON p.User_id = u.User_id
                       WHERE p.Recycler_ID = ?
                         AND p.status IN ('Collected','Cancelled')
                       ORDER BY p.request_date DESC
                       LIMIT 10");
$stmt->execute([$recycler_id]);
$history = $stmt->fetchAll();

$auto_refresh = 20;


/* ===== 4. THE PAGE ===== */

$page_title = "User Requests";
$page_css   = "recycler.css";
include "header.php";
?>

      <?php show_flash(); ?>

      <header class="top-header glow-card">
        <div class="welcome-text">
          <h1>Hello, <?= htmlspecialchars($recycler_name) ?> 🚛</h1>
          <p>Pick up any request you like in your area. Nobody assigns them to you.</p>
        </div>
        <div class="area-badge">
          <span class="badge-icon">📍</span>
          <div class="badge-info">
            <span class="badge-label">Your Zone</span>
            <span class="badge-points"><?= htmlspecialchars($recycler_city) ?></span>
          </div>
        </div>
      </header>


      <?php if ($my_job): ?>
      <!-- ============ THE JOB YOU ARE ON ============ -->
      <section class="pickup-card glow-card current-job">

        <div class="card-head">
          <h2 class="card-title">🔧 Your Current Job &nbsp;·&nbsp; <?= htmlspecialchars($my_job['Pickup_ID']) ?></h2>
          <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $my_job['status'])) ?>">
            <?= htmlspecialchars($my_job['status']) ?>
          </span>
        </div>

        <div class="pickup-detail-grid">
          <div class="detail-box">
            <span class="detail-label">Customer</span>
            <span class="detail-value"><?= htmlspecialchars($my_job['FirstName'] . ' ' . $my_job['LastName']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Phone (dial to coordinate)</span>
            <span class="detail-value"><?= $my_job['user_phone'] ? htmlspecialchars($my_job['user_phone']) : '—' ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Home Address</span>
            <span class="detail-value"><?= htmlspecialchars($my_job['pickup_address']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Waste Type</span>
            <span class="detail-value"><?= htmlspecialchars($my_job['Pickup_type']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Weight</span>
            <span class="detail-value"><?= htmlspecialchars($my_job['weight_kg']) ?> kg</span>
          </div>
          <?php if ($my_job['notes']): ?>
          <div class="detail-box">
            <span class="detail-label">Customer Note</span>
            <span class="detail-value"><?= htmlspecialchars($my_job['notes']) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <div class="status-actions">
          <?php if ($my_job['status'] == 'Assigned'): ?>
          <form method="POST" action="recycler_requests.php">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="pickup_id" value="<?= $my_job['Pickup_ID'] ?>">
            <input type="hidden" name="new_status" value="On the way">
            <button type="submit" class="btn-step">🚚 I am on the way</button>
          </form>
          <?php endif; ?>

          <?php if ($my_job['status'] == 'On the way'): ?>
          <form method="POST" action="recycler_requests.php">
            <input type="hidden" name="action" value="status">
            <input type="hidden" name="pickup_id" value="<?= $my_job['Pickup_ID'] ?>">
            <input type="hidden" name="new_status" value="Arrived">
            <button type="submit" class="btn-step highlight">🔔 I have arrived</button>
          </form>
          <?php endif; ?>

          <?php if ($my_job['status'] != 'Arrived'): ?>
          <form method="POST" action="recycler_requests.php">
            <input type="hidden" name="action" value="release">
            <input type="hidden" name="pickup_id" value="<?= $my_job['Pickup_ID'] ?>">
            <button type="submit" class="btn-step danger">✖ Release this job</button>
          </form>
          <?php endif; ?>
        </div>

        <!-- Feature 4: recycler's own confirmation -->
        <?php if ($my_job['status'] == 'Arrived'): ?>
          <?php if (!$my_job['recycler_confirmed']): ?>
          <div class="confirm-box">
            <p>Collected the waste from the customer? Confirm it here.
               Points are added once the customer confirms too.</p>
            <form method="POST" action="recycler_requests.php">
              <input type="hidden" name="action" value="confirm">
              <input type="hidden" name="pickup_id" value="<?= $my_job['Pickup_ID'] ?>">
              <button type="submit" class="btn-confirm-recycler">✅ I have collected the waste</button>
            </form>
          </div>
          <?php else: ?>
          <p class="waiting-for-user">
            ✅ You confirmed. Waiting for the customer to confirm too, then it closes.
          </p>
          <?php endif; ?>
        <?php endif; ?>

      </section>
      <?php endif; ?>


      <!-- ============ OPEN REQUESTS IN YOUR CITY ============ -->
      <section class="pickup-card glow-card">
        <div class="card-head">
          <h2 class="card-title">Open Requests in <?= htmlspecialchars($recycler_city) ?></h2>
          <span class="count-pill"><?= count($open_requests) ?> waiting</span>
        </div>

        <?php if ($my_job): ?>
          <p class="empty-msg">You are already on a job. Finish or release it to see open requests again.</p>
        <?php elseif (count($open_requests) == 0): ?>
          <p class="empty-msg">
            No open requests in <?= htmlspecialchars($recycler_city) ?> right now.
            This page checks again every 20 seconds.
          </p>
        <?php else: ?>
          <div class="job-list">
            <?php foreach ($open_requests as $req): ?>
            <div class="job-card">
              <div class="job-top">
                <span class="job-id"><?= htmlspecialchars($req['Pickup_ID']) ?></span>
                <span class="job-type"><?= htmlspecialchars($req['Pickup_type']) ?></span>
              </div>
              <h3 class="job-customer"><?= htmlspecialchars($req['FirstName'] . ' ' . $req['LastName']) ?></h3>
              <p class="job-line">📍 <?= htmlspecialchars($req['pickup_address']) ?></p>
              <p class="job-line">⚖️ <?= htmlspecialchars($req['weight_kg']) ?> kg &nbsp;·&nbsp; 🕒 <?= htmlspecialchars($req['request_date']) ?></p>
              <?php if ($req['notes']): ?>
              <p class="job-note">“<?= htmlspecialchars($req['notes']) ?>”</p>
              <?php endif; ?>
              <!-- No dropdown: pickup is always the home address. -->
              <form method="POST" action="recycler_requests.php" class="accept-form">
                <input type="hidden" name="action" value="accept">
                <input type="hidden" name="pickup_id" value="<?= $req['Pickup_ID'] ?>">
                <button type="submit" class="btn-accept">✅ Accept This Request</button>
              </form>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>


      <!-- ============ YOUR PAST JOBS ============ -->
      <?php if (count($history) > 0): ?>
      <section class="pickup-card glow-card">
        <div class="card-head">
          <h2 class="card-title">Your Completed Jobs</h2>
        </div>
        <table class="history-table">
          <tr><th>ID</th><th>Customer</th><th>Type</th><th>Weight</th><th>Date</th><th>Status</th></tr>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['Pickup_ID']) ?></td>
            <td><?= htmlspecialchars($h['FirstName'] . ' ' . $h['LastName']) ?></td>
            <td><?= htmlspecialchars($h['Pickup_type']) ?></td>
            <td><?= htmlspecialchars($h['weight_kg']) ?> kg</td>
            <td><?= htmlspecialchars($h['request_date']) ?></td>
            <td><span class="status-pill status-<?= strtolower($h['status']) ?>"><?= htmlspecialchars($h['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </section>
      <?php endif; ?>

<?php include "footer.php"; ?>