<?php
$currentPage = basename($_SERVER['PHP_SELF']);
function nav_active_s(string $file, string $current): string {
    return $file === $current ? ' active' : '';
}
?>
<aside class="sidebar">
  <div class="logo-slot">
    <div class="logo-mark">🎓</div>
    <div class="logo-caption"><strong><?= h($_SESSION['student_name'] ?? 'Student') ?></strong>Student account</div>
  </div>

  <nav class="side-nav">
    <a href="index.php" class="<?= nav_active_s('index.php', $currentPage) ?>">Dashboard</a>
    <a href="postings.php" class="<?= nav_active_s('postings.php', $currentPage) ?>">Browse internships &amp; jobs</a>
    <a href="applications.php" class="<?= nav_active_s('applications.php', $currentPage) ?>">My applications</a>
    <a href="profile.php" class="<?= nav_active_s('profile.php', $currentPage) ?>">Profile</a>
  </nav>
</aside>
