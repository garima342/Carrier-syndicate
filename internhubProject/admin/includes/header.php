<?php
$flash      = flash_get();
$flashError = flash_error_get();
?>
<header class="topbar">
  <div class="topbar-left">
    <div class="topbar-logo"><img src="../logo/logo.png" alt="Carrier Syndicate" class="logo-img" style="width:100%;height:100%;object-fit:contain;display:block;"></div>
    <div class="topbar-title">Admin — <?= h($_SESSION['admin_name'] ?? 'Admin') ?></div>
  </div>
  <div class="topbar-right">
    <a class="icon-btn" href="logout.php" title="Log out">↩</a>
  </div>
</header>
<?php if ($flash): ?><div class="flash-banner flash-success"><?= h($flash) ?></div><?php endif; ?>
<?php if ($flashError): ?><div class="flash-banner flash-error"><?= h($flashError) ?></div><?php endif; ?>