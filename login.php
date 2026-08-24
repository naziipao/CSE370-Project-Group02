<?php
/* ============================================================
   login.php

   Combines three old files:
     - frontend/UI/HTML/login.html    (the markup below)
     - frontend/JS/login.js           (the form toggle)
     - controllers/loginController.js (signup + login logic)

   One login form serves two kinds of account. The email is
   looked for in `user` first, then in `recycler`. Whichever
   table it turns up in decides where the person lands.
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

// Which form is visible: "login" or "signup".
// login.js did this in the browser; now the URL decides.
$show = (isset($_GET['form']) && $_GET['form'] == 'signup') ? 'signup' : 'login';

// Values typed before an error, so nothing has to be retyped.
$old = isset($_SESSION['old']) ? $_SESSION['old'] : [];
unset($_SESSION['old']);


/* ===== 2. HANDLE THE FORMS ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];


    /* ---------- SIGN UP ---------- */
    /* Only normal users sign up here. Recycler accounts are
       created directly in the database, because a recycler is
       staff, not a member of the public.                      */

    if ($action == 'signup') {

        // --- shared fields ---
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

        // --- student only ---
        $student_id      = trim($_POST['student_id']);
        $institute_eiin  = $_POST['institute_eiin'];
        $education_level = $_POST['education_level'];
        $new_inst_name   = trim($_POST['new_institute_name']);
        $new_inst_eiin   = trim($_POST['new_institute_eiin']);

        // --- non-student only ---
        $nid        = trim($_POST['nid']);
        $occupation = trim($_POST['occupation']);

        // Remember what was typed in case we send them back.
        $_SESSION['old'] = [
            'first_name'          => $first_name,
            'last_name'           => $last_name,
            'email'               => $email,
            'phone'               => $phone,
            'role'                => $role,
            'dob'                 => $dob,
            'gender'              => $gender,
            'street_address'      => $street,
            'city'                => $city,
            'student_id'          => $student_id,
            'institute_eiin'      => $institute_eiin,
            'education_level'     => $education_level,
            'new_institute_name'  => $new_inst_name,
            'new_institute_eiin'  => $new_inst_eiin,
            'nid'                 => $nid,
            'occupation'          => $occupation
        ];


        /* ----- Work out which institute this student belongs to -----

           student.institute_EIIN is a foreign key pointing at
           edu_institute_stats.institute_EIIN, so an institute that
           is not in that table cannot be used. If the student chose
           "not listed", the institute is created first.            */

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

                // Reuse the row if that EIIN already exists.
                $stmt = $pdo->prepare("SELECT institute_EIIN
                                       FROM edu_institute_stats
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

        // The email must be free in BOTH tables, otherwise login
        // would not know which account was meant.
        $stmt = $pdo->prepare("SELECT User_id FROM `user` WHERE Email = ?");
        $stmt->execute([$email]);

        $stmt2 = $pdo->prepare("SELECT Recycler_ID FROM recycler WHERE email = ?");
        $stmt2->execute([$email]);

        if ($stmt->fetch() || $stmt2->fetch()) {

            set_flash("This email is already registered.");
            header("Location: login.php?form=signup");
            exit;
        }

        // Pick a User_id that is not taken yet.
        do {
            $user_id = random_int(100000000, 999999999);

            $stmt = $pdo->prepare("SELECT User_id FROM `user` WHERE User_id = ?");
            $stmt->execute([$user_id]);

        } while ($stmt->fetch());

        // The old code nested four callbacks here. A transaction does
        // the same job: every insert succeeds, or none of them do.
        $pdo->beginTransaction();

        // The institute must exist before the student row references it.
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

        // Every user owns exactly one wallet, so create it now.
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

        // --- try the user table first ---
        $stmt = $pdo->prepare("SELECT User_id, FirstName
                               FROM `user`
                               WHERE Email = ? AND Pin = ?");
        $stmt->execute([$email, $password]);
        $user = $stmt->fetch();

        if ($user) {

            $_SESSION['user_id']     = $user['User_id'];
            $_SESSION['user_name']   = $user['FirstName'];
            $_SESSION['last_active'] = time();

            // A fresh session ID at login stops session fixation.
            session_regenerate_id(true);

            set_flash("Login successful. Welcome back, " . $user['FirstName'] . "!");

            header("Location: dashboard.php");
            exit;
        }

        // --- not a user, so try the recycler table ---
        $stmt = $pdo->prepare("SELECT Recycler_ID, name, city
                               FROM recycler
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

        // --- neither table matched ---
        $_SESSION['old'] = ['email' => $email];

        set_flash("Invalid email or password.");
        header("Location: login.php");
        exit;
    }
}


/* ===== 3. LOAD THE DATA THE FORM NEEDS ===== */

// Institutes already on the leaderboard, for the dropdown.
$institutes = $pdo->query("SELECT institute_EIIN, Institute_name
                           FROM edu_institute_stats
                           ORDER BY Institute_name ASC")->fetchAll();

// Nobody can be born in the future.
$today = date('Y-m-d');


/* ===== 4. THE PAGE ===== */
// Layout and classes are exactly as in the original login.html.
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
    <div class="brand-header">
      <svg class="brand-icon" viewBox="0 0 24 24">
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
            action="login.php" method="POST">

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

        <!-- Recyclers use this same form. Their accounts live in the
             recycler table and are created by the team, not signed up. -->
        <p class="recycler-hint">
          🚛 Recyclers sign in here too, with the account given to you.
        </p>
      </form>

      <!-- ================= SIGNUP FORM ================= -->
      <form id="signupForm"
            class="auth-form <?= $show == 'signup' ? 'active' : '' ?>"
            action="login.php" method="POST">

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
              <?php
              $genders = ['Male', 'Female', 'Other', 'Prefer not to say'];
              foreach ($genders as $g):
              ?>
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

        <!-- ---------- STUDENT ONLY ----------
             CSS hides this block when Non-Student is selected.
             These fields are never marked required, because a
             browser refuses to submit a form containing a hidden
             required field. PHP validates them instead. -->
        <div class="role-fields student-only">

          <p class="section-label">Student Details</p>

          <div class="form-group">
            <label for="signupInstitute">Institute</label>
            <select id="signupInstitute" name="institute_eiin">
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
                The official code for your institute. It starts on the
                leaderboard with 0 points.
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
                <?php
                $levels = ['Secondary', 'Higher Secondary', 'Undergraduate'];
                foreach ($levels as $lvl):
                ?>
                  <option value="<?= $lvl ?>"
                    <?= ($old['education_level'] ?? '') == $lvl ? 'selected' : '' ?>>
                    <?= $lvl ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

        </div>

        <!-- ---------- NON-STUDENT ONLY ---------- -->
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

</body>
</html>