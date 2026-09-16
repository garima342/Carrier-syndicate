<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT s.*, c.company_name FROM slots s JOIN companies c ON c.id = s.company_id WHERE s.id = ?");
$stmt->execute([$id]);
$slot = $stmt->fetch();
if (!$slot) { flash_error_set('Slot not found.'); redirect('slots.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $title = trim($_POST['title'] ?? '');
    $total = (int) ($_POST['total_slots'] ?? 0);
    $filled = (int) ($_POST['filled_slots'] ?? 0);

    if ($title === '') {
        flash_error_set('Title is required.');
    } else {
        $upd = $pdo->prepare("UPDATE slots SET title=?, total_slots=?, filled_slots=? WHERE id=?");
        $upd->execute([$title, $total, $filled, $id]);
        flash_set('Slot updated.');
        redirect('slots.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit slot — Admin</title>
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
      <div class="page-head"><h1>Edit slot</h1><p><?= h($slot['company_name']) ?></p></div>
      <form method="post" action="slot-edit.php?id=<?= $id ?>" class="form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="field"><label>Title</label><input type="text" name="title" value="<?= h($slot['title']) ?>" required></div>
        <div class="field"><label>Total slots</label><input type="number" name="total_slots" value="<?= (int)$slot['total_slots'] ?>" min="0"></div>
        <div class="field"><label>Filled slots</label><input type="number" name="filled_slots" value="<?= (int)$slot['filled_slots'] ?>" min="0"></div>
        <div class="card-actions">
          <button type="submit" class="btn btn-create">Save changes</button>
          <a href="slots.php" class="btn btn-modify">Cancel</a>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
