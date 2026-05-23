<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

$postId = (int)($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
$redirect = $_POST['redirect'] ?? '/home.php';

if ($postId && $content) {
    $stmt = $db->prepare('INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)');
    $stmt->execute([$postId, $me['id'], $content]);
}

header('Location: ' . $redirect);
exit;
