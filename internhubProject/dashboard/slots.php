<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$rowsStmt = $pdo->prepare("SELECT * FROM slots WHERE company_id = ? ORDER BY created_at DESC");
$rowsStmt->execute([current_company_id()]);
$rows = $rowsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Open Slots — Company Dashboard</title>
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
      <div class="page-head list-page-head">
        <div>
          <h1>🗂️ Open Slots</h1>
          <p>Define categories, set total slots, and track how many are filled.</p>
        </div>
        <a href="slot-form.php" class="btn btn-create">＋ Add Slot Category</a>
      </div>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;">
          <div class="ph-title">No slot categories yet</div>
          <div>Click "Add Slot Category" above to create the first one.</div>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr><th>Title</th><th>Total slots</th><th>Filled</th><th>Availability</th><th>Updated</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row):
                $isFull = $row['total_slots'] > 0 && $row['filled_slots'] >= $row['total_slots'];
                $remaining = max(0, $row['total_slots'] - $row['filled_slots']);
              ?>
                <tr>
                  <td><div class="row-title"><?= h($row['title']) ?></div></td>
                  <td><?= (int)$row['total_slots'] ?></td>
                  <td><?= (int)$row['filled_slots'] ?></td>
                  <td>
                    <span class="status-pill <?= $isFull ? 'status-inactive' : 'status-active' ?>">
                      <?= $isFull ? 'Full' : $remaining . ' left' ?>
                    </span>
                  </td>
                  <td class="row-sub"><?= h(date('d M Y', strtotime($row['updated_at']))) ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="slot-form.php?id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="slot-delete.php" method="post">
                      <?= csrf_field() ?>
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
<script src="js/script.js"></script>
</body>
</html>
