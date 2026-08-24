<?php
/* ============================================================
   config/points.php

   One small file that every points-related page includes, so
   the rules live in exactly one place and can never disagree
   between the dashboard, the deposit flow and the profile.

   Two rules only:
     1. how many points a weight of waste is worth
     2. which badge a points total earns
   ============================================================ */


// 10 Green Points for every 1 kg of recycled waste.
define('POINTS_PER_KG', 10);


/**
 * Points earned for a given weight.
 * round() keeps it a whole number even for weights like 12.5 kg.
 */
function points_for_weight($weight_kg) {
    return (int) round($weight_kg * POINTS_PER_KG);
}


/**
 * The badge that a points total earns.
 *
 *   Bronze    0    - 299
 *   Silver    300  - 599
 *   Gold      600  - 999
 *   Diamond   1000 and up
 */
function badge_for_points($points) {

    if ($points >= 1000) return 'Diamond';
    if ($points >= 600)  return 'Gold';
    if ($points >= 300)  return 'Silver';
    return 'Bronze';
}


/**
 * Award a completed pickup's points to everyone they touch.
 *
 * Call this ONCE, inside a transaction, when a pickup becomes
 * "Collected". It does four things:
 *
 *   1. writes a row in the deposit table (the permanent record)
 *   2. adds the points to the user's wallet
 *   3. adds the weight and points to the user, and updates the badge
 *   4. adds the points to the student's institute leaderboard total
 *
 * $pdo             - the database connection
 * $user_id         - who recycled
 * $weight_kg       - how much
 * $waste_type      - e.g. "Plastic"
 * $pickup_id       - the pickup this came from
 * $recycler_id     - who collected it
 */
function award_pickup_points($pdo, $user_id, $weight_kg, $waste_type,
                             $pickup_id, $recycler_id) {

    $points = points_for_weight($weight_kg);

    // --- 1. the deposit record ---
    // Build the next deposit_id: DEP001, DEP002, ...
    $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(deposit_id, 4) AS UNSIGNED))
                         FROM deposit");
    $next       = ((int) $stmt->fetchColumn()) + 1;
    $deposit_id = 'DEP' . str_pad($next, 3, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("INSERT INTO deposit
                             (deposit_id, deposit_date, earned_points,
                              waste_type, weight, User_id, pickup_id, recycler_id)
                           VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$deposit_id, $points, $waste_type, $weight_kg,
                    $user_id, $pickup_id, $recycler_id]);

    // --- 2. add points to the wallet ---
    $stmt = $pdo->prepare("UPDATE wallet
                           SET current_points = current_points + ?
                           WHERE User_id = ?");
    $stmt->execute([$points, $user_id]);

    // --- 3. add weight + points to the user, then set the badge ---
    $stmt = $pdo->prepare("UPDATE `user`
                           SET total_recycled       = total_recycled + ?,
                               current_badge_points = current_badge_points + ?
                           WHERE User_id = ?");
    $stmt->execute([$weight_kg, $points, $user_id]);

    // Re-read the new total so the badge matches it.
    $stmt = $pdo->prepare("SELECT current_badge_points FROM `user` WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $new_total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE `user` SET Badge_name = ? WHERE User_id = ?");
    $stmt->execute([badge_for_points($new_total), $user_id]);

    // --- 4. add points to the institute leaderboard, if a student ---
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