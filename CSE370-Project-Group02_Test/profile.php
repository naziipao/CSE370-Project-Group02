<?php
session_start();
require_once 'config/DBconnect.php'; // Adjust this path to your actual DB connection file

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// ---------------------------------------------------------
// HELPER: CALCULATE BADGE BASED ON POINTS
// ---------------------------------------------------------
function calculateBadge($points) {
    if ($points >= 1000) return 'Diamond';
    if ($points >= 600)  return 'Gold';
    if ($points >= 300)  return 'Silver';
    return 'Bronze';
}

// ---------------------------------------------------------
// 1. POST ACTION: UPDATE PROFILE DETAILS
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_profile'])) {
    $dob           = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $gender        = !empty($_POST['gender']) ? $_POST['gender'] : null;
    $street        = !empty($_POST['street']) ? $_POST['street'] : null;
    $city          = !empty($_POST['city']) ? $_POST['city'] : null;
    $phone_num     = !empty($_POST['phone']) ? trim($_POST['phone']) : null;
    $user_type     = $_POST['user_type'] ?? 'non_student';

    try {
        $pdo->beginTransaction();

        // Update core user record
        $stmt = $pdo->prepare("UPDATE user SET DOB = ?, Gender = ?, StreetAddress = ?, City = ? WHERE User_id = ?");
        $stmt->execute([$dob, $gender, $street, $city, $user_id]);

        // Update or insert phone
        if ($phone_num) {
            $stmt = $pdo->prepare("INSERT INTO phone (User_id, Phone) VALUES (?, ?) ON DUPLICATE KEY UPDATE Phone = ?");
            $stmt->execute([$user_id, $phone_num, $phone_num]);
        }

        // Handle Student / Non-Student specifics
        if ($user_type === 'student') {
            $student_id = !empty($_POST['student_id']) ? $_POST['student_id'] : null;
            $eiin       = !empty($_POST['eiin']) ? $_POST['eiin'] : null;
            $edu_level  = !empty($_POST['edu_level']) ? $_POST['edu_level'] : null;

            $stmt = $pdo->prepare("INSERT INTO student (User_id, Student_id, institute_EIIN) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE Student_id=?, institute_EIIN=?");
            $stmt->execute([$user_id, $student_id, $eiin, $student_id, $eiin]);

            if ($edu_level) {
                $stmt = $pdo->prepare("INSERT INTO student_edu_level (User_id, Education_Level) VALUES (?, ?) ON DUPLICATE KEY UPDATE Education_Level=?");
                $stmt->execute([$user_id, $edu_level, $edu_level]);
            }
        } else {
            $nid        = !empty($_POST['nid']) ? $_POST['nid'] : null;
            $occupation = !empty($_POST['occupation']) ? $_POST['occupation'] : null;

            $stmt = $pdo->prepare("INSERT INTO non_student (User_id, NID, Occupation) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE NID=?, Occupation=?");
            $stmt->execute([$user_id, $nid, $occupation, $nid, $occupation]);
        }

        $pdo->commit();
        $message = "Profile details updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update profile: " . $e->getMessage();
    }
}

