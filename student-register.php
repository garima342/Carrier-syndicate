<?php session_start(); if (!empty($_SESSION['student_id'])) { header("Location: student/index.php"); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Registration — InternHub</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  
<style>
:root{
 --navy:#101828;--navy2:#1d2540;--purple:#6758e8;--purple2:#8b5cf6;
 --text:#182230;--muted:#667085;--border:#e6e8ef;--bg:#f6f7fb;--white:#fff
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{
 margin:0;min-height:100vh;background:var(--bg);color:var(--text);
 font-family:Inter,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif
}
body:before{content:"";position:fixed;inset:0;pointer-events:none;background:
 radial-gradient(circle at 75% 8%,rgba(103,88,232,.08),transparent 25%),
 radial-gradient(circle at 15% 90%,rgba(139,92,246,.06),transparent 25%)}
.brand-panel{
 position:fixed;inset:0 auto 0 0;width:39%;padding:48px 5vw 42px;color:#fff;
 display:flex;flex-direction:column;justify-content:space-between;overflow:hidden;
 background:radial-gradient(circle at 85% 18%,rgba(139,92,246,.30),transparent 30%),
 radial-gradient(circle at 15% 82%,rgba(103,88,232,.22),transparent 35%),
 linear-gradient(150deg,#0d1222,#1b2340 58%,#33265f)
}
.brand-panel:before,.brand-panel:after{
 content:"";position:absolute;border-radius:50%;border:1px solid rgba(255,255,255,.08)
}
.brand-panel:before{width:500px;height:500px;right:-310px;top:28%}
.brand-panel:after{width:330px;height:330px;left:-230px;bottom:-170px}
.brand-top,.ledger-list,.brand-foot{position:relative;z-index:2}
.brand-row{display:flex;align-items:center;justify-content:space-between}
.logo-mark{
 width:54px;height:54px;border-radius:16px;display:grid;place-items:center;
 background:linear-gradient(135deg,#fff,rgba(255,255,255,.78));
 box-shadow:0 10px 30px rgba(0,0,0,.16);overflow:hidden
}
.logo-mark img{width:36px;height:36px;object-fit:contain;display:block}
.registry-stamp{
 width:78px;height:78px;border:1px dashed rgba(255,255,255,.38);border-radius:50%;
 display:grid;place-items:center;text-align:center;font-size:8px;text-transform:uppercase;
 letter-spacing:1.5px;line-height:1.35;transform:rotate(-8deg);opacity:.8
}
.brand-name{margin-top:42px;font-size:11px;font-weight:700;letter-spacing:1.8px;text-transform:uppercase;color:#c9c5ff}
.brand-headline{
 max-width:540px;margin:18px 0;font:700 clamp(40px,4.2vw,66px)/.98 "Space Grotesk",sans-serif;
 letter-spacing:-3px
}
.brand-sub{max-width:500px;margin:0;color:rgba(255,255,255,.67);font-size:14px;line-height:1.8}
.ledger-list{margin:0;padding:0;list-style:none;max-width:520px}
.ledger-list li{
 display:flex;gap:16px;align-items:flex-start;padding:16px 0;
 border-top:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.72);
 font-size:12px;line-height:1.6
}
.ledger-num{
 min-width:27px;height:27px;border-radius:8px;background:rgba(255,255,255,.08);
 display:grid;place-items:center;color:#c9c5ff;font:700 10px "Space Grotesk"
}
.brand-foot{font-size:12px;color:rgba(255,255,255,.52)}
.brand-foot a{color:#fff;text-underline-offset:4px}
.form-panel{margin-left:39%;min-height:100vh;padding:54px 6vw 80px}
.form-wrap{width:100%;max-width:620px;margin:0 auto}
.form-eyebrow{
 display:inline-flex;align-items:center;gap:7px;font-size:10px;font-weight:800;
 letter-spacing:1.5px;text-transform:uppercase;color:var(--purple)
}
.form-eyebrow:before{content:"";width:20px;height:2px;border-radius:2px;background:var(--purple)}
.form-title{margin:10px 0 8px;font:700 40px/1.08 "Space Grotesk",sans-serif;letter-spacing:-1.5px}
.form-desc{margin:0 0 27px;color:var(--muted);font-size:13px;line-height:1.6}
form{display:flex;flex-direction:column;gap:15px}
.section{
 position:relative;background:rgba(255,255,255,.95);border:1px solid var(--border);
 border-radius:20px;padding:26px;box-shadow:0 8px 30px rgba(16,24,40,.04)
}
.section:after{
 content:"";position:absolute;left:0;top:26px;width:3px;height:36px;
 border-radius:0 5px 5px 0;background:linear-gradient(#6758e8,#9b72f5)
}
.section-head{display:flex;align-items:center;gap:11px;margin-bottom:21px}
.section-num{
 width:31px;height:31px;border-radius:10px;display:grid;place-items:center;
 background:#f0eeff;color:var(--purple);font:800 10px "Space Grotesk"
}
.section-head h3{margin:0;font:650 17px "Space Grotesk"}
.field{display:flex;flex-direction:column;gap:7px;margin-bottom:15px}
.field:last-child{margin-bottom:0}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:15px}
label{font-size:11px;font-weight:700;color:#344054}
input,select,textarea{
 width:100%;border:1px solid #dfe2e9;background:#fcfcfd;border-radius:11px;padding:12px 13px;
 color:var(--text);font:400 13px Inter,sans-serif;outline:none;transition:all .18s
}
input::placeholder{color:#a0a7b4}
input:hover,select:hover{border-color:#c9cdd7;background:#fff}
input:focus,select:focus{
 border-color:#776be9;background:#fff;box-shadow:0 0 0 4px rgba(103,88,232,.10)
}
.pass-error{font-size:11px;color:#c43232;margin:-5px 0 4px}
.otp-row{display:grid;grid-template-columns:1fr 105px;gap:9px}
.otp-btn{
 border:1px solid #d9d4ff;border-radius:11px;background:#f4f2ff;color:#5548c8;
 font-weight:750;cursor:pointer;transition:.18s
}
.otp-btn:hover{background:#ebe8ff;border-color:#c8c2ff}
.captcha-box{
 display:flex;align-items:center;gap:10px;padding:9px;border:1px solid var(--border);
 border-radius:11px;background:#fafbfc
}
#captchaImage{
 width:145px;height:46px;object-fit:cover;border-radius:7px;border:1px solid #e4e7ec;background:#fff
}
#refreshCaptcha{
 border:1px solid #d8dce5;background:#fff;border-radius:8px;padding:8px 11px;
 font-size:11px;font-weight:700;cursor:pointer;color:#475467
}
#refreshCaptcha:hover{background:#f2f3f6}
.terms-row{
 display:flex;align-items:center;gap:9px;margin:4px 0 1px;padding:0 2px
}
.terms-row input{width:16px;height:16px;accent-color:var(--purple)}
.terms-label{font-size:11px;font-weight:550;color:#475467}
.submit-btn{
 width:100%;border:0;border-radius:12px;padding:15px 20px;
 background:linear-gradient(135deg,#5d55d8,#7648ca);color:#fff;
 font:750 13px Inter,sans-serif;cursor:pointer;
 box-shadow:0 12px 26px rgba(103,88,232,.24);transition:.18s
}
.submit-btn:hover{transform:translateY(-2px);box-shadow:0 16px 32px rgba(103,88,232,.29)}
.submit-btn:active{transform:translateY(0)}
.form-status{padding:12px 14px;border-radius:11px;font-size:12px;margin-bottom:15px}
@media(max-width:900px){
 .brand-panel{position:relative;width:100%;min-height:410px;padding:36px 28px 30px}
 .form-panel{margin-left:0;padding:36px 24px 60px}
 .brand-headline{font-size:44px}.ledger-list{display:none}
}
@media(max-width:560px){
 .field-row{grid-template-columns:1fr;gap:0}.otp-row{grid-template-columns:1fr}
 .brand-headline{font-size:37px;letter-spacing:-2px}.form-title{font-size:32px}
 .section{padding:21px;border-radius:16px}
}
</style>

</head>
<body>

  <div class="brand-panel">
    <div class="brand-top">
      <div class="brand-row">
        <div class="logo-mark"><img src="logo.svg" alt="InternHub"></div>
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

        <div class="section">
          <div class="section-head"><span class="section-num">01</span><h3>Personal details</h3></div>

          <div class="field">
            <label for="full_name">Full name *</label>
            <input type="text" id="full_name" name="full_name" placeholder="Your full name" required>
          </div>

          <div class="field">
            <label for="phone">Mobile number *</label>
            <input type="text" id="phone" name="phone" placeholder="10-digit mobile number" maxlength="10" required>
          </div>
        </div>

        <div class="section">
          <div class="section-head"><span class="section-num">02</span><h3>Education details</h3></div>

          <div class="field">
            <label for="college">College / University *</label>
            <input type="text" id="college" name="college" placeholder="Your institution" required>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="course">Course *</label>
              <input type="text" id="course" name="course" placeholder="e.g. B.Tech CSE" required>
            </div>
            <div class="field">
              <label for="grad_year">Graduation year *</label>
              <input type="number" id="grad_year" name="graduation_year" placeholder="2027" min="2000" max="2100" required>
            </div>
          </div>
        </div>

        <div class="section">
          <div class="section-head"><span class="section-num">03</span><h3>Email verification</h3></div>

          <div class="field">
            <label for="student_email">Email *</label>
            <div class="otp-row">
              <input type="email" id="student_email" name="email" placeholder="you@example.com" required>
              <button type="button" id="sendOtpBtn" class="otp-btn">Send OTP</button>
            </div>
          </div>

          <div class="field">
            <label for="otp">Email OTP *</label>
            <input type="text" id="otp" name="email_otp" placeholder="6-digit verification code" maxlength="6" required>
          </div>
        </div>

        <div class="section">
          <div class="section-head"><span class="section-num">04</span><h3>Secure your account</h3></div>

          <div class="field">
            <label for="pass">Password *</label>
            <input type="password" id="pass" name="password" placeholder="At least 8 characters" minlength="8" required>
          </div>

          <div class="field">
            <label for="pass2">Confirm password *</label>
            <input type="password" id="pass2" name="confirm_password" placeholder="Re-enter your password" required>
          </div>

          <div id="passError" class="pass-error" style="display:none;">Passwords do not match.</div>

          <div class="field">
            <label for="captcha">Captcha *</label>
            <div class="captcha-box">
              <img id="captchaImage" src="captcha.php" alt="Captcha">
              <button type="button" id="refreshCaptcha">↻ Refresh</button>
            </div>
            <input type="text" id="captcha" name="captcha" placeholder="Enter the code above" required>
          </div>

          <div class="terms-row">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms" class="terms-label">I agree to the Terms &amp; Conditions</label>
          </div>
        </div>

        <button type="submit" id="submitBtn" class="submit-btn">Create student account</button>
      </form>
    </div>
  </div>

  <script src="student-register.js"></script>
</body>
</html>
