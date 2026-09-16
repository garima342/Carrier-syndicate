<?php
// Expects $pdo to already exist
$profileStmt = $pdo->prepare("SELECT * FROM profile WHERE company_id = ?");
$profileStmt->execute([current_company_id()]);
$profile = $profileStmt->fetch();
if (!$profile) {
    $profile = ['company_name' => 'Your Company Name', 'bio' => '', 'logo_path' => null];
}

// Resolve logo path: logos uploaded via registration go to /internhub/logo/
// logos uploaded via the dashboard go to /internhub/dashboard/uploads/
$resolvedLogoUrl = '';
if ($profile['logo_path']) {
    $lp = $profile['logo_path'];
    // dashboard/uploads/ paths are relative to dashboard folder
    if (str_starts_with($lp, 'uploads/')) {
        $resolvedLogoUrl = $lp;
    } elseif (str_starts_with($lp, 'logo/')) {
        // root-relative logo (set during registration)
        $resolvedLogoUrl = APP_BASE_URL . '/' . $lp;
    } else {
        $resolvedLogoUrl = h($lp);
    }
}
$logoStyle = $resolvedLogoUrl
    ? "background-image:url('" . h($resolvedLogoUrl) . "');background-size:cover;background-position:center;"
    : '';

// Active nav detection
$currentPage = basename($_SERVER['PHP_SELF']);
function nav_active(string $file, string $current): string {
    return $file === $current ? ' active' : '';
}
?>
<aside class="sidebar">
  <div class="logo-slot" title="Click to upload your logo" onclick="document.getElementById('logoInput').click()">
    <div class="logo-mark" id="logoMark" style="<?= $logoStyle ?>">
      <?= $resolvedLogoUrl ? '' : '+' ?>
    </div>
    <div class="logo-caption"><strong>Company Logo</strong>Click to upload</div>
  </div>
  <input type="file" id="logoInput" accept="image/*" style="display:none">

  <div class="profile-block">
    <div class="p-name"><?= h($profile['company_name']) ?></div>
    <div class="p-bio"><?= h($profile['bio']) ?></div>
    <a href="profile.php" class="profile-edit-link">✎ Edit profile</a>
  </div>

  <nav class="side-nav">
    <a href="index.php" class="<?= nav_active('index.php', $currentPage) ?>">Dashboard</a>
    <a href="internships.php" class="<?= nav_active('internships.php', $currentPage) . nav_active('postings-view.php', $currentPage) ?>">Internships</a>
    <a href="jobs.php" class="<?= nav_active('jobs.php', $currentPage) ?>">Jobs</a>
    <a href="sessions.php" class="<?= nav_active('sessions.php', $currentPage) . nav_active('session-form.php', $currentPage) ?>">Online Sessions</a>
    <a href="slots.php" class="<?= nav_active('slots.php', $currentPage) . nav_active('slot-form.php', $currentPage) ?>">Open Slots</a>
    <a href="chat.php" class="<?= nav_active('chat.php', $currentPage) ?>">
      💬 Messages
      <?php
        $chatUnreadStmt = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE receiver_id = ? AND is_read = 0");
        $chatUnreadStmt->execute([current_company_id()]);
        $chatUnread = (int)$chatUnreadStmt->fetchColumn();
        if ($chatUnread > 0):
      ?><span style="background:#B3402D;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px;margin-left:4px;"><?= $chatUnread ?></span><?php endif; ?>
    </a>
    <a href="settings.php" class="<?= nav_active('settings.php', $currentPage) ?>">Settings</a>
    <a href="../logout.php">Log out</a>
  </nav>
</aside>
