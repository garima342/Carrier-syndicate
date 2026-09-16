<?php
require_once __DIR__ . '/includes/functions.php';
require_login_api();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['logo'])) {
    echo json_encode(['success' => false, 'error' => 'No file received.']);
    exit;
}

$file = $_FILES['logo'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error.']);
    exit;
}

$allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/webp' => 'webp'];
$mime = mime_content_type($file['tmp_name']);

if (!isset($allowed[$mime])) {
    echo json_encode(['success' => false, 'error' => 'Please upload a PNG, JPG, GIF, or WEBP image.']);
    exit;
}

if ($file['size'] > 3 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'Image must be under 3MB.']);
    exit;
}

$ext = $allowed[$mime];
$filename = 'logo_' . current_company_id() . '_' . time() . '.' . $ext;
$destPath = __DIR__ . '/uploads/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'error' => 'Could not save file.']);
    exit;
}

$publicPath = 'uploads/' . $filename;

$stmt = $pdo->prepare("UPDATE profile SET logo_path = ?, edits_count = edits_count + 1 WHERE company_id = ?");
$stmt->execute([$publicPath, current_company_id()]);

add_notification($pdo, 'Company logo was updated.');

echo json_encode(['success' => true, 'path' => $publicPath]);
