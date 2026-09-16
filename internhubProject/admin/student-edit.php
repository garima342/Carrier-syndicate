<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();
if (!$student) { flash_error_set('Student not found.'); redirect('students.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $college = trim($_POST['college'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $graduation_year = trim($_POST['graduation_year'] ?? '');
    $email_verified = ($_POST['email_verified'] ?? 'No') === 'Yes' ? 'Yes' : 'No';

    if ($full_name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_error_set('Name and a valid email are required.');
    } else {
        $upd = $pdo->prepare(
            "UPDATE students SET full_name=?, email=?, phone=?, college=?, course=?, graduation_year=?, email_verified=? WHERE id=?"
        );
        $upd->execute([$full_name, $email, $phone, $college, $course, $graduation_year, $email_verified, $id]);
        flash_set('Student updated.');
        redirect('students.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit student — Admin</title>
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
      <div class="page-head"><h1>Edit student</h1><p><?= h($student['full_name']) ?></p></div>
      <form method="post" action="student-edit.php?id=<?= $id ?>" class="form-card">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="field"><label>Full name</label><input type="text" name="full_name" value="<?= h($student['full_name']) ?>" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" value="<?= h($student['email']) ?>" required></div>
        <div class="field"><label>Mobile number</label><input type="text" name="phone" value="<?= h($student['phone']) ?>"></div>
        <div class="field"><label>College</label><input type="text" name="college" value="<?= h($student['college']) ?>"></div>
        <div class="field"><label>Course</label><input type="text" name="course" value="<?= h($student['course']) ?>"></div>
        <div class="field"><label>Graduation year</label><input type="number" name="graduation_year" value="<?= h($student['graduation_year']) ?>"></div>
        <div class="field">
          <label>Email verified</label>
          <select name="email_verified">
            <option value="Yes" <?= $student['email_verified'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
            <option value="No" <?= $student['email_verified'] === 'No' ? 'selected' : '' ?>>No</option>
          </select>
        </div>
        <div class="card-actions">
          <button type="submit" class="btn btn-create">Save changes</button>
          <a href="students.php" class="btn btn-modify">Cancel</a>
        </div>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
