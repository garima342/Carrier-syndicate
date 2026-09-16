<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('profile.php');
csrf_verify();

$name = trim($_POST['company_name'] ?? '');
$bio = trim($_POST['bio'] ?? '');

if ($name === '') {
    flash_set('Company name is required.');
    redirect('profile.php');
}

$stmt = $pdo->prepare("UPDATE profile SET company_name = ?, bio = ?, edits_count = edits_count + 1 WHERE company_id = ?");
$stmt->execute([$name, $bio, current_company_id()]);

add_notification($pdo, 'Company profile was updated.');
flash_set('Profile updated.');
redirect('profile.php');
