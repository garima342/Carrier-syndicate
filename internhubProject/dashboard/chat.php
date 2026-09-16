<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$myId = current_company_id();

// All other registered companies to chat with
$contactsStmt = $pdo->prepare(
    "SELECT c.id, p.company_name,
            (SELECT message FROM chat_messages
             WHERE (sender_id = ? AND receiver_id = c.id)
                OR (sender_id = c.id AND receiver_id = ?)
             ORDER BY created_at DESC LIMIT 1) AS last_message,
            (SELECT COUNT(*) FROM chat_messages
             WHERE sender_id = c.id AND receiver_id = ? AND is_read = 0) AS unread_count
     FROM companies c
     JOIN profile p ON p.company_id = c.id
     WHERE c.id <> ?
     ORDER BY (SELECT created_at FROM chat_messages
               WHERE (sender_id = ? AND receiver_id = c.id)
                  OR (sender_id = c.id AND receiver_id = ?)
               ORDER BY created_at DESC LIMIT 1) DESC,
              p.company_name ASC"
);
$contactsStmt->execute([$myId, $myId, $myId, $myId, $myId, $myId]);
$contacts = $contactsStmt->fetchAll();

// Active conversation
$withId       = isset($_GET['with']) ? (int) $_GET['with'] : 0;
$withProfile  = null;
$messages     = [];

if ($withId && $withId !== $myId) {
    $wpStmt = $pdo->prepare("SELECT p.company_name FROM profile p WHERE p.company_id = ?");
    $wpStmt->execute([$withId]);
    $withProfile = $wpStmt->fetchColumn();

    if ($withProfile) {
        // Mark messages from that person as read
        $markRead = $pdo->prepare(
            "UPDATE chat_messages SET is_read = 1
             WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
        );
        $markRead->execute([$withId, $myId]);

        // Load conversation
        $msgStmt = $pdo->prepare(
            "SELECT m.*, p.company_name AS sender_name
             FROM chat_messages m
             JOIN profile p ON p.company_id = m.sender_id
             WHERE (m.sender_id = ? AND m.receiver_id = ?)
                OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.created_at ASC"
        );
        $msgStmt->execute([$myId, $withId, $withId, $myId]);
        $messages = $msgStmt->fetchAll();
    }
}

// My profile name
$myNameStmt = $pdo->prepare("SELECT company_name FROM profile WHERE company_id = ?");
$myNameStmt->execute([$myId]);
$myName = $myNameStmt->fetchColumn() ?: ($_SESSION['company_name'] ?? 'Me');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages — Company Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="shell">
  <?php include 'includes/header.php'; ?>
  <div class="body">
    <?php include 'includes/sidebar.php'; ?>
    <main class="main">
      <div class="page-head">
        <h1>💬 Messages</h1>
        <p>Chat with other registered companies on InternHub.</p>
      </div>

      <div class="chat-wrap">
        <!-- Contact list -->
        <div class="chat-contacts">
          <div class="chat-contacts-head">Companies</div>
          <div class="chat-contact-list">
            <?php if (!$contacts): ?>
              <div style="padding:16px;font-size:12.5px;color:#8A93A6;">No other companies registered yet.</div>
            <?php else: ?>
              <?php foreach ($contacts as $c): ?>
                <a href="chat.php?with=<?= (int)$c['id'] ?>" style="text-decoration:none;color:inherit;">
                  <div class="chat-contact-item <?= ($withId === (int)$c['id']) ? 'selected' : '' ?>">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:6px;">
                      <span class="cc-name"><?= h($c['company_name']) ?></span>
                      <?php if ($c['unread_count'] > 0): ?>
                        <span class="unread-dot" title="<?= (int)$c['unread_count'] ?> unread"></span>
                      <?php endif; ?>
                    </div>
                    <?php if ($c['last_message']): ?>
                      <div class="cc-preview"><?= h(mb_strimwidth($c['last_message'], 0, 50, '…')) ?></div>
                    <?php endif; ?>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Conversation pane -->
        <div class="chat-main">
          <?php if ($withProfile): ?>
            <div class="chat-main-head">
              💬 <?= h($withProfile) ?>
            </div>

            <div class="chat-messages" id="chatMessages">
              <?php if (!$messages): ?>
                <div class="no-chat">No messages yet. Say hello 👋</div>
              <?php else: ?>
                <?php foreach ($messages as $msg):
                  $isMine = (int)$msg['sender_id'] === $myId;
                ?>
                  <div class="chat-bubble-wrap <?= $isMine ? 'mine' : 'theirs' ?>">
                    <div class="chat-bubble"><?= nl2br(h($msg['message'])) ?></div>
                    <div class="chat-meta">
                      <?= h($isMine ? 'You' : $msg['sender_name']) ?> · <?= h(date('d M, H:i', strtotime($msg['created_at']))) ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="chat-input-row">
              <textarea id="chatInput" placeholder="Type a message…" rows="2" maxlength="2000"></textarea>
              <button class="btn btn-create" id="chatSendBtn">Send ➤</button>
            </div>

          <?php else: ?>
            <div class="chat-placeholder">
              <?= $contacts ? 'Select a company from the left to start chatting.' : 'No other companies to chat with yet.' ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </main>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>

