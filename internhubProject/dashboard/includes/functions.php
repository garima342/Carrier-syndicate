<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

// Base URL of the InternHub app root (one level above /dashboard).
// Adjust this if you deploy the project under a different path.
if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', '/internhub');
}

// Escape output safely
function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// The company_id of whoever is logged in, or 0 if nobody is.
function current_company_id(): int {
    return (int) ($_SESSION['company_id'] ?? 0);
}

// Call at the top of any page that renders HTML and requires login.
// Sends the visitor to the login page if there's no session.
function company_session_valid(): bool {
    global $pdo;
    $id = current_company_id();
    if (!$id) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM companies WHERE id = ?");
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

function require_login() {
    if (!company_session_valid()) {
        session_unset();
        header('Location: ' . APP_BASE_URL . '/login.php');
        exit;
    }
}

function require_login_api() {
    if (!company_session_valid()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Not authenticated. Please log in again.']);
        exit;
    }
}

// Insert a row into the notifications table, scoped to a company
function add_notification(PDO $pdo, string $message, ?int $companyId = null) {
    $companyId = $companyId ?? current_company_id();
    $stmt = $pdo->prepare("INSERT INTO notifications (company_id, message) VALUES (?, ?)");
    $stmt->execute([$companyId, $message]);
}

// Redirect helper
function redirect(string $url) {
    header("Location: $url");
    exit;
}

// Small flash-message helper using the session
function flash_set(string $msg) {
    $_SESSION['flash'] = $msg;
}

function flash_get(): ?string {
    if (!empty($_SESSION['flash'])) {
        $msg = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $msg;
    }
    return null;
}

// Flash error (shown in red banner)
function flash_error_set(string $msg) {
    $_SESSION['flash_error'] = $msg;
}

function flash_error_get(): ?string {
    if (!empty($_SESSION['flash_error'])) {
        $msg = $_SESSION['flash_error'];
        unset($_SESSION['flash_error']);
        return $msg;
    }
    return null;
}

// ---------------------------------------------------------------
// CSRF helpers
// ---------------------------------------------------------------
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Emit a hidden <input> with the CSRF token (call inside any <form>)
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

// Verify the token on POST — calls http 403 + die on mismatch
function csrf_verify(): void {
    $submitted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $submitted)) {
        http_response_code(403);
        die('Invalid or missing CSRF token. Please go back and try again.');
    }
}
