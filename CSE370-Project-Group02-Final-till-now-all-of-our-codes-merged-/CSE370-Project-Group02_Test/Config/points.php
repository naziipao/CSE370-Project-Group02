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
 * deposit_id is now generated safely using the same two-step
 * approach as Pickup_ID:
 *   - insert with a temporary placeholder so MySQL assigns deposit_seq
 *   - read deposit_seq back with lastInsertId() (session-scoped,
 *     never collides with another concurrent insert)
 *   - format as DEP01, DEP02 ... and write it back
 *
 * This replaces the old SELECT MAX(...) + 1 approach which had a
 * race condition: two simultaneous completions could read the same
 * MAX and try to insert the same deposit_id.
 */
function award_pickup_points($pdo, $user_id, $weight_kg, $waste_type,
                             $pickup_id, $recycler_id) {

    $points = points_for_weight($weight_kg);


    // --- 1. the deposit record ---

    // Step A: insert with a temporary deposit_id so MySQL can assign
    //         deposit_seq via AUTO_INCREMENT.
    $stmt = $pdo->prepare("INSERT INTO deposit
                             (deposit_id, deposit_date, earned_points,
                              waste_type, weight, User_id, pickup_id, recycler_id)
                           VALUES ('TEMP', CURDATE(), ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$points, $waste_type, $weight_kg,
                    $user_id, $pickup_id, $recycler_id]);

    // Step B: read the guaranteed-unique sequence number MySQL just gave.
    //         lastInsertId() is session-scoped inside MySQL — it always
    //         returns YOUR connection's own last insert, never another
    //         concurrent user's, so no collision is possible.
    $seq        = $pdo->lastInsertId();
    $deposit_id = 'DEP' . str_pad($seq, 2, '0', STR_PAD_LEFT);
    // gives DEP01, DEP02 ... DEP09, DEP10, DEP11 ...

    // Step C: write the formatted ID back.
    $stmt = $pdo->prepare("UPDATE deposit SET deposit_id = ? WHERE deposit_seq = ?");
    $stmt->execute([$deposit_id, $seq]);


    // --- 2. add points to the wallet ---
    $stmt = $pdo->prepare("UPDATE wallet
                           SET current_points = current_points + ?
                           WHERE User_id = ?");
    $stmt->execute([$points, $user_id]);


    // --- 3. add weight + points to the user, then recalculate badge ---
    $stmt = $pdo->prepare("UPDATE `user`
                           SET total_recycled       = total_recycled + ?,
                               current_badge_points = current_badge_points + ?
                           WHERE User_id = ?");
    $stmt->execute([$weight_kg, $points, $user_id]);

    // Re-read the new total so the badge always matches the real number.
    $stmt = $pdo->prepare("SELECT current_badge_points FROM `user` WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $new_total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("UPDATE `user` SET Badge_name = ? WHERE User_id = ?");
    $stmt->execute([badge_for_points($new_total), $user_id]);


    // --- 4. add points to the institute leaderboard, if a student ---
    // Non-students simply have no row in the student table, so
    // fetchColumn() returns false and nothing happens.
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