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
<title>Terms of Service — <?= h($settings['site_name']) ?></title>
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
        <h1>Terms of Service</h1>
        <p>Last updated: <?= date('d F Y') ?></p>
      </div>
      <div class="static-page">

        <h3>1. Acceptance of terms</h3>
        <p>By registering a company account on InternHub and accessing the dashboard, you agree to be bound by these Terms of Service. If you do not agree, please do not use this platform.</p>

        <h3>2. Account registration</h3>
        <p>You must provide accurate and complete information during registration, including a valid CIN number, company email, and recruiter contact details. You are responsible for maintaining the confidentiality of your login credentials. You must notify us immediately of any unauthorised access to your account.</p>

        <h3>3. Permitted use</h3>
        <p>This platform is provided exclusively for registered companies to:</p>
        <p>— Post genuine internship and job opportunities.<br>
           — Manage online sessions and open slots for candidates.<br>
           — Track application statistics for your own postings.<br>
           — Communicate with other registered companies via the Messages feature.</p>

        <h3>4. Prohibited conduct</h3>
        <p>You agree not to:</p>
        <p>— Post fraudulent, misleading, or discriminatory internship or job listings.<br>
           — Use the platform to harvest applicant data for purposes other than legitimate hiring.<br>
           — Attempt to access, modify, or delete another company's data.<br>
           — Use automated tools to scrape or flood the platform with requests.<br>
           — Upload content that is unlawful, offensive, or infringes any third-party rights.</p>

        <h3>5. Postings and content</h3>
        <p>You retain ownership of the postings and content you create. By publishing a posting, you grant InternHub a limited, non-exclusive licence to display that content on the platform. You are solely responsible for the accuracy and legality of your postings.</p>

        <h3>6. Account termination</h3>
        <p>We reserve the right to suspend or terminate accounts that violate these terms, post fraudulent listings, or engage in abusive behaviour, without prior notice. You may also delete your own account by contacting us at the email listed in your Settings page.</p>

        <h3>7. Limitation of liability</h3>
        <p>InternHub is provided "as is". We make no warranties about uptime, data loss, or fitness for a particular purpose. To the maximum extent permitted by law, we are not liable for any indirect, incidental, or consequential damages arising from your use of this platform.</p>

        <h3>8. Changes to these terms</h3>
        <p>We may update these terms at any time. The "Last updated" date at the top of this page will reflect changes. Continued use of the platform after changes are posted constitutes acceptance of the revised terms.</p>

        <h3>9. Governing law</h3>
        <p>These terms are governed by the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts in the state where the operating company is registered.</p>

        <h3>10. Contact</h3>
        <p>Questions about these terms? Email: <strong><?= h($settings['contact_email'] ?: 'admin@example.com') ?></strong></p>

      </div>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
