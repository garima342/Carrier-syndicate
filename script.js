// script.js — InternHub company registration form behavior

document.addEventListener("DOMContentLoaded", () => {
  const form         = document.getElementById("registerForm");
  const logoBox      = document.getElementById("logoBox");
  const logoInput    = document.getElementById("logoInput");
  const statusBox    = document.getElementById("formStatus");
  const submitBtn    = document.getElementById("submitBtn");
  const passInput    = document.getElementById("pass");
  const pass2Input   = document.getElementById("pass2");
  const passError    = document.getElementById("passError");

  const cinInput          = document.getElementById("cin");
  const websiteInput      = document.getElementById("website");
  const recruiterInput    = document.getElementById("recruiter");
  const designationInput  = document.getElementById("designation");
  const officialEmailInput= document.getElementById("official_email");
  const mobileInput       = document.getElementById("mobile");
  const pinInput          = document.getElementById("pin");
  const otpInput          = document.getElementById("otp");
  const termsInput        = document.getElementById("terms");
  const captchaInput      = document.getElementById("captcha"); // was missing — caused submit to silently fail

  const sendOtpBtn   = document.getElementById("sendOtpBtn");
  const companyEmailInput = document.querySelector('input[name="company_email"]');
  const captchaImage = document.getElementById("captchaImage");
  const refreshCaptchaBtn = document.getElementById("refreshCaptcha");

  // ---- Logo preview -------------------------------------------------
  logoBox.addEventListener("click", () => logoInput.click());

  logoInput.addEventListener("change", () => {
    const file = logoInput.files[0];
    if (!file) return;

    const validTypes = ["image/png", "image/jpeg"];
    if (!validTypes.includes(file.type)) {
      showStatus("Logo must be a PNG or JPG file.", "error");
      logoInput.value = "";
      return;
    }
    if (file.size > 2 * 1024 * 1024) {
      showStatus("Logo must be under 2MB.", "error");
      logoInput.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      logoBox.innerHTML = `<img src="${e.target.result}" alt="Company logo preview">`;
    };
    reader.readAsDataURL(file);
  });

  // ---- CAPTCHA refresh ------------------------------------------------
  // (single listener — was previously duplicated in two places)
  refreshCaptchaBtn.addEventListener("click", () => {
    captchaImage.src = "captcha.php?" + new Date().getTime();
  });

  function refreshCaptcha() {
    captchaImage.src = "captcha.php?" + new Date().getTime();
  }

  // ---- Live password match check ------------------------------------
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

  // ---- Send OTP -------------------------------------------------------
  let otpCooldown = 0;
  let otpTimer = null;

  sendOtpBtn.addEventListener("click", async () => {
    const email = companyEmailInput.value.trim();
    if (!isValidEmail(email)) {
      showStatus("Enter a valid Company Email before requesting an OTP.", "error");
      companyEmailInput.focus();
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

  // ---- Form submission -----------------------------------------------
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    hideStatus();

    if (passInput.value.length < 8) {
      showStatus("Password must be at least 8 characters.", "error");
      return;
    }
    if (!checkPasswordsMatch()) {
      showStatus("Passwords do not match.", "error");
      return;
    }
    if (!cinInput.value.trim()) {
      showStatus("CIN number is mandatory.", "error"); return;
    }
    if (websiteInput.value && !isValidURL(websiteInput.value)) {
      showStatus("Invalid company website.", "error"); return;
    }
    if (!recruiterInput.value.trim()) {
      showStatus("Recruiter name is required.", "error"); return;
    }
    if (!designationInput.value.trim()) {
      showStatus("Designation is required.", "error"); return;
    }
    if (!isValidEmail(officialEmailInput.value)) {
      showStatus("Valid official email required.", "error"); return;
    }
    if (!/^[0-9]{10}$/.test(mobileInput.value)) {
      showStatus("Mobile number must be 10 digits.", "error"); return;
    }
    if (!/^[0-9]{6}$/.test(pinInput.value)) {
      showStatus("PIN code must be 6 digits.", "error"); return;
    }
    if (!/^[0-9]{6}$/.test(otpInput.value)) {
      showStatus("Enter the 6-digit Email OTP.", "error"); return;
    }
    if (!termsInput.checked) {
      showStatus("You must accept the Terms & Conditions.", "error"); return;
    }
    if (!captchaInput.value.trim()) {
      showStatus("Please enter the Captcha.", "error"); return;
    }

    // Note: the file input (name="company_logo") is already part of the form,
    // so FormData(form) picks it up automatically — no need to re-set it.
    const formData = new FormData(form);

    submitBtn.disabled = true;
    submitBtn.textContent = "Creating account…";

    try {
      const res = await fetch("register.php", {
        method: "POST",
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        showStatus(data.message || "Account created successfully.", "success");
        form.reset();
        logoBox.innerHTML = "Upload";
      } else {
        showStatus(data.message || "Something went wrong.", "error");
      }
    } catch (err) {
      showStatus("Could not reach the server. Please try again.", "error");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Create company account";
      refreshCaptcha(); // always refresh captcha after an attempt, success or fail
    }
  });

  // ---- Helpers ------------------------------------------------------
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

  function isValidURL(url) {
    try { new URL(url); return true; } catch { return false; }
  }
});