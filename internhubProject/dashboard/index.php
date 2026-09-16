<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

function postings_summary(PDO $pdo, int $companyId, string $type): array {
    $stmt = $pdo->prepare("SELECT
        COALESCE(SUM(open_positions),0)        AS open_positions,
        COALESCE(SUM(applications_received),0)  AS applications_received,
        COALESCE(SUM(accepted),0)               AS accepted,
        COALESCE(SUM(on_hold),0)                AS on_hold,
        COALESCE(SUM(rejected),0)               AS rejected
        FROM postings WHERE company_id = ? AND type = ?");
    $stmt->execute([$companyId, $type]);
    return $stmt->fetch();
}

$companyId = current_company_id();

$internStats = postings_summary($pdo, $companyId, 'internship');
$jobStats    = postings_summary($pdo, $companyId, 'job');

$activeStmt = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE is_active = 1 AND company_id = ?");
$activeStmt->execute([$companyId]);
$activeSessions = (int) $activeStmt->fetchColumn();

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE company_id = ?");
$totalStmt->execute([$companyId]);
$totalSessions = (int) $totalStmt->fetchColumn();

$slotStmt = $pdo->prepare("SELECT
    COUNT(*) AS categories,
    COALESCE(SUM(total_slots),0) AS total,
    COALESCE(SUM(filled_slots),0) AS filled
    FROM slots WHERE company_id = ?");
$slotStmt->execute([$companyId]);
$slotTotals = $slotStmt->fetch();
$allFull = $slotTotals['total'] > 0 && $slotTotals['filled'] >= $slotTotals['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Company Dashboard</title>
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
        <h1>Dashboard</h1>
        <p>Track internships, jobs, and sessions in one place.</p>
      </div>

      <div class="grid">

        <!-- INTERNSHIP -->
        <div class="card">
          <div class="card-head">
            <div class="card-title">
              <div class="card-icon">🎓</div>
              <h3>Internship</h3>
            </div>
          </div>

          <div class="stat-list">
            <div class="stat-row"><span class="stat-label">Open internships</span><a href="internships.php" class="stat-count"><?= (int)$internStats['open_positions'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Applications received</span><a href="internships.php" class="stat-count"><?= (int)$internStats['applications_received'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Accepted</span><a href="internships.php" class="stat-count"><?= (int)$internStats['accepted'] ?></a></div>
            <div class="stat-row"><span class="stat-label">On Hold</span><a href="internships.php" class="stat-count"><?= (int)$internStats['on_hold'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Rejected</span><a href="internships.php" class="stat-count"><?= (int)$internStats['rejected'] ?></a></div>
          </div>

          <div class="card-actions">
            <a href="posting-form.php?type=internship" class="btn btn-create">＋ Add</a>
            <a href="internships.php" class="btn btn-modify">✎ Modify</a>
          </div>

          <div class="card-note">Totals across all internship postings, live from the database. "Modify" lists every posting to edit or delete.</div>
        </div>

        <!-- JOB -->
        <div class="card">
          <div class="card-head">
            <div class="card-title">
              <div class="card-icon">💼</div>
              <h3>Job</h3>
            </div>
          </div>

          <div class="stat-list">
            <div class="stat-row"><span class="stat-label">Open positions</span><a href="jobs.php" class="stat-count"><?= (int)$jobStats['open_positions'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Applications received</span><a href="jobs.php" class="stat-count"><?= (int)$jobStats['applications_received'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Accepted</span><a href="jobs.php" class="stat-count"><?= (int)$jobStats['accepted'] ?></a></div>
            <div class="stat-row"><span class="stat-label">On Hold</span><a href="jobs.php" class="stat-count"><?= (int)$jobStats['on_hold'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Rejected</span><a href="jobs.php" class="stat-count"><?= (int)$jobStats['rejected'] ?></a></div>
          </div>

          <div class="card-actions">
            <a href="posting-form.php?type=job" class="btn btn-create">＋ Add</a>
            <a href="jobs.php" class="btn btn-modify">✎ Modify</a>
          </div>

          <div class="card-note">Totals across all job postings, live from the database. "Modify" lists every posting to edit or delete.</div>
        </div>

        <!-- ONLINE SESSION -->
        <div class="card">
          <div class="card-head">
            <div class="card-title">
              <div class="card-icon">🖥️</div>
              <h3>Online Session</h3>
            </div>
          </div>

          <div class="stat-list">
            <div class="stat-row">
              <span class="stat-label">Status</span>
              <span class="status-pill <?= $activeSessions > 0 ? 'status-active' : 'status-inactive' ?>">
                <?= $activeSessions > 0 ? 'Active' : 'Inactive' ?>
              </span>
            </div>
            <div class="stat-row"><span class="stat-label">Total sessions</span><a href="sessions.php" class="stat-count"><?= $totalSessions ?></a></div>
          </div>

          <div class="card-actions">
            <a href="session-form.php" class="btn btn-create">＋ Add</a>
            <a href="sessions.php" class="btn btn-modify">✎ Modify</a>
          </div>

          <div class="card-note">The company toggles each session active/inactive — status here reflects the database.</div>
        </div>

        <!-- OPEN SLOTS ("to be decided") -->
        <div class="card">
          <div class="card-head">
            <div class="card-title">
              <div class="card-icon">🗂️</div>
              <h3>Open Slots</h3>
            </div>
          </div>

          <div class="stat-list">
            <div class="stat-row"><span class="stat-label">Categories</span><a href="slots.php" class="stat-count"><?= (int)$slotTotals['categories'] ?></a></div>
            <div class="stat-row"><span class="stat-label">Total slots</span><span class="stat-count"><?= (int)$slotTotals['total'] ?></span></div>
            <div class="stat-row"><span class="stat-label">Filled</span><span class="stat-count"><?= (int)$slotTotals['filled'] ?></span></div>
            <div class="stat-row">
              <span class="stat-label">Availability</span>
              <span class="status-pill <?= $allFull ? 'status-inactive' : 'status-active' ?>">
                <?= $slotTotals['categories'] == 0 ? 'None yet' : ($allFull ? 'Full' : 'Open') ?>
              </span>
            </div>
          </div>

          <div class="card-actions">
            <a href="slot-form.php" class="btn btn-create">＋ Add</a>
            <a href="slots.php" class="btn btn-modify">✎ Modify</a>
          </div>

          <div class="card-note">The company sets how many slots exist per category; it shows "Full" once filled reaches the total.</div>
        </div>

      </div>
    </main>
  </div>

  <?php include 'includes/footer.php'; ?>
</div>

<script src="js/script.js"></script>
</body>
</html>
