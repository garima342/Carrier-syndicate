<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM students WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (full_name LIKE ? OR email LIKE ? OR college LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students — Admin</title>
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
      <div class="page-head"><h1>Students</h1><p>Every registered student on InternHub.</p></div>

      <form class="filter-bar" method="get" action="">
        <input type="text" name="q" placeholder="Search name, email or college…" value="<?= h($search) ?>">
        <button type="submit" class="btn btn-modify">🔍 Search</button>
        <?php if ($search !== ''): ?><a href="students.php" class="btn btn-danger">✕ Clear</a><?php endif; ?>
      </form>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;"><div class="ph-title">No students found</div></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>College</th><th>Course</th><th>Grad. year</th><th>Verified</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td class="row-title"><?= h($row['full_name']) ?></td>
                  <td><?= h($row['email']) ?></td>
                  <td><?= h($row['college']) ?></td>
                  <td><?= h($row['course']) ?></td>
                  <td><?= h($row['graduation_year']) ?></td>
                  <td><?= h($row['email_verified']) ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="student-edit.php?id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="delete.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="table" value="students">
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
