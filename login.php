<?php
/* ============================================================
   login.php

   One login form serves THREE kinds of account:
     - normal user    -> user table     -> dashboard.php
     - recycler       -> recycler table -> recycler_requests.php
     - center manager -> center_manager table -> center_manager.php

   Visual: dark "glazing" gradient + spinning recycle icon on submit.
   ============================================================ */


/* ===== 1. SETUP ===== */

require_once "config/DBconnect.php";
require_once "config/auth.php";

// Already logged in? Send them to the right home page.
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}
if (isset($_SESSION['recycler_id'])) {
    header("Location: recycler_requests.php");
    exit;
}
if (isset($_SESSION['manager_id'])) {
    header("Location: center_manager.php");
    exit;
}

$show = (isset($_GET['form']) && $_GET['form'] == 'signup') ? 'signup' : 'login';

$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
unset($_SESSION['old']);


/* ===== 2. HANDLE THE FORMS ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];


    /* ---------- SIGN UP ---------- */

    if ($action == 'signup') {

        $role       = $_POST['role'];
        $first_name = trim($_POST['first_name']);
        $last_name  = trim($_POST['last_name']);
        $email      = trim($_POST['email']);
        $phone      = trim($_POST['phone']);
        $password   = $_POST['password'];
        $dob        = $_POST['dob'];
        $gender     = $_POST['gender'];
        $street     = trim($_POST['street_address']);
        $city       = trim($_POST['city']);

        $student_id      = trim($_POST['student_id']);
        $institute_eiin  = $_POST['institute_eiin'];
        $education_level = $_POST['education_level'];
        $new_inst_name   = trim($_POST['new_institute_name']);
        $new_inst_eiin   = trim($_POST['new_institute_eiin']);

        $nid        = trim($_POST['nid']);
        $occupation = trim($_POST['occupation']);

        $_SESSION['old'] = [
            'first_name'         => $first_name,
            'last_name'          => $last_name,
            'email'              => $email,
            'phone'              => $phone,
            'role'               => $role,
            'dob'                => $dob,
            'gender'             => $gender,
            'street_address'     => $street,
            'city'               => $city,
            'student_id'         => $student_id,
            'institute_eiin'     => $institute_eiin,
            'education_level'    => $education_level,
            'new_institute_name' => $new_inst_name,
            'new_institute_eiin' => $new_inst_eiin,
            'nid'                => $nid,
            'occupation'         => $occupation
        ];

        $final_eiin       = null;
        $create_institute = false;

        if ($role == 'Student') {

            if ($institute_eiin == '__new__') {

                if ($new_inst_name == '' || $new_inst_eiin == '') {
                    set_flash("Please enter both the institute name and its EIIN.");
                    header("Location: login.php?form=signup");
                    exit;
                }

                if (strlen($new_inst_eiin) > 20) {
                    set_flash("An EIIN can be at most 20 characters.");
                    header("Location: login.php?form=signup");
                    exit;
                }

                $stmt = $pdo->prepare("SELECT institute_EIIN FROM edu_institute_stats
                                       WHERE institute_EIIN = ?");
                $stmt->execute([$new_inst_eiin]);

                $final_eiin       = $new_inst_eiin;
                $create_institute = !$stmt->fetch();

            } elseif ($institute_eiin != '') {
                $final_eiin = $institute_eiin;
            } else {
                set_flash("Please choose your institute, or add it if it is not listed.");
                header("Location: login.php?form=signup");
                exit;
            }
        }

        // Email must be free in ALL THREE tables.
        $stmt  = $pdo->prepare("SELECT User_id FROM `user` WHERE Email = ?");
        $stmt->execute([$email]);
        $stmt2 = $pdo->prepare("SELECT Recycler_ID FROM recycler WHERE email = ?");
        $stmt2->execute([$email]);
        $stmt3 = $pdo->prepare("SELECT Manager_ID FROM center_manager WHERE email = ?");
        $stmt3->execute([$email]);

        if ($stmt->fetch() || $stmt2->fetch() || $stmt3->fetch()) {
            set_flash("This email is already registered.");
            header("Location: login.php?form=signup");
            exit;
        }

        do {
            $user_id = random_int(100000000, 999999999);
            $stmt = $pdo->prepare("SELECT User_id FROM `user` WHERE User_id = ?");
            $stmt->execute([$user_id]);
        } while ($stmt->fetch());

        $pdo->beginTransaction();

        if ($create_institute) {
            $stmt = $pdo->prepare("INSERT INTO edu_institute_stats
                                     (institute_EIIN, Institute_name, CumulativeEarnedPoints)
                                   VALUES (?, ?, 0)");
            $stmt->execute([$final_eiin, $new_inst_name]);
        }

        $stmt = $pdo->prepare("INSERT INTO `user`
                                 (User_id, FirstName, LastName, Email, Pin,
                                  DOB, Gender, StreetAddress, City)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $first_name, $last_name, $email, $password,
                        $dob, $gender, $street, $city]);

        $stmt = $pdo->prepare("INSERT INTO phone (User_id, Phone) VALUES (?, ?)");
        $stmt->execute([$user_id, $phone]);

        if ($role == 'Student') {

            $stmt = $pdo->prepare("INSERT INTO student
                                     (User_id, Student_id, institute_EIIN)
                                   VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $student_id, $final_eiin]);

            if ($education_level != '') {
                $stmt = $pdo->prepare("INSERT INTO student_edu_level
                                         (User_id, Education_Level)
                                       VALUES (?, ?)");
                $stmt->execute([$user_id, $education_level]);
            }

        } else {
            $stmt = $pdo->prepare("INSERT INTO non_student
                                     (User_id, NID, Occupation)
                                   VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $nid, $occupation]);
        }

        $stmt = $pdo->prepare("INSERT INTO wallet
                                 (wallet_id, current_points, User_id, transaction_date)
                               VALUES (?, 0, ?, CURDATE())");
        $stmt->execute(["W" . $user_id, $user_id]);

        $pdo->commit();

        unset($_SESSION['old']);

        if ($create_institute) {
            set_flash("Account created, and " . $new_inst_name
                      . " was added to the leaderboard. You can log in now.");
        } else {
            set_flash("Account created successfully. You can log in now.");
        }

        header("Location: login.php");
        exit;
    }


    /* ---------- LOG IN ---------- */

    if ($action == 'login') {

        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        // --- 1. try the user table ---
        $stmt = $pdo->prepare("SELECT User_id, FirstName FROM `user`
                               WHERE Email = ? AND Pin = ?");
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user_id']     = $user['User_id'];
            $_SESSION['user_name']   = $user['FirstName'];
            $_SESSION['last_active'] = time();
            session_regenerate_id(true);
            set_flash("Login successful. Welcome back, " . $user['FirstName'] . "!");
            header("Location: dashboard.php");
            exit;
        }

        // --- 2. try the recycler table ---
        $stmt = $pdo->prepare("SELECT Recycler_ID, name, city FROM recycler
                               WHERE email = ? AND password = ?");
        $stmt->execute([$email, $password]);
        $recycler = $stmt->fetch();

        if ($recycler) {
            $_SESSION['recycler_id']   = $recycler['Recycler_ID'];
            $_SESSION['recycler_name'] = $recycler['name'];
            $_SESSION['recycler_city'] = $recycler['city'];
            $_SESSION['last_active']   = time();
            session_regenerate_id(true);
            set_flash("Welcome back, " . $recycler['name'] . "!");
            header("Location: recycler_requests.php");
            exit;
        }

        // --- 3. try the center manager table ---
        // JOIN collection_center so the sidebar can greet them with
        // their center's name without a second query.
        $stmt = $pdo->prepare("SELECT m.Manager_ID, m.name, m.Center_ID,
                                      c.Center_name
                               FROM center_manager m
                               JOIN collection_center c ON c.Center_ID = m.Center_ID
                               WHERE m.email = ? AND m.password = ?");
        $stmt->execute([$email, $password]);
        $manager = $stmt->fetch();

        if ($manager) {
            $_SESSION['manager_id']          = $manager['Manager_ID'];
            $_SESSION['manager_name']        = $manager['name'];
            $_SESSION['manager_center']      = $manager['Center_ID'];
            $_SESSION['manager_center_name'] = $manager['Center_name'];
            $_SESSION['last_active']         = time();
            session_regenerate_id(true);
            set_flash("Welcome back, " . $manager['name'] . "!");
            header("Location: center_manager.php");
            exit;
        }

        // --- no table matched ---
        $_SESSION['old'] = ['email' => $email];
        set_flash("Invalid email or password.");
        header("Location: login.php");
        exit;
    }
}


/* ===== 3. LOAD THE DATA THE FORM NEEDS ===== */

$institutes = $pdo->query("SELECT institute_EIIN, Institute_name
                           FROM edu_institute_stats
                           ORDER BY Institute_name ASC")->fetchAll();

$today = date('Y-m-d');


/* ===== 4. THE PAGE ===== */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Smart Circular Recycling Platform - Authentication</title>
  <link rel="stylesheet" href="CSS/login.css">
</head>
<body>

  <div class="auth-container">

    <!-- Loading overlay shown while the recycle icon spins on submit -->
    <div id="loadingOverlay" class="loading-overlay">
      <svg class="animated-recycle-icon" viewBox="0 0 24 24">
        <path d="M12 2L9.5 6.5H14.5L12 2ZM4.5 10.5L2 15H7L4.5 10.5ZM19.5 10.5L17 15H22L19.5 10.5ZM10.2 8.5L7.7 13H11.2L9.7 10.3L10.2 8.5ZM13.8 8.5L14.3 10.3L12.8 13H16.3L13.8 8.5ZM8.5 16.5L6 21H18L15.5 16.5H8.5Z"/>
      </svg>
      <p id="overlayText" class="loading-text">Signing in...</p>
      <p class="loading-subtext">Connecting to Circular Campus Network</p>
    </div>

    <div class="brand-header">
      <svg id="brandIcon" class="brand-icon" viewBox="0 0 24 24">
        <path d="M12 2L9.5 6.5H14.5L12 2ZM4.5 10.5L2 15H7L4.5 10.5ZM19.5 10.5L17 15H22L19.5 10.5ZM10.2 8.5L7.7 13H11.2L9.7 10.3L10.2 8.5ZM13.8 8.5L14.3 10.3L12.8 13H16.3L13.8 8.5ZM8.5 16.5L6 21H18L15.5 16.5H8.5Z"/>
      </svg>
      <h1 class="brand-title">Smart Circular Recycling</h1>
      <p class="brand-subtitle">Environmental Sustainability &amp; Recycling Platform</p>
    </div>

    <div class="form-content">

      <?php show_flash(); ?>

      <!-- ================= LOGIN FORM ================= -->
      <form id="loginForm"
            class="auth-form <?= $show == 'login' ? 'active' : '' ?>"
            action="login.php" method="POST"
            onsubmit="handleAuth(event, 'Logging in...')">

        <input type="hidden" name="action" value="login">

        <h2 class="form-title">Welcome Back!</h2>

        <div class="form-group">
          <label for="loginEmail">Email Address</label>
          <input type="email" id="loginEmail" name="email"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                 placeholder="user@domain.com" required />
        </div>

        <div class="form-group">
          <label for="loginPassword">Password</label>
          <input type="password" id="loginPassword" name="password"
                 placeholder="••••••••" required />
        </div>

        <p class="toggle-text">
          Not registered yet?
          <a class="toggle-link" href="login.php?form=signup">Sign Up</a>
        </p>

        <button type="submit" class="btn-submit" style="margin-top: 20px;">Log In</button>

        <p class="recycler-hint">
          🚛 Recyclers and 🗄️ Center Managers sign in here too.
        </p>
      </form>

      <!-- ================= SIGNUP FORM ================= -->
      <form id="signupForm"
            class="auth-form <?= $show == 'signup' ? 'active' : '' ?>"
            action="login.php" method="POST"
            onsubmit="handleAuth(event, 'Creating Account...')">

        <input type="hidden" name="action" value="signup">

        <h2 class="form-title">Create Account</h2>

        <div class="form-group">
          <label>Select Role</label>
          <div class="role-selection">
            <div class="role-option">
              <input type="radio" id="roleStudent" name="role" value="Student"
                     <?= ($old['role'] ?? 'Student') == 'Student' ? 'checked' : '' ?> />
              <label for="roleStudent" class="role-label">Student</label>
            </div>
            <div class="role-option">
              <input type="radio" id="roleNonStudent" name="role" value="Non-Student"
                     <?= ($old['role'] ?? '') == 'Non-Student' ? 'checked' : '' ?> />
              <label for="roleNonStudent" class="role-label">Non-Student</label>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="first_name"
                   value="<?= htmlspecialchars($old['first_name'] ?? '') ?>"
                   placeholder="John" required />
          </div>
          <div class="form-group">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="last_name"
                   value="<?= htmlspecialchars($old['last_name'] ?? '') ?>"
                   placeholder="Doe" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="signupDob">Date of Birth</label>
            <input type="date" id="signupDob" name="dob"
                   value="<?= htmlspecialchars($old['dob'] ?? '') ?>"
                   max="<?= $today ?>" required />
          </div>
          <div class="form-group">
            <label for="signupGender">Gender</label>
            <select id="signupGender" name="gender" required>
              <option value="">Select...</option>
              <?php foreach (['Male','Female','Other','Prefer not to say'] as $g): ?>
                <option value="<?= $g ?>"
                  <?= ($old['gender'] ?? '') == $g ? 'selected' : '' ?>>
                  <?= $g ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label for="signupEmail">Email Address</label>
          <input type="email" id="signupEmail" name="email"
                 value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                 placeholder="john.doe@university.edu" required />
        </div>

        <div class="form-group">
          <label for="signupPhone">Phone Number</label>
          <input type="tel" id="signupPhone" name="phone"
                 value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                 placeholder="+880 1700-000000" required />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="signupStreet">Street Address</label>
            <input type="text" id="signupStreet" name="street_address"
                   value="<?= htmlspecialchars($old['street_address'] ?? '') ?>"
                   placeholder="Town Hall Road" />
          </div>
          <div class="form-group">
            <label for="signupCity">City</label>
            <input type="text" id="signupCity" name="city"
                   value="<?= htmlspecialchars($old['city'] ?? '') ?>"
                   placeholder="Dhaka" />
          </div>
        </div>

        <!-- STUDENT ONLY: hidden by CSS when Non-Student is selected -->
        <div class="role-fields student-only">

          <p class="section-label">Student Details</p>

          <div class="form-group">
            <label for="signupInstitute">Institute</label>
            <select id="signupInstitute" name="institute_eiin"
                    onchange="this.setAttribute('data-selected', this.value)"
                    data-selected="<?= htmlspecialchars($old['institute_eiin'] ?? '') ?>">
              <option value="">Select your institute...</option>
              <?php foreach ($institutes as $inst): ?>
                <option value="<?= htmlspecialchars($inst['institute_EIIN']) ?>"
                  <?= ($old['institute_eiin'] ?? '') == $inst['institute_EIIN'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($inst['Institute_name']) ?>
                </option>
              <?php endforeach; ?>
              <option value="__new__"
                <?= ($old['institute_eiin'] ?? '') == '__new__' ? 'selected' : '' ?>>
                My institute is not listed - add it
              </option>
            </select>
            <span class="field-note">Your institute's leaderboard rank depends on this.</span>
          </div>

          <div class="institute-new">
            <p class="subsection-label">Adding a new institute?</p>
            <div class="form-group">
              <label for="newInstName">Institute Name</label>
              <input type="text" id="newInstName" name="new_institute_name"
                     value="<?= htmlspecialchars($old['new_institute_name'] ?? '') ?>"
                     placeholder="BRAC University" />
            </div>
            <div class="form-group">
              <label for="newInstEiin">Institute EIIN</label>
              <input type="text" id="newInstEiin" name="new_institute_eiin"
                     value="<?= htmlspecialchars($old['new_institute_eiin'] ?? '') ?>"
                     placeholder="100021" maxlength="20" />
              <span class="field-note">
                The official code for your institute. It starts on the leaderboard with 0 points.
              </span>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="signupStudentId">Student ID</label>
              <input type="text" id="signupStudentId" name="student_id"
                     value="<?= htmlspecialchars($old['student_id'] ?? '') ?>"
                     placeholder="STU001" />
            </div>
            <div class="form-group">
              <label for="signupEduLevel">Education Level</label>
              <select id="signupEduLevel" name="education_level">
                <option value="">Select...</option>
                <?php foreach (['Secondary','Higher Secondary','Undergraduate'] as $lvl): ?>
                  <option value="<?= $lvl ?>"
                    <?= ($old['education_level'] ?? '') == $lvl ? 'selected' : '' ?>>
                    <?= $lvl ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

        </div>

        <!-- NON-STUDENT ONLY -->
        <div class="role-fields non-student-only">

          <p class="section-label">Non-Student Details</p>

          <div class="form-row">
            <div class="form-group">
              <label for="signupNid">NID Number</label>
              <input type="text" id="signupNid" name="nid"
                     value="<?= htmlspecialchars($old['nid'] ?? '') ?>"
                     placeholder="1234567890" />
            </div>
            <div class="form-group">
              <label for="signupOccupation">Occupation</label>
              <input type="text" id="signupOccupation" name="occupation"
                     value="<?= htmlspecialchars($old['occupation'] ?? '') ?>"
                     placeholder="Engineer" />
            </div>
          </div>

        </div>

        <div class="form-group">
          <label for="signupPassword">Password</label>
          <input type="password" id="signupPassword" name="password"
                 placeholder="Minimum 8 characters" required />
        </div>

        <button type="submit" class="btn-submit">Sign Up</button>

        <p class="toggle-text" style="margin-top: 18px;">
          Already registered?
          <a class="toggle-link" href="login.php?form=login">Log In</a>
        </p>
      </form>

    </div>
  </div>

  <script>
    function handleAuth(event, statusMessage) {
      event.preventDefault();
      const overlay     = document.getElementById('loadingOverlay');
      const brandIcon   = document.getElementById('brandIcon');
      const overlayText = document.getElementById('overlayText');
      overlayText.textContent = statusMessage;
      overlay.classList.add('active');
      brandIcon.classList.add('spinning');
      setTimeout(() => { event.target.submit(); }, 2000);
    }
  </script>

</body>
</html>