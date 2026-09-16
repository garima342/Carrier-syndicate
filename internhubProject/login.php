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
    /* ============================================================
       Carrier Syndicate — login
       Palette: #0A1A2F  #0F2C45  #022C45  #2F4A67  + white
       CSS only. Overrides style.css for this page.
       ============================================================ */

    :root {
      --bg-deep:   #0A1A2F;
      --bg-mid:    #0F2C45;
      --bg-alt:    #022C45;
      --accent:    #2F4A67;
      --accent-up: #4C6E93;
      --accent-lt: #7C9BBE;
      --white:     #FFFFFF;
      --paper:     #F7F9FC;
      --ink:       #0A1A2F;
      --ink-soft:  #4A5B70;
      --line:      #DDE4EC;
      --danger:    #B3341F;
      --danger-bg: #FDF1EF;
    }

    * { box-sizing: border-box; }

    html, body { height: 100%; }

    body {
      margin: 0;
      display: flex;
      min-height: 100vh;
      width: 100%;
      overflow-x: hidden;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      color: var(--ink);
      background: var(--white);
      -webkit-font-smoothing: antialiased;
    }

    /* ---------------- LEFT : BRAND PANEL ---------------- */

    .brand-panel {
      flex: 0 0 44%;
      max-width: 560px;
      background:
        radial-gradient(760px 420px at 15% 0%, rgba(76,110,147,0.30), transparent 62%),
        radial-gradient(620px 460px at 100% 100%, rgba(2,44,69,0.85), transparent 58%),
        var(--bg-deep);
      color: var(--white);
      padding: clamp(36px, 4.5vw, 60px);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      overflow: hidden;
    }
    /* thin hairline grid, very low opacity — adds depth without noise */
    .brand-panel::after {
      content: "";
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(255,255,255,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255,255,255,0.035) 1px, transparent 1px);
      background-size: 56px 56px;
      pointer-events: none;
    }
    .brand-top { position: relative; z-index: 1; }

    .brand-row {
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px;
      margin-bottom: clamp(48px, 9vh, 88px);
    }

    /* logo image replaces the "IH" text node */
    .brand-panel .logo-mark {
      width: 48px; height: 48px;
      flex-shrink: 0;
      border-radius: 50%;
      font-size: 0;
      color: transparent;
      text-indent: -9999px;
      overflow: hidden;
      background-color: var(--white);
      background-image: url('logo_internship.jpeg');
      background-repeat: no-repeat;
      background-size: 150% auto;
      background-position: 45% 5%;
      box-shadow: 0 4px 16px rgba(0,0,0,0.45);
    }

    .registry-stamp {
      border: 1px solid rgba(255,255,255,0.22);
      border-radius: 6px;
      padding: 7px 13px;
      text-align: right;
    }
    .registry-stamp span {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 9.5px;
      font-weight: 600;
      line-height: 1.45;
      letter-spacing: .16em;
      text-transform: uppercase;
      color: var(--accent-lt);
    }

    /* brand name -> Carrier Syndicate */
    .brand-panel .brand-name {
      font-size: 0;
      color: transparent;
      line-height: 1;
      margin-bottom: 20px;
    }
    .brand-panel .brand-name::after {
      content: "Carrier Syndicate";
      display: block;
      font-family: 'Space Grotesk', sans-serif;
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .26em;
      text-transform: uppercase;
      color: var(--accent-lt);
    }

    .brand-headline {
      font-family: 'Space Grotesk', sans-serif;
      font-size: clamp(30px, 3.6vw, 44px);
      font-weight: 600;
      line-height: 1.1;
      letter-spacing: -0.015em;
      color: var(--white);
      margin: 0 0 18px;
    }
    .brand-sub {
      font-size: 15px;
      line-height: 1.7;
      color: rgba(255,255,255,0.68);
      max-width: 400px;
      margin: 0;
    }

    .brand-foot {
      position: relative; z-index: 1;
      font-size: 13.5px;
      color: rgba(255,255,255,0.55);
      padding-top: 26px;
      border-top: 1px solid rgba(255,255,255,0.12);
    }
    .brand-foot a {
      color: var(--white);
      font-weight: 600;
      text-decoration: none;
      border-bottom: 1px solid rgba(255,255,255,0.35);
      padding-bottom: 1px;
      transition: border-color .18s ease;
    }
    .brand-foot a:hover { border-bottom-color: var(--white); }

    /* ---------------- RIGHT : FORM PANEL ---------------- */

    .form-panel {
      flex: 1;
      background: var(--white);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: clamp(32px, 5vw, 64px) clamp(20px, 4vw, 48px);
    }
    .form-wrap { width: 100%; }

    /* role tabs — segmented control, HackerRank-ish */
    .role-tabs {
      display: flex;
      gap: 0;
      margin-bottom: 34px;
      background: var(--paper);
      border: 1px solid var(--line);
      border-radius: 10px;
      padding: 4px;
    }
    .role-tabs a {
      flex: 1;
      text-align: center;
      padding: 9px 8px;
      border-radius: 7px;
      font-size: 13px;
      font-weight: 600;
      text-decoration: none;
      color: var(--ink-soft);
      transition: background .18s ease, color .18s ease, box-shadow .18s ease;
    }
    .role-tabs a:hover { color: var(--ink); }
    .role-tabs a.active {
      background: var(--bg-deep);
      color: var(--white);
      box-shadow: 0 2px 8px rgba(10,26,47,0.28);
    }

    .form-eyebrow {
      font-family: 'Space Grotesk', sans-serif;
      font-size: 11px;
      font-weight: 600;
      letter-spacing: .18em;
      text-transform: uppercase;
      color: var(--accent-up);
      margin-bottom: 12px;
    }
    .form-title {
      font-family: 'Space Grotesk', sans-serif;
      font-size: clamp(24px, 3vw, 30px);
      font-weight: 600;
      letter-spacing: -0.01em;
      color: var(--ink);
      margin: 0 0 10px;
    }
    .form-desc {
      font-size: 14.5px;
      line-height: 1.65;
      color: var(--ink-soft);
      margin: 0 0 28px;
    }

    /* error banner */
    .form-status.error {
      background: var(--danger-bg);
      border: 1px solid rgba(179,52,31,0.28);
      border-left: 3px solid var(--danger);
      color: var(--danger);
      font-size: 13.5px;
      font-weight: 500;
      line-height: 1.5;
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 22px;
    }

    /* fields */
    .field { margin-bottom: 20px; }
    .field label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--ink);
      margin-bottom: 8px;
      letter-spacing: .005em;
    }
    .field input {
      width: 100%;
      padding: 13px 15px;
      font-family: inherit;
      font-size: 14.5px;
      color: var(--ink);
      background: var(--white);
      border: 1px solid var(--line);
      border-radius: 9px;
      outline: none;
      transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
    }
    .field input::placeholder { color: #9AA7B6; }
    .field input:hover { border-color: #C3CEDB; }
    .field input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(47,74,103,0.14);
      background: var(--white);
    }

    .login-forgot {
      text-align: right;
      margin: -6px 0 24px;
      font-size: 13px;
    }
    .login-forgot a {
      color: var(--accent-up);
      font-weight: 600;
      text-decoration: none;
    }
    .login-forgot a:hover { color: var(--bg-deep); text-decoration: underline; }

    .submit-btn {
      width: 100%;
      padding: 14px 18px;
      font-family: 'Inter', sans-serif;
      font-size: 14.5px;
      font-weight: 600;
      letter-spacing: .01em;
      color: var(--white);
      background: var(--bg-deep);
      border: none;
      border-radius: 9px;
      cursor: pointer;
      box-shadow: 0 6px 18px -8px rgba(10,26,47,0.75);
      transition: background .18s ease, transform .15s ease, box-shadow .18s ease;
    }
    .submit-btn:hover {
      background: var(--bg-mid);
      transform: translateY(-1px);
      box-shadow: 0 10px 24px -8px rgba(10,26,47,0.8);
    }
    .submit-btn:active { transform: translateY(0); }
    .submit-btn:focus-visible {
      outline: none;
      box-shadow: 0 0 0 3px rgba(47,74,103,0.3);
    }

    /* ---------------- RESPONSIVE ---------------- */

    @media (max-width: 960px) {
      body { flex-direction: column; }
      .brand-panel {
        flex: 0 0 auto;
        max-width: none;
        width: 100%;
        padding: 32px 28px 36px;
      }
      .brand-row { margin-bottom: 34px; }
      .brand-headline { font-size: 30px; }
      .brand-sub { max-width: none; }
      .brand-foot { padding-top: 22px; margin-top: 26px; }
      .form-panel { padding: 40px 28px 56px; }
      .form-wrap { margin: 0 auto; }
    }

    @media (max-width: 520px) {
      .brand-panel { padding: 26px 22px 30px; }
      .brand-panel .logo-mark { width: 42px; height: 42px; }
      .registry-stamp { padding: 6px 10px; }
      .registry-stamp span { font-size: 9px; letter-spacing: .12em; }
      .brand-headline { font-size: 26px; }
      .brand-sub { font-size: 14px; }
      .form-panel { padding: 32px 22px 48px; }
      .role-tabs a { font-size: 12px; padding: 9px 4px; }
      .field input { font-size: 16px; } /* stops iOS zoom-on-focus */
    }
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
