<?php
session_start();
require_once "db.php";

$role = ($_GET['role'] ?? $_POST['role'] ?? 'company') === 'student' ? 'student' : 'company';

// Redirect if already logged in
if (!empty($_SESSION['company_id'])) {
    header("Location: dashboard/index.php");
    exit;
}
if (!empty($_SESSION['student_id'])) {
    header("Location: student/index.php");
    exit;
}

$token   = trim($_GET['token'] ?? $_POST['token'] ?? '');
$message = '';
$msgType = '';
$validToken = false;
$userId  = null;
$userType = null;

if ($token !== '') {
    // Note: looked up by token alone (tokens are random 32-byte values,
    // effectively unique on their own) and NOW() is MySQL's own clock —
    // the same clock forgot-password.php used to set expires_at, so
    // there's no timezone mismatch between "when it was issued" and
    // "is it expired yet".
    $stmt = $conn->prepare(
        "SELECT user_type, user_id FROM password_reset_tokens
         WHERE token = ? AND expires_at > NOW() AND used = 0"
    );
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $row    = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $validToken = true;
        $userType   = $row['user_type'];
        $userId     = (int) $row['user_id'];
        $role       = $userType; // trust the token's own role over the URL param
    } else {
        $message = 'This reset link is invalid or has expired. Please request a new one.';
        $msgType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $message = 'Password must be at least 8 characters.';
        $msgType = 'error';
    } elseif ($password !== $password2) {
        $message = 'Passwords do not match.';
        $msgType = 'error';
    } else {
        $hash  = password_hash($password, PASSWORD_BCRYPT);
        $table = $userType === 'student' ? 'students' : 'companies';

        // Update the password
        $upd = $conn->prepare("UPDATE $table SET password_hash = ? WHERE id = ?");
        $upd->bind_param("si", $hash, $userId);
        $upd->execute();
        $upd->close();

        // Mark token as used
        $markUsed = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $markUsed->bind_param("s", $token);
        $markUsed->execute();
        $markUsed->close();

        $message = 'Password reset successfully! You can now log in with your new password.';
        $msgType = 'success';
        $validToken = false; // hide the form
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password — InternHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="brand-panel">
    <div class="brand-top">
      <div class="brand-row">
        <div class="logo-mark">IH</div>
        <div class="registry-stamp" aria-hidden="true"><span>Company<br>Registry</span></div>
      </div>
      <div class="brand-name">InternHub for employers</div>
      <h1 class="brand-headline">Set a new password.</h1>
      <p class="brand-sub">Choose something strong — at least 8 characters.</p>
    </div>
    <div class="brand-foot">Back to <a href="login.php?role=<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">Sign in</a></div>
  </div>

  <div class="form-panel">
    <div class="form-wrap" style="max-width:420px;">
      <div class="form-eyebrow">Account recovery</div>
      <h2 class="form-title">Reset your password</h2>

      <?php if ($message): ?>
        <div class="form-status <?= $msgType ?>" style="display:block;margin-bottom:20px;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if ($validToken): ?>
      <form method="POST" action="reset-password.php" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">

        <div class="field">
          <label for="password">New password *</label>
          <input type="password" id="password" name="password" placeholder="At least 8 characters" required minlength="8" autofocus>
        </div>
        <div class="field">
          <label for="confirm_password">Confirm new password *</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
        </div>
        <div id="passError" class="pass-error" style="display:none;">Passwords do not match.</div>

        <button type="submit" class="submit-btn">Set new password</button>
      </form>
      <?php elseif ($msgType === 'success'): ?>
        <p style="margin-top:20px;text-align:center;">
          <a href="login.php?role=<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>" class="submit-btn" style="display:inline-block;text-decoration:none;width:auto;padding:13px 32px;">Go to login</a>
        </p>
      <?php elseif (!$token): ?>
        <p style="color:#6B6558;font-size:14px;">No reset token found. <a href="forgot-password.php">Request a new link</a>.</p>
      <?php endif; ?>
    </div>
  </div>

  <script>
    const p1 = document.getElementById('password');
    const p2 = document.getElementById('confirm_password');
    const pe = document.getElementById('passError');
    if (p1 && p2 && pe) {
      p2.addEventListener('input', () => {
        pe.style.display = (p2.value && p1.value !== p2.value) ? 'block' : 'none';
      });
    }
  </script>
</body>
</html>
