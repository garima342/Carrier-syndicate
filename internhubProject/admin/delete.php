<?php
require_once __DIR__ . '/includes/functions.php';
require_admin();
csrf_verify();

$allowed = [
    'companies' => 'companies.php',
    'students'  => 'students.php',
    'postings'  => 'postings.php',
    'sessions'  => 'sessions.php',
    'slots'     => 'slots.php',
];

$table = $_POST['table'] ?? '';
$id    = (int) ($_POST['id'] ?? 0);

if (!isset($allowed[$table]) || $id <= 0) {
    flash_error_set('Invalid delete request.');
    redirect('index.php');
}

$stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
$stmt->execute([$id]);

flash_set('Deleted successfully.');
redirect($allowed[$table]);
