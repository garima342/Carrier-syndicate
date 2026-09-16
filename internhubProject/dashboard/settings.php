<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$settingsStmt = $pdo->prepare("SELECT * FROM settings WHERE company_id = ?");
$settingsStmt->execute([current_company_id()]);
$settings = $settingsStmt->fetch();
if (!$settings) {
    $settings = ['site_name' => 'Company Dashboard', 'contact_email' => '', 'notify_dashboard' => 1];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings — Company Dashboard</title>
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
        <h1>⚙️ Settings</h1>
        <p>Basic site settings, stored in the <code>settings</code> table.</p>
      </div>

      <form class="form-card" method="post" action="settings-save.php">
        <?= csrf_field() ?>
        <label class="field">
          <span>Site / dashboard name</span>
          <input type="text" name="site_name" required maxlength="150" value="<?= h($settings['site_name']) ?>">
        </label>

        <label class="field">
          <span>Contact email</span>
          <input type="email" name="contact_email" value="<?= h($settings['contact_email']) ?>" placeholder="admin@yourcompany.com">
        </label>

        <label class="checkbox-field">
          <input type="checkbox" name="notify_dashboard" value="1" <?= $settings['notify_dashboard'] ? 'checked' : '' ?>>
          <span>Show notification bell alerts for dashboard activity</span>
        </label>

        <div class="form-actions">
          <a href="index.php" class="btn btn-modify">Cancel</a>
          <button type="submit" class="btn btn-create">Save settings</button>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
