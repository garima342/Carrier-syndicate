<?php require_once __DIR__ . '/includes/functions.php';
require_login();
$settingsStmt = $pdo->prepare("SELECT contact_email, site_name FROM settings WHERE company_id = ?");
$settingsStmt->execute([current_company_id()]);
$settings = $settingsStmt->fetch() ?: ['contact_email' => '', 'site_name' => 'Company Dashboard'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy — <?= h($settings['site_name']) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="shell">
  <?php include 'includes/header.php'; ?>
  <div class="body">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main">
      <div class="page-head">
        <h1>Privacy Policy</h1>
        <p>Last updated: <?= date('d F Y') ?></p>
      </div>
      <div class="static-page">

        <h3>1. Who we are</h3>
        <p><?= h($settings['site_name']) ?> is a company dashboard operated by the registered company on InternHub. This policy explains how data collected through this platform is handled.</p>

        <h3>2. Information we collect</h3>
        <p>We collect the following categories of data:</p>
        <p><strong>Company account data</strong> — company name, CIN number, industry, size, website, registered address, and recruiter contact details provided during registration.</p>
        <p><strong>Login credentials</strong> — your company email and a securely hashed (bcrypt) password. We never store your plain-text password.</p>
        <p><strong>Dashboard activity data</strong> — internship and job postings you create, session records, open slot categories, and profile updates.</p>
        <p><strong>Notifications</strong> — system-generated messages triggered by your own dashboard actions (creating, editing, or deleting postings/sessions/slots).</p>

        <h3>3. How we use your data</h3>
        <p>Your data is used exclusively to operate your company dashboard. Specifically:</p>
        <p>— To authenticate you and display your company's own postings, sessions, and slots.<br>
           — To send OTP emails for registration email verification.<br>
           — To send password reset emails when requested.<br>
           — To display notification history for your account activity.</p>
        <p>We do not sell, rent, or share your data with third parties for marketing purposes.</p>

        <h3>4. Data storage and security</h3>
        <p>All data is stored in a MySQL database on the server hosting this application. Passwords are hashed with bcrypt. Password reset tokens are single-use and expire after 1 hour. Uploaded logos are stored in the server's <code>uploads/</code> folder and are only accessible via authenticated sessions.</p>

        <h3>5. Cookies and sessions</h3>
        <p>This application uses a single PHP session cookie to maintain your login state. No third-party tracking cookies or analytics scripts are used by default.</p>

        <h3>6. Data retention</h3>
        <p>Your data is retained for as long as your company account is active. Deleting a posting, session, or slot removes it from the database immediately. If you wish to have your entire company account deleted, contact us using the email below.</p>

        <h3>7. Your rights</h3>
        <p>You have the right to access, correct, or request deletion of your personal data at any time. Contact us at the address below and we will respond within 30 days.</p>

        <h3>8. Third-party services</h3>
        <p>Registration email verification and password reset emails are sent via Gmail SMTP (Google). Your email address is passed to Google's servers only for the purpose of delivering these transactional emails. Please refer to <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google's Privacy Policy</a> for how they handle email data.</p>

        <h3>9. Changes to this policy</h3>
        <p>We may update this policy occasionally. The "Last updated" date at the top of this page will reflect any changes. Continued use of the dashboard after an update constitutes acceptance.</p>

        <h3>10. Contact</h3>
        <p>For privacy-related questions or requests, email us at: <strong><?= h($settings['contact_email'] ?: 'admin@example.com') ?></strong></p>

      </div>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
