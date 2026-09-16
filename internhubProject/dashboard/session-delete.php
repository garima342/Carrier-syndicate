<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('sessions.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("SELECT title FROM sessions WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, current_company_id()]);
    $title = $stmt->fetchColumn();

    $del = $pdo->prepare("DELETE FROM sessions WHERE id = ? AND company_id = ?");
    $del->execute([$id, current_company_id()]);

    if ($title) add_notification($pdo, "Session \"$title\" was deleted.");
    flash_set('Session deleted.');
}

redirect('sessions.php');
