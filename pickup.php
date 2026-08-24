<?php
/* ============================================================
   pickup.php  -  FEATURE 1: Home Pickup Requests (user side)

   Updated for:
     - a weight field; pickup is only allowed for >= 10 kg
     - BOTH the user and the recycler must confirm the handover
       before points are awarded
     - a "recyclers are busy" message if nobody accepts in 10 min
     - the Call button is gone; the phone number is shown so the
       user can dial it themselves

   The page reloads itself every 15 seconds while a request is
   live, which is how it notices status changes without any
   JavaScript.
   ============================================================ */


/* ===== 1. SETUP ===== */

require_once "config/DBconnect.php";
require_once "config/auth.php";
require_once "config/points.php";

require_login();

$user_id = $_SESSION['user_id'];

// Minimum weight before a pickup is allowed.
define('MIN_PICKUP_KG', 10);

// How long before "no recycler accepted" shows, in minutes.
define('PICKUP_WAIT_MINUTES', 10);

// The user's city decides which recyclers can see the request.
$stmt = $pdo->prepare("SELECT City, StreetAddress FROM `user` WHERE User_id = ?");
$stmt->execute([$user_id]);
$me = $stmt->fetch();

$my_city    = $me['City'];
$my_address = $me['StreetAddress'];


/* ===== 2. HANDLE THE FORMS ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];


    /* ---------- Make a new pickup request ---------- */

    if ($action == 'create') {

        $pickup_type = $_POST['pickup_type'];
        $weight      = (float) $_POST['weight_kg'];
        $notes       = trim($_POST['notes']);

        // The pickup address is always the user's home address.
        // No dropdown, no free-text: one fixed collection point.
        $address = $my_address;
        $city    = $my_city;

        // Feature 2: pickups are only for larger loads. Smaller
        // amounts should be dropped at a collection center instead.
        if ($weight < MIN_PICKUP_KG) {

            set_flash("Home pickup is only for " . MIN_PICKUP_KG
                      . " kg or more. Please deposit smaller amounts at a collection center.");
            header("Location: pickup.php");
            exit;
        }

        if ($address == '' || $city == '') {

            set_flash("Your home address is not set. Please update your profile first.");
            header("Location: pickup.php");
            exit;
        }

        // Only one live request at a time.
        $stmt = $pdo->prepare("SELECT Pickup_ID FROM pickup_request
                               WHERE User_id = ?
                                 AND status IN ('Pending','Assigned','On the way','Arrived')");
        $stmt->execute([$user_id]);

        if ($stmt->fetch()) {

            set_flash("You already have a pickup in progress.");
            header("Location: pickup.php");
            exit;
        }

        // Next ID: PU001, PU002, ...
        $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(Pickup_ID, 3) AS UNSIGNED))
                             FROM pickup_request");
        $next      = ((int) $stmt->fetchColumn()) + 1;
        $pickup_id = 'PU' . str_pad($next, 3, '0', STR_PAD_LEFT);

        // Recycler_ID stays NULL, which is what makes the request
        // visible to recyclers in this city.
        $stmt = $pdo->prepare("INSERT INTO pickup_request
                                 (Pickup_ID, User_id, Pickup_type, status,
                                  pickup_address, city, weight_kg, notes, request_date)
                               VALUES (?, ?, ?, 'Pending', ?, ?, ?, ?, NOW())");
        $stmt->execute([$pickup_id, $user_id, $pickup_type, $address,
                        $city, $weight, $notes]);

        set_flash("Request " . $pickup_id . " posted. Recyclers in "
                  . $city . " can now accept it.");
        header("Location: pickup.php");
        exit;
    }


    /* ---------- Cancel a request ---------- */

    if ($action == 'cancel') {

        $pickup_id = $_POST['pickup_id'];

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE pickup_request
                               SET status = 'Cancelled'
                               WHERE Pickup_ID = ?
                                 AND User_id = ?
                                 AND status IN ('Pending','Assigned')");
        $stmt->execute([$pickup_id, $user_id]);

        // Free the recycler if one had accepted.
        $stmt = $pdo->prepare("UPDATE recycler SET is_available = 1
                               WHERE Recycler_ID = (SELECT Recycler_ID
                                                    FROM pickup_request
                                                    WHERE Pickup_ID = ?)");
        $stmt->execute([$pickup_id]);

        $pdo->commit();

        set_flash("Pickup request cancelled.");
        header("Location: pickup.php");
        exit;
    }


    /* ---------- Confirm the handover (USER side) ----------

       Points are only awarded once BOTH sides have confirmed.
       Whoever confirms second is the one who triggers the payout,
       so neither the user nor the recycler can claim points alone. */

    if ($action == 'confirm') {

        $pickup_id = $_POST['pickup_id'];

        $pdo->beginTransaction();

        // Record the user's confirmation.
        $stmt = $pdo->prepare("UPDATE pickup_request
                               SET user_confirmed = 1
                               WHERE Pickup_ID = ?
                                 AND User_id = ?
                                 AND status = 'Arrived'");
        $stmt->execute([$pickup_id, $user_id]);

        // Read the request back to see if BOTH sides have now confirmed.
        $stmt = $pdo->prepare("SELECT User_id, Recycler_ID, weight_kg, Pickup_type,
                                      user_confirmed, recycler_confirmed
                               FROM pickup_request
                               WHERE Pickup_ID = ?");
        $stmt->execute([$pickup_id]);
        $req = $stmt->fetch();

        if ($req && $req['user_confirmed'] && $req['recycler_confirmed']) {

            // Both agree. Close it out and award the points.
            $stmt = $pdo->prepare("UPDATE pickup_request
                                   SET status = 'Collected'
                                   WHERE Pickup_ID = ?");
            $stmt->execute([$pickup_id]);

            award_pickup_points($pdo, $req['User_id'], $req['weight_kg'],
                                $req['Pickup_type'], $pickup_id, $req['Recycler_ID']);

            // Recycler is free again.
            $stmt = $pdo->prepare("UPDATE recycler SET is_available = 1
                                   WHERE Recycler_ID = ?");
            $stmt->execute([$req['Recycler_ID']]);

            $pdo->commit();

            $earned = points_for_weight($req['weight_kg']);
            set_flash("Handover complete! You earned " . $earned . " Green Points. 🌿");

        } else {

            $pdo->commit();
            set_flash("Thanks! Waiting for the recycler to confirm as well.");
        }

        header("Location: pickup.php");
        exit;
    }
}


/* ===== 3. LOAD THE DATA TO DISPLAY ===== */

// --- the live request, if any ---
$stmt = $pdo->prepare("SELECT p.*,
                              r.name       AS recycler_name,
                              r.phone      AS recycler_phone,
                              r.vehicle_no AS recycler_vehicle
                       FROM pickup_request p
                       LEFT JOIN recycler r ON p.Recycler_ID = r.Recycler_ID
                       WHERE p.User_id = ?
                         AND p.status IN ('Pending','Assigned','On the way','Arrived')
                       ORDER BY p.request_date DESC
                       LIMIT 1");
$stmt->execute([$user_id]);
$active = $stmt->fetch();

if ($active) {
    $auto_refresh = 15;
}

// Feature 4: has a Pending request been waiting too long?
$waited_too_long = false;
if ($active && $active['status'] == 'Pending') {

    $stmt = $pdo->prepare("SELECT TIMESTAMPDIFF(MINUTE, request_date, NOW())
                           FROM pickup_request WHERE Pickup_ID = ?");
    $stmt->execute([$active['Pickup_ID']]);

    if ((int) $stmt->fetchColumn() >= PICKUP_WAIT_MINUTES) {
        $waited_too_long = true;
    }
}

// --- recyclers covering the user's city ---
$stmt = $pdo->prepare("SELECT name, phone, vehicle_no, is_available
                       FROM recycler
                       WHERE city = ?
                       ORDER BY is_available DESC, name ASC");
$stmt->execute([$my_city]);
$local_recyclers = $stmt->fetchAll();

// --- past requests ---
$stmt = $pdo->prepare("SELECT p.Pickup_ID, p.Pickup_type, p.status,
                              p.weight_kg, p.request_date,
                              r.name AS recycler_name
                       FROM pickup_request p
                       LEFT JOIN recycler r ON p.Recycler_ID = r.Recycler_ID
                       WHERE p.User_id = ?
                         AND p.status IN ('Collected','Cancelled')
                       ORDER BY p.request_date DESC
                       LIMIT 10");
$stmt->execute([$user_id]);
$history = $stmt->fetchAll();

// Progress tracker steps.
$steps = ['Pending', 'Assigned', 'On the way', 'Arrived', 'Collected'];
$step_now = 0;
if ($active) {
    $found = array_search($active['status'], $steps);
    $step_now = ($found === false) ? 0 : $found;
}


/* ===== 4. THE PAGE ===== */

$page_title = "Home Pickup Request";
$page_css   = "pickup.css";
include "header.php";
?>

      <?php show_flash(); ?>

      <!-- ============ ARRIVAL ALERT ============ -->
      <?php if ($active && $active['status'] == 'Arrived' && !$active['user_confirmed']): ?>
      <div class="arrival-alert">
        <span class="alert-bell">🔔</span>
        <div class="alert-text">
          <h2>Your recycler has arrived!</h2>
          <p>
            <?= htmlspecialchars($active['recycler_name']) ?>
            is outside in vehicle
            <strong><?= htmlspecialchars($active['recycler_vehicle']) ?></strong>.
            Hand over the waste, then confirm below.
          </p>
        </div>
        <form method="POST" action="pickup.php">
          <input type="hidden" name="action" value="confirm">
          <input type="hidden" name="pickup_id" value="<?= $active['Pickup_ID'] ?>">
          <button type="submit" class="btn-confirm">I have handed over the waste</button>
        </form>
      </div>
      <?php endif; ?>

      <!-- Waiting for the recycler's half of the confirmation -->
      <?php if ($active && $active['status'] == 'Arrived' && $active['user_confirmed'] && !$active['recycler_confirmed']): ?>
      <div class="dual-wait">
        <span class="waiting-dot"></span>
        You confirmed. Waiting for <?= htmlspecialchars($active['recycler_name']) ?> to confirm too, then your points are added.
      </div>
      <?php endif; ?>


      <header class="top-header glow-card">
        <div class="welcome-text">
          <h1>Home Pickup Request 🚚</h1>
          <p>Post a request and a recycler in your area will accept it.</p>
        </div>
        <div class="area-badge">
          <span class="badge-icon">📍</span>
          <div class="badge-info">
            <span class="badge-label">Your Area</span>
            <span class="badge-points">
              <?= $my_city ? htmlspecialchars($my_city) : 'Not set' ?>
            </span>
          </div>
        </div>
      </header>


      <?php if ($active): ?>
      <!-- ============ ACTIVE REQUEST ============ -->
      <section class="pickup-card glow-card">

        <div class="card-head">
          <h2 class="card-title">Request <?= htmlspecialchars($active['Pickup_ID']) ?></h2>
          <span class="status-pill status-<?= strtolower(str_replace(' ', '-', $active['status'])) ?>">
            <?= htmlspecialchars($active['status']) ?>
          </span>
        </div>

        <ol class="tracker">
          <?php foreach ($steps as $i => $step): ?>
            <li class="tracker-step <?= $i <= $step_now ? 'done' : '' ?> <?= $i == $step_now ? 'current' : '' ?>">
              <span class="tracker-dot"><?= $i + 1 ?></span>
              <span class="tracker-label"><?= $step == 'Assigned' ? 'Accepted' : $step ?></span>
            </li>
          <?php endforeach; ?>
        </ol>

        <div class="pickup-detail-grid">
          <div class="detail-box">
            <span class="detail-label">Waste Type</span>
            <span class="detail-value"><?= htmlspecialchars($active['Pickup_type']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Weight</span>
            <span class="detail-value"><?= htmlspecialchars($active['weight_kg']) ?> kg</span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Pickup Address</span>
            <span class="detail-value"><?= htmlspecialchars($active['pickup_address']) ?></span>
          </div>
          <div class="detail-box">
            <span class="detail-label">Worth</span>
            <span class="detail-value"><?= points_for_weight($active['weight_kg']) ?> pts</span>
          </div>
          <?php if ($active['notes']): ?>
          <div class="detail-box">
            <span class="detail-label">Your Note</span>
            <span class="detail-value"><?= htmlspecialchars($active['notes']) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <!-- Who accepted the job. No call button: the number is shown. -->
        <?php if ($active['Recycler_ID']): ?>
        <div class="recycler-panel">
          <div class="recycler-avatar">🧑‍🔧</div>
          <div class="recycler-info">
            <span class="recycler-label">Request accepted by</span>
            <h3 class="recycler-name"><?= htmlspecialchars($active['recycler_name']) ?></h3>
            <div class="recycler-meta">
              <span class="meta-chip">📞 <?= htmlspecialchars($active['recycler_phone']) ?></span>
              <span class="meta-chip">🚛 <?= htmlspecialchars($active['recycler_vehicle']) ?></span>
            </div>
          </div>
        </div>
        <?php elseif ($waited_too_long): ?>
        <!-- Feature 4: nobody accepted in time -->
        <div class="busy-panel">
          <span class="busy-icon">😕</span>
          <div>
            <strong>Recyclers nearby are busy right now.</strong>
            <p>No one in <?= htmlspecialchars($active['city']) ?> has accepted yet.
               You can keep waiting, or cancel and try again in a little while.</p>
          </div>
        </div>
        <?php else: ?>
        <div class="waiting-panel">
          <span class="waiting-dot"></span>
          Waiting for a recycler in <?= htmlspecialchars($active['city']) ?> to accept your request...
        </div>
        <?php endif; ?>

        <?php if ($active['status'] == 'Pending' || $active['status'] == 'Assigned'): ?>
        <form method="POST" action="pickup.php" class="cancel-form">
          <input type="hidden" name="action" value="cancel">
          <input type="hidden" name="pickup_id" value="<?= $active['Pickup_ID'] ?>">
          <button type="submit" class="btn-cancel">Cancel this request</button>
        </form>
        <?php endif; ?>

        <p class="refresh-note">This page checks for updates every 15 seconds.</p>

      </section>

      <?php else: ?>
      <!-- ============ NEW REQUEST FORM ============ -->
      <section class="pickup-card glow-card">

        <div class="card-head">
          <h2 class="card-title">Request a Home Pickup</h2>
        </div>

        <div class="rule-note">
          🌿 Home pickup is for <strong><?= MIN_PICKUP_KG ?> kg or more</strong>.
          For smaller amounts, use a collection center.
          You earn <strong><?= POINTS_PER_KG ?> points per kg</strong>.
        </div>

        <form method="POST" action="pickup.php" class="pickup-form">
          <input type="hidden" name="action" value="create">

          <div class="form-row">
            <div class="form-group">
              <label for="pickupType">Waste Type</label>
              <select id="pickupType" name="pickup_type" required>
                <option value="Home">Household Mixed</option>
                <option value="Plastic">Plastic</option>
                <option value="Paper">Paper</option>
                <option value="Glass">Glass</option>
                <option value="Metal">Metal</option>
                <option value="E-waste">Electronic Waste</option>
              </select>
            </div>
            <div class="form-group">
              <label for="weightKg">Approx. Weight (kg)</label>
              <input type="number" id="weightKg" name="weight_kg"
                     min="<?= MIN_PICKUP_KG ?>" step="0.5"
                     placeholder="e.g. 12" required />
              <span class="field-note">Must be at least <?= MIN_PICKUP_KG ?> kg.</span>
            </div>
          </div>

          <div class="form-group">
            <label>Pickup Address</label>
            <div class="fixed-address">
              📍 <?= $my_address ? htmlspecialchars($my_address) : '<em>Not set - update your profile</em>' ?>
              <?php if ($my_city): ?>, <?= htmlspecialchars($my_city) ?><?php endif; ?>
            </div>
            <span class="field-note">Pickups always go to your home address on file.</span>
          </div>

          <div class="form-group">
            <label for="pickupNotes">Note for the Recycler <span class="optional">(optional)</span></label>
            <input type="text" id="pickupNotes" name="notes"
                   placeholder="Ring the bell twice, 3rd floor" />
          </div>

          <button type="submit" class="btn-primary">Post Pickup Request</button>
        </form>

      </section>
      <?php endif; ?>


      <!-- ============ RECYCLERS IN YOUR AREA ============ -->
      <section class="pickup-card glow-card">
        <div class="card-head">
          <h2 class="card-title">Recyclers in <?= $my_city ? htmlspecialchars($my_city) : 'your area' ?></h2>
          <span class="count-pill"><?= count($local_recyclers) ?> found</span>
        </div>

        <?php if (count($local_recyclers) == 0): ?>
          <p class="empty-msg">
            No recyclers cover <?= htmlspecialchars($my_city) ?> yet.
            You can still post a request and it will wait until one joins.
          </p>
        <?php else: ?>
          <div class="recycler-grid">
            <?php foreach ($local_recyclers as $r): ?>
            <div class="recycler-tile <?= $r['is_available'] ? '' : 'busy' ?>">
              <div class="tile-top">
                <span class="tile-avatar">🧑‍🔧</span>
                <span class="tile-status <?= $r['is_available'] ? 'free' : 'busy' ?>">
                  <?= $r['is_available'] ? 'Available' : 'On a job' ?>
                </span>
              </div>
              <h3 class="tile-name"><?= htmlspecialchars($r['name']) ?></h3>
              <p class="tile-line">🚛 <?= htmlspecialchars($r['vehicle_no']) ?></p>
              <p class="tile-line">📞 <?= htmlspecialchars($r['phone']) ?></p>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>


      <!-- ============ PAST REQUESTS ============ -->
      <?php if (count($history) > 0): ?>
      <section class="pickup-card glow-card">
        <div class="card-head">
          <h2 class="card-title">Past Requests</h2>
        </div>
        <table class="history-table">
          <tr><th>ID</th><th>Type</th><th>Weight</th><th>Recycler</th><th>Date</th><th>Status</th></tr>
          <?php foreach ($history as $h): ?>
          <tr>
            <td><?= htmlspecialchars($h['Pickup_ID']) ?></td>
            <td><?= htmlspecialchars($h['Pickup_type']) ?></td>
            <td><?= htmlspecialchars($h['weight_kg']) ?> kg</td>
            <td><?= $h['recycler_name'] ? htmlspecialchars($h['recycler_name']) : '—' ?></td>
            <td><?= htmlspecialchars($h['request_date']) ?></td>
            <td><span class="status-pill status-<?= strtolower($h['status']) ?>"><?= htmlspecialchars($h['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </table>
      </section>
      <?php endif; ?>

<?php include "footer.php"; ?>