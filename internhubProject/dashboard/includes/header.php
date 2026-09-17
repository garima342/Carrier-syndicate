<?php
// Expects $pdo to already exist (included after functions.php)
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE is_read = 0 AND company_id = ?");
$unreadStmt->execute([current_company_id()]);
$unread = (int) $unreadStmt->fetchColumn();
$flash      = flash_get();
$flashError = flash_error_get();

// Resolve company logo for topbar (same logic as sidebar)
$topbarLogoPath = $profile['logo_path'] ?? null;
$topbarLogoUrl  = '';
if ($topbarLogoPath) {
    if (str_starts_with($topbarLogoPath, 'uploads/')) {
        $topbarLogoUrl = $topbarLogoPath;
    } elseif (str_starts_with($topbarLogoPath, 'logo/')) {
        $topbarLogoUrl = APP_BASE_URL . '/' . $topbarLogoPath;
    } else {
        $topbarLogoUrl = h($topbarLogoPath);
    }
}
?>
<header class="topbar">
  <div class="topbar-left">
    <img src="../logo/logo.jpeg" alt="Carrier Syndicate" style="height:36px;margin-right:10px;">
    <div class="topbar-logo">
      <?php if ($topbarLogoUrl): ?>
        <img src="<?= h($topbarLogoUrl) ?>" alt="Logo" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
      <?php else: ?>
        IH
      <?php endif; ?>
    </div>
    <div class="topbar-title"><?= h($_SESSION['company_name'] ?? 'Company Dashboard') ?></div>
  </div>
  <div class="topbar-right">
    <div class="icon-btn" id="notifBtn" title="Notifications">
      🔔
      <?php if ($unread > 0): ?><span class="notif-badge" id="notifBadge"><?= $unread ?></span><?php endif; ?>
      <div class="notif-dropdown hidden" id="notifDropdown">
        <div class="notif-loading">Loading…</div>
      </div>
    </div>
    <a class="icon-btn" href="settings.php" title="Settings">⚙️</a>
  </div>
</header>
<?php if ($flash): ?>
  <div class="flash-banner flash-success"><?= h($flash) ?></div>
<?php endif; ?>
<?php if ($flashError): ?>
  <div class="flash-banner flash-error"><?= h($flashError) ?></div>
<?php endif; ?>
