<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    csrf_verify();
    $id = (int) ($_POST['id'] ?? 0);
    $pdo->prepare("UPDATE sessions SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
    flash_set('Session status updated.');
    redirect('sessions.php');
}

$stmt = $pdo->query(
    "SELECT s.*, c.company_name FROM sessions s JOIN companies c ON c.id = s.company_id ORDER BY s.created_at DESC"
);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sessions — Admin</title>
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
      <div class="page-head"><h1>Sessions</h1><p>Every online session across all companies.</p></div>
      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;"><div class="ph-title">No sessions found</div></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Title</th><th>Company</th><th>Link</th><th>Status</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td class="row-title"><?= h($row['title']) ?></td>
                  <td><?= h($row['company_name']) ?></td>
                  <td><?= $row['session_link'] ? '<a href="'.h($row['session_link']).'" target="_blank">Open link</a>' : '—' ?></td>
                  <td><?= $row['is_active'] ? 'Active' : 'Inactive' ?></td>
                  <td class="row-actions">
                    <form action="sessions.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="toggle">
                      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                      <button type="submit" class="btn btn-modify"><?= $row['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form class="js-delete" action="delete.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="table" value="sessions">
                      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                      <button type="submit" class="btn btn-danger">🗑 Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<script>
document.querySelectorAll('.js-delete').forEach(f => f.addEventListener('submit', e => {
  if (!confirm('Delete this record? This cannot be undone.')) e.preventDefault();
}));
</script>
</body>
</html>
