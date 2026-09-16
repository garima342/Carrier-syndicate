<?php
require_once __DIR__ . '/../includes/functions.php';
require_login_api();

header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? 'poll');
$myId   = current_company_id();

// ---------------------------------------------------------------
// SEND a message
// ---------------------------------------------------------------
if ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'POST required.']);
        exit;
    }

    $receiverId = (int) ($_POST['receiver_id'] ?? 0);
    $message    = trim($_POST['message'] ?? '');

    if (!$receiverId || $receiverId === $myId) {
        echo json_encode(['success' => false, 'error' => 'Invalid recipient.']);
        exit;
    }
    if ($message === '') {
        echo json_encode(['success' => false, 'error' => 'Message cannot be empty.']);
        exit;
    }
    if (mb_strlen($message) > 2000) {
        echo json_encode(['success' => false, 'error' => 'Message too long (max 2000 chars).']);
        exit;
    }

    // Verify receiver exists
    $check = $pdo->prepare("SELECT id FROM companies WHERE id = ?");
    $check->execute([$receiverId]);
    if (!$check->fetchColumn()) {
        echo json_encode(['success' => false, 'error' => 'Recipient not found.']);
        exit;
    }

    $ins = $pdo->prepare(
        "INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)"
    );
    $ins->execute([$myId, $receiverId, $message]);

    $createdAt = date('d M, H:i');
    echo json_encode([
        'success'    => true,
        'message'    => $message,
        'created_at' => $createdAt,
    ]);
    exit;
}

// ---------------------------------------------------------------
// POLL for new messages in a conversation
// ---------------------------------------------------------------
if ($action === 'poll') {
    $withId  = (int) ($_GET['with'] ?? 0);
    $afterId = (int) ($_GET['after'] ?? 0);

    if (!$withId || $withId === $myId) {
        echo json_encode([]);
        exit;
    }

    // Mark as read while we're at it
    $markRead = $pdo->prepare(
        "UPDATE chat_messages SET is_read = 1
         WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
    );
    $markRead->execute([$withId, $myId]);

    $stmt = $pdo->prepare(
        "SELECT m.id, m.message, m.created_at, p.company_name AS sender_name
         FROM chat_messages m
         JOIN profile p ON p.company_id = m.sender_id
         WHERE m.sender_id = ? AND m.receiver_id = ?
           AND m.id > ?
         ORDER BY m.created_at ASC"
    );
    $stmt->execute([$withId, $myId, $afterId]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['message']     = h($row['message']);
        $row['sender_name'] = h($row['sender_name']);
        $row['created_at']  = date('d M, H:i', strtotime($row['created_at']));
    }
    echo json_encode($rows);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action.']);
