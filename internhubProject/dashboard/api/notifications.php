<?php
require_once __DIR__ . '/../includes/functions.php';
require_login_api();

header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? 'list');

if ($action === 'mark_read') {
    $markStmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND company_id = ?");
    $markStmt->execute([current_company_id()]);
    echo json_encode(['success' => true]);
    exit;
}

// default: list latest 20 notifications
$rowsStmt = $pdo->prepare("SELECT id, message, is_read, created_at FROM notifications WHERE company_id = ? ORDER BY created_at DESC LIMIT 20");
$rowsStmt->execute([current_company_id()]);
$rows = $rowsStmt->fetchAll();
foreach ($rows as &$row) {
    $row['message'] = h($row['message']);
    $row['created_at'] = date('d M, H:i', strtotime($row['created_at']));
}
echo json_encode($rows);