// ---------------------------------------------------------
// 2. POST ACTION: TRANSFER POINTS TO FRIEND
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_transfer_points'])) {
    $receiver_identifier = trim($_POST['friend_identifier']);
    $transfer_points     = intval($_POST['points']);

    if ($transfer_points <= 0) {
        $error = "Please enter a valid amount of points to transfer.";
    } else {
        try {
            // Find receiver user by Email or User_id
            $stmt = $pdo->prepare("SELECT User_id, FirstName, LastName FROM user WHERE Email = ? OR User_id = ?");
            $stmt->execute([$receiver_identifier, $receiver_identifier]);
            $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$receiver) {
                $error = "User not found. Please check the Email or User ID.";
            } elseif ($receiver['User_id'] == $user_id) {
                $error = "You cannot transfer points to yourself.";
            } else {
                // Get current sender points
                $stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ? LIMIT 1");
                $stmt->execute([$user_id]);
                $sender_wallet = $stmt->fetch(PDO::FETCH_ASSOC);
                $sender_points = $sender_wallet ? intval($sender_wallet['current_points']) : 0;

                if ($sender_points < $transfer_points) {
                    $error = "Insufficient balance! You only have {$sender_points} points.";
                } else {
                    $pdo->beginTransaction();
                    $receiver_id = $receiver['User_id'];

                    // Deduct from Sender
                    $new_sender_pts = $sender_points - $transfer_points;
                    $sender_badge   = calculateBadge($new_sender_pts);

                    $stmt = $pdo->prepare("UPDATE wallet SET current_points = ? WHERE User_id = ?");
                    $stmt->execute([$new_sender_pts, $user_id]);

                    $stmt = $pdo->prepare("UPDATE user SET current_badge_points = ?, Badge_name = ? WHERE User_id = ?");
                    $stmt->execute([$new_sender_pts, $sender_badge, $user_id]);

                    // Add to Receiver
                    $stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ? LIMIT 1");
                    $stmt->execute([$receiver_id]);
                    $receiver_wallet = $stmt->fetch(PDO::FETCH_ASSOC);
                    $new_recv_pts = ($receiver_wallet ? intval($receiver_wallet['current_points']) : 0) + $transfer_points;
                    $recv_badge   = calculateBadge($new_recv_pts);

                    $stmt = $pdo->prepare("UPDATE wallet SET current_points = ? WHERE User_id = ?");
                    $stmt->execute([$new_recv_pts, $receiver_id]);

                    $stmt = $pdo->prepare("UPDATE user SET current_badge_points = ?, Badge_name = ? WHERE User_id = ?");
                    $stmt->execute([$new_recv_pts, $recv_badge, $receiver_id]);

                    $pdo->commit();
                    $message = "Successfully transferred {$transfer_points} points to {$receiver['FirstName']} {$receiver['LastName']}!";
                }
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transfer failed: " . $e->getMessage();
        }
    }
}

