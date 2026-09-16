<?php
require_once __DIR__ . '/includes/functions.php';
require_student_login();
$sid = current_student_id();

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$sid]);
$student = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My profile — InternHub</title>
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
      <div class="page-head"><h1>My profile</h1><p>Keep your details up to date.</p></div>

      <form method="post" action="profile-save.php" class="form-card">
        <?= csrf_field() ?>
        <div class="field"><label>Full name</label><input type="text" name="full_name" value="<?= h($student['full_name']) ?>" required></div>
        <div class="field"><label>Email</label><input type="email" value="<?= h($student['email']) ?>" disabled></div>
        <div class="field"><label>Mobile number</label><input type="text" name="phone" maxlength="10" value="<?= h($student['phone']) ?>" required></div>
        <div class="field"><label>College / University</label><input type="text" name="college" value="<?= h($student['college']) ?>" required></div>
        <div class="field"><label>Course</label><input type="text" name="course" value="<?= h($student['course']) ?>" required></div>
        <div class="field"><label>Graduation year</label><input type="number" name="graduation_year" value="<?= h($student['graduation_year']) ?>" required></div>
        <button type="submit" class="btn btn-create">Save changes</button>
      </form>
    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
