<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$type = ($_POST['type'] ?? '') === 'job' ? 'job' : 'internship';
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$open = max(0, (int) ($_POST['open_positions'] ?? 0));
$applications = max(0, (int) ($_POST['applications_received'] ?? 0));
$accepted = max(0, (int) ($_POST['accepted'] ?? 0));
$onHold = max(0, (int) ($_POST['on_hold'] ?? 0));
$rejected = max(0, (int) ($_POST['rejected'] ?? 0));

if ($title === '') {
    flash_set('Title is required — please try again.');
    redirect('posting-form.php?type=' . urlencode($type) . ($id ? '&id=' . $id : ''));
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE postings SET title=?, description=?, open_positions=?, applications_received=?, accepted=?, on_hold=?, rejected=? WHERE id=? AND type=? AND company_id=?");
    $stmt->execute([$title, $description, $open, $applications, $accepted, $onHold, $rejected, $id, $type, current_company_id()]);
    add_notification($pdo, ucfirst($type) . " posting \"$title\" was updated.");
    flash_set('Posting updated.');
} else {
    $stmt = $pdo->prepare("INSERT INTO postings (company_id, type, title, description, open_positions, applications_received, accepted, on_hold, rejected) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([current_company_id(), $type, $title, $description, $open, $applications, $accepted, $onHold, $rejected]);
    add_notification($pdo, ucfirst($type) . " posting \"$title\" was added.");
    flash_set('Posting created.');
}

redirect($type === 'job' ? 'jobs.php' : 'internships.php');
