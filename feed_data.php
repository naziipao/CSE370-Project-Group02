<?php
/* ============================================================
   feed_data.php   —   BACKEND for the Dashboard Activity Feed

   FEATURE 3 (Aurpita): a scrollable "what's happening right now"
   timeline on the dashboard, styled like a social media feed.
   Not a notification system - nothing pops up, nothing needs to
   be marked read. It is just a live list.

   THE KEY IDEA
   ------------
   Every completed pickup AND every accepted center deposit already
   ends by inserting one row into the `deposit` table - that is
   exactly what award_pickup_points() and award_deposit_points()
   both do, in config/points.php and deposit_data.php. So the feed
   needs NO new table, NO new column, and NO code added anywhere
   else in the app. It only has to read `deposit` and show it nicely.

   ORDERING WITHOUT A TIMESTAMP
   -----------------------------
   deposit_date is a DATE, not a DATETIME - it has no time of day.
   So two deposits made an hour apart on the same day would look
   "equally recent" if we only sorted by date. deposit_seq (the
   AUTO_INCREMENT column added earlier purely to generate safe
   DEP01/DEP02 IDs) always increases with every insert, so ordering
   by (deposit_date, deposit_seq) both DESC gives the true
   chronological order for free - no schema change needed.

   WASTE TYPE LABELS
   ------------------
   pickup.php's dropdown and deposit.php's dropdown now both use the
   exact same string, "Household (Mixed)", for this category, so
   every NEW row goes into the deposit table clean. The other entries
   in the map below - "Home", "Household Mixed", "Household(Mixed)" -
   are earlier strings this category was saved under before both
   dropdowns were made to match. Those old rows already exist in the
   database with whatever string was live at the time; a dropdown fix
   never rewrites rows that were already saved. feed_waste_label()
   maps all of them to one clean display label - display only,
   nothing stored in the database changes here.
   ============================================================ */


/**
 * The most recent activity across the WHOLE platform - not just
 * the logged-in user. Reads like a live community feed: "who
 * recycled what, just now" - not a private history list (that
 * already exists as "Past Requests" on pickup.php / deposit.php).
 *
 * Each row: name, institute (if a student), badge, waste type,
 * weight, points earned, and how long ago it happened.
 */
function get_recent_activity($pdo, $limit = 20) {

    $stmt = $pdo->prepare("
        SELECT  d.deposit_id,
                d.deposit_date,
                d.earned_points,
                d.waste_type,
                d.weight,
                u.FirstName,
                u.LastName,
                u.Badge_name,
                e.Institute_name
        FROM        deposit d
        JOIN        `user` u ON u.User_id = d.User_id
        LEFT JOIN   student s ON s.User_id = u.User_id
        LEFT JOIN   edu_institute_stats e ON e.institute_EIIN = s.institute_EIIN
        ORDER BY    d.deposit_date DESC, d.deposit_seq DESC
        LIMIT " . (int) $limit
    );
    $stmt->execute();
    $rows = $stmt->fetchAll();

    // Turn each raw row into exactly what the page will print, so
    // dashboard.php stays a plain loop with no logic inside it.
    $feed = [];
    foreach ($rows as $r) {

        $feed[] = [
            'name'       => trim($r['FirstName'] . ' ' . $r['LastName']),
            'institute'  => $r['Institute_name'],   // null for non-students
            'badge'      => $r['Badge_name'] ?: 'Bronze',
            'waste_type' => feed_waste_label($r['waste_type']),
            'icon'       => feed_waste_icon($r['waste_type']),
            'weight'     => (float) $r['weight'],
            'points'     => (int) $r['earned_points'],
            'time_label' => feed_time_label($r['deposit_date']),
            'accent'     => feed_badge_accent($r['Badge_name']),
        ];
    }
    return $feed;
}


/**
 * Turns whatever raw value is stored in waste_type into one clean
 * label. "Household (Mixed)" is the current, correct value both
 * dropdowns now share - the other keys only exist to cover rows
 * saved before that fix. Display only - the database value itself
 * is never touched here.
 */
function feed_waste_label($waste_type) {

    $labels = [
        'Household (Mixed)'   => 'Household Waste',   // current
        'Home'                => 'Household Waste',   // old value
        'Plastic'              => 'Plastic',
        'Paper'                => 'Paper',
        'Glass'                => 'Glass',
        'Metal'                => 'Metal',
        'E-waste'               => 'Electronic Waste',
    ];

    return $labels[$waste_type] ?? $waste_type;
}


/** One emoji per waste type, so the feed is easy to scan at a glance. */
function feed_waste_icon($waste_type) {

    $icons = [
        'Household (Mixed)'  => '🗑️',
        'Home'               => '🗑️',
        'Household Mixed'    => '🗑️',
        'Household(Mixed)'   => '🗑️',
        'Plastic'             => '🥤',
        'Paper'               => '📄',
        'Glass'               => '🍾',
        'Metal'               => '🥫',
        'E-waste'              => '🔌',
    ];

    // Anything else falls back to the generic recycle icon
    // rather than showing nothing.
    return $icons[$waste_type] ?? '♻️';
}


/**
 * A friendly "how long ago" label from a DATE-only column.
 * Deliberately day-granularity ("Today", "Yesterday", "3 days ago")
 * since deposit_date has no time of day to be more precise with.
 */
function feed_time_label($date_str) {

    if (!$date_str) return '';

    $days = (int) ((strtotime(date('Y-m-d')) - strtotime($date_str)) / 86400);

    if ($days <= 0) return 'Today';
    if ($days == 1) return 'Yesterday';
    if ($days < 7)  return $days . ' days ago';

    return date('M j', strtotime($date_str));   // e.g. "Aug 14"
}


/** One accent colour per badge tier, so the feed doubles as a
 *  visual reminder of the badge system elsewhere in the app. */
function feed_badge_accent($badge_name) {

    $colors = [
        'Bronze'  => '#cd7f32',
        'Silver'  => '#cac3c3',
        'Gold'    => '#f5b942',
        'Diamond' => '#74c69d',
    ];

    return $colors[$badge_name] ?? $colors['Bronze'];
}
