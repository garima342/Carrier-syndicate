<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$slot = ['title' => '', 'total_slots' => 0, 'filled_slots' => 0];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM slots WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, current_company_id()]);
    $found = $stmt->fetch();
    if (!$found) redirect('slots.php');
    $slot = $found;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id ? 'Edit' : 'Add' ?> Slot Category — Company Dashboard</title>
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
        <h1><?= $id ? '✎ Edit' : '＋ Add' ?> Slot Category</h1>
        <p>The company decides the total slots — the list page shows "Full" once filled reaches that number.</p>
      </div>

      <form class="form-card" method="post" action="slot-save.php">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int)($slot['id'] ?? 0) ?>">

        <label class="field">
          <span>Title</span>
          <input type="text" name="title" required maxlength="150" value="<?= h($slot['title']) ?>" placeholder="e.g. Mentorship Program Batch 3">
        </label>

        <div class="field-grid field-grid-2">
          <label class="field">
            <span>Total slots</span>
            <input type="number" name="total_slots" min="0" value="<?= (int)$slot['total_slots'] ?>">
          </label>
          <label class="field">
            <span>Filled slots</span>
            <input type="number" name="filled_slots" min="0" value="<?= (int)$slot['filled_slots'] ?>">
          </label>
        </div>

        <div class="form-actions">
          <a href="slots.php" class="btn btn-modify">Cancel</a>
          <button type="submit" class="btn btn-create"><?= $id ? 'Save changes' : 'Create category' ?></button>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script src="js/script.js"></script>
</body>
</html>
