<?php
session_start();
require_once "db.php";
require_once __DIR__ . "/PHPMailer/src/Exception.php";
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";
require_once __DIR__ . "/mail_config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Which account type is this for? "company" (default) or "student".
$role = ($_GET['role'] ?? $_POST['role'] ?? 'company') === 'student' ? 'student' : 'company';

// Redirect if already logged in
if ($role === 'company' && !empty($_SESSION['company_id'])) {
    header("Location: dashboard/index.php");
    exit;
}
if ($role === 'student' && !empty($_SESSION['student_id'])) {
    header("Location: student/index.php");
    exit;
}

$message = '';
$msgType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $msgType = 'error';
    } else {
        if ($role === 'student') {
            $table = 'students';
            $emailCol = 'email';
            $nameCol  = 'full_name';
        } else {
            $table = 'companies';
            $emailCol = 'company_email';
            $nameCol  = 'company_name';
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT id, $nameCol AS acct_name FROM $table WHERE $emailCol = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $account = $result->fetch_assoc();
        $stmt->close();

        if ($account) {
            // Generate secure token
            $token = bin2hex(random_bytes(32));

            // Remove any old token for this account
            $del = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_type = ? AND user_id = ?");
            $del->bind_param("si", $role, $account['id']);
            $del->execute();
            $del->close();

            // Insert new token. IMPORTANT: the expiry is computed by MySQL
            // itself (NOW() + INTERVAL 1 HOUR), not by PHP's date()/time().
            // Using PHP's clock here was the bug that made links show as
            // "expired" the instant they were opened: PHP's timezone and
            // MySQL's session timezone didn't agree, so the stored
            // expires_at could already be in the past according to the
            // NOW() used later to check it. Letting MySQL set its own
            // expiry means the write and the check always use the same
            // clock.
            $ins = $conn->prepare(
                "INSERT INTO password_reset_tokens (user_type, user_id, token, expires_at)
                 VALUES (?, ?, ?, NOW() + INTERVAL 1 HOUR)"
            );
            $ins->bind_param("sis", $role, $account['id'], $token);
            $ins->execute();
            $ins->close();

            // Build reset link
            $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host      = $_SERVER['HTTP_HOST'];
            $basePath  = dirname($_SERVER['SCRIPT_NAME']);
            $resetLink = $protocol . '://' . $host . $basePath . '/reset-password.php?token=' . $token . '&role=' . $role;

            // Send email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USERNAME;
                $mail->Password   = MAIL_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->setFrom(MAIL_USERNAME, 'InternHub');
                $mail->addAddress($email, $account['acct_name']);
                $mail->Subject = 'Reset your InternHub password';
                $mail->isHTML(true);
                $mail->Body = '
                    <p>Hi ' . htmlspecialchars($account['acct_name']) . ',</p>
                    <p>We received a request to reset your InternHub password.</p>
                    <p><a href="' . $resetLink . '" style="background:#10233F;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-family:sans-serif;">Reset Password</a></p>
                    <p>This link expires in <strong>1 hour</strong>. If you did not request this, you can safely ignore this email.</p>
                    <p>— InternHub Team</p>';
                $mail->AltBody = "Reset your password: $resetLink\n\nThis link expires in 1 hour.";
                $mail->send();
            } catch (Exception $e) {
                // Silently fail for security — don't expose mail errors
            }
        }

        // Always show the same message to prevent email enumeration
        $message = 'If that email is registered, a reset link has been sent. Check your inbox (and spam).';
        $msgType = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password — InternHub</title>
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
      <div class="brand-name">InternHub <?= $role === 'student' ? 'for students' : 'for employers' ?></div>
      <h1 class="brand-headline">Forgot your password?</h1>
      <p class="brand-sub">Enter the email you registered with and we'll send you a link to reset your password.</p>
    </div>
    <div class="brand-foot">Remember it? <a href="login.php?role=<?= $role ?>">Sign in</a></div>
  </div>

  <div class="form-panel">
    <div class="form-wrap" style="max-width:420px;">
      <div class="form-eyebrow">Account recovery</div>
      <h2 class="form-title">Reset your password</h2>
      <p class="form-desc">We'll email you a secure link. It expires in 1 hour.</p>

      <?php if ($message): ?>
        <div class="form-status <?= $msgType ?>" style="display:block;"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if ($msgType !== 'success'): ?>
      <form method="POST" action="forgot-password.php" novalidate>
        <input type="hidden" name="role" value="<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>">
        <div class="field">
          <label for="email"><?= $role === 'student' ? 'Registered email' : 'Company email' ?> *</label>
          <input type="email" id="email" name="email" placeholder="<?= $role === 'student' ? 'you@example.com' : 'hello@yourcompany.com' ?>" required autofocus
                 value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <button type="submit" class="submit-btn">Send reset link</button>
      </form>
      <?php else: ?>
        <p style="margin-top:20px;text-align:center;"><a href="login.php?role=<?= $role ?>" class="submit-btn" style="display:inline-block;text-decoration:none;width:auto;padding:13px 32px;">Back to login</a></p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
