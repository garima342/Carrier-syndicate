<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$rowsStmt = $pdo->prepare("SELECT * FROM sessions WHERE company_id = ? ORDER BY created_at DESC");
$rowsStmt->execute([current_company_id()]);
$rows = $rowsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Online Sessions — Company Dashboard</title>
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
          <h1>🖥️ Online Sessions</h1>
          <p>Add sessions and flip them active/inactive — the dashboard reflects this live.</p>
        </div>
        <a href="session-form.php" class="btn btn-create">＋ Add Session</a>
      </div>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;">
          <div class="ph-title">No sessions yet</div>
          <div>Click "Add Session" above to create the first one.</div>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr><th>Title</th><th>Link</th><th>Status</th><th>Updated</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td>
                    <div class="row-title"><?= h($row['title']) ?></div>
                    <?php if ($row['description']): ?><div class="row-sub"><?= h(mb_strimwidth($row['description'], 0, 80, '…')) ?></div><?php endif; ?>
                  </td>
                  <td class="row-sub"><?= $row['session_link'] ? '<a href="' . h($row['session_link']) . '" target="_blank" rel="noopener">Open link</a>' : '—' ?></td>
                  <td>
                    <form action="session-toggle.php" method="post" class="inline-toggle-form">
                      <?= csrf_field() ?>
                      <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                      <button type="submit" class="status-pill status-toggle <?= $row['is_active'] ? 'status-active' : 'status-inactive' ?>">
                        <?= $row['is_active'] ? 'Active' : 'Inactive' ?>
                      </button>
                    </form>
                  </td>
                  <td class="row-sub"><?= h(date('d M Y', strtotime($row['updated_at']))) ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="session-form.php?id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="session-delete.php" method="post">
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
