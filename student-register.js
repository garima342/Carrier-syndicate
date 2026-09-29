// student-register.js — InternHub student registration form behavior

document.addEventListener("DOMContentLoaded", () => {
  const form        = document.getElementById("studentRegisterForm");
  const statusBox   = document.getElementById("formStatus");
  const submitBtn   = document.getElementById("submitBtn");
  const passInput   = document.getElementById("pass");
  const pass2Input  = document.getElementById("pass2");
  const passError   = document.getElementById("passError");
  const otpInput    = document.getElementById("otp");
  const captchaInput = document.getElementById("captcha");
  const emailInput  = document.getElementById("student_email");
  const phoneInput  = document.getElementById("phone");

  const sendOtpBtn   = document.getElementById("sendOtpBtn");
  const captchaImage = document.getElementById("captchaImage");
  const refreshCaptchaBtn = document.getElementById("refreshCaptcha");

  refreshCaptchaBtn.addEventListener("click", () => {
    captchaImage.src = "captcha.php?" + new Date().getTime();
  });
  function refreshCaptcha() {
    captchaImage.src = "captcha.php?" + new Date().getTime();
  }

  function checkPasswordsMatch() {
    if (pass2Input.value && passInput.value !== pass2Input.value) {
      passError.style.display = "block";
      return false;
    }
    passError.style.display = "none";
    return true;
  }
  passInput.addEventListener("input", checkPasswordsMatch);
  pass2Input.addEventListener("input", checkPasswordsMatch);

  let otpCooldown = 0, otpTimer = null;
  sendOtpBtn.addEventListener("click", async () => {
    const email = emailInput.value.trim();
    if (!isValidEmail(email)) {
      showStatus("Enter a valid email before requesting an OTP.", "error");
      emailInput.focus();
      return;
    }
    sendOtpBtn.disabled = true;
    const originalText = sendOtpBtn.textContent;
    sendOtpBtn.textContent = "Sending…";
    try {
      const res = await fetch("send_otp.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "email=" + encodeURIComponent(email)
      });
      const data = await res.json();
      if (data.success) {
        showStatus(data.message || "OTP sent to your email.", "success");
        startOtpCooldown(originalText);
      } else {
        showStatus(data.message || "Could not send OTP.", "error");
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = originalText;
      }
    } catch (err) {
      showStatus("Could not reach the server to send OTP. Please try again.", "error");
      sendOtpBtn.disabled = false;
      sendOtpBtn.textContent = originalText;
    }
  });

  function startOtpCooldown(originalText) {
    otpCooldown = 30;
    sendOtpBtn.disabled = true;
    sendOtpBtn.textContent = `Resend (${otpCooldown}s)`;
    clearInterval(otpTimer);
    otpTimer = setInterval(() => {
      otpCooldown -= 1;
      if (otpCooldown <= 0) {
        clearInterval(otpTimer);
        sendOtpBtn.disabled = false;
        sendOtpBtn.textContent = originalText;
      } else {
        sendOtpBtn.textContent = `Resend (${otpCooldown}s)`;
      }
    }, 1000);
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    hideStatus();

    if (passInput.value.length < 8) { showStatus("Password must be at least 8 characters.", "error"); return; }
    if (!checkPasswordsMatch()) { showStatus("Passwords do not match.", "error"); return; }
    if (!/^[0-9]{10}$/.test(phoneInput.value)) { showStatus("Mobile number must be 10 digits.", "error"); return; }
    if (!/^[0-9]{6}$/.test(otpInput.value)) { showStatus("Enter the 6-digit Email OTP.", "error"); return; }
    if (!captchaInput.value.trim()) { showStatus("Please enter the Captcha.", "error"); return; }

    const formData = new FormData(form);
    submitBtn.disabled = true;
    submitBtn.textContent = "Creating account…";

    try {
      const res = await fetch("student-register-save.php", { method: "POST", body: formData });
      const data = await res.json();

      if (data.success) {
        showStatus(data.message || "Account created successfully.", "success");
        submitBtn.textContent = "Redirecting…";
        window.location.href = data.redirect || "student/index.php";
        return;
      } else {
        showStatus(data.message || "Something went wrong.", "error");
      }
    } catch (err) {
      showStatus("Could not reach the server. Please try again.", "error");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Create student account";
      refreshCaptcha();
    }
  });

  function showStatus(message, type) {
    statusBox.textContent = message;
    statusBox.className = "form-status " + type;
    statusBox.style.display = "block";
    statusBox.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }
  function hideStatus() {
    statusBox.style.display = "none";
    statusBox.textContent = "";
    statusBox.className = "form-status";
  }
  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }
});
