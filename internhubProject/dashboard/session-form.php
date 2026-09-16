<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$session = ['title' => '', 'description' => '', 'session_link' => '', 'is_active' => 0];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, current_company_id()]);
    $found = $stmt->fetch();
    if (!$found) redirect('sessions.php');
    $session = $found;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id ? 'Edit' : 'Add' ?> Session — Company Dashboard</title>
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
        <h1><?= $id ? '✎ Edit' : '＋ Add' ?> Online Session</h1>
        <p>Set whether the session is active — the dashboard status badge reads this field.</p>
      </div>

      <form class="form-card" method="post" action="session-save.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)($session['id'] ?? 0) ?>">

        <label class="field">
          <span>Title</span>
          <input type="text" name="title" required maxlength="150" value="<?= h($session['title']) ?>" placeholder="e.g. Weekly Info Session">
        </label>

        <label class="field">
          <span>Description</span>
          <textarea name="description" rows="3" placeholder="What happens in this session"><?= h($session['description']) ?></textarea>
        </label>

        <label class="field">
          <span>Session link (Zoom / Meet / etc.)</span>
          <input type="url" name="session_link" value="<?= h($session['session_link']) ?>" placeholder="https://...">
        </label>

        <label class="checkbox-field">
          <input type="checkbox" name="is_active" value="1" <?= $session['is_active'] ? 'checked' : '' ?>>
          <span>Mark this session as active</span>
        </label>

        <div class="form-actions">
          <a href="sessions.php" class="btn btn-modify">Cancel</a>
          <button type="submit" class="btn btn-create"><?= $id ? 'Save changes' : 'Create session' ?></button>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
