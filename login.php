<?php
session_start();
require_once "db.php";

// Which tab is active: student | company | admin
$role = $_GET['role'] ?? $_POST['role'] ?? 'company';
if (!in_array($role, ['student', 'company', 'admin'], true)) {
    $role = 'company';
}

// Already logged in? Go straight to the right dashboard.
if (!empty($_SESSION['company_id'])) { header("Location: dashboard/index.php"); exit; }
if (!empty($_SESSION['student_id'])) { header("Location: student/index.php"); exit; }
if (!empty($_SESSION['admin_id']))   { header("Location: admin/index.php"); exit; }

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Enter both your email and password.";
    } elseif ($role === "company") {
        $stmt = $conn->prepare("SELECT id, company_name, password_hash FROM companies WHERE company_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $company = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($company && password_verify($password, $company["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["company_id"]   = $company["id"];
            $_SESSION["company_name"] = $company["company_name"];
            header("Location: dashboard/index.php");
            exit;
        } else {
            $error = "Incorrect email or password.";
        }
    } elseif ($role === "student") {
        $stmt = $conn->prepare("SELECT id, full_name, password_hash FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($student && password_verify($password, $student["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["student_id"]   = $student["id"];
            $_SESSION["student_name"] = $student["full_name"];
            header("Location: student/index.php");
            exit;
        } else {
            $error = "Incorrect email or password.";
        }
    } else { // admin
        $stmt = $conn->prepare("SELECT id, name, password_hash FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin["password_hash"])) {
            session_regenerate_id(true);
            $_SESSION["admin_id"]   = $admin["id"];
            $_SESSION["admin_name"] = $admin["name"];
            header("Location: admin/index.php");
            exit;
        } else {
            $error = "Incorrect email or password.";
        }
    }
}

$copy = [
    'company' => [
        'stamp'   => 'Company<br>Registry',
        'brand'   => 'InternHub for employers',
        'eyebrow' => 'Employer sign in',
        'sub'     => 'Sign in to manage your internship and job postings, sessions, and open slots.',
        'label'   => 'Company email',
        'placeholder' => 'hello@yourcompany.com',
        'foot'    => '<a href="company-register.html">Register your company</a>',
    ],
    'student' => [
        'stamp'   => 'Student<br>Portal',
        'brand'   => 'InternHub for students',
        'eyebrow' => 'Student sign in',
        'sub'     => 'Sign in to browse internships and jobs, and track your applications.',
        'label'   => 'Email',
        'placeholder' => 'you@example.com',
        'foot'    => '<a href="student-register.php">Create a student account</a>',
    ],
    'admin' => [
        'stamp'   => 'Admin<br>Console',
        'brand'   => 'InternHub admin',
        'eyebrow' => 'Admin sign in',
        'sub'     => 'Sign in to manage companies, students, and postings across InternHub.',
        'label'   => 'Admin email',
        'placeholder' => 'admin@internhub.com',
        'foot'    => 'Admin accounts are created by other admins, not self-registered.',
    ],
][$role];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Log in — InternHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .role-tabs { display:flex; gap:8px; margin-bottom:24px; }
    .role-tabs a {
      flex:1; text-align:center; padding:10px 8px; border-radius:8px;
      font-size:13px; font-weight:600; text-decoration:none; color:#6B6558;
      background:#F4F1EA; border:1px solid #E4DFD3;
    }
    .role-tabs a.active { background:#10233F; color:#fff; border-color:#10233F; }
  </style>
</head>
<body>

  <div class="brand-panel">
    <div class="brand-top">
      <div class="brand-row">
        <div class="logo-mark">IH</div>
        <div class="registry-stamp" aria-hidden="true"><span><?= $copy['stamp'] ?></span></div>
      </div>
      <div class="brand-name"><?= $copy['brand'] ?></div>
      <h1 class="brand-headline">Welcome back.</h1>
      <p class="brand-sub"><?= $copy['sub'] ?></p>
    </div>
    <div class="brand-foot">New here? <?= $copy['foot'] ?></div>
  </div>

  <div class="form-panel">
    <div class="form-wrap" style="max-width:420px;">

      <div class="role-tabs">
        <a href="login.php?role=student" class="<?= $role === 'student' ? 'active' : '' ?>">Student</a>
        <a href="login.php?role=company" class="<?= $role === 'company' ? 'active' : '' ?>">Company</a>
        <a href="login.php?role=admin"   class="<?= $role === 'admin'   ? 'active' : '' ?>">Admin</a>
      </div>

      <div class="form-eyebrow"><?= $copy['eyebrow'] ?></div>
      <h2 class="form-title">Log in to your account</h2>
      <p class="form-desc">Use the email and password you registered with.</p>

      <?php if ($error): ?>
        <div class="form-status error" style="display:block;"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" novalidate>
        <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
        <div class="field">
          <label for="email"><?= $copy['label'] ?> *</label>
          <input type="email" id="email" name="email" placeholder="<?= $copy['placeholder'] ?>" required autofocus>
        </div>
        <div class="field">
          <label for="password">Password *</label>
          <input type="password" id="password" name="password" placeholder="Your password" required>
        </div>
        <?php if ($role !== 'admin'): ?>
        <p class="login-forgot"><a href="forgot-password.php?role=<?= $role ?>">Forgot password?</a></p>
        <?php endif; ?>
        <button type="submit" class="submit-btn">Log in</button>
      </form>
    </div>
  </div>

</body>
</html>
