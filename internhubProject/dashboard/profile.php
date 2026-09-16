<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$profileStmt = $pdo->prepare("SELECT * FROM profile WHERE company_id = ?");
$profileStmt->execute([current_company_id()]);
$profile = $profileStmt->fetch();
if (!$profile) {
    $profile = ['company_name' => '', 'bio' => '', 'edits_count' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile — Company Dashboard</title>
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
        <h1>✎ Edit Company Profile</h1>
        <p>Profile edited <strong><?= (int)$profile['edits_count'] ?></strong> time<?= $profile['edits_count'] == 1 ? '' : 's' ?> so far. Logo uploads count too.</p>
      </div>

      <form class="form-card" method="post" action="profile-save.php">
        <?= csrf_field() ?>
        <label class="field">
          <span>Company name</span>
          <input type="text" name="company_name" required maxlength="150" value="<?= h($profile['company_name']) ?>">
        </label>

        <label class="field">
          <span>Short bio</span>
          <textarea name="bio" rows="3" placeholder="A line or two about what you do"><?= h($profile['bio']) ?></textarea>
        </label>

        <div class="form-actions">
          <a href="index.php" class="btn btn-modify">Cancel</a>
          <button type="submit" class="btn btn-create">Save changes</button>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
