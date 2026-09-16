<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();
$stmt = $pdo->query(
    "SELECT s.*, c.company_name FROM slots s JOIN companies c ON c.id = s.company_id ORDER BY s.created_at DESC"
);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Open slots — Admin</title>
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
      <div class="page-head"><h1>Open slots</h1><p>Every "to be decided" slot category across all companies.</p></div>
      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;"><div class="ph-title">No slots found</div></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Title</th><th>Company</th><th>Total</th><th>Filled</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td class="row-title"><?= h($row['title']) ?></td>
                  <td><?= h($row['company_name']) ?></td>
                  <td><?= (int)$row['total_slots'] ?></td>
                  <td><?= (int)$row['filled_slots'] ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="slot-edit.php?id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="delete.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="table" value="slots">
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
