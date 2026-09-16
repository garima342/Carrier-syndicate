<?php session_start(); if (!empty($_SESSION['student_id'])) { header("Location: student/index.php"); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Registration — InternHub</title>
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
        <div class="registry-stamp" aria-hidden="true"><span>Student<br>Portal</span></div>
      </div>
      <div class="brand-name">InternHub for students</div>
      <h1 class="brand-headline">Create your student profile.</h1>
      <p class="brand-sub">One profile lets you browse internships and jobs and apply to as many as you like.</p>
    </div>

    <ol class="ledger-list">
      <li><span class="ledger-num">01</span><span>Your basic details and college</span></li>
      <li><span class="ledger-num">02</span><span>Verify your email with an OTP</span></li>
      <li><span class="ledger-num">03</span><span>Set a password and you're in</span></li>
    </ol>

    <div class="brand-foot">Already registered? <a href="login.php?role=student">Sign in to your account</a></div>
  </div>

  <div class="form-panel">
    <div class="form-wrap" style="max-width:480px;">
      <div class="form-eyebrow">Student onboarding</div>
      <h2 class="form-title">Register as a student</h2>
      <p class="form-desc">Takes about 2 minutes. Fields marked with an asterisk are required.</p>

      <div id="formStatus" class="form-status" style="display:none;" role="status"></div>

      <form id="studentRegisterForm" action="student-register-save.php" method="POST" novalidate>

        <div class="field">
          <label for="full_name">Full name *</label>
          <input type="text" id="full_name" name="full_name" placeholder="Jane Doe" required>
        </div>

        <div class="field" style="display:flex;gap:8px;">
          <div style="flex:1;">
            <label for="student_email">Email *</label>
            <input type="email" id="student_email" name="email" placeholder="you@example.com" required>
          </div>
          <button type="button" id="sendOtpBtn" class="submit-btn" style="width:auto;white-space:nowrap;align-self:flex-end;padding:12px 16px;">Send OTP</button>
        </div>

        <div class="field">
          <label for="otp">Email OTP *</label>
          <input type="text" id="otp" name="email_otp" placeholder="6-digit code" maxlength="6" required>
        </div>

        <div class="field">
          <label for="phone">Mobile number *</label>
          <input type="text" id="phone" name="phone" placeholder="10-digit mobile number" maxlength="10" required>
        </div>

        <div class="field">
          <label for="college">College / University *</label>
          <input type="text" id="college" name="college" placeholder="Your institution" required>
        </div>

        <div class="field" style="display:flex;gap:12px;">
          <div style="flex:1;">
            <label for="course">Course *</label>
            <input type="text" id="course" name="course" placeholder="e.g. B.Tech CSE" required>
          </div>
          <div style="flex:1;">
            <label for="grad_year">Graduation year *</label>
            <input type="number" id="grad_year" name="graduation_year" placeholder="2027" min="2000" max="2100" required>
          </div>
        </div>

        <div class="field">
          <label for="pass">Password *</label>
          <input type="password" id="pass" name="password" placeholder="At least 8 characters" minlength="8" required>
        </div>
        <div class="field">
          <label for="pass2">Confirm password *</label>
          <input type="password" id="pass2" name="confirm_password" placeholder="Re-enter password" required>
        </div>
        <div id="passError" class="pass-error" style="display:none;">Passwords do not match.</div>

        <div class="field">
          <label for="captcha">Captcha *</label>
          <div style="display:flex;align-items:center;gap:10px;">
            <img id="captchaImage" src="captcha.php" alt="captcha" style="border-radius:6px;">
            <button type="button" id="refreshCaptcha" class="submit-btn" style="width:auto;padding:8px 12px;">&#8635;</button>
          </div>
          <input type="text" id="captcha" name="captcha" placeholder="Enter the code above" required>
        </div>

        <button type="submit" id="submitBtn" class="submit-btn">Create student account</button>
      </form>
    </div>
  </div>

  <script src="student-register.js"></script>
</body>
</html>
