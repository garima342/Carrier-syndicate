<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('sessions.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$link = trim($_POST['session_link'] ?? '');
$isActive = isset($_POST['is_active']) ? 1 : 0;

if ($title === '') {
    flash_set('Title is required — please try again.');
    redirect('session-form.php' . ($id ? '?id=' . $id : ''));
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE sessions SET title=?, description=?, session_link=?, is_active=? WHERE id=? AND company_id=?");
    $stmt->execute([$title, $description, $link, $isActive, $id, current_company_id()]);
    add_notification($pdo, "Session \"$title\" was updated.");
    flash_set('Session updated.');
} else {
    $stmt = $pdo->prepare("INSERT INTO sessions (company_id, title, description, session_link, is_active) VALUES (?,?,?,?,?)");
    $stmt->execute([current_company_id(), $title, $description, $link, $isActive]);
    add_notification($pdo, "Session \"$title\" was added.");
    flash_set('Session created.');
}

redirect('sessions.php');
