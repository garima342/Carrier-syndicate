<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('settings.php');
csrf_verify();

$siteName = trim($_POST['site_name'] ?? '');
$email = trim($_POST['contact_email'] ?? '');
$notify = isset($_POST['notify_dashboard']) ? 1 : 0;

if ($siteName === '') {
    flash_set('Site name is required.');
    redirect('settings.php');
}

$stmt = $pdo->prepare("UPDATE settings SET site_name = ?, contact_email = ?, notify_dashboard = ? WHERE company_id = ?");
$stmt->execute([$siteName, $email, $notify, current_company_id()]);

flash_set('Settings saved.');
redirect('settings.php');
