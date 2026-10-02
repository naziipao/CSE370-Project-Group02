<?php
/* ============================================================
   rankings_data.php   —   BACK-END ENDPOINT (data only, no HTML)

   The page (rankings.php) fetches this file every few seconds.
   It runs ONE query, ranks the institutes, and returns JSON.
   This is the only file in the feature that touches the database.

   Output shape:
   {
     "ok": true,
     "my_eiin": "100001",          // the logged-in student's institute (or null)
     "updated_at": "14:32:05",     // server time, shown as "last updated"
     "institutes": [
       { "rank":1, "eiin":"100009", "name":"Rajshahi College",
         "points":23100, "is_me":false },
       ...
     ]
   }
   ============================================================ */

require_once "config/auth.php";        // starts the session, gives require_login()
require_once "config/DBconnect.php";   // gives us $pdo

require_login();                       // no login = no data

// Safety net: if any included file accidentally echoes a stray
// space, warning, or blank line before this point, it would sit
// in front of our JSON and make the browser's res.json() fail
// silently (the page just gets stuck on "Loading..." forever).
// Throwing away any such output here means this endpoint always
// returns clean JSON and nothing else.
if (ob_get_length()) {
    ob_clean();
}

// This is an API response, not a web page: tell the browser it's JSON.
header('Content-Type: application/json; charset=utf-8');

$user_id = $_SESSION['user_id'];

try {

    /* ---- 1. Which institute does the logged-in user belong to? ----
       Used only to highlight their row. Non-students return nothing,
       which is fine — nothing gets highlighted.                       */
    $my_eiin = null;
    $stmt = $pdo->prepare("SELECT institute_EIIN FROM student WHERE User_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    if ($row && !empty($row['institute_EIIN'])) {
        $my_eiin = $row['institute_EIIN'];
    }

    /* ---- 2. Get every institute, best score first ---- */
    $sql = "SELECT institute_EIIN, Institute_name, CumulativeEarnedPoints
            FROM edu_institute_stats
            ORDER BY CumulativeEarnedPoints DESC, Institute_name ASC";
    $rows = $pdo->query($sql)->fetchAll();

    /* ---- 3. Attach a rank number (1, 2, 3, ...) in PHP ---- */
    $institutes = [];
    $rank = 1;
    foreach ($rows as $r) {
        $eiin = $r['institute_EIIN'];
        $institutes[] = [
            'rank'   => $rank,
            'eiin'   => $eiin,
            'name'   => $r['Institute_name'],
            'points' => (int) $r['CumulativeEarnedPoints'],
            'is_me'  => ($eiin === $my_eiin),
        ];
        $rank++;
    }

    echo json_encode([
        'ok'         => true,
        'my_eiin'    => $my_eiin,
        'updated_at' => date('H:i:s'),
        'institutes' => $institutes,
    ]);

} catch (Exception $e) {
    // Never crash the page: return a clean error the JS can handle.
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Could not load rankings right now.',
    ]);
}
