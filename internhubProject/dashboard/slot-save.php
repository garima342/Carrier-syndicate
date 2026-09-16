<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('slots.php');
csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$total = max(0, (int) ($_POST['total_slots'] ?? 0));
$filled = max(0, (int) ($_POST['filled_slots'] ?? 0));

if ($title === '') {
    flash_set('Title is required — please try again.');
    redirect('slot-form.php' . ($id ? '?id=' . $id : ''));
}

if ($id) {
    $stmt = $pdo->prepare("UPDATE slots SET title=?, total_slots=?, filled_slots=? WHERE id=? AND company_id=?");
    $stmt->execute([$title, $total, $filled, $id, current_company_id()]);
    add_notification($pdo, "Slot category \"$title\" was updated.");
    flash_set('Slot category updated.');
} else {
    $stmt = $pdo->prepare("INSERT INTO slots (company_id, title, total_slots, filled_slots) VALUES (?,?,?,?)");
    $stmt->execute([current_company_id(), $title, $total, $filled]);
    add_notification($pdo, "Slot category \"$title\" was added.");
    flash_set('Slot category created.');
}

redirect('slots.php');
