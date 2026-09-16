<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$type = ($_POST['type'] ?? '') === 'job' ? 'job' : 'internship';

if ($id) {
    $stmt = $pdo->prepare("SELECT title FROM postings WHERE id = ? AND type = ? AND company_id = ?");
    $stmt->execute([$id, $type, current_company_id()]);
    $title = $stmt->fetchColumn();

    $del = $pdo->prepare("DELETE FROM postings WHERE id = ? AND type = ? AND company_id = ?");
    $del->execute([$id, $type, current_company_id()]);

    if ($title) {
        add_notification($pdo, ucfirst($type) . " posting \"$title\" was deleted.");
    }
    flash_set('Posting deleted.');
}

redirect($type === 'job' ? 'jobs.php' : 'internships.php');