<script>
(function () {
  var withId = <?= (int)$withId ?>;
  if (!withId) return;

  var chatMessages = document.getElementById('chatMessages');
  var chatInput    = document.getElementById('chatInput');
  var chatSendBtn  = document.getElementById('chatSendBtn');

  // Scroll to bottom on load
  if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;

  function sendMessage() {
    var text = chatInput.value.trim();
    if (!text) return;

    chatSendBtn.disabled = true;

    fetch('api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'action=send&receiver_id=' + withId +
            '&message=' + encodeURIComponent(text) +
            '&csrf_token=' + encodeURIComponent(document.querySelector('meta[name="csrf"]')?.content || '')
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      if (data.success) {
        chatInput.value = '';
        appendBubble(data.message, 'mine', 'You', data.created_at);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      } else {
        alert(data.error || 'Could not send message.');
      }
    })
    .catch(function () { alert('Server error. Please try again.'); })
    .finally(function () { chatSendBtn.disabled = false; });
  }

  function appendBubble(text, side, name, time) {
    // Remove "no messages" placeholder if present
    var noChat = chatMessages.querySelector('.no-chat');
    if (noChat) noChat.remove();

    var wrap = document.createElement('div');
    wrap.className = 'chat-bubble-wrap ' + side;
    wrap.innerHTML =
      '<div class="chat-bubble">' + escapeHtml(text).replace(/\n/g, '<br>') + '</div>' +
      '<div class="chat-meta">' + escapeHtml(name) + ' · ' + escapeHtml(time) + '</div>';
    chatMessages.appendChild(wrap);
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  // Poll for new messages every 5 seconds
  var lastId = <?= $messages ? (int)end($messages)['id'] : 0 ?>;

  function pollMessages() {
    fetch('api/chat.php?action=poll&with=' + withId + '&after=' + lastId)
      .then(function (res) { return res.json(); })
      .then(function (items) {
        items.forEach(function (m) {
          appendBubble(m.message, 'theirs', m.sender_name, m.created_at);
          lastId = m.id;
        });
        if (items.length) chatMessages.scrollTop = chatMessages.scrollHeight;
      })
      .catch(function () {});
  }
  setInterval(pollMessages, 5000);

  // Send on button click
  chatSendBtn.addEventListener('click', sendMessage);

  // Send on Ctrl+Enter / Cmd+Enter
  chatInput.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
      e.preventDefault();
      sendMessage();
    }
  });
})();
</script>
<!-- Embed CSRF token for JS -->
<meta name="csrf" content="<?= h(csrf_token()) ?>">

<script src="js/script.js"></script>
</body>
</html>
