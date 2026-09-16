<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
$stmt->execute([$id]);
$company = $stmt->fetch();
if (!$company) { flash_error_set('Company not found.'); redirect('companies.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $company_name = trim($_POST['company_name'] ?? '');
    $company_email = trim($_POST['company_email'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $recruiter_name = trim($_POST['recruiter_name'] ?? '');
    $mobile_number = trim($_POST['mobile_number'] ?? '');
    $email_verified = ($_POST['email_verified'] ?? 'No') === 'Yes' ? 'Yes' : 'No';

    if ($company_name === '' || !filter_var($company_email, FILTER_VALIDATE_EMAIL)) {
        flash_error_set('Company name and a valid email are required.');
    } else {
        $upd = $pdo->prepare(
            "UPDATE companies SET company_name=?, company_email=?, industry=?, recruiter_name=?, mobile_number=?, email_verified=? WHERE id=?"
        );
        $upd->execute([$company_name, $company_email, $industry, $recruiter_name, $mobile_number, $email_verified, $id]);
        flash_set('Company updated.');
        redirect('companies.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit company — Admin</title>
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
      <div class="page-head"><h1>Edit company</h1><p><?= h($company['company_name']) ?></p></div>
      <form method="post" action="company-edit.php?id=<?= $id ?>" class="form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="field"><label>Company name</label><input type="text" name="company_name" value="<?= h($company['company_name']) ?>" required></div>
        <div class="field"><label>Company email</label><input type="email" name="company_email" value="<?= h($company['company_email']) ?>" required></div>
        <div class="field"><label>Industry</label><input type="text" name="industry" value="<?= h($company['industry']) ?>"></div>
        <div class="field"><label>Recruiter name</label><input type="text" name="recruiter_name" value="<?= h($company['recruiter_name']) ?>"></div>
        <div class="field"><label>Mobile number</label><input type="text" name="mobile_number" value="<?= h($company['mobile_number']) ?>"></div>
        <div class="field">
          <label>Email verified</label>
          <select name="email_verified">
            <option value="Yes" <?= $company['email_verified'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
            <option value="No" <?= $company['email_verified'] === 'No' ? 'selected' : '' ?>>No</option>
          </select>
        </div>
        <div class="card-actions">
          <button type="submit" class="btn btn-create">Save changes</button>
          <a href="companies.php" class="btn btn-modify">Cancel</a>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
