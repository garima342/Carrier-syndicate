<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
$settingsStmt = $pdo->prepare("SELECT contact_email FROM settings WHERE company_id = ?");
$settingsStmt->execute([current_company_id()]);
$settings = $settingsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Support — Company Dashboard</title>
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
      <div class="page-head"><h1>Support</h1><p>Need help? Reach out below.</p></div>
      <div class="static-page">
        <h3>Contact us</h3>
        <p>Email us at <strong><?= h($settings['contact_email'] ?? 'admin@example.com') ?></strong> — this address is pulled live from your Settings page.</p>
        <h3>Common questions</h3>
        <p>Add FAQs about applying to internships/jobs, joining online sessions, or checking slot availability.</p>
      </div>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
