<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();
$me = currentUser();
$db = getDB();

$action = $_POST['action'] ?? '';
$redirect = $_POST['redirect'] ?? '/home.php';

if ($action === 'post') {
    $content = trim($_POST['content'] ?? '');
    if ($content) {
        $stmt = $db->prepare('INSERT INTO posts (user_id, content) VALUES (?, ?)');
        $stmt->execute([$me['id'], $content]);
    }
} elseif ($action === 'delete') {
    $postId = (int)($_POST['post_id'] ?? 0);
    $stmt = $db->prepare('SELECT user_id FROM posts WHERE id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if ($post && $post['user_id'] == $me['id']) {
        $db->prepare('DELETE FROM likes WHERE post_id = ?')->execute([$postId]);
        $db->prepare('DELETE FROM comments WHERE post_id = ?')->execute([$postId]);
        $db->prepare('DELETE FROM posts WHERE id = ?')->execute([$postId]);
    }
}

header('Location: ' . $redirect);
exit;
