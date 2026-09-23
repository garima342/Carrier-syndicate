<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? '';
$company = $_GET['company'] ?? '';

/* Validate type */
if (!in_array($type, ['internship', 'job'], true)) {
    $type = '';
}

/* Get companies for dropdown */
$companyStmt = $pdo->query(
    "SELECT id, company_name
     FROM companies
     ORDER BY company_name ASC"
);

$companies = $companyStmt->fetchAll();

/* Build postings query */
$sql = "SELECT p.*, c.company_name
        FROM postings p
        JOIN companies c ON c.id = p.company_id
        WHERE 1=1";

$params = [];

/* Type filter */
if ($type !== '') {
    $sql .= " AND p.type = ?";
    $params[] = $type;
}

/* Company filter */
if ($company !== '' && ctype_digit((string)$company)) {
    $sql .= " AND p.company_id = ?";
    $params[] = (int)$company;
} else {
    $company = '';
}

/* Search filter */
if ($search !== '') {
    $sql .= " AND (
        p.title LIKE ?
        OR c.company_name LIKE ?
    )";

    $searchValue = "%$search%";

    $params[] = $searchValue;
    $params[] = $searchValue;
}

/* Newest postings first */
$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Postings — Admin</title>
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
      <div class="page-head"><h1>Postings</h1><p>Every internship and job across all companies.</p></div>

      <form class="filter-bar" method="get" action="">

  <!-- Search -->
  <input
    type="text"
    name="q"
    placeholder="Search title or company..."
    value="<?= h($search) ?>"
  >

  <!-- Type -->
  <select name="type" onchange="this.form.submit()">
    <option value="">All Types</option>

    <option
      value="internship"
      <?= $type === 'internship' ? 'selected' : '' ?>
    >
      Internship
    </option>

    <option
      value="job"
      <?= $type === 'job' ? 'selected' : '' ?>
    >
      Job
    </option>
  </select>

  <!-- Company -->
  <select name="company" onchange="this.form.submit()">
    <option value="">All Companies</option>

    <?php foreach ($companies as $item): ?>
      <option
        value="<?= (int)$item['id'] ?>"
        <?= $company == $item['id'] ? 'selected' : '' ?>
      >
        <?= h($item['company_name']) ?>
      </option>
    <?php endforeach; ?>

  </select>

  <!-- Search button -->
  <button type="submit" class="btn btn-modify">
    🔍 Filter
  </button>

  <!-- Clear -->
  <?php if ($search !== '' || $type !== '' || $company !== ''): ?>

    <a href="postings.php" class="btn btn-danger">
      ✕ Clear
    </a>

  <?php endif; ?>

</form>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;"><div class="ph-title">No postings found</div></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Title</th><th>Company</th><th>Type</th><th>Open</th><th>Applications</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td class="row-title"><?= h($row['title']) ?></td>
                  <td><?= h($row['company_name']) ?></td>
                  <td><?= $row['type'] === 'internship' ? '🎓 Internship' : '💼 Job' ?></td>
                  <td><?= (int)$row['open_positions'] ?></td>
                  <td><?= (int)$row['applications_received'] ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="posting-edit.php?id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="delete.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="table" value="postings">
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
