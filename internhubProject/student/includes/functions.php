<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', '/internhub');
}

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function current_student_id(): int {
    return (int) ($_SESSION['student_id'] ?? 0);
}

function student_session_valid(): bool {
    global $pdo;
    $id = current_student_id();
    if (!$id) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM students WHERE id = ?");
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

function require_student_login() {
    if (!student_session_valid()) {
        session_unset();
        header('Location: ' . APP_BASE_URL . '/login.php?role=student');
        exit;
    }
}

function flash_set(string $msg) { $_SESSION['flash'] = $msg; }
function flash_get(): ?string {
    if (!empty($_SESSION['flash'])) { $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m; }
    return null;
}
function flash_error_set(string $msg) { $_SESSION['flash_error'] = $msg; }
function flash_error_get(): ?string {
    if (!empty($_SESSION['flash_error'])) { $m = $_SESSION['flash_error']; unset($_SESSION['flash_error']); return $m; }
    return null;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) { $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}
function csrf_verify(): void {
    if (!hash_equals(csrf_token(), $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid or missing CSRF token. Please go back and try again.');
    }
}
