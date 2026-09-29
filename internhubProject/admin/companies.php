<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$search = trim($_GET['q'] ?? '');
$companyType = trim($_GET['company_type'] ?? '');
$industry = trim($_GET['industry'] ?? '');
$foundedYear = trim($_GET['founded_year'] ?? '');
$country = trim($_GET['country'] ?? '');
$state = trim($_GET['state'] ?? '');
$city = trim($_GET['city'] ?? '');

$sql = "SELECT * FROM companies WHERE 1=1";
$params = [];

/* Search filter */
if ($search !== '') {
    $sql .= " AND (company_name LIKE ? OR company_email LIKE ?)";
    $searchValue = "%$search%";
    $params[] = $searchValue;
    $params[] = $searchValue;
}

/* Company Type filter */
if ($companyType !== '') {
    $sql .= " AND company_type = ?";
    $params[] = $companyType;
}

/* Industry filter */
if ($industry !== '') {
    $sql .= " AND industry = ?";
    $params[] = $industry;
}

/* Founded Year filter */
if ($foundedYear !== '') {
    $sql .= " AND founded_year = ?";
    $params[] = $foundedYear;
}

/* Country filter */
if ($country !== '') {
    $sql .= " AND country = ?";
    $params[] = $country;
}

/* State filter */
if ($state !== '') {
    $sql .= " AND state = ?";
    $params[] = $state;
}

/* City filter */
if ($city !== '') {
    $sql .= " AND city = ?";
    $params[] = $city;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* Company Type dropdown values */
$companyTypeStmt = $pdo->query(
    "SELECT DISTINCT company_type
     FROM companies
     WHERE company_type IS NOT NULL
     AND company_type != ''
     ORDER BY company_type ASC"
);
$companyTypes = $companyTypeStmt->fetchAll(PDO::FETCH_COLUMN);

/* Industry dropdown values */
$industryStmt = $pdo->query(
    "SELECT DISTINCT industry
     FROM companies
     WHERE industry IS NOT NULL
     AND industry != ''
     ORDER BY industry ASC"
);
$industries = $industryStmt->fetchAll(PDO::FETCH_COLUMN);

/* Founded Year dropdown values */
$yearStmt = $pdo->query(
    "SELECT DISTINCT founded_year
     FROM companies
     WHERE founded_year IS NOT NULL
     AND founded_year != ''
     ORDER BY founded_year DESC"
);
$foundedYears = $yearStmt->fetchAll(PDO::FETCH_COLUMN);

/* Country dropdown values */
$countryStmt = $pdo->query(
    "SELECT DISTINCT country
     FROM companies
     WHERE country IS NOT NULL
     AND country != ''
     ORDER BY country ASC"
);
$countries = $countryStmt->fetchAll(PDO::FETCH_COLUMN);

/* State dropdown values */
$stateStmt = $pdo->query(
    "SELECT DISTINCT state
     FROM companies
     WHERE state IS NOT NULL
     AND state != ''
     ORDER BY state ASC"
);
$states = $stateStmt->fetchAll(PDO::FETCH_COLUMN);

/* City dropdown values */
$cityStmt = $pdo->query(
    "SELECT DISTINCT city
     FROM companies
     WHERE city IS NOT NULL
     AND city != ''
     ORDER BY city ASC"
);
$cities = $cityStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Companies — Admin</title>
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
      <div class="page-head"><h1>Companies</h1><p>Every registered employer on InternHub.</p></div>

      <form class="filter-bar" method="get" action="">

  <!-- Search -->
  <input
    type="text"
    name="q"
    placeholder="Search name or email..."
    value="<?= h($search) ?>"
  >

  <!-- Company Type -->
  <select name="company_type" onchange="this.form.submit()">
    <option value="">All Company Types</option>

    <?php foreach ($companyTypes as $item): ?>
      <option
        value="<?= h($item) ?>"
        <?= $companyType === $item ? 'selected' : '' ?>
      >
        <?= h($item) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- Industry -->
  <select name="industry" onchange="this.form.submit()">
    <option value="">All Industries</option>

    <?php foreach ($industries as $item): ?>
      <option
        value="<?= h($item) ?>"
        <?= $industry === $item ? 'selected' : '' ?>
      >
        <?= h($item) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- Founded Year -->
  <select name="founded_year" onchange="this.form.submit()">
    <option value="">All Founded Years</option>

    <?php foreach ($foundedYears as $year): ?>
      <option
        value="<?= h($year) ?>"
        <?= $foundedYear == $year ? 'selected' : '' ?>
      >
        <?= h($year) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- Country -->
  <select name="country" onchange="this.form.submit()">
    <option value="">All Countries</option>

    <?php foreach ($countries as $item): ?>
      <option
        value="<?= h($item) ?>"
        <?= $country === $item ? 'selected' : '' ?>
      >
        <?= h($item) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- State -->
  <select name="state" onchange="this.form.submit()">
    <option value="">All States</option>

    <?php foreach ($states as $item): ?>
      <option
        value="<?= h($item) ?>"
        <?= $state === $item ? 'selected' : '' ?>
      >
        <?= h($item) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- City -->
  <select name="city" onchange="this.form.submit()">
    <option value="">All Cities</option>

    <?php foreach ($cities as $item): ?>
      <option
        value="<?= h($item) ?>"
        <?= $city === $item ? 'selected' : '' ?>
      >
        <?= h($item) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <!-- Search -->
  <button type="submit" class="btn btn-modify">
    🔍 Search
  </button>

  <!-- Clear -->
  <?php if (
    $search !== '' ||
    $companyType !== '' ||
    $industry !== '' ||
    $foundedYear !== '' ||
    $country !== '' ||
    $state !== '' ||
    $city !== ''
  ): ?>

    <a href="companies.php" class="btn btn-danger">
      ✕ Clear
    </a>

  <?php endif; ?>

</form>

      <?php if (!$rows): ?>
        <div class="placeholder-box" style="min-height:160px;"><div class="ph-title">No companies found</div></div>
      <?php else: ?>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
<tr>
  <th>Company</th>
  <th>Email</th>
  <th>Company Type</th>
  <th>Industry</th>
  <th>Founded Year</th>
  <th>Location</th>
  <th>Recruiter</th>
  <th>Verified</th>
  <th>Joined</th>
  <th></th>
</tr>
</thead>
            <tbody>
              <?php foreach ($rows as $row): ?>
                <tr>
                  <td class="row-title"><?= h($row['company_name']) ?></td>

<td><?= h($row['company_email']) ?></td>

<td><?= h($row['company_type']) ?></td>

<td><?= h($row['industry']) ?></td>

<td><?= h($row['founded_year']) ?></td>

<td>
  <?= h($row['city']) ?>,
  <?= h($row['state']) ?>,
  <?= h($row['country']) ?>
</td>

<td><?= h($row['recruiter_name']) ?></td>
                  <td><?= h($row['email_verified']) ?></td>
                  <td class="row-sub"><?= h(date('d M Y', strtotime($row['created_at']))) ?></td>
                  <td class="row-actions">
                    <a class="btn btn-modify" href="company-edit.php?id=<?= (int)$row['id'] ?>">✎ Edit</a>
                    <form class="js-delete" action="delete.php" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="table" value="companies">
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