// ---------------------------------------------------------
// 3. FETCH USER DATA FOR DISPLAY
// ---------------------------------------------------------
$stmt = $pdo->prepare("SELECT * FROM user WHERE User_id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Phone FROM phone WHERE User_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$phone_data = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT current_points FROM wallet WHERE User_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$wallet_data = $stmt->fetch(PDO::FETCH_ASSOC);
$current_points = $wallet_data['current_points'] ?? $user_data['current_badge_points'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM student WHERE User_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$student_data = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT Education_Level FROM student_edu_level WHERE User_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$edu_level_data = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM non_student WHERE User_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$non_student_data = $stmt->fetch(PDO::FETCH_ASSOC);

$is_student = !empty($student_data['Student_id']);

// Check missing required profile details
$is_incomplete = empty($user_data['DOB']) || empty($user_data['City']) || empty($user_data['StreetAddress']) || empty($phone_data['Phone']);

// Setup header variables
$page_title = "User Profile";
$page_css   = "profile.css"; // This tells header.php to load CSS/profile.css
require_once 'header.php';
?>

<div class="dashboard-header">
    <h2>User Profile</h2>
    <p>Manage your account details and transfer points.</p>
</div>

<div class="profile-container">

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($is_incomplete): ?>
        <div class="alert alert-warning">
            <strong>Action Required:</strong> Please complete your profile details below to unlock all features.
        </div>
    <?php endif; ?>

    <!-- PROFILE OVERVIEW & BADGE DISPLAY -->
    <div class="profile-card">
        <h3 class="profile-name"><i class="fa-solid fa-circle-user"></i> <?= htmlspecialchars($user_data['FirstName'] . ' ' . $user_data['LastName']); ?></h3>
        <div class="grid-2">
            <div class="user-info-text">
                <p><strong>User ID:</strong> <?= htmlspecialchars($user_data['User_id']); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($user_data['Email']); ?></p>
            </div>
            <div class="wallet-box">
                <p class="wallet-title">Available Points</p>
                <h2 class="wallet-points"><?= $current_points; ?></h2>
                <p class="wallet-tier">
                    Tier: <span><?= calculateBadge($current_points); ?></span>
                </p>
            </div>
        </div>
    </div>

    <!-- FEATURE: TRANSFER POINTS TO A FRIEND -->
    <div class="profile-card">
        <h3 class="card-heading"><i class="fa-solid fa-hand-holding-hand"></i> Transfer Points (Sharing is CARING)</h3>
        <p class="card-subheading">Share your earned recycling points with others who need them.</p>

        <form method="POST" action="profile.php" class="transfer-form">
            <input type="hidden" name="action_transfer_points" value="1">
            <div class="form-group flex-2">
                <label>Point Receiver's E-mail or User ID:</label>
                <input type="text" name="friend_identifier" required placeholder="e.g. friend@gmail.com or 208595783">
            </div>
            <div class="form-group flex-1">
                <label>Points to Send:</label>
                <input type="number" name="points" min="1" max="<?= $current_points; ?>" required placeholder="Amount">
            </div>
            <div class="transfer-btn-container">
                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-paper-plane"></i> Send</button>
            </div>
        </form>
    </div>

    <!-- MANDATORY PROFILE EDIT FORM -->
    <div class="profile-card">
        <h3 class="card-heading"><i class="fa-solid fa-pen-to-square"></i> Edit Personal Information</h3>
        <form method="POST" action="profile.php">
            <input type="hidden" name="action_update_profile" value="1">

            <div class="grid-2">
                <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" name="dob" value="<?= htmlspecialchars($user_data['DOB'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Gender:</label>
                    <select name="gender">
                        <option value="Male" <?= ($user_data['Gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?= ($user_data['Gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?= ($user_data['Gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Street Address:</label>
                    <input type="text" name="street" value="<?= htmlspecialchars($user_data['StreetAddress'] ?? ''); ?>" required placeholder="House/Road No.">
                </div>
                <div class="form-group">
                    <label>City:</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($user_data['City'] ?? ''); ?>" required placeholder="City Name">
                </div>
            </div>

            <div class="form-group">
                <label>Phone Number:</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($phone_data['Phone'] ?? ''); ?>" required placeholder="017xxxxxxxx">
            </div>

            <hr class="divider">

            <div class="form-group radio-group">
                <label>Account Category:</label>
                <label class="radio-label">
                    <input type="radio" name="user_type" value="student" <?= $is_student ? 'checked' : ''; ?> onclick="toggleCategory('student')"> Student
                </label>
                <label class="radio-label">
                    <input type="radio" name="user_type" value="non_student" <?= !$is_student ? 'checked' : ''; ?> onclick="toggleCategory('non_student')"> Non-Student
                </label>
            </div>

            <!-- STUDENT FIELDS -->
            <div id="student_fields" style="display: <?= $is_student ? 'block' : 'none'; ?>;">
                <div class="grid-3">
                    <div class="form-group">
                        <label>Student ID:</label>
                        <input type="text" name="student_id" value="<?= htmlspecialchars($student_data['Student_id'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Institute EIIN:</label>
                        <input type="text" name="eiin" value="<?= htmlspecialchars($student_data['institute_EIIN'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Education Level:</label>
                        <input type="text" name="edu_level" value="<?= htmlspecialchars($edu_level_data['Education_Level'] ?? ''); ?>" placeholder="Undergraduate/Secondary">
                    </div>
                </div>
            </div>

            <!-- NON-STUDENT FIELDS -->
            <div id="non_student_fields" style="display: <?= !$is_student ? 'block' : 'none'; ?>;">
                <div class="grid-2">
                    <div class="form-group">
                        <label>NID:</label>
                        <input type="text" name="nid" value="<?= htmlspecialchars($non_student_data['NID'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Occupation:</label>
                        <input type="text" name="occupation" value="<?= htmlspecialchars($non_student_data['Occupation'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Profile Details</button>
        </form>
    </div>
</div>

<script>
function toggleCategory(type) {
    document.getElementById('student_fields').style.display = type === 'student' ? 'block' : 'none';
    document.getElementById('non_student_fields').style.display = type === 'non_student' ? 'block' : 'none';
}
</script>

    </main>
  </div> <!-- end app-container -->
</body>
</html>