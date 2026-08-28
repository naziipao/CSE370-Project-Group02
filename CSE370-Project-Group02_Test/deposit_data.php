<?php
/* ============================================================
   deposit_data.php   —   BACK-END for the Deposit Waste feature
                          (data + logic only, prints no HTML)

   Included by BOTH pages:
     - deposit.php          (the user posts a deposit request)
     - center_manager.php   (the manager accepts / rejects it)

   Nothing here echoes anything, so it can be safely required at
   the top of a page before header.php runs.

   It reuses the project's shared point rules from config/points.php
   (points_for_weight, badge_for_points) so the deposit flow and the
   pickup flow can never award different numbers for the same weight.
   ============================================================ */

require_once "config/points.php";   // points_for_weight(), badge_for_points()


/* ------------------------------------------------------------
   CAPACITY HELPERS
   These use the SAME "SUM the deposit weights" idea as centers.php,
   so the manager's capacity numbers always match that page exactly.
   ------------------------------------------------------------ */

/**
 * How much weight a single center is currently holding, worked out
 * live from the deposit table (never stored anywhere).
 * Returns an array: max, used, remaining, percent, is_full.
 */
function get_center_capacity($pdo, $center_id) {

    // max capacity of the center
    $stmt = $pdo->prepare("SELECT max_capacity FROM collection_center
                           WHERE Center_ID = ?");
    $stmt->execute([$center_id]);
    $max = (float) $stmt->fetchColumn();

    // weight already deposited there (accepted deposits only —
    // a Pending deposit_request is NOT in this table yet)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(weight), 0)
                           FROM deposit WHERE center_id = ?");
    $stmt->execute([$center_id]);
    $used = (float) $stmt->fetchColumn();

    $remaining = max($max - $used, 0);

    $percent = 0;
    if ($max > 0) {
        $percent = (int) round(($used / $max) * 100);
        if ($percent > 100) $percent = 100;
    } else {
        $percent = 100;
    }

    return [
        'max'       => $max,
        'used'      => $used,
        'remaining' => $remaining,
        'percent'   => $percent,
        'is_full'   => ($max > 0 && $used >= $max),
    ];
}

/**
 * Every center plus its live fill level, for the user's dropdown.
 * Full centers are flagged so the page can grey them out.
 *
 * If a $city is given, only centers in that city are returned. The
 * city is taken from the last part of the center's Address
 * (e.g. "Road 27, Dhanmondi, Dhaka" -> "Dhaka"), which is compared
 * to the user's own City. This is how a user only ever sees the
 * collection centers in their own city.
 */
function get_centers_with_capacity($pdo, $city = null) {

    $where  = "";
    $params = [];

    if ($city !== null && $city !== '') {
        $where    = "WHERE TRIM(SUBSTRING_INDEX(c.Address, ',', -1)) = ?";
        $params[] = $city;
    }

    $sql = "SELECT  c.Center_ID, c.Center_name, c.Address, c.max_capacity,
                    COALESCE(SUM(d.weight), 0) AS used_weight
            FROM        collection_center c
            LEFT JOIN   deposit d ON d.center_id = c.Center_ID
            $where
            GROUP BY    c.Center_ID, c.Center_name, c.Address, c.max_capacity
            ORDER BY    c.Center_name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $out = [];
    foreach ($rows as $r) {
        $max  = (float) $r['max_capacity'];
        $used = (float) $r['used_weight'];
        $out[] = [
            'id'        => $r['Center_ID'],
            'name'      => $r['Center_name'],
            'address'   => $r['Address'],
            'max'       => $max,
            'used'      => $used,
            'remaining' => max($max - $used, 0),
            'is_full'   => ($max > 0 && $used >= $max),
        ];
    }
    return $out;
}


/* ------------------------------------------------------------
   USER SIDE  (deposit.php)
   ------------------------------------------------------------ */

/**
 * Create one new Pending deposit request.
 * Returns [true, message] on success or [false, reason] on failure.
 *
 * Rules:
 *   - weight must be positive
 *   - the chosen center must exist and not be full
 *   - a user may only have ONE Pending request at a time
 */
function create_deposit_request($pdo, $user_id, $center_id, $waste_type, $weight) {

    $weight = (float) $weight;

    if ($weight <= 0) {
        return [false, "Please enter a weight greater than 0 kg."];
    }

    // Center must exist.
    $stmt = $pdo->prepare("SELECT Center_name FROM collection_center
                           WHERE Center_ID = ?");
    $stmt->execute([$center_id]);
    $center_name = $stmt->fetchColumn();
    if (!$center_name) {
        return [false, "Please choose a valid collection center."];
    }

    // Center must have room. A full center is rejected right away, so
    // the request is never even created.
    $cap = get_center_capacity($pdo, $center_id);
    if ($cap['is_full']) {
        return [false, $center_name . " is full right now, so your deposit could not be placed. "
                       . "Please choose another center."];
    }

    // The requested amount must fit in the space that is left. If it is
    // more than the free space, the request is cancelled immediately
    // (never created) and the user is told exactly how much room there is.
    if ($weight > $cap['remaining']) {
        return [false, "Your deposit was not placed: " . number_format($weight, 1)
                       . " kg is more than the free space at " . $center_name
                       . " (only " . number_format($cap['remaining'], 0) . " kg left). "
                       . "Please enter a smaller amount or pick another center."];
    }

    // Only one Pending request at a time.
    $stmt = $pdo->prepare("SELECT request_id FROM deposit_request
                           WHERE User_id = ? AND status = 'Pending'");
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) {
        return [false, "You already have a deposit waiting for a manager to accept."];
    }

    /* Safe ID: insert with a placeholder so MySQL assigns req_seq,
       read it back (per-connection, no race), then write DRxx back. */
    $stmt = $pdo->prepare("INSERT INTO deposit_request
                             (request_id, User_id, center_id, waste_type,
                              weight, status, request_date)
                           VALUES ('TEMP', ?, ?, ?, ?, 'Pending', NOW())");
    $stmt->execute([$user_id, $center_id, $waste_type, $weight]);

    $seq        = $pdo->lastInsertId();
    $request_id = 'DR' . str_pad($seq, 2, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("UPDATE deposit_request SET request_id = ?
                           WHERE req_seq = ?");
    $stmt->execute([$request_id, $seq]);

    return [true, "Deposit " . $request_id . " sent to " . $center_name
                  . ". Waiting for the center manager to accept it."];
}

/** The user's current Pending request (with the center's name), or false. */
function get_user_active_request($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT dr.*, c.Center_name
                           FROM deposit_request dr
                           JOIN collection_center c ON c.Center_ID = dr.center_id
                           WHERE dr.User_id = ? AND dr.status = 'Pending'
                           ORDER BY dr.request_date DESC
                           LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/** The user withdraws their own still-Pending request. */
function cancel_deposit_request($pdo, $request_id, $user_id) {
    $stmt = $pdo->prepare("UPDATE deposit_request
                           SET status = 'Cancelled'
                           WHERE request_id = ? AND User_id = ? AND status = 'Pending'");
    $stmt->execute([$request_id, $user_id]);

    if ($stmt->rowCount() == 0) {
        return [false, "That request can no longer be cancelled."];
    }
    return [true, "Deposit request cancelled."];
}

/** The user's finished requests (Accepted / Rejected / Cancelled), newest first. */
function get_user_recent_requests($pdo, $user_id, $limit = 8) {
    $stmt = $pdo->prepare("SELECT dr.request_id, dr.waste_type, dr.weight,
                                  dr.status, dr.request_date, c.Center_name
                           FROM deposit_request dr
                           JOIN collection_center c ON c.Center_ID = dr.center_id
                           WHERE dr.User_id = ?
                             AND dr.status IN ('Accepted','Rejected','Cancelled')
                           ORDER BY dr.request_date DESC
                           LIMIT " . (int) $limit);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}


/* ------------------------------------------------------------
   MANAGER SIDE  (center_manager.php)
   ------------------------------------------------------------ */

/** All Pending requests for one center, oldest first (fair queue). */
function get_pending_requests_for_center($pdo, $center_id) {
    $stmt = $pdo->prepare("SELECT dr.*, u.FirstName, u.LastName
                           FROM deposit_request dr
                           JOIN `user` u ON u.User_id = dr.User_id
                           WHERE dr.center_id = ? AND dr.status = 'Pending'
                           ORDER BY dr.request_date ASC");
    $stmt->execute([$center_id]);
    return $stmt->fetchAll();
}

/** Recently decided requests for one center (for the manager's history). */
function get_center_recent_decisions($pdo, $center_id, $limit = 10) {
    $stmt = $pdo->prepare("SELECT dr.request_id, dr.waste_type, dr.weight,
                                  dr.status, dr.handled_at,
                                  u.FirstName, u.LastName
                           FROM deposit_request dr
                           JOIN `user` u ON u.User_id = dr.User_id
                           WHERE dr.center_id = ?
                             AND dr.status IN ('Accepted','Rejected')
                           ORDER BY dr.handled_at DESC
                           LIMIT " . (int) $limit);
    $stmt->execute([$center_id]);
    return $stmt->fetchAll();
}

/**
 * Manager ACCEPTS a pending request.
 *
 * This is where the two features meet: we first check the deposit
 * fits inside the center's remaining capacity (same SUM as centers.php).
 * If it fits, the request becomes a real `deposit` row and the points
 * are paid out — and the center immediately looks fuller on centers.php.
 *
 * Returns [true, message] or [false, reason].
 */
function accept_deposit_request($pdo, $request_id, $manager_id, $center_id) {

    // Load the request and make sure it is really this center's to accept.
    $stmt = $pdo->prepare("SELECT * FROM deposit_request
                           WHERE request_id = ? AND center_id = ?");
    $stmt->execute([$request_id, $center_id]);
    $req = $stmt->fetch();

    if (!$req || $req['status'] != 'Pending') {
        return [false, "That request is no longer pending."];
    }

    // Capacity gate — the cross-feature check.
    $cap = get_center_capacity($pdo, $center_id);
    if ($req['weight'] > $cap['remaining']) {
        return [false, "Not enough space: only "
                       . number_format($cap['remaining'], 0)
                       . " kg left, but this deposit is "
                       . number_format($req['weight'], 0) . " kg. "
                       . "You can reject it or free up space."];
    }

    $pdo->beginTransaction();

    // Flip to Accepted. The status='Pending' guard stops a double-accept
    // if the manager somehow clicks twice; rowCount()==0 means someone/
    // something already handled it.
    $stmt = $pdo->prepare("UPDATE deposit_request
                           SET status = 'Accepted',
                               handled_by = ?, handled_at = NOW()
                           WHERE request_id = ? AND status = 'Pending'");
    $stmt->execute([$manager_id, $request_id]);

    if ($stmt->rowCount() == 0) {
        $pdo->rollBack();
        return [false, "That request was already handled."];
    }

    // Turn it into a permanent deposit (this is what fills the center)
    // and pay the user's points.
    $points = award_deposit_points($pdo, $req['User_id'], $req['weight'],
                                    $req['waste_type'], $center_id);

    $pdo->commit();

    return [true, "Accepted " . $request_id . ". "
                  . number_format($req['weight'], 0) . " kg added to the center; "
                  . $points . " points awarded to the user."];
}

/** Manager REJECTS a pending request. No deposit row, no points. */
function reject_deposit_request($pdo, $request_id, $manager_id, $center_id) {

    $stmt = $pdo->prepare("UPDATE deposit_request
                           SET status = 'Rejected',
                               handled_by = ?, handled_at = NOW()
                           WHERE request_id = ? AND center_id = ? AND status = 'Pending'");
    $stmt->execute([$manager_id, $request_id, $center_id]);

    if ($stmt->rowCount() == 0) {
        return [false, "That request is no longer pending."];
    }
    return [true, "Request " . $request_id . " was rejected."];
}


/* ------------------------------------------------------------
   THE POINTS ENGINE FOR DEPOSITS
   Mirrors award_pickup_points() in config/points.php step for step,
   the only difference being it records a center drop-off (center_id)
   instead of a pickup (pickup_id + recycler_id).
   ------------------------------------------------------------ */
function award_deposit_points($pdo, $user_id, $weight_kg, $waste_type, $center_id) {

    $points = points_for_weight($weight_kg);

    // 1. the permanent deposit record — this is what centers.php sums.
    //    Same TEMP -> deposit_seq -> DEPxx trick as the pickup flow.
    $stmt = $pdo->prepare("INSERT INTO deposit
                             (deposit_id, deposit_date, earned_points,
                              waste_type, weight, User_id, center_id)
                           VALUES ('TEMP', CURDATE(), ?, ?, ?, ?, ?)");
    $stmt->execute([$points, $waste_type, $weight_kg, $user_id, $center_id]);

    $seq        = $pdo->lastInsertId();
    $deposit_id = 'DEP' . str_pad($seq, 2, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("UPDATE deposit SET deposit_id = ? WHERE deposit_seq = ?");
    $stmt->execute([$deposit_id, $seq]);

    // 2. add points to the wallet
    $stmt = $pdo->prepare("UPDATE wallet
                           SET current_points = current_points + ?
                           WHERE User_id = ?");
    $stmt->execute([$points, $user_id]);

    // 3. add weight + points to the user, then recompute the badge
    $stmt = $pdo->prepare("UPDATE `user`
                           SET total_recycled       = total_recycled + ?,
                               current_badge_points = current_badge_points + ?
                           WHERE User_id = ?");
    $stmt->execute([$weight_kg, $points, $user_id]);

    $stmt = $pdo->prepare("SELECT current_badge_points FROM `user` WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $new_total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE `user` SET Badge_name = ? WHERE User_id = ?");
    $stmt->execute([badge_for_points($new_total), $user_id]);

    // 4. add points to the student's institute leaderboard (skipped for
    //    non-students, who have no student row).
    $stmt = $pdo->prepare("SELECT institute_EIIN FROM student WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $eiin = $stmt->fetchColumn();

    if ($eiin) {
        $stmt = $pdo->prepare("UPDATE edu_institute_stats
                               SET CumulativeEarnedPoints = CumulativeEarnedPoints + ?
                               WHERE institute_EIIN = ?");
        $stmt->execute([$points, $eiin]);
    }

    return $points;
}
