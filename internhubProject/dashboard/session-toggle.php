<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('sessions.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("SELECT title, is_active FROM sessions WHERE id = ? AND company_id = ?");
    $stmt->execute([$id, current_company_id()]);
    $row = $stmt->fetch();
    if ($row) {
        $newState = $row['is_active'] ? 0 : 1;
        $upd = $pdo->prepare("UPDATE sessions SET is_active = ? WHERE id = ? AND company_id = ?");
        $upd->execute([$newState, $id, current_company_id()]);
        add_notification($pdo, "Session \"{$row['title']}\" is now " . ($newState ? 'active' : 'inactive') . '.');
    }
}

redirect('sessions.php');
