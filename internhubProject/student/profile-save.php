<?php
require_once __DIR__ . '/includes/functions.php';
require_student_login();
csrf_verify();
$sid = current_student_id();

$full_name = trim($_POST['full_name'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$college   = trim($_POST['college'] ?? '');
$course    = trim($_POST['course'] ?? '');
$grad_year = trim($_POST['graduation_year'] ?? '');

if ($full_name === '' || !preg_match('/^[0-9]{10}$/', $phone) || $college === '' || $course === '' || !preg_match('/^[0-9]{4}$/', $grad_year)) {
    flash_error_set('Please fill every field correctly (10-digit mobile number, 4-digit year).');
    header('Location: profile.php');
    exit;
}

$stmt = $pdo->prepare("UPDATE students SET full_name=?, phone=?, college=?, course=?, graduation_year=? WHERE id=?");
$stmt->execute([$full_name, $phone, $college, $course, $grad_year, $sid]);
$_SESSION['student_name'] = $full_name;

flash_set('Profile updated.');
header('Location: profile.php');
exit;
